<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Load SDK autoloader — require once handles repeated includes gracefully
require_once APPPATH . '../piprapay-sdk/src/PipraPay/Gateway.php';
require_once APPPATH . '../piprapay-sdk/src/PipraPay/GatewayCollection.php';
require_once APPPATH . '../piprapay-sdk/src/PipraPay/GatewayCache.php';
require_once APPPATH . '../piprapay-sdk/src/PipraPay/PaymentRequest.php';
require_once APPPATH . '../piprapay-sdk/src/PipraPay/Transaction.php';
require_once APPPATH . '../piprapay-sdk/src/PipraPay/PipraPayClient.php';

use PipraPay\PipraPayClient;
use PipraPay\PaymentRequest;
use PipraPay\Gateway;
use PipraPay\GatewayCollection;

/**
 * Piprapay_core
 *
 * Thin CI3 wrapper around the PipraPayClient SDK.
 * ALL Piprapay communication (invoices AND external-project hub) goes through here.
 * No other class should instantiate PipraPayClient directly.
 */
class Piprapay_core
{
    /** @var \CI_Controller */
    protected $ci;

    /** @var PipraPayClient */
    protected $client;

    protected $api_url;
    protected $api_key;
    protected $api_secret;
    protected $merchant_id;
    protected $webhook_secret;
    protected $test_mode;
    protected $timeout;
    protected $last_error;
    protected $last_response;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->config('piprapay', TRUE);

        $cfg = $this->ci->config->item('piprapay');

        // Support both flat ("piprapay_*") and nested key formats
        $this->api_url        = $cfg['piprapay_api_url']        ?? $cfg['api_url']        ?? '';
        $this->api_key        = $cfg['piprapay_api_key']        ?? $cfg['api_key']        ?? '';
        $this->api_secret     = $cfg['piprapay_api_secret']     ?? $cfg['api_secret']     ?? '';
        $this->merchant_id    = $cfg['piprapay_merchant_id']    ?? $cfg['merchant_id']    ?? '';
        $this->webhook_secret = $cfg['piprapay_webhook_secret'] ?? $cfg['webhook_secret'] ?? '';
        $this->test_mode      = $cfg['piprapay_test_mode']      ?? $cfg['test_mode']      ?? true;
        $this->timeout        = $cfg['piprapay_timeout']        ?? $cfg['timeout']        ?? 30;

        $cache_ttl     = $cfg['piprapay_gateway_cache_ttl'] ?? 3600;
        $cache_enabled = $cfg['piprapay_gateway_cache_enabled'] ?? true;
        $cache_file    = APPPATH . 'cache/piprapay_gateways.json';

        $this->client = new PipraPayClient([
            'api_url'              => $this->api_url,
            'api_key'              => $this->api_key,
            'api_secret'           => $this->api_secret,
            'merchant_id'          => $this->merchant_id,
            'webhook_secret'       => $this->webhook_secret,
            'test_mode'            => $this->test_mode,
            'timeout'              => $this->timeout,
            'gateway_cache_file'   => $cache_file,
            'gateway_cache_ttl'    => $cache_ttl,
            'gateway_cache_enabled'=> $cache_enabled,
        ]);
    }

    // -----------------------------------------------------------------------
    // DIRECT SDK ACCESS
    // -----------------------------------------------------------------------

    /**
     * Returns the raw PipraPayClient instance for advanced SDK usage.
     */
    public function getClient(): PipraPayClient
    {
        return $this->client;
    }

    // -----------------------------------------------------------------------
    // PAYMENT OPERATIONS
    // -----------------------------------------------------------------------

    /**
     * Initiate a payment by building a proper PaymentRequest object.
     *
     * FIX (Bug #2): Previously passed a raw array directly to makeRequest(),
     * bypassing the SDK's PaymentRequest validation entirely. Now we build a
     * PaymentRequest so SDK validation (amount range, currency support, URL
     * format checks) runs before any HTTP call is made.
     *
     * @param  array $payment_data  Associative array with payment details.
     * @return array  ['success' => bool, 'data' => [...]] | ['success' => false, 'message' => '...']
     */
    public function initiatePayment(array $payment_data)
    {
        $request = new PaymentRequest();
        $request->setAmount((float) ($payment_data['amount'] ?? 0))
                ->setCurrency($payment_data['currency']        ?? 'BDT')
                ->setInvoiceId($payment_data['invoice_id']     ?? '')
                ->setGateway($payment_data['gateway']          ?? $this->ci->config->item('piprapay_default_gateway', 'piprapay') ?? 'bkash')
                ->setCallbackUrl($payment_data['callback_url'] ?? '')
                ->setSuccessUrl($payment_data['success_url']   ?? '')
                ->setCancelUrl($payment_data['cancel_url']     ?? '')
                ->setCustomerName($payment_data['customer_name']   ?? '')
                ->setCustomerEmail($payment_data['customer_email'] ?? '')
                ->setCustomerPhone($payment_data['customer_phone'] ?? '')
                ->setDescription($payment_data['description']  ?? '')
                ->setMetadata($payment_data['metadata']        ?? []);

        // Validate before making a network call
        $validation = $request->validate();
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => implode('; ', $validation['errors']),
            ];
        }

        return $this->client->initiatePayment($request);
    }

    /**
     * Verify a payment by gateway transaction ID.
     */
    public function verifyPayment($transaction_id)
    {
        return $this->client->verifyPayment($transaction_id);
    }

    /**
     * Get full transaction details.
     */
    public function getTransactionStatus($transaction_id)
    {
        return $this->client->getTransaction($transaction_id);
    }

    /**
     * Request a refund.
     */
    public function refundPayment($transaction_id, $amount = null)
    {
        return $this->client->refundPayment($transaction_id, $amount);
    }

    // -----------------------------------------------------------------------
    // GATEWAY LISTING
    // FIX (Bug #3): All gateway methods now delegate entirely to the SDK client
    // and always return a consistent ['success', 'data' => [plain array]] shape.
    // The GatewayCollection object is never returned raw to callers.
    // -----------------------------------------------------------------------

    /**
     * Get all active gateways as a plain PHP array.
     *
     * Returns:
     *   ['success' => true,  'data' => [[code, name, active, currencies, ...], ...]]
     *   ['success' => false, 'message' => '...']
     */
    public function getGateways(bool $useCache = true, bool $activeOnly = true): array
    {
        $result = $this->client->getGateways($useCache, $activeOnly);

        if (empty($result['success'])) {
            return [
                'success' => false,
                'message' => $result['message'] ?? 'Failed to retrieve gateways',
                'data'    => [],
            ];
        }

        // Always convert GatewayCollection → plain array
        $collection = $result['gateways'] ?? new GatewayCollection();
        return [
            'success' => true,
            'data'    => $collection instanceof GatewayCollection
                             ? $collection->toArray()
                             : (array) $collection,
        ];
    }

    /**
     * Get gateways filtered by currency, as a plain PHP array.
     */
    public function getGatewaysForCurrency(string $currency, bool $useCache = true): array
    {
        $result = $this->client->getGatewaysForCurrency($currency, $useCache);

        $collection = $result['gateways'] ?? new GatewayCollection();
        return [
            'success' => true,
            'data'    => $collection instanceof GatewayCollection
                             ? $collection->toArray()
                             : (array) $collection,
        ];
    }

    /**
     * Return a GatewayCollection object (for callers that need the full object API).
     */
    public function getGatewayCollection(bool $useCache = true, bool $activeOnly = true): GatewayCollection
    {
        return $this->client->getGatewayCollection($useCache, $activeOnly);
    }

    /**
     * Check whether a specific gateway code is active.
     */
    public function isGatewayAvailable(string $code): bool
    {
        return $this->client->isGatewayAvailable($code);
    }

    /**
     * Validate gateway config (amount range, currency).
     */
    public function validateGatewayConfig(string $code, array $config = []): array
    {
        return $this->client->validateGatewayConfig($code, $config);
    }

    /**
     * Force-refresh gateways, bypassing cache.
     */
    public function refreshGateways(): array
    {
        return $this->getGateways(false, false);
    }

    /**
     * Clear the gateway list cache file.
     */
    public function clearGatewayCache(): bool
    {
        return $this->client->clearGatewayCache();
    }

    // -----------------------------------------------------------------------
    // WEBHOOK
    // -----------------------------------------------------------------------

    /**
     * Verify an incoming Piprapay webhook signature using HMAC-SHA256.
     *
     * @param  string $payload    Raw request body.
     * @param  string $signature  Value of the X-Signature header.
     * @return bool
     */
    public function verifyWebhook($payload, $signature): bool
    {
        if (empty($this->webhook_secret)) {
            log_message('error', '[PipraPay] verifyWebhook called but webhook_secret is empty.');
            return false;
        }
        return $this->client->verifyWebhook($payload, $signature);
    }

    // -----------------------------------------------------------------------
    // DEBUGGING / INTROSPECTION
    // -----------------------------------------------------------------------

    public function getLastError()
    {
        return $this->client->getLastError();
    }

    public function getLastResponse()
    {
        return $this->client->getLastResponse();
    }

    public function getTestMode(): bool
    {
        return (bool) $this->test_mode;
    }

    public function setTestMode(bool $mode): void
    {
        $this->test_mode = $mode;
        $this->client->setTestMode($mode);
    }
}
