<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'tic_crm';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    $tables = [
        'tbl_api_clients',
        'tbl_api_tokens',
        'tbl_payment_gateways',
        'tbl_payments',
        'tbl_payment_transactions',
        'tbl_payment_logs',
        'tbl_webhook_logs',
        'tbl_refunds'
    ];

    echo "Checking Payment Hub Tables:\n";
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "[OK] $table exists.\n";
            // Check specific columns added in later migrations
            if ($table === 'tbl_webhook_logs') {
                $cols = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'retry_count'")->rowCount();
                echo "     - retry_count: " . ($cols > 0 ? "YES" : "NO") . "\n";
            }
            if ($table === 'tbl_payment_logs') {
                $cols = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'ip_address'")->rowCount();
                echo "     - ip_address: " . ($cols > 0 ? "YES" : "NO") . "\n";
            }
        } else {
            echo "[MISSING] $table\n";
        }
    }
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
