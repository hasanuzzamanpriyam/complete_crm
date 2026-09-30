<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of admistrator
 *
 * @author pc mart ltd
 */
class Calendar extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin_model');
        $this->load->model('invoice_model');
        $this->load->model('estimates_model');
        $this->load->model('domain_model');
        $this->load->model('hosting_model');
    }

    public function index($action = NULL)
    {
        $data['title'] = config_item('company_name');
        $data['dataTables'] = true;
        $data['select_2'] = true;
        $data['datepicker'] = true;
        $data['page'] = lang('calendar');
        $data['domain_model'] = $this->domain_model;
        $data['hosting_model'] = $this->hosting_model;
        
        $data['calendar_categories'] = array();
        
        $config_map = array(
            'project' => 'project_on_calendar',
            'milestone' => 'milestone_on_calendar', 
            'tasks' => 'tasks_on_calendar',
            'bugs' => 'bugs_on_calendar',
            'invoice' => 'invoice_on_calendar',
            'payments' => 'payments_on_calendar',
            'estimate' => 'estimate_on_calendar',
            'opportunities' => 'opportunities_on_calendar',
            'leads' => 'leads_on_calendar',
            'goal' => 'goal_tracking_on_calendar',
            'holiday' => 'holiday_on_calendar',
            'absent' => 'absent_on_calendar',
            'on_leave' => 'on_leave_on_calendar',
            'expenses' => 'expense_schedule_on_calendar',
            'domain' => 'domain_on_calendar',
            'hosting' => 'hosting_on_calendar',
            'billing' => 'billing_on_calendar'
        );
        
        foreach ($config_map as $key => $config) {
            if (config_item($config) == 'on') {
                $data['calendar_categories'][] = $key;
            }
        }
        
        if (!empty($action) && $action == 'search') {
            $data['searchType'] = $this->uri->segment(5);
        } else {
            $data['searchType'] = 'all';
        }

        $user_id = $this->session->userdata('user_id');
        $user_info = $this->admin_model->check_by(array('user_id' => $user_id), 'tbl_users');
        $data['role'] = $user_info->role_id;

        $data['subview'] = $this->load->view('admin/calendar', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    function invoice_totals_per_currency()
    {
        $invoices_info = $this->db->where('inv_deleted', 'No')->get('tbl_invoices')->result();
        $paid = $due = array();
        $currency = 'USD';
        $symbol = array();
        foreach ($invoices_info as $v_invoices) {
            if (!isset($paid[$v_invoices->currency])) {
                $paid[$v_invoices->currency] = 0;
            }
            if (!isset($due[$v_invoices->currency])) {
                $due[$v_invoices->currency] = 0;
            }
            $paid[$v_invoices->currency] += $this->invoice_model->get_invoice_paid_amount($v_invoices->invoices_id);
            $due[$v_invoices->currency] += $this->invoice_model->get_invoice_due_amount($v_invoices->invoices_id);
            $currency = $this->admin_model->check_by(array('code' => $v_invoices->currency), 'tbl_currencies');
            $symbol[$v_invoices->currency] = $currency->symbol;
        }
        return array("paid" => $paid, "due" => $due, "symbol" => $symbol);
    }


    public function calendar_settings()
    {
        $data['title'] = lang('calendar_settings');
        $data['modal_subview'] = $this->load->view('admin/settings/calendar_settings', $data, FALSE);
        $this->load->view('admin/_layout_modal', $data);
    }

    public function save_settings()
    {
        $input_data = $this->admin_model->array_from_post(array('gcal_api_key', 'gcal_id', 'project_on_calendar', 'milestone_on_calendar', 'tasks_on_calendar', 'bugs_on_calendar', 'invoice_on_calendar', 'payments_on_calendar', 'estimate_on_calendar', 'opportunities_on_calendar', 'leads_on_calendar', 'goal_tracking_on_calendar', 'holiday_on_calendar', 'absent_on_calendar', 'on_leave_on_calendar', 'expense_schedule_on_calendar',
            'project_color', 'milestone_color', 'tasks_color', 'bugs_color', 'invoice_color', 'payments_color', 'estimate_color', 'opportunities_color', 'leads_color', 'goal_tracking_color', 'holiday_color', 'absent_color', 'on_leave_color', 'expense_schedule_color',
            'domain_on_calendar', 'domain_color', 'hosting_on_calendar', 'hosting_color', 'upcoming_expiry_days'));

        foreach ($input_data as $key => $value) {
            $data = array('value' => $value);
            $this->db->where('config_key', $key)->update('tbl_config', $data);
            $exists = $this->db->where('config_key', $key)->get('tbl_config');
            if ($exists->num_rows() == 0) {
                $this->db->insert('tbl_config', array("config_key" => $key, "value" => $value));
            }
        }
        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'settings',
            'module_field_id' => $this->session->userdata('user_id'),
            'activity' => ('activity_save_settings'),
            'value1' => $input_data['gcal_api_key']
        );

        $this->admin_model->_table_name = 'tbl_activities';
        $this->admin_model->_primary_key = 'activities_id';
        $this->admin_model->save($activity);
        // messages for user
        $type = "success";
        $message = lang('save_settings');
        set_message($type, $message);
        if (empty($_SERVER['HTTP_REFERER'])) {
            redirect('admin/calendar');
        } else {
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    private function json_error($message)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('status' => 'error', 'message' => $message)));
    }

    public function save_event()
    {
        $fields = array('title', 'notes', 'start_date', 'end_date', 'start_time', 'end_time', 'category', 'related_id', 'all_day', 'notify', 'calendar_events_id');
        $post = array();
        foreach ($fields as $field) {
            $value = $this->input->post($field);
            if ($value !== NULL && !is_scalar($value)) {
                $this->json_error('Invalid input');
                return;
            }
            $post[$field] = $value;
        }

        $title = trim((string) $post['title']);
        $start_date = trim((string) $post['start_date']);
        $end_date = trim((string) $post['end_date']);
        $start_time = trim((string) $post['start_time']);
        $end_time = trim((string) $post['end_time']);
        $id = (int) $post['calendar_events_id'];

        if ($title === '' || $start_date === '') {
            $this->json_error('Title and a valid start date are required');
            return;
        }
        if (strlen($title) > 255) {
            $this->json_error('Title must be 255 characters or fewer');
            return;
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)
            || ($d = DateTime::createFromFormat('Y-m-d', $start_date)) === FALSE
            || $d->format('Y-m-d') !== $start_date) {
            $this->json_error('Invalid start date');
            return;
        }
        if ($end_date !== '') {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)
                || ($d = DateTime::createFromFormat('Y-m-d', $end_date)) === FALSE
                || $d->format('Y-m-d') !== $end_date) {
                $this->json_error('Invalid end date');
                return;
            }
            if ($end_date < $start_date) {
                $this->json_error('End date must be on or after the start date');
                return;
            }
        }
        if (strlen($start_time) > 10) {
            $this->json_error('Start time must be 10 characters or fewer');
            return;
        }
        if (strlen($end_time) > 10) {
            $this->json_error('End time must be 10 characters or fewer');
            return;
        }
        $end_date = $end_date !== '' ? $end_date : $start_date;

        $allowed_categories = array('project', 'milestone', 'tasks', 'bugs', 'invoice', 'payments', 'holiday', 'on_leave');
        $category = in_array($post['category'], $allowed_categories, true) ? $post['category'] : 'project';

        $row = array(
            'title'        => $title,
            'category'     => $category,
            'start_date'   => $start_date,
            'end_date'     => $end_date,
            'start_time'   => $start_time,
            'end_time'     => $end_time,
            'all_day'      => $post['all_day'] ? 1 : 0,
            'notify'       => $post['notify'] ? 1 : 0,
            'related_type' => $post['related_id'] ? 'project' : null,
            'related_id'   => $post['related_id'] ? (int) $post['related_id'] : null,
            'notes'        => (string) $post['notes'],
        );

        if ($id > 0) {
            $exists = $this->db->where('calendar_events_id', $id)->count_all_results('tbl_calendar_events') > 0;
            if (!$exists) {
                $this->json_error('Event not found');
                return;
            }
            if (!$this->db->where('calendar_events_id', $id)->update('tbl_calendar_events', $row)) {
                $this->json_error('Could not save event');
                return;
            }
            $message = 'Event updated';
            $activity_key = 'active_event_updated';
        } else {
            $row['created_by'] = (int) $this->session->userdata('user_id');
            $row['created_at'] = date('Y-m-d H:i:s');
            if (!$this->db->insert('tbl_calendar_events', $row)) {
                $this->json_error('Could not save event');
                return;
            }
            $id = (int) $this->db->insert_id();
            $message = 'Event created';
            $activity_key = 'active_event_saved';
        }

        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'calendar',
            'module_field_id' => $id,
            'activity' => $activity_key,
            'value1' => $title
        );

        $this->admin_model->_table_name = 'tbl_activities';
        $this->admin_model->_primary_key = 'activities_id';
        $this->admin_model->save($activity);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('status' => 'success', 'id' => $id, 'message' => $message)));
    }

    public function delete_event()
    {
        $id = (int) $this->input->post('calendar_events_id');
        if ($id <= 0) {
            $this->json_error('Missing event id');
            return;
        }
        $event = $this->db->where('calendar_events_id', $id)->get('tbl_calendar_events')->row();
        $this->db->where('calendar_events_id', $id)->delete('tbl_calendar_events');

        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'calendar',
            'module_field_id' => $id,
            'activity' => 'Event Deleted',
            'value1' => $event ? $event->title : null
        );

        $this->admin_model->_table_name = 'tbl_activities';
        $this->admin_model->_primary_key = 'activities_id';
        $this->admin_model->save($activity);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('status' => 'success', 'message' => 'Event deleted')));
    }

}
