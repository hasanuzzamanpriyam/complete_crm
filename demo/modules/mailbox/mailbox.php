<?php defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Mailbox - e-mail client for ZiscoERP
Module ID: 32006405
Module uri: https://mailbox.ziscoerp.com
Description: Mailbox - Webmail based e-mail client Management for ZiscoERP
Version: 1.0.1
Author: unique_coder
Author uri: https://codecanyon.net/user/unique_coder
Requires at least: 4.0.0
*/
define('MAILBOX_MODULE', 'mailbox');

$CI = &get_instance();
module_languagesFiles(MAILBOX_MODULE, ['mailbox']);

$CI->module->css = [
    'modules/' . MAILBOX_MODULE . '/assets/css/styles.css',
];

$CI->load->helper(MAILBOX_MODULE . '/mailbox');
