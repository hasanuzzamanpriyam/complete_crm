<?php
// fix_db.php
define('ENVIRONMENT', 'production');
define('BASEPATH', dirname(__FILE__) . '/system/');
define('APPPATH', dirname(__FILE__) . '/application/');
define('VIEWPATH', dirname(__FILE__) . '/application/views/');
define('FCPATH', dirname(__FILE__) . '/');
define('SELF', 'fix_db.php');

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/fix_db.php';
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
$_SERVER['REQUEST_URI'] = '/fix_db.php';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_METHOD'] = 'GET';

require_once BASEPATH . 'core/CodeIgniter.php';

$CI =& get_instance();

echo "<h2>Database Cleanup for PipraPay</h2>";

// Clean up duplicate PipraPay rows in tbl_online_payment
$query = $CI->db->where('gateway_name', 'PipraPay')->get('tbl_online_payment');
$rows = $query->result_array();

if (count($rows) > 1) {
    echo "<p style='color: orange;'>Found " . count($rows) . " duplicate PipraPay gateway entries.</p>";
    $keep_id = $rows[0]['online_payment_id'];
    echo "Keeping online_payment_id: <strong>$keep_id</strong><br>";
    
    foreach ($rows as $index => $row) {
        if ($index > 0) {
            $CI->db->where('online_payment_id', $row['online_payment_id'])->delete('tbl_online_payment');
            echo "Deleted duplicate entry with ID: " . $row['online_payment_id'] . "<br>";
        }
    }
} else {
    echo "<p style='color: green;'>No duplicate PipraPay gateway entries found in tbl_online_payment.</p>";
}

// Ensure modal is 'No' and link is set correctly
$CI->db->where('gateway_name', 'PipraPay')->update('tbl_online_payment', [
    'modal' => 'No',
    'link' => 'payment/piprapay'
]);
echo "<p style='color: green;'>✓ Set PipraPay gateway modal to 'No' and link to 'payment/piprapay'.</p>";

echo "<h3>Cleanup finished! Please delete this file (fix_db.php) from your server now.</h3>";
