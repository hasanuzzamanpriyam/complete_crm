<?php defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();
$CI->db->query("DELETE FROM `tbl_menu` WHERE `tbl_menu`.`label` = 'woocommerce'");
$CI->db->query("DELETE FROM `tbl_menu` WHERE `tbl_menu`.`label` = 'orders'");
$CI->db->query("DELETE FROM `tbl_menu` WHERE `tbl_menu`.`label` = 'products'");
$CI->db->query("DELETE FROM `tbl_menu` WHERE `tbl_menu`.`label` = 'customers'");
$CI->db->query("DELETE FROM `tbl_menu` WHERE `tbl_menu`.`label` = 'stores'");

