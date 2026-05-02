<?php
define('BASEPATH', __DIR__);
// Instead of full CI instantiation, I'll just write a quick query to simulate what Hosting_model does, then verify.
$mysqli = new mysqli('127.0.0.1', 'root', '', 'tic_crm');
$today = date('Y-m-d');
$mysqli->query("UPDATE tblserver_hostings SET status = 'Expired' WHERE expiry_date < '$today' AND status != 'Expired'");

$res = $mysqli->query('SELECT id, title, expiry_date, status FROM tblserver_hostings WHERE id = 21');
if($res) print_r($res->fetch_assoc());
