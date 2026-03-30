<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Payment_webhooks
 * CLI Controller for handling payment hub webhooks (retries, etc.)
 * 
 * Usage: php index.php payment_webhooks retry
 * 
 * @property Webhook_logs_model $webhook_logs_model
 * @property Api_clients_model $api_clients_model
 * @property Payments_model $payments_model
 * @property CI_Input $input
 */
class Payment_webhooks extends MY_Controller
{
    protected $webhook_logs_model;
    protected $api_clients_model;
    protected $payments_model;

    public function __construct()
    {
        parent::__construct();
        // Only allow CLI access
        if (!$this->input->is_cli_request()) {
            show_error('Direct access is not allowed');
        }
        $this->load->model(['webhook_logs_model', 'api_clients_model', 'payments_model']);
        $this->load->helper('payment_hub');
    }

    /**
     * Retry failed outgoing webhooks.
     */
    public function retry()
    {
        $due = $this->webhook_logs_model->get_due_retries(50);
        
        if (empty($due)) {
            echo "No webhooks due for retry.\n";
            return;
        }

        echo "Found " . count($due) . " webhooks due for retry.\n";

        foreach ($due as $log) {
            $this->_process_retry($log);
        }
    }

    /**
     * Process a single retry.
     */
    private function _process_retry($log)
    {
        echo "Retrying ID {$log->id} for Payment ID {$log->payment_id}... ";

        $payment = $this->payments_model->get($log->payment_id);
        if (!$payment) {
            echo "Payment not found. Skipping.\n";
            return;
        }

        $client = $this->api_clients_model->get($payment->client_id);
        if (!$client) {
            echo "Client not found. Skipping.\n";
            return;
        }

        $payload = json_decode($log->payload, true);
        $payload['retry_count'] = $log->retry_count + 1;
        $payload['timestamp'] = time();

        $headers = ['Content-Type: application/json'];
        if (!empty($client->client_secret)) {
            $sig = hash_hmac('sha256', $payload['timestamp'] . '.' . json_encode($payload), $client->client_secret);
            $headers[] = 'X-Hub-Signature: ' . $sig;
            $headers[] = 'X-Hub-Timestamp: ' . $payload['timestamp'];
        }

        $ch = curl_init($log->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        $new_retry_count = $log->retry_count + 1;
        $status = ($http_code >= 200 && $http_code < 300) ? 'success' : 'failed';

        $update_data = [
            'retry_count'   => $new_retry_count,
            'response_code' => $http_code,
            'response_body' => $response ?: $curl_error,
            'status'        => $status
        ];

        if ($status === 'failed' && $new_retry_count < 5) {
            // Exponential backoff: 5m, 15m, 1h, 4h, 24h
            $backoff = [5, 15, 60, 240, 1440]; 
            $minutes = $backoff[$new_retry_count - 1] ?? 1440;
            $update_data['next_retry_at'] = date('Y-m-d H:i:s', strtotime("+$minutes minutes"));
            $update_data['last_error'] = $response ?: $curl_error;
            echo "Failed again. Next retry at {$update_data['next_retry_at']}\n";
        } else if ($status === 'success') {
            $update_data['next_retry_at'] = null;
            echo "Success!\n";
        } else {
            echo "Failed. Max retries reached.\n";
        }

        $this->webhook_logs_model->save($update_data, $log->id);
    }
}
