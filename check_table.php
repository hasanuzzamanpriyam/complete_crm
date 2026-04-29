<?php
require 'application/config/database.php';
$mysqli = new mysqli($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);
if ($mysqli->connect_error) {
    die('Connect Error: ' . $mysqli->connect_error);
}
$result = $mysqli->query('DESCRIBE tblserver_hostings');
if ($result) {
    echo "Table structure for tblserver_hostings:\n";
    echo str_repeat('-', 100) . "\n";
    echo sprintf("%-25s | %-25s | %-8s | %-5s | %s\n", "Field", "Type", "Null", "Key", "Default");
    echo str_repeat('-', 100) . "\n";
    while ($row = $result->fetch_assoc()) {
        printf("%-25s | %-25s | %-8s | %-5s | %s\n", $row['Field'], $row['Type'], $row['Null'], $row['Key'], $row['Default']);
    }
    $result->free();
} else {
    echo 'Error: ' . $mysqli->error . "\n";
}
$mysqli->close();
?>
