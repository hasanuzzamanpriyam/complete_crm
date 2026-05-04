<?php
define('BASEPATH', 'dummy');
define('ENVIRONMENT', 'development');
include 'application/config/database.php';

$db_config = $db['default'];
$mysqli = new mysqli($db_config['hostname'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

function dump_table($mysqli, $table) {
    echo "Structure for $table:\n";
    $result = $mysqli->query("DESCRIBE $table");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "Field: " . $row['Field'] . " | Type: " . $row['Type'] . "\n";
        }
    } else {
        echo "Table $table does not exist or error: " . $mysqli->error . "\n";
    }
    echo "\n";
}

dump_table($mysqli, 'tbldomains');
dump_table($mysqli, 'tblserver_hostings');
dump_table($mysqli, 'tblproviders');
dump_table($mysqli, 'tbl_domain_status');
dump_table($mysqli, 'tbl_domain_types');
dump_table($mysqli, 'tblhostings');

$mysqli->close();
