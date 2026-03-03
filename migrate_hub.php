<?php
/**
 * Master Migration Script for Payment Hub
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'tic_crm';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("DB Connection Failed: " . $e->getMessage() . "\n");
}

function tableExists($pdo, $table) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        return $stmt->rowCount() > 0;
    } catch (Exception $e) { return false; }
}

function columnExists($pdo, $table, $column) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $stmt->rowCount() > 0;
    } catch (Exception $e) { return false; }
}

echo "Starting Payment Hub Master Migration...\n";

// 1. Create Base Tables if missing (Legacy naming)
$base_sqls = [
    "CREATE TABLE IF NOT EXISTS `tbl_payment_projects` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `project_name` VARCHAR(255) NOT NULL,
        `api_key` VARCHAR(100) NOT NULL,
        `api_secret` VARCHAR(100) NOT NULL,
        `callback_url` VARCHAR(255) DEFAULT NULL,
        `webhook_url` VARCHAR(255) DEFAULT NULL,
        `status` ENUM('active', 'inactive') DEFAULT 'active',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `api_key` (`api_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

    "CREATE TABLE IF NOT EXISTS `tbl_api_tokens` (
        `id`              INT(11) NOT NULL AUTO_INCREMENT,
        `project_id`      INT(11) NOT NULL,
        `token_name`      VARCHAR(100) NOT NULL DEFAULT 'Default Token',
        `token_prefix`    VARCHAR(12)  NOT NULL,
        `token_hash`      VARCHAR(64)  NOT NULL,
        `signing_secret`  VARCHAR(64)  NOT NULL,
        `ip_whitelist`    TEXT         DEFAULT NULL,
        `status`          ENUM('active','disabled','revoked') NOT NULL DEFAULT 'active',
        `expires_at`      DATETIME     DEFAULT NULL,
        `last_used_at`    DATETIME     DEFAULT NULL,
        `last_used_ip`    VARCHAR(45)  DEFAULT NULL,
        `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `token_hash` (`token_hash`),
        KEY `project_id` (`project_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

    "CREATE TABLE IF NOT EXISTS `tbl_external_transactions` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `project_id` INT(11) NOT NULL,
        `external_reference` VARCHAR(100) NOT NULL,
        `amount` DECIMAL(15,2) NOT NULL,
        `currency` VARCHAR(10) DEFAULT 'BDT',
        `gateway_name` VARCHAR(50) DEFAULT 'PipraPay',
        `gateway_transaction_id` VARCHAR(100) DEFAULT NULL,
        `status` ENUM('pending', 'success', 'failed', 'cancelled') DEFAULT 'pending',
        `payment_method` VARCHAR(50) DEFAULT NULL,
        `raw_response` TEXT DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `project_id` (`project_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
];

foreach ($base_sqls as $sql) {
    $pdo->exec($sql);
}
echo "✔ Base legacy tables ensured.\n";

// 2. Renames (Migration 613 logic)
if (tableExists($pdo, 'tbl_payment_projects') && !tableExists($pdo, 'tbl_api_clients')) {
    $pdo->exec("RENAME TABLE `tbl_payment_projects` TO `tbl_api_clients` ");
    echo "✔ Renamed tbl_payment_projects to tbl_api_clients\n";
}
if (tableExists($pdo, 'tbl_external_transactions') && !tableExists($pdo, 'tbl_payments')) {
    $pdo->exec("RENAME TABLE `tbl_external_transactions` TO `tbl_payments` ");
    echo "✔ Renamed tbl_external_transactions to tbl_payments\n";
}

// 3. New Tables (Migration 613 logic)
$new_tables = [
    "CREATE TABLE IF NOT EXISTS `tbl_payment_gateways` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `gateway_slug` VARCHAR(50) NOT NULL,
        `config` TEXT DEFAULT NULL,
        `status` ENUM('active','inactive') DEFAULT 'active',
        `is_default` TINYINT(1) DEFAULT '0',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `gateway_slug` (`gateway_slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

    "CREATE TABLE IF NOT EXISTS `tbl_payment_transactions` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `payment_id` INT(11) NOT NULL,
        `gateway_id` INT(11) DEFAULT NULL,
        `gateway_txn_id` VARCHAR(100) DEFAULT NULL,
        `status` VARCHAR(50) NOT NULL,
        `raw_response` LONGTEXT DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `payment_id` (`payment_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

    "CREATE TABLE IF NOT EXISTS `tbl_payment_logs` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `payment_id` INT(11) DEFAULT NULL,
        `log_level` VARCHAR(10) DEFAULT 'info',
        `message` TEXT NOT NULL,
        `context` TEXT DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

    "CREATE TABLE IF NOT EXISTS `tbl_webhook_logs` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `payment_id` INT(11) DEFAULT NULL,
        `direction` ENUM('incoming','outgoing') NOT NULL,
        `url` VARCHAR(255) DEFAULT NULL,
        `payload` LONGTEXT DEFAULT NULL,
        `response_code` INT(5) DEFAULT NULL,
        `response_body` TEXT DEFAULT NULL,
        `status` ENUM('success','failed','pending') DEFAULT 'pending',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

    "CREATE TABLE IF NOT EXISTS `tbl_refunds` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `payment_id` INT(11) NOT NULL,
        `amount` DECIMAL(15,2) NOT NULL,
        `reason` TEXT DEFAULT NULL,
        `gateway_refund_id` VARCHAR(100) DEFAULT NULL,
        `status` VARCHAR(50) NOT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
];

foreach ($new_tables as $sql) {
    $pdo->exec($sql);
}
echo "✔ New hub tables ensured.\n";

// Seed Piprapay if missing
$stmt = $pdo->query("SELECT id FROM tbl_payment_gateways WHERE gateway_slug = 'piprapay'");
if ($stmt->rowCount() == 0) {
    $pdo->exec("INSERT INTO tbl_payment_gateways (name, gateway_slug, is_default) VALUES ('PipraPay', 'piprapay', 1)");
    echo "✔ Seeded Piprapay gateway.\n";
}

// 4. Schema Refinement (Migration 613 & 614 logic)
if (tableExists($pdo, 'tbl_api_clients')) {
    if (!columnExists($pdo, 'tbl_api_clients', 'client_id')) {
        $pdo->exec("ALTER TABLE `tbl_api_clients` ADD COLUMN `client_id` VARCHAR(64) NULL AFTER `project_name`, ADD COLUMN `client_secret` VARCHAR(128) NULL AFTER `client_id`, ADD COLUMN `updated_at` DATETIME NULL AFTER `created_at` ");
        $pdo->exec("UPDATE `tbl_api_clients` SET `client_id` = `api_key`, `client_secret` = `api_secret` WHERE `client_id` IS NULL");
        echo "✔ Updated tbl_api_clients columns.\n";
    }
}

if (tableExists($pdo, 'tbl_payments')) {
    if (!columnExists($pdo, 'tbl_payments', 'customer_name')) {
        $pdo->exec("ALTER TABLE `tbl_payments` ADD COLUMN `customer_name` VARCHAR(100) NULL AFTER `currency`, ADD COLUMN `customer_email` VARCHAR(100) NULL AFTER `customer_name` ");
        echo "✔ Updated tbl_payments columns.\n";
    }
}

// Webhook Retry Columns (614)
addColumnIfMissing($pdo, 'tbl_webhook_logs', 'retry_count', '`retry_count` INT(5) DEFAULT 0 AFTER `status`');
addColumnIfMissing($pdo, 'tbl_webhook_logs', 'next_retry_at', '`next_retry_at` DATETIME NULL AFTER `retry_count`');
addColumnIfMissing($pdo, 'tbl_webhook_logs', 'last_error', '`last_error` TEXT NULL AFTER `next_retry_at`');

// Audit Columns (614)
addColumnIfMissing($pdo, 'tbl_payment_logs', 'ip_address', '`ip_address` VARCHAR(45) NULL AFTER `context`');
addColumnIfMissing($pdo, 'tbl_payment_logs', 'user_agent', '`user_agent` VARCHAR(255) NULL AFTER `ip_address`');

function addColumnIfMissing($pdo, $table, $column, $definition) {
    if (!columnExists($pdo, $table, $column)) {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN $definition");
        echo "✔ Added $column to $table\n";
    }
}

// 5. Update migration version
try {
    $pdo->exec("UPDATE `tbl_migrations` SET `version` = 614");
    echo "✔ Updated tbl_migrations version to 614\n";
} catch (Exception $e) {}

echo "\nMigration Complete! All tables and columns are synchronized.\n";
