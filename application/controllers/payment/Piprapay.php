<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Piprapay payment controller – new PayTic integration.
 * Provides checkout, callback, webhook, success and refund handling.
 */
class Piprapay extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('gateways/Piprapay_gateway');
        $this->load->model('Invoice_model'); // existing model
        $this->load->helper(['url', 'form']);
    }

    /** Show checkout page for an invoice */
    public function pay($invoice_id) {
        $invoice = $this->Invoice_model->get($invoice_id);
        if (!$invoice) show_error('Invoice not found', 404);
        $gateways = $this->piprapay_gateway->fetch_gateways();
        $data = [
            'invoice'   => $invoice,
            'gateways'  => $gateways,
            'actionUrl' => site_url('payment/piprapay/purchase')
        ];
        $this->load->view('payment/piprapay', $data);
    }

    /** Build checkout request and redirect to Piprapay */
    public function purchase() {
        $post = $this->input->post();
        $invoice_id = $post['invoice_id'] ?? null;
        $gateway_id = $post['gateway_id'] ?? null;
        if (!$invoice_id || !$gateway_id) {
            set_flash_error('Missing data');
            redirect($_SERVER['HTTP_REFERER']);
        }
        $invoice = $this->Invoice_model->get($invoice_id);
        $payload = [
            'gateway_id' => $gateway_id,
            'amount'     => $invoice->total,
            'currency'   => $invoice->currency ?? 'BDT',
            'customer'   => [
                'name'  => $invoice->client_name,
                'email' => $invoice->client_email,
                'phone' => $invoice->client_phone,
            ],
            'return_url' => site_url('payment/piprapay/callback'),
            'webhook_url'=> site_url('payment/piprapay/webhook'),
            'description'=> "Invoice #{$invoice->code}",
            'test'       => $this->config->item('piprapay')['test_mode'],
        ];
        $resp = $this->piprapay_gateway->create_checkout($payload);
        if (empty($resp['pp_url'])) {
            set_flash_error('Unable to start payment');
            redirect($_SERVER['HTTP_REFERER']);
        }
        // store pp_id for later verification
        $this->Invoice_model->update($invoice_id, ['pp_id' => $resp['pp_id']]);
        redirect($resp['pp_url']);
    }

    /** Return URL – called after user completes payment */
    public function callback() {
        $pp_id = $this->input->get('pp_id');
        if (!$pp_id) {
            set_flash_error('Missing payment reference');
            redirect(site_url('invoices'));
        }
        $verification = $this->piprapay_gateway->verify_payment($pp_id);
        $ok = (!empty($verification['status']) && $verification['status'] === 'success');
        $invoice = $this->Invoice_model->get_by_pp_id($pp_id);
        if ($invoice && $ok) {
            $this->Invoice_model->mark_paid($invoice->id, $verification);
        }
        set_flash_success('Payment successful');
        redirect(site_url('payment/piprapay/success'));
    }

    /** Simple success page */
    public function success() {
        $this->load->view('payment/piprapay_success');
    }

    /** Webhook endpoint – async notifications */
    public function webhook() {
        $payload = json_decode(file_get_contents('php://input'), true);
        if (empty($payload['pp_id'])) {
            http_response_code(400);
            exit;
        }
        $verification = $this->piprapay_gateway->verify_payment($payload['pp_id']);
        if (!empty($verification['status']) && $verification['status'] === 'success') {
            $invoice = $this->Invoice_model->get_by_pp_id($payload['pp_id']);
            if ($invoice) {
                $this->Invoice_model->mark_paid($invoice->id, $verification);
            }
        }
        http_response_code(200);
    }

    /** Refund a payment */
    public function refund($pp_id) {
        $amount = $this->input->post('amount');
        $res = $this->piprapay_gateway->refund_payment($pp_id, (float)$amount);
        echo json_encode($res);
    }
}
?>