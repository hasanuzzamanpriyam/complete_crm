<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'tic_crm';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // Check if tbl_hub_payments already exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_hub_payments'");
    if ($stmt->rowCount() > 0) {
        echo "tbl_hub_payments already exists. Skipping rename.\n";
    } else {
        $pdo->exec("RENAME TABLE tbl_external_transactions TO tbl_hub_payments");
        echo "Table tbl_external_transactions renamed to tbl_hub_payments successfully.\n";
    }
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
