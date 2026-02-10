<?php defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();
$CI->db->query("DELETE FROM `tbl_menu` WHERE `tbl_menu`.`label` = 'complaints'");
$CI->db->query("DELETE FROM `tbl_client_menu` WHERE `tbl_client_menu`.`label` = 'complaints'");
$CI->db->query("DROP TABLE `tbl_complaints_types`");
$CI->db->query("DROP TABLE `tbl_complaints`");
$CI->db->query("DROP TABLE `tbl_complaints_replies`");
