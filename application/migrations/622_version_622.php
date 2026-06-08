<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_622 extends CI_Migration
{
    public function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        // 1. Add is_super_admin column to tbl_users
        if (!$this->db->field_exists('is_super_admin', 'tbl_users')) {
            $this->db->query("ALTER TABLE `tbl_users` ADD `is_super_admin` TINYINT(1) NOT NULL DEFAULT '0' AFTER `role_id`;");
        }

        // 2. Create tbl_user_permissions for per-user permission overrides
        $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_user_permissions` (
            `user_permission_id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NOT NULL,
            `menu_id` INT(11) NOT NULL,
            `view` INT(1) DEFAULT '0',
            `created` INT(1) DEFAULT '0',
            `edited` INT(1) DEFAULT '0',
            `deleted` INT(1) DEFAULT '0',
            `updated_at` DATETIME DEFAULT NULL,
            PRIMARY KEY (`user_permission_id`),
            UNIQUE KEY `user_menu` (`user_id`, `menu_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // 3. Create tbl_audit_logs for detailed audit trail
        $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_audit_logs` (
            `audit_id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) DEFAULT NULL,
            `username` VARCHAR(100) DEFAULT NULL,
            `action` VARCHAR(100) NOT NULL,
            `module` VARCHAR(100) DEFAULT NULL,
            `module_id` INT(11) DEFAULT NULL,
            `details` TEXT DEFAULT NULL,
            `ip_address` VARCHAR(45) DEFAULT NULL,
            `user_agent` VARCHAR(255) DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`audit_id`),
            KEY `user_id` (`user_id`),
            KEY `module` (`module`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // 4. Add super admin menu items
        $this->db->query("INSERT INTO `tbl_menu` (`menu_id`, `label`, `link`, `icon`, `parent`, `sort`, `status`) VALUES
            (NULL, 'super_admin', '', 'fa fa-shield', '0', '999', '1'),
            (NULL, 'super_admin_dashboard', 'admin/superadmin', 'fa fa-dashboard', (SELECT `menu_id` FROM (SELECT `menu_id` FROM `tbl_menu` WHERE `label` = 'super_admin' LIMIT 1) AS `m`), '1', '1'),
            (NULL, 'user_permissions', 'admin/superadmin/permissions', 'fa fa-key', (SELECT `menu_id` FROM (SELECT `menu_id` FROM `tbl_menu` WHERE `label` = 'super_admin' LIMIT 1) AS `m`), '2', '1'),
            (NULL, 'audit_logs', 'admin/superadmin/audit_logs', 'fa fa-history', (SELECT `menu_id` FROM (SELECT `menu_id` FROM `tbl_menu` WHERE `label` = 'super_admin' LIMIT 1) AS `m`), '3', '1'),
            (NULL, 'super_admin_settings', 'admin/superadmin/settings', 'fa fa-cogs', (SELECT `menu_id` FROM (SELECT `menu_id` FROM `tbl_menu` WHERE `label` = 'super_admin' LIMIT 1) AS `m`), '4', '1');");

        // 5. Set existing admin (user_id = 1) as super admin with password 12345678
        $enc_key = $this->db->get_where('tbl_config', array('config_key' => 'encryption_key'))->row();
        $key = !empty($enc_key) ? $enc_key->value : 'I6PnEPbQNLslYMj7ChKxDJ2yenuHLkXn';
        $hashed_pw = hash('sha512', '12345678' . $key);

        $this->db->query("UPDATE `tbl_users` SET `is_super_admin` = 1, `password` = '" . $hashed_pw . "' WHERE `user_id` = 1;");

        // 6. Update version
        $this->db->query("UPDATE `tbl_config` SET `value` = '6.2.2' WHERE `tbl_config`.`config_key` = 'version';");
    }

    public function down()
    {
        if ($this->db->field_exists('is_super_admin', 'tbl_users')) {
            $this->db->query("ALTER TABLE `tbl_users` DROP COLUMN `is_super_admin`;");
        }

        $this->db->query("DROP TABLE IF EXISTS `tbl_user_permissions`;");
        $this->db->query("DROP TABLE IF EXISTS `tbl_audit_logs`;");

        $this->db->query("DELETE FROM `tbl_menu` WHERE `label` IN ('super_admin', 'super_admin_dashboard', 'user_permissions', 'audit_logs', 'super_admin_settings');");

        $this->db->query("UPDATE `tbl_config` SET `value` = '6.2.1' WHERE `tbl_config`.`config_key` = 'version';");
    }
}
