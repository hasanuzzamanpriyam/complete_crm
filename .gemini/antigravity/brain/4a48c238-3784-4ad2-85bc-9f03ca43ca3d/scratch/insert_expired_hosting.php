<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'tic_crm');
$mysqli->query("INSERT INTO tblserver_hostings (title, provider_id, server_type, status, purchase_date, expiry_date) VALUES ('Expired Test Hosting', 1, 'Test', 'Active', '2026-01-01', '2026-04-30')");
echo "Inserted expired hosting with ID: " . $mysqli->insert_id . "\n";
