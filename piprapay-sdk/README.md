# PipraPay PHP SDK

A standalone PHP SDK for integrating PipraPay centralized payment system into your projects.

## Features

- Easy integration with PipraPay payment platform
- Support for multiple payment gateways (bKash, Nagad, Stripe, etc.)
- Payment initiation and verification
- Transaction management
- Webhook verification
- PSR-4 autoloading
- Framework agnostic

## Installation

Install via Composer:

```bash
composer require piprapay/sdk
```

Or include manually:

```php
require_once 'vendor/autoload.php';
```

## Configuration

```php
use PipraPay\PipraPayClient;

$config = [
    'api_url' => 'https://payment.yourdomain.com/api/v1',
    'api_key' => 'your_api_key',
    'api_secret' => 'your_api_secret',
    'merchant_id' => 'your_merchant_id',
    'webhook_secret' => 'your_webhook_secret',
    'test_mode' => true
];

$client = new PipraPayClient($config);
```

## Usage

### Initiate Payment

```php
use PipraPay\PipraPayClient;
use PipraPay\PaymentRequest;

$client = new PipraPayClient($config);

$request = new PaymentRequest();
$request->setAmount(1000)
        ->setCurrency('BDT')
        ->setInvoiceId('INV-001')
        ->setCustomerName('John Doe')
        ->setCustomerEmail('[email protected]')
        ->setCustomerPhone('+8801XXXXXXXXX')
        ->setGateway('bkash')
        ->setCallbackUrl('https://yourdomain.com/payment/callback')
        ->setSuccessUrl('https://yourdomain.com/payment/success')
        ->setCancelUrl('https://yourdomain.com/payment/cancel')
        ->setDescription('Payment for Invoice #INV-001');

$response = $client->initiatePayment($request);

if ($response['success']) {
    $paymentUrl = $response['data']['payment_url'];
    header('Location: ' . $paymentUrl);
}
```

### Verify Payment

```php
$transactionId = $_GET['transaction_id'];

$response = $client->verifyPayment($transactionId);

if ($response['success']) {
    $transaction = $response['data'];
    
    if ($transaction['status'] === 'success') {
        // Process successful payment
    }
}
```

### Get Transaction Details

```php
$transactionId = 'TXN123456';

$response = $client->getTransaction($transactionId);

if ($response['success']) {
    $transaction = $response['data'];
    // Access transaction details
}
```

### Refund Payment

```php
$transactionId = 'TXN123456';
$amount = 500; // Optional, full refund if null

$response = $client->refundPayment($transactionId, $amount);

if ($response['success']) {
    // Refund processed successfully
}
```

### Get Available Gateways

```php
$response = $client->getGateways();

if ($response['success']) {
    $gateways = $response['data'];
    foreach ($gateways as $gateway) {
        echo $gateway['name'] . ' (' . $gateway['code'] . ")\n";
    }
}
```

### Handle Webhooks

```php
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_SIGNATURE'];

if ($client->verifyWebhook($payload, $signature)) {
    $data = json_decode($payload, true);
    
    $transactionId = $data['transaction_id'];
    $status = $data['status'];
    
    // Process webhook data
    http_response_code(200);
} else {
    http_response_code(401);
}
```

## Supported Payment Gateways

- bKash
- Nagad
- Stripe
- More coming soon...

## Framework Integrations

### Laravel

Create a service provider:

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use PipraPay\PipraPayClient;

class PipraPayServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PipraPayClient::class, function ($app) {
            return new PipraPayClient([
                'api_url' => config('services.piprapay.url'),
                'api_key' => config('services.piprapay.key'),
                'api_secret' => config('services.piprapay.secret'),
                'merchant_id' => config('services.piprapay.merchant_id'),
                'test_mode' => config('services.piprapay.test_mode')
            ]);
        });
    }
}
```

### CodeIgniter

Use directly with the library:

```php
$this->load->library('Piprapay_core');
```

### Native PHP

See examples in the `examples/` directory.

## Error Handling

```php
$response = $client->initiatePayment($request);

if (!$response['success']) {
    $error = $client->getLastError();
    echo "Error: " . $response['message'];
}
```

## Testing

To enable test mode:

```php
$config['test_mode'] = true;
$client = new PipraPayClient($config);
```

## API Methods

| Method | Description |
|--------|-------------|
| `initiatePayment(PaymentRequest $request)` | Initiate a new payment |
| `verifyPayment($transactionId)` | Verify payment status |
| `getTransaction($transactionId)` | Get transaction details |
| `refundPayment($transactionId, $amount = null)` | Refund a payment |
| `getGateways()` | Get available payment gateways |
| `verifyWebhook($payload, $signature)` | Verify webhook signature |

## Payment Request Methods

| Method | Description |
|--------|-------------|
| `setAmount($amount)` | Set payment amount |
| `setCurrency($currency)` | Set currency code |
| `setInvoiceId($invoiceId)` | Set invoice ID |
| `setCustomerName($name)` | Set customer name |
| `setCustomerEmail($email)` | Set customer email |
| `setCustomerPhone($phone)` | Set customer phone |
| `setGateway($gateway)` | Set payment gateway |
| `setCallbackUrl($url)` | Set callback URL |
| `setSuccessUrl($url)` | Set success URL |
| `setCancelUrl($url)` | Set cancel URL |
| `setDescription($description)` | Set payment description |
| `setMetadata(array $metadata)` | Set metadata |
| `addMetadata($key, $value)` | Add metadata item |

## Requirements

- PHP >= 7.4
- cURL extension
- JSON extension

## License

MIT License

## Support

For issues and support, please contact [email protected]

## Documentation

For more detailed documentation, visit [https://piprapay.com/developer](https://piprapay.com/developer)
