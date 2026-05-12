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

        foreach ($expiring as $billing) {
            $events[] = array(
                'title' => '[BIL] ' . $billing['name'],
                'start' => $billing['expiry_date'],
                'end' => $billing['expiry_date'],
                'color' => '#8e44ad',
                'url' => base_url() . $billing['link'],
                'type' => 'billing',
                'status' => 'upcoming',
                'days_left' => $billing['days_left']
            );
        }

        foreach ($expired as $billing) {
            $events[] = array(
                'title' => '[BIL] ' . $billing['name'],
                'start' => date('Y-m-d'),
                'end' => date('Y-m-d'),
                'color' => '#c0392b',
                'url' => base_url() . $billing['link'],
                'type' => 'billing',
                'status' => 'expired',
                'days_expired' => $billing['days_expired']
            );
        }

        return $events;
    }
}
