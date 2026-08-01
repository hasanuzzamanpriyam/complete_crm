<?php
/**
 * Global-scope runner for model-level tests that need a booted CodeIgniter
 * instance. `index.php` must be required at the top level of a script so the
 * HMVC router's `global $CFG` reference resolves; PHPUnit's own bootstrap is
 * included from inside a method and would not. Boot CI here, then delegate to
 * PHPUnit.
 *
 * Usage:
 *   php application/tests/models/run-model-tests.php -c application/tests/models/phpunit-model.xml
 */
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', '1');

$_SERVER['HTTP_USER_AGENT'] = 'PHPUnit';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_PORT'] = '80';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';
$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/../../..';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['QUERY_STRING'] = '';

require_once __DIR__ . '/../../../vendor/autoload.php';

$saved_argv = $_SERVER['argv'];
$_SERVER['argv'] = array($saved_argv[0]);
ini_set('display_errors', '0');
ob_start();
require_once __DIR__ . '/../../../index.php';
ob_end_clean();
ini_set('display_errors', '1');
$_SERVER['argv'] = $saved_argv;

require_once __DIR__ . '/../../../vendor/phpunit/phpunit/phpunit';
