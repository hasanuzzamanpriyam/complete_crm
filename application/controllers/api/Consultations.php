<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Consultation REST API (v1).
 *
 * Endpoints:
 *   GET  api/v1/consultations/consultants          List active consultants
 *   GET  api/v1/consultations/slots                 Available slots
 *   GET  api/v1/consultations/bookings              List appointments
 *   GET  api/v1/consultations/bookings/{id}         Single appointment
 *   POST api/v1/consultations/bookings              Create a booking
 *   POST api/v1/consultations/bookings/{id}/cancel  Cancel an appointment
 */
class Consultations extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Api_auth');
        $this->load->model('consultation_model');
    }

    public function consultants()
    {
        $this->api_auth->authenticate();

        $consultants = $this->consultation_model->get_consultants(true);
        $result = array();
        foreach ($consultants as $c) {
            $result[] = array(
                'consultant_id' => (int)$c->consultant_id,
                'name'          => $c->name,
                'email'         => $c->email,
                'phone'         => $c->phone,
                'timezone'      => $c->timezone,
                'department'    => $c->department,
                'bio'           => $c->bio,
            );
        }
        return $this->_respond(200, true, 'OK', array('consultants' => $result));
    }

    public function slots()
    {
        $this->api_auth->authenticate();

        $consultant_id = (int)$this->input->get('consultant_id');
        $date = $this->input->get('date');
        $timezone = $this->input->get('timezone');
        $duration = (int)$this->input->get('duration');

        if ($consultant_id <= 0 || empty($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $this->_respond(400, false, 'consultant_id and date (Y-m-d) are required');
        }
        if (empty($timezone)) {
            $timezone = consultation_company_timezone();
        }
        if ($duration <= 0) {
            $duration = (int)$this->consultation_model->get_setting('default_duration', 30);
        }

        $slots = $this->consultation_model->get_available_slots($consultant_id, $date, $timezone, $duration);

        return $this->_respond(200, true, 'OK', array(
            'consultant_id' => $consultant_id,
            'date'          => $date,
            'timezone'      => $timezone,
            'duration'      => $duration,
            'slots'         => $slots,
        ));
    }

    public function bookings()
    {
        $this->api_auth->authenticate();

        if ($this->input->method(true) === 'POST') {
            return $this->_create_booking();
        }

        $status = $this->input->get('status');
        $this->db->select('tbl_consultation_appointments.*, tbl_consultants.name as consultant_name');
        $this->db->from('tbl_consultation_appointments');
        $this->db->join('tbl_consultants', 'tbl_consultants.consultant_id = tbl_consultation_appointments.consultant_id', 'left');
        if (!empty($status)) {
            $this->db->where('tbl_consultation_appointments.status', $status);
        }
        $this->db->order_by('tbl_consultation_appointments.appointment_date', 'DESC');
        $rows = $this->db->get()->result();

        $result = array();
        foreach ($rows as $a) {
            $result[] = $this->_appointment_payload($a);
        }
        return $this->_respond(200, true, 'OK', array('appointments' => $result));
    }

    public function booking($id)
    {
        $this->api_auth->authenticate();

        $appointment = $this->consultation_model->get_appointment((int)$id);
        if (empty($appointment)) {
            return $this->_respond(404, false, 'Appointment not found');
        }
        return $this->_respond(200, true, 'OK', array('appointment' => $this->_appointment_payload($appointment)));
    }

    public function cancel($id)
    {
        $this->api_auth->authenticate();

        $appointment = $this->consultation_model->get_appointment((int)$id);
        if (empty($appointment)) {
            return $this->_respond(404, false, 'Appointment not found');
        }
        if ($appointment->status === 'cancelled') {
            return $this->_respond(409, false, 'Appointment is already cancelled');
        }
        $this->consultation_model->change_status($appointment->appointment_id, 'cancelled');
        consultation_notify_cancellation($appointment);

        return $this->_respond(200, true, 'Appointment cancelled', array(
            'appointment' => $this->_appointment_payload($this->consultation_model->get_appointment($appointment->appointment_id)),
        ));
    }

    /* -----------------------------------------------------------------
     * Internal helpers
     * ---------------------------------------------------------------- */

    private function _create_booking()
    {
        if ((string)$this->consultation_model->get_setting('booking_enabled', '1') !== '1') {
            return $this->_respond(403, false, 'Online booking is currently disabled');
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $input = $this->input->post();
        }

        $consultant_id = (int)($input['consultant_id'] ?? 0);
        $customer_name = trim($input['customer_name'] ?? '');
        $customer_email = trim($input['customer_email'] ?? '');
        $customer_phone = trim($input['customer_phone'] ?? '');
        $company = trim($input['company'] ?? '');
        $customer_timezone = trim($input['customer_timezone'] ?? '');
        $appointment_date = trim($input['appointment_date'] ?? '');
        $appointment_time = trim($input['appointment_time'] ?? '');
        $consultation_type = trim($input['consultation_type'] ?? 'consultation');
        $notes = trim($input['notes'] ?? '');
        $duration = (int)($input['duration_minutes'] ?? 0);

        $errors = array();
        if ($consultant_id <= 0) {
            $errors[] = 'consultant_id is required';
        }
        if (empty($customer_name)) {
            $errors[] = 'customer_name is required';
        }
        if (empty($customer_email) || !filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'customer_email must be a valid email';
        }
        if (empty($customer_timezone)) {
            $errors[] = 'customer_timezone is required';
        }
        if (empty($appointment_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $appointment_date)) {
            $errors[] = 'appointment_date (Y-m-d) is required';
        }
        if (empty($appointment_time) || !preg_match('/^\d{2}:\d{2}$/', $appointment_time)) {
            $errors[] = 'appointment_time (H:i) is required';
        }
        if ($duration <= 0) {
            $duration = (int)$this->consultation_model->get_setting('default_duration', 30);
        }
        if (!empty($errors)) {
            return $this->_respond(422, false, implode(' ', $errors));
        }

        $consultant = $this->consultation_model->get_consultant($consultant_id);
        if (empty($consultant) || (int)$consultant->is_active !== 1) {
            return $this->_respond(422, false, 'This consultant is not available');
        }
        if (!$this->consultation_model->is_slot_available($consultant_id, $appointment_date, $appointment_time, $customer_timezone, $duration)) {
            return $this->_respond(409, false, 'Sorry, that time slot is no longer available. Please pick another time.');
        }

        $room = consultation_generate_room();
        $password = consultation_generate_password();
        $cancel_token = bin2hex(random_bytes(32));

        $data = array(
            'consultant_id'     => $consultant_id,
            'customer_name'     => $customer_name,
            'customer_email'    => $customer_email,
            'customer_phone'    => $customer_phone,
            'company'           => $company,
            'country'           => trim($input['country'] ?? ''),
            'customer_timezone' => $customer_timezone,
            'appointment_date'  => $appointment_date,
            'appointment_time'  => $appointment_time,
            'duration_minutes'  => $duration,
            'consultation_type' => $consultation_type,
            'notes'             => $notes,
            'meeting_room'      => $room,
            'meeting_url'       => consultation_build_guest_url($room, $customer_email, $customer_name),
            'moderator_url'     => consultation_build_moderator_url($room, $consultant->email, $consultant->name),
            'meeting_password'  => $password,
            'status'            => 'confirmed',
            'cancel_token'      => $cancel_token,
        );

        $appointment_id = $this->consultation_model->create_appointment($data);

        $data['appointment_id'] = (int)$appointment_id;
        $appointment = (object)$data;
        $appointment->consultant_name = $consultant->name;
        $appointment->consultant_email = $consultant->email;

        $tokens = consultation_mail_tokens($appointment);
        $tokens['MEETING_URL'] = $data['meeting_url'];
        consultation_send_mail('consultation_confirmation_customer', $customer_email, $tokens);
        $tokens['MEETING_URL'] = $data['moderator_url'];
        consultation_send_mail('consultation_confirmation_consultant', $consultant->email, $tokens);

        return $this->_respond(201, true, 'Booking created', array(
            'appointment' => $this->_appointment_payload($this->consultation_model->get_appointment($appointment_id)),
        ));
    }

    private function _appointment_payload($a)
    {
        return array(
            'appointment_id'    => (int)$a->appointment_id,
            'consultant_id'     => (int)$a->consultant_id,
            'consultant_name'   => !empty($a->consultant_name) ? $a->consultant_name : null,
            'customer_name'     => $a->customer_name,
            'customer_email'    => $a->customer_email,
            'customer_phone'    => $a->customer_phone,
            'company'           => $a->company,
            'customer_timezone' => $a->customer_timezone,
            'appointment_date'  => $a->appointment_date,
            'appointment_time'  => substr($a->appointment_time, 0, 5),
            'duration_minutes'  => (int)$a->duration_minutes,
            'consultation_type' => $a->consultation_type,
            'status'            => $a->status,
            'meeting_url'       => $a->meeting_url,
            'meeting_password'  => $a->meeting_password,
            'created_at'        => $a->created_at,
        );
    }

    private function _respond($status_code, $success, $message, $data = null)
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $response = array('success' => $success, 'message' => $message);
        if ($data !== null) {
            $response = array_merge($response, $data);
        }
        $this->output
            ->set_status_header($status_code)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}
