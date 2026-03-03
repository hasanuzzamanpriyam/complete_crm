<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'tic_crm';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    $stmt = $pdo->query("DESCRIBE tbl_hub_payments");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "JSON_START\n";
    echo json_encode($cols);
    echo "\nJSON_END\n";
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
