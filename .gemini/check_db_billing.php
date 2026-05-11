<?php
$mysqli = new mysqli('localhost', 'root', '', 'tic_crm');
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
$result = $mysqli->query("DESCRIBE tbl_billing_orders");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
$mysqli->close();
