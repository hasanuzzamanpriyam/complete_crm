<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h2>Pay Invoice #<?php echo html_escape($invoice->code); ?></h2>

<?php echo form_open($actionUrl); ?>
    <?php echo form_hidden('invoice_id', $invoice->id); ?>

    <div>
        <label>Gateway</label>
        <select name="gateway_id" required>
            <?php foreach ($gateways as $gw): ?>
                <option value="<?php echo $gw['id']; ?>"><?php echo html_escape($gw['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label>Amount</label>
        <input type="text" name="amount" value="<?php echo number_format($invoice->total, 2); ?>" readonly>
    </div>

    <button type="submit">Proceed to Piprapay</button>
<?php echo form_close(); ?>
