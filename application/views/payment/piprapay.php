<?php echo message_box('success') ?>
<?php echo message_box('error') ?>
<?php
/**
 * FIX (Bug #6 — XSS): All user-controlled data is now passed through
 * htmlspecialchars() before being echoed into HTML/attributes.
 */
$allow_customer_edit_amount = config_item('allow_customer_edit_amount');
$cur = $this->invoice_model->check_by(['code' => $invoice_info->currency], 'tbl_currencies');

// Pre-escape everything used in HTML output
$safe_reference  = htmlspecialchars($invoice_info->reference_no ?? '', ENT_QUOTES, 'UTF-8');
$safe_invoice_id = (int) $invoice_id;           // numeric — no XSS risk
$safe_invoice_due = (float) $invoice_due;        // numeric
$safe_cur_symbol  = htmlspecialchars($cur->symbol ?? '', ENT_QUOTES, 'UTF-8');
?>
<div class="panel panel-custom">
    <div class="panel-heading">
        <button type="button" class="close" data-dismiss="modal">
            <span aria-hidden="true">&times;</span>
            <span class="sr-only">Close</span>
        </button>
        <h4 class="modal-title">
            Paying
            <strong>
                <?php if (!empty($allow_customer_edit_amount) && $allow_customer_edit_amount == 'No'): ?>
                    <?= display_money($safe_invoice_due, $cur->symbol ?? '') ?>
                <?php endif; ?>
            </strong>
            for Invoice # <?= $safe_reference ?> via PipraPay
        </h4>
    </div>

    <div class="panel-body">
        <?php
        $attributes = [
            'id'                   => 'piprapay_form',
            'name'                 => 'piprapay',
            'data-parsley-validate'=> '',
            'novalidate'           => '',
            'class'                => 'form-horizontal',
        ];
        echo form_open(site_url('payment/piprapay/purchase'), $attributes);
        ?>

        <?php if (!empty($allow_customer_edit_amount) && $allow_customer_edit_amount == 'No'): ?>
            <input name="amount" value="<?= $safe_invoice_due ?>" type="hidden">
        <?php endif; ?>

        <input type="hidden" name="invoice_id" value="<?= $safe_invoice_id ?>">

        <div class="form-group">
            <label class="col-lg-4 control-label">
                <?= lang('amount') ?> (<?= $safe_cur_symbol ?>)
            </label>
            <div class="col-lg-5">
                <?php if (!empty($allow_customer_edit_amount) && $allow_customer_edit_amount == 'Yes'): ?>
                    <input type="number"
                           step="0.01"
                           min="0.01"
                           max="<?= $safe_invoice_due ?>"
                           required
                           name="amount"
                           id="pp_amount"
                           class="form-control"
                           value="<?= $safe_invoice_due ?>">
                <?php else: ?>
                    <input type="text"
                           class="form-control"
                           id="pp_amount"
                           value="<?= display_money($safe_invoice_due) ?>"
                           readonly>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-4 control-label"><?= lang('payment_method') ?></label>
            <div class="col-lg-5">
                <select name="gateway" class="form-control" required id="piprapay_gateway">
                    <option value=""><?= lang('select_gateway') ?></option>
                    <?php if (!empty($allowed_gateways)): ?>
                        <?php foreach ($allowed_gateways as $gateway): ?>
                            <?php
                                // FIX (Bug #6): all gateway attributes escaped
                                if (empty($gateway['code']) || empty($gateway['name']) || empty($gateway['active'])) {
                                    continue;
                                }
                                $g_code      = htmlspecialchars($gateway['code'],      ENT_QUOTES, 'UTF-8');
                                $g_name      = htmlspecialchars($gateway['name'],      ENT_QUOTES, 'UTF-8');
                                $g_icon      = htmlspecialchars($gateway['icon'] ?? '', ENT_QUOTES, 'UTF-8');
                                $g_min       = (float) ($gateway['min_amount'] ?? 0);
                                $g_max       = (float) ($gateway['max_amount'] ?? PHP_FLOAT_MAX);
                                $g_currencies= htmlspecialchars(
                                                  isset($gateway['currencies']) ? implode(',', $gateway['currencies']) : '',
                                                  ENT_QUOTES, 'UTF-8');
                                $g_region    = htmlspecialchars($gateway['region'] ?? 'global', ENT_QUOTES, 'UTF-8');
                                $selected_attr = (isset($selected_gateway) && $selected_gateway === $gateway['code']) ? 'selected' : '';
                            ?>
                            <option value="<?= $g_code ?>"
                                    data-icon="<?= $g_icon ?>"
                                    data-currencies="<?= $g_currencies ?>"
                                    data-min-amount="<?= $g_min ?>"
                                    data-max-amount="<?= $g_max ?>"
                                    <?= $selected_attr ?>>
                                <?= $g_name ?>
                                <?php if ($g_region !== 'global'): ?>
                                    <small class="text-muted">(<?= ucfirst($g_region) ?>)</small>
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
    $(document).ready(function () {
        $('#piprapay_form').on('submit', function (e) {
            var amount  = parseFloat($('#pp_amount').val());
            var gateway = $('#piprapay_gateway').val();
            var maxAmount = parseFloat($('#piprapay_gateway option:selected').data('max-amount'));
            var minAmount = parseFloat($('#piprapay_gateway option:selected').data('min-amount'));

            if (!gateway) {
                e.preventDefault();
                alert('Please select a payment gateway');
                return false;
            }

            if (isNaN(amount) || amount <= 0) {
                e.preventDefault();
                alert('Please enter a valid amount');
                return false;
            }

            if (minAmount > 0 && amount < minAmount) {
                e.preventDefault();
                alert('Minimum amount for this gateway is ' + minAmount);
                return false;
            }

            if (maxAmount > 0 && amount > maxAmount) {
                e.preventDefault();
                alert('Maximum amount for this gateway is ' + maxAmount);
                return false;
            }

            return true;
        });
    });
</script>
