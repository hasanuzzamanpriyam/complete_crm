<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Universal Dynamic Tool Registry - metadata overrides.
 *
 * The registry is auto-discovered from the live database schema
 * (information_schema). This file adjusts what the discovery produces:
 *
 *   ai_registry_exclude   tables never exposed to the AI (system/log/junction).
 *   ai_registry_read_only tables exposed with list/get only (no create/update/delete).
 *   ai_registry_overrides per-table metadata merged on top of discovered columns.
 */

$config['ai_registry_exclude'] = array(
    // Core platform / installer
    'installer',
    'tbl_migrations',
    'tbl_modules',
    'tbl_sessions',
    'tbl_user_api_sessions',

    // AI assistant internal
    'tbl_ai_messages',
    'tbl_ai_prompts',
    'tbl_ai_providers',
    'tbl_ai_sessions',

    // Activity / audit / notifications
    'tbl_activities',
    'tbl_audit_logs',
    'tbl_notifications',
    'tbl_email_queue',
    'tbl_outgoing_emails',

    // Key/value + UI config (handled by dedicated set_config/get_config tools)
    'tbl_config',
    'tbl_admin_config',
    'tbl_dashboard',
    'tbl_menu',
    'tbl_client_menu',
    'tbl_form',
    'tbl_templates',
    'tbl_days',
    'tbl_card_config',
    'tbl_online_payment',
    'tbl_payment_projects',

    // Permissions / RBAC junctions
    'tbl_user_role',
    'tbl_user_permissions',
    'tbl_client_role',

    // Private chat / team chat internals
    'tbl_private_chat',
    'tbl_private_chat_users',
    'tbl_private_chat_messages',
    'tbl_team_members',
    'tbl_team_messages',

    // File registry + attachment/comment children (handled by parent modules)
    'tbl_files',
    'tbl_uploaded_files',
    'tbl_attachments',
    'tbl_attachments_files',
    'tbl_task_comment',

    // Legacy / bridge / sync data
    'bl_teams',
    'bl_team_members',
    'biometric_attendance_logs',
    'biometric_employee_mapping',
    'tbl_woocommerce_assigned',
    'tbl_woocommerce_customers',
    'tbl_woocommerce_orders',
    'tbl_woocommerce_products',
    'tbl_woocommerce_stores',
    'tbl_woocommerce_summary',
    'piprapay_transactions',
    'tbl_piprapay_gateway_stats',
    'tbl_piprapay_payment_logs',
    'tbl_piprapay_transactions',
    'tbl_external_transactions',
    'tbl_user_api_sessions',

    // Junction / child tables with no standalone value
    'tbl_warehouses_products',
    'tbl_transfer_item',
    'tbl_transfer_itemlist',
    'tbl_team_members',
    'tbl_team_messages',
    'tbl_pinaction',
    'tbl_spreadsheet_hash_share',
    'tbl_spreadsheet_my_folder',
    'tbl_spreadsheet_related',

    // Legacy duplicate lookup tables (real tables: tbl_priority, tbl_contract_type)
    'tbl_priorities',
    'tbl_contracts_types',

    // Accounting line items (handled via parent journal/voucher hooks)
    'tbl_journal_items',
    'tbl_voucher_items',

    // Payroll child rows (handled via salary template / payment hooks)
    'tbl_salary_payment_details',
    'tbl_salary_payment_allowance',
    'tbl_salary_payment_deduction',

    // Timesync internal logs
    'tbl_timesync_config_log',
    'tbl_timesync_user_settings_log',
    'tbl_screenshot_deletions',
);

$config['ai_registry_read_only'] = array(
    // Desktop / timesync telemetry
    'tbl_screenshots',
    'tbl_desktop_time_entries',
    'tbl_desktop_app_usage',
    'tbl_timesync_time_balances',

    // Attendance clock history is machine-generated
    'tbl_clock_history',
);

$config['ai_registry_overrides'] = array(
    'tbl_priorities' => array(
        'singular' => 'priority',
        'plural'   => 'priorities',
        'label_column' => 'priority',
    ),
    'tbl_priority' => array(
        'singular' => 'priority_setting',
        'plural'   => 'priority_settings',
    ),
    'tbl_status' => array(
        'singular' => 'status',
        'plural'   => 'statuses',
    ),
    'tbl_departments' => array(
        'aliases' => array('department'),
    ),
    'tbl_designations' => array(
        'aliases' => array('designation'),
    ),
    'tbl_mettings' => array(
        'singular' => 'meeting',
        'plural'   => 'meetings',
        'aliases'  => array('meeting'),
    ),
    'tbl_calls' => array(
        'singular' => 'call_log',
        'plural'   => 'call_logs',
        'aliases'  => array('call'),
    ),
    'tbl_milestones' => array(
        'aliases' => array('milestone'),
    ),
    'tbl_job_appliactions' => array(
        'singular' => 'job_application',
        'plural'   => 'job_applications',
        'aliases'  => array('application', 'job_application'),
    ),
    'tbl_hourly_rate' => array(
        'singular' => 'hourly_rate',
        'plural'   => 'hourly_rates',
    ),
    'tbl_advance_salary' => array(
        'singular' => 'advance_salary',
        'plural'   => 'advance_salaries',
    ),
    'tbl_employee_payroll' => array(
        'singular' => 'employee_payroll',
        'plural'   => 'employee_payrolls',
    ),
    'tbl_salary_template' => array(
        'singular' => 'salary_template',
        'plural'   => 'salary_templates',
    ),
    'tbl_salary_payment' => array(
        'singular' => 'salary_payment',
        'plural'   => 'salary_payments',
    ),
    'tbl_salary_payslip' => array(
        'singular' => 'salary_payslip',
        'plural'   => 'salary_payslips',
    ),
    'tbl_journals' => array(
        'singular' => 'journal',
        'plural'   => 'journals',
    ),
    'tbl_chart_of_accounts' => array(
        'singular' => 'chart_of_account',
        'plural'   => 'chart_of_accounts',
        'aliases'  => array('account', 'chart_of_accounts'),
    ),
    'tbl_account_sub_type' => array(
        'singular' => 'account_sub_type',
        'plural'   => 'account_sub_types',
    ),
    'tbl_account_type' => array(
        'singular' => 'account_type',
        'plural'   => 'account_types',
    ),
    'tbl_accounts' => array(
        'singular' => 'account',
        'plural'   => 'accounts',
        'aliases'  => array('bank_account'),
    ),
    'tbl_transactions' => array(
        'singular' => 'transaction',
        'plural'   => 'transactions',
    ),
    'tbl_transfer' => array(
        'singular' => 'transfer',
        'plural'   => 'transfers',
    ),
    'tbl_warehouse' => array(
        'singular' => 'warehouse',
        'plural'   => 'warehouses',
    ),
    'tbl_stock' => array(
        'singular' => 'stock_item',
        'plural'   => 'stock_items',
        'aliases'  => array('stock'),
    ),
    'tbl_suppliers' => array(
        'singular' => 'supplier',
        'plural'   => 'suppliers',
    ),
    'tbl_purchases' => array(
        'singular' => 'purchase',
        'plural'   => 'purchases',
    ),
    'tbl_refunds' => array(
        'singular' => 'refund',
        'plural'   => 'refunds',
    ),
    'tbl_return_stock' => array(
        'singular' => 'return_stock',
        'plural'   => 'return_stock_entries',
        'aliases'  => array('return_stock'),
    ),
    'tbl_server_types' => array(
        'singular' => 'server_type',
        'plural'   => 'server_types',
    ),
    'tbl_hosting_plans' => array(
        'singular' => 'hosting_plan',
        'plural'   => 'hosting_plans',
    ),
    'tbl_nameservers' => array(
        'singular' => 'nameserver',
        'plural'   => 'nameservers',
    ),
    'tbl_dns_providers' => array(
        'singular' => 'dns_provider',
        'plural'   => 'dns_providers',
    ),
    'tbl_domain_types' => array(
        'singular' => 'domain_type',
        'plural'   => 'domain_types',
    ),
    'tbl_domain_status' => array(
        'singular' => 'domain_status',
        'plural'   => 'domain_statuses',
    ),
    'tbl_billing_orders' => array(
        'singular' => 'billing_order',
        'plural'   => 'billing_orders',
    ),
    'tbl_billing_types' => array(
        'singular' => 'billing_type',
        'plural'   => 'billing_types',
    ),
    'tbl_billing_flags' => array(
        'singular' => 'billing_flag',
        'plural'   => 'billing_flags',
    ),
    'tbl_billing_status' => array(
        'singular' => 'billing_status',
        'plural'   => 'billing_statuses',
    ),
    'tbl_billing_bill_status' => array(
        'singular' => 'billing_bill_status',
        'plural'   => 'billing_bill_statuses',
    ),
    'tbl_billing_manage' => array(
        'singular' => 'billing_management',
        'plural'   => 'billing_management',
        'aliases'  => array('billing_manage'),
    ),
    'tblserver_hostings' => array(
        'singular' => 'server_hosting',
        'plural'   => 'server_hostings',
        'aliases'  => array('hosting', 'server_hosting'),
        'table'    => 'tblserver_hostings',
    ),
    'tblhostings' => array(
        'singular' => 'hosting',
        'plural'   => 'hostings',
        'table'    => 'tblhostings',
    ),
    'tbldomains' => array(
        'singular' => 'domain',
        'plural'   => 'domains',
        'table'    => 'tbldomains',
    ),
    'tblproviders' => array(
        'singular' => 'provider',
        'plural'   => 'providers',
        'table'    => 'tblproviders',
    ),
    'tbl_contract_renewals' => array(
        'singular' => 'contract_renewal',
        'plural'   => 'contract_renewals',
    ),
    'tbl_contracts_types' => array(
        'singular' => 'contract_type',
        'plural'   => 'contract_types',
        'aliases'  => array('contract_type'),
    ),
    'tbl_holiday' => array(
        'singular' => 'holiday',
        'plural'   => 'holidays',
    ),
    'tbl_leave_category' => array(
        'singular' => 'leave_category',
        'plural'   => 'leave_categories',
    ),
    'tbl_leave_application' => array(
        'singular' => 'leave_application',
        'plural'   => 'leave_applications',
    ),
    'tbl_overtime' => array(
        'singular' => 'overtime',
        'plural'   => 'overtime_entries',
        'aliases'  => array('overtime'),
    ),
    'tbl_working_days' => array(
        'singular' => 'working_day',
        'plural'   => 'working_days',
    ),
    'tbl_workplace' => array(
        'singular' => 'workplace',
        'plural'   => 'workplaces',
    ),
    'tbl_expense_category' => array(
        'singular' => 'expense_category',
        'plural'   => 'expense_categories',
    ),
    'tbl_income_category' => array(
        'singular' => 'income_category',
        'plural'   => 'income_categories',
    ),
    'tbl_payment_methods' => array(
        'singular' => 'payment_method',
        'plural'   => 'payment_methods',
    ),
    'tbl_custom_field' => array(
        'singular' => 'custom_field',
        'plural'   => 'custom_fields',
    ),
    'tbl_tags' => array(
        'singular' => 'tag',
        'plural'   => 'tags',
    ),
    'tbl_allowed_ip' => array(
        'singular' => 'allowed_ip',
        'plural'   => 'allowed_ips',
    ),
    'tbl_award_rule' => array(
        'singular' => 'award_rule',
        'plural'   => 'award_rules',
    ),
    'tbl_award_program' => array(
        'singular' => 'award_program',
        'plural'   => 'award_programs',
    ),
    'tbl_currencies' => array(
        'singular' => 'currency',
        'plural'   => 'currencies',
    ),
    'tbl_countries' => array(
        'singular' => 'country',
        'plural'   => 'countries',
    ),
    'tbl_languages' => array(
        'singular' => 'language',
        'plural'   => 'languages',
    ),
    'tbl_locales' => array(
        'singular' => 'locale',
        'plural'   => 'locales',
    ),
    'tbl_opportunities_state_reason' => array(
        'singular' => 'opportunity_state_reason',
        'plural'   => 'opportunity_state_reasons',
    ),
    'tbl_lead_status' => array(
        'singular' => 'lead_status',
        'plural'   => 'lead_statuses',
    ),
    'tbl_lead_source' => array(
        'singular' => 'lead_source',
        'plural'   => 'lead_sources',
    ),
    'tbl_customer_group' => array(
        'singular' => 'customer_group',
        'plural'   => 'customer_groups',
    ),
    'tbl_announcements' => array(
        'singular' => 'announcement',
        'plural'   => 'announcements',
    ),
    'tbl_training' => array(
        'singular' => 'training',
        'plural'   => 'trainings',
    ),
    'tbl_goal_tracking' => array(
        'singular' => 'goal_tracking',
        'plural'   => 'goal_tracking_entries',
        'aliases'  => array('goal', 'goal_tracking'),
    ),
    'tbl_performance_indicator' => array(
        'singular' => 'performance_indicator',
        'plural'   => 'performance_indicators',
    ),
    'tbl_performance_apprisal' => array(
        'singular' => 'performance_appraisal',
        'plural'   => 'performance_appraisals',
    ),
    'tbl_promotions' => array(
        'singular' => 'promotion',
        'plural'   => 'promotions',
    ),
    'tbl_resignations' => array(
        'singular' => 'resignation',
        'plural'   => 'resignations',
    ),
    'tbl_terminations' => array(
        'singular' => 'termination',
        'plural'   => 'terminations',
    ),
    'tbl_warnings' => array(
        'singular' => 'warning',
        'plural'   => 'warnings',
    ),
    'tbl_employee_document' => array(
        'singular' => 'employee_document',
        'plural'   => 'employee_documents',
    ),
    'tbl_employee_bank' => array(
        'singular' => 'employee_bank',
        'plural'   => 'employee_banks',
    ),
    'tbl_notes' => array(
        'singular' => 'note',
        'plural'   => 'notes',
    ),
    'tbl_todo' => array(
        'singular' => 'todo',
        'plural'   => 'todos',
    ),
    'tbl_knowledgebase' => array(
        'singular' => 'knowledge_article',
        'plural'   => 'knowledge_articles',
        'aliases'  => array('article', 'kb'),
    ),
    'tbl_kb_category' => array(
        'singular' => 'kb_category',
        'plural'   => 'kb_categories',
        'aliases'  => array('knowledgebase_category'),
    ),
    'tbl_inbox' => array(
        'singular' => 'inbox_mail',
        'plural'   => 'inbox_mails',
    ),
    'tbl_sent' => array(
        'singular' => 'sent_mail',
        'plural'   => 'sent_mails',
    ),
    'tbl_draft' => array(
        'singular' => 'draft_mail',
        'plural'   => 'draft_mails',
    ),
    'tbl_letter_templates' => array(
        'singular' => 'letter_template',
        'plural'   => 'letter_templates',
    ),
    'tbl_letter_variables' => array(
        'singular' => 'letter_variable',
        'plural'   => 'letter_variables',
    ),
    'tbl_generated_letters' => array(
        'singular' => 'generated_letter',
        'plural'   => 'generated_letters',
    ),
    'tbl_interviews' => array(
        'singular' => 'interview',
        'plural'   => 'interviews',
    ),
    'tbl_offer_letters' => array(
        'singular' => 'offer_letter',
        'plural'   => 'offer_letters',
    ),
    'tbl_offer_templates' => array(
        'singular' => 'offer_template',
        'plural'   => 'offer_templates',
    ),
    'tbl_recruitment_skills' => array(
        'singular' => 'recruitment_skill',
        'plural'   => 'recruitment_skills',
    ),
    'tbl_job_skills' => array(
        'singular' => 'job_skill',
        'plural'   => 'job_skills',
    ),
    'tbl_job_circular' => array(
        'singular' => 'job_circular',
        'plural'   => 'job_circulars',
        'aliases'  => array('job'),
    ),
    'tbl_employee_award' => array(
        'singular' => 'employee_award',
        'plural'   => 'employee_awards',
    ),
    'tbl_award_points' => array(
        'singular' => 'award_point',
        'plural'   => 'award_points',
    ),
    'tbl_discipline' => array(
        'singular' => 'discipline',
        'plural'   => 'disciplines',
    ),
    'tbl_offence_category' => array(
        'singular' => 'offence_category',
        'plural'   => 'offence_categories',
    ),
    'tbl_penalty_category' => array(
        'singular' => 'penalty_category',
        'plural'   => 'penalty_categories',
    ),
    'tbl_goal_type' => array(
        'singular' => 'goal_type',
        'plural'   => 'goal_types',
    ),
    'tbl_manufacturer' => array(
        'singular' => 'manufacturer',
        'plural'   => 'manufacturers',
    ),
    'tbl_saved_items' => array(
        'singular' => 'saved_item',
        'plural'   => 'saved_items',
        'aliases'  => array('item', 'product'),
    ),
    'tbl_consultants' => array(
        'singular' => 'consultant',
        'plural'   => 'consultants',
    ),
    'tbl_consultation_slots' => array(
        'singular' => 'consultation_slot',
        'plural'   => 'consultation_slots',
    ),
    'tbl_consultation_appointments' => array(
        'singular' => 'consultation_appointment',
        'plural'   => 'consultation_appointments',
    ),
    'tbl_zoom_meeting' => array(
        'singular' => 'zoom_meeting',
        'plural'   => 'zoom_meetings',
    ),
    'tbl_jitsi_meetings' => array(
        'singular' => 'jitsi_meeting',
        'plural'   => 'jitsi_meetings',
    ),
    'tbl_account_details' => array(
        'singular' => 'account_detail',
        'plural'   => 'account_details',
        'aliases'  => array('profile', 'employee'),
    ),
);

/**
 * Module map: for a given UI context (the last URI segment, e.g. "payroll"
 * from "admin > payroll") list the tables whose tools should be inlined in
 * the prompt. Everything else stays discoverable via search_registry.
 */
$config['ai_registry_module_map'] = array(
    'payroll' => array(
        'tbl_salary_template',
        'tbl_employee_payroll',
        'tbl_advance_salary',
        'tbl_salary_payment',
        'tbl_salary_payslip',
        'tbl_overtime',
        'tbl_hourly_rate',
    ),
    'accounting' => array(
        'tbl_journals',
        'tbl_chart_of_accounts',
        'tbl_accounts',
        'tbl_transactions',
        'tbl_transfer',
        'tbl_client',
        'tbl_suppliers',
    ),
    'server_management' => array(
        'tblserver_hostings',
        'tblhostings',
        'tbldomains',
        'tblproviders',
        'tbl_nameservers',
        'tbl_dns_providers',
        'tbl_server_types',
        'tbl_hosting_plans',
    ),
    'server' => array(
        'tblserver_hostings',
        'tblhostings',
        'tbldomains',
        'tblproviders',
        'tbl_nameservers',
        'tbl_dns_providers',
    ),
    'stock' => array(
        'tbl_stock',
        'tbl_stock_category',
        'tbl_stock_sub_category',
        'tbl_warehouse',
        'tbl_suppliers',
    ),
    'warehouse' => array(
        'tbl_warehouse',
        'tbl_stock',
        'tbl_stock_category',
        'tbl_stock_sub_category',
    ),
    'supplier' => array(
        'tbl_suppliers',
        'tbl_purchases',
        'tbl_return_stock',
    ),
    'purchase' => array(
        'tbl_purchases',
        'tbl_return_stock',
        'tbl_suppliers',
    ),
    'user' => array(
        'tbl_account_details',
        'tbl_departments',
        'tbl_designations',
        'tbl_employee_document',
        'tbl_employee_bank',
    ),
    'attendance' => array(
        'tbl_attendance',
        'tbl_clock',
        'tbl_holiday',
    ),
    'leave_management' => array(
        'tbl_leave_application',
        'tbl_leave_category',
    ),
    'holiday' => array(
        'tbl_holiday',
    ),
    'departments' => array(
        'tbl_departments',
    ),
    'designation' => array(
        'tbl_designations',
    ),
    'hr' => array(
        'tbl_account_details',
        'tbl_departments',
        'tbl_designations',
        'tbl_employee_document',
        'tbl_employee_bank',
        'tbl_promotions',
        'tbl_terminations',
        'tbl_resignations',
        'tbl_warnings',
        'tbl_training',
        'tbl_performance_indicator',
        'tbl_performance_apprisal',
    ),
    'report' => array(
        'tbl_transactions',
        'tbl_accounts',
        'tbl_invoices',
        'tbl_payments',
    ),
    'items' => array(
        'tbl_items',
        'tbl_saved_items',
        'tbl_stock',
        'tbl_stock_category',
        'tbl_stock_sub_category',
    ),
    'settings' => array(
        'tbl_income_category',
        'tbl_expense_category',
        'tbl_payment_methods',
        'tbl_working_days',
        'tbl_currencies',
        'tbl_countries',
        'tbl_languages',
        'tbl_tags',
        'tbl_priorities',
        'tbl_status',
        'tbl_lead_status',
        'tbl_lead_source',
        'tbl_customer_group',
        'tbl_goal_type',
    ),
    'mailbox' => array(
        'tbl_inbox',
        'tbl_sent',
        'tbl_draft',
    ),
    'goal_tracking' => array(
        'tbl_goal_tracking',
        'tbl_goal_type',
    ),
    'consultation' => array(
        'tbl_consultants',
        'tbl_consultation_slots',
        'tbl_consultation_appointments',
    ),
);
