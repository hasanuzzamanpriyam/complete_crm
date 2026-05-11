<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Hosting_model extends MY_Model
{
    public $_table_name = 'tblserver_hostings';
    public $_primary_key = 'id';
    public $_order_by = 'id DESC';


    public function __construct()
    {
        parent::__construct();

        // Automatically update expired and expiring hostings
        $today = date('Y-m-d');
        $expiring_soon = date('Y-m-d', strtotime('+30 days'));

        // 1. Mark as Expired if date passed
        $this->db->where('expiry_date <', $today);
        $this->db->where('status !=', 'Expired');
        $this->db->update('tblserver_hostings', array('status' => 'Expired'));

        // 2. Mark as Expiring if within 30 days and currently Active
        $this->db->where('expiry_date >=', $today);
        $this->db->where('expiry_date <=', $expiring_soon);
        $this->db->where('status', 'Active');
        $this->db->update('tblserver_hostings', array('status' => 'Expiring'));

        // 3. Mark as Active if not expired/expiring but marked otherwise
        $this->db->where('expiry_date >', $expiring_soon);
        $this->db->where('status', 'Expiring');
        $this->db->update('tblserver_hostings', array('status' => 'Active'));

        // 4. Auto-complete renewal tasks for auto-renewal hostings
        $this->db->select('tbl_task.task_id, tbl_task.module_field_id, tblserver_hostings.title');
        $this->db->from('tbl_task');
        $this->db->join('tblserver_hostings', 'tbl_task.module_field_id = tblserver_hostings.id');
        $this->db->where('tbl_task.module', 'server_hosting');
        $this->db->where('tbl_task.task_status !=', 'completed');
        $this->db->where('tbl_task.due_date <=', $today);
        $this->db->where('tblserver_hostings.renew', 'automatic');
        $query = $this->db->get();
        if ($query) {
            $tasks_to_complete = $query->result();
            foreach ($tasks_to_complete as $task) {
                $this->db->where('task_id', $task->task_id);
                $this->db->update('tbl_task', array('task_status' => 'completed'));

                $notify_data = array(
                    'description' => 'task_updated',
                    'icon' => 'fa-check-circle',
                    'link' => 'admin/tasks/view_details/' . $task->task_id,
                    'value' => 'Renewal: ' . $task->title
                );
                add_notification($notify_data);
            }
        }
    }

    public function insert_hosting($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        if ($this->db->insert('tblserver_hostings', $data)) {
            return $this->db->insert_id();
        }

        return false;
    }

    public function get_hostings($limit, $start, $filters = array())
    {
        $this->db->select('sh.*, p.provider_name');
        $this->db->from('tblserver_hostings sh');
        $this->db->join('tblproviders p', 'sh.provider_id = p.id', 'left');
        $this->staff_query('tblserver_hostings');

        if (!empty($filters)) {
            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $this->db->where('sh.expiry_date >=', $filters['start_date']);
                $this->db->where('sh.expiry_date <=', $filters['end_date']);
            } elseif (!empty($filters['start_date'])) {
                $this->db->where('sh.expiry_date >=', $filters['start_date']);
            } elseif (!empty($filters['end_date'])) {
                $this->db->where('sh.expiry_date <=', $filters['end_date']);
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

        $this->db->order_by('sh.title', 'ASC');
        $this->db->limit($limit, $start);
        $query = $this->db->get();
        $hostings = $query->result_array();
        $today = time();
        foreach ($hostings as &$hosting) {
            $expiry = strtotime($hosting['expiry_date']);
            $hosting['days_remaining'] = ceil(($expiry - $today) / 86400);
        }
        return $hostings;
    }

    public function get_hostings_count($filters = array())
    {
        $this->db->select('sh.id');
        $this->db->from('tblserver_hostings sh');
        $this->db->join('tblproviders p', 'sh.provider_id = p.id', 'left');
        $this->staff_query('tblserver_hostings');

        if (!empty($filters)) {
            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $this->db->where('sh.expiry_date >=', $filters['start_date']);
                $this->db->where('sh.expiry_date <=', $filters['end_date']);
            } elseif (!empty($filters['start_date'])) {
                $this->db->where('sh.expiry_date >=', $filters['start_date']);
            } elseif (!empty($filters['end_date'])) {
                $this->db->where('sh.expiry_date <=', $filters['end_date']);
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

    public function get_all_providers()
    {
        $this->db->select('id, provider_name');
        $this->db->from('tblproviders');
        $this->db->where('status', 'Active');
        $this->db->order_by('provider_name', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_provider_url($provider_id)
    {
        $this->db->select('provider_url');
        $this->db->from('tblproviders');
        $this->db->where('id', $provider_id);
        $query = $this->db->get();
        $row = $query->row();
        return $row ? $row->provider_url : '';
    }

    public function get_all_projects()
    {
        $this->db->select('project_id, project_name');
        $this->db->from('tbl_project');
        $this->db->order_by('project_name', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_all_clients()
    {
        $this->db->select('client_id, name');
        $this->db->from('tbl_client');
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_hosting_by_id($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get('tblserver_hostings');
        return $query->row();
    }

    public function get_hosting_info($id)
    {
        $this->db->select('sh.*, p.provider_name as provider, c.symbol as currency_symbol');
        $this->db->from('tblserver_hostings sh');
        $this->db->join('tblproviders p', 'sh.provider_id = p.id', 'left');
        $this->db->join('tbl_currencies c', 'sh.currency_id = c.code', 'left');
        $this->db->where('sh.id', $id);
        $query = $this->db->get();
        if ($query) {
            $row = $query->row();
            if ($row) {
                // Fetch projects
                $row->projects_names = '';
                if (!empty($row->project_id)) {
                    $project_ids = explode(',', $row->project_id);
                    $this->db->select('project_name');
                    $this->db->where_in('project_id', $project_ids);
                    $projects = $this->db->get('tbl_project')->result_array();
                    if (!empty($projects)) {
                        $row->projects_names = implode(', ', array_column($projects, 'project_name'));
                    }
                }

                // Fetch clients
                $row->clients_names = '';
                if (!empty($row->client_id)) {
                    $client_ids = explode(',', $row->client_id);
                    $this->db->select('name');
                    $this->db->where_in('client_id', $client_ids);
                    $clients = $this->db->get('tbl_client')->result_array();
                    if (!empty($clients)) {
                        $row->clients_names = implode(', ', array_column($clients, 'name'));
                    }
                }
            }
            return $row;
        }
        return NULL;
    }

    public function get_stats()
    {
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

    public function update_hosting($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('tblserver_hostings', $data);
    }

    public function delete_hosting($id)
    {
        if (is_array($id)) {
            $this->db->where_in('id', $id);
        } else {
            $this->db->where('id', $id);
        }
        return $this->db->delete('tblserver_hostings');
    }

    public function get_expired_hostings()
    {
        $today = date('Y-m-d');
        $this->db->reset_query();
        $this->db->select('id, title as name, expiry_date, status, renew');
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
            $hosting['link'] = 'admin/server_management/view_hosting/' . $hosting['id'];
        }

        return $hostings;
    }

    public function get_expiring_hostings($days = 7)
    {
        $today = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime("+{$days} days"));

        $this->db->reset_query();
        $this->db->select('id, title as name, expiry_date, status, renew');
        $this->db->from('tblserver_hostings');
        $this->db->where('expiry_date >=', $today);
        $this->db->where('expiry_date <=', $end_date);
        $this->db->where('status', 'Active');
        $this->db->order_by('expiry_date', 'ASC');
        $query = $this->db->get();
        $hostings = $query->result_array();

        $today_timestamp = strtotime($today);
        foreach ($hostings as &$hosting) {
            $hosting['type'] = 'hosting';
            $days_left = (strtotime($hosting['expiry_date']) - $today_timestamp) / (60 * 60 * 24);
            $hosting['days_left'] = is_float($days_left) ? ceil($days_left) : intval($days_left);
            $hosting['link'] = 'admin/server_management/view_hosting/' . $hosting['id'];
        }

        return $hostings;
    }

    public function get_calendar_events()
    {
        $events = array();
        $upcoming_days = config_item('upcoming_expiry_days') ? config_item('upcoming_expiry_days') : 7;

        $expiring = $this->get_expiring_hostings($upcoming_days);
        $expired = $this->get_expired_hostings();

        foreach ($expiring as $hosting) {
            $renew_type = (isset($hosting['renew']) && $hosting['renew'] == 'automatic') ? ' (Auto)' : ' (Manual)';
            $events[] = array(
                'title' => '[HST] ' . $hosting['name'] . $renew_type,
                'start' => $hosting['expiry_date'],
                'end' => $hosting['expiry_date'],
                'color' => config_item('hosting_color') ?: '#ffd93d',
                'url' => base_url() . $hosting['link'],
                'type' => 'hosting',
                'status' => 'upcoming',
                'days_left' => $hosting['days_left']
            );
        }

        foreach ($expired as $hosting) {
            $renew_type = (isset($hosting['renew']) && $hosting['renew'] == 'automatic') ? ' (Auto)' : ' (Manual)';
            $events[] = array(
                'title' => '[HST] ' . $hosting['name'] . $renew_type,
                'start' => date('Y-m-d'),
                'end' => date('Y-m-d'),
                'color' => '#ff6b6b',
                'url' => base_url() . $hosting['link'],
                'type' => 'hosting',
                'status' => 'expired',
                'days_expired' => $hosting['days_expired']
            );
        }

        return $events;
    }

    public function get_all_hostings_for_notification()
    {
        $this->db->select('id, title, expiry_date, status');
        $this->db->from('tblserver_hostings');
        $this->db->where('status !=', 'Expired');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_distinct_dns_providers()
    {
        $this->db->select('DISTINCT(dns_provider_name) as provider_name');
        $this->db->from('tblserver_hostings');
        $this->db->where('dns_provider_name IS NOT NULL');
        $this->db->where('dns_provider_name !=', '');
        $this->db->order_by('dns_provider_name', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }
    public function get_all_active_hostings()
    {
        $this->db->select('id, title as hosting_name');
        $this->db->from('tblserver_hostings');
        $this->db->where('status', 'Active');
        $this->db->order_by('title', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }
}
