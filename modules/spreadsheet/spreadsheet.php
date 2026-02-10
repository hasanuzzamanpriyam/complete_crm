<?php defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Spreadsheet online for ZiscorERP
Module ID: 31883927
Module uri: https://codecanyon.net/item/spreadsheet-online-for-ziscorerp/31883927
Description: Spreadsheet Online Management for ZiscoERP
Version: 1.0.1
Author: unique_coder
Author uri: https://codecanyon.net/user/unique_coder
Requires at least: 4.0.0
*/
define('SPREADSHEET_MODULE', 'spreadsheet');

/**
 * Load the module helper
 */
$CI = &get_instance();

/**
 * Register language files, must be registered if the module is using languages
 */
module_languagesFiles(SPREADSHEET_MODULE, [SPREADSHEET_MODULE]);

$CI->load->helper(SPREADSHEET_MODULE . '/spreadsheet');
