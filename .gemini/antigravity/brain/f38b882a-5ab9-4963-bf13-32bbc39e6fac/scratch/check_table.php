<?php
define('BASEPATH', 'dummy');
define('ENVIRONMENT', 'development');
require_once 'application/config/database.php';

$db_config = $db['default'];
$table = $argv[1] ?? 'tbldomains';

try {
    $dsn = "mysql:host={$db_config['hostname']};dbname={$db_config['database']};charset={$db_config['char_set']}";
    $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Table structure for $table:\n";
    $stmt = $pdo->query("DESCRIBE $table");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row['Field']} - {$row['Type']}\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
