<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PipraPay\PipraPayClient;
use PipraPay\PaymentRequest;

$config = [
    'api_url' => 'https://payment.yourdomain.com/api/v1',
    'api_key' => 'your_api_key_here',
    'api_secret' => 'your_api_secret_here',
    'merchant_id' => 'your_merchant_id_here',
    'test_mode' => true
];

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
    $transactionId = $response['data']['transaction_id'];
    
    echo "Payment URL: " . $paymentUrl . "\n";
    echo "Transaction ID: " . $transactionId . "\n";
    
    header('Location: ' . $paymentUrl);
} else {
    echo "Error: " . $response['message'] . "\n";
}
