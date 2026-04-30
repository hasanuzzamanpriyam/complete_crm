<?php
$mysqli = new mysqli("localhost", "root", "", "tic_crm");
if ($mysqli->connect_error) {
    die("Connect Error (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
}

function print_table_info($mysqli, $table) {
    echo "Structure of $table:\n";
    $result = $mysqli->query("DESCRIBE $table");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            print_r($row);
        }
    } else {
        echo "Table $table not found or error: " . $mysqli->error . "\n";
    }
}

print_table_info($mysqli, "nameserver");
print_table_info($mysqli, "tbl_nameservers");
print_table_info($mysqli, "tbldomains");

$mysqli->close();
