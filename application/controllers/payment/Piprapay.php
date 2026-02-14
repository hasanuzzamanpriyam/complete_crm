<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Piprapay extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('invoice_model');
        $this->load->library('piprapay_gateway');
    }

    public function pay($invoice_id = NULL)
    {
        if (!$invoice_id) {
            set_message('error', 'Invalid invoice ID');
            redirect($_SERVER['HTTP_REFERER']);
        }

        $invoice_info = $this->invoice_model->check_by(array('invoices_id' => $invoice_id), 'tbl_invoices');
        $invoice_due = $this->invoice_model->calculate_to('invoice_due', $invoice_id);
        
        if ($invoice_due <= 0) {
            $invoice_due = 0.00;
        }

        $data['title'] = lang('piprapay_payment');
        $data['breadcrumbs'] = lang('piprapay');
        $data['piprapay'] = TRUE;
        $data['invoice_info'] = $invoice_info;
        $data['invoice_id'] = $invoice_id;
        $data['invoice_due'] = $invoice_due;

        $currency = $invoice_info->currency ?? 'BDT';
        $data['allowed_gateways'] = $this->piprapay_gateway->getGatewayOptions($currency);

        if ($this->input->post()) {
            $data['post'] = true;
            $data['amount'] = $this->input->post('amount', true);
            $data['selected_gateway'] = $this->input->post('gateway', true);
        }

        if ($this->input->post()) {
            $data['subview'] = $this->load->view('payment/piprapay', $data, true);
            $client_id = $this->session->userdata('client_id');
            if (!empty($client_id)) {
                $this->load->view('client/_layout_main', $data);
            } else {
                $this->load->view('frontend/_layout_main', $data);
            }
        } else {
            $data['subview'] = $this->load->view('payment/piprapay', $data, FALSE);
            $this->load->view('client/_layout_modal', $data);
        }
    }

    public function purchase()
    {
        if (!$this->input->post()) {
            set_message('error', 'Invalid request');
            redirect($_SERVER['HTTP_REFERER']);
        }

        $invoice_id = $this->input->post('invoice_id');
        $amount = $this->input->post('amount');
        $gateway = $this->input->post('gateway');

        $invoice_info = $this->invoice_model->check_by(array('invoices_id' => $invoice_id), 'tbl_invoices');
        $invoice_due = $this->invoice_model->calculate_to('invoice_due', $invoice_id);

        if ($amount > $invoice_due) {
            set_message('error', 'Amount exceeds due amount');
            redirect($_SERVER['HTTP_REFERER']);
        }

        $payment_data = array(
            'invoice_id' => $invoice_id,
            'amount' => $amount,
            'gateway' => $gateway,
            'cancel_url' => $_SERVER['HTTP_REFERER']
        );

        $this->piprapay_gateway->invoice_payment($payment_data);
    }

    public function callback()
    {
        $transaction_id = $this->input->get('transaction_id');
        $status = $this->input->get('status');
        $invoice_id = $this->input->get('invoice_id');

        $callback_data = array(
            'transaction_id' => $transaction_id,
            'invoice_id' => $invoice_id,
            'status' => $status
        );

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
            redirect('frontend/view_invoice/' . url_encode(isset($result['invoice_id']) ? $result['invoice_id'] : $invoice_id));
        }
    }

    public function success()
    {
        $transaction_id = $this->session->userdata('piprapay_transaction_id');
        $invoice_id = $this->session->userdata('piprapay_invoice_id');

        if (!$transaction_id || !$invoice_id) {
            set_message('error', 'Invalid transaction session');
            redirect('client/dashboard');
        }

        $verification_result = $this->piprapay_gateway->verifyPayment($transaction_id);

        if ($verification_result['success']) {
            $result = $this->piprapay_gateway->addPayment(
                $verification_result['invoice_id'],
                $verification_result['amount'],
                $verification_result['transaction_id'],
                'PipraPay-' . $verification_result['gateway']
            );

            if ($result['type'] === 'success') {
                set_message('success', $result['message']);
                $redirect_url = 'client/invoice/manage_invoice/invoice_details/' . $verification_result['invoice_id'];
            } else {
                set_message('error', $result['message']);
                $redirect_url = 'client/dashboard';
            }
        } else {
            set_message('error', 'Payment verification failed: ' . $verification_result['message']);
            $redirect_url = 'client/dashboard';
        }

        $this->session->unset_userdata('piprapay_transaction_id');
        $this->session->unset_userdata('piprapay_invoice_id');
        $this->session->unset_userdata('piprapay_amount');

        $client_id = $this->session->userdata('client_id');
        if (!empty($client_id)) {
            redirect($redirect_url);
        } else {
            redirect('frontend/view_invoice/' . url_encode($invoice_id));
        }
    }

    public function cancel()
    {
        set_message('warning', 'Payment was cancelled');
        
        $invoice_id = $this->session->userdata('piprapay_invoice_id');
        
        $client_id = $this->session->userdata('client_id');
        if (!empty($client_id)) {
            if ($invoice_id) {
                redirect('client/invoice/manage_invoice/invoice_details/' . $invoice_id);
            } else {
                redirect('client/dashboard');
            }
        } else {
            if ($invoice_id) {
                redirect('frontend/view_invoice/' . url_encode($invoice_id));
            } else {
                redirect('frontend');
            }
        }
    }

    public function webhook()
    {
        $input = file_get_contents('php://input');
        $signature = $this->input->server('HTTP_X_SIGNATURE');

        if (!$input || !$signature) {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'Invalid request'));
            return;
        }

        $this->ci->load->library('piprapay_core');

        if (!$this->ci->piprapay_core->verifyWebhook($input, $signature)) {
            http_response_code(401);
            echo json_encode(array('success' => false, 'message' => 'Invalid signature'));
            return;
        }

        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'Invalid JSON'));
            return;
        }

        $callback_data = array(
            'transaction_id' => isset($data['transaction_id']) ? $data['transaction_id'] : '',
            'invoice_id' => isset($data['invoice_id']) ? $data['invoice_id'] : '',
            'status' => isset($data['status']) ? $data['status'] : ''
        );

        $result = $this->piprapay_gateway->processCallback($callback_data);

        http_response_code(200);
        echo json_encode(array('success' => $result['success'], 'message' => $result['message']));
    }

    public function refund()
    {
        $transaction_id = $this->input->post('transaction_id');
        $amount = $this->input->post('amount');

        if (empty($transaction_id)) {
            set_message('error', 'Transaction ID is required');
            redirect($_SERVER['HTTP_REFERER']);
        }

        $result = $this->piprapay_gateway->refundTransaction($transaction_id, $amount);

        if ($result['success']) {
            set_message('success', 'Refund processed successfully');
        } else {
            set_message('error', 'Refund failed: ' . $result['message']);
        }

        redirect($_SERVER['HTTP_REFERER']);
    }
}
