<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Public Free Consultation booking controller (no login required)
 */
class Booking extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('consultation_model');
    }

    public function index()
    {
        $data['title'] = 'Book a Free Consultation';
        $data['settings'] = $this->consultation_model->get_settings();
        $data['consultants'] = $this->consultation_model->get_consultants(true);
        $data['timezones'] = consultation_timezone_list();
        $data['subview'] = $this->load->view('booking/booking', $data, true);
        $this->load->view('booking/_layout_main', $data);
    }

    /**
     * AJAX: list active consultants as JSON
     */
    public function get_consultants()
    {
        $this->_check_ajax();
        $this->_check_booking_enabled();

        $consultants = $this->consultation_model->get_consultants(true);
        $result = array();
        foreach ($consultants as $consultant) {
            $result[] = array(
                'consultant_id' => (int)$consultant->consultant_id,
                'name'          => $consultant->name,
                'email'         => $consultant->email,
                'phone'         => $consultant->phone,
                'timezone'      => $consultant->timezone,
                'department'    => $consultant->department,
                'bio'           => $consultant->bio,
                'avatar'        => !empty($consultant->avatar) ? base_url() . $consultant->avatar : '',
            );
        }
        $this->_respond(array('success' => true, 'consultants' => $result));
    }

    /**
     * AJAX: get available time slots.
     * Pass either a single `date` (Y-m-d, customer-local) or `days` (count)
     * plus `consultant_id`, `timezone` and optional `duration`.
     */
    public function get_slots()
    {
        $this->_check_ajax();
        $this->_check_booking_enabled();

        $consultant_id = (int)$this->input->get('consultant_id');
        $customer_tz = $this->input->get('timezone');
        $duration = (int)$this->input->get('duration');
        $date = $this->input->get('date');
        $from = $this->input->get('from');
        $to = $this->input->get('to');
        $days = (int)$this->input->get('days');

        if (empty($customer_tz)) {
            $customer_tz = consultation_company_timezone();
        }
        if ($duration <= 0) {
            $duration = (int)$this->consultation_model->get_setting('default_duration', 30);
        }

        $result = array();
        if (!empty($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            // Specific date: either a chosen consultant or the first free one.
            if ($consultant_id > 0) {
                $result[$date] = $this->consultation_model->get_available_slots($consultant_id, $date, $customer_tz, $duration);
            } else {
                $result[$date] = $this->consultation_model->get_consultant_agnostic_slots($date, $customer_tz, $duration);
            }
        } elseif (!empty($from) && !empty($to)
            && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)
            && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            // Calendar range: return the available dates for highlighting.
            $available_dates = $this->consultation_model->get_available_dates($from, $to, $customer_tz, $duration);
            $this->_respond(array(
                'success'         => true,
                'duration'        => $duration,
                'timezone'        => $customer_tz,
                'timezone_offset' => $this->_tz_offset_minutes($customer_tz),
                'available_dates' => $available_dates,
            ));
            return;
        } elseif ($consultant_id > 0) {
            // Legacy fallback: next N days for a specific consultant.
            $days = $days > 0 ? min($days, 31) : 31;
            for ($i = 0; $i < $days; $i++) {
                $d = date('Y-m-d', strtotime('+' . $i . ' days'));
                $slots = $this->consultation_model->get_available_slots($consultant_id, $d, $customer_tz, $duration);
                if (!empty($slots)) {
                    $result[$d] = $slots;
                }
            }
        }

        $this->_respond(array(
            'success'    => true,
            'duration'   => $duration,
            'timezone'   => $customer_tz,
            'timezone_offset' => $this->_tz_offset_minutes($customer_tz),
            'slots'      => $result,
        ));
    }

    /**
     * AJAX: create a booking
     */
    public function book()
    {
        $this->_check_ajax();
        $this->_check_booking_enabled();

        $consultant_id = (int)$this->input->post('consultant_id');
        $customer_name = trim($this->input->post('customer_name', true));
        $customer_email = trim($this->input->post('customer_email', true));
        $customer_phone = trim($this->input->post('customer_phone', true));
        $company = trim($this->input->post('company', true));
        $country = trim($this->input->post('country', true));
        $customer_timezone = trim($this->input->post('customer_timezone', true));
        $appointment_date = trim($this->input->post('appointment_date', true));
        $appointment_time = trim($this->input->post('appointment_time', true));
        $consultation_type = trim($this->input->post('consultation_type', true));
        $notes = trim($this->input->post('notes', true));
        $duration = (int)$this->input->post('duration_minutes');

        $errors = array();
        if (empty($customer_name)) {
            $errors[] = 'Please enter your name.';
        }
        if (empty($customer_email) || !filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (empty($customer_timezone)) {
            $errors[] = 'Please select your timezone.';
        }
        if (empty($appointment_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $appointment_date)) {
            $errors[] = 'Please choose an appointment date.';
        }
        if (empty($appointment_time) || !preg_match('/^\d{2}:\d{2}$/', $appointment_time)) {
            $errors[] = 'Please choose an appointment time.';
        }
        if (empty($consultation_type)) {
            $consultation_type = 'consultation';
        }
        if ($duration <= 0) {
            $duration = (int)$this->consultation_model->get_setting('default_duration', 30);
        }

        // Auto-assign the first consultant who is free at the requested slot.
        if ($consultant_id <= 0) {
            $consultant_id = (int)$this->consultation_model->assign_consultant_for_slot($appointment_date, $appointment_time, $customer_timezone, $duration);
        }

        if (!empty($errors)) {
            $this->_respond(array('success' => false, 'message' => implode(' ', $errors)), 422);
        }

        $consultant = $this->consultation_model->get_consultant($consultant_id);
        if (empty($consultant) || (int)$consultant->is_active !== 1) {
            $this->_respond(array('success' => false, 'message' => 'Sorry, no consultant is available at this time. Please choose another time.'), 409);
        }

        if (!$this->consultation_model->is_slot_available($consultant_id, $appointment_date, $appointment_time, $customer_timezone, $duration)) {
            $this->_respond(array('success' => false, 'message' => 'Sorry, that time slot is no longer available. Please pick another time.'), 409);
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
            'country'           => $country,
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

        // Customer-facing email must NOT reveal the assigned consultant.
        $tokens = consultation_mail_tokens($appointment);
        $tokens['MEETING_URL'] = $data['meeting_url'];
        $customer_tokens = $tokens;
        $customer_tokens['CONSULTANT_NAME'] = consultation_generic_consultant_name();
        consultation_send_mail('consultation_confirmation_customer', $customer_email, $customer_tokens);

        $tokens['MEETING_URL'] = $data['moderator_url'];
        consultation_send_mail('consultation_confirmation_consultant', $consultant->email, $tokens);

        $this->_respond(array(
            'success'    => true,
            'appointment_id' => (int)$appointment_id,
            'confirm_url'    => site_url('booking/confirm/' . $room),
        ));
    }

    /**
     * Public confirmation page
     *
     * @param string $room Meeting room name
     */
    public function confirm($room)
    {
        $appointment = $this->consultation_model->get_appointment_by_room($room);
        if (empty($appointment)) {
            show_404();
        }
        $data['title'] = 'Booking Confirmed';
        $data['appointment'] = $appointment;

        // UTC start/end for calendar links
        $appointment->start_utc = $this->_appointment_to_utc($appointment, 'Ymd\THis\Z');
        $appointment->end_utc = $this->_appointment_to_utc($appointment, 'Ymd\THis\Z', true);

        $data['subview'] = $this->load->view('booking/confirmation', $data, true);
        $this->load->view('booking/_layout_main', $data);
    }

    /**
     * Public cancel via one-time token (used by the {CANCEL_LINK} email link).
     *
     * @param string $token Cancel token
     */
    public function cancel($token)
    {
        $token = trim(rawurldecode($token));
        if (empty($token)) {
            show_404();
        }
        $appointment = $this->consultation_model->get_appointment_by_token($token);
        if (empty($appointment)) {
            show_404();
        }

        if ($appointment->status === 'confirmed') {
            $this->consultation_model->change_status($appointment->appointment_id, 'cancelled');
            consultation_notify_cancellation($appointment);
        }

        $data['title'] = 'Consultation Cancelled';
        $data['appointment'] = $appointment;
        $data['subview'] = $this->load->view('booking/cancelled', $data, true);
        $this->load->view('booking/_layout_main', $data);
    }

    /* -----------------------------------------------------------------
     * Internal helpers
     * ---------------------------------------------------------------- */

    private function _check_ajax()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
    }

    private function _check_booking_enabled()
    {
        if ((string)$this->consultation_model->get_setting('booking_enabled', '1') !== '1') {
            $this->_respond(array('success' => false, 'message' => 'Online booking is currently disabled.'), 403);
        }
    }

    private function _respond($data, $status = 200)
    {
        $this->output->set_status_header($status);
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($data));
        $this->output->_display();
        exit;
    }

    private function _tz_offset_minutes($tz)
    {
        try {
            return (new DateTime('now', new DateTimeZone($tz)))->getOffset() / 60;
        } catch (Exception $e) {
            return 0;
        }
    }

    private function _appointment_to_utc($appointment, $format, $end = false)
    {
        $tz = !empty($appointment->customer_timezone) ? $appointment->customer_timezone : consultation_company_timezone();
        try {
            $dt = new DateTime($appointment->appointment_date . ' ' . $appointment->appointment_time, new DateTimeZone($tz));
        } catch (Exception $e) {
            return '';
        }
        if ($end) {
            $duration = (int)$appointment->duration_minutes;
            if ($duration <= 0) {
                $duration = 30;
            }
            $dt->modify('+' . $duration . ' minutes');
        }
        $dt->setTimezone(new DateTimeZone('UTC'));
        return $dt->format($format);
    }
}
