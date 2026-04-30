<?php
define('BASEPATH', 'dummy');
include 'application/config/database.php';

$dsn = "mysql:host=" . $db['default']['hostname'] . ";dbname=" . $db['default']['database'] . ";charset=" . $db['default']['char_set'];
try {
    $pdo = new PDO($dsn, $db['default']['username'], $db['default']['password']);
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo $row[0] . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
