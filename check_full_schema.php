<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'tic_crm';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    $tables = ['tbl_payments', 'tbl_external_transactions', 'tbl_phub_payments'];
    
    foreach ($tables as $table) {
        echo "--- Checking Table: $table ---\n";
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $cols = $pdo->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cols as $c) {
                echo "Field: {$c['Field']}, Type: {$c['Type']}, Key: {$c['Key']}, Extra: {$c['Extra']}\n";
            }
        } else {
            echo "Table does not exist.\n";
        }
        echo "\n";
    }
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
