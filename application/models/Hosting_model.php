<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Hosting_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function insert_hosting($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('tblserver_hostings', $data);
    }

    public function get_hostings($limit, $start, $filters = array()) {
        $this->db->select('sh.*, p.provider_name');
        $this->db->from('tblserver_hostings sh');
        $this->db->join('tblproviders p', 'sh.provider_id = p.id', 'left');

        if (!empty($filters)) {
            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $this->db->where('sh.purchase_date >=', $filters['start_date']);
                $this->db->where('sh.purchase_date <=', $filters['end_date']);
            } elseif (!empty($filters['start_date'])) {
                $this->db->where('sh.purchase_date >=', $filters['start_date']);
            } elseif (!empty($filters['end_date'])) {
                $this->db->where('sh.purchase_date <=', $filters['end_date']);
            }

            if (!empty($filters['status']) && $filters['status'] !== 'All') {
                $this->db->where('sh.status', $filters['status']);
            }

            if (!empty($filters['provider_id']) && $filters['provider_id'] !== 'All') {
                $this->db->where('sh.provider_id', $filters['provider_id']);
            }

            if (!empty($filters['search'])) {
                $search = $this->db->escape_like_str($filters['search']);
                $this->db->group_start();
                $this->db->like('sh.title', $search);
                $this->db->or_like('p.provider_name', $search);
                $this->db->or_like('sh.ip_address', $search);
                $this->db->group_end();
            }
        }

        $this->db->order_by('sh.id', 'DESC');
        $this->db->limit($limit, $start);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_hostings_count($filters = array()) {
        $this->db->select('sh.id');
        $this->db->from('tblserver_hostings sh');
        $this->db->join('tblproviders p', 'sh.provider_id = p.id', 'left');

        if (!empty($filters)) {
            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $this->db->where('sh.purchase_date >=', $filters['start_date']);
                $this->db->where('sh.purchase_date <=', $filters['end_date']);
            } elseif (!empty($filters['start_date'])) {
                $this->db->where('sh.purchase_date >=', $filters['start_date']);
            } elseif (!empty($filters['end_date'])) {
                $this->db->where('sh.purchase_date <=', $filters['end_date']);
            }

            if (!empty($filters['status']) && $filters['status'] !== 'All') {
                $this->db->where('sh.status', $filters['status']);
            }

            if (!empty($filters['provider_id']) && $filters['provider_id'] !== 'All') {
                $this->db->where('sh.provider_id', $filters['provider_id']);
            }

            if (!empty($filters['search'])) {
                $search = $this->db->escape_like_str($filters['search']);
                $this->db->group_start();
                $this->db->like('sh.title', $search);
                $this->db->or_like('p.provider_name', $search);
                $this->db->or_like('sh.ip_address', $search);
                $this->db->group_end();
            }
        }

        return $this->db->count_all_results();
    }

    public function get_all_providers() {
        $this->db->select('id, provider_name');
        $this->db->from('tblproviders');
        $this->db->where('status', 'Active');
        $this->db->order_by('provider_name', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_provider_url($provider_id) {
        $this->db->select('provider_url');
        $this->db->from('tblproviders');
        $this->db->where('id', $provider_id);
        $query = $this->db->get();
        $row = $query->row();
        return $row ? $row->provider_url : '';
    }

    public function get_all_projects() {
        $this->db->select('project_id, project_name');
        $this->db->from('tbl_project');
        $this->db->order_by('project_name', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_all_clients() {
        $this->db->select('client_id, name');
        $this->db->from('tbl_client');
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_hosting_by_id($id) {
        $this->db->where('id', $id);
        $query = $this->db->get('tblserver_hostings');
        return $query->row();
    }

    public function get_stats() {
        $stats = [];
        $stats['total'] = $this->db->count_all('tblserver_hostings');
        
        $this->db->where('status', 'Active');
        $stats['active'] = $this->db->count_all_results('tblserver_hostings');
        
        $this->db->where('status', 'Pending');
        $stats['pending'] = $this->db->count_all_results('tblserver_hostings');
        
        $this->db->where('status', 'Suspended');
        $stats['suspended'] = $this->db->count_all_results('tblserver_hostings');
        
        $this->db->where('status', 'Cancelled');
        $stats['cancelled'] = $this->db->count_all_results('tblserver_hostings');
        
        $this->db->where('expiry_date >=', date('Y-m-d'));
        $this->db->where('expiry_date <=', date('Y-m-d', strtotime('+30 days')));
        $this->db->where('status', 'Active');
        $stats['expiring'] = $this->db->count_all_results('tblserver_hostings');
        
        $this->db->where('expiry_date <', date('Y-m-d'));
        $stats['expired'] = $this->db->count_all_results('tblserver_hostings');
        
        return $stats;
    }

    public function update_hosting($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('tblserver_hostings', $data);
    }

    public function delete_hosting($id) {
        if (is_array($id)) {
            $this->db->where_in('id', $id);
        } else {
            $this->db->where('id', $id);
        }
        return $this->db->delete('tblserver_hostings');
    }

    public function get_expired_hostings() {
        $today = date('Y-m-d');
        $this->db->reset_query();
        $this->db->select('id, title as name, expiry_date, status');
        $this->db->from('tblserver_hostings');
        $this->db->where("(status = 'Expired' OR status = 'Cancelled' OR expiry_date < '" . $today . "')", NULL, FALSE);
        $this->db->order_by('expiry_date', 'DESC');
        $query = $this->db->get();
        $hostings = $query->result_array();
        
        $today_timestamp = strtotime($today);
        foreach ($hostings as &$hosting) {
            $hosting['type'] = 'hosting';
            $days_expired = ($today_timestamp - strtotime($hosting['expiry_date'])) / (60 * 60 * 24);
            $hosting['days_expired'] = is_float($days_expired) ? ceil($days_expired) : intval($days_expired);
            $hosting['link'] = 'admin/server_management/add_hosting/' . $hosting['id'];
        }
        
        return $hostings;
    }
}
