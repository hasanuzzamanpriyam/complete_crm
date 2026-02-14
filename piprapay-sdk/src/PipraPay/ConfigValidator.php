<?php

namespace PipraPay;

class ConfigValidator
{
    private array $requiredFields = [
        'api_url',
        'api_key',
        'api_secret',
        'merchant_id'
    ];

    private array $errors = [];

    public function validate(array $config): bool
    {
        $this->errors = [];

        $this->validateRequiredFields($config);
        $this->validateApiUrl($config);
        $this->validateApiKeys($config);
        $this->validateMerchantId($config);
        $this->validateTestMode($config);
        $this->validateTimeout($config);

        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): ?string
    {
        return $this->errors[0] ?? null;
    }

    private function validateRequiredFields(array $config): void
    {
        foreach ($this->requiredFields as $field) {
            if (!isset($config[$field]) || empty($config[$field])) {
                $this->errors[] = "Required field '{$field}' is missing or empty";
            }
        }
    }

    private function validateApiUrl(array $config): void
    {
        if (!isset($config['api_url'])) {
            return;
        }

        $url = $config['api_url'];

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->errors[] = "API URL is not a valid URL: {$url}";
        }

        if (!preg_match('/^https:\/\//i', $url)) {
            $this->errors[] = "API URL must use HTTPS: {$url}";
        }
    }

    private function validateApiKeys(array $config): void
    {
        if (!isset($config['api_key'])) {
            return;
        }

        $apiKey = $config['api_key'];

        if (strlen($apiKey) < 16) {
            $this->errors[] = "API key appears to be too short (minimum 16 characters expected)";
        }

        if (!isset($config['api_secret'])) {
            return;
        }

        $apiSecret = $config['api_secret'];

        if (strlen($apiSecret) < 16) {
            $this->errors[] = "API secret appears to be too short (minimum 16 characters expected)";
        }

        if ($apiKey === $apiSecret) {
            $this->errors[] = "API key and API secret should not be the same";
        }
    }

    private function validateMerchantId(array $config): void
    {
        if (!isset($config['merchant_id'])) {
            return;
        }

        $merchantId = $config['merchant_id'];

        if (!is_string($merchantId) && !is_numeric($merchantId)) {
            $this->errors[] = "Merchant ID must be a string or number";
        }
    }

    private function validateTestMode(array $config): void
    {
        if (isset($config['test_mode'])) {
            $testMode = $config['test_mode'];

            if (!is_bool($testMode) && !in_array($testMode, ['true', 'false', '1', '0', 1, 0], true)) {
                $this->errors[] = "Test mode must be a boolean value";
            }
        }
    }

    private function validateTimeout(array $config): void
    {
        if (isset($config['timeout'])) {
            $timeout = $config['timeout'];

            if (!is_numeric($timeout)) {
                $this->errors[] = "Timeout must be a number";
            }

            if ($timeout < 1 || $timeout > 300) {
                $this->errors[] = "Timeout must be between 1 and 300 seconds";
            }
        }
    }

    public function validateConnectivity(array $config): array
    {
        if (!$this->validate($config)) {
            return [
                'success' => false,
                'message' => 'Invalid configuration: ' . $this->getFirstError(),
                'errors' => $this->errors
            ];
        }

        $testUrl = rtrim($config['api_url'], '/') . '/health';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $testUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-API-Key: ' . $config['api_key'],
            'X-API-Secret: ' . $config['api_secret']
        ]);

        if (isset($config['test_mode']) && $config['test_mode']) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $error,
                'http_code' => null
            ];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'message' => 'Connection successful',
                'http_code' => $httpCode,
                'response' => json_decode($response, true)
            ];
        }

        return [
            'success' => false,
            'message' => "Server returned HTTP code {$httpCode}",
            'http_code' => $httpCode,
            'response' => $response
        ];
    }

    public static function create(): self
    {
        return new self();
    }

    public function setRequiredFields(array $fields): self
    {
        $this->requiredFields = $fields;
        return $this;
    }

    public function getRequiredFields(): array
    {
        return $this->requiredFields;
    }
}
