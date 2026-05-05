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
        $this->load->model('billing_model');

        $method = $this->router->fetch_method();
        if ($method == 'domain' || $method == 'add_domain') {
            $this->session->set_userdata('menu_active_id', array('111122222223', '1111222222233'));
        } elseif ($method == 'hosting' || $method == 'add_hosting') {
            $this->session->set_userdata('menu_active_id', array('111122222223', '1111222222232'));
        } elseif ($method == 'provider' || $method == 'add_provider') {
            $this->session->set_userdata('menu_active_id', array('111122222223', '1111222222234'));
        } elseif ($method == 'billing') {
            $this->session->set_userdata('menu_active_id', array('111122222223', '1111222222235'));
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

        usort($items, function ($a, $b) {
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

        usort($items, function ($a, $b) {
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
        usort($items, function ($a, $b) {
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

        $limit = $this->input->get('limit', TRUE) ?: 10;
        $filters['limit'] = $limit;
        $total_rows = $this->hosting_model->get_hostings_count($filters);
        $offset = $this->uri->segment(4) ? $this->uri->segment(4) : 0;

        $data['hostings'] = $this->hosting_model->get_hostings($limit, $offset, $filters);
        $data['providers'] = $this->hosting_model->get_all_providers();
        $data['projects'] = $this->hosting_model->get_all_projects();
        $data['clients'] = $this->hosting_model->get_all_clients();
        $data['domains'] = $this->domain_model->get_all_domains();
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

        $limit = $this->input->get('limit', TRUE) ?: 10;
        $filters['limit'] = $limit;
        $total_rows = $this->domain_model->get_domains_count($filters);
        $offset = $this->uri->segment(4) ? $this->uri->segment(4) : 0;

        $data['domains'] = $this->domain_model->get_domains($limit, $offset, $filters);
        $data['filters'] = $filters;
        $data['total_rows'] = $total_rows;
        $data['offset'] = $offset;

        $data['providers'] = $this->domain_model->get_all_providers();
        $data['domain_statuses'] = $this->domain_model->get_domain_statuses();

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

    public function view_domain($id = NULL)
    {
        try {
            if (!$id) {
                echo "Invalid Domain ID";
                return;
            }

            $data['domain'] = $this->domain_model->get_domain_info($id);
            if (empty($data['domain'])) {
                echo "Domain not found";
                return;
            }

            $this->load->view('admin/server_management/view_domain', $data);
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            echo "Error: " . $e->getMessage();
        }
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
        $data['currencies'] = $this->db->get('tbl_currencies')->result_array();
        $data['server_types'] = $this->db->get('tbl_server_types')->result_array();
        $data['plans'] = $this->db->get('tbl_hosting_plans')->result_array();

        if ($this->input->post()) {
            $this->form_validation->set_rules('title', 'Title', 'required|trim');
            $this->form_validation->set_rules('provider_id', 'Provider', 'required|trim');
            $this->form_validation->set_rules('server_type', 'Server Type', 'required|trim');
            $this->form_validation->set_rules('purchase_date', 'Purchase Date', 'required|trim');
            $this->form_validation->set_rules('days', 'Duration', 'required|trim|numeric');
            $this->form_validation->set_rules('time_unit', 'Time Unit', 'required|trim');
            $this->form_validation->set_rules('renew', 'Renew', 'required|trim|in_list[automatic,manual]');
            $this->form_validation->set_rules('status', 'Status', 'required|trim');

            if ($this->form_validation->run() === FALSE) {
                if ($id) {
                    $data['hosting_info'] = $this->hosting_model->get_hosting_by_id($id);
                }
                $data['providers'] = $this->hosting_model->get_all_providers();
                $data['clients'] = $this->hosting_model->get_all_clients();
                $data['projects'] = $this->hosting_model->get_all_projects();
                $data['domains'] = $this->domain_model->get_all_domains();
                $data['nameservers'] = $this->db->get('tbl_nameservers')->result_array();
                $data['dns_providers'] = $this->db->order_by('name', 'ASC')->get('tbl_dns_providers')->result_array();
                $data['subview'] = $this->load->view('admin/server_management/add_hosting', $data, TRUE);
                $this->load->view('admin/_layout_main', $data);
            } else {
                // Auto-calculate expiry date
                $purchase_date = $this->input->post('purchase_date', TRUE);
                $days_value = $this->input->post('days', TRUE);
                $time_unit = $this->input->post('time_unit', TRUE);

                $expiry_date = $this->input->post('expiry_date', TRUE); // fallback
                if ($purchase_date && $days_value && $time_unit) {
                    $date = new DateTime($purchase_date);
                    switch ($time_unit) {
                        case 'Days':
                            $date->modify('+' . $days_value . ' days');
                            break;
                        case 'Weeks':
                            $date->modify('+' . ($days_value * 7) . ' days');
                            break;
                        case 'Months':
                            $date->modify('+' . $days_value . ' months');
                            break;
                        case 'Years':
                            $date->modify('+' . $days_value . ' years');
                            break;
                        case 'Decade':
                            $date->modify('+' . ($days_value * 10) . ' years');
                            break;
                        case 'Century':
                            $date->modify('+' . ($days_value * 100) . ' years');
                            break;
                    }
                    $expiry_date = $date->format('Y-m-d');
                }

                $renew = $this->input->post('renew', TRUE);
                if (!in_array($renew, array('automatic', 'manual'), true)) {
                    $renew = 'manual';
                }

                $dns_provider_name = trim((string) $this->input->post('dns_provider_name', TRUE));
                $dns_provider_id = NULL;
                // We are no longer connecting this to tblproviders as requested.
                // It will just be stored as a string in the hosting record.

                $data_save = array(
                    'title' => $this->input->post('title', TRUE),
                    'server_name' => $this->input->post('server_name', TRUE),
                    'hostname' => $this->input->post('hostname', TRUE),
                    'main_domain' => is_array($this->input->post('main_domain')) ? implode(',', $this->input->post('main_domain')) : $this->input->post('main_domain', TRUE),
                    'nameservers' => is_array($this->input->post('nameservers')) ? implode(',', $this->input->post('nameservers')) : $this->input->post('nameservers', TRUE),
                    'provider_id' => $this->input->post('provider_id', TRUE),
                    'provider_url' => $this->input->post('provider_url', TRUE),
                    'server_type' => $this->input->post('server_type', TRUE),
                    'server_location' => $this->input->post('server_location', TRUE),
                    'ip_address' => $this->input->post('ip_address', TRUE),
                    'cpanel_url' => $this->input->post('cpanel_url', TRUE),
                    'username' => $this->input->post('username', TRUE),
                    'password' => $this->input->post('password', TRUE),
                    'dns_provider_id' => $dns_provider_id,
                    'dns_provider_name' => $dns_provider_name,
                    'dns_email' => $this->input->post('dns_email', TRUE),
                    'dns_password' => $this->input->post('dns_password', TRUE),
                    'date' => $this->input->post('date', TRUE),
                    'purchase_date' => $this->input->post('purchase_date', TRUE),
                    'expiry_date' => $expiry_date,
                    'days' => $this->input->post('days', TRUE),
                    'time_unit' => $this->input->post('time_unit', TRUE),
                    'renew' => $renew,
                    'price' => $this->input->post('price', TRUE),
                    'currency_id' => $this->input->post('currency_id', TRUE),
                    'project_id' => is_array($this->input->post('project_id')) ? implode(',', $this->input->post('project_id')) : $this->input->post('project_id', TRUE),
                    'client_id' => is_array($this->input->post('client_id')) ? implode(',', $this->input->post('client_id')) : $this->input->post('client_id', TRUE),
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
                    if ($this->hosting_model->update_hosting($id, $data_save)) {
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
                        set_message('error', 'Failed to update hosting. Database error.');
                    }
                } else {
                    $new_id = $this->hosting_model->insert_hosting($data_save);
                    if ($new_id) {
                        $this->log_activity('server_management', 'Added new hosting "' . $data_save['title'] . '"', 'fa-plus', 'admin/server_management/add_hosting/' . $new_id, $data_save['status']);

                        $notify_data = array(
                            'description' => 'new_hosting_added',
                            'icon' => 'fa-server',
                            'link' => 'admin/server_management/add_hosting/' . $new_id,
                            'value' => $data_save['title']
                        );
                        add_notification($notify_data);

                        set_message('success', 'Hosting added successfully!');
                    } else {
                        set_message('error', 'Failed to add hosting. Database error.');
                    }
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
            $data['domains'] = $this->domain_model->get_all_domains();
            $data['nameservers'] = $this->db->get('tbl_nameservers')->result_array();
            $data['dns_providers'] = $this->db->order_by('name', 'ASC')->get('tbl_dns_providers')->result_array();
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
            $this->form_validation->set_rules('days', 'Duration', 'required|trim|numeric');
            $this->form_validation->set_rules('time_unit', 'Time Unit', 'required|trim');

            if ($this->form_validation->run() === FALSE) {
                if ($this->input->is_ajax_request()) {
                    echo json_encode(array('status' => 'error', 'message' => validation_errors()));
                    return;
                }
                if ($id) {
                    $data['domain_info'] = $this->domain_model->get_domain_by_id($id);
                }
                $data['providers'] = $this->domain_model->get_all_providers();
                $data['hostings'] = $this->domain_model->get_all_hostings();
                $data['clients'] = $this->domain_model->get_all_clients();
                $data['projects'] = $this->domain_model->get_all_projects();
                $data['domain_types'] = $this->domain_model->get_domain_types();
                $data['domain_statuses'] = $this->domain_model->get_domain_statuses();
                $data['currencies'] = $this->db->get('tbl_currencies')->result_array();
                $data['nameservers'] = $this->db->get('tbl_nameservers')->result_array();
                $data['subview'] = $this->load->view('admin/server_management/add_domain', $data, TRUE);
                $this->load->view('admin/_layout_main', $data);
            } else {
                // Auto-calculate expiry date (server-side fallback)
                $purchase_date = $this->input->post('purchase_date', TRUE);
                $days_value = $this->input->post('days', TRUE);
                $time_unit = $this->input->post('time_unit', TRUE);
                $expiry_date = $this->input->post('expiry_date', TRUE); // Default from form

                if ($purchase_date && $days_value && $time_unit) {
                    try {
                        $date = new DateTime($purchase_date);
                        $amount = intval($days_value);
                        switch ($time_unit) {
                            case 'Days':
                                $date->modify('+' . $amount . ' days');
                                break;
                            case 'Weeks':
                                $date->modify('+' . ($amount * 7) . ' days');
                                break;
                            case 'Months':
                                $date->modify('+' . $amount . ' months');
                                break;
                            case 'Years':
                                $date->modify('+' . $amount . ' years');
                                break;
                            case 'Decade':
                                $date->modify('+' . ($amount * 10) . ' years');
                                break;
                            case 'Century':
                                $date->modify('+' . ($amount * 100) . ' years');
                                break;
                        }
                        $expiry_date = $date->format('Y-m-d');
                    } catch (Exception $e) {
                        // Keep form value if date parsing fails
                    }
                }

                $data_save = array(
                    'domain_name'             => $this->input->post('domain_name', TRUE),
                    'provider_id'           => $this->input->post('provider_id', TRUE),
                    'provider_url'         => $this->input->post('provider_url', TRUE),
                    'domain_type'           => $this->input->post('domain_type', TRUE),
                    'hosting_id'            => $this->input->post('hosting_id', TRUE),
                    'username'              => $this->input->post('username', TRUE),
                    'password'              => $this->input->post('password', TRUE),
                    'status'                => $this->input->post('status', TRUE),
                    'date'                  => $this->input->post('date', TRUE),
                    'purchase_date'        => $this->input->post('purchase_date', TRUE),
                    'expiry_date'          => $expiry_date,
                    'days'                 => $this->input->post('days', TRUE),
                    'time_unit'            => $this->input->post('time_unit', TRUE),
                    'price'                => $this->input->post('price', TRUE),
                    'currency_id'          => $this->input->post('currency_id', TRUE),
                    'plan'                 => $this->input->post('days', TRUE) . ' ' . $this->input->post('time_unit', TRUE),
                    'registrar_url'         => $this->input->post('registrar_url', TRUE),
                    'registrar_username'    => $this->input->post('registrar_username', TRUE),
                    'registrar_password'    => $this->input->post('registrar_password', TRUE),
                    'registrar_status'     => $this->input->post('registrar_status', TRUE),
                    'project_id'           => is_array($this->input->post('project_id')) ? implode(',', $this->input->post('project_id')) : $this->input->post('project_id', TRUE),
                    'client_id'            => is_array($this->input->post('client_id')) ? implode(',', $this->input->post('client_id')) : $this->input->post('client_id', TRUE),
                    'auto_renewal'         => $this->input->post('auto_renewal') ? 1 : 0,
                    'whois_protection'     => $this->input->post('whois_protection') ? 1 : 0,
                    'expiry_notification' => $this->input->post('expiry_notification') ? 1 : 0,
                    'notification_days'   => $this->input->post('expiry_notification') ? $this->input->post('notification_days', TRUE) : NULL,
                    'notification_time_unit' => $this->input->post('expiry_notification') ? $this->input->post('notification_time_unit', TRUE) : NULL,
                    'is_locked'            => $this->input->post('is_locked') ? 1 : 0,
                    'is_for_sale'          => $this->input->post('is_for_sale') ? 1 : 0,
                    'nameservers'          => is_array($this->input->post('nameservers')) ? implode(',', $this->input->post('nameservers')) : $this->input->post('nameservers', TRUE),
                    'description'        => $this->input->post('description', TRUE)
                );

                // Handle Custom Fields
                $labels = $this->input->post('custom_field_label', TRUE);
                $values = $this->input->post('custom_field_value', TRUE);
                $custom_fields = array();
                if (!empty($labels)) {
                    foreach ($labels as $index => $label) {
                        if (!empty($label)) {
                            $custom_fields[] = array(
                                'label' => $label,
                                'value' => isset($values[$index]) ? $values[$index] : ''
                            );
                        }
                    }
                }
                $data_save['custom_fields'] = json_encode($custom_fields);

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

                if ($this->input->is_ajax_request()) {
                    echo json_encode(array(
                        'status' => 'success',
                        'id' => $id ? $id : $this->db->insert_id(),
                        'text' => $data_save['domain_name']
                    ));
                    return;
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
            $data['domain_types'] = $this->domain_model->get_domain_types();
            $data['domain_statuses'] = $this->domain_model->get_domain_statuses();
            $data['currencies'] = $this->db->get('tbl_currencies')->result_array();
            $data['nameservers'] = $this->db->get('tbl_nameservers')->result_array();

            if ($this->input->is_ajax_request()) {
                $this->load->view('admin/server_management/add_domain', $data);
                return;
            }
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
                if ($this->input->is_ajax_request()) {
                    $response = array('status' => 'error', 'message' => validation_errors());
                    $this->output->set_content_type('application/json')->set_output(json_encode($response));
                    $this->output->_display();
                    exit;
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
                if ($this->input->is_ajax_request()) {
                    $response = array(
                        'status' => 'success',
                        'message' => 'Provider saved successfully',
                        'id' => $id ? $id : $this->db->insert_id(),
                        'text' => $data_save['provider_name']
                    );
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode($response));
                    $this->output->_display();
                    exit;
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
            if ($this->input->is_ajax_request()) {
                $this->load->view('admin/server_management/add_provider', $data);
                return;
            }
            $data['subview'] = $this->load->view('admin/server_management/add_provider', $data, TRUE);
            $this->load->view('admin/_layout_main', $data);
        }
    }

    public function add_nameserver()
    {
        if ($this->input->post()) {
            if ($this->input->is_ajax_request()) {
                $name = $this->input->post('name', TRUE);
                if ($name) {
                    $data = array(
                        'name' => $name,
                        'created_at' => date('Y-m-d H:i:s')
                    );
                    if ($this->db->insert('tbl_nameservers', $data)) {
                        $response = array(
                            'status' => 'success',
                            'id' => $name,
                            'text' => $name
                        );
                        echo json_encode($response);
                    } else {
                        $response = array('status' => 'error', 'message' => 'Database error: ' . $this->db->error()['message']);
                        echo json_encode($response);
                    }
                    exit;
                }
                echo json_encode(array('status' => 'error', 'message' => 'Invalid input'));
                exit;
            }
            redirect('admin/server_management/hosting');
        }

        if ($this->input->is_ajax_request()) {
            $this->load->view('admin/server_management/add_nameserver');
            return;
        }
        $data['subview'] = $this->load->view('admin/server_management/add_nameserver', [], TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function add_server_type()
    {
        if ($this->input->post()) {
            if ($this->input->is_ajax_request()) {
                $name = $this->input->post('name', TRUE);
                if ($name) {
                    $data = array(
                        'name' => $name,
                        'created_at' => date('Y-m-d H:i:s')
                    );
                    if ($this->db->insert('tbl_server_types', $data)) {
                        $insert_id = $this->db->insert_id();
                        $response = array(
                            'status' => 'success',
                            'id' => $insert_id,
                            'text' => $name
                        );
                        echo json_encode($response);
                    } else {
                        $response = array(
                            'status' => 'error',
                            'message' => 'Database error: ' . $this->db->error()['message']
                        );
                        echo json_encode($response);
                    }
                    exit;
                }
                echo json_encode(array('status' => 'error', 'message' => 'Invalid input'));
                exit;
            }
            redirect('admin/server_management/hosting');
        }

        if ($this->input->is_ajax_request()) {
            $this->load->view('admin/server_management/add_server_type');
            return;
        }
        $data['subview'] = $this->load->view('admin/server_management/add_server_type', [], TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function add_plan()
    {
        if ($this->input->post()) {
            if ($this->input->is_ajax_request()) {
                $name = $this->input->post('name', TRUE);
                if ($name) {
                    $data = array(
                        'name' => $name,
                        'created_at' => date('Y-m-d H:i:s')
                    );
                    if ($this->db->insert('tbl_hosting_plans', $data)) {
                        $insert_id = $this->db->insert_id();
                        $response = array(
                            'status' => 'success',
                            'id' => $insert_id,
                            'text' => $name
                        );
                        echo json_encode($response);
                    } else {
                        $response = array(
                            'status' => 'error',
                            'message' => 'Database error: ' . $this->db->error()['message']
                        );
                        echo json_encode($response);
                    }
                    exit;
                }
                echo json_encode(array('status' => 'error', 'message' => 'Invalid input'));
                exit;
            }
            redirect('admin/server_management/hosting');
        }

        if ($this->input->is_ajax_request()) {
            $this->load->view('admin/server_management/add_plan');
            return;
        }
        $data['subview'] = $this->load->view('admin/server_management/add_plan', [], TRUE);
        $this->load->view('admin/_layout_main', $data);
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
            'provider_type' => $this->input->get('provider_type', TRUE) ?: 'All',
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

    public function add_dns_provider()
    {
        if ($this->input->post()) {
            if ($this->input->is_ajax_request()) {
                $name = $this->input->post('name', TRUE);
                if ($name) {
                    $data = array(
                        'name' => $name,
                        'created_at' => date('Y-m-d H:i:s')
                    );
                    if ($this->db->insert('tbl_dns_providers', $data)) {
                        $response = array(
                            'status' => 'success',
                            'id' => $name,
                            'text' => $name
                        );
                        echo json_encode($response);
                    } else {
                        $response = array('status' => 'error', 'message' => 'Database error: ' . $this->db->error()['message']);
                        echo json_encode($response);
                    }
                    exit;
                }
                echo json_encode(array('status' => 'error', 'message' => 'Invalid input'));
                exit;
            }
            redirect('admin/server_management/hosting');
        }

        if ($this->input->is_ajax_request()) {
            $this->load->view('admin/server_management/add_dns_provider');
            return;
        }
        $data['subview'] = $this->load->view('admin/server_management/add_dns_provider', [], TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function hosting_names()
    {
        $data['title'] = "Manage Hosting Names";
        $data['hostings'] = $this->domain_model->get_all_hostings();
        $data['subview'] = $this->load->view('admin/server_management/hosting_names', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function delete_hosting_name($id)
    {
        if ($id) {
            $this->db->where('id', $id)->delete('tblhostings');
            set_message('success', 'Hosting deleted successfully!');
        }
        redirect('admin/server_management/hosting_names');
    }

    public function add_hosting_type()
    {
        if ($this->input->post()) {
            $hosting_name = $this->input->post('hosting_name', TRUE);
            if ($hosting_name) {
                // Check if exists
                $check = $this->db->where('hosting_name', $hosting_name)->get('tblhostings')->row();
                if (!$check) {
                    $data = array(
                        'hosting_name' => $hosting_name,
                        'status' => 'Active',
                        'created_at' => date('Y-m-d H:i:s')
                    );
                    if ($this->domain_model->insert_hosting_type($data)) {
                        $new_id = $this->db->insert_id();
                        echo json_encode(array(
                            'status' => 'success',
                            'id' => $new_id,
                            'text' => $hosting_name
                        ));
                    } else {
                        echo json_encode(array('status' => 'error', 'message' => 'Database error'));
                    }
                } else {
                    echo json_encode(array('status' => 'error', 'message' => 'Hosting already exists'));
                }
            } else {
                echo json_encode(array('status' => 'error', 'message' => 'Invalid input'));
            }
            exit;
        }

        if ($this->input->is_ajax_request()) {
            $this->load->view('admin/server_management/add_hosting_type');
            return;
        }
    }

    public function add_domain_type()
    {
        if ($this->input->post()) {
            $domain_type = $this->input->post('domain_type', TRUE);
            if ($domain_type) {
                // Check if exists
                $check = $this->db->where('domain_type', $domain_type)->get('tbl_domain_types')->row();
                if (!$check) {
                    $data = array(
                        'domain_type' => strtoupper($domain_type),
                        'created_at' => date('Y-m-d H:i:s')
                    );
                    if ($this->db->insert('tbl_domain_types', $data)) {
                        $new_id = $this->db->insert_id();
                        echo json_encode(array(
                            'status' => 'success',
                            'id' => strtoupper($domain_type),
                            'text' => strtoupper($domain_type)
                        ));
                    } else {
                        echo json_encode(array('status' => 'error', 'message' => 'Database error'));
                    }
                } else {
                    echo json_encode(array('status' => 'error', 'message' => 'Type already exists'));
                }
            } else {
                echo json_encode(array('status' => 'error', 'message' => 'Invalid input'));
            }
            exit;
        }

        if ($this->input->is_ajax_request()) {
            $this->load->view('admin/server_management/add_domain_type');
            return;
        }
    }

    public function add_domain_status()
    {
        if ($this->input->post()) {
            $status_name = $this->input->post('status_name', TRUE);
            if ($status_name) {
                // Check if exists
                $check = $this->db->where('status_name', $status_name)->get('tbl_domain_status')->row();
                if (!$check) {
                    $data = array(
                        'status_name' => $status_name,
                        'created_at' => date('Y-m-d H:i:s')
                    );
                    if ($this->domain_model->insert_domain_status($data)) {
                        $new_id = $this->db->insert_id();
                        echo json_encode(array(
                            'status' => 'success',
                            'id' => $status_name,
                            'text' => $status_name
                        ));
                    } else {
                        echo json_encode(array('status' => 'error', 'message' => 'Database error'));
                    }
                } else {
                    echo json_encode(array('status' => 'error', 'message' => 'Status already exists'));
                }
            } else {
                echo json_encode(array('status' => 'error', 'message' => 'Invalid input'));
            }
            exit;
        }

        if ($this->input->is_ajax_request()) {
            $this->load->view('admin/server_management/add_domain_status');
            return;
        }
    }

    public function add_project()
    {
        if ($this->input->post()) {
            $project_name = $this->input->post('project_name', TRUE);
            if ($project_name) {
                $data = array(
                    'project_name' => $project_name,
                    'created_at' => date('Y-m-d H:i:s')
                );
                if ($this->db->insert('tbl_project', $data)) {
                    $new_id = $this->db->insert_id();
                    echo json_encode(array(
                        'status' => 'success',
                        'id' => $new_id,
                        'text' => $project_name
                    ));
                } else {
                    echo json_encode(array('status' => 'error', 'message' => 'Database error'));
                }
            } else {
                echo json_encode(array('status' => 'error', 'message' => 'Invalid input'));
            }
            exit;
        }

        if ($this->input->is_ajax_request()) {
            $this->load->view('admin/server_management/add_project');
            return;
        }
    }

    public function add_client()
    {
        if ($this->input->post()) {
            $name = $this->input->post('name', TRUE);
            if ($name) {
                $data = array(
                    'name' => $name,
                    'email' => $this->input->post('email', TRUE),
                    'created_at' => date('Y-m-d H:i:s')
                );
                if ($this->db->insert('tbl_client', $data)) {
                    $new_id = $this->db->insert_id();
                    echo json_encode(array(
                        'status' => 'success',
                        'id' => $new_id,
                        'text' => $name
                    ));
                } else {
                    echo json_encode(array('status' => 'error', 'message' => 'Database error'));
                }
            } else {
                echo json_encode(array('status' => 'error', 'message' => 'Invalid input'));
            }
            exit;
        }

        if ($this->input->is_ajax_request()) {
            $this->load->view('admin/server_management/add_client');
            return;
        }
    }
    public function change_domain_lock($id, $status)
    {
        $data['is_locked'] = $status;
        $this->domain_model->update_domain($id, $data);
        $domain = $this->domain_model->get_domain_by_id($id);
        $this->log_activity('server_management', ($status ? 'Locked' : 'Unlocked') . ' domain "' . $domain->domain_name . '"', 'fa-lock');
        
        $response = array('status' => 'success', 'message' => 'Domain ' . ($status ? 'locked' : 'unlocked') . ' successfully');
        echo json_encode($response);
        exit;
    }
    public function billing()
    {
        $data['title'] = lang('billing_order');
        $data['billings'] = $this->billing_model->get_all_billing();
        $data['currencies'] = $this->db->get('tbl_currencies')->result_array();
        $data['subview'] = $this->load->view('admin/server_management/billing', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function save_billing()
    {
        if ($this->input->is_ajax_request()) {
            $labels = $this->input->post('label', TRUE);
            $values = $this->input->post('value', TRUE);
            $types = $this->input->post('type', TRUE);
            $currencies = $this->input->post('currency', TRUE);
            $renewal_dates = $this->input->post('renewal_date', TRUE);
            $expiry_dates = $this->input->post('expiry_date', TRUE);
            $durations = $this->input->post('duration', TRUE);
            $time_units = $this->input->post('time_unit', TRUE);
            $renews = $this->input->post('renew', TRUE);
            
            if (!empty($labels)) {
                foreach ($labels as $index => $label) {
                    if (!empty($label)) {
                        $data = array(
                            'label' => $label,
                            'value' => isset($values[$index]) ? $values[$index] : '',
                            'type' => isset($types[$index]) ? $types[$index] : 'text',
                            'currency' => !empty($currencies[$index]) ? $currencies[$index] : NULL,
                            'renewal_date' => !empty($renewal_dates[$index]) ? $renewal_dates[$index] : NULL,
                            'expiry_date' => !empty($expiry_dates[$index]) ? $expiry_dates[$index] : NULL,
                            'duration' => !empty($durations[$index]) ? $durations[$index] : NULL,
                            'time_unit' => !empty($time_units[$index]) ? $time_units[$index] : NULL,
                            'renew' => !empty($renews[$index]) ? $renews[$index] : NULL
                        );
                        $this->billing_model->save_billing($data);
                    }
                }
                $response = array('status' => 'success', 'message' => 'Billing data saved successfully!');
            } else {
                $response = array('status' => 'error', 'message' => 'No data to save!');
            }
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }
    }

    public function edit_billing()
    {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id', TRUE);
            if (!$id) {
                echo json_encode(['status' => 'error', 'message' => 'ID is required']);
                return;
            }

            $data = array(
                'id' => $id,
                'label' => $this->input->post('label', TRUE),
                'value' => $this->input->post('value', TRUE),
                'type' => $this->input->post('type', TRUE),
                'currency' => $this->input->post('currency', TRUE) ?: NULL,
                'renewal_date' => $this->input->post('renewal_date', TRUE) ?: NULL,
                'expiry_date' => $this->input->post('expiry_date', TRUE) ?: NULL,
                'duration' => $this->input->post('duration', TRUE) ?: NULL,
                'time_unit' => $this->input->post('time_unit', TRUE) ?: NULL,
                'renew' => $this->input->post('renew', TRUE) ?: NULL
            );

            $this->billing_model->save_billing($data);
            echo json_encode(['status' => 'success', 'message' => 'Billing item updated successfully!']);
        }
    }
    public function delete_billing($id = NULL)
    {
        $ids = $this->input->post('ids', TRUE);
        if (!empty($ids)) {
            $this->billing_model->delete_billing($ids);
            $this->log_activity('server_management', 'Deleted ' . count($ids) . ' billing item(s)', 'fa-trash');
            set_message('success', 'Selected billing items deleted successfully!');
        } elseif ($id) {
            $this->billing_model->delete_billing($id);
            $this->log_activity('server_management', 'Deleted billing item ID: ' . $id, 'fa-trash');
            set_message('success', 'Billing item deleted successfully!');
        } else {
            set_message('error', 'Nothing to delete!');
        }
        redirect('admin/server_management/billing');
    }
}
