<?php defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();
$CI->db->query("DELETE FROM `tbl_menu` WHERE `tbl_menu`.`label` = 'jitsi'");
$CI->db->query("DELETE FROM `tbl_client_menu` WHERE `tbl_client_menu`.`label` = 'jitsi'");
$CI->db->query("DROP TABLE IF EXISTS `tbl_jitsi_meetings`");
$CI->db->query("DELETE FROM `tbl_email_templates` WHERE `code` = 'jitsi_meeting_start'");
$CI->db->query("UPDATE `tbl_config` SET `value` = NULL WHERE `tbl_config`.`config_key` IN ('jitsi_domain', 'jitsi_app_id', 'jitsi_private_key', 'jitsi_public_key');");
