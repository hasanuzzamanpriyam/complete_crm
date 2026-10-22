<?php $this->load->view('admin/client/_client_assets'); ?>
<div class="cl-root cl-tbl">
    <div class="cl-card">
        <div class="cl-sec-head">
            <h1><?= lang('transactions') ?></h1>
        </div>
        
        <table class="cl-tbl cl-mini" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?= lang('date') ?></th>
                <th><?= lang('account') ?></th>
                <th><?= lang('type') ?></th>
                <th class="cl-num"><?= lang('amount') ?></th>
                <th class="cl-acts"><?= lang('action') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $total_income = 0;
            $total_expense = 0;
            $client_transactions = get_order_by('tbl_transactions', array('paid_by' => $client_details->client_id), 'transactions_id');
            if (!empty($client_transactions)) : foreach ($client_transactions as $v_transactions) :
                $account_info = $this->client_model->check_by(array('account_id' => $v_transactions->account_id), 'tbl_accounts');
                ?>
                <tr>
                    <td><?= strftime(config_item('date_format'), strtotime($v_transactions->date)); ?></td>
                    <td><?= html_escape($account_info->account_name) ?></td>
                    <td><span class="cl-tag <?= $v_transactions->type == 'Income' ? 'cl-tag-mint' : 'cl-tag-amber' ?>"><?= $v_transactions->type ?></span></td>
                    <td class="cl-num"><?= display_money($v_transactions->amount, default_currency()) ?></td>
                    <td class="cl-acts">
                        <?php
                        if ($v_transactions->type == 'Income') {
                            $total_income += $v_transactions->amount;
                            ?>
                            <?= btn_edit('admin/transactions/create_deposit/' . $v_transactions->transactions_id) ?>
                            <?= btn_delete('admin/transactions/delete_deposit/' . $v_transactions->transactions_id) ?>
                            <?php
                        } else {
                            $total_expense += $v_transactions->amount;
                            ?>
                            <?= btn_edit('admin/transactions/create_expense/' . $v_transactions->transactions_id) ?>
                            <?= btn_delete('admin/transactions/delete_expense/' . $v_transactions->transactions_id) ?>
                        <?php } ?>
                    </td>
                </tr>
            <?php
            endforeach;
            endif;
            ?>
            </tbody>
        </table>
        
        <div class="cl-panel-foot">
            <small><strong><?= lang('total_income') ?>:</strong> <strong class="cl-tag cl-tag-mint"><?= display_money($total_income, default_currency()) ?></strong></small>
            <small class="cl-money-neg"><strong><?= lang('total_expense') ?>:</strong> <strong><?= display_money($total_expense, default_currency()) ?></strong></small>
        </div>
    </div>
</div>