<?php defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Woocommerce Module for ZiscorERP
Module ID: 31884928
Module uri: https://codecanyon.net/item/quickbooks-sync-for-ziscorerp/31884927
Description: Woocommerce  Management for ZiscoERP
Version: 1.0.1
Author: unique_coder
Author uri: https://codecanyon.net/user/unique_coder
Requires at least: 4.0.2
*/
define('WOOCOMMERCE_MODULE', 'woocommerce');

$CI = &get_instance();
module_languagesFiles(WOOCOMMERCE_MODULE, ['woocommerce']);
$CI->load->helper(WOOCOMMERCE_MODULE . '/woocommerce');
