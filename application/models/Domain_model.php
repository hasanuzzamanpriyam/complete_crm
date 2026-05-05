<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Domain_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        
        // Automatically update expired and expiring domains
        $today = date('Y-m-d');
        $expiring_soon = date('Y-m-d', strtotime('+30 days'));

        // 1. Mark as Expired if date passed
        $this->db->where('expiry_date <', $today);
        $this->db->where('status !=', 'Expired');
        $this->db->update('tbldomains', array('status' => 'Expired'));

        // 2. Mark as Expiring if within 30 days and currently Active
        $this->db->where('expiry_date >=', $today);
        $this->db->where('expiry_date <=', $expiring_soon);
        $this->db->where('status', 'Active');
        $this->db->update('tbldomains', array('status' => 'Expiring'));

        // 3. Mark as Active if not expired/expiring but marked otherwise (optional sync back)
        $this->db->where('expiry_date >', $expiring_soon);
        $this->db->where('status', 'Expiring');
        $this->db->update('tbldomains', array('status' => 'Active'));
    }

    public function insert_domain($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->insert('tbldomains', $data);
        return $this->db->insert_id();
    }

    public function get_domains($limit, $start, $filters = array()) {
        $this->db->select('d.*, p.provider_name, h.hosting_name');
        $this->db->from('tbldomains d');
        $this->db->join('tblproviders p', 'd.provider_id = p.id', 'left');
        $this->db->join('tblhostings h', 'd.hosting_id = h.id', 'left');

        if (!empty($filters)) {
            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $this->db->where('d.expiry_date >=', $filters['start_date']);
                $this->db->where('d.expiry_date <=', $filters['end_date']);
            } elseif (!empty($filters['start_date'])) {
                $this->db->where('d.expiry_date >=', $filters['start_date']);
            } elseif (!empty($filters['end_date'])) {
                $this->db->where('d.expiry_date <=', $filters['end_date']);
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
        $domains = $query->result_array();
        $today = time();
        foreach ($domains as &$domain) {
            $expiry = strtotime($domain['expiry_date']);
            $domain['days_remaining'] = ceil(($expiry - $today) / 86400);
        }
        return $domains;
    }

    public function get_domains_count($filters = array()) {
        $this->db->select('d.id');
        $this->db->from('tbldomains d');
        $this->db->join('tblproviders p', 'd.provider_id = p.id', 'left');

        if (!empty($filters)) {
            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $this->db->where('d.expiry_date >=', $filters['start_date']);
                $this->db->where('d.expiry_date <=', $filters['end_date']);
            } elseif (!empty($filters['start_date'])) {
                $this->db->where('d.expiry_date >=', $filters['start_date']);
            } elseif (!empty($filters['end_date'])) {
                $this->db->where('d.expiry_date <=', $filters['end_date']);
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

    public function get_domain_info($id) {
        $this->db->select('d.*, p.provider_name as provider, h.hosting_name as hosting');
        $this->db->from('tbldomains d');
        $this->db->join('tblproviders p', 'd.provider_id = p.id', 'left');
        $this->db->join('tblhostings h', 'd.hosting_id = h.id', 'left');
        $this->db->where('d.id', $id);
        $query = $this->db->get();
        if ($query) {
            $row = $query->row();
            if ($row) {
                // Fetch projects
                $row->project = '';
                if (!empty($row->project_id)) {
                    $project_ids = explode(',', $row->project_id);
                    $this->db->select('project_name');
                    $this->db->where_in('project_id', $project_ids);
                    $projects = $this->db->get('tbl_project')->result_array();
                    if (!empty($projects)) {
                        $row->project = implode(', ', array_column($projects, 'project_name'));
                    }
                }
                
                // Fetch clients
                $row->client_name = '';
                if (!empty($row->client_id)) {
                    $client_ids = explode(',', $row->client_id);
                    $this->db->select('name');
                    $this->db->where_in('client_id', $client_ids);
                    $clients = $this->db->get('tbl_client')->result_array();
                    if (!empty($clients)) {
                        $row->client_name = implode(', ', array_column($clients, 'name'));
                    }
                }
            }
            return $row;
        }
        return NULL;
    }

    public function update_domain($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update('tbldomains', $data);
    }

    public function delete_domain($id) {
        return $this->db->where('id', $id)->delete('tbldomains');
    }

    public function get_all_domains() {
        return $this->db->get('tbldomains')->result_array();
    }

    public function get_stats() {
        $stats = [];
        $stats['total'] = $this->db->count_all('tbldomains');
        
        $this->db->where('status', 'Active');
        $stats['active'] = $this->db->count_all_results('tbldomains');
        
        $this->db->where('status', 'Pending');
        $stats['pending'] = $this->db->count_all_results('tbldomains');
        
        $this->db->where('status', 'Expired');
        $stats['expired'] = $this->db->count_all_results('tbldomains');
        
        $this->db->where('expiry_date <', date('Y-m-d'));
        $this->db->where('status !=', 'Expired');
        $stats['expired_auto'] = $this->db->count_all_results('tbldomains');
        
        $stats['expired'] = ($stats['expired'] ?? 0) + ($stats['expired_auto'] ?? 0);
        
        $this->db->where('expiry_date >=', date('Y-m-d'));
        $this->db->where('expiry_date <=', date('Y-m-d', strtotime('+30 days')));
        $this->db->where('status', 'Active');
        $stats['expiring'] = $this->db->count_all_results('tbldomains');
        
        return $stats;
    }

    public function get_all_hostings() {
        $this->db->select('id, hosting_name');
        $this->db->from('tblhostings');
        $this->db->order_by('hosting_name', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function insert_hosting_type($data) {
        return $this->db->insert('tblhostings', $data);
    }

    public function get_expired_domains() {
        $today = date('Y-m-d');
        $this->db->reset_query();
        $this->db->select('id, domain_name as name, expiry_date, status, auto_renewal');
        $this->db->from('tbldomains');
        $this->db->where("(status = 'Expired' OR expiry_date < '" . $today . "')", NULL, FALSE);
        $this->db->order_by('expiry_date', 'DESC');
        $query = $this->db->get();
        $domains = $query->result_array();
        
        $today_timestamp = strtotime($today);
        foreach ($domains as &$domain) {
            $domain['type'] = 'domain';
            $days_expired = ($today_timestamp - strtotime($domain['expiry_date'])) / (60 * 60 * 24);
            $domain['days_expired'] = is_float($days_expired) ? ceil($days_expired) : intval($days_expired);
            $domain['link'] = 'admin/server_management/view_domain/' . $domain['id'];
        }
        
        return $domains;
    }

    public function get_expiring_domains($days = 7) {
        $today = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime("+{$days} days"));
        
        $this->db->reset_query();
        $this->db->select('id, domain_name as name, expiry_date, status, auto_renewal');
        $this->db->from('tbldomains');
        $this->db->where('expiry_date >=', $today);
        $this->db->where('expiry_date <=', $end_date);
        $this->db->where('status', 'Active');
        $this->db->order_by('expiry_date', 'ASC');
        $query = $this->db->get();
        $domains = $query->result_array();
        
        $today_timestamp = strtotime($today);
        foreach ($domains as &$domain) {
            $domain['type'] = 'domain';
            $days_left = (strtotime($domain['expiry_date']) - $today_timestamp) / (60 * 60 * 24);
            $domain['days_left'] = is_float($days_left) ? ceil($days_left) : intval($days_left);
            $domain['link'] = 'admin/server_management/view_domain/' . $domain['id'];
        }
        
        return $domains;
    }

    public function get_calendar_events() {
        $events = array();
        $upcoming_days = config_item('upcoming_expiry_days') ? config_item('upcoming_expiry_days') : 7;
        
        $expiring = $this->get_expiring_domains($upcoming_days);
        $expired = $this->get_expired_domains();
        
        foreach ($expiring as $domain) {
            $renew_type = ($domain['auto_renewal'] == 1) ? ' (Auto)' : ' (Manual)';
            $events[] = array(
                'title' => '[DOM] ' . $domain['name'] . $renew_type,
                'start' => $domain['expiry_date'],
                'end' => $domain['expiry_date'],
                'color' => config_item('domain_color') ?: '#ffd93d',
                'url' => base_url() . $domain['link'],
                'type' => 'domain',
                'status' => 'upcoming',
                'days_left' => $domain['days_left']
            );
        }
        
        foreach ($expired as $domain) {
            $renew_type = ($domain['auto_renewal'] == 1) ? ' (Auto)' : ' (Manual)';
            $events[] = array(
                'title' => '[DOM] ' . $domain['name'] . $renew_type,
                'start' => date('Y-m-d'),
                'end' => date('Y-m-d'),
                'color' => '#ff6b6b',
                'url' => base_url() . $domain['link'],
                'type' => 'domain',
                'status' => 'expired',
                'days_expired' => $domain['days_expired']
            );
        }
        
        return $events;
    }

    public function get_all_domains_for_notification() {
        $this->db->select('id, domain_name, expiry_date, status');
        $this->db->from('tbldomains');
        $this->db->where('status !=', 'Expired');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_domain_types() {
        return $this->db->get('tbl_domain_types')->result_array();
    }

    public function get_domain_statuses() {
        return $this->db->get('tbl_domain_status')->result_array();
    }

    public function insert_domain_status($data) {
        return $this->db->insert('tbl_domain_status', $data);
    }
}