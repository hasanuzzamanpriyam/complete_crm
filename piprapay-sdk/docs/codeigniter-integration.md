# PipraPay CodeIgniter Integration Guide

## Table of Contents
1. [Quick Start](#quick-start)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Using Helper Functions](#using-helper-functions)
5. [Dynamic Gateway Discovery](#dynamic-gateway-discovery)
6. [Creating Payments](#creating-payments)
7. [Handling Callbacks](#handling-callbacks)
8. [Advanced Usage](#advanced-usage)
9. [Troubleshooting](#troubleshooting)

## Quick Start

### 1. Load the Helper

```php
// In your controller
$this->load->helper('piprapay');
```

### 2. Check if PipraPay is Enabled

```php
if (!piprapay_is_enabled()) {
    show_error('PipraPay is not enabled');
}
```

### 3. Get Available Gateways

```php
$gateways = piprapay_get_gateways();

if ($gateways['success']) {
    foreach ($gateways['data'] as $gateway) {
        echo $gateway['name'] . "\n";
    }
}
```

## Installation

### Manual Installation

1. **Copy SDK files** to `piprapay-sdk/` directory
2. **Copy library files** to `application/libraries/`
3. **Copy helper file** to `application/helpers/piprapay_helper.php`
4. **Copy config file** to `application/config/piprapay.php`

### Autoloading

Add PipraPay to your autoload configuration:

```php
// application/config/autoload.php
$autoload['libraries'] = array('piprapay_core');
$autoload['helper'] = array('piprapay');
```

## Configuration

### Basic Configuration

```php
// application/config/piprapay.php
$config['piprapay_enabled'] = TRUE;
$config['piprapay_api_url'] = 'https://payment.yourdomain.com/api/v1';
$config['piprapay_api_key'] = 'your_api_key';
$config['piprapay_api_secret'] = 'your_api_secret';
$config['piprapay_merchant_id'] = 'your_merchant_id';
$config['piprapay_webhook_secret'] = 'your_webhook_secret';
$config['piprapay_test_mode'] = TRUE;
$config['piprapay_timeout'] = 30;
$config['piprapay_default_gateway'] = 'bkash';
$config['piprapay_gateway_cache_ttl'] = 3600;
$config['piprapay_gateway_cache_enabled'] = TRUE;
```

### Environment-based Configuration

```php
// application/config/piprapay.php
$config['piprapay_enabled'] = ENVIRONMENT === 'production';

if (ENVIRONMENT === 'production') {
    $config['piprapay_api_url'] = 'https://payment.yourdomain.com/api/v1';
    $config['piprapay_test_mode'] = FALSE;
} else {
    $config['piprapay_api_url'] = 'https://payment-test.yourdomain.com/api/v1';
    $config['piprapay_test_mode'] = TRUE;
}
```

## Using Helper Functions

### Get PipraPay Client

```php
$client = piprapay_client();
```

### Check if PipraPay is Enabled

```php
if (piprapay_is_enabled()) {
    // PipraPay is enabled
}
```

### Check if Test Mode

```php
if (piprapay_is_test_mode()) {
    // Running in test mode
}
```

### Get Configuration

```php
$config = piprapay_get_config();

echo $config['piprapay_api_url'];
```

## Dynamic Gateway Discovery

### Get All Active Gateways

```php
$gateways = piprapay_get_gateways(true, true);

if ($gateways['success']) {
    $gatewayList = $gateways['data'];

    foreach ($gatewayList as $gateway) {
        if ($gateway['active']) {
            echo "Gateway: {$gateway['name']} ({$gateway['code']})\n";
        }
    }
}
```

### Get Gateways for Specific Currency

```php
$gateways = piprapay_get_gateways_for_currency('BDT');

if ($gateways['success']) {
    $gatewayList = $gateways['data'];

    foreach ($gatewayList as $gateway) {
        echo "Gateway: {$gateway['name']}\n";
        echo "  Min Amount: {$gateway['min_amount']}\n";
        echo "  Max Amount: {$gateway['max_amount']}\n";
        echo "  Currencies: " . implode(', ', $gateway['currencies']) . "\n";
    }
}
```

### Check if Gateway is Available

```php
if (piprapay_is_gateway_available('bkash')) {
    // bKash is available
}
```

### Get Gateway Details

```php
$gateway = piprapay_get_gateway('bkash');

if ($gateway) {
    echo "Gateway: " . $gateway->getName() . "\n";
    echo "Icon: " . $gateway->getIcon() . "\n";
    echo "Currencies: " . implode(', ', $gateway->getCurrencies()) . "\n";
    echo "Features: " . implode(', ', $gateway->getFeatures()) . "\n";
}
```

### Validate Gateway Configuration

```php
$validation = piprapay_validate_gateway_config('bkash', [
    'amount' => 1000,
    'currency' => 'BDT'
]);

if ($validation['success']) {
    // Gateway configuration is valid
} else {
    echo "Error: " . $validation['message'];
}
```

### Generate Gateway Dropdown Options

```php
$options = piprapay_gateway_options_dropdown('', 'BDT', [
    'class' => 'form-control',
    'id' => 'payment_gateway'
]);

echo $options;
```

## Creating Payments

### Basic Payment

```php
$payment = piprapay_create_payment(
    $invoice_id,
    $amount,
    'bkash'
);

if ($payment['success']) {
    $paymentUrl = $payment['data']['payment_url'];
    $transactionId = $payment['data']['transaction_id'];

    redirect($paymentUrl);
} else {
    echo "Error: " . $payment['message'];
}
```

### Payment with Customer Data

```php
$payment = piprapay_create_payment(
    $invoice_id,
    $amount,
    'nagad',
    [
        'name' => 'John Doe',
        'email' => '[email protected]',
        'phone' => '+8801XXXXXXXXX'
    ]
);

if ($payment['success']) {
    redirect($payment['data']['payment_url']);
}
```

### Advanced Payment (Using SDK Directly)

```php
$client = piprapay_client();

$request = new \PipraPay\PaymentRequest();
$request->setAmount(1000)
        ->setCurrency('BDT')
        ->setInvoiceId('INV-001')
        ->setCustomerName('John Doe')
        ->setCustomerEmail('[email protected]')
        ->setCustomerPhone('+8801XXXXXXXXX')
        ->setGateway('bkash')
        ->setCallbackUrl(site_url('payment/callback'))
        ->setSuccessUrl(site_url('payment/success'))
        ->setCancelUrl(site_url('payment/cancel'))
        ->setDescription('Payment for Invoice #INV-001');

$response = $client->initiatePayment($request);

if ($response['success']) {
    redirect($response['data']['payment_url']);
}
```

## Handling Callbacks

### Verify Payment

```php
$transaction_id = $this->input->get('transaction_id');

$verification = piprapay_verify_payment($transaction_id);

if ($verification['success']) {
    $transaction = $verification['data'];

    if ($transaction['status'] === 'success') {
        // Payment successful - update invoice
        $amount = $transaction['amount'];
        $gateway = $transaction['gateway'];

        // Add payment to your system
        // ...
    }
}
```

### Process Webhook

```php
$input = file_get_contents('php://input');
$signature = $this->input->server('HTTP_X_SIGNATURE');

$client = piprapay_client();

if ($client->verifyWebhook($input, $signature)) {
    $data = json_decode($input, true);

    $transaction_id = $data['transaction_id'];
    $status = $data['status'];

    // Process webhook
    // ...

    http_response_code(200);
} else {
    http_response_code(401);
}
```

### Get Transaction Details

```php
$transaction = piprapay_get_transaction($transaction_id);

if ($transaction['success']) {
    $data = $transaction['data'];

    echo "Status: " . $data['status'] . "\n";
    echo "Amount: " . $data['amount'] . "\n";
    echo "Gateway: " . $data['gateway'] . "\n";
}
```

## Advanced Usage

### Refresh Gateway Cache

```php
$gateways = piprapay_refresh_gateways();

if ($gateways['success']) {
    echo "Gateways refreshed successfully";
}
```

### Clear Gateway Cache

```php
$result = piprapay_clear_cache();

if ($result) {
    echo "Gateway cache cleared";
}
```

### Working with Gateway Collection

```php
$collection = piprapay_get_gateway_collection();

// Get active gateways for BDT
$bdtGateways = $collection->getActive()->getByCurrency('BDT');

// Get gateways with refund feature
$refundableGateways = $collection->withFeature('refund');

// Get gateways sorted by name
$sortedGateways = $collection->sortBy('name', 'asc');

// Find a specific gateway
$gateway = $collection->find(function($g) {
    return $g->getCode() === 'bkash';
});
```

### Test Connection to PipraPay

```php
$client = piprapay_client();

$testResult = $client->testConnection();

if ($testResult['success']) {
    echo "Connection successful";
} else {
    echo "Connection failed: " . $testResult['message'];
}
```

### Validate Configuration

```php
$client = piprapay_client();

$configValidation = $client->validateConfig();

if ($configValidation['valid']) {
    echo "Configuration is valid";
} else {
    echo "Errors: " . implode(', ', $configValidation['errors']);
}
```

## Troubleshooting

### Gateway List Not Loading

**Problem:** Gateways are not showing up in the dropdown

**Solutions:**
1. Check if PipraPay is enabled: `piprapay_is_enabled()`
2. Verify API credentials in config
3. Clear gateway cache: `piprapay_clear_cache()`
4. Test connection: `$client->testConnection()`
5. Check PipraPay server logs

```php
// Debug code
$config = piprapay_get_config();
echo "API URL: " . $config['piprapay_api_url'] . "\n";
echo "Test Mode: " . ($config['piprapay_test_mode'] ? 'Yes' : 'No') . "\n";

$client = piprapay_client();
$result = $client->testConnection();
print_r($result);
```

### Payment Not Initiating

**Problem:** Payment URL is not returned

**Solutions:**
1. Verify gateway code is correct
2. Check if gateway is active: `piprapay_is_gateway_available('bkash')`
3. Validate amount is within gateway limits
4. Check currency is supported by gateway

```php
// Debug payment
$validation = piprapay_validate_gateway_config('bkash', [
    'amount' => $amount,
    'currency' => 'BDT'
]);

if (!$validation['success']) {
    echo "Validation Error: " . $validation['message'];
}
```

### Webhook Not Receiving

**Problem:** Webhook endpoint not receiving callbacks

**Solutions:**
1. Verify webhook URL is accessible from PipraPay server
2. Check firewall allows PipraPay server IP
3. Validate webhook secret matches
4. Check server logs for errors

```php
// Debug webhook
$input = file_get_contents('php://input');
file_put_contents('webhook_debug.log', $input . "\n", FILE_APPEND);
```

### Connection Timeout

**Problem:** Requests to PipraPay timeout

**Solutions:**
1. Increase timeout in config
2. Check network connectivity
3. Verify PipraPay server is running
4. Test connection from command line

```php
// Increase timeout
$config = piprapay_get_config();
$config['piprapay_timeout'] = 60;

// Or test connection
$client = piprapay_client();
$result = $client->testConnection();
print_r($result);
```

## Best Practices

1. **Always validate gateway configuration** before initiating payments
2. **Use caching** for gateway lists to improve performance
3. **Handle errors gracefully** and provide clear user feedback
4. **Log all transactions** for debugging and audit trails
5. **Test in sandbox mode** before going live
6. **Secure webhook endpoints** with signature verification
7. **Implement retry logic** for failed API requests
8. **Monitor API rate limits** and implement throttling if needed

## Complete Example

```php
class Payment extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('piprapay');
        $this->load->model('invoice_model');
    }

    public function pay($invoice_id)
    {
        if (!piprapay_is_enabled()) {
            show_error('Payment system is not available');
        }

        $invoice = $this->invoice_model->get($invoice_id);
        $gateways = piprapay_get_gateways_for_currency($invoice->currency);

        $data = [
            'invoice' => $invoice,
            'gateways' => $gateways['data'] ?? []
        ];

        $this->load->view('payment/select', $data);
    }

    public function process()
    {
        $invoice_id = $this->input->post('invoice_id');
        $amount = $this->input->post('amount');
        $gateway = $this->input->post('gateway');

        // Validate gateway
        $validation = piprapay_validate_gateway_config($gateway, [
            'amount' => $amount,
            'currency' => $this->invoice_model->get($invoice_id)->currency
        ]);

        if (!$validation['success']) {
            show_error($validation['message']);
        }

        // Create payment
        $payment = piprapay_create_payment($invoice_id, $amount, $gateway);

        if ($payment['success']) {
            redirect($payment['data']['payment_url']);
        } else {
            show_error($payment['message']);
        }
    }

    public function callback()
    {
        $transaction_id = $this->input->get('transaction_id');

        $verification = piprapay_verify_payment($transaction_id);

        if ($verification['success']) {
            $transaction = $verification['data'];

            if ($transaction['status'] === 'success') {
                // Update invoice status
                $this->invoice_model->mark_paid(
                    $transaction['invoice_id'],
                    $transaction['amount'],
                    $transaction['transaction_id']
                );

                redirect('payment/success');
            }
        }

        redirect('payment/failed');
    }
}
```

## Support

For more information:
- SDK Documentation: `piprapay-sdk/README.md`
- API Reference: PipraPay Server Documentation
- Troubleshooting: Check logs and use debug code

## License

See LICENSE file for details.
