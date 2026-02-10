<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Email Configuration
|--------------------------------------------------------------------------
|
| The following are the settings for sending emails from your application.
| Adjust them based on your mail server.
|
*/

$config['protocol'] = 'sendmail'; // You can change it to 'mail', 'sendmail', or 'smtp' as needed
$config['smtp_host'] = 'mail.tic.com.bd'; // Your SMTP server address
$config['smtp_user'] = 'hello@tic.com.bd'; // Your SMTP username
$config['smtp_pass'] = 'Forid@@2024$$'; // Your SMTP password
$config['smtp_port'] = 465; // Usually 587 for TLS or 465 for SSL
$config['smtp_crypto'] = 'tls'; // Change to 'ssl' if using SSL
$config['mailtype'] = 'html'; // Can be 'text' if sending plain text emails
$config['charset'] = 'utf-8';
$config['wordwrap'] = TRUE;
$config['newline'] = "\r\n"; // Important for SMTP, use "\r\n" for line breaks

/* End of file email.php */
/* Location: ./application/config/email.php */
