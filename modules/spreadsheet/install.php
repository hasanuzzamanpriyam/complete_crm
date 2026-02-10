<?php defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();
$CI->db->query(
  "INSERT INTO `tbl_menu` (`menu_id`, `label`, `link`, `icon`, `parent`, `sort`, `time`, `status`) 
  VALUES (NULL, 'spreadsheet', 'admin/spreadsheet', 'fa fa-file-excel-o', '0', '2', CURRENT_TIMESTAMP, '1');"
);

$CI->db->query(
  "INSERT INTO `tbl_client_menu` (`menu_id`, `label`, `link`, `icon`, `parent`, `sort`, `time`, `status`) 
  VALUES (NULL, 'spreadsheet', 'client/spreadsheet', 'fa fa-file-excel-o', '0', '2', CURRENT_TIMESTAMP, '1');"
);

$CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_spreadsheet_hash_share` (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `rel_type` varchar(20) NOT NULL,
    `rel_id` int NOT NULL,
    `id_share` int NOT NULL,
    `hash` text,
    `inserted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `role` int NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
$CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_spreadsheet_related` (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `parent_id` int NOT NULL,
    `rel_type` varchar(20) NOT NULL,
    `rel_id` int NOT NULL,
    `inserted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `hash` varchar(250) NOT NULL DEFAULT '',
    `role` int NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;");
$CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_spreadsheet_my_folder` (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `parent_id` text NOT NULL,
    `name` text NOT NULL,
    `type` varchar(20) DEFAULT NULL,
    `size` text,
    `staffid` int NOT NULL,
    `category` varchar(20) NOT NULL DEFAULT 'my_folder',
    `inserted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `data_form` longtext,
    `staffs_share` text,
    `departments_share` text,
    `clients_share` text,
    `client_groups_share` text,
    `rel_type` varchar(100) DEFAULT NULL,
    `rel_id` varchar(11) DEFAULT NULL,
    `group_share_staff` varchar(1) DEFAULT NULL,
    `group_share_client` varchar(1) DEFAULT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;");
