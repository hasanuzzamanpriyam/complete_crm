<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PipraPay (PayTic) unified gateway controller.
 *
 * Entry point for all PipraPay interactions:
 *   POST/GET /piprapay/initiate/{invoice_id}
 *   GET      /piprapay/callback_success
 *   GET      /piprapay/callback_cancel
 *
 * This controller is module-agnostic – any module (invoices, retainers,
 * subscriptions) can call it by passing the appropriate invoice ID.
 */
class Piprapay_gateway extends CI_Controller {

    /** @var Piprapay */
    protected $piprapay;

    // --------------------------------------------------------------------

    public function __construct()
    {
        parent::__construct();

        // Load the PipraPay core driver library
        $this->load->library('Piprapay');
        $this->piprapay = $this->piprapay;

        // Load the ERP's invoice model
        $this->load->model('invoice_model');

        // Load CI helpers / libraries
        $this->load->helper(['url', 'form']);
    }

    // --------------------------------------------------------------------

    /**
     * 1. Initiate payment – called by the "Pay with PipraPay" button.
     *
     * URL: /piprapay/initiate/{invoice_id}
     * Method: GET or POST
     *
     * @param int $invoice_id
     */
    public function initiate_payment($invoice_id)
    {
        // --- Input validation ---
        $invoiceId = (int) $invoice_id;
        if ($invoiceId <= 0) {
            show_error('Invalid invoice ID', 400);
        }

        // --- Fetch invoice using the standard CRM method ---
        $invoice = $this->invoice_model->check_by(
            array('invoices_id' => $invoiceId),
            'tbl_invoices'
        );

        if (empty($invoice)) {
            show_error('Invoice not found', 404);
        }

        // --- Prevent double payment ---
        $payment_status = $this->invoice_model->get_payment_status($invoiceId);
        if ($payment_status === lang('fully_paid')) {
            $this->_flash_and_redirect('error', 'This invoice is already paid.');
        }

        // --- Calculate invoice due amount ---
        $invoice_due = (float) $this->invoice_model->calculate_to('invoice_due', $invoiceId);
        if ($invoice_due <= 0) {
            $invoice_due = 0.00;
        }

        // --- Fetch client info ---
        $client_info = $this->invoice_model->check_by(
            array('client_id' => $invoice->client_id),
            'tbl_client'
        );

        // --- Build callback URLs ---
        $returnUrl = site_url('piprapay/callback_success');
        $cancelUrl = site_url('piprapay/callback_cancel');

        // --- Metadata to preserve context ---
        $metaData = [
            'invoice_id'     => $invoice->invoices_id,
            'invoice_ref'    => $invoice->reference_no,
            'customer_id'    => $invoice->client_id ?? 0,
            'customer_name'  => !empty($client_info) ? $client_info->name : '',
            'customer_email' => !empty($client_info) ? $client_info->email : '',
            'customer_phone' => !empty($client_info) ? $client_info->mobile : '',
            'description'    => 'Invoice #' . $invoice->reference_no,
        ];

        try {
            // --- Create checkout session via the PipraPay library ---
            $session = $this->piprapay->create_checkout_session(
                $invoice_due,
                $invoice->currency ?? 'BDT',
                $returnUrl,
                $cancelUrl,
                $metaData
            );
        } catch (\RuntimeException $e) {
            log_message('error', 'PipraPay initiate_payment: ' . $e->getMessage());
            $this->_flash_and_redirect('error', 'Unable to start payment. Please try again later.');
            return;
        }

        // --- Record 'initiated' transaction in ledger table ---
        $this->db->insert('piprapay_transactions', [
            'invoice_id'             => $invoice->invoices_id,
            'transaction_reference'  => $session['pp_id'],
            'amount'                 => $invoice_due,
            'currency'               => $invoice->currency ?? 'BDT',
            'payment_gateway_status' => 'initiated',
            'raw_response'           => json_encode($session),
            'created_at'             => date('Y-m-d H:i:s'),
        ]);

        // --- Optionally store pp_id on the invoice row for quick lookup ---
        if ($this->db->field_exists('pp_id', 'tbl_invoices')) {
            $this->db->where('invoices_id', $invoice->invoices_id)
                     ->update('tbl_invoices', ['pp_id' => $session['pp_id']]);
        }

        // --- Redirect the user to PipraPay checkout ---
        redirect($session['pp_url']);
    }

    // --------------------------------------------------------------------

    /**
     * 2. Success callback – PipraPay redirects here after a successful payment.
     *
     * URL: /piprapay/callback_success?pp_id=XXXXX
     * Method: GET
     */
    public function callback_success()
    {
        // --- Capture the transaction reference from query string ---
        $ppId = $this->input->get('pp_id', TRUE);

        if (empty($ppId)) {
            $this->_flash_and_redirect('error', 'Missing payment reference.');
            return;
        }

        try {
            // --- Verify the payment with PipraPay ---
            $verification = $this->piprapay->verify_payment($ppId);
        } catch (\RuntimeException $e) {
            log_message('error', 'PipraPay callback_success verify: ' . $e->getMessage());
            $this->_flash_and_redirect('error', 'Payment verification failed. Please contact support.');
            return;
        }

        // --- Determine success ---
        $isSuccess = $this->piprapay->is_successful($verification);

        // --- Look up the pending transaction ---
        $transaction = $this->db
            ->where('transaction_reference', $ppId)
            ->get('piprapay_transactions')
            ->row();

        if (empty($transaction)) {
            $this->_flash_and_redirect('error', 'Transaction record not found.');
            return;
        }

        // --- Update transaction ledger ---
        $this->db->where('id', $transaction->id)
                 ->update('piprapay_transactions', [
                     'payment_gateway_status' => $isSuccess ? 'success' : 'failed',
                     'raw_response'           => json_encode($verification),
                     'updated_at'             => date('Y-m-d H:i:s'),
                 ]);

        if (!$isSuccess) {
            $this->_flash_and_redirect('error', 'Payment was not successful.');
            return;
        }

        // --- Update the core invoice status and record the payment ---
        $this->_record_successful_payment($transaction->invoice_id, $ppId, $verification);

        // --- Success message and redirect back to invoice view ---
        $this->_flash_and_redirect(
            'success',
            'Thank you! Your payment has been received successfully.',
            'frontend/view_invoice/' . url_encode($transaction->invoice_id)
        );
    }

    // --------------------------------------------------------------------

    /**
     * 3. Cancel callback – user aborted the payment on PipraPay.
     *
     * URL: /piprapay/callback_cancel
     * Method: GET
     */
    public function callback_cancel()
    {
        $ppId = $this->input->get('pp_id', TRUE);

        if (!empty($ppId)) {
            $this->db->where('transaction_reference', $ppId)
                     ->update('piprapay_transactions', [
                         'payment_gateway_status' => 'cancelled',
                         'updated_at'             => date('Y-m-d H:i:s'),
                     ]);
        }

        $invoiceId = $this->input->get('invoice_id', TRUE);
        $redirect  = !empty($invoiceId)
            ? 'frontend/view_invoice/' . url_encode($invoiceId)
            : 'frontend/dashboard';

        $this->_flash_and_redirect(
            'warning',
            'Payment was cancelled. You can try again anytime.',
            $redirect
        );
    }

    // --------------------------------------------------------------------

    /**
     * Helper – set a flash message and redirect.
     *
     * @param string $type    'success', 'error', 'info', 'warning'
     * @param string $message
     * @param string $uri     Optional redirect URI (default = HTTP_REFERER)
     */
    protected function _flash_and_redirect($type, $message, $uri = NULL)
    {
        // Use the CRM's standard set_message helper
        set_message($type, $message);

        if ($uri) {
            redirect($uri);
        }
        redirect($_SERVER['HTTP_REFERER'] ?? 'frontend/dashboard');
    }

    // --------------------------------------------------------------------

    /**
     * Helper – mark an invoice as paid and record the payment in the CRM.
     *
     * Inserts a record into tbl_payments (the standard ERP payment table)
     * and updates the invoice's paid status.
     *
     * @param int    $invoiceId
     * @param string $ppId
     * @param array  $verification  Full verify-payment response
     */
    protected function _record_successful_payment($invoiceId, $ppId, array $verification)
    {
        // Determine the paid amount from the verification response
        $amount = $verification['amount'] ?? $verification['paid_amount'] ?? 0;

        // Insert payment into the CRM's standard payments table
        $payment_data = array(
            'invoices_id'    => $invoiceId,
            'amount'         => $amount,
            'payment_date'   => date('Y-m-d H:i:s'),
            'payment_method' => 'PipraPay',
            'transaction_id' => $ppId,
            'notes'          => 'Paid via PipraPay (PayTic). Ref: ' . $ppId,
            'month_paid'     => date('m-Y'),
        );

        $this->db->insert('tbl_payments', $payment_data);

        // Update invoice status if needed
        // The invoice status is calculated dynamically from payments, 
        // but we also set a direct status flag for quick reference
        $this->db->where('invoices_id', $invoiceId)
                 ->update('tbl_invoices', [
                     'status' => 'Paid',
                 ]);
    }
}
