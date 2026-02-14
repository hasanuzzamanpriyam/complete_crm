<?php

defined('BASEPATH') or exit('No direct script access allowed');

$config['piprapay_enabled'] = FALSE;
$config['piprapay_api_url'] = 'https://payment.yourdomain.com/api/v1';
$config['piprapay_api_key'] = '';
$config['piprapay_api_secret'] = '';
$config['piprapay_merchant_id'] = '';
$config['piprapay_webhook_secret'] = '';
$config['piprapay_test_mode'] = TRUE;
$config['piprapay_timeout'] = 30;
$config['piprapay_default_gateway'] = 'bkash';

$config['piprapay_gateway_cache_ttl'] = 3600;
$config['piprapay_gateway_cache_enabled'] = TRUE;

$config['piprapay_fallback_gateways'] = [];
