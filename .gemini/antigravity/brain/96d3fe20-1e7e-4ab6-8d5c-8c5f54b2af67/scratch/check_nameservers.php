<?php
$mysqli = new mysqli("localhost", "root", "", "tic_crm");
if ($mysqli->connect_error) {
    die("Connect Error (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
}

$res = $mysqli->query("SHOW TABLES LIKE '%nameserver%'");
while ($row = $res->fetch_array()) {
    $table = $row[0];
    echo "Structure of $table:\n";
    $result = $mysqli->query("DESCRIBE $table");
    while ($field = $result->fetch_assoc()) {
        print_r($field);
    }
}
$mysqli->close();
