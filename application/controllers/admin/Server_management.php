<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Server_management extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('provider_model');
        $this->load->model('domain_model');
        $this->load->model('hosting_model');
        
        $method = $this->router->fetch_method();
        if ($method == 'domain' || $method == 'add_domain') {
            $this->session->set_userdata('menu_active_id', array('111122222223', '1111222222233'));
        } elseif ($method == 'hosting' || $method == 'add_hosting') {
            $this->session->set_userdata('menu_active_id', array('111122222223', '1111222222232'));
        } elseif ($method == 'provider' || $method == 'add_provider') {
            $this->session->set_userdata('menu_active_id', array('111122222223', '1111222222234'));
        } else {
            $this->session->set_userdata('menu_active_id', array('111122222223', '1111222222231'));
        }
    }

    public function index()
    {
        $this->dashboard();
    }

    public function dashboard()
    {
        $data['title'] = lang('server_dashboard');

        $domain_stats = $this->domain_model->get_stats();
        $hosting_stats = $this->hosting_model->get_stats();
        $provider_stats = $this->provider_model->get_stats();

        $data['stats'] = [
            'total_hostings' => $hosting_stats['total'],
            'active_hostings' => $hosting_stats['active'],
            'pending_hostings' => $hosting_stats['pending'],
            'suspended_hostings' => $hosting_stats['suspended'],
            'expired_hostings' => $hosting_stats['expired'],
            'total_domains' => $domain_stats['total'],
            'active_domains' => $domain_stats['active'],
            'pending_domains' => $domain_stats['pending'],
            'expiring_hostings' => $hosting_stats['expiring'],
            'expiring_domains' => $domain_stats['expiring'],
            'expired_domains' => $domain_stats['expired'],
            'total_providers' => $provider_stats['total'],
            'active_providers' => $provider_stats['active'],
            'inactive_providers' => $provider_stats['inactive'],
            'running_count' => $hosting_stats['active'] + $domain_stats['active']
        ];

        $data['recent_activities'] = $this->get_recent_activities();
        $data['expiring_items'] = $this->get_expiring_items();
        $data['expired_items'] = $this->get_expired_items();
        $data['inactive_providers'] = $this->get_inactive_providers();
        $data['running_items'] = $this->get_running_items();

        $data['subview'] = $this->load->view('admin/server_management/dashboard', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    private function get_recent_activities()
    {
        $this->db->select('a.*, u.username');
        $this->db->from('tbl_activities a');
        $this->db->join('tbl_users u', 'a.user = u.user_id', 'left');
        $this->db->where('a.module', 'server_management');
        $this->db->order_by('a.activities_id', 'DESC');
        $this->db->limit(10);
        $query = $this->db->get();
        $activities = $query->result_array();

        $formatted = [];
        foreach ($activities as $activity) {
            $formatted[] = [
                'user' => !empty($activity['username']) ? $activity['username'] : 'System',
                'action' => $activity['activity'],
                'time' => $this->time_ago($activity['activity_date']),
                'icon' => $activity['icon'],
                'link' => $activity['link']
            ];
        }

        return $formatted;
    }

    private function time_ago($datetime)
    {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return $diff . ' seconds ago';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' minutes ago';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' hours ago';
        } elseif ($diff < 2592000) {
            return floor($diff / 86400) . ' days ago';
        } else {
            return date('M j, Y', $timestamp);
        }
    }

    private function log_activity($module, $activity, $icon = 'fa-server', $link = null, $value1 = null, $value2 = null)
    {
        $user_id = $this->session->userdata('user_id');
        
        $activity_data = [
            'user' => $user_id ? $user_id : 0,
            'module' => $module,
            'activity' => $activity,
            'icon' => $icon,
            'link' => $link,
            'value1' => $value1,
            'value2' => $value2,
            'activity_date' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_activities', $activity_data);
    }

    private function get_expiring_items($days = 30)
    {
        $items = [];
        $end_date = date('Y-m-d', strtotime("+{$days} days"));
        $today = date('Y-m-d');
        
        $this->db->select('id, domain_name as name, expiry_date, "domain" as type');
        $this->db->from('tbldomains');
        $this->db->where('expiry_date >=', $today);
        $this->db->where('expiry_date <=', $end_date);
        $this->db->where('status', 'Active');
        $domains = $this->db->get()->result_array();
        
        $this->db->select('id, title as name, expiry_date, "hosting" as type');
        $this->db->from('tblserver_hostings');
        $this->db->where('expiry_date >=', $today);
        $this->db->where('expiry_date <=', $end_date);
        $this->db->where('status', 'Active');
        $hostings = $this->db->get()->result_array();
        
        $items = array_merge($domains, $hostings);
        
        usort($items, function($a, $b) {
            return strtotime($a['expiry_date']) - strtotime($b['expiry_date']);
        });
        
        foreach ($items as &$item) {
            $item['days_left'] = (int)ceil((strtotime($item['expiry_date']) - strtotime($today)) / (60 * 60 * 24));
            $item['link'] = $item['type'] === 'domain' 
                ? 'admin/server_management/add_domain/' . $item['id'] 
                : 'admin/server_management/add_hosting/' . $item['id'];
        }
        
        return $items;
    }

    private function get_expired_items()
    {
        $expired_domains = $this->domain_model->get_expired_domains();
        $expired_hostings = $this->hosting_model->get_expired_hostings();
        
        $items = array_merge($expired_domains, $expired_hostings);
        
        usort($items, function($a, $b) {
            return strtotime($b['expiry_date']) - strtotime($a['expiry_date']);
        });
        
        return $items;
    }

    private function get_inactive_providers()
    {
        return $this->provider_model->get_inactive_providers();
    }
    
    private function get_running_items()
    {
        $items = [];
        $today = date('Y-m-d');
        
        // Get active domains that haven't expired
        $this->db->select('id, domain_name as name, purchase_date, expiry_date, "domain" as type');
        $this->db->from('tbldomains');
        $this->db->where('status', 'Active');
        $this->db->where('expiry_date >=', $today);
        $domains = $this->db->get()->result_array();
        
        // Get active hostings that haven't expired
        $this->db->select('id, title as name, purchase_date, expiry_date, "hosting" as type');
        $this->db->from('tblserver_hostings');
        $this->db->where('status', 'Active');
        $this->db->where('expiry_date >=', $today);
        $hostings = $this->db->get()->result_array();
        
        $items = array_merge($domains, $hostings);
        
        // Sort by oldest purchase_date (longest running first)
        usort($items, function($a, $b) {
            return strtotime($a['purchase_date']) - strtotime($b['purchase_date']);
        });
        
        foreach ($items as &$item) {
            $purchase_timestamp = strtotime($item['purchase_date']);
            $today_timestamp = strtotime($today);
            $days_running = (int)(($today_timestamp - $purchase_timestamp) / (60 * 60 * 24));
            
            // Calculate years and months
            $years = floor($days_running / 365);
            $remaining_days = $days_running % 365;
            $months = floor($remaining_days / 30);
            
            if ($years > 0) {
                $item['running_for'] = $years . ' year' . ($years > 1 ? 's' : '');
                if ($months > 0) {
                    $item['running_for'] .= ' ' . $months . ' month' . ($months > 1 ? 's' : '');
                }
            } elseif ($months > 0) {
                $item['running_for'] = $months . ' month' . ($months > 1 ? 's' : '');
            } else {
                $item['running_for'] = $days_running . ' day' . ($days_running > 1 ? 's' : '');
            }
            
            $item['days_running'] = $days_running;
            $item['link'] = $item['type'] === 'domain' 
                ? 'admin/server_management/add_domain/' . $item['id'] 
                : 'admin/server_management/add_hosting/' . $item['id'];
        }
        
        return $items;
    }

    public function hosting()
    {
        $data['title'] = lang('hosting_management');

        $filters = array(
            'start_date'  => $this->input->get('start_date', TRUE),
            'end_date'   => $this->input->get('end_date', TRUE),
            'status'    => $this->input->get('status', TRUE) ?: 'All',
            'provider_id' => $this->input->get('provider_id', TRUE) ?: 'All',
            'search'    => $this->input->get('search', TRUE)
        );

        $limit = 10;
        $total_rows = $this->hosting_model->get_hostings_count($filters);
        $offset = $this->uri->segment(4) ? $this->uri->segment(4) : 0;

        $data['hostings'] = $this->hosting_model->get_hostings($limit, $offset, $filters);
        $data['providers'] = $this->hosting_model->get_all_providers();
        $data['filters'] = $filters;
        $data['total_rows'] = $total_rows;
        $data['offset'] = $offset;

        $this->load->library('pagination');
        $config['base_url'] = base_url('admin/server_management/hosting');
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $limit;
        $config['reuse_query_string'] = TRUE;
        $config['full_tag_open'] = '<ul class="pagination pagination-sm mb-0">';
        $config['full_tag_close'] = '</ul>';
        $config['first_link'] = 'First';
        $config['last_link'] = 'Last';
        $config['first_tag_open'] = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['prev_link'] = '&laquo;';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['next_link'] = '&raquo;';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="page-item">';
        $config['last_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['attributes'] = array('class' => 'page-link');

        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();

        $data['subview'] = $this->load->view('admin/server_management/hosting', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function delete_hosting($id = NULL)
    {
        $ids = $this->input->post('ids', TRUE);
        if (!empty($ids)) {
            $this->hosting_model->delete_hosting($ids);
            $this->log_activity('server_management', 'Deleted ' . count($ids) . ' hosting(s)', 'fa-trash');
            set_message('success', 'Selected hostings deleted successfully!');
        } elseif ($id) {
            $hosting = $this->hosting_model->get_hosting_by_id($id);
            $this->hosting_model->delete_hosting($id);
            if ($hosting) {
                $this->log_activity('server_management', 'Deleted hosting "' . $hosting->title . '"', 'fa-trash');
            }
            set_message('success', 'Hosting deleted successfully!');
        } else {
            set_message('error', 'Nothing to delete!');
        }
        redirect('admin/server_management/hosting');
    }

    public function domain()
    {
        $data['title'] = lang('domain_management');

        $filters = array(
            'start_date'  => $this->input->get('start_date', TRUE),
            'end_date'   => $this->input->get('end_date', TRUE),
            'status'    => $this->input->get('status', TRUE) ?: 'All',
            'provider_id' => $this->input->get('provider_id', TRUE) ?: 'All',
            'search'    => $this->input->get('search', TRUE)
        );

        $limit = 10;
        $total_rows = $this->domain_model->get_domains_count($filters);
        $offset = $this->uri->segment(4) ? $this->uri->segment(4) : 0;

        $data['domains'] = $this->domain_model->get_domains($limit, $offset, $filters);
        $data['filters'] = $filters;
        $data['total_rows'] = $total_rows;
        $data['offset'] = $offset;
        
        $data['providers'] = $this->domain_model->get_all_providers();

        $this->load->library('pagination');
        $config['base_url'] = base_url('admin/server_management/domain');
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $limit;
        $config['reuse_query_string'] = TRUE;
        $config['full_tag_open'] = '<ul class="pagination pagination-sm mb-0">';
        $config['full_tag_close'] = '</ul>';
        $config['first_link'] = 'First';
        $config['last_link'] = 'Last';
        $config['first_tag_open'] = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['prev_link'] = '&laquo;';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['next_link'] = '&raquo;';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="page-item">';
        $config['last_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['attributes'] = array('class' => 'page-link');

        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();

        $data['subview'] = $this->load->view('admin/server_management/domain', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function delete_domain($id = NULL)
    {
        $ids = $this->input->post('ids', TRUE);
        if (!empty($ids)) {
            $this->domain_model->delete_domain($ids);
            $this->log_activity('server_management', 'Deleted ' . count($ids) . ' domain(s)', 'fa-trash');
            set_message('success', 'Selected domains deleted successfully!');
        } elseif ($id) {
            $domain = $this->domain_model->get_domain_by_id($id);
            $this->domain_model->delete_domain($id);
            if ($domain) {
                $this->log_activity('server_management', 'Deleted domain "' . $domain->domain_name . '"', 'fa-trash');
            }
            set_message('success', 'Domain deleted successfully!');
        } else {
            set_message('error', 'Nothing to delete!');
        }
        redirect('admin/server_management/domain');
    }

    public function add_hosting($id = NULL)
    {
        $data['title'] = lang('add_hosting');
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('title', 'Title', 'required|trim');
            $this->form_validation->set_rules('provider_id', 'Provider', 'required|trim');
            $this->form_validation->set_rules('server_type', 'Server Type', 'required|trim');
            $this->form_validation->set_rules('purchase_date', 'Purchase Date', 'required|trim');
            $this->form_validation->set_rules('expiry_date', 'Expiry Date', 'required|trim');
            $this->form_validation->set_rules('plan', 'Plan', 'required|trim');
            $this->form_validation->set_rules('status', 'Status', 'required|trim');

            if ($this->form_validation->run() === FALSE) {
                if ($id) {
                    $data['hosting_info'] = $this->hosting_model->get_hosting_by_id($id);
                }
                $data['providers'] = $this->hosting_model->get_all_providers();
                $data['clients'] = $this->hosting_model->get_all_clients();
                $data['projects'] = $this->hosting_model->get_all_projects();
                $data['subview'] = $this->load->view('admin/server_management/add_hosting', $data, TRUE);
                $this->load->view('admin/_layout_main', $data);
            } else {
                $data_save = array(
                    'title' => $this->input->post('title', TRUE),
                    'provider_id' => $this->input->post('provider_id', TRUE),
                    'provider_url' => $this->input->post('provider_url', TRUE),
                    'server_type' => $this->input->post('server_type', TRUE),
                    'server_location' => $this->input->post('server_location', TRUE),
                    'ip_address' => $this->input->post('ip_address', TRUE),
                    'cpanel_url' => $this->input->post('cpanel_url', TRUE),
                    'username' => $this->input->post('username', TRUE),
                    'password' => $this->input->post('password', TRUE),
                    'purchase_date' => $this->input->post('purchase_date', TRUE),
                    'expiry_date' => $this->input->post('expiry_date', TRUE),
                    'plan' => $this->input->post('plan', TRUE),
                    'price' => $this->input->post('price', TRUE),
                    'project_id' => $this->input->post('project_id', TRUE),
                    'client_id' => $this->input->post('client_id', TRUE),
                    'status' => $this->input->post('status', TRUE),
                    'ftp_username' => $this->input->post('ftp_username', TRUE),
                    'ftp_password' => $this->input->post('ftp_password', TRUE),
                    'ssl_certificate' => $this->input->post('ssl_certificate') ? 1 : 0,
                    'ssl_expiry_date' => $this->input->post('ssl_expiry_date', TRUE),
                    'ssl_type' => $this->input->post('ssl_type', TRUE),
                    'ssl_info' => $this->input->post('ssl_info', TRUE),
                    'expiry_notification' => $this->input->post('expiry_notification') ? 1 : 0,
                    'notification_days' => $this->input->post('expiry_notification') ? $this->input->post('notification_days', TRUE) : NULL,
                    'notification_time_unit' => $this->input->post('expiry_notification') ? $this->input->post('notification_time_unit', TRUE) : NULL,
                    'description' => $this->input->post('description', TRUE)
                );

                if ($id) {
                    $this->hosting_model->update_hosting($id, $data_save);
                    $this->log_activity('server_management', 'Updated hosting "' . $data_save['title'] . '"', 'fa-pencil', 'admin/server_management/add_hosting/' . $id, $data_save['status']);
                    
                    $notify_data = array(
                        'description' => 'hosting_updated',
                        'icon' => 'fa-server',
                        'link' => 'admin/server_management/add_hosting/' . $id,
                        'value' => $data_save['title']
                    );
                    add_notification($notify_data);
                    
                    set_message('success', 'Hosting updated successfully!');
                } else {
                    $new_id = $this->hosting_model->insert_hosting($data_save);
                    $this->log_activity('server_management', 'Added new hosting "' . $data_save['title'] . '"', 'fa-plus', 'admin/server_management/add_hosting/' . $new_id, $data_save['status']);
                    
                    $notify_data = array(
                        'description' => 'new_hosting_added',
                        'icon' => 'fa-server',
                        'link' => 'admin/server_management/add_hosting/' . $new_id,
                        'value' => $data_save['title']
                    );
                    add_notification($notify_data);
                    
                    set_message('success', 'Hosting added successfully!');
                }
                redirect('admin/server_management/hosting');
            }
        } else {
            if ($id) {
                $data['hosting_info'] = $this->hosting_model->get_hosting_by_id($id);
            }
            $data['providers'] = $this->hosting_model->get_all_providers();
            $data['clients'] = $this->hosting_model->get_all_clients();
            $data['projects'] = $this->hosting_model->get_all_projects();
            $data['subview'] = $this->load->view('admin/server_management/add_hosting', $data, TRUE);
            $this->load->view('admin/_layout_main', $data);
        }
    }

    public function fetch_hosting_provider_url()
    {
        $provider_id = $this->input->post('provider_id');
        
        if ($provider_id) {
            $url = $this->hosting_model->get_provider_url($provider_id);
            echo json_encode(array('status' => 'success', 'provider_url' => $url));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Invalid provider'));
        }
        exit;
    }

    public function add_domain($id = NULL)
    {
        $data['title'] = lang('add_domain');
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('domain_name', 'Domain Name', 'required|trim');
            $this->form_validation->set_rules('provider_id', 'Provider', 'required|trim');
            $this->form_validation->set_rules('domain_type', 'Type', 'required|trim');
            $this->form_validation->set_rules('status', 'Status', 'required|trim');
            $this->form_validation->set_rules('purchase_date', 'Purchase Date', 'required|trim');
            $this->form_validation->set_rules('expiry_date', 'Expiry Date', 'required|trim');
            $this->form_validation->set_rules('plan', 'Plan', 'required|trim');

            if ($this->form_validation->run() === FALSE) {
                if ($id) {
                    $data['domain_info'] = $this->domain_model->get_domain_by_id($id);
                }
$data['providers'] = $this->domain_model->get_all_providers();
        $data['hostings'] = $this->domain_model->get_all_hostings();
                $data['clients'] = $this->domain_model->get_all_clients();
                $data['projects'] = $this->domain_model->get_all_projects();
                $data['subview'] = $this->load->view('admin/server_management/add_domain', $data, TRUE);
                $this->load->view('admin/_layout_main', $data);
            } else {
                $data_save = array(
                    'domain_name'             => $this->input->post('domain_name', TRUE),
                    'provider_id'           => $this->input->post('provider_id', TRUE),
                    'provider_url'         => $this->input->post('provider_url', TRUE),
                    'domain_type'           => $this->input->post('domain_type', TRUE),
                    'hosting_id'            => $this->input->post('hosting_id', TRUE),
                    'username'              => $this->input->post('username', TRUE),
                    'password'              => $this->input->post('password', TRUE),
                    'status'                => $this->input->post('status', TRUE),
                    'purchase_date'        => $this->input->post('purchase_date', TRUE),
                    'expiry_date'          => $this->input->post('expiry_date', TRUE),
                    'price'                => $this->input->post('price', TRUE),
                    'plan'                 => $this->input->post('plan', TRUE),
                    'registrar_url'         => $this->input->post('registrar_url', TRUE),
                    'registrar_username'    => $this->input->post('registrar_username', TRUE),
                    'registrar_password'    => $this->input->post('registrar_password', TRUE),
                    'registrar_status'     => $this->input->post('registrar_status', TRUE),
                    'project_id'           => $this->input->post('project_id', TRUE),
                    'client_id'            => $this->input->post('client_id', TRUE),
                    'auto_renewal'         => $this->input->post('auto_renewal') ? 1 : 0,
                    'whois_protection'     => $this->input->post('whois_protection') ? 1 : 0,
                    'expiry_notification' => $this->input->post('expiry_notification') ? 1 : 0,
                    'notification_days'   => $this->input->post('expiry_notification') ? $this->input->post('notification_days', TRUE) : NULL,
                    'notification_time_unit' => $this->input->post('expiry_notification') ? $this->input->post('notification_time_unit', TRUE) : NULL,
                    'description'        => $this->input->post('description', TRUE)
                );

                if ($id) {
                    $this->domain_model->update_domain($id, $data_save);
                    $this->log_activity('server_management', 'Updated domain "' . $data_save['domain_name'] . '"', 'fa-pencil', 'admin/server_management/add_domain/' . $id, $data_save['status']);
                    
                    $notify_data = array(
                        'description' => 'domain_updated',
                        'icon' => 'fa-globe',
                        'link' => 'admin/server_management/add_domain/' . $id,
                        'value' => $data_save['domain_name']
                    );
                    add_notification($notify_data);
                    
                    set_message('success', 'Domain updated successfully!');
                } else {
                    $new_id = $this->domain_model->insert_domain($data_save);
                    $this->log_activity('server_management', 'Added new domain "' . $data_save['domain_name'] . '"', 'fa-plus', 'admin/server_management/add_domain/' . $new_id, $data_save['status']);
                    
                    $notify_data = array(
                        'description' => 'new_domain_added',
                        'icon' => 'fa-globe',
                        'link' => 'admin/server_management/add_domain/' . $new_id,
                        'value' => $data_save['domain_name']
                    );
                    add_notification($notify_data);
                    
                    set_message('success', 'Domain added successfully!');
                }
                redirect('admin/server_management/domain');
            }
        } else {
            if ($id) {
                $data['domain_info'] = $this->domain_model->get_domain_by_id($id);
                if (empty($data['domain_info'])) {
                    set_message('error', 'Domain not found!');
                    redirect('admin/server_management/domain');
                }
            }
$data['providers'] = $this->domain_model->get_all_providers();
                $data['clients'] = $this->domain_model->get_all_clients();
                $data['projects'] = $this->domain_model->get_all_projects();
                $data['hostings'] = $this->domain_model->get_all_hostings();
                $data['subview'] = $this->load->view('admin/server_management/add_domain', $data, TRUE);
                $this->load->view('admin/_layout_main', $data);
        }
    }

    public function fetch_provider_url()
    {
        $provider_id = $this->input->post('provider_id');
        
        if ($provider_id) {
            $url = $this->domain_model->get_provider_url($provider_id);
            echo json_encode(array('status' => 'success', 'provider_url' => $url));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Invalid provider'));
        }
        
        // Prevent any extra output
        exit;
    }

    public function add_provider($id = NULL)
    {
        $data['title'] = lang('add_provider');
        if ($this->input->post()) {
            $id = $this->input->post('provider_id', TRUE);
            $this->form_validation->set_rules('provider_name', 'Provider Name', 'required|trim');
            $this->form_validation->set_rules('provider_url', 'Provider URL', 'required|trim|callback_valid_url');
            $this->form_validation->set_rules('provider_type', 'Provider Type', 'required|trim');
            $this->form_validation->set_rules('status', 'Status', 'required|trim');

            if ($this->form_validation->run() === FALSE) {
                if ($id) {
                    $data['provider_info'] = $this->provider_model->get_provider_by_id($id);
                }
                $data['subview'] = $this->load->view('admin/server_management/add_provider', $data, TRUE);
                $this->load->view('admin/_layout_main', $data);
            } else {
                $data_save = array(
                    'provider_name' => $this->input->post('provider_name', TRUE),
                    'provider_url'  => $this->input->post('provider_url', TRUE),
                    'provider_type' => $this->input->post('provider_type', TRUE),
                    'status'        => $this->input->post('status', TRUE),
                    'description'  => $this->input->post('description', TRUE)
                );

                if ($id) {
                    $this->provider_model->update_provider($id, $data_save);
                    set_message('success', 'Provider updated successfully!');
                } else {
                    $data_save['created_at'] = date('Y-m-d H:i:s');
                    $this->provider_model->insert_provider($data_save);
                    set_message('success', 'Provider added successfully!');
                }
                redirect('admin/server_management/provider');
            }
        } else {
            if ($id) {
                $data['provider_info'] = $this->provider_model->get_provider_by_id($id);
                if (empty($data['provider_info'])) {
                    set_message('error', 'Provider not found!');
                    redirect('admin/server_management/provider');
                }
            }
            $data['subview'] = $this->load->view('admin/server_management/add_provider', $data, TRUE);
            $this->load->view('admin/_layout_main', $data);
        }
    }

    public function valid_url($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL) || !preg_match('/^https?:\/\//', $url)) {
            $this->form_validation->set_message('valid_url', 'The {field} field must be a valid URL (e.g., https://example.com)');
            return FALSE;
        }

        $valid_tlds = ['com', 'org', 'net', 'io', 'dev', 'tech', 'co', 'info', 'biz', 'edu', 'gov', 'app', 'cloud', 'ai', 'io', 'me', 'us', 'uk', 'ca', 'au'];
        $parsed = parse_url($url, PHP_URL_HOST);
        $parts = preg_split('/\./', $parsed);
        $tld = strtolower(end($parts));

        if (!in_array($tld, $valid_tlds)) {
            $this->form_validation->set_message('valid_url', 'The {field} must have a valid domain extension (.com, .org, .net, etc.)');
            return FALSE;
        }

        return TRUE;
    }

    public function provider($offset = 0)
    {
        $data['title'] = lang('provider_management');

        $filters = array(
            'status'        => $this->input->get('status', TRUE) ?: 'All',
            'provider_type'=> $this->input->get('provider_type', TRUE) ?: 'All',
            'search'       => $this->input->get('search', TRUE),
            'start_date'   => $this->input->get('start_date', TRUE),
            'end_date'     => $this->input->get('end_date', TRUE)
        );

        $limit = 1000;
        $total_rows = $this->provider_model->get_providers_count($filters);

        $data['providers'] = $this->provider_model->get_providers($limit, 0, $filters);
        $data['filters'] = $filters;
        $data['total_rows'] = $total_rows;

        $data['dataTables'] = TRUE;
        $data['subview'] = $this->load->view('admin/server_management/provider', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function delete_provider($id = NULL)
    {
        $ids = $this->input->post('ids', TRUE);
        if (!empty($ids)) {
            // Bulk delete
            $this->provider_model->delete_provider($ids);
            set_message('success', 'Selected providers deleted successfully!');
        } elseif ($id) {
            // Single delete
            $this->provider_model->delete_provider($id);
            set_message('success', 'Provider deleted successfully!');
        } else {
            set_message('error', 'Nothing to delete!');
        }
        redirect('admin/server_management/provider');
    }
}
