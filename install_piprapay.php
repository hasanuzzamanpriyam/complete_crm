<?php

$database_config = [
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'tic_crm'
];

echo "=== PipraPay Database Setup ===\n\n";

$mysqli = new mysqli($database_config['hostname'], $database_config['username'], $database_config['password'], $database_config['database']);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error . "\n");
}

echo "✓ Connected to database: " . $database_config['database'] . "\n\n";

echo "=== Step 1: Add PipraPay to online payments table ===\n";

$check_payment = $mysqli->query("SELECT * FROM `tbl_online_payment` WHERE `gateway_name` = 'PipraPay'");

if ($check_payment->num_rows > 0) {
    echo "✓ PipraPay already exists in tbl_online_payment\n\n";
} else {
    $sql = "INSERT INTO `tbl_online_payment` (`online_payment_id`, `gateway_name`, `icon`, `field_1`, `field_2`, `field_3`, `field_4`, `field_5`, `link`, `modal`) VALUES 
    (NULL, 'PipraPay', 'piprapay.png', 'piprapay_api_url', 'piprapay_api_key', 'piprapay_api_secret', 'piprapay_merchant_id', '', 'payment/piprapay', 'Yes')";
    
    if ($mysqli->query($sql)) {
        echo "✓ PipraPay added to tbl_online_payment\n\n";
    } else {
        echo "✗ Error adding PipraPay: " . $mysqli->error . "\n\n";
    }
}

echo "=== Step 2: Add PipraPay column to invoices table ===\n";

$check_column = $mysqli->query("SHOW COLUMNS FROM `tbl_invoices` LIKE 'allow_piprapay'");

if ($check_column->num_rows > 0) {
    echo "✓ Column allow_piprapay already exists in tbl_invoices\n\n";
} else {
    $sql = "ALTER TABLE `tbl_invoices` ADD `allow_piprapay` ENUM('Yes','No') NULL DEFAULT 'Yes' AFTER `allow_tappayment`";
    
    if ($mysqli->query($sql)) {
        echo "✓ Column allow_piprapay added to tbl_invoices\n\n";
    } else {
        echo "✗ Error adding column: " . $mysqli->error . "\n\n";
    }
}

echo "=== Step 3: Add PipraPay configuration items ===\n";

$config_items = [
    'piprapay_enabled',
    'piprapay_api_url',
    'piprapay_api_key',
    'piprapay_api_secret',
    'piprapay_merchant_id',
    'piprapay_webhook_secret',
    'piprapay_test_mode',
    'piprapay_default_gateway'
];

$added_count = 0;
$skipped_count = 0;

foreach ($config_items as $key) {
    $check_config = $mysqli->query("SELECT * FROM `tbl_config` WHERE `config_key` = '$key'");
    
    if ($check_config->num_rows > 0) {
        $skipped_count++;
        echo "  - $key: Already exists (skipped)\n";
    } else {
        $config_data = [
            'piprapay_enabled' => "INSERT INTO `tbl_config` (`config_key`, `value`) VALUES ('piprapay_enabled', 'FALSE')",
            'piprapay_api_url' => "INSERT INTO `tbl_config` (`config_key`, `value`) VALUES ('piprapay_api_url', 'https://payment.yourdomain.com/api/v1')",
            'piprapay_api_key' => "INSERT INTO `tbl_config` (`config_key`, `value`) VALUES ('piprapay_api_key', '')",
            'piprapay_api_secret' => "INSERT INTO `tbl_config` (`config_key`, `value`) VALUES ('piprapay_api_secret', '')",
            'piprapay_merchant_id' => "INSERT INTO `tbl_config` (`config_key`, `value`) VALUES ('piprapay_merchant_id', '')",
            'piprapay_webhook_secret' => "INSERT INTO `tbl_config` (`config_key`, `value`) VALUES ('piprapay_webhook_secret', '')",
            'piprapay_test_mode' => "INSERT INTO `tbl_config` (`config_key`, `value`) VALUES ('piprapay_test_mode', 'TRUE')",
            'piprapay_default_gateway' => "INSERT INTO `tbl_config` (`config_key`, `value`) VALUES ('piprapay_default_gateway', 'bkash')"
        ];
        
        if ($mysqli->query($config_data[$key])) {
            $added_count++;
            echo "  + $key: Added successfully\n";
        } else {
            echo "  ✗ $key: Error - " . $mysqli->error . "\n";
        }
    }
}

echo "\n✓ Configuration items: $added_count added, $skipped_count skipped\n\n";

echo "=== Step 4: Update version ===\n";

$sql = "UPDATE `tbl_config` SET `value` = '6.1.0' WHERE `tbl_config`.`config_key` = 'version'";

if ($mysqli->query($sql)) {
    echo "✓ Version updated to 6.1.0\n\n";
} else {
    echo "✗ Error updating version: " . $mysqli->error . "\n\n";
}

$mysqli->close();

echo "=== Database Setup Complete! ===\n\n";
echo "Next steps:\n";
echo "1. Login to your CRM admin panel\n";
echo "2. Go to Settings > Payment Settings\n";
echo "3. Configure PipraPay with your credentials\n";
echo "4. Install PipraPay server from https://piprapay.com\n";
echo "5. Set up webhook URL in PipraPay\n\n";
echo "For detailed instructions, read QUICKSTART.md\n\n";
