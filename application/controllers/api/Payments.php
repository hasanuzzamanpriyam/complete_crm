<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'core/PH_Api_Controller.php';

/**
 * Payment Hub REST API — v1
 * 
 * @property Payment_hub_auth $payment_hub_auth
 * @property Payment_service $payment_service
 * @property Api_clients_model $api_clients_model
 * @property Payment_gateways_model $payment_gateways_model
 * @property Gateway_factory $gateway_factory
 * @property Payments_model $payments_model
 */
class Payments extends PH_Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library(['payment_hub_auth', 'payment_service']);
        $this->load->model('api_clients_model');
    }

    /**
     * POST api/v1/payments/initiate
     */
    public function initiate()
    {
        try {
            // 1. Authenticate request (Bearer + HMAC)
            $client = $this->payment_hub_auth->authenticate();

            // 2. Extract and validate basic data
            $data = $this->input->post(NULL, TRUE);
            if (empty($data['external_reference']) || empty($data['amount'])) {
                return $this->_send_error("Missing required fields: external_reference, amount", 400);
            }

            // 3. Delegate to Service Layer
            $result = $this->payment_service->initiate_payment($client, $data);

            return $this->_send_success($result, "Payment initiated successfully", 201);

        } catch (Exception $e) {
            return $this->_handle_exception($e);
        }
    }

    /**
     * GET/POST api/v1/payments/callback/{gateway_slug}
     * Handle incoming gateway callbacks/webhooks.
     */
    public function callback($gateway_slug)
    {
        try {
            // 1. Get Driver
            $this->load->model('payment_gateways_model');
            $this->load->library('gateway_factory');
            
            $gateway_row = $this->payment_gateways_model->get_by_slug($gateway_slug);
            if (!$gateway_row) throw new Exception("Invalid gateway.");

            $driver = $this->gateway_factory->get_driver($gateway_row);
            
            // 2. Process callback via driver
            $raw_data = $this->input->get_post(NULL, TRUE);
            $result = $driver->handle_callback($raw_data);

            if (!$result['success'] || empty($result['transaction_id'])) {
                return $this->_send_error("Invalid callback data", 400);
            }

            // 3. Update status & Notify Client
            $success = $this->payment_service->update_payment_status(
                $gateway_slug,
                $result['transaction_id'],
                $result['status'],
                $result['raw']
            );

            if (!$success) {
                return $this->_send_error("Callback processing failed", 500);
            }

            return $this->_send_success([], "Callback processed successfully");

        } catch (Exception $e) {
            return $this->_handle_exception($e);
        }
    }

    /**
     * GET api/v1/payments/status/{hub_payment_id}
     */
    public function status($hub_payment_id)
    {
        try {
            // 1. Authenticate request (Bearer + HMAC)
            $client = $this->payment_hub_auth->authenticate();

            // 2. Fetch Payment
            $this->load->model('payments_model');
            $payment = $this->payments_model->get($hub_payment_id);

            if (!$payment || $payment->client_id != $client->id) {
                return $this->_send_error("Payment not found", 404);
            }

            return $this->_send_success([
                'id' => $payment->id,
                'external_reference' => $payment->external_reference,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'created_at' => $payment->created_at
            ]);

        } catch (Exception $e) {
            return $this->_handle_exception($e);
        }
    }

    /**
     * GET api/v1/payments/gateways
     */
    public function gateways()
    {
        try {
            $this->load->model('payment_gateways_model');
            $gateways = $this->payment_gateways_model->get_active();

            return $this->_send_success($gateways, "Gateways retrieved successfully");

        } catch (Exception $e) {
            return $this->_handle_exception($e);
        }
    }
}
