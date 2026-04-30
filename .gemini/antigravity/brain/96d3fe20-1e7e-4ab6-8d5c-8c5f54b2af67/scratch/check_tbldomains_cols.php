<?php
$mysqli = new mysqli("localhost", "root", "", "tic_crm");
if ($mysqli->connect_error) {
    die("Connect Error (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
}

$result = $mysqli->query("DESCRIBE tbldomains");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}
print_r($columns);

$mysqli->close();
