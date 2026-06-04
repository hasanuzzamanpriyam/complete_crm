<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>
<h2>Pay Invoice #<?php echo html_escape($invoice->reference_no); ?></h2>

<?php echo form_open($actionUrl); ?>
    <?php echo form_hidden('invoice_id', $invoice->invoices_id); ?>

    <div class="form-group">
        <label>Gateway</label>
        <select name="gateway_id" required class="form-control">
            <?php foreach ($gateways as $gw): ?>
                <option value="<?php echo $gw['id']; ?>"><?php echo html_escape($gw['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Amount (<?php echo html_escape($invoice->currency ?? 'BDT'); ?>)</label>
        <input type="text" class="form-control" value="<?php echo number_format($total, 2); ?>" readonly>
    </div>

    <button type="submit" class="btn btn-success">Proceed to PipraPay</button>
<?php echo form_close(); ?>
