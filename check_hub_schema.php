<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'tic_crm';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    echo "Columns in tbl_hub_payments:\n";
    $stmt = $pdo->query("DESCRIBE tbl_hub_payments");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
