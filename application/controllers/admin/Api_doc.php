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
                'name' => 'Health Check',
                'description' => 'Check if the API is running. No authentication required.',
                'method' => 'GET',
                'endpoint' => 'api/health',
                'parameters' => [],
                'response_example' => [
                    'success' => true,
                    'message' => 'ok',
                    'status' => 'ok',
                    'version' => '1.0.0',
                    'timestamp' => '2026-01-01T00:00:00+00:00',
                ]
            ],
            [
                'name' => 'User Login',
                'description' => 'Authenticate with ERP credentials. Returns bearer tokens and user profile.',
                'method' => 'POST',
                'endpoint' => 'api/auth/login',
                'parameters' => [
                    ['name' => 'username', 'type' => 'string', 'description' => 'ERP username'],
                    ['name' => 'password', 'type' => 'string', 'description' => 'ERP password'],
                ],
                'response_example' => [
                    'success' => true,
                    'message' => 'Login successful',
                    'access_token' => 'a1b2c3d4e5f6...',
                    'refresh_token' => 'f6e5d4c3b2a1...',
                    'user' => [
                        'id' => 1,
                        'username' => 'admin',
                        'email' => 'admin@example.com',
                        'role' => 'admin',
                        'full_name' => 'Administrator',
                        'is_active' => true,
                        'allow_demo' => true,
                    ],
                    'expires_in' => 86400,
                ]
            ],
            [
                'name' => 'Refresh Token',
                'description' => 'Exchange a refresh token for a new access token pair.',
                'method' => 'POST',
                'endpoint' => 'api/auth/refresh',
                'parameters' => [
                    ['name' => 'refresh_token', 'type' => 'string', 'description' => 'Refresh token from login response'],
                ],
                'response_example' => [
                    'success' => true,
                    'access_token' => 'new_token...',
                    'refresh_token' => 'new_refresh...',
                    'user' => ['id' => 1, 'username' => 'admin'],
                    'expires_in' => 86400,
                ]
            ],
            [
                'name' => 'Logout',
                'description' => 'Invalidate the current API session.',
                'method' => 'POST',
                'endpoint' => 'api/auth/logout',
                'parameters' => [],
                'response_example' => ['success' => true, 'message' => 'Logged out'],
            ],
            [
                'name' => 'Current User',
                'description' => 'Get the authenticated user profile.',
                'method' => 'GET',
                'endpoint' => 'api/auth/me',
                'parameters' => [],
                'response_example' => [
                    'success' => true,
                    'message' => 'OK',
                    'id' => 1,
                    'username' => 'admin',
                    'email' => 'admin@example.com',
                    'role' => 'admin',
                    'full_name' => 'Administrator',
                    'is_active' => true,
                    'allow_demo' => true,
                ]
            ],
            [
                'name' => 'Register User',
                'description' => 'Register a new user account from the desktop app.',
                'method' => 'POST',
                'endpoint' => 'api/auth/register',
                'parameters' => [
                    ['name' => 'username', 'type' => 'string', 'description' => 'Desired username'],
                    ['name' => 'email', 'type' => 'string', 'description' => 'Email address'],
                    ['name' => 'password', 'type' => 'string', 'description' => 'Password'],
                    ['name' => 'full_name', 'type' => 'string', 'description' => 'Display name (optional)'],
                ],
                'response_example' => ['success' => true, 'message' => 'User registered'],
            ],
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
            ],
            [
                'name' => 'List Tasks',
                'description' => 'Get tasks visible to the current user. Supports task sync from TimeSync desktop.',
                'method' => 'GET',
                'endpoint' => 'api/tasks',
                'parameters' => [],
                'response_example' => [
                    'success' => true,
                    'tasks' => [
                        [
                            'id' => 1,
                            'title' => 'Design homepage',
                            'description' => 'Create wireframes',
                            'project_id' => 2,
                            'status' => 'in_progress',
                            'estimated_minutes' => 120,
                            'erp_id' => 1,
                            'created_by' => 1,
                            'created_at' => '2026-01-01 10:00:00',
                        ]
                    ]
                ]
            ],
            [
                'name' => 'Create Task',
                'description' => 'Create a new task. Used by TimeSync desktop when syncing tasks.',
                'method' => 'POST',
                'endpoint' => 'api/tasks',
                'parameters' => [
                    ['name' => 'title', 'type' => 'string', 'description' => 'Task title (required)'],
                    ['name' => 'description', 'type' => 'string', 'description' => 'Task description'],
                    ['name' => 'project_id', 'type' => 'int', 'description' => 'Project ID'],
                    ['name' => 'status', 'type' => 'string', 'description' => 'pending|in_progress|completed|on_hold'],
                    ['name' => 'estimated_minutes', 'type' => 'int', 'description' => 'Estimated minutes'],
                ],
                'response_example' => [
                    'success' => true,
                    'message' => 'Task created',
                    'id' => 10,
                    'erp_id' => 10,
                ]
            ],
            [
                'name' => 'Update Task',
                'description' => 'Update task fields. Returns the updated ERP task ID.',
                'method' => 'PUT',
                'endpoint' => 'api/tasks/:id',
                'parameters' => [
                    ['name' => 'title', 'type' => 'string', 'description' => 'Task title'],
                    ['name' => 'description', 'type' => 'string', 'description' => 'Task description'],
                    ['name' => 'status', 'type' => 'string', 'description' => 'Task status'],
                ],
                'response_example' => ['success' => true, 'message' => 'Task updated'],
            ],
            [
                'name' => 'Delete Task',
                'description' => 'Delete a task. Also deletes related time entries and screenshots.',
                'method' => 'DELETE',
                'endpoint' => 'api/tasks/:id',
                'parameters' => [],
                'response_example' => ['success' => true, 'message' => 'Task deleted'],
            ],
            [
                'name' => 'List Projects',
                'description' => 'Get all active projects.',
                'method' => 'GET',
                'endpoint' => 'api/projects',
                'parameters' => [],
                'response_example' => [
                    'success' => true,
                    'projects' => [
                        ['id' => 1, 'name' => 'Website Redesign', 'is_active' => true]
                    ]
                ]
            ],
            [
                'name' => 'List Users',
                'description' => 'Get all active staff users.',
                'method' => 'GET',
                'endpoint' => 'api/users',
                'parameters' => [],
                'response_example' => [
                    'success' => true,
                    'users' => [
                        ['id' => 1, 'username' => 'admin', 'full_name' => 'Admin', 'is_active' => true]
                    ]
                ]
            ],
            [
                'name' => 'Push Time Entry',
                'description' => 'Sync a time tracking entry from TimeSync desktop.',
                'method' => 'POST',
                'endpoint' => 'api/time-entries',
                'parameters' => [
                    ['name' => 'task_id', 'type' => 'int', 'description' => 'Task ID (required)'],
                    ['name' => 'started_at', 'type' => 'datetime', 'description' => 'Start time'],
                    ['name' => 'stopped_at', 'type' => 'datetime', 'description' => 'Stop time'],
                    ['name' => 'total_seconds', 'type' => 'int', 'description' => 'Total tracked seconds'],
                ],
                'response_example' => [
                    'success' => true,
                    'id' => 5,
                    'total_seconds' => 3600,
                ]
            ],
            [
                'name' => 'Update Time Entry',
                'description' => 'Update a time entry (e.g., finalize stopped_at and total_seconds).',
                'method' => 'PUT',
                'endpoint' => 'api/time-entries/:id',
                'parameters' => [
                    ['name' => 'stopped_at', 'type' => 'datetime', 'description' => 'Stop time'],
                    ['name' => 'total_seconds', 'type' => 'int', 'description' => 'Total seconds'],
                ],
                'response_example' => ['success' => true, 'message' => 'Time entry updated'],
            ],
            [
                'name' => 'Attendance Check-In',
                'description' => 'Clock in for the day. Creates or updates attendance record.',
                'method' => 'POST',
                'endpoint' => 'api/attendance/check-in',
                'parameters' => [],
                'response_example' => [
                    'success' => true,
                    'message' => 'Checked in',
                    'attendance_id' => 10,
                    'clock_id' => 25,
                ]
            ],
            [
                'name' => 'Attendance Check-Out',
                'description' => 'Clock out from the current session.',
                'method' => 'POST',
                'endpoint' => 'api/attendance/check-out',
                'parameters' => [],
                'response_example' => ['success' => true, 'message' => 'Checked out'],
            ],
            [
                'name' => 'My Attendance',
                'description' => 'Get the current user\'s attendance records.',
                'method' => 'GET',
                'endpoint' => 'api/attendance',
                'parameters' => [
                    ['name' => 'from', 'type' => 'date', 'description' => 'Start date (YYYY-MM-DD)'],
                    ['name' => 'to', 'type' => 'date', 'description' => 'End date (YYYY-MM-DD)'],
                    ['name' => 'limit', 'type' => 'int', 'description' => 'Max records (default 30)'],
                ],
                'response_example' => [
                    'success' => true,
                    'attendance' => [
                        [
                            'date' => '2026-01-15',
                            'status' => 'present',
                            'total_hours' => 8.5,
                            'clocks' => [
                                ['clockin_time' => '09:00:00', 'clockout_time' => '18:00:00'],
                            ],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'Upload Screenshot',
                'description' => 'Upload a screenshot captured by TimeSync desktop (base64 encoded PNG).',
                'method' => 'POST',
                'endpoint' => 'api/screenshots',
                'parameters' => [
                    ['name' => 'image_base64', 'type' => 'string', 'description' => 'Base64-encoded PNG image'],
                    ['name' => 'task_id', 'type' => 'int', 'description' => 'Associated task ID'],
                    ['name' => 'captured_at', 'type' => 'datetime', 'description' => 'When screenshot was captured'],
                ],
                'response_example' => [
                    'success' => true,
                    'message' => 'Screenshot uploaded',
                    'id' => 42,
                    'file_url' => 'https://erp.example.com/uploads/screenshots/1/20260101_120000.png',
                ]
            ],
            [
                'name' => 'List Screenshots',
                'description' => 'List screenshots. Admins see all; users see only their own.',
                'method' => 'GET',
                'endpoint' => 'api/screenshots',
                'parameters' => [
                    ['name' => 'user_id', 'type' => 'int', 'description' => 'Filter by user (admin only)'],
                    ['name' => 'task_id', 'type' => 'int', 'description' => 'Filter by task'],
                    ['name' => 'from', 'type' => 'date', 'description' => 'Start date'],
                    ['name' => 'limit', 'type' => 'int', 'description' => 'Max records'],
                ],
                'response_example' => [
                    'success' => true,
                    'screenshots' => [
                        [
                            'id' => 1,
                            'user_id' => 1,
                            'username' => 'Administrator',
                            'file_url' => 'https://erp.example.com/uploads/screenshots/1/20260101_120000.png',
                            'captured_at' => '2026-01-01 12:00:00',
                        ]
                    ]
                ]
            ],
            [
                'name' => 'List Consultants',
                'description' => 'List active consultants. Auth: Bearer token OR X-API-Key header (key managed under Consultation Settings).',
                'method' => 'GET',
                'endpoint' => 'api/v1/consultations/consultants',
                'parameters' => [],
                'response_example' => [
                    'success' => true,
                    'message' => 'OK',
                    'consultants' => [
                        [
                            'consultant_id' => 4,
                            'name' => 'aaa',
                            'email' => 'aaa@example.com',
                            'phone' => '',
                            'timezone' => 'Asia/Dhaka',
                            'department' => '',
                            'bio' => '',
                        ]
                    ]
                ]
            ],
            [
                'name' => 'Available Slots',
                'description' => 'Get available time slots for a consultant on a date. Auth: Bearer token OR X-API-Key header.',
                'method' => 'GET',
                'endpoint' => 'api/v1/consultations/slots',
                'parameters' => [
                    ['name' => 'consultant_id', 'type' => 'int', 'description' => 'Consultant ID (required)'],
                    ['name' => 'date', 'type' => 'date', 'description' => 'Target date YYYY-MM-DD (required)'],
                    ['name' => 'timezone', 'type' => 'string', 'description' => 'Customer timezone (defaults to company timezone)'],
                    ['name' => 'duration', 'type' => 'int', 'description' => 'Meeting duration in minutes (defaults to setting)'],
                ],
                'response_example' => [
                    'success' => true,
                    'message' => 'OK',
                    'consultant_id' => 4,
                    'date' => '2026-08-05',
                    'timezone' => 'Asia/Dhaka',
                    'duration' => 30,
                    'slots' => [['time' => '10:00', 'end_time' => '10:30']],
                ]
            ],
            [
                'name' => 'Create Booking',
                'description' => 'Create a consultation booking. Emails confirmation to customer and consultant. Auth: Bearer token OR X-API-Key header.',
                'method' => 'POST',
                'endpoint' => 'api/v1/consultations/bookings',
                'parameters' => [
                    ['name' => 'consultant_id', 'type' => 'int', 'description' => 'Consultant ID (required)'],
                    ['name' => 'customer_name', 'type' => 'string', 'description' => 'Customer name (required)'],
                    ['name' => 'customer_email', 'type' => 'string', 'description' => 'Valid customer email (required)'],
                    ['name' => 'customer_timezone', 'type' => 'string', 'description' => 'Customer timezone (required)'],
                    ['name' => 'appointment_date', 'type' => 'date', 'description' => 'Date YYYY-MM-DD (required)'],
                    ['name' => 'appointment_time', 'type' => 'string', 'description' => 'Start time H:i (required)'],
                    ['name' => 'customer_phone', 'type' => 'string', 'description' => 'Customer phone'],
                    ['name' => 'company', 'type' => 'string', 'description' => 'Company name'],
                    ['name' => 'country', 'type' => 'string', 'description' => 'Country'],
                    ['name' => 'consultation_type', 'type' => 'string', 'description' => 'Type (defaults to consultation)'],
                    ['name' => 'notes', 'type' => 'string', 'description' => 'Notes'],
                    ['name' => 'duration_minutes', 'type' => 'int', 'description' => 'Duration (defaults to setting)'],
                ],
                'response_example' => [
                    'success' => true,
                    'message' => 'Booking created',
                    'appointment' => [
                        'appointment_id' => 10,
                        'consultant_id' => 4,
                        'consultant_name' => 'aaa',
                        'customer_name' => 'John Doe',
                        'customer_email' => 'john@client.com',
                        'status' => 'confirmed',
                        'appointment_date' => '2026-08-05',
                        'appointment_time' => '10:00',
                        'meeting_url' => 'https://meet.example.com/...',
                    ]
                ]
            ],
            [
                'name' => 'List Bookings',
                'description' => 'List consultation appointments, optionally filtered by status. Auth: Bearer token OR X-API-Key header.',
                'method' => 'GET',
                'endpoint' => 'api/v1/consultations/bookings',
                'parameters' => [
                    ['name' => 'status', 'type' => 'string', 'description' => 'Filter: confirmed|pending|cancelled|completed|no_show'],
                ],
                'response_example' => [
                    'success' => true,
                    'message' => 'OK',
                    'appointments' => [
                        ['appointment_id' => 10, 'customer_name' => 'John Doe', 'status' => 'confirmed']
                    ]
                ]
            ],
            [
                'name' => 'Cancel Booking',
                'description' => 'Cancel a consultation appointment. Auth: Bearer token OR X-API-Key header.',
                'method' => 'POST',
                'endpoint' => 'api/v1/consultations/bookings/:id/cancel',
                'parameters' => [],
                'response_example' => [
                    'success' => true,
                    'message' => 'Appointment cancelled',
                    'appointment' => ['appointment_id' => 10, 'status' => 'cancelled'],
                ]
            ],
        ];

        $data['subview'] = $this->load->view('admin/api_doc/index', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }
}
