<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'tic_crm');
$res = $mysqli->query('SELECT id, title, expiry_date, status FROM tblserver_hostings');
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
