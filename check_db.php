<?php
define('BASEPATH', '1');
require 'application/config/database.php';
$db_info = $db['default'];
$conn = new mysqli($db_info['hostname'], $db_info['username'], $db_info['password'], $db_info['database']);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

echo "--- EMPLOYEE MAPPING ---\n";
$res = $conn->query("SELECT * FROM biometric_employee_mapping");
while($row = $res->fetch_assoc()) {
    print_r($row);
}

echo "\n--- LATEST 5 RAW LOGS ---\n";
$res = $conn->query("SELECT * FROM biometric_attendance_logs ORDER BY id DESC LIMIT 5");
while($row = $res->fetch_assoc()) {
    print_r($row);
}

$conn->close();
