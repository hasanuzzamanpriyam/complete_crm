<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Webhook_logs_model
 * Tracks incoming and outgoing webhooks.
 */
class Webhook_logs_model extends MY_Model
{
    public $_table_name  = 'tbl_webhook_logs';
    public $_primary_key = 'id';
    public $_order_by    = 'id DESC';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Log a webhook event.
     */
    public function log($direction, $payment_id, $url, $payload, $response_code = null, $response_body = null, $status = 'pending')
    {
        $this->load->helper('payment_hub');
        
        $masked_payload = mask_sensitive_data($payload);
        $masked_response = mask_sensitive_data($response_body);

        return $this->save([
            'payment_id' => $payment_id,
            'direction' => $direction,
            'url' => $url,
            'payload' => is_array($masked_payload) ? json_encode($masked_payload) : $masked_payload,
            'response_code' => $response_code,
            'response_body' => is_array($masked_response) ? json_encode($masked_response) : $masked_response,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get webhooks due for retry.
     */
    public function get_due_retries($limit = 50)
    {
        $this->db->where('direction', 'outgoing');
        $this->db->where('status', 'failed');
        $this->db->where('retry_count <', 5);
        $this->db->group_start();
        $this->db->where('next_retry_at <=', date('Y-m-d H:i:s'));
        $this->db->or_where('next_retry_at', null);
        $this->db->group_end();
        $this->db->limit($limit);
        
        return $this->get();
    }
}
