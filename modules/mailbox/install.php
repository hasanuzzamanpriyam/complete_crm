<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
 * The file is responsible for handing the mailbox installation
 */

$CI = &get_instance();
$CI->db->query('ALTER TABLE `tbl_inbox` ADD `from_user_id` INT(11) NULL DEFAULT NULL AFTER `inbox_id`;');
$CI->db->query('ALTER TABLE tbl_users ADD `smtp_unread_email` tinyint(1) NOT NULL DEFAULT 0, ADD  `smtp_delete_mail_after_import` tinyint(1) NOT NULL DEFAULT 0;');
$CI->db->query('ALTER TABLE tbl_inbox ADD `mail_uid` int(11) DEFAULT NULL,ADD `upload_file` text DEFAULT NULL;');
$CI->db->query('ALTER TABLE tbl_sent ADD `mail_uid` int(11) DEFAULT NULL,ADD `upload_file` text DEFAULT NULL;');
$CI->db->query('ALTER TABLE `tbl_users` CHANGE `active_email` `active_email` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, CHANGE `smtp_email_type` `smtp_email_type` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, CHANGE `smtp_encription` `smtp_encription` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, CHANGE `smtp_action` `smtp_action` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, CHANGE `smtp_host_name` `smtp_host_name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, CHANGE `smtp_username` `smtp_username` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;');
$CI->db->query('ALTER TABLE `tbl_inbox` CHANGE `to` `to` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `from` `from` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;');
$CI->db->query('ALTER TABLE `tbl_inbox` ADD `cc` VARCHAR(100) NULL DEFAULT NULL AFTER `to`;');

$CI->db->query("ALTER TABLE `tbl_inbox` ADD `smtp_host_name` varchar(100) NOT NULL DEFAULT 'system' ");
$CI->db->query("ALTER TABLE `tbl_inbox` ADD `mail_folder` varchar(100) NOT NULL DEFAULT '' ");

$CI->db->query("UPDATE tbl_inbox SET smtp_host_name = 'system', mail_folder = 'inbox'  WHERE  smtp_host_name != '' AND mail_uid IS NULL");

$CI->db->query("ALTER TABLE `tbl_inbox` ADD UNIQUE `key_for_uid` USING HASH(`mail_uid`, `smtp_host_name`, `mail_folder`)");

$CI->db->query("ALTER TABLE `tbl_inbox` ADD  `mail_status` VARCHAR(100) NOT NULL DEFAULT ''");
$CI->db->query("ALTER TABLE `tbl_users` ADD  `smtp_syn_time` INTEGER ");
$CI->db->query("ALTER TABLE `tbl_users` ADD  `mail_host` VARCHAR(100)");
$CI->db->query("ALTER TABLE `tbl_users` ADD  `smtp_menu` VARCHAR(512) DEFAULT ''");
