<?php
/**
 * ============================================================
 * TIC CRM Demo Mode - VPS Deployment Script
 * ============================================================
 * 
 * This script sets up the demo subdirectory on the VPS.
 * It creates the demo database, seeds data, and creates
 * symlinks for assets.
 * 
 * The main `tic_crm` database is NEVER modified - only read from.
 * 
 * Usage: php setup_demo_vps.php
 * Run from: /var/www/cp.tic.com.bd/
 * ============================================================
 */

echo "=== TIC CRM Demo VPS Setup ===\n\n";
$basedir = __DIR__;

// --- Step 1: Validate environment ---
echo "[1/7] Checking environment...\n";

$phpver = phpversion();
echo "  PHP version: $phpver\n";

// Check if demo directory exists
if (!is_dir("$basedir/demo")) {
    echo "  ERROR: demo/ directory not found. Run this from the project root.\n";
    exit(1);
}
if (!is_dir("$basedir/demo/seeds")) {
    echo "  ERROR: demo/seeds/ directory not found. Did you git pull?\n";
    exit(1);
}

// Check MySQL connection
$mysql_host = 'localhost';
$mysql_user = 'root';
$mysql_pass = 'Igmc@@2026!!';

try {
    $main_db = new mysqli($mysql_host, $mysql_user, $mysql_pass, 'tic_crm');
    if ($main_db->connect_error) {
        echo "  ERROR: Cannot connect to MySQL: " . $main_db->connect_error . "\n";
        exit(1);
    }
    echo "  MySQL connection: OK\n";
} catch (Exception $e) {
    echo "  ERROR: MySQL connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// --- Step 2: Create demo database ---
echo "\n[2/7] Creating demo database...\n";
$admin = new mysqli($mysql_host, $mysql_user, $mysql_pass);
$admin->query("DROP DATABASE IF EXISTS tic_crm_demo");
if ($admin->query("CREATE DATABASE tic_crm_demo CHARACTER SET utf8 COLLATE utf8_general_ci")) {
    echo "  tic_crm_demo database created.\n";
} else {
    echo "  ERROR creating database: " . $admin->error . "\n";
    exit(1);
}
$admin->close();

// --- Step 3: Copy schema from main ---
echo "\n[3/7] Copying schema from main DB...\n";
$cmd = "mysqldump -u $mysql_user -p'$mysql_pass' --no-data tic_crm 2>/dev/null | mysql -u $mysql_user -p'$mysql_pass' tic_crm_demo 2>/dev/null";
$output = null;
$ret = null;
exec($cmd, $output, $ret);
if ($ret === 0) {
    echo "  Schema copied successfully.\n";
} else {
    echo "  WARNING: mysqldump returned code $ret. Checking if tables exist anyway...\n";
}

// Verify tables were created
$demo_db = new mysqli($mysql_host, $mysql_user, $mysql_pass, 'tic_crm_demo');
$tables = $demo_db->query("SHOW TABLES");
if ($tables && $tables->num_rows > 0) {
    echo "  Verified: " . $tables->num_rows . " tables exist in demo DB.\n";
} else {
    echo "  ERROR: No tables found in demo DB. Schema copy failed.\n";
    echo "  Try running manually:\n";
    echo "    mysqldump -u root -p tic_crm --no-data | mysql -u root -p tic_crm_demo\n";
    exit(1);
}

// --- Step 4: Seed reference data ---
echo "\n[4/7] Seeding reference data...\n";
$seed_file = "$basedir/demo/seeds/seed.sql";
if (!file_exists($seed_file)) {
    echo "  ERROR: $seed_file not found.\n";
    exit(1);
}
$seed_sql = file_get_contents($seed_file);
// Replace cross-database references for VPS (both DBs on same server)
$seed_sql = str_replace('tic_crm.', 'tic_crm.', $seed_sql);

if ($demo_db->multi_query($seed_sql)) {
    do {
        if ($result = $demo_db->store_result()) $result->free();
    } while ($demo_db->next_result());
    echo "  Reference data seeded.\n";
} else {
    echo "  WARNING: seed.sql error: " . $demo_db->error . "\n";
    echo "  Trying line-by-line...\n";
    // Fall back to running individual queries
    $queries = explode(";\n", $seed_sql);
    foreach ($queries as $q) {
        $q = trim($q);
        if (!empty($q)) {
            if (!$demo_db->query($q)) {
                echo "  Skipped query (likely duplicate): " . substr($demo_db->error, 0, 60) . "\n";
            }
        }
    }
    echo "  Reference data seeded (with some skipped).\n";
}

// --- Step 5: Seed missing reference tables ---
echo "\n[5/7] Copying missing reference tables from main DB...\n";

$tables_to_copy = ['tbl_locales', 'tbl_goal_type', 'tbl_accounts', 'tbl_form', 'tbl_priority', 'tbl_status', 'tbl_lead_status', 'tbl_lead_source'];
$copied = 0;
foreach ($tables_to_copy as $table) {
    $demo_db->query("TRUNCATE TABLE `$table`");
    $r = $main_db->query("SELECT * FROM `$table`");
    if (!$r) {
        echo "  ERROR: Cannot read $table from main DB: " . $main_db->error . "\n";
        continue;
    }
    $cols = [];
    while ($field = $r->fetch_field()) { $cols[] = $field->name; }
    $col_list = '`' . implode('`, `', $cols) . '`';
    $count = 0;
    while ($row = $r->fetch_assoc()) {
        $vals = [];
        foreach ($cols as $col) {
            $v = $row[$col];
            $vals[] = is_null($v) ? 'NULL' : "'" . $demo_db->real_escape_string($v) . "'";
        }
        $sql = "INSERT INTO `$table` ($col_list) VALUES (" . implode(', ', $vals) . ")";
        if ($demo_db->query($sql)) $count++;
        else echo "  WARNING: insert into $table failed: " . $demo_db->error . "\n";
    }
    echo "  $table: $count rows\n";
    $copied += $count;
}
echo "  Total: $copied rows across " . count($tables_to_copy) . " tables.\n";

// --- Step 6: Seed sample data ---
echo "\n[6/7] Seeding sample business data...\n";
$seed_data_file = "$basedir/demo/seeds/seed_data.php";
if (file_exists($seed_data_file)) {
    include $seed_data_file;
    echo "  Sample data seeded.\n";
} else {
    echo "  ERROR: $seed_data_file not found.\n";
}

$demo_db->close();
$main_db->close();

// --- Step 7: Create symlinks ---
echo "\n[7/7] Creating asset symlinks...\n";
$links = [
    'assets' => '../assets',
    'asset' => '../asset',
    'uploads' => '../uploads',
    'modules' => '../modules',
];

foreach ($links as $target => $source) {
    $link_path = "$basedir/demo/$target";
    if (is_link($link_path)) {
        echo "  $target symlink already exists.\n";
        continue;
    }
    if (file_exists($link_path)) {
        echo "  $target exists as file/dir (not a symlink). Removing and recreating...\n";
        if (is_dir($link_path)) {
            // Try rmdir (only works if empty)
            if (!@rmdir($link_path)) {
                echo "  WARNING: $target directory is not empty. Keeping it.\n";
                continue;
            }
        } else {
            unlink($link_path);
        }
    }
    if (symlink($source, $link_path)) {
        echo "  $target -> $source\n";
    } else {
        echo "  WARNING: Could not create $target symlink. Trying cp -rl...\n";
        // Fallback: try creating hardlink or copy
        exec("cp -rl " . escapeshellarg("$basedir/$target") . " " . escapeshellarg($link_path) . " 2>/dev/null");
        if (file_exists($link_path)) {
            echo "  $target copied via hardlink.\n";
        } else {
            echo "  ERROR: Failed to create $target. Manual step needed.\n";
        }
    }
}

echo "\n=== Demo setup complete! ===\n";
echo "Test at: https://cp.tic.bd/demo/\n";
echo "Logins:\n";
echo "  Admin:    demo_admin / 123456\n";
echo "  Manager:  demo_manager / 123456\n";
echo "  Employee: demo_employee / 123456\n";
echo "\nDon't forget to add the nginx location block for /demo/ if not already configured.\n";
echo "Example: location /demo/ { alias /var/www/cp.tic.com.bd/demo/; ... }\n";
