<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Piprapay_gateway
 *
 * CI3 gateway class for processing CRM invoice payments via Piprapay.
 * Extends App_gateway which provides addPayment(), can_action(), etc.
 *
 * FIX (Bug #4): Piprapay_core is now loaded ONCE in __construct() instead of
 * being reloaded inside every method call. CI's load->library() is idempotent
 * for the first load but the repeated calls were wasteful and fragile.
 */
class Piprapay_gateway extends App_gateway
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('piprapay');
        $this->setName('PipraPay');

        // Load once — all methods share this instance
        $this->ci->load->library('piprapay_core');
    }

    // -----------------------------------------------------------------------
    // INVOICE PAYMENT (CRM internal flow)
    // -----------------------------------------------------------------------

    /**
     * Initiate a Piprapay payment for a CRM invoice.
     * Redirects the user to the Piprapay-hosted payment page.
     *
     * @param array $data  Must contain 'invoice_id', optionally 'amount', 'gateway', 'cancel_url'.
     */
    public function invoice_payment(array $data)
    {
        $invoice_id = (int) ($data['invoice_id'] ?? 0);

        $invoice_info = get_row('tbl_invoices', ['invoices_id' => $invoice_id]);
        if (empty($invoice_info)) {
            set_message('error', 'Invoice not found.');
            redirect($_SERVER['HTTP_REFERER'] ?? 'client/dashboard');
        }

        $client_info  = get_row('tbl_client', ['client_id' => $invoice_info->client_id]);
        $invoice_due  = (float) $this->ci->invoice_model->calculate_to('invoice_due', $invoice_id);
        $invoice_due  = max(0, $invoice_due);

        $amount = isset($data['amount']) ? (float) $data['amount'] : $invoice_due;

        // Server-side cap: never charge more than the due amount (mirrors Bug #5 fix)
        if ($amount <= 0 || $amount > $invoice_due) {
            set_message('error', 'Invalid payment amount.');
            redirect($_SERVER['HTTP_REFERER'] ?? 'client/dashboard');
        }

        $payment_data = [
            'amount'         => $amount,
            'currency'       => $invoice_info->currency ?? 'BDT',
            'invoice_id'     => $invoice_info->invoices_id,
            'customer_name'  => $client_info->name   ?? '',
            'customer_email' => $client_info->email  ?? '',
            'customer_phone' => $client_info->phone  ?? '',
            'gateway'        => $data['gateway'] ?? 'bkash',
            'callback_url'   => base_url('payment/piprapay/callback'),
            'success_url'    => base_url('payment/piprapay/success'),
            'cancel_url'     => $data['cancel_url'] ?? base_url('payment/piprapay/cancel'),
            'description'    => 'Payment for Invoice ' . $invoice_info->reference_no,
            'metadata'       => [
                'client_id'         => $invoice_info->client_id,
                'invoice_reference' => $invoice_info->reference_no,
            ],
        ];

        $response = $this->ci->piprapay_core->initiatePayment($payment_data);

        if (!empty($response['success']) && !empty($response['data']['payment_url'])) {
            $txn_data = $response['data'];

            $this->ci->session->set_userdata([
                'piprapay_transaction_id' => $txn_data['transaction_id'] ?? null,
                'piprapay_invoice_id'     => $invoice_id,
                'piprapay_amount'         => $txn_data['amount'] ?? $amount,
            ]);

            redirect($txn_data['payment_url']);
        }

        $err = $response['message'] ?? 'Payment initiation failed.';
        log_message('error', '[PipraPay] invoice_payment failed: ' . $err);
        set_message('error', $err);
        redirect($_SERVER['HTTP_REFERER'] ?? 'client/dashboard');
    }

    // -----------------------------------------------------------------------
    // PAYMENT VERIFICATION
    // -----------------------------------------------------------------------

    /**
     * Verify a gateway transaction and return structured data.
     *
     * @param  string $transaction_id  Gateway-issued transaction ID.
     * @return array  ['success' => bool, 'transaction_id' => ..., 'amount' => ..., ...]
     */
    public function verifyPayment($transaction_id): array
    {
        $response = $this->ci->piprapay_core->verifyPayment($transaction_id);

        if (!empty($response['success'])) {
            $txn = $response['data'] ?? [];
            $status = strtolower($txn['status'] ?? '');

            if (in_array($status, ['success', 'completed', 'paid'])) {
                return [
                    'success'        => true,
                    'transaction_id' => $txn['transaction_id'] ?? $transaction_id,
                    'amount'         => $txn['amount']         ?? 0,
                    'currency'       => $txn['currency']       ?? 'BDT',
                    'gateway'        => $txn['gateway']        ?? '',
                    'invoice_id'     => $txn['invoice_id']     ?? null,
                ];
            }
        }

        return [
            'success' => false,
            'message' => $response['message'] ?? 'Payment verification failed',
        ];
    }

    // -----------------------------------------------------------------------
    // CALLBACK PROCESSING
    // -----------------------------------------------------------------------

    /**
     * Process a payment callback: verify with Piprapay and record the payment in CRM.
     *
     * @param  array $callback_data  Must contain 'transaction_id', optionally 'invoice_id'.
     * @return array  ['success' => bool, 'message' => '...', 'invoice_id' => ...(on success)]
     */
    public function processCallback(array $callback_data): array
    {
        $transaction_id = trim($callback_data['transaction_id'] ?? '');

        if (empty($transaction_id)) {
            return ['success' => false, 'message' => 'Invalid transaction ID'];
        }

        $verification = $this->verifyPayment($transaction_id);

        if (!$verification['success']) {
            return $verification;
        }

        // Prefer verified invoice_id over caller-supplied one
        $invoice_id = $verification['invoice_id']
                      ?? ($callback_data['invoice_id'] ?? null);

        if (empty($invoice_id)) {
            return ['success' => false, 'message' => 'Invoice ID could not be determined'];
        }

        $result = $this->addPayment(
            $invoice_id,
            $verification['amount'],
            $verification['transaction_id'],
            'PipraPay-' . $verification['gateway']
        );

        if (($result['type'] ?? '') === 'success') {
            return [
                'success'    => true,
                'message'    => 'Payment processed successfully',
                'invoice_id' => $invoice_id,
            ];
        }

        return [
            'success' => false,
            'message' => $result['message'] ?? 'Could not record payment in CRM',
        ];
    }

    // -----------------------------------------------------------------------
    // GATEWAY LISTING
    // -----------------------------------------------------------------------

    /**
     * Get active gateways, optionally filtered by currency.
     * Always returns a plain PHP array (never a GatewayCollection object).
     *
     * FIX (Bug #3 downstream): Normalises the return value from Piprapay_core
     * which now always returns ['success', 'data' => [...]].
     *
     * @param  string|null $currency  ISO 4217 currency code.
     * @return array  [['code' => ..., 'name' => ..., 'active' => ..., ...], ...]
     */
    public function getGatewayOptions(string $currency = null): array
    {
        if ($currency) {
            $result = $this->ci->piprapay_core->getGatewaysForCurrency($currency);
        } else {
            $result = $this->ci->piprapay_core->getGateways(true, true);
        }

        return $result['data'] ?? [];
    }

    // -----------------------------------------------------------------------
    // REFUND
    // -----------------------------------------------------------------------

    /**
     * Request a refund for a completed transaction.
     */
    public function refundTransaction($transaction_id, $amount = null): array
    {
        return $this->ci->piprapay_core->refundPayment($transaction_id, $amount);
    }
}
