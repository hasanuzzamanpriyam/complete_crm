<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/tic_crm/test_preview/test_preview_offer");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 1);
$response = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

echo "HTTP Code: " . $info['http_code'] . "\n";
echo "Response Body:\n" . $response . "\n";
