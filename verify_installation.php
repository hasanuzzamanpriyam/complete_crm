<?php
$mysqli = new mysqli('localhost', 'root', '', 'tic_crm');
$result = $mysqli->query("SELECT * FROM tbl_config WHERE config_key LIKE 'piprapay%'");
echo "=== PipraPay Configurations in Database ===\n\n";
while($row = $result->fetch_assoc()) {
    echo $row['config_key'] . " = " . $row['value'] . "\n";
}

echo "\n=== PipraPay in Online Payments Table ===\n";
$result2 = $mysqli->query("SELECT * FROM tbl_online_payment WHERE gateway_name = 'PipraPay'");
if ($result2->num_rows > 0) {
    $payment = $result2->fetch_assoc();
    echo "✓ Gateway: " . $payment['gateway_name'] . "\n";
    echo "✓ Link: " . $payment['link'] . "\n";
    echo "✓ Modal: " . $payment['modal'] . "\n";
} else {
    echo "✗ PipraPay not found\n";
}

echo "\n=== PipraPay Column in Invoices Table ===\n";
$result3 = $mysqli->query("SHOW COLUMNS FROM tbl_invoices LIKE 'allow_piprapay'");
if ($result3->num_rows > 0) {
    echo "✓ Column allow_piprapay exists\n";
} else {
    echo "✗ Column allow_piprapay not found\n";
}

$mysqli->close();
