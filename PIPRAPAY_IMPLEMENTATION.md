# PipraPay Integration Implementation Summary

## Overview
This document summarizes the implementation of PipraPay centralized payment system for the CRM and provides a reusable SDK for other projects.

## Files Created

### CRM Integration Files

1. **Configuration File**
   - Location: `application/config/piprapay.php`
   - Purpose: Stores PipraPay API configuration settings

2. **Core API Library**
   - Location: `application/libraries/Piprapay_core.php`
   - Purpose: Handles API requests to PipraPay server
   - Features: Payment initiation, verification, refund, webhook verification

3. **Gateway Class**
   - Location: `application/libraries/gateways/Piprapay_gateway.php`
   - Purpose: Extends App_gateway for CRM payment integration
   - Features: Invoice payment processing, callback handling, transaction verification

4. **Payment Controller**
   - Location: `application/controllers/payment/Piprapay.php`
   - Purpose: Handles HTTP requests for payments
   - Methods: pay(), purchase(), callback(), success(), cancel(), webhook(), refund()

5. **Payment View**
   - Location: `application/views/payment/piprapay.php`
   - Purpose: Display payment form to customers
   - Features: Gateway selection, amount input, form validation

6. **Database Migration**
   - Location: `application/migrations/610_version_610.php`
   - Purpose: Database schema updates
   - Changes: Adds PipraPay to online payments table, adds config items, invoice settings

### Standalone SDK Package

**Location: `piprapay-sdk/`**

1. **Core Classes**
   - `PipraPayClient.php` - Main API client
   - `PaymentRequest.php` - Payment request builder
   - `Transaction.php` - Transaction data model

2. **Examples**
   - `examples/basic-payment.php` - How to initiate payment
   - `examples/verify-transaction.php` - How to verify payment

3. **Documentation**
   - `README.md` - Complete SDK documentation
   - `composer.json` - Composer package configuration

## Installation Steps for CRM

### 1. Run Database Migration
```bash
# Run migration to update database schema
php index.php migrate latest
```

### 2. Configure PipraPay Settings
Navigate to: Admin > Settings > Payment Settings

Configure the following:
- PipraPay Enabled: Yes
- PipraPay API URL: Your PipraPay instance URL
- PipraPay API Key: Your API key from PipraPay
- PipraPay API Secret: Your API secret from PipraPay
- PipraPay Merchant ID: Your merchant ID
- PipraPay Webhook Secret: Your webhook secret
- Test Mode: Enable for testing
- Default Payment Gateway: Select bKash, Nagad, or Stripe

### 3. Enable PipraPay for Invoices
Set `allow_piprapay` to 'Yes' in invoice settings or per invoice basis.

### 4. Configure Webhook
Set webhook URL in PipraPay:
```
https://your-crm-domain.com/payment/piprapay/webhook
```

## How to Use in CRM

### From Client Portal
1. Navigate to Invoice
2. Click "Pay Now" button
3. Select PipraPay as payment method
4. Choose gateway (bKash/Nagad/Stripe)
5. Enter amount
6. Submit payment
7. Complete payment on PipraPay page
8. Return to CRM

### From Admin Panel
1. Navigate to Invoice
2. Click "Make Payment"
3. Select Online Payment > PipraPay
4. Follow same process as above

## Using SDK in Other Projects

### Installation
```bash
cd piprapay-sdk
composer install
```

### Basic Usage
```php
require_once 'vendor/autoload.php';

use PipraPay\PipraPayClient;
use PipraPay\PaymentRequest;

$config = [
    'api_url' => 'https://payment.yourdomain.com/api/v1',
    'api_key' => 'your_api_key',
    'api_secret' => 'your_api_secret',
    'merchant_id' => 'your_merchant_id',
    'test_mode' => true
];

$client = new PipraPayClient($config);

$request = new PaymentRequest();
$request->setAmount(1000)
        ->setCurrency('BDT')
        ->setInvoiceId('INV-001')
        ->setGateway('bkash')
        ->setCallbackUrl('https://yourdomain.com/callback')
        ->setSuccessUrl('https://yourdomain.com/success')
        ->setCancelUrl('https://yourdomain.com/cancel');

$response = $client->initiatePayment($request);

if ($response['success']) {
    header('Location: ' . $response['data']['payment_url']);
}
```

## Supported Payment Gateways

- **bKash** - Bangladeshi mobile financial service
- **Nagad** - Bangladeshi digital financial service  
- **Stripe** - International payment gateway

## API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/payments/initiate` | POST | Initiate payment |
| `/payments/verify/{id}` | GET | Verify payment |
| `/transactions/{id}` | GET | Get transaction details |
| `/payments/refund` | POST | Refund payment |
| `/gateways` | GET | Get available gateways |

## Security Features

- HMAC signature verification for webhooks
- SSL/TLS encryption
- API key and secret authentication
- Test mode for sandbox testing
- Transaction verification before completion

## Configuration Reference

### CRM Config (`application/config/piprapay.php`)
```php
$config['piprapay_enabled'] = FALSE;
$config['piprapay_api_url'] = 'https://payment.yourdomain.com/api/v1';
$config['piprapay_api_key'] = '';
$config['piprapay_api_secret'] = '';
$config['piprapay_merchant_id'] = '';
$config['piprapay_webhook_secret'] = '';
$config['piprapay_test_mode'] = TRUE;
$config['piprapay_timeout'] = 30;
$config['piprapay_default_gateway'] = 'bkash';
$config['piprapay_allowed_gateways'] = ['bkash', 'nagad', 'stripe'];
```

## Next Steps

1. **Install PipraPay**
   - Download PipraPay from https://piprapay.com
   - Install on dedicated subdomain (e.g., payment.yourdomain.com)
   - Configure bKash, Nagad, Stripe gateways
   - Generate API credentials

2. **Test Integration**
   - Enable test mode
   - Test each payment gateway
   - Verify webhook callbacks
   - Test payment flows

3. **Go Live**
   - Switch to production mode
   - Update API URLs
   - Monitor transactions
   - Set up alerts

## Troubleshooting

### Payment Fails
- Check API credentials are correct
- Verify PipraPay server is accessible
- Check test mode settings
- Review error logs

### Webhook Not Working
- Verify webhook URL is correct
- Check firewall allows PipraPay server
- Validate webhook secret matches
- Check PHP error logs

### Transaction Not Verified
- Ensure transaction ID is correct
- Check API connection
- Verify PipraPay server status
- Review timeout settings

## Support

For issues related to:
- **CRM Integration**: Check this implementation
- **PipraPay Server**: Contact PipraPay support at [email protected]
- **SDK**: Review piprapay-sdk/README.md

## File Structure

```
D:\laragon\www\tic_crm\
├── application/
│   ├── config/
│   │   └── piprapay.php (NEW)
│   ├── controllers/
│   │   └── payment/
│   │       └── Piprapay.php (NEW)
│   ├── libraries/
│   │   ├── Piprapay_core.php (NEW)
│   │   └── gateways/
│   │       └── Piprapay_gateway.php (NEW)
│   ├── migrations/
│   │   └── 610_version_610.php (NEW)
│   └── views/
│       └── payment/
│           └── piprapay.php (NEW)
└── piprapay-sdk/ (NEW - standalone package)
    ├── src/
    │   └── PipraPay/
    │       ├── PipraPayClient.php
    │       ├── PaymentRequest.php
    │       └── Transaction.php
    ├── examples/
    │   ├── basic-payment.php
    │   └── verify-transaction.php
    ├── composer.json
    └── README.md
```

## License

This implementation is provided for integration with your CRM and can be reused in other projects.
