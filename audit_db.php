<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'tic_crm';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    echo "--- Search Table Names ---\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $t) {
        if (strpos($t, 'payment') !== false || strpos($t, 'api') !== false || strpos($t, 'webhook') !== false || strpos($t, 'refund') !== false || strpos($t, 'transaction') !== false) {
            echo "Table: $t\n";
            $cols = $pdo->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cols as $c) {
                echo "  - {$c['Field']} ({$c['Type']})\n";
            }
            echo "\n";
        }
    }
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
