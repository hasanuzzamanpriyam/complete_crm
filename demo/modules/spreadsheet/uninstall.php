<?php defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();
$CI->db->query("DELETE FROM `tbl_menu` WHERE `tbl_menu`.`label` = 'spreadsheet'");
$CI->db->query("DELETE FROM `tbl_client_menu` WHERE `tbl_client_menu`.`label` = 'spreadsheet'");
$CI->db->query("DROP TABLE `tbl_spreadsheet_hash_share`");
$CI->db->query("DROP TABLE `tbl_spreadsheet_related`");
$CI->db->query("DROP TABLE `tbl_spreadsheet_my_folder`");
