<?php
/**
 * Payment Hub — Database Setup Script
 *
 * Run this ONCE to create the two payment hub tables.
 * After execution, DELETE this file from your server.
 *
 * Usage: http://your-crm.local/run_payment_hub_setup.php
 */

define('BASEPATH', true);

// Load CI database config
$db_config = [];
require __DIR__ . '/application/config/database.php';
$c = $db['default'];

$dsn = "mysql:host={$c['hostname']};dbname={$c['database']};charset=utf8";
try {
    $pdo = new PDO($dsn, $c['username'], $c['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die('DB Connection Failed: ' . $e->getMessage());
}

$sqls = [
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
        KEY `project_id` (`project_id`),
        CONSTRAINT `fk_token_setup_project` FOREIGN KEY (`project_id`) REFERENCES `tbl_payment_projects` (`id`) ON DELETE CASCADE
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
        KEY `project_id` (`project_id`),
        CONSTRAINT `fk_ph_project_id` FOREIGN KEY (`project_id`) REFERENCES `tbl_payment_projects` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
];

$errors = [];
foreach ($sqls as $sql) {
    try {
        $pdo->exec($sql);
        echo '<p style="color:green">✔ ' . trim(substr($sql, 0, 60)) . '…</p>';
    } catch (PDOException $e) {
        $errors[] = $e->getMessage();
        echo '<p style="color:red">✘ ' . $e->getMessage() . '</p>';
    }
}

if (empty($errors)) {
    echo '<h2 style="color:green">✔ Payment Hub tables created successfully!</h2>';
    echo '<p><strong>Next step:</strong> <a href="admin/payment_hub">Go to Payment Hub Admin</a></p>';
    echo '<p style="color:red"><strong>Security:</strong> Delete this file from your server immediately.</p>';
} else {
    echo '<h2 style="color:orange">Setup completed with some errors (see above).</h2>';
}
