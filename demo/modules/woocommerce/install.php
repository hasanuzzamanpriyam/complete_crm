<?php
defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();

$CI->db->query("INSERT INTO `tbl_menu` (`menu_id`, `label`, `link`, `icon`, `parent`, `sort`, `time`, `status`) VALUES (NULL, 'woocommerce', '#', 'fa fa-file-excel-o', 0, 1, '2022-06-06 08:36:23', 1)");
$woocommerce_id = get_any_field('tbl_menu', array('label' => 'woocommerce'), 'menu_id');
$CI->db->query("INSERT INTO `tbl_menu` (`menu_id`, `label`, `link`, `icon`, `parent`, `sort`, `status`) VALUES
        (NULL, 'orders', 'admin/woocommerce/orders', 'fa fa-circle-o', $woocommerce_id, '1', '1'),
        (NULL, 'products', 'admin/woocommerce/products', 'fa fa-circle-o', $woocommerce_id, '1', '1'),
        (NULL, 'customers', 'admin/woocommerce/customers', 'fa fa-circle-o', $woocommerce_id, '1', '1'),
        (NULL, 'stores', 'admin/woocommerce/stores', 'fa fa-circle-o', $woocommerce_id, '1', '1')"
);

$CI->db->query('CREATE TABLE IF NOT EXISTS `tbl_woocommerce_assigned` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
$CI->db->query('CREATE TABLE IF NOT EXISTS `tbl_woocommerce_customers` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `woo_customer_id` int NOT NULL,
  `userid` int DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `avatar_url` text,
  `store_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;');
$CI->db->query('CREATE TABLE IF NOT EXISTS `tbl_woocommerce_orders` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `customer_id` int NOT NULL,
  `address` text,
  `phone` varchar(50) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `date_created` date DEFAULT NULL,
  `date_modified` date DEFAULT NULL,
  `total` varchar(30) DEFAULT NULL,
  `invoice_id` int DEFAULT NULL,
  `store_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
$CI->db->query('CREATE TABLE IF NOT EXISTS `tbl_woocommerce_products` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `itemid` int DEFAULT NULL,
  `name` varchar(500) DEFAULT NULL,
  `permalink` varchar(500) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `price` varchar(20) DEFAULT NULL,
  `sales` varchar(20) DEFAULT NULL,
  `picture` text,
  `short_description` varchar(512) DEFAULT NULL,
  `category` text,
  `date_created` date DEFAULT NULL,
  `date_modified` date DEFAULT NULL,
  `store_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
$CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_woocommerce_stores` (
  `store_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_name` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `secret` varchar(250) DEFAULT NULL,
  `productPage` int DEFAULT NULL,
  `orderPage` int DEFAULT '1',
  `customerPage` int DEFAULT '1',
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
$CI->db->query('CREATE TABLE IF NOT EXISTS `tbl_woocommerce_summary` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int DEFAULT NULL,
  `customers` text,
  `orders` text,
  `products` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;');









