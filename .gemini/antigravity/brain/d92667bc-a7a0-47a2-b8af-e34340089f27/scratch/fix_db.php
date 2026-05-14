<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // Try empty password first

$conn = new mysqli($host, $user, $pass, 'tic_crm');

if ($conn->connect_error) {
    // Try with the password from diff if empty fails
    $pass = 'Igmc@@2026!!';
    $conn = new mysqli($host, $user, $pass, 'tic_crm');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}

$tables = ['tblserver_hostings', 'tbldomains', 'tblproviders', 'tbl_billing_orders'];

foreach ($tables as $table) {
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'permission'");
    if ($result && $result->num_rows == 0) {
        echo "Adding permission column to $table...\n";
        if ($conn->query("ALTER TABLE `$table` ADD `permission` TEXT NULL")) {
            echo "Successfully added to $table.\n";
        } else {
            echo "Error adding to $table: " . $conn->error . "\n";
        }
    } elseif ($result) {
        echo "Column 'permission' already exists in $table.\n";
    } else {
        echo "Error checking table $table: " . $conn->error . "\n";
    }
}

$conn->close();
