<?php
//defined('BASEPATH') OR exit('No direct script access allowed');

$db = mysqli_connect('localhost', 'root', '', 'tic_crm');
if (!$db) {
    die('Connection failed: ' . mysqli_connect_error());
}

$sql1 = "CREATE TABLE IF NOT EXISTS `tbl_server_types` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `created_at` datetime NOT NULL,
    PRIMARY KEY (`id`)
)";

$sql2 = "CREATE TABLE IF NOT EXISTS `tbl_hosting_plans` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `created_at` datetime NOT NULL,
    PRIMARY KEY (`id`)
)";

if (mysqli_query($db, $sql1)) {
    echo "Table tbl_server_types created successfully\n";
} else {
    echo "Error creating tbl_server_types: " . mysqli_error($db) . "\n";
}

if (mysqli_query($db, $sql2)) {
    echo "Table tbl_hosting_plans created successfully\n";
} else {
    echo "Error creating tbl_hosting_plans: " . mysqli_error($db) . "\n";
}

mysqli_close($db);
?>
