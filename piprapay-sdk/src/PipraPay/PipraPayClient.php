<?php

namespace PipraPay;

class PipraPayClient
{
    private $apiUrl;
    private $apiKey;
    private $apiSecret;
    private $merchantId;
    private $webhookSecret;
    private $testMode;
    private $timeout;
    private $lastError;
    private $lastResponse;
    private $gatewayCache;
    private $gatewayCacheEnabled;
    private $gatewayCacheTtl;

    public function __construct(array $config = [])
    {
        $this->apiUrl = $config['api_url'] ?? 'https://payment.yourdomain.com/api/v1';
        $this->apiKey = $config['api_key'] ?? '';
        $this->apiSecret = $config['api_secret'] ?? '';
        $this->merchantId = $config['merchant_id'] ?? '';
        $this->webhookSecret = $config['webhook_secret'] ?? '';
        $this->testMode = $config['test_mode'] ?? true;
        $this->timeout = $config['timeout'] ?? 30;

        $cacheFile = $config['gateway_cache_file'] ?? '';
        $this->gatewayCacheTtl = $config['gateway_cache_ttl'] ?? 3600;
        $this->gatewayCacheEnabled = $config['gateway_cache_enabled'] ?? true;

        $this->gatewayCache = new GatewayCache($cacheFile, $this->gatewayCacheTtl, $this->gatewayCacheEnabled);
    }

    public function initiatePayment(PaymentRequest $request)
    {
        $endpoint = $this->apiUrl . '/payments/initiate';
        
        $data = [
            'merchant_id' => $this->merchantId,
            'amount' => $request->getAmount(),
            'currency' => $request->getCurrency(),
            'invoice_id' => $request->getInvoiceId(),
            'customer_name' => $request->getCustomerName(),
            'customer_email' => $request->getCustomerEmail(),
            'customer_phone' => $request->getCustomerPhone(),
            'gateway' => $request->getGateway(),
            'callback_url' => $request->getCallbackUrl(),
            'success_url' => $request->getSuccessUrl(),
            'cancel_url' => $request->getCancelUrl(),
            'description' => $request->getDescription(),
            'metadata' => $request->getMetadata()
        ];

        return $this->makeRequest('POST', $endpoint, $data);
    }

    public function verifyPayment($transactionId)
    {
        $endpoint = $this->apiUrl . '/payments/verify/' . $transactionId;
        return $this->makeRequest('GET', $endpoint);
    }

    public function getTransaction($transactionId)
    {
        $endpoint = $this->apiUrl . '/transactions/' . $transactionId;
        return $this->makeRequest('GET', $endpoint);
    }

    public function refundPayment($transactionId, $amount = null)
    {
        $endpoint = $this->apiUrl . '/payments/refund';

        $data = [
            'transaction_id' => $transactionId,
            'amount' => $amount
        ];

        return $this->makeRequest('POST', $endpoint, $data);
    }

    public function verifyWebhook($payload, $signature)
    {
        $expectedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);
        return hash_equals($expectedSignature, $signature);
    }

    public function getGateways(bool $useCache = true, bool $activeOnly = true): array
    {
        $cacheKey = 'gateways_' . ($activeOnly ? 'active' : 'all');

        if ($useCache && $this->gatewayCache->isEnabled()) {
            $cached = $this->gatewayCache->get($cacheKey);

            if ($cached !== null) {
                return $cached;
            }
        }

        $response = $this->makeRequest('GET', $this->apiUrl . '/gateways');

        if (!$response['success']) {
            return ['success' => false, 'message' => $response['message'], 'gateways' => new GatewayCollection()];
        }

        $gatewayCollection = GatewayCollection::fromApiResponse($response['data']);

        if ($activeOnly) {
            $gatewayCollection = $gatewayCollection->getActive();
        }

        $gatewaysData = $gatewayCollection->toArray();

        if ($useCache && $this->gatewayCache->isEnabled()) {
            $this->gatewayCache->set($cacheKey, $gatewaysData);
        }

        return [
            'success' => true,
            'gateways' => $gatewayCollection,
            'data' => $gatewaysData
        ];
    }

    public function getGatewayCollection(bool $useCache = true, bool $activeOnly = true): GatewayCollection
    {
        $response = $this->getGateways($useCache, $activeOnly);

        return $response['gateways'] ?? new GatewayCollection();
    }

    public function getGateway(string $code): ?Gateway
    {
        $collection = $this->getGatewayCollection();

        return $collection->getGateway($code);
    }

    public function refreshGateways(): array
    {
        $this->gatewayCache->delete('gateways_active');
        $this->gatewayCache->delete('gateways_all');

        return $this->getGateways(false, false);
    }

    public function clearGatewayCache(): bool
    {
        $this->gatewayCache->delete('gateways_active');
        $this->gatewayCache->delete('gateways_all');

        return true;
    }

    public function isGatewayAvailable(string $code): bool
    {
        $gateway = $this->getGateway($code);

        return $gateway !== null && $gateway->isActive();
    }

    public function getGatewaysForCurrency(string $currency, bool $useCache = true): array
    {
        $collection = $this->getGatewayCollection($useCache, true);

        $filteredCollection = $collection->getByCurrency($currency);

        return [
            'success' => true,
            'gateways' => $filteredCollection,
            'data' => $filteredCollection->toArray()
        ];
    }

    public function validateGatewayConfig(string $code, array $config = []): array
    {
        $gateway = $this->getGateway($code);

        if ($gateway === null) {
            return [
                'success' => false,
                'message' => 'Gateway not found: ' . $code
            ];
        }

        if (!$gateway->isActive()) {
            return [
                'success' => false,
                'message' => 'Gateway is not active: ' . $code
            ];
        }

        if (isset($config['amount']) && !$gateway->isValidAmount($config['amount'])) {
            return [
                'success' => false,
                'message' => sprintf(
                    'Amount %.2f is not within valid range (%.2f - %.2f) for gateway %s',
                    $config['amount'],
                    $gateway->getMinAmount(),
                    $gateway->getMaxAmount(),
                    $code
                )
            ];
        }

        if (isset($config['currency']) && !$gateway->supportsCurrency($config['currency'])) {
            return [
                'success' => false,
                'message' => sprintf(
                    'Gateway %s does not support currency %s. Supported currencies: %s',
                    $code,
                    $config['currency'],
                    implode(', ', $gateway->getCurrencies())
                )
            ];
        }

        return [
            'success' => true,
            'message' => 'Gateway configuration is valid',
            'gateway' => $gateway->toArray()
        ];
    }

    public function getGatewayCache(): GatewayCache
    {
        return $this->gatewayCache;
    }

    public function isGatewayCacheEnabled(): bool
    {
        return $this->gatewayCacheEnabled;
    }

    public function setGatewayCacheEnabled(bool $enabled): self
    {
        $this->gatewayCacheEnabled = $enabled;
        $this->gatewayCache->setEnabled($enabled);
        return $this;
    }

    public function getGatewayCacheTtl(): int
    {
        return $this->gatewayCacheTtl;
    }

    public function setGatewayCacheTtl(int $ttl): self
    {
        $this->gatewayCacheTtl = $ttl;
        $this->gatewayCache->setTtl($ttl);
        return $this;
    }

    public function validateConfig(): array
    {
        $validator = ConfigValidator::create();

        $config = [
            'api_url' => $this->apiUrl,
            'api_key' => $this->apiKey,
            'api_secret' => $this->apiSecret,
            'merchant_id' => $this->merchantId,
            'test_mode' => $this->testMode,
            'timeout' => $this->timeout
        ];

        $isValid = $validator->validate($config);

        return [
            'valid' => $isValid,
            'errors' => $validator->getErrors(),
            'first_error' => $validator->getFirstError()
        ];
    }

    public function testConnection(): array
    {
        $validator = ConfigValidator::create();

        $config = [
            'api_url' => $this->apiUrl,
            'api_key' => $this->apiKey,
            'api_secret' => $this->apiSecret,
            'merchant_id' => $this->merchantId,
            'test_mode' => $this->testMode,
            'timeout' => 10
        ];

        return $validator->validateConnectivity($config);
    }

    protected function makeRequest($method, $url, $data = [])
    {
        $headers = [
            'Content-Type: application/json',
            'X-API-Key: ' . $this->apiKey,
            'X-API-Secret: ' . $this->apiSecret
        ];

        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        if ($this->testMode) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        
        curl_close($ch);

        $this->lastResponse = $response;

        if ($curlError) {
            $this->lastError = 'CURL Error: ' . $curlError;
            return [
                'success' => false,
                'message' => $this->lastError
            ];
        }

        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->lastError = 'Invalid JSON response';
            return [
                'success' => false,
                'message' => 'Invalid response from server'
            ];
        }

        if ($httpCode >= 400) {
            $this->lastError = $decodedResponse['message'] ?? 'HTTP Error: ' . $httpCode;
            return [
                'success' => false,
                'message' => $this->lastError,
                'http_code' => $httpCode
            ];
        }

        return [
            'success' => true,
            'data' => $decodedResponse
        ];
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    public function setTestMode($mode)
    {
        $this->testMode = $mode;
    }

    public function getTestMode()
    {
        return $this->testMode;
    }

    public function setApiUrl($url)
    {
        $this->apiUrl = $url;
    }

    public function getApiUrl()
    {
        return $this->apiUrl;
    }
}
