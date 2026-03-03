<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'tic_crm';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    echo "CLEAN DESCRIBE tbl_hub_payments:\n";
    $stmt = $pdo->query("DESCRIBE tbl_hub_payments");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        echo "{$c['Field']} | {$c['Type']}\n";
    }
    
    echo "\nRENAMING project_id to client_id if needed...\n";
    $col_names = array_column($cols, 'Field');
    if (in_array('project_id', $col_names) && !in_array('client_id', $col_names)) {
        $pdo->exec("ALTER TABLE tbl_hub_payments CHANGE project_id client_id INT(11)");
        echo "project_id renamed to client_id successfully.\n";
    }
    
    // Ensure gateway_id exists (just in case)
    if (!in_array('gateway_id', $col_names)) {
        $pdo->exec("ALTER TABLE tbl_hub_payments ADD gateway_id INT(11) NULL AFTER client_id");
        echo "gateway_id added successfully.\n";
    }

} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
