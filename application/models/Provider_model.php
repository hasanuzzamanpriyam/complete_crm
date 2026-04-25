<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Provider_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function insert_provider($data) {
        return $this->db->insert('tblproviders', $data);
    }

    public function get_providers($limit, $start, $filters = array()) {
        $this->db->select('*');
        $this->db->from('tblproviders');

        if (!empty($filters)) {
            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $this->db->where('created_at >=', $filters['start_date'] . ' 00:00:00');
                $this->db->where('created_at <=', $filters['end_date'] . ' 23:59:59');
            } elseif (!empty($filters['start_date'])) {
                $this->db->where('created_at >=', $filters['start_date'] . ' 00:00:00');
            } elseif (!empty($filters['end_date'])) {
                $this->db->where('created_at <=', $filters['end_date'] . ' 23:59:59');
            }

            if (!empty($filters['status']) && $filters['status'] !== 'All') {
                $this->db->where('status', $filters['status']);
            }

            if (!empty($filters['provider_type']) && $filters['provider_type'] !== 'All') {
                $this->db->where('provider_type', $filters['provider_type']);
            }

            if (!empty($filters['search'])) {
                $search = $this->db->escape_like_str($filters['search']);
                $this->db->group_start();
                $this->db->like('provider_name', $search);
                $this->db->or_like('provider_url', $search);
                $this->db->group_end();
            }
        }

        $this->db->order_by('id', 'DESC');
        $this->db->limit($limit, $start);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_providers_count($filters = array()) {
        $this->db->select('*');
        $this->db->from('tblproviders');

        if (!empty($filters)) {
            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $this->db->where('created_at >=', $filters['start_date'] . ' 00:00:00');
                $this->db->where('created_at <=', $filters['end_date'] . ' 23:59:59');
            } elseif (!empty($filters['start_date'])) {
                $this->db->where('created_at >=', $filters['start_date'] . ' 00:00:00');
            } elseif (!empty($filters['end_date'])) {
                $this->db->where('created_at <=', $filters['end_date'] . ' 23:59:59');
            }

            if (!empty($filters['status']) && $filters['status'] !== 'All') {
                $this->db->where('status', $filters['status']);
            }

            if (!empty($filters['provider_type']) && $filters['provider_type'] !== 'All') {
                $this->db->where('provider_type', $filters['provider_type']);
            }

            if (!empty($filters['search'])) {
                $search = $this->db->escape_like_str($filters['search']);
                $this->db->group_start();
                $this->db->like('provider_name', $search);
                $this->db->or_like('provider_url', $search);
                $this->db->group_end();
            }
        }

        return $this->db->count_all_results();
    }

    public function get_provider_by_id($id) {
        $this->db->where('id', $id);
        $query = $this->db->get('tblproviders');
        return $query->row();
    }

    public function update_provider($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('tblproviders', $data);
    }

    public function delete_provider($id) {
        if (is_array($id)) {
            $this->db->where_in('id', $id);
        } else {
            $this->db->where('id', $id);
        }
        return $this->db->delete('tblproviders');
    }

    public function get_stats() {
        $stats = [];
        $stats['total'] = $this->db->count_all('tblproviders');
        
        $this->db->where('status', 'Active');
        $stats['active'] = $this->db->count_all_results('tblproviders');
        
        $this->db->where('status', 'Inactive');
        $stats['inactive'] = $this->db->count_all_results('tblproviders');
        
        return $stats;
    }
}