<?php
define('BASEPATH', 'TRUE');
include 'index.php';
$CI =& get_instance();
$CI->load->database();

echo "--- DOMAINS ---\n";
$query = $CI->db->like('domain_name', 'myservice.io')->get('tbldomains');
print_r($query->result_array());

echo "\n--- HOSTING ---\n";
$query2 = $CI->db->like('title', 'Production App')->get('tblserver_hostings');
print_r($query2->result_array());
