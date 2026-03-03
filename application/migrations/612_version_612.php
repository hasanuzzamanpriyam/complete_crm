<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration 612 — API Token System
 *
 * Adds `tbl_api_tokens` for per-project bearer token management with
 * IP whitelisting, expiry, and HMAC signing.
 */
class Migration_Version_612 extends CI_Migration
{
    public function up()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_api_tokens` (
            `id`              INT(11) NOT NULL AUTO_INCREMENT,
            `project_id`      INT(11) NOT NULL,
            `token_name`      VARCHAR(100) NOT NULL DEFAULT 'Default Token',
            `token_prefix`    VARCHAR(12)  NOT NULL COMMENT 'First 8 chars of raw token for display',
            `token_hash`      VARCHAR(64)  NOT NULL COMMENT 'SHA-256 hash of raw token — never store raw',
            `signing_secret`  VARCHAR(64)  NOT NULL COMMENT 'HMAC-SHA256 signing secret shared with project',
            `ip_whitelist`    TEXT         DEFAULT NULL COMMENT 'JSON array of allowed IPs; NULL = any IP',
            `status`          ENUM('active','disabled','revoked') NOT NULL DEFAULT 'active',
            `expires_at`      DATETIME     DEFAULT NULL COMMENT 'NULL = never expires',
            `last_used_at`    DATETIME     DEFAULT NULL,
            `last_used_ip`    VARCHAR(45)  DEFAULT NULL,
            `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `token_hash` (`token_hash`),
            KEY `project_id` (`project_id`),
            KEY `status` (`status`),
            CONSTRAINT `fk_token_project_id`
                FOREIGN KEY (`project_id`) REFERENCES `tbl_payment_projects` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $this->db->query("UPDATE `tbl_config` SET `value` = '6.1.2' WHERE `config_key` = 'version';");
    }

    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS `tbl_api_tokens`;");
        $this->db->query("UPDATE `tbl_config` SET `value` = '6.1.1' WHERE `config_key` = 'version';");
    }
}
