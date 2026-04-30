<?php
$mysqli = new mysqli("localhost", "root", "", "tic_crm");
if ($mysqli->connect_error) {
    die("Connect Error (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
}

$mysqli->query("ALTER TABLE tbldomains ADD COLUMN nameservers TEXT AFTER hosting_id");
if ($mysqli->error) {
    echo "Error: " . $mysqli->error . "\n";
} else {
    echo "Column 'nameservers' added to 'tbldomains' successfully.\n";
}

$mysqli->close();
