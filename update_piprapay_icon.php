<?php
// Update PipraPay Icon Path in Database
// This script updates the icon path for PipraPay to point to the correct logo location

// Database connection details
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'tic_crm';

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL update query
$sql = "UPDATE tbl_online_payment SET icon = 'asset/images/payment_logo/piprapay.png' WHERE gateway_name = 'PipraPay'";

// Execute query
if ($conn->query($sql) === TRUE) {
    echo "✓ PipraPay icon path updated successfully!<br>";
    echo "Affected rows: " . $conn->affected_rows . "<br><br>";

    // Verify the update
    $verify_sql = "SELECT gateway_name, icon FROM tbl_online_payment WHERE gateway_name = 'PipraPay'";
    $result = $conn->query($verify_sql);

    if ($result->num_rows > 0) {
        echo "<strong>Verification:</strong><br>";
        while ($row = $result->fetch_assoc()) {
            echo "Gateway: " . $row["gateway_name"] . "<br>";
            echo "Icon Path: " . $row["icon"] . "<br>";
        }
    }
} else {
    echo "Error updating record: " . $conn->error;
}

$conn->close();
