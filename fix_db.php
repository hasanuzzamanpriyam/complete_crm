<?php
// fix_db.php
// Standalone DB fixer script for PipraPay

define('BASEPATH', true); // Bypass defined('BASEPATH') check in config files
define('ENVIRONMENT', 'production');
$db = [];
$active_group = 'default';

// Include the database configuration
if (file_exists(__DIR__ . '/application/config/database.php')) {
    include __DIR__ . '/application/config/database.php';
} else {
    die("Error: application/config/database.php not found.\n");
}

$db_config = $db[$active_group] ?? null;
if (!$db_config) {
    die("Error: Database configuration group '$active_group' not found.\n");
}

$host = $db_config['hostname'];
$user = $db_config['username'];
$pass = $db_config['password'];
$dbname = $db_config['database'];

$is_cli = (PHP_SAPI === 'cli');

if (!$is_cli) {
    echo "<html><head><title>Database Cleanup</title></head><body style='font-family: sans-serif; padding: 20px;'>";
}

echo "<h3>Connecting to database...</h3>";
$mysqli = @new mysqli($host, $user, $pass, $dbname);

if ($mysqli->connect_error) {
    $err = "Database connection failed: " . $mysqli->connect_error;
    if ($is_cli) {
        die($err . "\n");
    } else {
        die("<p style='color: red;'>$err</p></body></html>");
    }
}

echo "<p style='color: green;'>✓ Database connected successfully.</p>";

// 1. Clean up duplicate PipraPay entries in tbl_online_payment
$result = $mysqli->query("SELECT * FROM tbl_online_payment WHERE gateway_name = 'PipraPay'");
if ($result) {
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    
    if (count($rows) > 1) {
        echo "<p style='color: orange;'>Found " . count($rows) . " duplicate PipraPay gateway entries.</p>";
        $keep_id = $rows[0]['online_payment_id'];
        echo "Keeping entry ID: <strong>$keep_id</strong><br>";
        
        foreach ($rows as $index => $row) {
            if ($index > 0) {
                $id_to_delete = $row['online_payment_id'];
                $mysqli->query("DELETE FROM tbl_online_payment WHERE online_payment_id = $id_to_delete");
                echo "Deleted duplicate entry ID: $id_to_delete<br>";
            }
        }
    } else {
        echo "<p>No duplicate PipraPay entries found in tbl_online_payment.</p>";
    }
}

// 2. Ensure modal = 'No' and link = 'payment/piprapay' for the remaining PipraPay entry
$update_sql = "UPDATE tbl_online_payment SET modal = 'No', link = 'payment/piprapay' WHERE gateway_name = 'PipraPay'";
if ($mysqli->query($update_sql)) {
    echo "<p style='color: green;'>✓ Successfully updated PipraPay modal setting to 'No' and link to 'payment/piprapay'.</p>";
} else {
    echo "<p style='color: red;'>Error updating PipraPay setting: " . $mysqli->error . "</p>";
}

echo "<h3>Cleanup finished successfully! Please delete this file (fix_db.php) from your server.</h3>";

if (!$is_cli) {
    echo "</body></html>";
}
