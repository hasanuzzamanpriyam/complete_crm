<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Payment_transactions_model
 * Tracks individual gateway attempts for a payment.
 */
class Payment_transactions_model extends MY_Model
{
    public $_table_name  = 'tbl_payment_transactions';
    public $_primary_key = 'id';
    public $_order_by    = 'id DESC';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Log a new transaction attempt.
     */
    public function log_attempt($payment_id, $gateway_id, $status, $gateway_txn_id = null, $raw_response = null)
    {
        $this->load->helper('payment_hub');
        $masked_response = mask_sensitive_data($raw_response);

        return $this->save([
            'payment_id' => $payment_id,
            'gateway_id' => $gateway_id,
            'status' => $status,
            'gateway_txn_id' => $gateway_txn_id,
            'raw_response' => is_array($masked_response) ? json_encode($masked_response) : $masked_response,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
