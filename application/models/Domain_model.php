<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Domain_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function insert_domain($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('tbldomains', $data);
    }

    public function get_domains($limit, $start, $filters = array()) {
        $this->db->select('d.*, p.provider_name, h.hosting_name');
        $this->db->from('tbldomains d');
        $this->db->join('tblproviders p', 'd.provider_id = p.id', 'left');
        $this->db->join('tblhostings h', 'd.hosting_id = h.id', 'left');

        if (!empty($filters)) {
            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $this->db->where('d.purchase_date >=', $filters['start_date']);
                $this->db->where('d.purchase_date <=', $filters['end_date']);
            } elseif (!empty($filters['start_date'])) {
                $this->db->where('d.purchase_date >=', $filters['start_date']);
            } elseif (!empty($filters['end_date'])) {
                $this->db->where('d.purchase_date <=', $filters['end_date']);
            }

            if (!empty($filters['status']) && $filters['status'] !== 'All') {
                $this->db->where('d.status', $filters['status']);
            }

            if (!empty($filters['provider_id']) && $filters['provider_id'] !== 'All') {
                $this->db->where('d.provider_id', $filters['provider_id']);
            }

            if (!empty($filters['search'])) {
                $search = $this->db->escape_like_str($filters['search']);
                $this->db->group_start();
                $this->db->like('d.domain_name', $search);
                $this->db->or_like('p.provider_name', $search);
                $this->db->or_like('d.username', $search);
                $this->db->group_end();
            }
        }

        $this->db->order_by('d.id', 'DESC');
        $this->db->limit($limit, $start);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_domains_count($filters = array()) {
        $this->db->select('d.id');
        $this->db->from('tbldomains d');
        $this->db->join('tblproviders p', 'd.provider_id = p.id', 'left');

        if (!empty($filters)) {
            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $this->db->where('d.purchase_date >=', $filters['start_date']);
                $this->db->where('d.purchase_date <=', $filters['end_date']);
            } elseif (!empty($filters['start_date'])) {
                $this->db->where('d.purchase_date >=', $filters['start_date']);
            } elseif (!empty($filters['end_date'])) {
                $this->db->where('d.purchase_date <=', $filters['end_date']);
            }

            if (!empty($filters['status']) && $filters['status'] !== 'All') {
                $this->db->where('d.status', $filters['status']);
            }

            if (!empty($filters['provider_id']) && $filters['provider_id'] !== 'All') {
                $this->db->where('d.provider_id', $filters['provider_id']);
            }

            if (!empty($filters['search'])) {
                $search = $this->db->escape_like_str($filters['search']);
                $this->db->group_start();
                $this->db->like('d.domain_name', $search);
                $this->db->or_like('p.provider_name', $search);
                $this->db->or_like('d.username', $search);
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

    public function get_all_clients() {
        $this->db->select('client_id, name');
        $this->db->from('tbl_client');
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_all_projects() {
        $this->db->select('project_id, project_name');
        $this->db->from('tbl_project');
        $this->db->order_by('project_name', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_domain_by_id($id) {
        $this->db->where('id', $id);
        $query = $this->db->get('tbldomains');
        return $query->row();
    }

    public function update_domain($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('tbldomains', $data);
    }

    public function delete_domain($id) {
        if (is_array($id)) {
            $this->db->where_in('id', $id);
        } else {
            $this->db->where('id', $id);
        }
        return $this->db->delete('tbldomains');
    }

    public function get_all_hostings() {
        $this->db->select('id, hosting_name');
        $this->db->from('tblhostings');
        $this->db->where('status', 'Active');
        $this->db->order_by('hosting_name', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }
}