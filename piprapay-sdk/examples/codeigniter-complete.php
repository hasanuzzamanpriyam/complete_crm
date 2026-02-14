<?php
/**
 * PipraPay CodeIgniter Integration Example
 *
 * This example demonstrates how to integrate PipraPay into a CodeIgniter application
 * using the helper functions and SDK features.
 */

// Load the PipraPay helper
$this->load->helper('piprapay');

// ============================================================
// Example 1: Check if PipraPay is enabled
// ============================================================
if (piprapay_is_enabled()) {
    echo "PipraPay is enabled\n";
} else {
    echo "PipraPay is not enabled\n";
}

// ============================================================
// Example 2: Check if running in test mode
// ============================================================
if (piprapay_is_test_mode()) {
    echo "Running in test mode\n";
} else {
    echo "Running in live mode\n";
}

// ============================================================
// Example 3: Get available gateways
// ============================================================
$gateways = piprapay_get_gateways();

if ($gateways['success']) {
    $gatewayList = $gateways['data'];

    echo "Available Gateways:\n";
    foreach ($gatewayList as $gateway) {
        if ($gateway['active']) {
            echo "- " . $gateway['name'] . " (" . $gateway['code'] . ")\n";
        }
    }
}

// ============================================================
// Example 4: Get gateways for specific currency
// ============================================================
$currencyGateways = piprapay_get_gateways_for_currency('BDT');

if ($currencyGateways['success']) {
    echo "\nGateways supporting BDT:\n";

    foreach ($currencyGateways['data'] as $gateway) {
        echo "- " . $gateway['name'] . "\n";
        echo "  Min: " . $gateway['min_amount'] . "\n";
        echo "  Max: " . $gateway['max_amount'] . "\n";
    }
}

// ============================================================
// Example 5: Check if gateway is available
// ============================================================
$gatewayCode = 'bkash';

if (piprapay_is_gateway_available($gatewayCode)) {
    echo "\n{$gatewayCode} is available\n";
} else {
    echo "\n{$gatewayCode} is not available\n";
}

// ============================================================
// Example 6: Get gateway details
// ============================================================
$gateway = piprapay_get_gateway('bkash');

if ($gateway) {
    echo "\nGateway Details:\n";
    echo "Name: " . $gateway->getName() . "\n";
    echo "Code: " . $gateway->getCode() . "\n";
    echo "Icon: " . $gateway->getIcon() . "\n";
    echo "Currencies: " . implode(', ', $gateway->getCurrencies()) . "\n";
    echo "Features: " . implode(', ', $gateway->getFeatures()) . "\n";
    echo "Min Amount: " . $gateway->getMinAmount() . "\n";
    echo "Max Amount: " . $gateway->getMaxAmount() . "\n";
    echo "Region: " . $gateway->getRegion() . "\n";
}

// ============================================================
// Example 7: Validate gateway configuration
// ============================================================
$validation = piprapay_validate_gateway_config('bkash', [
    'amount' => 1000,
    'currency' => 'BDT'
]);

if ($validation['success']) {
    echo "\nGateway configuration is valid\n";
} else {
    echo "\nGateway configuration error: " . $validation['message'] . "\n";
}

// ============================================================
// Example 8: Create a payment
// ============================================================
$invoiceId = 'INV-001';
$amount = 1000;
$gatewayCode = 'bkash';

$payment = piprapay_create_payment(
    $invoiceId,
    $amount,
    $gatewayCode,
    [
        'name' => 'John Doe',
        'email' => '[email protected]',
        'phone' => '+8801XXXXXXXXX'
    ]
);

if ($payment['success']) {
    $paymentUrl = $payment['data']['payment_url'];
    $transactionId = $payment['data']['transaction_id'];

    echo "\nPayment created successfully\n";
    echo "Payment URL: " . $paymentUrl . "\n";
    echo "Transaction ID: " . $transactionId . "\n";

    // Redirect to payment page
    // redirect($paymentUrl);
} else {
    echo "\nPayment creation failed: " . $payment['message'] . "\n";
}

// ============================================================
// Example 9: Verify a payment
// ============================================================
$transactionId = 'TXN123456';

$verification = piprapay_verify_payment($transactionId);

if ($verification['success']) {
    $transaction = $verification['data'];

    echo "\nPayment Verification:\n";
    echo "Status: " . $transaction['status'] . "\n";
    echo "Amount: " . $transaction['amount'] . "\n";
    echo "Currency: " . $transaction['currency'] . "\n";
    echo "Gateway: " . $transaction['gateway'] . "\n";
    echo "Invoice ID: " . $transaction['invoice_id'] . "\n";
} else {
    echo "\nVerification failed: " . $verification['message'] . "\n";
}

// ============================================================
// Example 10: Get transaction details
// ============================================================
$transaction = piprapay_get_transaction($transactionId);

if ($transaction['success']) {
    $data = $transaction['data'];

    echo "\nTransaction Details:\n";
    echo "Status: " . $data['status'] . "\n";
    echo "Customer: " . $data['customer_name'] . "\n";
    echo "Created: " . $data['created_at'] . "\n";
}

// ============================================================
// Example 11: Refund a payment
// ============================================================
$refund = piprapay_refund_payment($transactionId, 500);

if ($refund['success']) {
    echo "\nRefund processed successfully\n";
} else {
    echo "\nRefund failed: " . $refund['message'] . "\n";
}

// ============================================================
// Example 12: Generate gateway dropdown options
// ============================================================
$dropdown = piprapay_gateway_options_dropdown('', 'BDT', [
    'class' => 'form-control',
    'id' => 'payment_gateway',
    'required' => true
]);

echo "\nGateway Dropdown:\n";
echo $dropdown;

// ============================================================
// Example 13: Refresh gateway cache
// ============================================================
$refreshResult = piprapay_refresh_gateways();

if ($refreshResult['success']) {
    echo "\nGateway cache refreshed successfully\n";
}

// ============================================================
// Example 14: Clear gateway cache
// ============================================================
$clearResult = piprapay_clear_cache();

if ($clearResult) {
    echo "Gateway cache cleared\n";
}

// ============================================================
// Example 15: Working with GatewayCollection
// ============================================================
$collection = piprapay_get_gateway_collection();

// Get active BDT gateways with refund feature
$filtered = $collection
    ->getActive()
    ->getByCurrency('BDT')
    ->withFeature('refund')
    ->sortBy('name', 'asc');

echo "\nFiltered Gateways:\n";
foreach ($filtered as $gateway) {
    echo "- " . $gateway->getName() . "\n";
}

// ============================================================
// Example 16: Test connection to PipraPay
// ============================================================
$client = piprapay_client();

$testResult = $client->testConnection();

if ($testResult['success']) {
    echo "\nConnection test successful\n";
    echo "HTTP Code: " . $testResult['http_code'] . "\n";
} else {
    echo "\nConnection test failed: " . $testResult['message'] . "\n";
}

// ============================================================
// Example 17: Validate configuration
// ============================================================
$configValidation = $client->validateConfig();

if ($configValidation['valid']) {
    echo "Configuration is valid\n";
} else {
    echo "Configuration errors:\n";
    foreach ($configValidation['errors'] as $error) {
        echo "- " . $error . "\n";
    }
}

// ============================================================
// Example 18: Complete payment flow in a controller
// ============================================================
class Payment extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('piprapay');
        $this->load->model('invoice_model');
    }

    public function index($invoice_id)
    {
        // Check if PipraPay is enabled
        if (!piprapay_is_enabled()) {
            show_error('Payment system is not available');
        }

        // Get invoice details
        $invoice = $this->invoice_model->get($invoice_id);

        if (!$invoice) {
            show_error('Invoice not found');
        }

        // Get available gateways for invoice currency
        $gateways = piprapay_get_gateways_for_currency($invoice->currency);

        // Prepare view data
        $data = [
            'invoice' => $invoice,
            'gateways' => $gateways['success'] ? $gateways['data'] : []
        ];

        // Load view
        $this->load->view('payment/select', $data);
    }

    public function process()
    {
        $invoice_id = $this->input->post('invoice_id');
        $amount = $this->input->post('amount');
        $gateway = $this->input->post('gateway');

        // Get invoice details
        $invoice = $this->invoice_model->get($invoice_id);

        // Validate gateway configuration
        $validation = piprapay_validate_gateway_config($gateway, [
            'amount' => $amount,
            'currency' => $invoice->currency
        ]);

        if (!$validation['success']) {
            show_error($validation['message']);
        }

        // Create payment
        $payment = piprapay_create_payment($invoice_id, $amount, $gateway, [
            'name' => $this->session->userdata('name'),
            'email' => $this->session->userdata('email'),
            'phone' => $this->session->userdata('phone')
        ]);

        if ($payment['success']) {
            redirect($payment['data']['payment_url']);
        } else {
            show_error($payment['message']);
        }
    }

    public function callback()
    {
        $transaction_id = $this->input->get('transaction_id');

        // Verify payment
        $verification = piprapay_verify_payment($transaction_id);

        if ($verification['success']) {
            $transaction = $verification['data'];

            if ($transaction['status'] === 'success') {
                // Update invoice status
                $this->invoice_model->addPayment(
                    $transaction['invoice_id'],
                    $transaction['amount'],
                    $transaction['transaction_id'],
                    'PipraPay-' . $transaction['gateway']
                );

                redirect('payment/success');
            } else {
                redirect('payment/failed');
            }
        } else {
            show_error('Payment verification failed');
        }
    }

    public function success()
    {
        $this->load->view('payment/success');
    }

    public function failed()
    {
        $this->load->view('payment/failed');
    }
}

// ============================================================
// Example 19: View template for gateway selection
// ============================================================
/*
<!-- application/views/payment/select.php -->
<div class="payment-form">
    <h2>Pay Invoice #<?= $invoice->reference_no ?></h2>
    <p>Amount: <?= display_money($invoice->amount, $invoice->currency) ?></p>

    <form method="post" action="<?= site_url('payment/process') ?>">
        <input type="hidden" name="invoice_id" value="<?= $invoice->id ?>">

        <div class="form-group">
            <label>Payment Amount</label>
            <input type="number" name="amount"
                   value="<?= $invoice->amount ?>"
                   min="1"
                   max="<?= $invoice->amount ?>"
                   class="form-control"
                   required>
        </div>

        <div class="form-group">
            <label>Select Payment Method</label>
            <select name="gateway" class="form-control" required>
                <option value="">Select Gateway</option>
                <?php foreach ($gateways as $gateway): ?>
                    <?php if ($gateway['active']): ?>
                        <option value="<?= $gateway['code'] ?>"
                                data-min="<?= $gateway['min_amount'] ?>"
                                data-max="<?= $gateway['max_amount'] ?>">
                            <?= $gateway['name'] ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">
            Pay Now
        </button>
    </form>
</div>
*/

// ============================================================
// Example 20: Webhook handler
// ============================================================
class Webhook extends CI_Controller
{
    public function piprapay()
    {
        // Get raw input
        $input = file_get_contents('php://input');
        $signature = $this->input->server('HTTP_X_SIGNATURE');

        // Load helper
        $this->load->helper('piprapay');

        // Get client
        $client = piprapay_client();

        // Verify webhook signature
        if (!$client->verifyWebhook($input, $signature)) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid signature']);
            return;
        }

        // Parse webhook data
        $data = json_decode($input, true);

        $transaction_id = $data['transaction_id'];
        $status = $data['status'];
        $invoice_id = $data['invoice_id'];

        // Log webhook
        log_message('info', 'PipraPay webhook received: ' . json_encode($data));

        // Process webhook based on status
        if ($status === 'success') {
            // Get transaction details
            $transaction = piprapay_get_transaction($transaction_id);

            if ($transaction['success']) {
                $txData = $transaction['data'];

                // Add payment to invoice
                $this->load->model('invoice_model');
                $this->invoice_model->addPayment(
                    $invoice_id,
                    $txData['amount'],
                    $txData['transaction_id'],
                    'PipraPay-' . $txData['gateway']
                );

                log_message('info', 'Payment added for invoice ' . $invoice_id);
            }
        }

        // Return success response
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Webhook processed']);
    }
}

// ============================================================
// End of examples
// ============================================================
