<?php
// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'tic_crm';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Disable strict mode for this session to allow handling 0000-00-00
$conn->query("SET SESSION sql_mode = ''");

// Delete rows where timestamp is zero or invalid
$sql = "DELETE FROM biometric_attendance_logs WHERE timestamp = '0000-00-00 00:00:00' OR timestamp < '2000-01-01' OR timestamp IS NULL";

if ($conn->query($sql) === TRUE) {
    echo "Successfully deleted " . $conn->affected_rows . " invalid log entries.";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
