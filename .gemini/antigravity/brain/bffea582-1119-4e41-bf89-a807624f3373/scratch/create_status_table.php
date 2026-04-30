<?php
define('BASEPATH', 'dummy');
define('ENVIRONMENT', 'development');
require_once 'application/config/database.php';
$db_config = $db['default'];

try {
    $dsn = "mysql:host={$db_config['hostname']};dbname={$db_config['database']};charset={$db_config['char_set']}";
    $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS tbl_domain_status (
        id INT AUTO_INCREMENT PRIMARY KEY,
        status_name VARCHAR(100) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    
    $pdo->exec($sql);
    
    // Insert initial data if empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM tbl_domain_status");
    if ($stmt->fetchColumn() == 0) {
        $initial_statuses = ['Active', 'Pending', 'Transferring', 'Expired'];
        $insert_stmt = $pdo->prepare("INSERT INTO tbl_domain_status (status_name) VALUES (?)");
        foreach ($initial_statuses as $status) {
            $insert_stmt->execute([$status]);
        }
    }
    
    echo "Table tbl_domain_status created successfully or already exists.";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
