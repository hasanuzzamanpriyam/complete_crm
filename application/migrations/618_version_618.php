<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_618 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        $this->db->query("UPDATE `tbl_config` SET `value` = '6.1.8' WHERE `tbl_config`.`config_key` = 'version';");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_email_queue` (
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `recipient` varchar(255) NOT NULL,
            `subject` varchar(500) NOT NULL,
            `message` longtext NOT NULL,
            `attachments` text DEFAULT NULL,
            `status` enum('pending','sending','sent','failed') NOT NULL DEFAULT 'pending',
            `attempts` int(11) NOT NULL DEFAULT 0,
            `max_attempts` int(11) NOT NULL DEFAULT 3,
            `last_error` text DEFAULT NULL,
            `next_retry_at` datetime DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `sent_at` datetime DEFAULT NULL,
            `source` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_status` (`status`),
            KEY `idx_next_retry` (`next_retry_at`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }

    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS `tbl_email_queue`;");
        $this->db->query("UPDATE `tbl_config` SET `value` = '6.1.7' WHERE `tbl_config`.`config_key` = 'version';");
    }
}
