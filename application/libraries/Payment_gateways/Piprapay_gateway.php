<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'libraries/Payment_gateways/Base_gateway.php';

/**
 * Piprapay_gateway
 * Concrete implementation of Piprapay as a Hub gateway driver.
 */
class Piprapay_gateway extends Base_gateway
{
    /** @var Piprapay_core */
    protected $piprapay_core;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->ci->load->library('piprapay_core');
        $this->piprapay_core = $this->ci->piprapay_core;
    }

    public function initiate(array $data)
    {
        $response = $this->piprapay_core->initiatePayment($data);
        
        return [
            'success'        => !empty($response['success']),
            'transaction_id' => $response['data']['transaction_id'] ?? null,
            'payment_url'    => $response['data']['payment_url']    ?? null,
            'message'        => $response['message'] ?? null,
            'raw'            => $response
        ];
    }

    public function verify($txn_id)
    {
        $response = $this->piprapay_core->verifyPayment($txn_id);
        $data = $response['data'] ?? [];
        
        return [
            'success' => !empty($response['success']),
            'status'  => strtolower($data['status'] ?? 'failed'),
            'raw'     => $response
        ];
    }

    public function refund($txn_id, $amount = null)
    {
        $response = $this->piprapay_core->refundPayment($txn_id, $amount);
        
        return [
            'success'   => !empty($response['success']),
            'refund_id' => $response['data']['refund_id'] ?? null,
            'raw'       => $response
        ];
    }

    public function handle_callback(array $raw_data)
    {
        return [
            'success'        => !empty($raw_data['transaction_id']),
            'transaction_id' => $raw_data['transaction_id'] ?? null,
            'status'         => strtolower($raw_data['status'] ?? 'pending'),
            'raw'            => $raw_data
        ];
    }
}
