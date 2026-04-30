<?php
define('BASEPATH', 'dummy');
define('ENVIRONMENT', 'development');
require_once 'application/config/database.php';
$db_config = $db['default'];

try {
    $dsn = "mysql:host={$db_config['hostname']};dbname={$db_config['database']};charset={$db_config['char_set']}";
    $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "ALTER TABLE tbldomains 
            ADD COLUMN days INT NULL AFTER expiry_date,
            ADD COLUMN time_unit VARCHAR(20) NULL AFTER days";
    
    $pdo->exec($sql);
    echo "Columns 'days' and 'time_unit' added to tbldomains successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
