<?php
/**
 * Server Management Reseed Script
 *
 * Clears all server management data (hostings, domains, billing, providers)
 * and their related tasks/calendar events, then seeds 10 fresh items.
 *
 * Usage: php seeder_reseed.php
 */

$mysqli = new mysqli('localhost', 'root', '', 'tic_crm');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error . "\n");
}

echo "Connected to database.\n";

// Helper: generate random permission JSON
function random_permission() {
    $users = range(1, 10);
    shuffle($users);
    $count = rand(1, 3);
    $perm = [];
    for ($i = 0; $i < $count; $i++) {
        $uid = $users[$i];
        $actions = ['view'];
        if (rand(0, 1)) $actions[] = 'edit';
        if (rand(0, 1)) $actions[] = 'delete';
        $perm[$uid] = $actions;
    }
    return json_encode($perm);
}

// Helper: pick random from array
function pick($arr) {
    return $arr[array_rand($arr)];
}

// =========================================================================
// 1. DELETE server-management-related tasks + cleanup
// =========================================================================
echo "\n--- Cleaning up server management tasks ---\n";

// Find task IDs linked to server management
$result = $mysqli->query("SELECT task_id FROM tbl_task WHERE module IN ('server_hosting', 'domain')");
$task_ids = [];
while ($row = $result->fetch_assoc()) {
    $task_ids[] = $row['task_id'];
}

if (!empty($task_ids)) {
    $ids_str = implode(',', $task_ids);

    // Delete from tbl_tasks_timer
    $mysqli->query("DELETE FROM tbl_tasks_timer WHERE task_id IN ($ids_str)");
    echo "  Deleted " . $mysqli->affected_rows . " timer records.\n";

    // Delete from tbl_task_comment (module = 'tasks' referencing these tasks)
    $mysqli->query("DELETE FROM tbl_task_comment WHERE module = 'tasks' AND module_field_id IN ($ids_str)");
    echo "  Deleted " . $mysqli->affected_rows . " task comments.\n";

    // Delete from tbl_task_chat* tables (dynamic per-task)
    $chat_tables = $mysqli->query("SHOW TABLES LIKE 'tbl_task_chat_%'");
    while ($ct = $chat_tables->fetch_row()) {
        $tname = $ct[0];
        $suffix = str_replace('tbl_task_chat_', '', $tname);
        if (in_array((int)$suffix, $task_ids)) {
            $mysqli->query("DROP TABLE IF EXISTS `$tname`");
            echo "  Dropped table $tname.\n";
        }
    }

    // Delete the tasks themselves
    $mysqli->query("DELETE FROM tbl_task WHERE task_id IN ($ids_str)");
    echo "  Deleted " . $mysqli->affected_rows . " renewal tasks.\n";
} else {
    echo "  No existing renewal tasks found.\n";
}

// =========================================================================
// 2. TRUNCATE server management tables
// =========================================================================
echo "\n--- Clearing server management tables ---\n";

$mysqli->query("SET FOREIGN_KEY_CHECKS = 0");

$truncate_tables = [
    'tbl_billing_orders',
    'tbldomains',
    'tblserver_hostings',
    'tbl_billing_manage',
    'tbl_billing_bill_status',
    'tbl_billing_status',
    'tbl_billing_flags',
    'tbl_billing_types',
    'tbl_domain_status',
    'tbl_hosting_plans',
    'tbl_server_types',
    'tblhostings',
    'tblproviders',
];

foreach ($truncate_tables as $table) {
    $mysqli->query("TRUNCATE TABLE `$table`");
    echo "  Truncated $table.\n";
}

$mysqli->query("SET FOREIGN_KEY_CHECKS = 1");

// =========================================================================
// 3. SEED supporting options
// =========================================================================
echo "\n--- Seeding supporting options ---\n";

$now = date('Y-m-d H:i:s');

// Hosting name types
$hosting_names = ['Shared Hosting', 'VPS Hosting', 'Dedicated Server', 'Cloud Hosting', 'Reseller Hosting'];
foreach ($hosting_names as $name) {
    $mysqli->query("INSERT INTO tblhostings (hosting_name, status, created_at) VALUES ('$name', 'Active', '$now')");
}
echo "  Inserted " . count($hosting_names) . " hosting name types.\n";

// Server types
$server_types = ['Linux', 'Windows', 'Docker'];
foreach ($server_types as $st) {
    $mysqli->query("INSERT INTO tbl_server_types (name, created_at) VALUES ('$st', '$now')");
}
echo "  Inserted " . count($server_types) . " server types.\n";

// Hosting plans
$hosting_plans = ['Starter', 'Business', 'Enterprise', 'Ultimate'];
foreach ($hosting_plans as $hp) {
    $mysqli->query("INSERT INTO tbl_hosting_plans (name, created_at) VALUES ('$hp', '$now')");
}
echo "  Inserted " . count($hosting_plans) . " hosting plans.\n";

// Domain statuses
$domain_statuses = ['Active', 'Expired', 'Expiring', 'Pending', 'Transferring', 'Cancelled'];
foreach ($domain_statuses as $ds) {
    $mysqli->query("INSERT INTO tbl_domain_status (status_name, created_at) VALUES ('$ds', '$now')");
}
echo "  Inserted " . count($domain_statuses) . " domain statuses.\n";

// Billing types
$billing_types = ['Hosting', 'Domain', 'SSL', 'Maintenance', 'License'];
foreach ($billing_types as $bt) {
    $mysqli->query("INSERT INTO tbl_billing_types (name, created_at) VALUES ('$bt', '$now')");
}
echo "  Inserted " . count($billing_types) . " billing types.\n";

// Billing flags
$billing_flags = ['Urgent', 'Normal', 'High Priority'];
foreach ($billing_flags as $bf) {
    $mysqli->query("INSERT INTO tbl_billing_flags (name, created_at) VALUES ('$bf', '$now')");
}
echo "  Inserted " . count($billing_flags) . " billing flags.\n";

// Billing statuses
$billing_statuses = ['Active', 'Pending', 'Expired'];
foreach ($billing_statuses as $bs) {
    $mysqli->query("INSERT INTO tbl_billing_status (name, created_at) VALUES ('$bs', '$now')");
}
echo "  Inserted " . count($billing_statuses) . " billing statuses.\n";

// Bill statuses
$bill_statuses = ['Paid', 'Unpaid', 'Overdue', 'Cancelled'];
foreach ($bill_statuses as $bs) {
    $mysqli->query("INSERT INTO tbl_billing_bill_status (name, created_at) VALUES ('$bs', '$now')");
}
echo "  Inserted " . count($bill_statuses) . " bill statuses.\n";

// Manage options
$manage_opts = ['Manual', 'Auto'];
foreach ($manage_opts as $mo) {
    $mysqli->query("INSERT INTO tbl_billing_manage (name, created_at) VALUES ('$mo', '$now')");
}
echo "  Inserted " . count($manage_opts) . " manage options.\n";

// =========================================================================
// 4. SEED 4 providers
// =========================================================================
echo "\n--- Seeding 4 providers ---\n";

$providers = [
    [
        'provider_name' => 'Namecheap',
        'provider_url'  => 'https://www.namecheap.com',
        'provider_type' => 'Domain Registrar',
        'status'        => 'Active',
        'description'   => 'Domain registration and hosting services',
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
    $row['created_at'] = $now;
    $cols = implode(', ', array_keys($row));
    $vals = "'" . implode("', '", array_map(function($v) use ($mysqli) {
        return $mysqli->real_escape_string((string)$v);
    }, array_values($row))) . "'";
    $mysqli->query("INSERT INTO tblproviders ($cols) VALUES ($vals)");
    echo "  Provider: {$row['provider_name']} -> ID {$mysqli->insert_id}\n";
}

// =========================================================================
// 5. SEED 2 hostings
// =========================================================================
echo "\n--- Seeding 2 hostings ---\n";

$hostings = [
    [
        'title'          => 'Production App Server',
        'server_name'    => 'prod-app01',
        'hostname'       => 'prod-app01.tic.com.bd',
        'provider_id'    => 2,
        'provider_url'   => 'https://www.digitalocean.com',
        'server_type'    => 'Linux',
        'server_location'=> 'Singapore',
        'ip_address'     => '159.89.100.50',
        'cpanel_url'     => 'https://prod-app01.tic.com.bd:2083',
        'username'       => 'prod_admin',
        'password'       => 'enc_prod_pass_001',
        'purchase_date'  => date('Y-m-d', strtotime('-6 months')),
        'expiry_date'    => date('Y-m-d', strtotime('+180 days')),
        'days'           => 365,
        'time_unit'      => 'Days',
        'renew'          => 'automatic',
        'currency_id'    => 'USD',
        'price'          => 89.00,
        'project_id'     => '3',
        'client_id'      => '1',
        'status'         => 'Active',
        'ssl_certificate'=> 1,
        'ssl_expiry_date'=> date('Y-m-d', strtotime('+300 days')),
        'ssl_type'       => "Let's Encrypt",
        'expiry_notification' => 1,
        'notification_days'   => 14,
        'notification_time_unit' => 'Days',
        'description'    => 'Main production application server hosting client apps',
    ],
    [
        'title'          => 'Dev Sandbox Server',
        'server_name'    => 'dev-sandbox01',
        'hostname'       => 'dev-sandbox.tic.com.bd',
        'provider_id'    => 3,
        'provider_url'   => 'https://aws.amazon.com',
        'server_type'    => 'Linux',
        'server_location'=> 'Mumbai',
        'ip_address'     => '13.126.200.75',
        'cpanel_url'     => '',
        'username'       => 'dev_admin',
        'password'       => 'enc_dev_pass_002',
        'purchase_date'  => date('Y-m-d', strtotime('-3 months')),
        'expiry_date'    => date('Y-m-d', strtotime('+90 days')),
        'days'           => 365,
        'time_unit'      => 'Days',
        'renew'          => 'manual',
        'currency_id'    => 'USD',
        'price'          => 45.00,
        'project_id'     => '2',
        'client_id'      => '2',
        'status'         => 'Active',
        'ssl_certificate'=> 0,
        'expiry_notification' => 1,
        'notification_days'   => 7,
        'notification_time_unit' => 'Days',
        'description'    => 'Development sandbox for testing and staging',
    ],
];

$hosting_ids = [];
foreach ($hostings as $row) {
    $row['created_at'] = $now;
    $cols = implode(', ', array_keys($row));
    $vals = "'" . implode("', '", array_map(function($v) use ($mysqli) {
        return $mysqli->real_escape_string((string)$v);
    }, array_values($row))) . "'";
    $mysqli->query("INSERT INTO tblserver_hostings ($cols) VALUES ($vals)");
    $id = $mysqli->insert_id;
    $hosting_ids[] = $id;
    echo "  Hosting: {$row['title']} -> ID $id\n";
}

// =========================================================================
// 6. SEED 2 domains
// =========================================================================
echo "\n--- Seeding 2 domains ---\n";

$domains = [
    [
        'domain_name'           => 'myapp.com',
        'provider_id'           => 1,
        'provider_url'          => 'https://www.namecheap.com',
        'domain_type'           => '.COM',
        'hosting_id'            => $hosting_ids[0],
        'username'              => 'myapp_admin',
        'password'              => 'dom_pass_001',
        'status'                => 'Active',
        'date'                  => date('Y-m-d', strtotime('-1 year')),
        'purchase_date'         => date('Y-m-d', strtotime('-1 year')),
        'expiry_date'           => date('Y-m-d', strtotime('+120 days')),
        'days'                  => 365,
        'time_unit'             => 'Days',
        'price'                 => 15.00,
        'currency_id'           => 'USD',
        'plan'                  => 'Business',
        'registrar_url'         => 'https://www.namecheap.com',
        'registrar_username'    => 'myapp_reg',
        'registrar_password'    => 'reg_pass_001',
        'registrar_status'      => 'Active',
        'project_id'            => '3',
        'client_id'             => '1',
        'auto_renewal'          => 1,
        'renew'                 => 'automatic',
        'whois_protection'      => 1,
        'expiry_notification'   => 1,
        'notification_days'     => 30,
        'notification_time_unit'=> 'Days',
        'is_locked'             => 0,
        'is_for_sale'           => 0,
        'description'           => 'Primary application domain',
    ],
    [
        'domain_name'           => 'myservice.io',
        'provider_id'           => 1,
        'provider_url'          => 'https://www.namecheap.com',
        'domain_type'           => '.IO',
        'hosting_id'            => $hosting_ids[1],
        'username'              => 'svc_admin',
        'password'              => 'dom_pass_002',
        'status'                => 'Active',
        'date'                  => date('Y-m-d', strtotime('-6 months')),
        'purchase_date'         => date('Y-m-d', strtotime('-6 months')),
        'expiry_date'           => date('Y-m-d', strtotime('+30 days')),
        'days'                  => 365,
        'time_unit'             => 'Days',
        'price'                 => 39.00,
        'currency_id'           => 'USD',
        'plan'                  => 'Enterprise',
        'registrar_url'         => 'https://www.namecheap.com',
        'registrar_username'    => 'svc_reg',
        'registrar_password'    => 'reg_pass_002',
        'registrar_status'      => 'Active',
        'project_id'            => '5',
        'client_id'             => '3',
        'auto_renewal'          => 0,
        'renew'                 => 'manual',
        'whois_protection'      => 1,
        'expiry_notification'   => 1,
        'notification_days'     => 14,
        'notification_time_unit'=> 'Days',
        'is_locked'             => 0,
        'is_for_sale'           => 0,
        'description'           => 'SaaS service platform domain - expiring soon',
    ],
];

$domain_ids = [];
foreach ($domains as $row) {
    $row['created_at'] = $now;
    $row['updated_at'] = $now;
    $cols = implode(', ', array_keys($row));
    $vals = "'" . implode("', '", array_map(function($v) use ($mysqli) {
        return $mysqli->real_escape_string((string)$v);
    }, array_values($row))) . "'";
    $mysqli->query("INSERT INTO tbldomains ($cols) VALUES ($vals)");
    $id = $mysqli->insert_id;
    $domain_ids[] = $id;
    echo "  Domain: {$row['domain_name']} -> ID $id\n";
}

// =========================================================================
// 7. SEED 2 billings
// =========================================================================
echo "\n--- Seeding 2 billings ---\n";

$billings = [
    [
        'label'       => 'DigitalOcean Droplet - App Server',
        'value'       => '89.00',
        'type'        => 'Hosting',
        'currency'    => 'USD',
        'renewal_date' => date('Y-m-d', strtotime('+180 days')),
        'expiry_date'  => date('Y-m-d', strtotime('+180 days')),
        'duration'    => '365',
        'time_unit'   => 'Days',
        'renew'       => 'automatic',
        'provider_id' => 2,
        'flag'        => 'Normal',
        'contact_id'  => 1,
        'address'     => 'Dhaka, Bangladesh',
        'contact_phone' => '+8801712345678',
        'contact_email' => 'admin@tic.com.bd',
        'registration_date' => date('Y-m-d', strtotime('-6 months')),
        'buy_date'    => date('Y-m-d', strtotime('-6 months')),
        'status'      => 'Active',
        'billing_cycle' => 'Yearly',
        'last_billed_date' => date('Y-m-d', strtotime('-6 months')),
        'billing_end_date' => date('Y-m-d', strtotime('+180 days')),
        'bill_status' => 'Paid',
        'project_id'  => '3',
        'client_id'   => '1',
        'manage'      => 'Auto',
        'server_details' => '159.89.100.50',
        'login_details'  => 'prod_admin / enc_prod_pass_001',
        'enable_expiry_notification' => 1,
        'port'        => '22',
        'description' => 'Yearly billing for production app server',
    ],
    [
        'label'       => 'Namecheap Domain - myapp.com',
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
        'registration_date' => date('Y-m-d', strtotime('-1 year')),
        'buy_date'    => date('Y-m-d', strtotime('-1 year')),
        'status'      => 'Active',
        'billing_cycle' => 'Yearly',
        'last_billed_date' => date('Y-m-d', strtotime('-1 year')),
        'billing_end_date' => date('Y-m-d', strtotime('+120 days')),
        'bill_status' => 'Paid',
        'project_id'  => '3',
        'client_id'   => '1',
        'manage'      => 'Auto',
        'enable_expiry_notification' => 1,
        'description' => 'Annual domain renewal for myapp.com',
    ],
];

foreach ($billings as $row) {
    $row['created_at'] = $now;
    $row['updated_at'] = $now;
    $cols = implode(', ', array_keys($row));
    $vals = "'" . implode("', '", array_map(function($v) use ($mysqli) {
        return $mysqli->real_escape_string((string)$v);
    }, array_values($row))) . "'";
    $mysqli->query("INSERT INTO tbl_billing_orders ($cols) VALUES ($vals)");
    echo "  Billing: {$row['label']} -> ID {$mysqli->insert_id}\n";
}

// =========================================================================
// 8. CREATE renewal tasks for hostings and domains (4 tasks)
// =========================================================================
echo "\n--- Creating renewal tasks with random permissions ---\n";

// Tasks for hostings
for ($i = 0; $i < count($hosting_ids); $i++) {
    $hid = $hosting_ids[$i];
    $h_title = $hostings[$i]['title'];
    $created_by = rand(1, 10);
    $permission = random_permission();
    $due_date = $hostings[$i]['expiry_date'];

    $task_data = [
        'task_name'        => 'Renew Hosting: ' . $h_title,
        'task_description' => 'Automatic renewal task for hosting: ' . $h_title,
        'task_start_date'  => date('Y-m-d'),
        'due_date'         => $due_date,
        'task_created_date'=> $now,
        'task_status'      => 'not_started',
        'task_progress'    => 0,
        'timer_status'     => 'off',
        'logged_time'      => '0',
        'module'           => 'server_hosting',
        'module_field_id'  => $hid,
        'created_by'       => $created_by,
        'permission'       => $permission,
        'hourly_rate'      => '0.00',
        'billable'         => 'No',
        'index_no'         => $i + 1,
        'milestones_order' => 0,
    ];

    $cols = implode(', ', array_keys($task_data));
    $vals = "'" . implode("', '", array_map(function($v) use ($mysqli) {
        return $mysqli->real_escape_string((string)$v);
    }, array_values($task_data))) . "'";
    $mysqli->query("INSERT INTO tbl_task ($cols) VALUES ($vals)");
    echo "  Task: {$task_data['task_name']} -> ID {$mysqli->insert_id} (permission: $permission)\n";
}

// Tasks for domains
for ($i = 0; $i < count($domain_ids); $i++) {
    $did = $domain_ids[$i];
    $d_name = $domains[$i]['domain_name'];
    $created_by = rand(1, 10);
    $permission = random_permission();
    $due_date = $domains[$i]['expiry_date'];

    $task_data = [
        'task_name'        => 'Renew Domain: ' . $d_name,
        'task_description' => 'Automatic renewal task for domain: ' . $d_name,
        'task_start_date'  => date('Y-m-d'),
        'due_date'         => $due_date,
        'task_created_date'=> $now,
        'task_status'      => 'not_started',
        'task_progress'    => 0,
        'timer_status'     => 'off',
        'logged_time'      => '0',
        'module'           => 'domain',
        'module_field_id'  => $did,
        'created_by'       => $created_by,
        'permission'       => $permission,
        'hourly_rate'      => '0.00',
        'billable'         => 'No',
        'index_no'         => count($hosting_ids) + $i + 1,
        'milestones_order' => 0,
    ];

    $cols = implode(', ', array_keys($task_data));
    $vals = "'" . implode("', '", array_map(function($v) use ($mysqli) {
        return $mysqli->real_escape_string((string)$v);
    }, array_values($task_data))) . "'";
    $mysqli->query("INSERT INTO tbl_task ($cols) VALUES ($vals)");
    echo "  Task: {$task_data['task_name']} -> ID {$mysqli->insert_id} (permission: $permission)\n";
}

// =========================================================================
// DONE
// =========================================================================
echo "\n========================================\n";
echo "  SEEDING COMPLETE!\n";
echo "========================================\n";
echo "  Providers:  4\n";
echo "  Hostings:   2 (with 2 renewal tasks)\n";
echo "  Domains:    2 (with 2 renewal tasks)\n";
echo "  Billings:   2\n";
echo "  Tasks:      4 (random permissions assigned)\n";
echo "----------------------------------------\n";
echo "  Total: 10 items seeded!\n";
echo "========================================\n";

$mysqli->close();
