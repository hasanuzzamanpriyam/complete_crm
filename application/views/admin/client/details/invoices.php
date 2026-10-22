<?php $this->load->view('admin/client/_client_assets'); ?>
<div class="cl-root cl-tbl">
    <div class="cl-card">
        <div class="cl-sec-head">
            <h1><?= lang('invoices') ?></h1>
            <div class="cl-spacer"></div>
            <?php
            $in_created = can_action('13', 'created');
            $in_edited = can_action('13', 'edited');
            if (!empty($in_created) || !empty($in_edited)) {
                ?>
                <a href="<?= base_url() ?>admin/invoice/createinvoice/create_invoice/c_<?= $client_details->client_id ?>"
                   class="cl-btn cl-btn-primary"><?= lang('new_invoice') ?></a>
            <?php } ?>
            <a data-toggle="modal" data-target="#myModal"
               href="<?= base_url() ?>admin/invoice/zipped/invoice/<?= $client_details->client_id ?>"
               class="cl-btn cl-btn-ghost"><?= lang('zip_invoice') ?></a>
        </div>
        
        <table class="cl-tbl cl-mini" id="datatable_action" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?= lang('reference_no') ?></th>
                <th><?= lang('date_issued') ?></th>
                <th><?= lang('due_date') ?></th>
                <th class="cl-num"><?= lang('amount') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $total_invoice = 0;
            $client_invoices = get_result('tbl_invoices', array('client_id' => $client_details->client_id));
            if (!empty($client_invoices)) {
                foreach ($client_invoices as $key => $invoice) {
                    $total_invoice += $this->invoice_model->invoice_payable($invoice->invoices_id);
                    ?>
                    <tr>
                        <td>
                            <a class="cl-nm"
                               href="<?= base_url() ?>admin/invoice/manage_invoice/invoice_details/<?= $invoice->invoices_id ?>">
                                <?= html_escape($invoice->reference_no) ?>
                            </a>
                        </td>
                        <td><?= strftime(config_item('date_format'), strtotime($invoice->date_saved)); ?></td>
                        <td><?= strftime(config_item('date_format'), strtotime($invoice->due_date)); ?></td>
                        <td class="cl-num">
                            <?= display_money($this->invoice_model->invoice_payable($invoice->invoices_id), client_currency($client_details->client_id)); ?>
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>
            </tbody>
        </table>
        
        <div class="cl-panel-foot">
            <span><?= lang('invoice') . ' ' . lang('amount') ?>: <strong class="cl-tag cl-tag-mint"><?= display_money($total_invoice, client_currency($client_details->client_id)) ?></strong></span>
        </div>
    </div>
</div>