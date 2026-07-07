<?php defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Jitsi Video Calling for ZiscoERP
Module ID: 99001001
Module uri: https://jitsi.org
Description: Jitsi Video Meeting Management with JWT Authentication for ZiscoERP
Version: 1.0.0
Author: unique_coder
Author uri: https://codecanyon.net/user/unique_coder
Requires at least: 4.0.2
*/
define('JITSI_MODULE', 'jitsi');

$CI = &get_instance();

module_languagesFiles(JITSI_MODULE, [JITSI_MODULE]);

$CI->load->helper(JITSI_MODULE . '/jitsi');
