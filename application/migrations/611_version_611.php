<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_611 extends CI_Migration
{
    public function up()
    {
        // Table for External Projects
        $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_payment_projects` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // Table for External Transactions
        $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_external_transactions` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `project_id` INT(11) NOT NULL,
            `external_reference` VARCHAR(100) NOT NULL,
            `amount` DECIMAL(10,2) NOT NULL,
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
            CONSTRAINT `fk_project_id` FOREIGN KEY (`project_id`) REFERENCES `tbl_payment_projects` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $this->db->query("UPDATE `tbl_config` SET `value` = '6.1.1' WHERE `config_key` = 'version';");
    }

    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS `tbl_external_transactions`;");
        $this->db->query("DROP TABLE IF EXISTS `tbl_payment_projects`;");
        $this->db->query("UPDATE `tbl_config` SET `value` = '6.1.0' WHERE `config_key` = 'version';");
    }
}
