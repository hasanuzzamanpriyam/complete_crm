<?php
$mysqli = new mysqli("localhost", "root", "", "tic_crm");
if ($mysqli->connect_error) {
    die("Connect Error (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
}

$res = $mysqli->query("SHOW TABLES");
$tables = [];
while ($row = $res->fetch_array()) {
    $tables[] = $row[0];
}

foreach ($tables as $table) {
    if (strpos($table, 'nameserver') !== false) {
        echo "Structure of $table:\n";
        $result = $mysqli->query("DESCRIBE $table");
        while ($row = $result->fetch_assoc()) {
            print_r($row);
        }
    }
}

echo "Structure of tbldomains:\n";
$result = $mysqli->query("DESCRIBE tbldomains");
while ($row = $result->fetch_assoc()) {
    print_r($row);
}

$mysqli->close();
