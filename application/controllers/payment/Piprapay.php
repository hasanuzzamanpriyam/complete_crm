<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Piprapay extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('invoice_model');
        $this->load->library('gateways/Piprapay_gateway');
        $this->load->helper(['url', 'form']);
    }

    public function pay($invoice_id) {
        $invoice = $this->invoice_model->check_by(['invoices_id' => $invoice_id], 'tbl_invoices');
        if (!$invoice) show_404();
        $total = $this->invoice_model->calculate_to('total', $invoice_id);
        $gateways = $this->piprapay_gateway->fetch_gateways();
        $data = [
            'invoice'   => $invoice,
            'total'     => $total,
            'gateways'  => $gateways,
            'actionUrl' => site_url('payment/piprapay/purchase'),
        ];
        $this->load->view('payment/piprapay', $data);
    }

    public function purchase() {
        $post = $this->input->post();
        $invoice_id = $post['invoice_id'] ?? null;
        $gateway_id = $post['gateway_id'] ?? null;
        if (!$invoice_id || !$gateway_id) {
            set_message('error', 'Missing data');
            redirect($_SERVER['HTTP_REFERER']);
        }
        $invoice = $this->invoice_model->check_by(['invoices_id' => $invoice_id], 'tbl_invoices');
        if (!$invoice) show_404();
        $total = $this->invoice_model->calculate_to('total', $invoice_id);
        $client = $this->invoice_model->check_by(['client_id' => $invoice->client_id], 'tbl_client');
        $payload = [
            'gateway_id' => $gateway_id,
            'amount'     => $total,
            'currency'   => $invoice->currency ?? 'BDT',
            'customer'   => [
                'name'  => $client->name ?? '',
                'email' => $client->email ?? '',
                'phone' => $client->phone ?? '',
            ],
            'return_url' => site_url('payment/piprapay/callback'),
            'webhook_url'=> site_url('payment/piprapay/webhook'),
            'description'=> "Invoice #{$invoice->reference_no}",
        ];
        $resp = $this->piprapay_gateway->create_checkout($payload);
        if (empty($resp['pp_url'])) {
            set_message('error', 'Unable to start payment');
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->db->where('invoices_id', $invoice_id)->update('tbl_invoices', ['pp_id' => $resp['pp_id']]);
        redirect($resp['pp_url']);
    }

    public function callback() {
        $pp_id = $this->input->get('pp_id');
        if (!$pp_id) {
            set_message('error', 'Missing payment reference');
            redirect(site_url('invoices'));
        }
        try {
            $verification = $this->piprapay_gateway->verify_payment($pp_id);
        } catch (Exception $e) {
            log_message('error', 'Piprapay callback verify error: ' . $e->getMessage());
            set_message('error', 'Payment verification failed');
            redirect(site_url('invoices'));
        }
        $invoice = $this->invoice_model->check_by(['pp_id' => $pp_id], 'tbl_invoices');
        if ($invoice && !empty($verification['status']) && $verification['status'] === 'success') {
            $total = $this->invoice_model->calculate_to('total', $invoice->invoices_id);
            $this->db->insert('tbl_payments', [
                'invoices_id'    => $invoice->invoices_id,
                'paid_by'        => $invoice->client_id,
                'payment_method' => 'PipraPay',
                'currency'       => $invoice->currency ?? 'BDT',
                'amount'         => $total,
                'payment_date'   => date('Y-m-d'),
                'trans_id'       => $pp_id,
                'notes'          => 'PipraPay (PayTic) – ' . ($verification['status'] ?? 'completed'),
                'month_paid'     => date('m'),
                'year_paid'      => date('Y'),
            ]);
            $this->db->where('invoices_id', $invoice->invoices_id)->update('tbl_invoices', [
                'status'         => 'Paid',
                'paid_date'      => date('Y-m-d'),
                'payment_method' => 'PipraPay',
            ]);
        }
        set_message('success', 'Payment successful');
        redirect(site_url('payment/piprapay/success'));
    }

    public function success() {
        $this->load->view('payment/piprapay_success');
    }

    public function webhook() {
        $payload = json_decode(file_get_contents('php://input'), true);
        if (empty($payload['pp_id'])) {
            http_response_code(400);
            exit;
        }
        try {
            $verification = $this->piprapay_gateway->verify_payment($payload['pp_id']);
        } catch (Exception $e) {
            log_message('error', 'Piprapay webhook verify error: ' . $e->getMessage());
            http_response_code(500);
            exit;
        }
        $invoice = $this->invoice_model->check_by(['pp_id' => $payload['pp_id']], 'tbl_invoices');
        if ($invoice && !empty($verification['status']) && $verification['status'] === 'success') {
            $total = $this->invoice_model->calculate_to('total', $invoice->invoices_id);
            $this->db->insert('tbl_payments', [
                'invoices_id'    => $invoice->invoices_id,
                'paid_by'        => $invoice->client_id,
                'payment_method' => 'PipraPay',
                'currency'       => $invoice->currency ?? 'BDT',
                'amount'         => $total,
                'payment_date'   => date('Y-m-d'),
                'trans_id'       => $payload['pp_id'],
                'month_paid'     => date('m'),
                'year_paid'      => date('Y'),
            ]);
            $this->db->where('invoices_id', $invoice->invoices_id)->update('tbl_invoices', [
                'status'         => 'Paid',
                'paid_date'      => date('Y-m-d'),
                'payment_method' => 'PipraPay',
            ]);
        }
        http_response_code(200);
    }

    public function refund($pp_id) {
        $amount = $this->input->post('amount');
        $res = $this->piprapay_gateway->refund_payment($pp_id, (float)$amount);
        echo json_encode($res);
    }
}
?>