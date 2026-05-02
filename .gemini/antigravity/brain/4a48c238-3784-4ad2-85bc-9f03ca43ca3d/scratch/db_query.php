<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'tic_crm');
$res = $mysqli->query('SELECT DISTINCT status FROM tbldomains');
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
$res = $mysqli->query('SELECT * FROM tbl_domain_status');
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
$res = $mysqli->query('SELECT * FROM tblserver_hostings LIMIT 1');
if($res) print_r($res->fetch_assoc());
