# PipraPay Quick Reference Guide

## Quick Start

```php
// 1. Load helper
$this->load->helper('piprapay');

// 2. Get gateways for currency
$gateways = piprapay_get_gateways_for_currency('BDT');

// 3. Create payment
$payment = piprapay_create_payment($invoice_id, $amount, 'bkash');

// 4. Redirect to payment URL
if ($payment['success']) {
    redirect($payment['data']['payment_url']);
}
```

## Common Helper Functions

| Function | Purpose |
|----------|---------|
| `piprapay_get_gateways()` | Get all gateways |
| `piprapay_get_gateways_for_currency($currency)` | Get gateways for currency |
| `piprapay_is_gateway_available($code)` | Check if gateway available |
| `piprapay_create_payment($invoice, $amount, $gateway)` | Create payment |
| `piprapay_verify_payment($tx_id)` | Verify payment |
| `piprapay_gateway_options_dropdown($selected, $currency)` | Generate dropdown |

## Dynamic Gateway Discovery

```php
// All active gateways
$gateways = piprapay_get_gateways();

// Gateways for specific currency
$gateways = piprapay_get_gateways_for_currency('BDT');

// Get gateway collection for filtering
$collection = piprapay_get_gateway_collection();

// Filter and chain operations
$filtered = $collection
    ->getActive()
    ->getByCurrency('BDT')
    ->withFeature('refund')
    ->sortBy('name', 'asc');
```

## Gateway Filtering Examples

```php
// Get collection
$collection = piprapay_get_gateway_collection();

// Active only
$active = $collection->getActive();

// By currency
$bdt = $collection->getByCurrency('BDT');

// By region
$bd = $collection->getByRegion('bangladesh');

// By feature
$refundable = $collection->withFeature('refund');

// Chain filters
$result = $collection
    ->getActive()
    ->getByCurrency('BDT')
    ->withFeature('refund');

// Custom filter
$filtered = $collection->filter(function($g) {
    return $g->getMinAmount() <= 1000;
});

// Sort
$sorted = $collection->sortBy('name', 'asc');
```

## Gateway Validation

```php
// Validate gateway configuration
$validation = piprapay_validate_gateway_config('bkash', [
    'amount' => 1000,
    'currency' => 'BDT'
]);

if ($validation['success']) {
    // Valid
} else {
    echo $validation['message'];
}
```

## Caching

```php
// Refresh gateway cache
piprapay_refresh_gateways();

// Clear gateway cache
piprapay_clear_cache();
```

## Configuration

```php
// application/config/piprapay.php
$config['piprapay_enabled'] = TRUE;
$config['piprapay_api_url'] = 'https://payment.yourdomain.com/api/v1';
$config['piprapay_api_key'] = 'your_api_key';
$config['piprapay_api_secret'] = 'your_api_secret';
$config['piprapay_merchant_id'] = 'your_merchant_id';
$config['piprapay_webhook_secret'] = 'your_webhook_secret';
$config['piprapay_test_mode'] = TRUE;
$config['piprapay_gateway_cache_ttl'] = 3600;
$config['piprapay_gateway_cache_enabled'] = TRUE;
```

## Complete Payment Flow

```php
// 1. Show gateway selection
public function pay($invoice_id) {
    $invoice = $this->invoice_model->get($invoice_id);
    $gateways = piprapay_get_gateways_for_currency($invoice->currency);

    $data = [
        'invoice' => $invoice,
        'gateways' => $gateways['data'] ?? []
    ];

    $this->load->view('payment/pay', $data);
}

// 2. Process payment
public function process() {
    $payment = piprapay_create_payment(
        $this->input->post('invoice_id'),
        $this->input->post('amount'),
        $this->input->post('gateway')
    );

    if ($payment['success']) {
        redirect($payment['data']['payment_url']);
    }
}

// 3. Handle callback
public function callback() {
    $tx_id = $this->input->get('transaction_id');
    $verification = piprapay_verify_payment($tx_id);

    if ($verification['success']) {
        // Update invoice
        redirect('payment/success');
    }
}
```

## View Example

```php
<!-- Gateway dropdown -->
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

<!-- Or use helper -->
<?= piprapay_gateway_options_dropdown('', 'BDT', ['class' => 'form-control']) ?>
```

## Webhook Handler

```php
public function webhook() {
    $input = file_get_contents('php://input');
    $signature = $this->input->server('HTTP_X_SIGNATURE');

    $client = piprapay_client();

    if ($client->verifyWebhook($input, $signature)) {
        $data = json_decode($input, true);

        if ($data['status'] === 'success') {
            // Process payment
            $this->invoice_model->addPayment(
                $data['invoice_id'],
                $data['amount'],
                $data['transaction_id'],
                'PipraPay-' . $data['gateway']
            );
        }

        http_response_code(200);
    } else {
        http_response_code(401);
    }
}
```

## Key Changes from Old Implementation

### Old Way
```php
// Hardcoded gateways
$config['piprapay_allowed_gateways'] = ['bkash', 'nagad', 'stripe'];

// Hardcoded check in view
if (in_array($gateway, ['bkash', 'nagad', 'stripe'])) {
    // ...
}
```

### New Way
```php
// Dynamic - no hardcoded gateways!
$gateways = piprapay_get_gateways();

// View automatically shows all available gateways
foreach ($gateways['data'] as $gateway) {
    if ($gateway['active']) {
        // Show gateway
    }
}
```

## Adding New Gateways

### Step 1: Configure in PipraPay Admin
- Login to PipraPay admin panel
- Navigate to Settings > Payment Gateways
- Add new gateway with credentials
- Enable gateway

### Step 2: Done!
- Gateway automatically appears in your application
- No code changes needed!

## Troubleshooting

### Gateway not showing?
```php
// Refresh cache
piprapay_refresh_gateways();

// Check if available
if (!piprapay_is_gateway_available('bkash')) {
    echo "Gateway not available";
}
```

### Connection issues?
```php
$client = piprapay_client();
$result = $client->testConnection();

if (!$result['success']) {
    echo "Error: " . $result['message'];
}
```

### Configuration errors?
```php
$client = piprapay_client();
$validation = $client->validateConfig();

if (!$validation['valid']) {
    print_r($validation['errors']);
}
```

## Documentation

- **Full Guide**: `piprapay-sdk/docs/codeigniter-integration.md`
- **Gateway Management**: `piprapay-sdk/docs/gateway-management.md`
- **Complete Examples**: `piprapay-sdk/examples/codeigniter-complete.php`
- **Implementation Summary**: `ENHANCEMENT_SUMMARY.md`

## Benefits

✅ **Zero code changes** when adding new gateways
✅ **Unlimited gateway support** via PipraPay admin
✅ **Dynamic discovery** from API
✅ **Smart caching** for performance
✅ **Advanced filtering** (currency, region, features)
✅ **Easy to use** helper functions
✅ **Framework-agnostic** SDK
✅ **Fully reusable** across projects
