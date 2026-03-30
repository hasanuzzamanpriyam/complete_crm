<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Api_doc extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $data['title'] = 'API Documentation';
        $data['base_url'] = base_url();

        $data['api_endpoints'] = [
            [
                'name' => 'Staff Users',
                'description' => 'Get a list of all active staff members.',
                'method' => 'GET',
                'endpoint' => 'api/staff-users',
                'parameters' => [],
                'response_example' => [
                    'users' => [
                        [
                            'user_id' => '1',
                            'username' => 'admin',
                            'email' => 'admin@example.com',
                            'role_id' => '3',
                            'fullname' => 'Administrator',
                            'employment_id' => 'EMP001',
                            'phone' => '1234567890',
                            'staff_position' => 'Senior Developer',
                            'facebook_url' => '',
                            'instagram_url' => '',
                            'x_url' => '',
                            'linkedin_url' => '',
                            'designations' => 'Managing Director',
                            'image' => 'https://example.com/assets/img/user/default_avatar.jpg'
                        ]
                    ]
                ]
            ],
            [
                'name' => 'Clients',
                'description' => 'Get a list of all active clients.',
                'method' => 'GET',
                'endpoint' => 'api/clients',
                'parameters' => [],
                'response_example' => [
                    'clients' => [
                        [
                            'user_id' => '2',
                            'username' => 'client_john',
                            'email' => 'john@client.com',
                            'role_id' => '2',
                            'fullname' => 'John Doe',
                            'employment_id' => '',
                            'phone' => '0987654321',
                            'staff_position' => '',
                            'facebook_url' => '',
                            'instagram_url' => '',
                            'x_url' => '',
                            'linkedin_url' => '',
                            'image' => 'https://example.com/assets/img/user/default_avatar.jpg'
                        ]
                    ]
                ]
            ],
            [
                'name' => 'Jobs Posted List',
                'description' => 'Get a list of all published job circulars.',
                'method' => 'GET',
                'endpoint' => 'api/jobs-posted-list',
                'parameters' => [],
                'response_example' => [
                    'jobs' => [
                        [
                            'job_circular_id' => '1',
                            'job_title' => 'Senior Developer',
                            'designations' => 'Developer',
                            'vacancy_no' => '2',
                            'last_date' => '2026-12-31',
                            'description' => '...',
                            'status' => 'published'
                        ]
                    ]
                ]
            ]
        ];

        $data['subview'] = $this->load->view('admin/api_doc/index', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }
}
