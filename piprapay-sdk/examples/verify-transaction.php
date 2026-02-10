<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PipraPay\PipraPayClient;

$config = [
    'api_url' => 'https://payment.yourdomain.com/api/v1',
    'api_key' => 'your_api_key_here',
    'api_secret' => 'your_api_secret_here',
    'merchant_id' => 'your_merchant_id_here',
    'test_mode' => true
];

$client = new PipraPayClient($config);

$transactionId = $_GET['transaction_id'] ?? '';

if (empty($transactionId)) {
    die('Transaction ID is required');
}

$response = $client->verifyPayment($transactionId);

if ($response['success']) {
    $transaction = $response['data'];
    
    echo "Transaction ID: " . $transaction['transaction_id'] . "\n";
    echo "Amount: " . $transaction['amount'] . " " . $transaction['currency'] . "\n";
    echo "Status: " . $transaction['status'] . "\n";
    echo "Gateway: " . $transaction['gateway'] . "\n";
    
    if ($transaction['status'] === 'success' || $transaction['status'] === 'completed') {
        echo "Payment was successful!\n";
        
        echo "Processing payment in your system...\n";
        
    } else {
        echo "Payment status: " . $transaction['status'] . "\n";
    }
} else {
    echo "Verification failed: " . $response['message'] . "\n";
}
