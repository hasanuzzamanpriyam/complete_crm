<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_617 extends CI_Migration
{
    public function up()
    {
        // ------------------------------------------------------------
        // 1. Providers
        // ------------------------------------------------------------
        $providers = [
            [
                'provider_name' => 'Namecheap',
                'provider_url'  => 'https://www.namecheap.com',
                'provider_type' => 'Domain Registrar',
                'status'        => 'Active',
                'description'   => 'Domain registration and hosting services',
            ],
            [
                'provider_name' => 'GoDaddy',
                'provider_url'  => 'https://www.godaddy.com',
                'provider_type' => 'Domain Registrar',
                'status'        => 'Active',
                'description'   => 'Domain names, hosting, and SSL certificates',
            ],
            [
                'provider_name' => 'DigitalOcean',
                'provider_url'  => 'https://www.digitalocean.com',
                'provider_type' => 'Cloud Hosting',
                'status'        => 'Active',
                'description'   => 'Cloud infrastructure and VPS hosting',
            ],
            [
                'provider_name' => 'Amazon Web Services',
                'provider_url'  => 'https://aws.amazon.com',
                'provider_type' => 'Cloud Hosting',
                'status'        => 'Active',
                'description'   => 'Cloud computing and hosting services',
            ],
            [
                'provider_name' => 'Cloudflare',
                'provider_url'  => 'https://www.cloudflare.com',
                'provider_type' => 'DNS / CDN',
                'status'        => 'Active',
                'description'   => 'CDN, DNS, and security services',
            ],
        ];

        foreach ($providers as $row) {
            $row['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('tblproviders', $row);
        }

        // ------------------------------------------------------------
        // 2. Supporting options
        // ------------------------------------------------------------
        // Hosting name types
        $hosting_names = [
            ['hosting_name' => 'Shared Hosting',     'status' => 'Active'],
            ['hosting_name' => 'VPS Hosting',         'status' => 'Active'],
            ['hosting_name' => 'Dedicated Server',    'status' => 'Active'],
            ['hosting_name' => 'Cloud Hosting',       'status' => 'Active'],
            ['hosting_name' => 'Reseller Hosting',    'status' => 'Active'],
        ];
        foreach ($hosting_names as $row) {
            $row['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('tblhostings', $row);
        }

        // Server types
        $server_types = [
            ['name' => 'Linux',       'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Windows',     'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Docker',      'created_at' => date('Y-m-d H:i:s')],
        ];
        foreach ($server_types as $row) {
            $this->db->insert('tbl_server_types', $row);
        }

        // Hosting plans
        $hosting_plans = [
            ['name' => 'Starter',   'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Business',  'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Enterprise','created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Ultimate',  'created_at' => date('Y-m-d H:i:s')],
        ];
        foreach ($hosting_plans as $row) {
            $this->db->insert('tbl_hosting_plans', $row);
        }

        // Domain status options
        $domain_statuses = [
            ['status_name' => 'Active',       'created_at' => date('Y-m-d H:i:s')],
            ['status_name' => 'Expired',      'created_at' => date('Y-m-d H:i:s')],
            ['status_name' => 'Expiring',     'created_at' => date('Y-m-d H:i:s')],
            ['status_name' => 'Pending',      'created_at' => date('Y-m-d H:i:s')],
            ['status_name' => 'Transferring', 'created_at' => date('Y-m-d H:i:s')],
            ['status_name' => 'Cancelled',    'created_at' => date('Y-m-d H:i:s')],
        ];
        foreach ($domain_statuses as $row) {
            $this->db->insert('tbl_domain_status', $row);
        }

        // Billing types
        $billing_types = [
            ['name' => 'Hosting',     'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Domain',      'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'SSL',         'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Maintenance', 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'License',     'created_at' => date('Y-m-d H:i:s')],
        ];
        foreach ($billing_types as $row) {
            $this->db->insert('tbl_billing_types', $row);
        }

        // Billing flags
        $billing_flags = [
            ['name' => 'Urgent',       'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Normal',       'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'High Priority','created_at' => date('Y-m-d H:i:s')],
        ];
        foreach ($billing_flags as $row) {
            $this->db->insert('tbl_billing_flags', $row);
        }

        // Billing statuses
        $billing_statuses = [
            ['name' => 'Active',  'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Pending', 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Expired', 'created_at' => date('Y-m-d H:i:s')],
        ];
        foreach ($billing_statuses as $row) {
            $this->db->insert('tbl_billing_status', $row);
        }

        // Billing bill statuses
        $billing_bill_statuses = [
            ['name' => 'Paid',       'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Unpaid',     'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Overdue',    'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Cancelled',  'created_at' => date('Y-m-d H:i:s')],
        ];
        foreach ($billing_bill_statuses as $row) {
            $this->db->insert('tbl_billing_bill_status', $row);
        }

        // Billing manage options
        $billing_manage = [
            ['name' => 'Manual',  'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Auto',    'created_at' => date('Y-m-d H:i:s')],
        ];
        foreach ($billing_manage as $row) {
            $this->db->insert('tbl_billing_manage', $row);
        }

        // ------------------------------------------------------------
        // 3. Hosting records (tblserver_hostings)
        // ------------------------------------------------------------
        $hostings = [
            [
                'title'         => 'Production Web Server',
                'server_name'   => 'web01',
                'hostname'      => 'web01.tic.com.bd',
                'provider_id'   => 3,
                'provider_url'  => 'https://www.digitalocean.com',
                'server_type'   => 'Linux',
                'server_location'=> 'Singapore',
                'ip_address'    => '159.89.123.45',
                'cpanel_url'    => 'https://web01.tic.com.bd:2083',
                'username'      => 'admin_web',
                'password'      => 'enc_pass_123',
                'purchase_date' => '2025-01-15',
                'expiry_date'   => date('Y-m-d', strtotime('+180 days')),
                'days'          => 365,
                'time_unit'     => 'Days',
                'renew'         => 'automatic',
                'currency_id'   => 'USD',
                'price'         => 89.00,
                'project_id'    => '3',
                'client_id'     => '1',
                'status'        => 'Active',
                'ssl_certificate' => 1,
                'ssl_expiry_date' => date('Y-m-d', strtotime('+300 days')),
                'ssl_type'      => 'Let\'s Encrypt',
                'expiry_notification' => 1,
                'notification_days'   => 14,
                'notification_time_unit' => 'Days',
                'description'   => 'Main production web server hosting client applications',
            ],
            [
                'title'         => 'Staging Environment',
                'server_name'   => 'stg01',
                'hostname'      => 'staging.tic.com.bd',
                'provider_id'   => 4,
                'provider_url'  => 'https://aws.amazon.com',
                'server_type'   => 'Linux',
                'server_location'=> 'Mumbai',
                'ip_address'    => '13.126.78.90',
                'cpanel_url'    => '',
                'username'      => 'stg_admin',
                'password'      => 'enc_pass_456',
                'purchase_date' => '2025-06-01',
                'expiry_date'   => date('Y-m-d', strtotime('+90 days')),
                'days'          => 365,
                'time_unit'     => 'Days',
                'renew'         => 'manual',
                'currency_id'   => 'USD',
                'price'         => 45.00,
                'project_id'    => '2',
                'client_id'     => '2',
                'status'        => 'Active',
                'ssl_certificate' => 1,
                'ssl_expiry_date' => date('Y-m-d', strtotime('+200 days')),
                'ssl_type'      => 'Let\'s Encrypt',
                'expiry_notification' => 1,
                'notification_days'   => 7,
                'notification_time_unit' => 'Days',
                'description'   => 'Staging server for testing deployments',
            ],
            [
                'title'         => 'Legacy App Server',
                'server_name'   => 'app01',
                'hostname'      => 'app01.tic.com.bd',
                'provider_id'   => 3,
                'provider_url'  => 'https://www.digitalocean.com',
                'server_type'   => 'Linux',
                'server_location'=> 'Singapore',
                'ip_address'    => '159.89.67.89',
                'cpanel_url'    => '',
                'username'      => 'app_admin',
                'password'      => 'enc_pass_789',
                'purchase_date' => '2024-03-10',
                'expiry_date'   => date('Y-m-d', strtotime('-5 days')),
                'days'          => 365,
                'time_unit'     => 'Days',
                'renew'         => 'manual',
                'currency_id'   => 'USD',
                'price'         => 120.00,
                'project_id'    => '5',
                'client_id'     => '6',
                'status'        => 'Active',
                'ssl_certificate' => 0,
                'expiry_notification' => 1,
                'notification_days'   => 30,
                'notification_time_unit' => 'Days',
                'description'   => 'Legacy application server - needs renewal',
            ],
            [
                'title'         => 'Backup Storage Server',
                'server_name'   => 'bkup01',
                'hostname'      => 'backup.tic.com.bd',
                'provider_id'   => 4,
                'provider_url'  => 'https://aws.amazon.com',
                'server_type'   => 'Linux',
                'server_location'=> 'Frankfurt',
                'ip_address'    => '18.156.34.56',
                'cpanel_url'    => '',
                'username'      => 'bkup_admin',
                'password'      => 'enc_pass_101',
                'purchase_date' => '2025-09-01',
                'expiry_date'   => date('Y-m-d', strtotime('+14 days')),
                'days'          => 90,
                'time_unit'     => 'Days',
                'renew'         => 'automatic',
                'currency_id'   => 'EUR',
                'price'         => 30.00,
                'project_id'    => '3',
                'client_id'     => '1',
                'status'        => 'Active',
                'ssl_certificate' => 1,
                'ssl_expiry_date' => date('Y-m-d', strtotime('+60 days')),
                'ssl_type'      => 'SSL.com',
                'expiry_notification' => 1,
                'notification_days'   => 14,
                'notification_time_unit' => 'Days',
                'description'   => 'Automated backup and disaster recovery server',
            ],
            [
                'title'         => 'New Client Portal',
                'server_name'   => 'portal01',
                'hostname'      => 'portal.tic.com.bd',
                'provider_id'   => 3,
                'provider_url'  => 'https://www.digitalocean.com',
                'server_type'   => 'Linux',
                'server_location'=> 'Singapore',
                'ip_address'    => '159.89.99.99',
                'cpanel_url'    => 'https://portal.tic.com.bd:2083',
                'username'      => 'portal_admin',
                'password'      => 'enc_pass_202',
                'purchase_date' => date('Y-m-d'),
                'expiry_date'   => date('Y-m-d', strtotime('+365 days')),
                'days'          => 365,
                'time_unit'     => 'Days',
                'renew'         => 'automatic',
                'currency_id'   => 'USD',
                'price'         => 60.00,
                'project_id'    => '7',
                'client_id'     => '7',
                'status'        => 'Pending',
                'ssl_certificate' => 0,
                'expiry_notification' => 1,
                'notification_days'   => 30,
                'notification_time_unit' => 'Days',
                'description'   => 'New client portal server - setup in progress',
            ],
        ];

        foreach ($hostings as $row) {
            $this->db->insert('tblserver_hostings', $row);
        }

        // ------------------------------------------------------------
        // 4. Domain records (tbldomains)
        // ------------------------------------------------------------
        $domains = [
            [
                'domain_name'       => 'tic.com.bd',
                'provider_id'       => 1,
                'provider_url'      => 'https://www.namecheap.com',
                'domain_type'       => '.COM.BD',
                'hosting_id'        => 1,
                'username'          => 'tic_admin',
                'password'          => 'dom_pass_111',
                'status'            => 'Active',
                'date'              => '2024-01-01',
                'purchase_date'     => '2024-01-01',
                'expiry_date'       => date('Y-m-d', strtotime('+120 days')),
                'days'              => 365,
                'time_unit'         => 'Days',
                'price'             => 15.00,
                'currency_id'       => 'USD',
                'plan'              => 'Business',
                'registrar_url'     => 'https://www.namecheap.com',
                'registrar_username'=> 'tic_reg',
                'registrar_password'=> 'reg_pass_111',
                'registrar_status'  => 'Active',
                'project_id'        => '3',
                'client_id'         => '1',
                'auto_renewal'      => 1,
                'renew'             => 'automatic',
                'whois_protection'  => 1,
                'expiry_notification' => 1,
                'notification_days'   => 30,
                'notification_time_unit' => 'Days',
                'is_locked'         => 0,
                'is_for_sale'       => 0,
                'description'       => 'Primary company domain',
            ],
            [
                'domain_name'       => 'ticlimited.org',
                'provider_id'       => 2,
                'provider_url'      => 'https://www.godaddy.com',
                'domain_type'       => '.ORG',
                'hosting_id'        => 2,
                'username'          => 'org_admin',
                'password'          => 'dom_pass_222',
                'status'            => 'Active',
                'date'              => '2025-03-15',
                'purchase_date'     => '2025-03-15',
                'expiry_date'       => date('Y-m-d', strtotime('+200 days')),
                'days'              => 365,
                'time_unit'         => 'Days',
                'price'             => 12.00,
                'currency_id'       => 'USD',
                'plan'              => 'Starter',
                'registrar_url'     => 'https://www.godaddy.com',
                'registrar_username'=> 'org_reg',
                'registrar_password'=> 'reg_pass_222',
                'registrar_status'  => 'Active',
                'project_id'        => '2',
                'client_id'         => '2',
                'auto_renewal'      => 1,
                'renew'             => 'automatic',
                'whois_protection'  => 1,
                'expiry_notification' => 1,
                'notification_days'   => 14,
                'notification_time_unit' => 'Days',
                'is_locked'         => 0,
                'is_for_sale'       => 0,
                'description'       => 'Company organizational domain',
            ],
            [
                'domain_name'       => 'eazyproo.io',
                'provider_id'       => 1,
                'provider_url'      => 'https://www.namecheap.com',
                'domain_type'       => '.IO',
                'hosting_id'        => 1,
                'username'          => 'eazy_admin',
                'password'          => 'dom_pass_333',
                'status'            => 'Active',
                'date'              => '2025-06-20',
                'purchase_date'     => '2025-06-20',
                'expiry_date'       => date('Y-m-d', strtotime('+10 days')),
                'days'              => 365,
                'time_unit'         => 'Days',
                'price'             => 39.00,
                'currency_id'       => 'USD',
                'plan'              => 'Enterprise',
                'registrar_url'     => 'https://www.namecheap.com',
                'registrar_username'=> 'eazy_reg',
                'registrar_password'=> 'reg_pass_333',
                'registrar_status'  => 'Active',
                'project_id'        => '14',
                'client_id'         => '12',
                'auto_renewal'      => 0,
                'renew'             => 'manual',
                'whois_protection'  => 0,
                'expiry_notification' => 1,
                'notification_days'   => 7,
                'notification_time_unit' => 'Days',
                'is_locked'         => 0,
                'is_for_sale'       => 0,
                'description'       => 'Client project domain - expiring soon',
            ],
            [
                'domain_name'       => 'oldproject.com',
                'provider_id'       => 2,
                'provider_url'      => 'https://www.godaddy.com',
                'domain_type'       => '.COM',
                'hosting_id'        => 3,
                'username'          => 'old_admin',
                'password'          => 'dom_pass_444',
                'status'            => 'Active',
                'date'              => '2023-01-10',
                'purchase_date'     => '2023-01-10',
                'expiry_date'       => date('Y-m-d', strtotime('-30 days')),
                'days'              => 365,
                'time_unit'         => 'Days',
                'price'             => 11.00,
                'currency_id'       => 'USD',
                'plan'              => 'Starter',
                'registrar_url'     => 'https://www.godaddy.com',
                'registrar_username'=> 'old_reg',
                'registrar_password'=> 'reg_pass_444',
                'registrar_status'  => 'Expired',
                'project_id'        => '3',
                'client_id'         => '1',
                'auto_renewal'      => 0,
                'renew'             => 'manual',
                'whois_protection'  => 0,
                'expiry_notification' => 0,
                'notification_days'   => 7,
                'notification_time_unit' => 'Days',
                'is_locked'         => 0,
                'is_for_sale'       => 1,
                'description'       => 'Old project domain - expired and for sale',
            ],
            [
                'domain_name'       => 'newventure.tech',
                'provider_id'       => 1,
                'provider_url'      => 'https://www.namecheap.com',
                'domain_type'       => '.TECH',
                'hosting_id'        => NULL,
                'username'          => '',
                'password'          => '',
                'status'            => 'Pending',
                'date'              => date('Y-m-d'),
                'purchase_date'     => date('Y-m-d'),
                'expiry_date'       => date('Y-m-d', strtotime('+365 days')),
                'days'              => 365,
                'time_unit'         => 'Days',
                'price'             => 25.00,
                'currency_id'       => 'USD',
                'plan'              => 'Starter',
                'registrar_url'     => 'https://www.namecheap.com',
                'registrar_username'=> 'newventure_reg',
                'registrar_password'=> 'reg_pass_555',
                'registrar_status'  => 'Pending',
                'project_id'        => '8',
                'client_id'         => '8',
                'auto_renewal'      => 1,
                'renew'             => 'automatic',
                'whois_protection'  => 1,
                'expiry_notification' => 1,
                'notification_days'   => 14,
                'notification_time_unit' => 'Days',
                'is_locked'         => 0,
                'is_for_sale'       => 0,
                'description'       => 'New domain for upcoming tech venture',
            ],
        ];

        foreach ($domains as $row) {
            $this->db->insert('tbldomains', $row);
        }

        // ------------------------------------------------------------
        // 5. Billing orders (tbl_billing_orders)
        // ------------------------------------------------------------
        $billings = [
            [
                'label'       => 'DigitalOcean Droplet - Web Server',
                'value'       => '89.00',
                'type'        => 'Hosting',
                'currency'    => 'USD',
                'renewal_date' => date('Y-m-d', strtotime('+180 days')),
                'expiry_date'  => date('Y-m-d', strtotime('+180 days')),
                'duration'    => '365',
                'time_unit'   => 'Days',
                'renew'       => 'automatic',
                'provider_id' => 3,
                'flag'        => 'Normal',
                'contact_id'  => 1,
                'address'     => 'Dhaka, Bangladesh',
                'contact_phone' => '+8801712345678',
                'contact_email' => 'admin@tic.com.bd',
                'registration_date' => '2025-01-15',
                'buy_date'    => '2025-01-15',
                'status'      => 'Active',
                'billing_cycle' => 'Yearly',
                'last_billed_date' => '2025-01-15',
                'billing_end_date' => date('Y-m-d', strtotime('+180 days')),
                'bill_status' => 'Paid',
                'project_id'  => '3',
                'client_id'   => '1',
                'manage'      => 'Auto',
                'server_details' => '159.89.123.45',
                'login_details'  => 'admin_web / enc_pass_123',
                'enable_expiry_notification' => 1,
                'port'        => '22',
                'description' => 'Monthly billing for production web server',
            ],
            [
                'label'       => 'Namecheap Domain - tic.com.bd',
                'value'       => '15.00',
                'type'        => 'Domain',
                'currency'    => 'USD',
                'renewal_date' => date('Y-m-d', strtotime('+120 days')),
                'expiry_date'  => date('Y-m-d', strtotime('+120 days')),
                'duration'    => '365',
                'time_unit'   => 'Days',
                'renew'       => 'automatic',
                'provider_id' => 1,
                'flag'        => 'Normal',
                'contact_id'  => 1,
                'address'     => 'Dhaka, Bangladesh',
                'contact_phone' => '+8801712345678',
                'contact_email' => 'admin@tic.com.bd',
                'registration_date' => '2024-01-01',
                'buy_date'    => '2024-01-01',
                'status'      => 'Active',
                'billing_cycle' => 'Yearly',
                'last_billed_date' => '2025-01-01',
                'billing_end_date' => date('Y-m-d', strtotime('+120 days')),
                'bill_status' => 'Paid',
                'project_id'  => '3',
                'client_id'   => '1',
                'manage'      => 'Auto',
                'enable_expiry_notification' => 1,
                'description' => 'Annual domain renewal for primary domain',
            ],
            [
                'label'       => 'SSL Certificate - RapidSSL',
                'value'       => '199.00',
                'type'        => 'SSL',
                'currency'    => 'USD',
                'renewal_date' => date('Y-m-d', strtotime('+25 days')),
                'expiry_date'  => date('Y-m-d', strtotime('+25 days')),
                'duration'    => '365',
                'time_unit'   => 'Days',
                'renew'       => 'manual',
                'provider_id' => 2,
                'flag'        => 'High Priority',
                'contact_id'  => 4,
                'address'     => 'Dhaka, Bangladesh',
                'contact_phone' => '+8801711111111',
                'contact_email' => 'mohasin@tic.com.bd',
                'registration_date' => '2025-05-01',
                'buy_date'    => '2025-05-01',
                'status'      => 'Active',
                'billing_cycle' => 'Yearly',
                'last_billed_date' => '2025-05-01',
                'billing_end_date' => date('Y-m-d', strtotime('+25 days')),
                'bill_status' => 'Unpaid',
                'project_id'  => '5',
                'client_id'   => '6',
                'manage'      => 'Manual',
                'enable_expiry_notification' => 1,
                'enable_reminders_weekend' => 1,
                'description' => 'SSL certificate for client portal - needs renewal',
            ],
            [
                'label'       => 'AWS EC2 - Backup Server',
                'value'       => '360.00',
                'type'        => 'Hosting',
                'currency'    => 'EUR',
                'renewal_date' => date('Y-m-d', strtotime('-10 days')),
                'expiry_date'  => date('Y-m-d', strtotime('-10 days')),
                'duration'    => '90',
                'time_unit'   => 'Days',
                'renew'       => 'manual',
                'provider_id' => 4,
                'flag'        => 'Urgent',
                'contact_id'  => 1,
                'address'     => 'Frankfurt, Germany',
                'contact_phone' => '+8801712345678',
                'contact_email' => 'admin@tic.com.bd',
                'registration_date' => '2025-09-01',
                'buy_date'    => '2025-09-01',
                'status'      => 'Expired',
                'billing_cycle' => 'Quarterly',
                'last_billed_date' => '2025-09-01',
                'billing_end_date' => date('Y-m-d', strtotime('-10 days')),
                'bill_status' => 'Overdue',
                'project_id'  => '3',
                'client_id'   => '1',
                'manage'      => 'Manual',
                'server_details' => '18.156.34.56',
                'login_details'  => 'bkup_admin / enc_pass_101',
                'enable_expiry_notification' => 1,
                'port'        => '22',
                'description' => 'Backup server billing - payment overdue',
            ],
            [
                'label'       => 'License Renewal - cPanel',
                'value'       => '42.00',
                'type'        => 'License',
                'currency'    => 'USD',
                'renewal_date' => date('Y-m-d', strtotime('+60 days')),
                'expiry_date'  => date('Y-m-d', strtotime('+60 days')),
                'duration'    => '30',
                'time_unit'   => 'Days',
                'renew'       => 'automatic',
                'provider_id' => 3,
                'flag'        => 'Normal',
                'contact_id'  => 5,
                'address'     => 'Dhaka, Bangladesh',
                'contact_phone' => '+8801711112222',
                'contact_email' => 'azharul@tic.com.bd',
                'registration_date' => '2025-11-01',
                'buy_date'    => '2025-11-01',
                'status'      => 'Pending',
                'billing_cycle' => 'Monthly',
                'last_billed_date' => '2025-11-01',
                'billing_end_date' => date('Y-m-d', strtotime('+60 days')),
                'bill_status' => 'Unpaid',
                'project_id'  => '2',
                'client_id'   => '2',
                'manage'      => 'Auto',
                'enable_expiry_notification' => 1,
                'description' => 'Monthly cPanel license for staging server',
            ],
        ];

        foreach ($billings as $row) {
            $row['created_at'] = date('Y-m-d H:i:s');
            $row['updated_at'] = date('Y-m-d H:i:s');
            $this->db->insert('tbl_billing_orders', $row);
        }
    }

    public function down()
    {
        // Remove billing orders
        $this->db->truncate('tbl_billing_orders');

        // Remove domains
        $this->db->truncate('tbldomains');

        // Remove hostings
        $this->db->truncate('tblserver_hostings');

        // Remove supporting options
        $this->db->truncate('tbl_billing_manage');
        $this->db->truncate('tbl_billing_bill_status');
        $this->db->truncate('tbl_billing_status');
        $this->db->truncate('tbl_billing_flags');
        $this->db->truncate('tbl_billing_types');
        $this->db->truncate('tbl_domain_status');
        $this->db->truncate('tbl_hosting_plans');
        $this->db->truncate('tbl_server_types');
        $this->db->truncate('tblhostings');

        // Remove only the providers we added (last 5)
        $this->db->where('id >', 0);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(5);
        // Actually, let's delete them more safely
        $this->db->where('provider_name IN ( \'Namecheap\', \'GoDaddy\', \'DigitalOcean\', \'Amazon Web Services\', \'Cloudflare\' )');
        $this->db->delete('tblproviders');
    }
}
