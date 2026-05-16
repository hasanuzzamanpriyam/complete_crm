<?php
/**
 * Fix Active Timers Script
 * 
 * This script ensures that the 'active_timers' entry exists in 'tbl_dashboard'.
 * Upload this to your server's root directory and run it via your browser.
 */

// Load database configuration from CodeIgniter
define('BASEPATH', 'dummy');
define('ENVIRONMENT', 'development');
include_once('application/config/database.php');

$db_config = $db['default'];

$mysqli = new mysqli($db_config['hostname'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "Connected to database: " . $db_config['database'] . "<br>";

// Check if active_timers already exists
$result = $mysqli->query("SELECT * FROM tbl_dashboard WHERE name = 'active_timers'");

if ($result->num_rows > 0) {
    echo "Entry 'active_timers' already exists in tbl_dashboard.<br>";
} else {
    echo "Entry 'active_timers' missing. Attempting to insert...<br>";
    
    // Determine next order_no for report=0
    $order_res = $mysqli->query("SELECT MAX(order_no) as max_order FROM tbl_dashboard WHERE report = 0");
    $order_row = $order_res->fetch_assoc();
    $next_order = ($order_row['max_order'] ?? 0) + 1;
    
    // Check if for_staff column exists
    $cols_res = $mysqli->query("SHOW COLUMNS FROM tbl_dashboard LIKE 'for_staff'");
    $has_for_staff = $cols_res->num_rows > 0;
    
    if ($has_for_staff) {
        $sql = "INSERT INTO tbl_dashboard (name, col, order_no, status, report, for_staff) 
                VALUES ('active_timers', 'col-md-6', $next_order, 1, 0, 1)";
    } else {
        $sql = "INSERT INTO tbl_dashboard (name, col, order_no, status, report) 
                VALUES ('active_timers', 'col-md-6', $next_order, 1, 0)";
    }
    
    if ($mysqli->query($sql)) {
        echo "Successfully inserted 'active_timers' into tbl_dashboard.<br>";
    } else {
        echo "Error inserting record: " . $mysqli->error . "<br>";
    }
}

$mysqli->close();
echo "Done.";
?>
