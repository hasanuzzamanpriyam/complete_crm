<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Piprapay payment controller
 *
 * Handles the CRM-side invoice payment flow:
 *   pay()      — show payment form
 *   purchase() — submit/initiate payment
 *   callback() — Piprapay redirects back here
 *   success()  — verifies and records the payment
 *   cancel()   — user cancelled
 *   webhook()  — async server-to-server notification from Piprapay
 *   refund()   — admin-initiated refund
 */
class Piprapay extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('invoice_model');
        $this->load->library('piprapay_gateway');
        // piprapay_core is loaded once inside piprapay_gateway::__construct()
    }

    // -----------------------------------------------------------------------

    public function pay($invoice_id = null)
    {
        if (!$invoice_id) {
            set_message('error', 'Invalid invoice ID');
            redirect($_SERVER['HTTP_REFERER'] ?? 'client/dashboard');
        }

        $invoice_info = $this->invoice_model->check_by(['invoices_id' => $invoice_id], 'tbl_invoices');
        $invoice_due  = (float) $this->invoice_model->calculate_to('invoice_due', $invoice_id);
        $invoice_due  = max(0, $invoice_due);

        $currency         = $invoice_info->currency ?? 'BDT';
        $allowed_gateways = $this->piprapay_gateway->getGatewayOptions($currency);

        $data = [
            'title'            => lang('piprapay_payment'),
            'breadcrumbs'      => lang('piprapay'),
            'piprapay'         => true,
            'invoice_info'     => $invoice_info,
            'invoice_id'       => $invoice_id,
            'invoice_due'      => $invoice_due,
            'allowed_gateways' => $allowed_gateways,
        ];

        if ($this->input->post()) {
            $data['post']             = true;
            $data['amount']           = $this->input->post('amount', true);
            $data['selected_gateway'] = $this->input->post('gateway', true);

            $data['subview'] = $this->load->view('payment/piprapay', $data, true);
            $client_id = $this->session->userdata('client_id');
            $this->load->view($client_id ? 'client/_layout_main' : 'frontend/_layout_main', $data);
        } else {
            $data['subview'] = $this->load->view('payment/piprapay', $data, false);
            $this->load->view('client/_layout_modal', $data);
        }
    }

    // -----------------------------------------------------------------------

    /**
     * FIX (Bug #5): Amount is now cast to float and validated server-side.
     * Previously a raw POST string was compared to the invoice due.
     */
    public function purchase()
    {
        if (!$this->input->post()) {
            set_message('error', 'Invalid request');
            redirect($_SERVER['HTTP_REFERER'] ?? 'client/dashboard');
        }

        $invoice_id = (int) $this->input->post('invoice_id', true);
        $amount     = (float) $this->input->post('amount', true);
        $gateway    = $this->input->post('gateway', true);

        if ($invoice_id <= 0) {
            set_message('error', 'Invalid invoice');
            redirect($_SERVER['HTTP_REFERER'] ?? 'client/dashboard');
        }

        if (!is_numeric($this->input->post('amount', true)) || $amount <= 0) {
            set_message('error', 'Invalid amount');
            redirect($_SERVER['HTTP_REFERER'] ?? 'client/dashboard');
        }

        if (empty($gateway)) {
            set_message('error', 'Please select a payment gateway');
            redirect($_SERVER['HTTP_REFERER'] ?? 'client/dashboard');
        }

        $invoice_due = (float) $this->invoice_model->calculate_to('invoice_due', $invoice_id);

        // Precision-safe comparison
        if (round($amount, 2) > round($invoice_due, 2)) {
            set_message('error', 'Amount exceeds the invoice due amount');
            redirect($_SERVER['HTTP_REFERER'] ?? 'client/dashboard');
        }

        $payment_data = [
            'invoice_id' => $invoice_id,
            'amount'     => $amount,
            'gateway'    => $gateway,
            'cancel_url' => base_url('payment/piprapay/cancel'),
        ];

        $this->piprapay_gateway->invoice_payment($payment_data);
        // invoice_payment() redirects — execution stops here on success.
    }

    // -----------------------------------------------------------------------

    public function callback()
    {
        $transaction_id = $this->input->get('transaction_id', true);
        $invoice_id     = $this->input->get('invoice_id', true);
        $status         = $this->input->get('status', true);

        $callback_data = [
            'transaction_id' => $transaction_id,
            'invoice_id'     => $invoice_id,
            'status'         => $status,
        ];

        $result = $this->piprapay_gateway->processCallback($callback_data);

        if ($result['success']) {
            set_message('success', $result['message']);
            $redirect_url = 'client/invoice/manage_invoice/invoice_details/' . $result['invoice_id'];
        } else {
            set_message('error', $result['message']);
            $redirect_url = 'client/dashboard';
        }

        $client_id = $this->session->userdata('client_id');
        if (!empty($client_id)) {
            redirect($redirect_url);
        } else {
            redirect('frontend/view_invoice/' . url_encode($result['invoice_id'] ?? $invoice_id));
        }
    }

    // -----------------------------------------------------------------------

    public function success()
    {
        $transaction_id = $this->session->userdata('piprapay_transaction_id');
        $invoice_id     = $this->session->userdata('piprapay_invoice_id');

        if (!$transaction_id || !$invoice_id) {
            set_message('error', 'Invalid transaction session');
            redirect('client/dashboard');
        }

        $verification = $this->piprapay_gateway->verifyPayment($transaction_id);

        if ($verification['success']) {
            $result = $this->piprapay_gateway->addPayment(
                $verification['invoice_id'] ?? $invoice_id,
                $verification['amount'],
                $verification['transaction_id'],
                'PipraPay-' . $verification['gateway']
            );

            if (($result['type'] ?? '') === 'success') {
                set_message('success', $result['message']);
                $redirect_url = 'client/invoice/manage_invoice/invoice_details/' . ($verification['invoice_id'] ?? $invoice_id);
            } else {
                set_message('error', $result['message']);
                $redirect_url = 'client/dashboard';
            }
        } else {
            set_message('error', 'Payment verification failed: ' . ($verification['message'] ?? ''));
            $redirect_url = 'client/dashboard';
        }

        $this->session->unset_userdata(['piprapay_transaction_id', 'piprapay_invoice_id', 'piprapay_amount']);

        $client_id = $this->session->userdata('client_id');
        if (!empty($client_id)) {
            redirect($redirect_url);
        } else {
            redirect('frontend/view_invoice/' . url_encode($invoice_id));
        }
    }

    // -----------------------------------------------------------------------

    public function cancel()
    {
        set_message('warning', 'Payment was cancelled');
        $invoice_id = $this->session->userdata('piprapay_invoice_id');

        $client_id = $this->session->userdata('client_id');
        if (!empty($client_id)) {
            redirect($invoice_id ? 'client/invoice/manage_invoice/invoice_details/' . $invoice_id : 'client/dashboard');
        } else {
            redirect($invoice_id ? 'frontend/view_invoice/' . url_encode($invoice_id) : 'frontend');
        }
    }

    // -----------------------------------------------------------------------

    /**
     * FIX (Bug #1): Previously used `$this->ci->load->library(...)` which
     * caused a fatal error because MY_Controller has no $ci property.
     * Piprapay_core is now loaded via $this->load and accessed via $this->.
     */
    public function webhook()
    {
        $input     = file_get_contents('php://input');
        $signature = $this->input->server('HTTP_X_SIGNATURE');

        if (empty($input) || empty($signature)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing payload or signature']);
            return;
        }

        // piprapay_core was already loaded by the gateway in __construct()
        if (!$this->piprapay_core->verifyWebhook($input, $signature)) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid webhook signature']);
            return;
        }

        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON body']);
            return;
        }

        $callback_data = [
            'transaction_id' => $data['transaction_id'] ?? '',
            'invoice_id'     => $data['invoice_id']     ?? '',
            'status'         => $data['status']         ?? '',
        ];

        $result = $this->piprapay_gateway->processCallback($callback_data);

        http_response_code(200);
        echo json_encode(['success' => $result['success'], 'message' => $result['message'] ?? '']);
    }

    // -----------------------------------------------------------------------

    public function refund()
    {
        $transaction_id = $this->input->post('transaction_id', true);
        $amount         = $this->input->post('amount', true);
        $amount         = !empty($amount) ? (float) $amount : null;

        if (empty($transaction_id)) {
            set_message('error', 'Transaction ID is required');
            redirect($_SERVER['HTTP_REFERER'] ?? 'admin/transactions');
        }

        $result = $this->piprapay_gateway->refundTransaction($transaction_id, $amount);

        if (!empty($result['success'])) {
            set_message('success', 'Refund processed successfully');
        } else {
            set_message('error', 'Refund failed: ' . ($result['message'] ?? 'Unknown error'));
        }

        redirect($_SERVER['HTTP_REFERER'] ?? 'admin/transactions');
    }
}
