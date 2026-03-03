<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'tic_crm';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    $check = ['tbl_payments', 'tbl_external_transactions', 'tbl_phub_payments', 'tbl_api_clients'];
    
    foreach ($check as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "[YES] $table exists. Columns:\n";
            $cols = $pdo->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cols as $c) {
                echo "  - {$c['Field']} ({$c['Type']})\n";
            }
        } else {
            echo "[NO] $table does not exist.\n";
        }
    }
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
