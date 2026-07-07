<?php defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();
$CI->db->query("DELETE FROM `tbl_menu` WHERE `tbl_menu`.`label` = 'contracts'");
$CI->db->query("DELETE FROM `tbl_client_menu` WHERE `tbl_client_menu`.`label` = 'contracts'");
