<?php echo message_box('success') ?>
<?php echo message_box('error') ?>
<?php
$allow_customer_edit_amount = config_item('allow_customer_edit_amount');
$cur = $this->invoice_model->check_by(array('code' => $invoice_info->currency), 'tbl_currencies');
?>
<div class="panel panel-custom">
    <div class="panel-heading">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                    class="sr-only">Close</span></button>
        <h4 class="modal-title">Paying <strong>
                <?php if (!empty($allow_customer_edit_amount) && $allow_customer_edit_amount == 'No') { ?>
                    <?= display_money($invoice_due, $cur->symbol); ?>
                <?php } ?>
            </strong> for Invoice # <?= $invoice_info->reference_no ?> via PipraPay</h4>
    </div>
    <div class="panel-body">
        <?php
        $attributes = array('id' => 'piprapay_form', 'name' => 'piprapay', 'data-parsley-validate' => "", 'novalidate' => "", 'class' => 'form-horizontal');
        echo form_open(site_url('payment/piprapay/purchase'), $attributes);
        ?>
        <?php if (!empty($allow_customer_edit_amount) && $allow_customer_edit_amount == 'No') { ?>
            <input name="amount" value="<?= ($invoice_due) ?>" type="hidden">
        <?php } ?>

        <input type="hidden" name="invoice_id" value="<?= $invoice_id ?>"/>

        <div class="form-group">
            <label class="col-lg-4 control-label"><?= lang('amount') ?> ( <?= $cur->symbol ?>) </label>
            <div class="col-lg-5">
                <?php if (!empty($allow_customer_edit_amount) && $allow_customer_edit_amount == 'Yes') { ?>
                    <input type="text" required name="amount" id="pp_amount" data-parsley-type="number"
                           max="<?= $invoice_due ?>" class="form-control"
                           value="<?= ($invoice_due) ?>">
                <?php } else { ?>
                    <input type="text" class="form-control" id="pp_amount" value="<?= display_money($invoice_due) ?>"
                           readonly>
                <?php } ?>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-4 control-label"><?= lang('payment_method') ?></label>
            <div class="col-lg-5">
                <select name="gateway" class="form-control" required id="piprapay_gateway">
                    <option value=""><?= lang('select_gateway') ?></option>
                    <?php if (!empty($allowed_gateways)) { ?>
                        <?php foreach ($allowed_gateways as $gateway) { ?>
                            <?php if (in_array($gateway['code'], array('bkash', 'nagad', 'stripe'))) { ?>
                                <option value="<?= $gateway['code'] ?>" <?= isset($selected_gateway) && $selected_gateway == $gateway['code'] ? 'selected' : '' ?>>
                                    <?= $gateway['name'] ?>
                                </option>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="modal-footer">
            <a href="#" class="btn btn-default" data-dismiss="modal"><?= lang('close') ?></a>
            <input type="submit" value="<?= lang('submit_payment') ?>" class="btn btn-success"/>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#piprapay_form').on('submit', function(e) {
            var amount = $('#pp_amount').val();
            var gateway = $('#piprapay_gateway').val();

            if (!gateway) {
                e.preventDefault();
                alert('Please select a payment gateway');
                return false;
            }

            if (amount <= 0) {
                e.preventDefault();
                alert('Please enter a valid amount');
                return false;
            }

            return true;
        });
    });
</script>
