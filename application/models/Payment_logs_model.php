<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Payment_logs_model
 * General audit trail for internal payment processing events.
 */
class Payment_logs_model extends MY_Model
{
    public $_table_name  = 'tbl_payment_logs';
    public $_primary_key = 'id';
    public $_order_by    = 'id DESC';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Log an event.
     */
    public function log($payment_id, $message, $log_level = 'info', $context = null)
    {
        $this->load->helper('payment_hub');
        $masked_context = mask_sensitive_data($context);

        $data = [
            'payment_id' => $payment_id,
            'log_level' => $log_level,
            'message' => $message,
            'context' => is_array($masked_context) ? json_encode($masked_context) : $masked_context,
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Capture IP and UA if available (ci instance check for CLI safety)
        if (isset($this->input)) {
            $data['ip_address'] = $this->input->ip_address();
            $data['user_agent'] = $this->input->user_agent();
        }

        return $this->save($data);
    }
}
