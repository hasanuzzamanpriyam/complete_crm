<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Payment_service
 * Orchestrates the payment process, combining models, gateways, and logs.
 *
 * @property Payments_model $payments_model
 * @property Payment_gateways_model $payment_gateways_model
 * @property Payment_transactions_model $payment_transactions_model
 * @property Payment_logs_model $payment_logs_model
 * @property Webhook_logs_model $webhook_logs_model
 * @property Api_clients_model $api_clients_model
 * @property Gateway_factory $gateway_factory
 */
class Payment_service
{
    /** @var CI_Controller */
    protected $ci;
    /** @var Payments_model */
    protected $payments_model;
    /** @var Payment_gateways_model */
    protected $payment_gateways_model;
    /** @var Payment_transactions_model */
    protected $payment_transactions_model;
    /** @var Payment_logs_model */
    protected $payment_logs_model;
    /** @var Webhook_logs_model */
    protected $webhook_logs_model;
    /** @var Refunds_model */
    protected $refunds_model;
    /** @var Api_clients_model */
    protected $api_clients_model;
    /** @var Gateway_factory */
    protected $gateway_factory;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->model([
            'api_clients_model',
            'payments_model',
            'payment_gateways_model',
            'payment_transactions_model',
            'payment_logs_model',
            'webhook_logs_model',
            'refunds_model'
        ]);
        $this->ci->load->library('gateway_factory');
        $this->ci->load->helper('payment_hub');

        // Assign to local properties to satisfy IDE and clean up access
        $this->api_clients_model          = $this->ci->api_clients_model;
        $this->payments_model             = $this->ci->payments_model;
        $this->payment_gateways_model     = $this->ci->payment_gateways_model;
        $this->payment_transactions_model = $this->ci->payment_transactions_model;
        $this->payment_logs_model         = $this->ci->payment_logs_model;
        $this->webhook_logs_model         = $this->ci->webhook_logs_model;
        $this->refunds_model             = $this->ci->refunds_model;
        $this->gateway_factory           = $this->ci->gateway_factory;
    }

    /**
     * Initiate a new hub payment.
     */
    public function initiate_payment($client, array $data)
    {
        // 1. Create Payment Record
        $payment_id = $this->ci->payments_model->create_payment([
            'client_id'          => $client->id,
            'external_reference' => $data['external_reference'],
            'amount'             => $data['amount'],
            'currency'           => $data['currency'] ?? 'BDT',
            'customer_name'      => $data['customer_name'] ?? null,
            'customer_email'     => $data['customer_email'] ?? null,
        ]);

        $this->payment_logs_model->log($payment_id, "Payment initiated by client: {$client->project_name}");

        // 2. Resolve Gateway
        $gateway_row = null;
        if (!empty($data['gateway_slug'])) {
            $gateway_row = $this->payment_gateways_model->get_by_slug($data['gateway_slug']);
        }
        if (!$gateway_row) {
            $gateway_row = $this->payment_gateways_model->get_default();
        }

        if (!$gateway_row) {
            $this->payments_model->save(['status' => 'failed'], $payment_id);
            throw new Exception("No active payment gateway available.");
        }

        // 3. Get Gateway Driver
        $driver = $this->gateway_factory->get_driver($gateway_row);
        if (!$driver) {
            $this->payments_model->save(['status' => 'failed'], $payment_id);
            throw new Exception("Gateway driver not implemented: {$gateway_row->gateway_slug}");
        }

        // 4. Initiate with Gateway
        $res = $driver->initiate($data);

        // 5. Log Gateway Transaction
        $this->payment_transactions_model->log_attempt(
            $payment_id,
            $gateway_row->id,
            $res['success'] ? 'initiated' : 'failed',
            $res['transaction_id'],
            $res['raw']
        );

        if (!$res['success']) {
            $this->payments_model->save(['status' => 'failed'], $payment_id);
            throw new Exception($res['message'] ?? "Gateway initiation failed.");
        }

        return [
            'hub_payment_id' => $payment_id,
            'payment_url'    => $res['payment_url'],
            'gateway_txn_id' => $res['transaction_id']
        ];
    }

    /**
     * Central callback/webhook handler to update payment status and notify client.
     */
    public function update_payment_status($gateway_slug, $gateway_txn_id, $status, $raw_response = null)
    {
        // 1. Find the transaction
        $txn = $this->payment_transactions_model->get_by([
            'gateway_txn_id' => $gateway_txn_id
        ], TRUE);

        if (!$txn) {
            log_message('error', "Payment Hub Callback: Transaction not found for ID $gateway_txn_id");
            return false;
        }

        $payment_id = $txn->payment_id;
        $payment = $this->payments_model->get($payment_id);

        if (!$payment) return false;

        // 2. Update Transaction Status
        $this->ci->payment_transactions_model->save([
            'status' => $status,
            'raw_response' => is_array($raw_response) ? json_encode($raw_response) : $raw_response
        ], $txn->id);

        // 3. Update Payment Status (mapped)
        $mapped_status = in_array($status, ['success', 'completed', 'paid']) ? 'success' : 'failed';
        $this->payments_model->save([
            'status' => $mapped_status,
            'updated_at' => date('Y-m-d H:i:s')
        ], $payment_id);

        $this->payment_logs_model->log($payment_id, "Payment status updated to $mapped_status via $gateway_slug callback");

        // 4. Notify External Project
        $this->notify_client($payment_id);

        return true;
    }

    /**
     * Send webhook notification to the originating project.
     */
    public function notify_client($payment_id)
    {
        $payment = $this->payments_model->get_detailed($payment_id);
        if (empty($payment) || empty($payment->webhook_url)) return;

        $payload = [
            'event'              => 'payment.' . $payment->status,
            'external_reference' => $payment->external_reference,
            'hub_payment_id'     => $payment->id,
            'amount'             => $payment->amount,
            'currency'           => $payment->currency,
            'status'             => $payment->status,
            'timestamp'          => time(),
        ];

        // Sign payload with client secret (if available)
        $client = $this->api_clients_model->get($payment->client_id);
        $headers = ['Content-Type: application/json'];
        
        if (!empty($client->client_secret)) {
            $sig = hash_hmac('sha256', $payload['timestamp'] . '.' . json_encode($payload), $client->client_secret);
            $headers[] = 'X-Hub-Signature: ' . $sig;
            $headers[] = 'X-Hub-Timestamp: ' . $payload['timestamp'];
        }

        // Log the outgoing webhook attempt
        $log_id = $this->webhook_logs_model->log('outgoing', $payment_id, $payment->webhook_url, $payload);

        // Execute 
        $ch = curl_init($payment->webhook_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Update webhook log
        $status = ($http_code >= 200 && $http_code < 300) ? 'success' : 'failed';
        $update_data = [
            'response_code' => $http_code,
            'response_body' => $response,
            'status'        => $status
        ];

        if ($status === 'failed') {
            // Schedule first retry in 5 minutes
            $update_data['next_retry_at'] = date('Y-m-d H:i:s', strtotime('+5 minutes'));
            $update_data['last_error'] = $response;
        }

        $this->webhook_logs_model->save($update_data, $log_id);
    }

    /**
     * Process a refund for a successful payment.
     */
    public function refund_transaction($payment_id, $amount = null, $reason = '')
    {
        $payment = $this->payments_model->get($payment_id);
        if (!$payment || $payment->status !== 'success') {
            throw new Exception("Only successful payments can be refunded.");
        }

        $gateway_row = $this->payment_gateways_model->get($payment->gateway_id);
        $gateway = $this->gateway_factory->get_driver($gateway_row);
        
        $this->payment_logs_model->log($payment_id, "Attempting refund for amount: " . ($amount ?: $payment->amount));

        try {
            $result = $gateway->refund($payment->gateway_transaction_id, $amount);

            if ($result['success']) {
                // Update refund table
                $this->refunds_model->save([
                    'payment_id' => $payment_id,
                    'amount'     => $amount ?: $payment->amount,
                    'reason'     => $reason,
                    'gateway_refund_id' => $result['refund_id'],
                    'status'     => 'completed',
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                // Update payment status if full refund
                if (empty($amount) || $amount >= $payment->amount) {
                    $this->update_payment_status($payment->gateway_slug, $payment->gateway_transaction_id, 'refunded', ['refund_id' => $result['refund_id']]);
                } else {
                    $this->payment_logs_model->log($payment_id, "Partial refund processed: " . $amount);
                }

                return true;
            } else {
                $this->payment_logs_model->log($payment_id, "Refund failed at gateway: " . json_encode($result['raw']));
                return false;
            }
        } catch (Exception $e) {
            $this->payment_logs_model->log($payment_id, "Refund exception: " . $e->getMessage());
            throw $e;
        }
    }
}
