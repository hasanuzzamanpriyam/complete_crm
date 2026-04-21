<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Server_Management extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
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
        $data['subview'] = $this->load->view('admin/server_management/domain', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function provider()
    {
        $data['title'] = lang('provider_management');
        $data['subview'] = $this->load->view('admin/server_management/provider', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }
}
