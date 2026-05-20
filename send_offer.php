<?php
// Load CodeIgniter
define('BASEPATH', TRUE);
$_SERVER['REMOTE_ADDR'] = "127.0.0.1";
$_SERVER['SERVER_ADDR'] = "127.0.0.1";

// Bootstrap CI
require_once dirname(__FILE__) . '/index.php';

// Now we can use CI functions
$CI =& get_instance();

// Load recruitment model
$CI->load->model('recruitment_model');

// Get offer details
$offer_id = 4;
$offer = $CI->recruitment_model->get_offer_by_id($offer_id);

if (empty($offer)) {
    echo "Offer not found\n";
    exit;
}

echo "Offer found for: " . $offer->candidate_name . " (" . $offer->candidate_email . ")\n";

// Get designation
$designation = '-';
if (!empty($offer->designations_id)) {
    $design_info = $CI->db->where('designations_id', $offer->designations_id)->get('tbl_designations')->row();
    if ($design_info) $designation = $design_info->designations;
}

$replacements = [
    '{CANDIDATE_NAME}' => $offer->candidate_name,
    '{JOB_TITLE}' => $offer->job_title,
    '{DESIGNATION}' => $designation,
    '{SALARY}' => $offer->salary_offered ?: 'As discussed',
    '{JOINING_DATE}' => $offer->joining_date ? strftime(config_item('date_format'), strtotime($offer->joining_date)) : 'To be confirmed',
    '{EMPLOYMENT_TYPE}' => lang($offer->employment_type ?? 'full_time'),
    '{COMPANY_NAME}' => config_item('company_name'),
    '{ADDITIONAL_TERMS}' => $offer->additional_terms ?: '',
    '{SITE_NAME}' => config_item('company_name')
];

$subject = $offer->offer_subject;
$body = $offer->offer_body;

foreach ($replacements as $key => $val) {
    $subject = str_replace($key, $val, $subject);
    $body = str_replace($key, $val, $body);
}

echo "Subject: " . $subject . "\n";
echo "Body length: " . strlen($body) . "\n";

// Update the offer in database with replaced placeholders
$CI->db->where('offer_id', $offer_id);
$CI->db->update('tbl_offer_letters', [
    'offer_subject' => $subject,
    'offer_body' => $body
]);

$params = [
    'recipient' => $offer->candidate_email,
    'subject' => $subject,
    'message' => $body,
    'resourceed_file' => ''
];

$sent = $CI->recruitment_model->send_email($params);
if ($sent) {
    $CI->recruitment_model->update_offer_status($offer_id, 'sent');
    echo "SUCCESS: Offer email sent to " . $offer->candidate_email . "\n";
} else {
    echo "FAILED: Could not send email to " . $offer->candidate_email . "\n";
}
