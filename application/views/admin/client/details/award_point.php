<div class="cl-card">
        <div class="cl-sec-head">
            <h1><?= lang('client') . ' ' . lang('points') ?></h1>
            <div class="cl-spacer"></div>
            <span class="cl-money-neg"><strong><?= lang('total') . ' ' . lang('points') ?>:</strong> <?php
                echo display_money($this->client_model->get_client_point_byid($client_details->client_id));
                ?></span>
        </div>
        
        <table class="cl-tbl cl-mini" id="DataTables" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?= lang('reference_no') ?></th>
                <th><?= lang('date') ?></th>
                <th><?= lang('award_point') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $all_award_point = $this->client_model->select_data('tbl_award_points', 'tbl_award_points.*,tbl_invoices.reference_no', NULL, array('tbl_award_points.client_id' => $client_details->client_id), ['tbl_invoices' => 'tbl_award_points.invoices_id = tbl_invoices.invoices_id'], true);
            foreach ($all_award_point as $v_point) {
                ?>
                <tr>
                    <td>
                        <a class="cl-nm" href="<?= base_url('admin/invoice/manage_invoice/invoice_details/' . $v_point['invoices_id']) ?>">
                            <?= html_escape($v_point['reference_no']) ?>
                        </a>
                    </td>
                    <td><?= strftime(config_item('date_format'), strtotime($v_point['date'])) ?></td>
                    <td><?= $v_point['client_award_point'] ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>