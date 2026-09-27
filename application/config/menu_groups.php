<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sidebar group map — the single source of truth for A2-7.
 *
 * Array order IS the sidebar display order. This file is not autoloaded, so
 * load it first, exactly like ai_registry.php and piprapay.php:
 *
 *     $this->load->config('menu_groups', false, true);
 *     $groups = config_item('menu_groups');
 *
 * There is no menu_group ordering column because admins do not need to reorder
 * groups, only to assign a menu to one.
 *
 * 'tools' is the documented fallback: an unknown or empty menu_group renders
 * here rather than producing an unlabelled section.
 */
$config['menu_groups'] = [
    'crm'      => 'CRM & Clients',
    'projects' => 'Projects & Tasks',
    'hr'       => 'HR & Payroll',
    'finance'  => 'Finance & Billing',
    'tools'    => 'Tools & Admin',
    'admin'    => 'Super Admin',
];
