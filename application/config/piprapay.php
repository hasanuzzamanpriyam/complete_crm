<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PipraPay (PayTic) global configuration.
 *
 * All values are loadable via  $this->load->config('piprapay', TRUE)
 * and accessible as $this->config->item('<key>', 'piprapay').
 */
$config['piprapay'] = [

    // Global enable/disable – admin UI toggles this
    'enabled'        => TRUE,

    // Base API URL (no trailing slash)
    'base_url'       => 'https://pay.tic.bd/api',

    // Endpoint paths (appended to base_url)
    'checkout_path'  => '/checkout/redirect',
    'verify_path'    => '/verify-payment',
    'refund_path'    => '/refund-payment',

    // Credentials
    'api_key'        => '17f6f097f58f41431c9b33fa437c9ef289113ab45eee91ad08',

    // Auth header – PipraPay custom header (not standard Bearer)
    'auth_header'    => 'MHS-PIPRAPAY-API-KEY',

    // Environment: TRUE = sandbox, FALSE = production
    'test_mode'      => TRUE,
];
