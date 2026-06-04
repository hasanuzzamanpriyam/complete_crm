<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PipraPay (PayTic) core driver.
 *
 * Usage from any controller/model/library:
 *   $this->load->library('Piprapay');
 *   $result = $this->piprapay->create_checkout_session(...);
 *
 * All cURL calls are logged with CI3's log_message().
 */
class Piprapay_lib {

    /** @var CI_Controller */
    protected $CI;

    protected $baseUrl;
    protected $apiKey;
    protected $authHeader;
    protected $testMode;

    // --------------------------------------------------------------------

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->config('piprapay', TRUE);

        $cfg = $this->CI->config->item('piprapay', 'piprapay') ?: [];

        $this->baseUrl     = rtrim(
            ($cfg['base_url'] ?? $this->CI->config->item('piprapay_api_url')) ?: '',
            '/'
        );
        $this->apiKey      = $cfg['api_key']  ?? $this->CI->config->item('piprapay_api_key') ?: '';
        $this->authHeader  = $cfg['auth_header'] ?? 'MHS-PIPRAPAY-API-KEY';
        $this->testMode    = (bool) ($cfg['test_mode'] ?? $this->CI->config->item('piprapay_test_mode') ?: FALSE);
    }

    // --------------------------------------------------------------------

    /**
     * Build the required HTTP headers for PipraPay.
     *
     * PipraPay uses a custom header (MHS-PIPRAPAY-API-KEY) with the raw key.
     * No "Bearer" prefix is added.
     *
     * @return string[]
     */
    protected function _headers()
    {
        return [
            'Content-Type: application/json',
            $this->authHeader . ': ' . $this->apiKey,
        ];
    }

    // --------------------------------------------------------------------

    /**
     * Execute a cURL POST request to the PipraPay API.
     *
     * @param string $endpointPath  e.g. '/checkout/redirect'
     * @param array  $payload       Associative array (JSON-encoded)
     * @param int    $timeout       Seconds (default 30)
     *
     * @return array ['http_code'=>int, 'body'=>array|null, 'curl_error'=>string]
     */
    protected function _post($endpointPath, array $payload, $timeout = 30)
    {
        $url  = $this->baseUrl . $endpointPath;
        $json = json_encode($payload);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => TRUE,
            CURLOPT_POSTFIELDS     => $json,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER     => $this->_headers(),
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $raw   = curl_exec($ch);
        $code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        log_message('info', sprintf(
            'PipraPay %s – HTTP %d – payload: %s – response: %s – error: %s',
            $endpointPath, $code, $json, $raw ?: 'empty', $error ?: 'none'
        ));

        return [
            'http_code'  => $code,
            'body'       => $raw ? json_decode($raw, TRUE) : NULL,
            'curl_error' => $error,
        ];
    }

    // --------------------------------------------------------------------

    /**
     * 1. Create a checkout session (redirect the user to PipraPay).
     *
     * @param float  $amount      Invoice total (decimal)
     * @param string $currency    Currency code (e.g. 'BDT')
     * @param string $returnUrl   Full URL where user is sent back after payment
     * @param string $cancelUrl   Full URL where user is sent if they cancel
     * @param array  $metaData    Contextual data (invoice_id, customer_id, etc.)
     *
     * @return array ['pp_id'=>string, 'pp_url'=>string]
     *
     * @throws RuntimeException on HTTP errors or malformed response
     */
    public function create_checkout_session(
        $amount,
        $currency,
        $returnUrl,
        $cancelUrl,
        array $metaData = []
    ) {
        $payload = [
            'amount'      => number_format((float) $amount, 2, '.', ''),
            'currency'    => strtoupper($currency) ?: 'BDT',
            'description' => $metaData['description'] ?? 'Payment via PipraPay',
            'customer'    => [
                'name'  => $metaData['customer_name']  ?? '',
                'email' => $metaData['customer_email'] ?? '',
                'phone' => $metaData['customer_phone'] ?? '',
            ],
            'return_url'  => $returnUrl,
            'cancel_url'  => $cancelUrl,
            'metadata'    => json_encode($metaData),
            'test'        => $this->testMode,
        ];

        $res = $this->_post('/checkout/redirect', $payload);

        if ($res['http_code'] !== 200 || empty($res['body'])) {
            throw new RuntimeException(
                'PipraPay checkout failed: HTTP ' . $res['http_code']
                . ' – ' . ($res['body']['error']['message'] ?? $res['curl_error'])
            );
        }

        $body  = $res['body'];
        $ppId  = $body['pp_id']  ?? NULL;
        $ppUrl = $body['pp_url'] ?? $body['redirect_url'] ?? $body['url'] ?? NULL;

        if (empty($ppId) || empty($ppUrl)) {
            throw new RuntimeException(
                'PipraPay checkout response missing pp_id or redirect URL'
            );
        }

        return ['pp_id' => $ppId, 'pp_url' => $ppUrl];
    }

    // --------------------------------------------------------------------

    /**
     * 2. Verify a completed payment.
     *
     * @param string $ppId  The PipraPay transaction ID from checkout.
     *
     * @return array  The full verification response from the API.
     *
     * @throws RuntimeException on HTTP errors
     */
    public function verify_payment($ppId)
    {
        $payload = ['pp_id' => (string) $ppId];
        $res     = $this->_post('/verify-payment', $payload);

        if ($res['http_code'] !== 200 || empty($res['body'])) {
            throw new RuntimeException(
                'PipraPay verification failed: HTTP ' . $res['http_code']
                . ' – ' . ($res['body']['error']['message'] ?? $res['curl_error'])
            );
        }

        return $res['body'];
    }

    // --------------------------------------------------------------------

    /**
     * 3. Refund a payment.
     *
     * @param string $ppId    The PipraPay transaction ID.
     * @param float  $amount  Amount to refund.
     * @param string $reason  Optional reason string.
     *
     * @return array  The refund response from the API.
     *
     * @throws RuntimeException on HTTP errors
     */
    public function refund_payment($ppId, $amount, $reason = '')
    {
        $payload = [
            'pp_id'  => (string) $ppId,
            'amount' => number_format((float) $amount, 2, '.', ''),
            'reason' => (string) $reason,
        ];
        $res = $this->_post('/refund-payment', $payload);

        if ($res['http_code'] !== 200 || empty($res['body'])) {
            throw new RuntimeException(
                'PipraPay refund failed: HTTP ' . $res['http_code']
                . ' – ' . ($res['body']['error']['message'] ?? $res['curl_error'])
            );
        }

        return $res['body'];
    }

    // --------------------------------------------------------------------

    /**
     * Determine whether the API response indicates a successful payment.
     *
     * @param array $response  The body returned by verify_payment().
     *
     * @return bool
     */
    public function is_successful(array $response)
    {
        return (
            !empty($response['status']) && $response['status'] === 'success'
        ) || (
            !empty($response['payment_status']) && $response['payment_status'] === 'completed'
        ) || (
            !empty($response['statusCode']) && $response['statusCode'] === '0000'
        );
    }
}
