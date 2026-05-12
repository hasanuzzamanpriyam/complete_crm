<?php
// I'll just use raw SQL to insert demo data.
$mysqli = new mysqli("localhost", "root", "", "tic_crm");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// 1. Get or Create Category
$mysqli->query("INSERT IGNORE INTO tbl_customer_group (type, customer_group) VALUES ('tasks', 'Server Management')");
$category_id = $mysqli->insert_id;
if (!$category_id) {
    $res = $mysqli->query("SELECT customer_group_id FROM tbl_customer_group WHERE customer_group='Server Management' AND type='tasks'");
    $category_id = $res->fetch_assoc()['customer_group_id'];
}

// 2. Create Master Tasks
$mysqli->query("INSERT INTO tbl_task (task_name, task_description, task_status, module, category_id, permission, task_start_date) 
                VALUES ('Domain Management', 'Master task for all domain related tasks.', 'not_started', 'server_management_master', $category_id, 'all', CURDATE())");
$domain_master_id = $mysqli->insert_id;

$mysqli->query("INSERT INTO tbl_task (task_name, task_description, task_status, module, category_id, permission, task_start_date) 
                VALUES ('Hosting Management', 'Master task for all hosting related tasks.', 'not_started', 'server_management_master', $category_id, 'all', CURDATE())");
$hosting_master_id = $mysqli->insert_id;

// 3. Create Demo Sub-tasks for Domains
for ($i = 1; $i <= 3; $i++) {
    $domain_name = "demo-domain-$i.com";
    $mysqli->query("INSERT INTO tbl_task (task_name, task_description, task_status, sub_task_id, category_id, permission, task_start_date, due_date) 
                    VALUES ('Recent Expiration: $domain_name', 'Demo task for recent expiration.', 'not_started', $domain_master_id, $category_id, 'all', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY))");
    $mysqli->query("INSERT INTO tbl_task (task_name, task_description, task_status, sub_task_id, category_id, permission, task_start_date, due_date) 
                    VALUES ('Future Expiration: $domain_name', 'Demo task for future expiration.', 'not_started', $domain_master_id, $category_id, 'all', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR))");
}

// 4. Create Demo Sub-tasks for Hostings
for ($i = 1; $i <= 2; $i++) {
    $hosting_title = "Demo Hosting Plan $i";
    $mysqli->query("INSERT INTO tbl_task (task_name, task_description, task_status, sub_task_id, category_id, permission, task_start_date, due_date) 
                    VALUES ('Recent Expiration: $hosting_title', 'Demo task for recent expiration.', 'not_started', $hosting_master_id, $category_id, 'all', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 5 DAY))");
    $mysqli->query("INSERT INTO tbl_task (task_name, task_description, task_status, sub_task_id, category_id, permission, task_start_date, due_date) 
                    VALUES ('Future Expiration: $hosting_title', 'Demo task for future expiration.', 'not_started', $hosting_master_id, $category_id, 'all', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR))");
}

echo "Demo data created successfully.\n";
echo "Domain Master ID: $domain_master_id\n";
echo "Hosting Master ID: $hosting_master_id\n";

$mysqli->close();
?>
