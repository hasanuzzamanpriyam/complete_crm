<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('piprapay_init')) {

    function piprapay_init(array $config = [])
    {
        $ci =& get_instance();
        $ci->load->library('piprapay_core', $config);

        return $ci->piprapay_core;
    }
}

if (!function_exists('piprapay_client')) {

    function piprapay_client()
    {
        $ci =& get_instance();

        if (!isset($ci->piprapay_core)) {
            $ci->load->library('piprapay_core');
        }

        return $ci->piprapay_core->getClient();
    }
}

if (!function_exists('piprapay_get_gateways')) {

    function piprapay_get_gateways(bool $useCache = true, bool $activeOnly = true)
    {
        $client = piprapay_client();

        return $client->getGateways($useCache, $activeOnly);
    }
}

if (!function_exists('piprapay_get_gateway_collection')) {

    function piprapay_get_gateway_collection(bool $useCache = true, bool $activeOnly = true)
    {
        $client = piprapay_client();

        return $client->getGatewayCollection($useCache, $activeOnly);
    }
}

if (!function_exists('piprapay_get_gateways_for_currency')) {

    function piprapay_get_gateways_for_currency(string $currency, bool $useCache = true)
    {
        $client = piprapay_client();

        return $client->getGatewaysForCurrency($currency, $useCache);
    }
}

if (!function_exists('piprapay_is_gateway_available')) {

    function piprapay_is_gateway_available(string $code): bool
    {
        $client = piprapay_client();

        return $client->isGatewayAvailable($code);
    }
}

if (!function_exists('piprapay_get_gateway')) {

    function piprapay_get_gateway(string $code)
    {
        $client = piprapay_client();

        return $client->getGateway($code);
    }
}

if (!function_exists('piprapay_validate_gateway_config')) {

    function piprapay_validate_gateway_config(string $code, array $config = []): array
    {
        $client = piprapay_client();

        return $client->validateGatewayConfig($code, $config);
    }
}

if (!function_exists('piprapay_create_payment')) {

    function piprapay_create_payment($invoice_id, $amount, $gateway, $customer_data = [])
    {
        $ci =& get_instance();
        $ci->load->library('piprapay_core');

        $invoice_info = get_row('tbl_invoices', array('invoices_id' => $invoice_id));

        if (!$invoice_info) {
            return [
                'success' => false,
                'message' => 'Invoice not found'
            ];
        }

        $client_info = get_row('tbl_client', array('client_id' => $invoice_info->client_id));

        $payment_data = [
            'amount' => $amount,
            'currency' => $invoice_info->currency,
            'invoice_id' => $invoice_id,
            'customer_name' => $customer_data['name'] ?? $client_info->name ?? '',
            'customer_email' => $customer_data['email'] ?? $client_info->email ?? '',
            'customer_phone' => $customer_data['phone'] ?? $client_info->phone ?? '',
            'gateway' => $gateway,
            'callback_url' => site_url('payment/piprapay/callback'),
            'success_url' => site_url('payment/piprapay/success'),
            'cancel_url' => site_url('payment/piprapay/cancel'),
            'description' => 'Payment for Invoice ' . $invoice_info->reference_no,
            'metadata' => [
                'client_id' => $invoice_info->client_id,
                'invoice_reference' => $invoice_info->reference_no
            ]
        ];

        return $ci->piprapay_core->initiatePayment($payment_data);
    }
}

if (!function_exists('piprapay_verify_payment')) {

    function piprapay_verify_payment($transaction_id)
    {
        $ci =& get_instance();
        $ci->load->library('piprapay_core');

        return $ci->piprapay_core->verifyPayment($transaction_id);
    }
}

if (!function_exists('piprapay_refund_payment')) {

    function piprapay_refund_payment($transaction_id, $amount = null)
    {
        $ci =& get_instance();
        $ci->load->library('piprapay_core');

        return $ci->piprapay_core->refundPayment($transaction_id, $amount);
    }
}

if (!function_exists('piprapay_get_transaction')) {

    function piprapay_get_transaction($transaction_id)
    {
        $ci =& get_instance();
        $ci->load->library('piprapay_core');

        return $ci->piprapay_core->getTransactionStatus($transaction_id);
    }
}

if (!function_exists('piprapay_refresh_gateways')) {

    function piprapay_refresh_gateways()
    {
        $client = piprapay_client();

        return $client->refreshGateways();
    }
}

if (!function_exists('piprapay_clear_cache')) {

    function piprapay_clear_cache()
    {
        $client = piprapay_client();

        return $client->clearGatewayCache();
    }
}

if (!function_exists('piprapay_gateway_options_dropdown')) {

    function piprapay_gateway_options_dropdown(string $selected = '', string $currency = null, array $attributes = []): string
    {
        $response = piprapay_get_gateways_for_currency($currency ?? 'BDT');

        if (!$response['success']) {
            return '<option value="">No gateways available</option>';
        }

        $gateways = $response['data'] ?? [];

        if (empty($gateways)) {
            return '<option value="">No gateways available</option>';
        }

        $html = '<option value="">Select Gateway</option>';

        foreach ($gateways as $gateway) {
            $code = $gateway['code'] ?? '';
            $name = $gateway['name'] ?? '';
            $active = $gateway['active'] ?? false;

            if (!$active) {
                continue;
            }

            $isSelected = $selected === $code ? 'selected' : '';
            $html .= "<option value=\"{$code}\" {$isSelected}>{$name}</option>";
        }

        return $html;
    }
}

if (!function_exists('piprapay_get_config')) {

    function piprapay_get_config()
    {
        $ci =& get_instance();
        $ci->config->load('piprapay', TRUE);

        return $ci->config->item('piprapay');
    }
}

if (!function_exists('piprapay_is_enabled')) {

    function piprapay_is_enabled(): bool
    {
        $config = piprapay_get_config();

        return isset($config['piprapay_enabled']) && $config['piprapay_enabled'] === TRUE;
    }
}

if (!function_exists('piprapay_is_test_mode')) {

    function piprapay_is_test_mode(): bool
    {
        $config = piprapay_get_config();

        return isset($config['piprapay_test_mode']) && $config['piprapay_test_mode'] === TRUE;
    }
}

if (!function_exists('piprapay_format_gateway_icon')) {

    function piprapay_format_gateway_icon(string $icon): string
    {
        if (empty($icon)) {
            return '';
        }

        if (filter_var($icon, FILTER_VALIDATE_URL)) {
            return "<img src=\"{$icon}\" alt=\"Gateway Icon\" style=\"max-width: 30px; max-height: 30px;\">";
        }

        return '';
    }
}

if (!function_exists('piprapay_log_error')) {

    function piprapay_log_error(string $message, array $context = [])
    {
        $ci =& get_instance();

        if (function_exists('log_message')) {
            log_message('error', '[PipraPay] ' . $message, $context);
        }
    }
}

if (!function_exists('piprapay_log_info')) {

    function piprapay_log_info(string $message, array $context = [])
    {
        $ci =& get_instance();

        if (function_exists('log_message')) {
            log_message('info', '[PipraPay] ' . $message, $context);
        }
    }
}
