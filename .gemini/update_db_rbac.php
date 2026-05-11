<?php
$mysqli = new mysqli('localhost', 'root', '', 'tic_crm');
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

// Add permission column to tbldomains if not exists
$result = $mysqli->query("SHOW COLUMNS FROM tbldomains LIKE 'permission'");
if ($result->num_rows == 0) {
    if ($mysqli->query("ALTER TABLE tbldomains ADD COLUMN permission TEXT AFTER custom_fields")) {
        echo "Added permission column to tbldomains\n";
    } else {
        echo "Error adding column to tbldomains: " . $mysqli->error . "\n";
    }
} else {
    echo "permission column already exists in tbldomains\n";
}

// Add permission column to tblserver_hostings if not exists
$result = $mysqli->query("SHOW COLUMNS FROM tblserver_hostings LIKE 'permission'");
if ($result->num_rows == 0) {
    if ($mysqli->query("ALTER TABLE tblserver_hostings ADD COLUMN permission TEXT AFTER description")) {
        echo "Added permission column to tblserver_hostings\n";
    } else {
        echo "Error adding column to tblserver_hostings: " . $mysqli->error . "\n";
    }
} else {
    echo "permission column already exists in tblserver_hostings\n";
}

$mysqli->close();
