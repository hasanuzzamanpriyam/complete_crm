<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'tic_crm';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    echo "Updating tbl_hub_payments with missing columns...\n";
    
    $cols = $pdo->query("DESCRIBE tbl_hub_payments")->fetchAll(PDO::FETCH_COLUMN);
    
    $to_add = [
        'gateway_id' => "INT(11) NULL AFTER project_id",
        'customer_name' => "VARCHAR(100) NULL AFTER currency",
        'customer_email' => "VARCHAR(100) NULL AFTER customer_name",
        'updated_at' => "DATETIME NULL AFTER created_at"
    ];
    
    foreach ($to_add as $col => $definition) {
        if (!in_array($col, $cols)) {
            $pdo->exec("ALTER TABLE tbl_hub_payments ADD `$col` $definition");
            echo "Column '$col' added successfully.\n";
        } else {
            echo "Column '$col' already exists.\n";
        }
    }
    
    echo "Database sync complete.\n";
    
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
