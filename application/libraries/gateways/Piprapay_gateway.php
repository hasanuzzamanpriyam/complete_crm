<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Piprapay (PayTic) gateway wrapper for CodeIgniter 3.
 * Handles checkout creation, verification and refunds via the PayTic API.
 */
class Piprapay_gateway {
    private $apiUrl;
    private $apiKey;
    private $testMode;

    public function __construct() {
        $CI =& get_instance();
        $CI->load->config('piprapay', TRUE);
        $cfg = $CI->config->item('piprapay') ?: [];

        $this->apiUrl   = rtrim(
            ($cfg['base_url'] ?? $CI->config->item('piprapay_api_url')) ?: '',
            '/'
        );
        $this->apiKey   = $cfg['api_key'] ?? $CI->config->item('piprapay_api_key') ?: '';
        $this->testMode = (bool) ($cfg['test_mode'] ?? $CI->config->item('piprapay_test_mode') ?: FALSE);

        if (empty($this->apiUrl) || empty($this->apiKey)) {
            log_message('error', 'Piprapay configuration missing or incomplete');
        }
    }

    private function authHeader(): array {
        return ['MHS-PIPRAPAY-API-KEY: ' . $this->apiKey];
    }

    /**
     * Fetch dynamic list of payment gateways.
     * Returns array of ['id'=>..., 'name'=>...] or empty array on error.
     */
    public function fetch_gateways(): array {
        $url = $this->apiUrl . '/gateway/list';
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $this->authHeader(),
            CURLOPT_TIMEOUT        => 15,
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code !== 200) {
            log_message('error', "Piprapay fetch_gateways failed HTTP $code");
            return [];
        }
        $data = json_decode($resp, true);
        return $data['gateways'] ?? [];
    }

    /**
     * Create a checkout session.
     * @param array $payload Required fields per PayTic docs.
     * @return array ['pp_id'=>string,'pp_url'=>string]
     */
    public function create_checkout(array $payload): array {
        $url = $this->apiUrl . '/checkout/redirect';
        $ch  = curl_init($url);
        $json = json_encode($payload);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $json,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => array_merge($this->authHeader(), ['Content-Type: application/json']),
            CURLOPT_TIMEOUT        => 20,
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code !== 200) {
            throw new Exception("Piprapay checkout failed HTTP $code");
        }
        $data = json_decode($resp, true);
        return [
            'pp_id'  => $data['pp_id'] ?? '',
            'pp_url' => $data['pp_url'] ?? '',
        ];
    }

    /** Verify a payment after return or webhook */
    public function verify_payment(string $ppId): array {
        $url = $this->apiUrl . '/verify-payment';
        $ch  = curl_init($url);
        $payload = json_encode(['pp_id' => $ppId]);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => array_merge($this->authHeader(), ['Content-Type: application/json']),
            CURLOPT_TIMEOUT        => 15,
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code !== 200) {
            throw new Exception("Piprapay verification failed HTTP $code");
        }
        return json_decode($resp, true);
    }

    /** Refund a payment */
    public function refund_payment(string $ppId, float $amount): array {
        $url = $this->apiUrl . '/refund-payment';
        $ch  = curl_init($url);
        $payload = json_encode(['pp_id' => $ppId, 'amount' => $amount]);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => array_merge($this->authHeader(), ['Content-Type: application/json']),
            CURLOPT_TIMEOUT        => 20,
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code !== 200) {
            throw new Exception("Piprapay refund failed HTTP $code");
        }
        return json_decode($resp, true);
    }
}
?>
