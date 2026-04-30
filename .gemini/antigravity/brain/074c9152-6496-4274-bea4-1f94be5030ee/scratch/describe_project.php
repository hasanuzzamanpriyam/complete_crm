<?php
define('BASEPATH', 'dummy');
define('ENVIRONMENT', 'development');
include 'application/config/database.php';

$dsn = "mysql:host=" . $db['default']['hostname'] . ";dbname=" . $db['default']['database'] . ";charset=" . $db['default']['char_set'];
try {
    $pdo = new PDO($dsn, $db['default']['username'], $db['default']['password']);
    $stmt = $pdo->query("DESCRIBE tbl_project");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
