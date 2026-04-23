<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Server_Management extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('provider_model');
        $this->load->model('domain_model');
    }

    public function index()
    {
        $this->dashboard();
    }

    public function dashboard()
    {
        $data['title'] = lang('server_dashboard');

        $data['stats'] = [
            'total_hostings' => 20,
            'active_hostings' => 6,
            'total_domains' => 20,
            'active_domains' => 3,
            'expiring_hostings' => 0,
            'expiring_domains' => 0
        ];

        $data['recent_activities'] = [
            [
                'user' => 'Lura Gleason',
                'action' => 'Hosting "API Server" was created',
                'time' => '2 hours ago'
            ],
            [
                'user' => 'Admin',
                'action' => 'Domain "example.com" renewed',
                'time' => '5 hours ago'
            ],
            [
                'user' => 'John Doe',
                'action' => 'Hosting "Main Server" suspended',
                'time' => '1 day ago'
            ],
            [
                'user' => 'Jane Smith',
                'action' => 'Provider "AWS" was added',
                'time' => '2 days ago'
            ]
        ];

        $data['subview'] = $this->load->view('admin/server_management/dashboard', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function hosting()
    {
        $data['title'] = lang('hosting_management');

        $data['hostings'] = [
            [
                'id' => 1,
                'title' => 'API Server',
                'provider_name' => 'Akamai',
                'server_type' => 'VPS',
                'status' => 'Active',
                'purchase_date' => '2024-01-15',
                'expiry_date' => '2025-01-15'
            ],
            [
                'id' => 2,
                'title' => 'Production Web Server',
                'provider_name' => 'Vercel',
                'server_type' => 'Cloud',
                'status' => 'Active',
                'purchase_date' => '2024-02-20',
                'expiry_date' => '2025-02-20'
            ],
            [
                'id' => 3,
                'title' => 'Development Server',
                'provider_name' => 'HostGator',
                'server_type' => 'Shared',
                'status' => 'Suspended',
                'purchase_date' => '2024-03-10',
                'expiry_date' => '2025-03-10'
            ],
            [
                'id' => 4,
                'title' => 'Database Server',
                'provider_name' => 'AWS',
                'server_type' => 'Dedicated',
                'status' => 'Pending',
                'purchase_date' => '2024-04-05',
                'expiry_date' => '2025-04-05'
            ],
            [
                'id' => 5,
                'title' => 'Backup Server',
                'provider_name' => 'DigitalOcean',
                'server_type' => 'VPS',
                'status' => 'Cancelled',
                'purchase_date' => '2023-06-01',
                'expiry_date' => '2024-06-01'
            ],
            [
                'id' => 6,
                'title' => 'Testing Server',
                'provider_name' => 'Vercel',
                'server_type' => 'Cloud',
                'status' => 'Active',
                'purchase_date' => '2024-05-12',
                'expiry_date' => '2025-05-12'
            ]
        ];

        $data['subview'] = $this->load->view('admin/server_management/hosting', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
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
            set_message('success', 'Selected domains deleted successfully!');
        } elseif ($id) {
            $this->domain_model->delete_domain($id);
            set_message('success', 'Domain deleted successfully!');
        } else {
            set_message('error', 'Nothing to delete!');
        }
        redirect('admin/server_management/domain');
    }

    public function add_hosting()
    {
        $data['title'] = lang('add_hosting');
        $data['subview'] = $this->load->view('admin/server_management/add_hosting', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
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
                    set_message('success', 'Domain updated successfully!');
                } else {
                    $this->domain_model->insert_domain($data_save);
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
