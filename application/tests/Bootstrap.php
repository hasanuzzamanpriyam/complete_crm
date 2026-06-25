<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../../vendor/autoload.php';

define('BASEPATH', realpath(__DIR__ . '/../../system/') . '/');
define('APPPATH', realpath(__DIR__ . '/../') . '/');
define('VIEWPATH', APPPATH . 'views/');
define('FCPATH', realpath(__DIR__ . '/../../') . '/');
define('ENVIRONMENT', 'testing');
