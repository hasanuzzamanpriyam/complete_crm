<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Payment_gateway_interface
 * Defines the contract for all payment gateway drivers.
 */
interface Payment_gateway_interface
{
    /**
     * @param array $data Payment details
     * @return array ['success' => bool, 'transaction_id' => string, 'payment_url' => string, 'raw' => mixed]
     */
    public function initiate(array $data);

    /**
     * @param string $txn_id
     * @return array ['success' => bool, 'status' => string, 'raw' => mixed]
     */
    public function verify($txn_id);

    /**
     * @param string $txn_id
     * @param float|null $amount
     * @return array ['success' => bool, 'refund_id' => string, 'raw' => mixed]
     */
    public function refund($txn_id, $amount = null);

    /**
     * @param array $raw_data
     * @return array ['success' => bool, 'transaction_id' => string, 'status' => string, 'raw' => mixed]
     */
    public function handle_callback(array $raw_data);
}

/**
 * Base_gateway
 * Common logic for all gateway drivers.
 */
abstract class Base_gateway implements Payment_gateway_interface
{
    protected $ci;
    protected $config;

    public function __construct(array $config = [])
    {
        $this->ci =& get_instance();
        $this->config = $config;
    }
}
