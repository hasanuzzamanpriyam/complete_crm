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

    public function __construct(array $config = [])
    {
        $this->apiUrl = $config['api_url'] ?? 'https://payment.yourdomain.com/api/v1';
        $this->apiKey = $config['api_key'] ?? '';
        $this->apiSecret = $config['api_secret'] ?? '';
        $this->merchantId = $config['merchant_id'] ?? '';
        $this->webhookSecret = $config['webhook_secret'] ?? '';
        $this->testMode = $config['test_mode'] ?? true;
        $this->timeout = $config['timeout'] ?? 30;
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

    public function getGateways()
    {
        $endpoint = $this->apiUrl . '/gateways';
        return $this->makeRequest('GET', $endpoint);
    }

    public function verifyWebhook($payload, $signature)
    {
        $expectedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);
        return hash_equals($expectedSignature, $signature);
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
