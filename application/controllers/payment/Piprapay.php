<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Piprapay extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('invoice_model');
        $this->load->library('piprapay_lib');
        $this->load->helper(['url', 'form']);
    }

    public function pay($invoice_id) {
        $invoice = $this->invoice_model->check_by(['invoices_id' => $invoice_id], 'tbl_invoices');
        if (!$invoice) show_404();

        $total  = $this->invoice_model->calculate_to('total', $invoice_id);
        $client = $this->invoice_model->check_by(['client_id' => $invoice->client_id], 'tbl_client');

        $customer_name  = '';
        $customer_email = '';
        $customer_phone = '';
        if ($client) {
            $customer_name  = $client->name ?? '';
            $customer_email = $client->email ?? '';
            $customer_phone = !empty($client->phone) ? $client->phone : (!empty($client->mobile) ? $client->mobile : '');
        }

        try {
            $resp = $this->piprapay_lib->create_checkout_session(
                $total,
                $invoice->currency ?? 'BDT',
                site_url('payment/piprapay/callback'),
                site_url('payment/piprapay/cancel'),
                [
                    'invoice_id'     => $invoice_id,
                    'reference_no'   => $invoice->reference_no,
                    'description'    => "Invoice #{$invoice->reference_no}",
                    'customer_name'  => $customer_name,
                    'customer_email' => $customer_email,
                    'customer_phone' => $customer_phone,
                ]
            );
        } catch (Throwable $e) {
            log_message('error', 'Piprapay checkout error: ' . $e->getMessage());
            set_message('error', 'Unable to start payment. Please try again. Error: ' . $e->getMessage());
            $referer = $_SERVER['HTTP_REFERER'] ?? site_url('frontend/view_invoice/' . url_encode($invoice_id));
            redirect($referer);
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
            $verification = $this->piprapay_lib->verify_payment($pp_id);
        } catch (Throwable $e) {
            log_message('error', 'Piprapay callback verify error: ' . $e->getMessage());
            set_message('error', 'Payment verification failed');
            redirect(site_url('invoices'));
        }
        $invoice = $this->invoice_model->check_by(['pp_id' => $pp_id], 'tbl_invoices');
        if ($invoice && $this->piprapay_lib->is_successful($verification)) {
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

    public function cancel() {
        set_message('error', 'Payment cancelled');
        redirect(site_url('invoices'));
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
            $verification = $this->piprapay_lib->verify_payment($payload['pp_id']);
        } catch (Throwable $e) {
            log_message('error', 'Piprapay webhook verify error: ' . $e->getMessage());
            http_response_code(500);
            exit;
        }
        $invoice = $this->invoice_model->check_by(['pp_id' => $payload['pp_id']], 'tbl_invoices');
        if ($invoice && $this->piprapay_lib->is_successful($verification)) {
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
        try {
            $res = $this->piprapay_lib->refund_payment($pp_id, (float)$amount);
        } catch (Throwable $e) {
            $res = ['error' => $e->getMessage()];
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($res));
    }
}
?>