<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . '../piprapay-sdk/src/PipraPay/Gateway.php';
require_once APPPATH . '../piprapay-sdk/src/PipraPay/GatewayCollection.php';
require_once APPPATH . '../piprapay-sdk/src/PipraPay/GatewayCache.php';

use PipraPay\PipraPayClient;
use PipraPay\PaymentRequest;
use PipraPay\Gateway;
use PipraPay\GatewayCollection;

class Piprapay_core
{
    protected $ci;
    protected $client;
    protected $api_url;
    protected $api_key;
    protected $api_secret;
    protected $merchant_id;
    protected $test_mode;
    protected $timeout;
    protected $last_error;
    protected $last_response;
    protected $gateway_cache_ttl;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->config('piprapay', TRUE);

        $piprapay_config = $this->ci->config->item('piprapay');

        $this->api_url = $piprapay_config['api_url'];
        $this->api_key = $piprapay_config['api_key'];
        $this->api_secret = $piprapay_config['api_secret'];
        $this->merchant_id = $piprapay_config['merchant_id'];
        $this->test_mode = $piprapay_config['test_mode'];
        $this->timeout = $piprapay_config['timeout'];
        $this->gateway_cache_ttl = $piprapay_config['gateway_cache_ttl'] ?? 3600;

        $this->initializeClient();
    }

    protected function initializeClient()
    {
        $piprapay_config = $this->ci->config->item('piprapay');
        $cacheFile = APPPATH . 'cache/piprapay_gateways.json';

        $config = [
            'api_url' => $this->api_url,
            'api_key' => $this->api_key,
            'api_secret' => $this->api_secret,
            'merchant_id' => $this->merchant_id,
            'webhook_secret' => $piprapay_config['webhook_secret'] ?? '',
            'test_mode' => $this->test_mode,
            'timeout' => $this->timeout,
            'gateway_cache_file' => $cacheFile,
            'gateway_cache_ttl' => $this->gateway_cache_ttl,
            'gateway_cache_enabled' => true
        ];

        $this->client = new PipraPayClient($config);
    }

    public function getClient(): PipraPayClient
    {
        return $this->client;
    }

    public function initiatePayment($payment_data)
    {
        $endpoint = $this->api_url . '/payments/initiate';
        
        $data = array(
            'merchant_id' => $this->merchant_id,
            'amount' => $payment_data['amount'],
            'currency' => isset($payment_data['currency']) ? $payment_data['currency'] : 'BDT',
            'invoice_id' => $payment_data['invoice_id'],
            'customer_name' => isset($payment_data['customer_name']) ? $payment_data['customer_name'] : '',
            'customer_email' => isset($payment_data['customer_email']) ? $payment_data['customer_email'] : '',
            'customer_phone' => isset($payment_data['customer_phone']) ? $payment_data['customer_phone'] : '',
            'gateway' => isset($payment_data['gateway']) ? $payment_data['gateway'] : $this->ci->config->item('default_gateway', 'piprapay'),
            'callback_url' => $payment_data['callback_url'],
            'success_url' => $payment_data['success_url'],
            'cancel_url' => $payment_data['cancel_url'],
            'description' => isset($payment_data['description']) ? $payment_data['description'] : '',
            'metadata' => isset($payment_data['metadata']) ? $payment_data['metadata'] : array()
        );

        return $this->makeRequest('POST', $endpoint, $data);
    }

    public function verifyPayment($transaction_id)
    {
        $endpoint = $this->api_url . '/payments/verify/' . $transaction_id;
        
        return $this->makeRequest('GET', $endpoint);
    }

    public function getTransactionStatus($transaction_id)
    {
        $endpoint = $this->api_url . '/transactions/' . $transaction_id;
        
        return $this->makeRequest('GET', $endpoint);
    }

    public function refundPayment($transaction_id, $amount = null)
    {
        $endpoint = $this->api_url . '/payments/refund';
        
        $data = array(
            'transaction_id' => $transaction_id,
            'amount' => $amount
        );

        return $this->makeRequest('POST', $endpoint, $data);
    }

    public function getSupportedGateways()
    {
        $endpoint = $this->api_url . '/gateways';

        return $this->makeRequest('GET', $endpoint);
    }

    public function getGateways(bool $useCache = true, bool $activeOnly = true): array
    {
        return $this->client->getGateways($useCache, $activeOnly);
    }

    public function getGatewayCollection(bool $useCache = true, bool $activeOnly = true): GatewayCollection
    {
        return $this->client->getGatewayCollection($useCache, $activeOnly);
    }

    public function getGatewaysForCurrency(string $currency, bool $useCache = true): array
    {
        return $this->client->getGatewaysForCurrency($currency, $useCache);
    }

    public function isGatewayAvailable(string $code): bool
    {
        return $this->client->isGatewayAvailable($code);
    }

    public function validateGatewayConfig(string $code, array $config = []): array
    {
        return $this->client->validateGatewayConfig($code, $config);
    }

    public function refreshGateways(): array
    {
        return $this->client->refreshGateways();
    }

    public function clearGatewayCache(): bool
    {
        return $this->client->clearGatewayCache();
    }

    public function verifyWebhook($payload, $signature)
    {
        $webhook_secret = $this->ci->config->item('webhook_secret', 'piprapay');
        
        $expected_signature = hash_hmac('sha256', $payload, $webhook_secret);
        
        return hash_equals($expected_signature, $signature);
    }

    protected function makeRequest($method, $url, $data = array())
    {
        $headers = array(
            'Content-Type: application/json',
            'X-API-Key: ' . $this->api_key,
            'X-API-Secret: ' . $this->api_secret
        );

        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        if ($this->test_mode) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        
        curl_close($ch);

        $this->last_response = $response;

        if ($curl_error) {
            $this->last_error = $curl_error;
            return array(
                'success' => false,
                'message' => 'CURL Error: ' . $curl_error
            );
        }

        $decoded_response = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->last_error = 'Invalid JSON response';
            return array(
                'success' => false,
                'message' => 'Invalid response from server'
            );
        }

        if ($http_code >= 400) {
            $this->last_error = isset($decoded_response['message']) ? $decoded_response['message'] : 'HTTP Error: ' . $http_code;
            return array(
                'success' => false,
                'message' => $this->last_error,
                'http_code' => $http_code
            );
        }

        return array(
            'success' => true,
            'data' => $decoded_response
        );
    }

    public function getLastError()
    {
        return $this->last_error;
    }

    public function getLastResponse()
    {
        return $this->last_response;
    }

    public function setTestMode($mode)
    {
        $this->test_mode = $mode;
    }

    public function getTestMode()
    {
        return $this->test_mode;
    }
}
