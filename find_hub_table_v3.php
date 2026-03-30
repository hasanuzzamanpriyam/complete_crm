<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'tic_crm';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $t) {
        $stmt_cols = $pdo->query("DESCRIBE `$t` ");
        $cols = $stmt_cols->fetchAll(PDO::FETCH_COLUMN);
        
        if (in_array('external_reference', $cols)) {
            echo "MATCH FOUND: Table '$t' has external_reference.\n";
            echo "Columns: " . implode(', ', $cols) . "\n\n";
        }
    }
    
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
