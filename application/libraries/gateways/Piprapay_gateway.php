<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Piprapay_gateway extends App_gateway
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('piprapay');
        $this->setName('PipraPay');
    }

    public function invoice_payment($data)
    {
        $this->ci->load->library('piprapay_core');
        
        $invoice_info = get_row('tbl_invoices', array('invoices_id' => $data['invoice_id']));
        $client_info = get_row('tbl_client', array('client_id' => $invoice_info->client_id));
        
        $invoice_due = $this->ci->invoice_model->calculate_to('invoice_due', $data['invoice_id']);
        
        if ($invoice_due <= 0) {
            $invoice_due = 0.00;
        }

        $payment_data = array(
            'amount' => isset($data['amount']) ? $data['amount'] : $invoice_due,
            'currency' => $invoice_info->currency,
            'invoice_id' => $invoice_info->invoices_id,
            'customer_name' => $client_info->name,
            'customer_email' => $client_info->email,
            'customer_phone' => $client_info->phone,
            'gateway' => isset($data['gateway']) ? $data['gateway'] : 'bkash',
            'callback_url' => base_url('payment/piprapay/callback'),
            'success_url' => base_url('payment/piprapay/success'),
            'cancel_url' => $data['cancel_url'] ?? $_SERVER['HTTP_REFERER'],
            'description' => 'Payment for Invoice ' . $invoice_info->reference_no,
            'metadata' => array(
                'client_id' => $invoice_info->client_id,
                'invoice_reference' => $invoice_info->reference_no
            )
        );

        $response = $this->ci->piprapay_core->initiatePayment($payment_data);

        if ($response['success']) {
            $payment_data = $response['data'];
            
            if (isset($payment_data['payment_url']) && !empty($payment_data['payment_url'])) {
                $this->ci->session->set_userdata(array(
                    'piprapay_transaction_id' => $payment_data['transaction_id'],
                    'piprapay_invoice_id' => $data['invoice_id'],
                    'piprapay_amount' => $payment_data['amount']
                ));
                
                redirect($payment_data['payment_url']);
            } else {
                set_message('error', 'Payment URL not received from PipraPay');
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            set_message('error', $response['message']);
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function verifyPayment($transaction_id)
    {
        $this->ci->load->library('piprapay_core');
        
        $response = $this->ci->piprapay_core->verifyPayment($transaction_id);

        if ($response['success']) {
            $transaction_data = $response['data'];
            
            if (isset($transaction_data['status']) && $transaction_data['status'] === 'success') {
                return array(
                    'success' => true,
                    'transaction_id' => $transaction_data['transaction_id'],
                    'amount' => $transaction_data['amount'],
                    'currency' => $transaction_data['currency'],
                    'gateway' => $transaction_data['gateway'],
                    'invoice_id' => $transaction_data['invoice_id']
                );
            }
        }

        return array(
            'success' => false,
            'message' => isset($response['message']) ? $response['message'] : 'Payment verification failed'
        );
    }

    public function processCallback($callback_data)
    {
        $transaction_id = isset($callback_data['transaction_id']) ? $callback_data['transaction_id'] : '';
        $invoice_id = isset($callback_data['invoice_id']) ? $callback_data['invoice_id'] : '';
        
        if (empty($transaction_id)) {
            return array(
                'success' => false,
                'message' => 'Invalid transaction ID'
            );
        }

        $verification_result = $this->verifyPayment($transaction_id);

        if ($verification_result['success']) {
            $result = $this->addPayment(
                $verification_result['invoice_id'],
                $verification_result['amount'],
                $verification_result['transaction_id'],
                'PipraPay-' . $verification_result['gateway']
            );

            if ($result['type'] === 'success') {
                return array(
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'invoice_id' => $verification_result['invoice_id']
                );
            } else {
                return array(
                    'success' => false,
                    'message' => $result['message']
                );
            }
        }

        return $verification_result;
    }

    public function getSupportedGateways()
    {
        $this->ci->load->library('piprapay_core');

        $response = $this->ci->piprapay_core->getGateways();

        if ($response['success'] && isset($response['gateways'])) {
            $collection = $response['gateways'];

            if ($collection instanceof \PipraPay\GatewayCollection) {
                return $collection->toArray();
            }

            return $response['data'] ?? [];
        }

        return [];
    }

    public function getGatewayOptions(string $currency = null): array
    {
        $this->ci->load->library('piprapay_core');

        if ($currency) {
            $response = $this->ci->piprapay_core->getGatewaysForCurrency($currency);
        } else {
            $response = $this->ci->piprapay_core->getGateways();
        }

        if ($response['success'] && isset($response['gateways'])) {
            $collection = $response['gateways'];

            if ($collection instanceof \PipraPay\GatewayCollection) {
                return $collection->toArray();
            }

            return $response['data'] ?? [];
        }

        return [];
    }

    public function refundTransaction($transaction_id, $amount = null)
    {
        $this->ci->load->library('piprapay_core');
        
        $response = $this->ci->piprapay_core->refundPayment($transaction_id, $amount);

        return $response;
    }
}
