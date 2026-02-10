<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->db->query(
    "INSERT INTO
 `tbl_menu` (`menu_id`, `label`, `link`, `icon`, `parent`, `sort`, `time`, `status`)
  VALUES (NULL, 'contracts', 'admin/contracts', 'fa fa-file', '0', '3', CURRENT_TIMESTAMP, '1');"
);

$CI->db->query(
    "INSERT INTO `tbl_client_menu` (`menu_id`, `label`, `link`, `icon`, `parent`, `sort`, `time`, `status`)
  VALUES (NULL, 'contracts', 'contracts/contract/list', 'fa fa-file', '0', '3', CURRENT_TIMESTAMP, '1');"
);

$CI->db->query("CREATE TABLE IF NOT EXISTS  `tbl_contracts_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` mediumtext NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");

$CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_contracts` (
  `contract_id` int NOT NULL AUTO_INCREMENT,
  `parent_contract_id` int DEFAULT NULL,
  `child_contract_id` int DEFAULT NULL,
  `content` longtext,
  `description` text,
  `subject` varchar(191) DEFAULT NULL,
  `client` int NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `contract_type` int DEFAULT NULL,
  `project_id` int DEFAULT NULL,
  `added_from` int NOT NULL,
  `date_added` datetime NOT NULL,
  `last_renewed` datetime DEFAULT NULL,
  `renewed_by` int DEFAULT NULL,
  `archived_on` datetime DEFAULT NULL,
  `archived_by` int DEFAULT NULL,
  `is_expiry_notified` int NOT NULL DEFAULT '0',
  `contract_value` decimal(15,2) DEFAULT NULL,
  `trash` tinyint(1) DEFAULT '0',
  `visible_to_client` varchar(28) DEFAULT '0',
  `hash` varchar(32) DEFAULT NULL,
  `signed` tinyint(1) NOT NULL DEFAULT '0',
  `signature` varchar(40) DEFAULT '',
  `marked_as_signed` tinyint(1) NOT NULL DEFAULT '0',
  `acceptance_firstname` varchar(50) DEFAULT '',
  `acceptance_lastname` varchar(50) DEFAULT '',
  `acceptance_email` varchar(100) DEFAULT '',
  `acceptance_date` datetime DEFAULT NULL,
  `acceptance_ip` varchar(40) DEFAULT '',
  `short_link` varchar(100) DEFAULT '',
  PRIMARY KEY (`contract_id`),
  KEY `client` (`client`),
  KEY `contract_type` (`contract_type`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");

$CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_contract_renewals` (
  `contract_renewal_id` int NOT NULL AUTO_INCREMENT,
  `contract_id` int NOT NULL,
  `old_start_date` date NOT NULL,
  `new_start_date` date NOT NULL,
  `old_end_date` date DEFAULT NULL,
  `new_end_date` date DEFAULT NULL,
  `old_value` decimal(15,2) DEFAULT NULL,
  `new_value` decimal(15,2) DEFAULT NULL,
  `date_renewed` datetime NOT NULL,
  `renewed_by` varchar(100) NOT NULL,
  `renewed_by_id` int NOT NULL DEFAULT '0',
  `is_on_old_expiry_notified` int DEFAULT '0',
  PRIMARY KEY (`contract_renewal_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");

$CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_templates` (
  `template_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `template_name` varchar(255) NOT NULL,
  `module` varchar(100) NOT NULL,
  `added_by` int(11) NOT NULL,
  `template_content` text DEFAULT NULL,
  PRIMARY KEY (`template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");

// if the column is not there add it into sql
if (!$CI->db->field_exists('module', 'tbl_files')) {
    $CI->db->query("ALTER TABLE `tbl_files` ADD `module` varchar(32) DEFAULT NULL");
    $CI->db->query("ALTER TABLE `tbl_files` ADD  `module_field_id` int(11) DEFAULT NULL");
}
$CI->db->query("ALTER TABLE `tbl_leads_notes` ADD  `module` varchar(32)  DEFAULT ''");
$CI->db->query("ALTER TABLE `tbl_leads_notes` ADD  `module_field_id` int(11) DEFAULT NULL");

$CI->db->query("ALTER TABLE `tbl_task` ADD  `module` varchar(32)  DEFAULT ''");
$CI->db->query("ALTER TABLE `tbl_task` ADD  `module_field_id` int(11) DEFAULT NULL");



