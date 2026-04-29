<?php
//defined('BASEPATH') OR exit('No direct script access allowed');
$db = mysqli_connect('localhost', 'root', '', 'tic_crm');
if (!$db) { die('Connection failed: ' . mysqli_connect_error()); }

// Check if currency_id column exists
$result = mysqli_query($db, "SHOW COLUMNS FROM tbl_hosting LIKE 'currency_id'");
if (mysqli_num_rows($result) == 0) {
    mysqli_query($db, "ALTER TABLE tbl_hosting ADD COLUMN currency_id INT(11) NULL AFTER plan");
    echo "Added currency_id column\n";
} else {
    echo "currency_id column already exists\n";
}

// Check if price column exists  
$result = mysqli_query($db, "SHOW COLUMNS FROM tbl_hosting LIKE 'price'");
if (mysqli_num_rows($result) == 0) {
    mysqli_query($db, "ALTER TABLE tbl_hosting ADD COLUMN price DECIMAL(10,2) NULL AFTER currency_id");
    echo "Added price column\n";
} else {
    echo "price column already exists\n";
}

mysqli_close($db);
?>
