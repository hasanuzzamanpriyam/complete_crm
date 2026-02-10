# PipraPay Quick Start Guide

## Step 1: Database Setup

### Option A: Run Migration (Recommended)
```bash
php index.php migrate latest
```

### Option B: Manual SQL
1. Open your MySQL client (phpMyAdmin or command line)
2. Import `setup_piprapay.sql` file
3. Verify tables are updated

## Step 2: Install PipraPay Server

### Download PipraPay
1. Visit https://piprapay.com
2. Download the latest version
3. Extract to your server subdomain (e.g., `payment.yourdomain.com`)

### Configure PipraPay
1. Create a MySQL database for PipraPay
2. Run PipraPay installer (follow on-screen instructions)
3. Create admin account
4. Login to PipraPay admin panel

### Configure Payment Gateways in PipraPay

#### bKash Setup
- Navigate to Settings > Payment Gateways
- Enable bKash
- Enter your bKash credentials (App Key, App Secret, Username, Password)
- Set Test/Live mode

#### Nagad Setup
- Enable Nagad
- Enter your Nagad credentials (Merchant ID, PGW ID, PGW Password)
- Configure callback URLs

#### Stripe Setup
- Enable Stripe
- Enter your Stripe API keys (Publishable Key, Secret Key)
- Set webhook endpoint

## Step 3: Configure PipraPay Integration in CRM

### Get API Credentials from PipraPay
1. In PipraPay Admin, go to Settings > API
2. Generate API Key and Secret
3. Note your Merchant ID
4. Generate Webhook Secret

### Update CRM Configuration

#### Via Admin Panel (Recommended)
1. Login to CRM as admin
2. Go to Settings > Payment Settings
3. Scroll to PipraPay section
4. Fill in:
   - **PipraPay Enabled**: Check to enable
   - **PipraPay API URL**: Your PipraPay instance URL (e.g., https://payment.yourdomain.com/api/v1)
   - **PipraPay API Key**: From PipraPay settings
   - **PipraPay API Secret**: From PipraPay settings
   - **PipraPay Merchant ID**: From PipraPay settings
   - **PipraPay Webhook Secret**: From PipraPay webhook settings
   - **PipraPay Test Mode**: Enable for testing
   - **Default Payment Gateway**: Select (bKash, Nagad, or Stripe)
5. Click Save

#### Via Config File
Edit `application/config/piprapay.php`:
```php
$config['piprapay_enabled'] = TRUE;
$config['piprapay_api_url'] = 'https://payment.yourdomain.com/api/v1';
$config['piprapay_api_key'] = 'your_api_key';
$config['piprapay_api_secret'] = 'your_api_secret';
$config['piprapay_merchant_id'] = 'your_merchant_id';
$config['piprapay_webhook_secret'] = 'your_webhook_secret';
$config['piprapay_test_mode'] = TRUE;
$config['piprapay_default_gateway'] = 'bkash';
```

## Step 4: Configure Webhook

### In PipraPay
1. Go to Settings > Webhooks
2. Add new webhook:
   - **URL**: `https://your-crm-domain.com/payment/piprapay/webhook`
   - **Events**: Select "Payment Completed", "Payment Failed"
   - **Secret**: Note the webhook secret
3. Save webhook

### Test Webhook
- In PipraPay, click "Test Webhook"
- Verify CRM receives the test (check logs)

## Step 5: Enable PipraPay for Invoices

### Globally
1. Go to Settings > Invoice Settings
2. Find "Default Payment Methods"
3. Enable PipraPay

### Per Invoice
1. Create/Edit an invoice
2. In Payment Settings section
3. Check "Allow PipraPay Payment"

## Step 6: Test Payment Flow

### Test Environment
1. Ensure Test Mode is enabled in both CRM and PipraPay
2. Create a test invoice
3. Login as client
4. Navigate to invoice
5. Click "Pay Now"
6. Select PipraPay
7. Choose gateway (e.g., bKash)
8. Enter amount
9. Submit
10. Complete payment on gateway page
11. Verify payment is recorded in CRM

### Test Webhook
1. Make a test payment
2. Check webhook logs in PipraPay
3. Verify CRM receives callback
4. Check payment status in CRM

## Step 7: Go Live

### Before Going Live
- [ ] Test all payment gateways
- [ ] Verify webhook delivery
- [ ] Test refund process
- [ ] Check email notifications
- [ ] Review transaction logs

### Switch to Production
1. In PipraPay: Switch gateways to Live mode
2. In CRM: Disable Test Mode
3. Update API URLs to production
4. Update webhook URL (if different)
5. Test live payment with small amount
6. Monitor first few transactions

## Troubleshooting

### Payment Not Initiating
- Check API credentials are correct
- Verify PipraPay server is accessible
- Check test mode settings match
- Review CRM error logs

### Webhook Not Receiving
- Verify webhook URL is correct
- Check firewall allows PipraPay server
- Validate webhook secret matches
- Check PHP error logs

### Payment Not Verifying
- Ensure transaction ID is correct
- Check API connection
- Verify PipraPay server status
- Review timeout settings

### Transaction Not Recorded
- Check invoice exists
- Verify payment status
- Review database logs
- Check payment model permissions

## Using SDK in Other Projects

### Install SDK
```bash
cd piprapay-sdk
composer install
```

### Quick Start
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

## Support

- **CRM Integration**: Check PIPRAPAY_IMPLEMENTATION.md
- **PipraPay Server**: Visit https://piprapay.com/support
- **SDK Documentation**: See piprapay-sdk/README.md
- **Email**: [email protected]

## API Endpoints Reference

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/payment/piprapay/pay/{invoice_id}` | GET/POST | Display payment form |
| `/payment/piprapay/purchase` | POST | Initiate payment |
| `/payment/piprapay/callback` | GET | Handle payment callback |
| `/payment/piprapay/success` | GET | Handle successful payment |
| `/payment/piprapay/cancel` | GET | Handle cancelled payment |
| `/payment/piprapay/webhook` | POST | Handle PipraPay webhooks |
| `/payment/piprapay/refund` | POST | Process refund |

## Configuration Checklist

- [ ] PipraPay server installed and configured
- [ ] Payment gateways (bKash, Nagad, Stripe) configured in PipraPay
- [ ] API credentials generated in PipraPay
- [ ] CRM configuration updated with PipraPay credentials
- [ ] Webhook configured in PipraPay
- [ ] Webhook URL accessible from PipraPay server
- [ ] PipraPay enabled in CRM payment settings
- [ ] Test payments completed successfully
- [ ] Email notifications working
- [ ] Transaction logs visible
- [ ] Ready for production

## Files Reference

- `application/config/piprapay.php` - Configuration
- `application/libraries/Piprapay_core.php` - API client
- `application/libraries/gateways/Piprapay_gateway.php` - Gateway class
- `application/controllers/payment/Piprapay.php` - Payment controller
- `application/views/payment/piprapay.php` - Payment form
- `application/migrations/610_version_610.php` - Database migration
- `piprapay-sdk/` - Reusable SDK package
- `setup_piprapay.php` - Setup verification script
- `setup_piprapay.sql` - Manual SQL setup

## Next Steps

1. Install PipraPay from https://piprapay.com
2. Configure payment gateways in PipraPay
3. Run setup verification: `php setup_piprapay.php`
4. Execute database migration
5. Configure CRM settings
6. Set up webhook
7. Test payment flow
8. Go live!

Good luck with your PipraPay integration! 🚀
