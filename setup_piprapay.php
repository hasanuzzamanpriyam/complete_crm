<?php

$files_to_check = [
    'application/config/piprapay.php',
    'application/libraries/Piprapay_core.php',
    'application/libraries/gateways/Piprapay_gateway.php',
    'application/controllers/payment/Piprapay.php',
    'application/views/payment/piprapay.php',
    'application/migrations/610_version_610.php',
];

echo "=== PipraPay Integration Setup Check ===\n\n";
echo "Checking required files...\n\n";

$all_exists = true;

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✓ " . $file . "\n";
    } else {
        echo "✗ " . $file . " - MISSING\n";
        $all_exists = false;
    }
}

echo "\n=== PipraPay SDK Check ===\n\n";

$sdk_files = [
    'piprapay-sdk/src/PipraPay/PipraPayClient.php',
    'piprapay-sdk/src/PipraPay/PaymentRequest.php',
    'piprapay-sdk/src/PipraPay/Transaction.php',
    'piprapay-sdk/composer.json',
    'piprapay-sdk/README.md',
    'piprapay-sdk/examples/basic-payment.php',
];

foreach ($sdk_files as $file) {
    if (file_exists($file)) {
        echo "✓ " . $file . "\n";
    } else {
        echo "✗ " . $file . " - MISSING\n";
        $all_exists = false;
    }
}

echo "\n";

if ($all_exists) {
    echo "SUCCESS: All required files are present!\n\n";
    echo "Next steps:\n";
    echo "1. Run the migration: php index.php migration\n";
    echo "2. Configure PipraPay in Admin > Settings > Payment Settings\n";
    echo "3. Install PipraPay from https://piprapay.com\n";
    echo "4. Set webhook URL in PipraPay\n\n";
} else {
    echo "ERROR: Some files are missing. Please check the implementation.\n\n";
    exit(1);
}
