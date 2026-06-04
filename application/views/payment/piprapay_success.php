<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>
<h2>Payment Successful</h2>
<p>Thank you! Your payment has been recorded.</p>
<a href="<?php echo site_url('invoices'); ?>" class="btn btn-primary">Back to invoices</a>
