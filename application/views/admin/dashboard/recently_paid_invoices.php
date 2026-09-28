<section class="panel panel-custom menu">
    <header class="panel-heading">
        <h3 class="panel-title"><?= lang('recently_paid_invoices') ?><span class="sub">Last 5 payments</span></h3>
    </header>
    <div class="panel-body inv-slim-scroll">
        <table class="table m-b-none text-sm">
            <thead>
            <tr>
                <th><?= lang('invoice') ?></th>
                <th><?= lang('amount') ?></th>
                <th><?= lang('method') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (!empty($recently_paid)) {
                foreach ($recently_paid as $v_paid) {
                    ?>
                    <tr>
                        <td><a href="<?= base_url() ?>admin/invoice/manage_invoice/payments_details/<?php echo $v_paid->invoices_id; ?>"><b><?= !empty($v_paid->reference_no) ? $v_paid->reference_no : $v_paid->trans_id ?></b></a></td>
                        <td style="font-weight:700"><?= display_money($v_paid->amount, $v_paid->currency) ?></td>
                        <td><span class="label label-<?= $v_paid->label ?>"><?= $v_paid->method_name ?></span></td>
                    </tr>
                    <?php
                }
            }
            ?>
            </tbody>
        </table>
    </div>
    <div class="panel-footer">
        <small><?= lang('total_receipts') ?>: <strong id="total_receipts"></strong></small>
    </div>
</section>
