<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Refunds_model
 * Tracks refund requests and their status.
 */
class Refunds_model extends MY_Model
{
    public $_table_name  = 'tbl_refunds';
    public $_primary_key = 'id';
    public $_order_by    = 'id DESC';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create a refund record.
     */
    public function create_refund($payment_id, $amount, $reason = null, $status = 'pending')
    {
        return $this->save([
            'payment_id' => $payment_id,
            'amount' => $amount,
            'reason' => $reason,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
