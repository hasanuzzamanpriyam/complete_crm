<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_624 extends CI_Migration
{
    public function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        // 1. Create tbl_admin_config for per-admin settings
        $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_admin_config` (
            `admin_config_id` INT(11) NOT NULL AUTO_INCREMENT,
            `admin_user_id` INT(11) NOT NULL,
            `config_key` VARCHAR(100) NOT NULL,
            `config_value` TEXT DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`admin_config_id`),
            UNIQUE KEY `uq_admin_key` (`admin_user_id`, `config_key`),
            KEY `admin_user_id` (`admin_user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // 2. Add created_by column to tbl_projects
        if (!$this->db->field_exists('created_by', 'tbl_project')) {
            $this->db->query("ALTER TABLE `tbl_projects` ADD `created_by` INT(11) NOT NULL DEFAULT '1' AFTER `project_id`;");
        }

        // 3. Copy existing settings from first admin to be admin-specific config
        //    This ensures existing admin's settings are preserved as their personal config
        $this->db->query("INSERT IGNORE INTO `tbl_admin_config` (`admin_user_id`, `config_key`, `config_value`)
            SELECT 1, `config_key`, `value` FROM `tbl_config`;");

        // 4. Update version
        $this->db->query("UPDATE `tbl_config` SET `value` = '6.2.4' WHERE `tbl_config`.`config_key` = 'version';");
    }

    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS `tbl_admin_config`;");

        if ($this->db->field_exists('created_by', 'tbl_project')) {
            $this->db->query("ALTER TABLE `tbl_projects` DROP COLUMN `created_by`;");
        }

        $this->db->query("UPDATE `tbl_config` SET `value` = '6.2.2' WHERE `tbl_config`.`config_key` = 'version';");
    }
}
