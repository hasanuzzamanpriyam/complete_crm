<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->db->query(
  "INSERT INTO
 `tbl_menu` (`menu_id`, `label`, `link`, `icon`, `parent`, `sort`, `time`, `status`)
  VALUES (NULL, 'complaints', 'admin/complaints', 'fa fa-inbox', '0', '3', CURRENT_TIMESTAMP, '1');"
);


$CI->db->query(
  "INSERT INTO `tbl_client_menu` (`menu_id`, `label`, `link`, `icon`, `parent`, `sort`, `time`, `status`)
  VALUES (NULL, 'complaints', 'complaints/complaint/complaints_list', 'fa fa-file', '0', '3', CURRENT_TIMESTAMP, '1');"
);


$CI->db->query("CREATE TABLE IF NOT EXISTS  `tbl_complaints_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` mediumtext NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");

$CI->db->query("CREATE TABLE IF NOT EXISTS  `tbl_complaints` (
  `tickets_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) DEFAULT 0,
  `ticket_code` varchar(32) DEFAULT NULL,
  `ticket_type` varchar(32) DEFAULT NULL,
  `ticket_sub_type` varchar(32) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `subject` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `body` text DEFAULT NULL,
  `status` varchar(200) DEFAULT NULL,
  `departments_id` int(11) DEFAULT NULL,
  `reporter` int(11) DEFAULT 0,
  `client` int(11) DEFAULT 0,
  `against` int(11) DEFAULT 0,
  `priority` varchar(50) DEFAULT NULL,
  `upload_file` text DEFAULT NULL,
  `comment` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `lodged_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `permission` text DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `last_reply` varchar(200) DEFAULT NULL,
  `resolver_id` int(11) NOT NULL,
  `resolver_signature` varchar(40) DEFAULT '',
  `resolver_signature_date` datetime DEFAULT NULL,
  `closer_id` int(11) NOT NULL,
  `closer_signature` varchar(40) DEFAULT '',
  `closer_signature_date` datetime DEFAULT NULL DEFAULT NULL,
  PRIMARY KEY (`tickets_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8");

$CI->db->query("CREATE TABLE IF NOT EXISTS  `tbl_complaints_replies` (
  `tickets_replies_id` int(11) NOT NULL AUTO_INCREMENT,
  `tickets_id` bigint(20) DEFAULT NULL,
  `ticket_reply_id` int(11) DEFAULT 0,
  `body` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `replier` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `replierid` int(11) DEFAULT NULL,
  `attachment` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`tickets_replies_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
