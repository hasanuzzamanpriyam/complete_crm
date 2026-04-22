<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Server_Management extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('provider_model');
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

        $data['domains'] = [
            [
                'id' => 1,
                'domain_name' => 'webapp.io',
                'provider' => 'Google Workspace',
                'domain_type' => 'IO',
                'status' => 'Active',
                'purchase_date' => '2024-01-10',
                'expiry_date' => '2025-01-10',
                'hosting' => 'API Server'
            ],
            [
                'id' => 2,
                'domain_name' => 'web.net',
                'provider' => 'GoDaddy',
                'domain_type' => 'NET',
                'status' => 'Active',
                'purchase_date' => '2024-02-15',
                'expiry_date' => '2025-02-15',
                'hosting' => 'Production Web Server'
            ],
            [
                'id' => 3,
                'domain_name' => 'mystore.dev',
                'provider' => 'Namecheap',
                'domain_type' => 'DEV',
                'status' => 'Pending',
                'purchase_date' => '2024-03-20',
                'expiry_date' => '2025-03-20',
                'hosting' => '-'
            ],
            [
                'id' => 4,
                'domain_name' => 'company.org',
                'provider' => 'MongoDB Atlas',
                'domain_type' => 'ORG',
                'status' => 'Transferring',
                'purchase_date' => '2024-04-05',
                'expiry_date' => '2025-04-05',
                'hosting' => 'Development Server'
            ],
            [
                'id' => 5,
                'domain_name' => 'techstore.tech',
                'provider' => 'Cloudflare',
                'domain_type' => 'TECH',
                'status' => 'Expired',
                'purchase_date' => '2023-05-01',
                'expiry_date' => '2024-05-01',
                'hosting' => 'Backup Server'
            ],
            [
                'id' => 6,
                'domain_name' => 'myapp.com',
                'provider' => 'AWS',
                'domain_type' => 'COM',
                'status' => 'Active',
                'purchase_date' => '2024-06-12',
                'expiry_date' => '2025-06-12',
                'hosting' => 'Testing Server'
            ]
        ];

        $data['subview'] = $this->load->view('admin/server_management/domain', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function add_hosting()
    {
        $data['title'] = lang('add_hosting');
        $data['subview'] = $this->load->view('admin/server_management/add_hosting', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function add_domain()
    {
        $data['title'] = lang('add_domain');
        $data['subview'] = $this->load->view('admin/server_management/add_domain', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
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
