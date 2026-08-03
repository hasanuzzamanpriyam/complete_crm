<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Admin management for the Free Consultation booking system.
 */
class Consultation extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('consultation_model');
        $this->load->helper('form');
    }

    /* -----------------------------------------------------------------
     * Entry point / tabs
     * ---------------------------------------------------------------- */

    public function index()
    {
        redirect('admin/consultation/appointments');
    }

    /* -----------------------------------------------------------------
     * Appointments
     * ---------------------------------------------------------------- */

    public function appointments()
    {
        $data['title'] = lang('consultation_appointments');

        $filters = array();
        $status = $this->input->get('status', true);
        $consultant_id = (int)$this->input->get('consultant_id');
        $search = $this->input->get('search', true);
        $from_date = $this->input->get('from_date', true);
        $to_date = $this->input->get('to_date', true);

        if (!empty($status)) {
            $filters['status'] = $status;
        }
        if (!empty($consultant_id)) {
            $filters['consultant_id'] = $consultant_id;
        }
        if (!empty($search)) {
            $filters['search'] = $search;
        }
        if (!empty($from_date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from_date)) {
            $filters['from_date'] = $from_date;
        }
        if (!empty($to_date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to_date)) {
            $filters['to_date'] = $to_date;
        }

        $data['status'] = $status;
        $data['consultant_id'] = $consultant_id;
        $data['search'] = $search;
        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;
        $data['status_counts'] = $this->consultation_model->count_by_status();
        $data['consultants'] = $this->consultation_model->get_consultants(true);
        $data['appointments'] = $this->consultation_model->get_appointments($filters);

        $data['subview'] = $this->load->view('admin/consultation/appointments', $data, true);
        $this->_render_or_ajax($data);
    }

    /**
     * Change an appointment's status (POST) and redirect back.
     */
    public function change_status($id = null)
    {
        $id = (int)$id;
        if ($id <= 0) {
            $id = (int)$this->input->post('appointment_id');
        }
        $status = $this->input->post('status', true);
        if ($id > 0) {
            $allowed = array('pending', 'confirmed', 'completed', 'cancelled', 'no_show');
            if (in_array($status, $allowed)) {
                $appointment = $this->consultation_model->get_appointment($id);
                $this->consultation_model->change_status($id, $status);
                if ($status === 'cancelled' && !empty($appointment) && $appointment->status !== 'cancelled') {
                    consultation_notify_cancellation($appointment);
                }
                set_message('success', lang('consultation_appointment_updated'));
            }
        }
        redirect('admin/consultation/appointments');
    }

    /**
     * View appointment details (modal).
     */
    public function view_appointment($id)
    {
        $appointment = $this->consultation_model->get_appointment((int)$id);
        if (empty($appointment)) {
            show_404();
        }
        $data['appointment'] = $appointment;
        $this->load->view('admin/consultation/_modal_appointment', $data);
        $this->load->view('admin/_layout_modal_lg', $data);
    }

    /**
     * Cancel an appointment (also used by the public cancel link in Step 5).
     */
    public function cancel_appointment($id)
    {
        $id = (int)$id;
        $appointment = $this->consultation_model->get_appointment($id);
        if (!empty($appointment)) {
            if ($appointment->status !== 'cancelled') {
                $this->consultation_model->change_status($id, 'cancelled');
                consultation_notify_cancellation($appointment);
            }
            set_message('success', lang('consultation_appointment_cancelled'));
        } else {
            set_message('error', lang('no_record_found'));
        }
        redirect('admin/consultation/appointments');
    }

    /* -----------------------------------------------------------------
     * Consultants
     * ---------------------------------------------------------------- */

    public function consultants()
    {
        $data['title'] = lang('consultation_consultants');
        $data['consultants'] = $this->consultation_model->get_consultants();
        $data['timezones'] = consultation_timezone_list();
        $data['subview'] = $this->load->view('admin/consultation/consultants', $data, true);
        $this->_render_or_ajax($data);
    }

    /**
     * Add/edit consultant (modal).
     */
    public function consultant_form($id = null)
    {
        $id = (int)$id;
        $data['consultant'] = null;
        if (!empty($id)) {
            $data['consultant'] = $this->consultation_model->get_consultant($id);
            if (empty($data['consultant'])) {
                show_404();
            }
        }
        $data['timezones'] = consultation_timezone_list();
        $this->load->view('admin/consultation/_modal_consultant', $data);
        $this->load->view('admin/_layout_modal', $data);
    }

    /**
     * Save consultant from POST.
     */
    public function save_consultant($id = null)
    {
        $id = (int)$id;
        $data = array(
            'name'       => trim($this->input->post('name', true)),
            'email'      => trim($this->input->post('email', true)),
            'phone'      => trim($this->input->post('phone', true)),
            'department' => trim($this->input->post('department', true)),
            'bio'        => trim($this->input->post('bio', true)),
            'timezone'   => trim($this->input->post('timezone', true)),
            'is_active'  => (int)(bool)$this->input->post('is_active'),
        );

        if (empty($data['name']) || empty($data['email']) || empty($data['timezone'])) {
            set_message('error', lang('consultation_consultant_name') . ' / ' . lang('consultation_consultant_email') . ' / ' . lang('consultation_timezone') . ' ' . lang('is_required'));
            redirect('admin/consultation/consultants');
        }
        if (!in_array($data['timezone'], consultation_timezone_list())) {
            $data['timezone'] = consultation_company_timezone();
        }

        $this->consultation_model->save_consultant($data, $id);
        set_message('success', lang('consultation_consultant_saved'));
        redirect('admin/consultation/consultants');
    }

    /**
     * Delete a consultant.
     */
    public function delete_consultant($id)
    {
        $id = (int)$id;
        $consultant = $this->consultation_model->get_consultant($id);
        if (!empty($consultant)) {
            $this->consultation_model->delete_consultant($id);
            set_message('success', lang('consultation_consultant_deleted'));
        } else {
            set_message('error', lang('no_record_found'));
        }
        redirect('admin/consultation/consultants');
    }

    /**
     * Weekly schedule editor for a consultant (modal).
     */
    public function slots($id)
    {
        $id = (int)$id;
        $data['consultant'] = $this->consultation_model->get_consultant($id);
        if (empty($data['consultant'])) {
            show_404();
        }
        $slots = $this->consultation_model->get_slots($id, true);
        $data['slots'] = array();
        foreach ($slots as $slot) {
            $data['slots'][(int)$slot->day_of_week] = array(
                'start_time' => $slot->start_time,
                'end_time'   => $slot->end_time,
                'is_active'  => (int)$slot->is_active,
            );
        }
        $data['days'] = array(
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        );
        $this->load->view('admin/consultation/_modal_slots', $data);
        $this->load->view('admin/_layout_modal', $data);
    }

    /**
     * Save a consultant's weekly schedule.
     */
    public function save_slots($id)
    {
        $id = (int)$id;
        $consultant = $this->consultation_model->get_consultant($id);
        if (empty($consultant)) {
            show_404();
        }

        $start_times = $this->input->post('start_time');
        $end_times = $this->input->post('end_time');
        $actives = $this->input->post('is_active');

        $slots = array();
        for ($day = 0; $day <= 6; $day++) {
            $start = isset($start_times[$day]) ? trim($start_times[$day]) : '';
            $end = isset($end_times[$day]) ? trim($end_times[$day]) : '';
            if (empty($start) || empty($end)) {
                continue;
            }
            if (!preg_match('/^\d{1,2}:\d{2}$/', $start) || !preg_match('/^\d{1,2}:\d{2}$/', $end)) {
                continue;
            }
            $slots[] = array(
                'day_of_week' => $day,
                'start_time'  => $start,
                'end_time'    => $end,
                'is_active'   => !empty($actives[$day]) ? 1 : 0,
            );
        }

        $this->consultation_model->save_slots($id, $slots);
        set_message('success', lang('consultation_slots_saved'));
        redirect('admin/consultation/consultants');
    }

    /* -----------------------------------------------------------------
     * Settings
     * ---------------------------------------------------------------- */

    public function settings()
    {
        $data['title'] = lang('consultation_settings');
        $data['settings'] = $this->consultation_model->get_settings();
        $data['subview'] = $this->load->view('admin/consultation/settings', $data, true);
        $this->_render_or_ajax($data);
    }

    public function save_settings()
    {
        $default_duration = max(5, (int)$this->input->post('default_duration'));
        $min_advance = max(0, (int)$this->input->post('min_advance_hours'));
        $buffer = max(0, (int)$this->input->post('buffer_minutes'));
        $reminder_hours = trim($this->input->post('reminder_hours', true));

        $hours = array();
        foreach (explode(',', $reminder_hours) as $h) {
            $h = trim($h);
            if (is_numeric($h) && $h > 0) {
                $hours[] = (int)$h;
            }
        }
        if (empty($hours)) {
            $hours = array(24, 1);
        }
        $hours = array_unique($hours);
        sort($hours);

        $this->consultation_model->save_settings(array(
            'booking_enabled'   => (string)(int)(bool)$this->input->post('booking_enabled'),
            'default_duration'  => (string)$default_duration,
            'min_advance_hours' => (string)$min_advance,
            'buffer_minutes'    => (string)$buffer,
            'reminder_hours'    => implode(',', $hours),
        ));

        set_message('success', lang('consultation_settings_saved'));
        redirect('admin/consultation/settings');
    }

    public function regenerate_api_key()
    {
        $key = bin2hex(random_bytes(32));
        $this->consultation_model->set_setting('api_key', $key);
        set_message('success', lang('consultation_api_key_generated'));
        redirect('admin/consultation/settings');
    }

    /* -----------------------------------------------------------------
     * Internal helpers
     * ---------------------------------------------------------------- */

    private function _render_or_ajax($data)
    {
        if ($this->input->get('ajax')) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'html'  => $data['subview'],
                    'title' => isset($data['title']) ? $data['title'] : '',
                )));
            return;
        }
        $this->load->view('admin/_layout_main', $data);
    }
}
