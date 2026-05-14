<?php

class Billing_model extends MY_Model
{
    public $_table_name = 'tbl_billing_orders';
    public $_primary_key = 'id';
    public $_order_by = 'id DESC';

    public function get_all_billing()
    {
        $this->db->select('tbl_billing_orders.id, tbl_billing_orders.label, tbl_billing_orders.type, tbl_billing_orders.value, tbl_billing_orders.currency, tbl_billing_orders.renewal_date, tbl_billing_orders.expiry_date, tbl_billing_orders.renew, tbl_billing_orders.status, tbl_billing_orders.permission, tblproviders.provider_name, tbl_client.name as client_name');
        $this->db->from('tbl_billing_orders');
        $this->db->join('tblproviders', 'tbl_billing_orders.provider_id = tblproviders.id', 'left');
        $this->db->join('tbl_client', 'tbl_billing_orders.client_id = tbl_client.client_id', 'left');
        $this->staff_query('tbl_billing_orders');
        $this->db->order_by('tbl_billing_orders.id', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    public function get_billings($limit, $start, $filters = array())
    {
        $this->db->select('b.*, p.provider_name, c.name as client_name');
        $this->db->from('tbl_billing_orders b');
        $this->db->join('tblproviders p', 'b.provider_id = p.id', 'left');
        $this->db->join('tbl_client c', 'b.client_id = c.client_id', 'left');
        $this->staff_query('tbl_billing_orders');

        if (!empty($filters)) {
            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $this->db->where('b.expiry_date >=', $filters['start_date']);
                $this->db->where('b.expiry_date <=', $filters['end_date']);
            } elseif (!empty($filters['start_date'])) {
                $this->db->where('b.expiry_date >=', $filters['start_date']);
            } elseif (!empty($filters['end_date'])) {
                $this->db->where('b.expiry_date <=', $filters['end_date']);
            }

            if (!empty($filters['status']) && $filters['status'] !== 'All') {
                $this->db->where('b.status', $filters['status']);
            }

            if (!empty($filters['provider_id']) && $filters['provider_id'] !== 'All') {
                $this->db->where('b.provider_id', $filters['provider_id']);
            }

            if (!empty($filters['search'])) {
                $search = $this->db->escape_like_str($filters['search']);
                $this->db->group_start();
                $this->db->like('b.label', $search);
                $this->db->or_like('p.provider_name', $search);
                $this->db->or_like('c.name', $search);
                $this->db->group_end();
            }
        }

        $this->db->order_by('b.id', 'DESC');
        $this->db->limit($limit, $start);
        $query = $this->db->get();
        return $query->result();
    }

    public function get_billings_count($filters = array())
    {
        $this->db->from('tbl_billing_orders b');
        $this->db->join('tblproviders p', 'b.provider_id = p.id', 'left');
        $this->db->join('tbl_client c', 'b.client_id = c.client_id', 'left');
        $this->staff_query('tbl_billing_orders');

        if (!empty($filters)) {
            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $this->db->where('b.expiry_date >=', $filters['start_date']);
                $this->db->where('b.expiry_date <=', $filters['end_date']);
            } elseif (!empty($filters['start_date'])) {
                $this->db->where('b.expiry_date >=', $filters['start_date']);
            } elseif (!empty($filters['end_date'])) {
                $this->db->where('b.expiry_date <=', $filters['end_date']);
            }

            if (!empty($filters['status']) && $filters['status'] !== 'All') {
                $this->db->where('b.status', $filters['status']);
            }

            if (!empty($filters['provider_id']) && $filters['provider_id'] !== 'All') {
                $this->db->where('b.provider_id', $filters['provider_id']);
            }

            if (!empty($filters['search'])) {
                $search = $this->db->escape_like_str($filters['search']);
                $this->db->group_start();
                $this->db->like('b.label', $search);
                $this->db->or_like('p.provider_name', $search);
                $this->db->or_like('c.name', $search);
                $this->db->group_end();
            }
        }

        return $this->db->count_all_results();
    }

    public function get_all_providers()
    {
        $this->db->select('id, provider_name');
        $this->db->from('tblproviders');
        $this->db->order_by('provider_name', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_all_statuses()
    {
        $this->db->select('DISTINCT(status) as status_name');
        $this->db->from('tbl_billing_orders');
        $this->db->where('status !=', '');
        return $this->db->get()->result_array();
    }

    public function get_billing_by_id($id)
    {
        return $this->get($id, TRUE);
    }

    public function get_billing_info($id)
    {
        $this->db->select('tbl_billing_orders.*, tblproviders.provider_name as provider, tbl_client.name as client_name, tbl_project.project_name, tbl_users.username as contact_person');
        $this->db->from('tbl_billing_orders');
        $this->db->join('tblproviders', 'tbl_billing_orders.provider_id = tblproviders.id', 'left');
        $this->db->join('tbl_client', 'tbl_billing_orders.client_id = tbl_client.client_id', 'left');
        $this->db->join('tbl_project', 'tbl_billing_orders.project_id = tbl_project.project_id', 'left');
        $this->db->join('tbl_users', 'tbl_billing_orders.contact_id = tbl_users.user_id', 'left');
        $this->db->where('tbl_billing_orders.id', $id);
        return $this->db->get()->row();
    }

    public function save_billing($data, $id = NULL)
    {
        return $this->save($data, $id);
    }

    public function get_stats()
    {
        $stats = [];
        $this->staff_query('tbl_billing_orders');
        $stats['total'] = $this->db->count_all_results('tbl_billing_orders');

        $this->db->where('status', 'Active');
        $this->staff_query('tbl_billing_orders');
        $stats['active'] = $this->db->count_all_results('tbl_billing_orders');

        $this->db->where('status', 'Pending');
        $this->staff_query('tbl_billing_orders');
        $stats['pending'] = $this->db->count_all_results('tbl_billing_orders');

        $this->db->where('status', 'Expired');
        $this->staff_query('tbl_billing_orders');
        $stats['expired'] = $this->db->count_all_results('tbl_billing_orders');

        $this->db->where('expiry_date <', date('Y-m-d'));
        $this->db->where('status !=', 'Expired');
        $this->staff_query('tbl_billing_orders');
        $stats['expired_auto'] = $this->db->count_all_results('tbl_billing_orders');

        $stats['expired'] = ($stats['expired'] ?? 0) + ($stats['expired_auto'] ?? 0);

        $this->db->where('expiry_date >=', date('Y-m-d'));
        $this->db->where('expiry_date <=', date('Y-m-d', strtotime('+30 days')));
        $this->db->where('status', 'Active');
        $this->staff_query('tbl_billing_orders');
        $stats['expiring'] = $this->db->count_all_results('tbl_billing_orders');

        return $stats;
    }

    public function get_expired_billing()
    {
        $today = date('Y-m-d');
        $this->db->select('id, label as name, expiry_date, status');
        $this->db->from('tbl_billing_orders');
        $this->db->where("(status = 'Expired' OR expiry_date < '" . $today . "')", NULL, FALSE);
        $this->staff_query('tbl_billing_orders');
        $this->db->order_by('expiry_date', 'DESC');
        $query = $this->db->get();
        $billings = $query->result_array();

        $today_timestamp = strtotime($today);
        foreach ($billings as &$billing) {
            $billing['type'] = 'billing';
            $days_expired = ($today_timestamp - strtotime($billing['expiry_date'])) / (60 * 60 * 24);
            $billing['days_expired'] = is_float($days_expired) ? ceil($days_expired) : intval($days_expired);
            $billing['link'] = 'admin/server_management/view_billing/' . $billing['id'];
        }

        return $billings;
    }

    public function get_expiring_billing($days = 7)
    {
        $today = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime("+{$days} days"));

        $this->db->select('id, label as name, expiry_date, status');
        $this->db->from('tbl_billing_orders');
        $this->db->where('expiry_date >=', $today);
        $this->db->where('expiry_date <=', $end_date);
        $this->db->where('status', 'Active');
        $this->staff_query('tbl_billing_orders');
        $this->db->order_by('expiry_date', 'ASC');
        $query = $this->db->get();
        $billings = $query->result_array();

        $today_timestamp = strtotime($today);
        foreach ($billings as &$billing) {
            $billing['type'] = 'billing';
            $days_left = (strtotime($billing['expiry_date']) - $today_timestamp) / (60 * 60 * 24);
            $billing['days_left'] = is_float($days_left) ? ceil($days_left) : intval($days_left);
            $billing['link'] = 'admin/server_management/view_billing/' . $billing['id'];
        }

        return $billings;
    }

    public function get_calendar_events()
    {
        $events = array();
        $upcoming_days = config_item('upcoming_expiry_days') ? config_item('upcoming_expiry_days') : 7;

        $expiring = $this->get_expiring_billing($upcoming_days);
        $expired = $this->get_expired_billing();

        $this->load->model('tasks_model');
        foreach ($expiring as $billing) {
            $task_id = $this->tasks_model->get_or_create_renewal_task('billing', $billing['id']);
            $events[] = array(
                'title' => '[BIL] ' . $billing['name'],
                'start' => $billing['expiry_date'],
                'end' => $billing['expiry_date'],
                'color' => '#8e44ad',
                'url' => $task_id ? base_url() . 'admin/tasks/details/' . $task_id : base_url() . $billing['link'],
                'type' => 'billing',
                'status' => 'upcoming',
                'days_left' => $billing['days_left']
            );
        }

        foreach ($expired as $billing) {
            $task_id = $this->tasks_model->get_or_create_renewal_task('billing', $billing['id']);
            $events[] = array(
                'title' => '[BIL] ' . $billing['name'],
                'start' => date('Y-m-d'),
                'end' => date('Y-m-d'),
                'color' => '#c0392b',
                'url' => $task_id ? base_url() . 'admin/tasks/details/' . $task_id : base_url() . $billing['link'],
                'type' => 'billing',
                'status' => 'expired',
                'days_expired' => $billing['days_expired']
            );
        }

        return $events;
    }
}
