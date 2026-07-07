<?php
// TIC CRM Demo Data Seeder
// Run: php demo/seeds/seed_data.php
// Must be run AFTER seed.sql has created users and reference data

$m = new mysqli('localhost', 'root', '', 'tic_crm_demo');
if ($m->connect_error) die("Connection failed: " . $m->connect_error);

echo "--- Seeding Demo Data ---\n\n";

// ============ CLEAN EXISTING DEMO DATA ============
$m->query("DELETE FROM tbl_client WHERE name LIKE '%Corp%' OR name LIKE '%Inc%' OR name LIKE '%Solutions%'");
$m->query("DELETE FROM tbl_project WHERE project_name LIKE '%ERP%' OR project_name LIKE '%Portal%' OR project_name LIKE '%App%' OR project_name LIKE '%Migration%'");
$m->query("DELETE FROM tbl_task WHERE task_name LIKE '%Requirement%' OR task_name LIKE '%Configuration%'");
$m->query("DELETE FROM tbl_saved_items WHERE item_name IN ('Consulting Service','Software License','Hosting Service','Mobile Development','Data Migration','API Integration','Training Session','Maintenance')");
$m->query("DELETE FROM tbl_invoices WHERE reference_no LIKE 'INV-%'");
$m->query("DELETE FROM tbl_leads WHERE lead_name IN ('TechStart Inc.','GreenEnergy Ltd','MediCare Plus')");
$m->query("DELETE FROM tbl_opportunities WHERE opportunity_name IN ('ERP Expansion','Support Contract')");
$m->query("DELETE FROM tbl_tickets WHERE ticket_code LIKE 'TCK-%'");
$m->query("DELETE FROM tbl_attendance WHERE date_in >= DATE_SUB(CURDATE(), INTERVAL 15 DAY)");
$m->query("DELETE FROM tbl_leave_application WHERE reason LIKE '%leave%' OR reason LIKE '%vacation%'");
$m->query("DELETE FROM tbl_estimates WHERE reference_no LIKE 'EST-%'");
$m->query("DELETE FROM tbl_proposals WHERE reference_no LIKE 'PRO-%'");
$m->query("DELETE FROM tbl_transactions WHERE name IN ('Client payment','Office rent','Consulting fee','Software subscription','Maintenance contract')");
$m->query("DELETE FROM tbl_goal_tracking WHERE subject LIKE '%Sales%' OR subject LIKE '%Customer%'");
$m->query("DELETE FROM tbl_bug WHERE bug_title LIKE '%redirect%' OR bug_title LIKE '%broken%'");
$m->query("DELETE FROM tbl_training WHERE training_name LIKE '%Onboarding%'");
$m->query("DELETE FROM tbl_suppliers WHERE name IN ('OfficeMax Supplies','TechWare Solutions','CleanCo Services')");
echo "Cleaned existing demo data\n";

// ============ GET DEMO USER IDS ============
$users = [];
$r = $m->query("SELECT user_id, username FROM tbl_users WHERE username LIKE 'demo_%'");
while ($row = $r->fetch_assoc()) {
    $users[$row['username']] = $row['user_id'];
}
$admin_id = $users['demo_admin'];
$manager_id = $users['demo_manager'];
$employee_id = $users['demo_employee'];
echo "Demo Admin ID: $admin_id\n";
echo "Demo Manager ID: $manager_id\n";
echo "Demo Employee ID: $employee_id\n";

// ============ TBL_CLIENT (3 sample clients) ============
echo "\n--- Seeding Clients ---\n";
$client_ids = [];
$clients = [
    ['Acme Corporation', 'info@acme.com', 'Acme Corp is a leading manufacturing company.', 'https://acme.com', '+1-555-0100', '123 Industrial Blvd', 'New York', '10001', 'USA'],
    ['Globex Inc.', 'contact@globex.com', 'Globex Inc. provides global logistics services.', 'https://globex.com', '+1-555-0200', '456 Commerce Dr', 'Los Angeles', '90001', 'USA'],
    ['Initech Solutions', 'hello@initech.com', 'Initech Solutions is a software development firm.', 'https://initech.com', '+1-555-0300', '789 Tech Park', 'San Francisco', '94105', 'USA'],
];
foreach ($clients as $c) {
    list($name, $email, $note, $website, $phone, $address, $city, $zip, $country) = $c;
    $m->query("INSERT INTO tbl_client (name, email, short_note, website, phone, address, city, zipcode, country, client_status, date_added) 
               VALUES ('$name', '$email', '$note', '$website', '$phone', '$address', '$city', '$zip', '$country', 1, NOW())");
    $client_ids[] = $m->insert_id;
    echo "  Client: $name (ID: {$m->insert_id})\n";
}

// ============ TBL_PROJECT (4 sample projects) ============
echo "\n--- Seeding Projects ---\n";
$project_ids = [];
$projects = [
    ['ERP Implementation', $client_ids[0], '2025-01-15', '2025-06-30', 'in_progress', 'Complete ERP system deployment for Acme Corp'],
    ['Logistics Portal', $client_ids[1], '2025-02-01', '2025-08-15', 'in_progress', 'Online logistics tracking portal for Globex'],
    ['Mobile App Dev', $client_ids[2], '2025-03-01', '2025-09-30', 'in_progress', 'Cross-platform mobile application for Initech'],
    ['Data Migration', $client_ids[0], '2025-04-01', '2025-07-15', 'completed', 'Legacy data migration to new ERP system'],
];
foreach ($projects as $p) {
    list($name, $cid, $start, $end, $status, $desc) = $p;
    $m->query("INSERT INTO tbl_project (project_name, client_id, start_date, end_date, project_status, description, created_by, notify_client, timer_status, created_time, progress, calculate_progress, demo_url)
               VALUES ('$name', $cid, '$start', '$end', '$status', '$desc', $admin_id, 'Yes', 'off', NOW(), '0', 'auto', '')");
    $project_ids[] = $m->insert_id;
    echo "  Project: $name (ID: {$m->insert_id})\n";
}

// ============ TBL_TASK (10 sample tasks) ============
echo "\n--- Seeding Tasks ---\n";
$task_data = [
    ['Requirement Gathering', $project_ids[0], 'not_started', $admin_id, 'Gather detailed requirements from Acme stakeholders'],
    ['System Configuration', $project_ids[0], 'in_progress', $manager_id, 'Configure ERP modules per requirements'],
    ['User Training', $project_ids[0], 'not_started', $employee_id, 'Train end users on the new system'],
    ['API Integration', $project_ids[1], 'in_progress', $manager_id, 'Integrate logistics API with tracking portal'],
    ['UI/UX Design', $project_ids[2], 'completed', $admin_id, 'Design mobile app wireframes and prototypes'],
    ['Backend Development', $project_ids[2], 'in_progress', $manager_id, 'Develop REST API for mobile application'],
    ['Data Cleansing', $project_ids[3], 'completed', $employee_id, 'Clean and validate legacy data before migration'],
    ['Testing & QA', $project_ids[1], 'not_started', $employee_id, 'Perform QA testing on logistics portal'],
    ['Frontend Development', $project_ids[2], 'not_started', $admin_id, 'Develop React Native frontend components'],
    ['Deployment', $project_ids[0], 'not_started', $manager_id, 'Deploy ERP to production environment'],
];
foreach ($task_data as $t) {
    list($name, $pid, $status, $assignee, $desc) = $t;
    $due = date('Y-m-d', strtotime('+' . rand(10, 90) . ' days'));
    $m->query("INSERT INTO tbl_task (project_id, task_name, task_description, task_start_date, due_date, task_status, task_progress, created_by, task_created_date, report_to)
               VALUES ($pid, '$name', '$desc', CURDATE(), '$due', '$status', 0, $assignee, NOW(), $assignee)");
    echo "  Task: $name (assigned to user $assignee)\n";
}

// ============ TBL_SAVED_ITEMS (8 sample products/services) ============
echo "\n--- Seeding Saved Items ---\n";
$items = [
    ['Consulting Service', 150.00, 'Professional consulting services'],
    ['Software License', 500.00, 'Annual software license fee'],
    ['Hosting Service', 99.00, 'Cloud hosting and infrastructure'],
    ['Mobile Development', 2500.00, 'Mobile application development'],
    ['Data Migration', 3000.00, 'Data migration and transformation'],
    ['API Integration', 1800.00, 'Third-party API integration'],
    ['Training Session', 1200.00, 'On-site training session (full day)'],
    ['Maintenance', 200.00, 'Monthly maintenance and support'],
];
foreach ($items as $i) {
    list($name, $cost, $desc) = $i;
    $m->query("INSERT INTO tbl_saved_items (item_name, unit_cost, item_desc, barcode_symbology, customer_group_id) VALUES ('$name', $cost, '$desc', 'code39', 0)");
    echo "  Item: $name (\$$cost)\n";
}

// ============ TBL_INVOICES (3 sample invoices) ============
echo "\n--- Seeding Invoices ---\n";
$inv_statuses = ['Unpaid', 'Paid', 'partially_paid'];
for ($i = 0; $i < 3; $i++) {
    $cid = $client_ids[$i];
    $ref = 'INV-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
    $amount = rand(2000, 8000);
    $status = $inv_statuses[$i];
    $date = date('Y-m-d', strtotime('-' . rand(5, 60) . ' days'));
    $due = date('Y-m-d', strtotime($date . ' +30 days'));
    $m->query("INSERT INTO tbl_invoices (reference_no, client_id, invoice_date, due_date, currency, status, user_id, notes, show_quantity_as, date_saved, warehouse_id)
               VALUES ('$ref', $cid, '$date', '$due', 'USD', '$status', $admin_id, 'Demo invoice', 'unit', NOW(), 1)");
    $inv_id = $m->insert_id;
    echo "  Invoice: $ref (client $cid, \$$amount, $status)\n";

    for ($j = 0; $j < 2; $j++) {
        $qty = rand(1, 5);
        $unit_cost = rand(100, 500);
        $total = $qty * $unit_cost;
        $item_name = $items[array_rand($items)][0];
        $m->query("INSERT INTO tbl_items (invoices_id, item_name, quantity, unit_cost, total_cost, item_desc) 
                   VALUES ($inv_id, '$item_name', $qty, $unit_cost, $total, 'Demo item')");
    }

    if ($status == 'Paid') {
        $pay_date = date('Y-m-d', strtotime($date . ' +15 days'));
        $m->query("INSERT INTO tbl_payments (invoices_id, amount, payment_method, payment_date, month_paid, year_paid, paid_by, account_id)
                   VALUES ($inv_id, $amount, 'Bank Transfer', '$pay_date', '" . date('m') . "', '" . date('Y') . "', $admin_id, 1)");
        $m->query("INSERT INTO tbl_transactions (invoices_id, name, type, category_id, amount, date, paid_by, payment_methods_id, status, account_id, added_by, reference, notes, create_date)
                   VALUES ($inv_id, 'Payment for $ref', 'Income', 1, $amount, '$pay_date', $admin_id, 'Bank', 'paid', 1, $admin_id, '$ref', 'Payment received', NOW())");
    }
}

// ============ TBL_LEADS (3) ============
echo "\n--- Seeding Leads ---\n";
$lead_data = [
    ['TechStart Inc.', 'contact@techstart.com', '555-1000', 'TechStart Inc', '123 Startup St', 'San Francisco', 'John Doe', 'New software startup looking for ERP', '@techstart'],
    ['GreenEnergy Ltd', 'info@greenenergy.com', '555-2000', 'GreenEnergy Ltd', '456 Green Ave', 'Portland', 'Jane Smith', 'Renewable energy company needs CRM', '@greenenergy'],
    ['MediCare Plus', 'hello@medicare.com', '555-3000', 'MediCare Plus', '789 Health Blvd', 'Boston', 'Bob Wilson', 'Healthcare provider wants patient portal', '@medicare'],
];
foreach ($lead_data as $l) {
    list($name, $email, $phone, $org, $addr, $city, $contact, $notes, $twitter) = $l;
    $m->query("INSERT INTO tbl_leads (lead_name, organization, address, city, contact_name, email, phone, mobile, facebook, notes, skype, twitter, created_time)
               VALUES ('$name', '$org', '$addr', '$city', '$contact', '$email', '$phone', '$phone', '', '$notes', '', '$twitter', NOW())");
    echo "  Lead: $name\n";
}

// ============ TBL_OPPORTUNITIES (2) ============
echo "\n--- Seeding Opportunities ---\n";
$opps = [
    ['ERP Expansion', 'Prospecting', '50000', 'Expand ERP modules for existing client'],
    ['Support Contract', 'Negotiation', '24000', 'Annual support and maintenance contract'],
];
foreach ($opps as $o) {
    list($name, $stage, $revenue, $notes) = $o;
    $close = date('Y-m-d', strtotime('+' . rand(15, 60) . ' days'));
    $m->query("INSERT INTO tbl_opportunities (opportunity_name, stages, probability, close_date, expected_revenue, next_action, next_action_date, notes, opportunities_state_reason_id, new_link)
               VALUES ('$name', '$stage', '50', '$close', '$revenue', 'Follow up call', '$close', '$notes', 0, '')");
    echo "  Opportunity: $name\n";
}

// ============ TBL_TICKETS (3) ============
echo "\n--- Seeding Tickets ---\n";
$ticket_data = [
    ['Login issue', 1, 'Cannot log into the portal after password reset', 'open'],
    ['Feature request', 2, 'Would like to add dark mode to the dashboard', 'in_progress'],
    ['Payment error', 3, 'Getting error 500 when processing payments', 'open'],
];
foreach ($ticket_data as $idx => $t) {
    list($subject, $dept, $body, $status) = $t;
    $code = 'TCK-' . str_pad($idx + 1, 4, '0', STR_PAD_LEFT);
    $m->query("INSERT INTO tbl_tickets (ticket_code, subject, body, status, departments_id, reporter, priority, created, last_reply)
               VALUES ('$code', '$subject', '$body', '$status', $dept, {$client_ids[$idx]}, 'Medium', NOW(), NOW())");
    echo "  Ticket: $code\n";
}

// ============ TBL_ATTENDANCE (recent 10 work days) ============
echo "\n--- Seeding Attendance ---\n";
$demo_users = [$admin_id, $manager_id, $employee_id];
for ($d = 10; $d >= 0; $d--) {
    $date = date('Y-m-d', strtotime("-$d days"));
    if (date('N', strtotime($date)) >= 6) continue;
    foreach ($demo_users as $uid) {
        $m->query("INSERT INTO tbl_attendance (user_id, date_in, date_out, attendance_status, clocking_status)
                   VALUES ($uid, '$date', '$date', 1, 1)");
    }
}
echo "  Attendance records for last 10 work days\n";

// ============ TBL_LEAVE_APPLICATION (3) ============
echo "\n--- Seeding Leave Applications ---\n";
$leave_data = [
    [$employee_id, 1, 'Sick leave - feeling unwell', 'single_day', date('Y-m-d', strtotime('+5 days')), null, 1],
    [$manager_id, 2, 'Annual vacation', 'multiple_days', date('Y-m-d', strtotime('+10 days')), date('Y-m-d', strtotime('+15 days')), 1],
    [$admin_id, 1, 'Personal leave', 'single_day', date('Y-m-d', strtotime('+3 days')), null, 2],
];
foreach ($leave_data as $l) {
    list($uid, $cat, $reason, $type, $start, $end, $status) = $l;
    $end_col = $end ? "'$end'" : "NULL";
    $m->query("INSERT INTO tbl_leave_application (user_id, leave_category_id, reason, leave_type, leave_start_date, leave_end_date, application_status, application_date)
               VALUES ($uid, $cat, '$reason', '$type', '$start', $end_col, $status, NOW())");
}
echo "  3 leave applications\n";

// ============ TBL_HOLIDAY (5) ============
echo "\n--- Seeding Holidays ---\n";
foreach ([
    ['New Year', date('Y') . '-01-01', date('Y') . '-01-01', '#ff0000'],
    ['Independence Day', date('Y') . '-07-04', date('Y') . '-07-04', '#0000ff'],
    ['Labor Day', date('Y') . '-09-01', date('Y') . '-09-01', '#00aa00'],
    ['Thanksgiving', date('Y') . '-11-27', date('Y') . '-11-28', '#ff8800'],
    ['Christmas', date('Y') . '-12-25', date('Y') . '-12-26', '#cc0000'],
] as $h) {
    list($name, $start, $end, $color) = $h;
    $m->query("INSERT INTO tbl_holiday (event_name, start_date, end_date, color, description) VALUES ('$name', '$start', '$end', '$color', 'Public holiday - $name')");
}
echo "  5 holidays\n";

// ============ TBL_ESTIMATES (2) ============
echo "\n--- Seeding Estimates ---\n";
foreach (['draft', 'sent'] as $i => $est_status) {
    $ref = 'EST-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
    $date = date('Y-m-d', strtotime('-' . rand(1, 30) . ' days'));
    $m->query("INSERT INTO tbl_estimates (reference_no, client_id, estimate_date, due_date, currency, status, user_id, show_quantity_as, date_saved)
               VALUES ('$ref', {$client_ids[$i]}, '$date', '" . date('Y-m-d', strtotime($date . ' +30 days')) . "', 'USD', '$est_status', $admin_id, 'unit', NOW())");
    echo "  Estimate: $ref\n";
}

// ============ TBL_PROPOSALS (1) ============
echo "\n--- Seeding Proposals ---\n";
$m->query("INSERT INTO tbl_proposals (reference_no, subject, module, module_id, proposal_date, proposal_month, proposal_year, due_date, currency, status, user_id, notes, total_tax)
           VALUES ('PRO-0001', 'Website Redesign Proposal', 'client', {$client_ids[0]}, CURDATE(), '" . date('m') . "', '" . date('Y') . "', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'USD', 'draft', $admin_id, 'Proposal notes', '0')");
echo "  1 proposal\n";

// ============ TBL_TRANSACTIONS (5) ============
echo "\n--- Seeding Transactions ---\n";
$txn_types = [['Income', 'Client payment', 1], ['Expense', 'Office rent', 2], ['Income', 'Consulting fee', 1], ['Expense', 'Software subscription', 2], ['Income', 'Maintenance contract', 1]];
foreach ($txn_types as $idx => $t) {
    list($type, $name, $cat) = $t;
    $amount = rand(500, 5000);
    $date = date('Y-m-d', strtotime("-$idx days"));
    $m->query("INSERT INTO tbl_transactions (name, type, category_id, amount, date, status, account_id, added_by, reference, notes, create_date, permission)
               VALUES ('$name', '$type', $cat, $amount, '$date', 'paid', 1, $admin_id, 'REF-$idx', 'Transaction notes', NOW(), 'all')");
}
echo "  5 transactions\n";

// ============ TBL_GOAL_TRACKING (2) ============
echo "\n--- Seeding Goals ---\n";
foreach ([
    ['Q1 Sales Target', 1, 0, date('Y') . '-01-01', date('Y') . '-03-31'],
    ['Customer Satisfaction', 1, 0, date('Y') . '-01-01', date('Y') . '-12-31'],
] as $g) {
    list($name, $type_id, $achieve, $start, $end) = $g;
    $m->query("INSERT INTO tbl_goal_tracking (subject, goal_type_id, achievement, start_date, end_date, description, account_id)
               VALUES ('$name', $type_id, $achieve, '$start', '$end', 'Demo goal description', $admin_id)");
}
echo "  2 goals\n";

// ============ TBL_BUG (2) ============
echo "\n--- Seeding Bugs ---\n";
$bug_data = [
    ['Login redirect loop', $project_ids[0], 'confirmed', 'High', 'Users get stuck in redirect loop after login'],
    ['Mobile UI broken', $project_ids[2], 'unconfirmed', 'Medium', 'Dashboard layout broken on small screens'],
];
foreach ($bug_data as $b) {
    list($title, $pid, $status, $priority, $desc) = $b;
    $m->query("INSERT INTO tbl_bug (project_id, bug_title, bug_description, bug_status, priority, severity, reporter, task_id, created_time)
               VALUES ($pid, '$title', '$desc', '$status', '$priority', 'major', $admin_id, 0, NOW())");
}
echo "  2 bugs\n";

// ============ TBL_TRAINING (1) ============
echo "\n--- Seeding Training ---\n";
$m->query("INSERT INTO tbl_training (training_name, remarks, user_id, assigned_by, vendor_name, start_date, finish_date, status)
           VALUES ('New Employee Onboarding', 'Comprehensive onboarding program for new hires covering company policies and systems.', $employee_id, $admin_id, 'In-house', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 5 DAY), 0)");
echo "  1 training program\n";

// ============ TBL_SUPPLIERS (3) ============
echo "\n--- Seeding Suppliers ---\n";
foreach ([
    ['OfficeMax Supplies', '555-9000', 'orders@officemax.com', '123 Supply St, Chicago'],
    ['TechWare Solutions', '555-9001', 'sales@techware.com', '456 Hardware Ave, Dallas'],
    ['CleanCo Services', '555-9002', 'info@cleanco.com', '789 Service Rd, Miami'],
] as $s) {
    list($name, $phone, $email, $address) = $s;
    $m->query("INSERT INTO tbl_suppliers (name, mobile, email, address) VALUES ('$name', '$phone', '$email', '$address')");
}
echo "  3 suppliers\n";

// ============ TBL_WAREHOUSE (2) ============
echo "\n--- Seeding Warehouse ---\n";
$m->query("INSERT IGNORE INTO tbl_warehouse (warehouse_name, address) VALUES ('Main Warehouse', '100 Industrial Park'), ('Secondary Warehouse', '200 Storage Lane')");
echo "  2 warehouses\n";

echo "\n=== Seeding Complete! ===\n";
