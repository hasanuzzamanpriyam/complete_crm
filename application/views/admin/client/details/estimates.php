<div class="cl-card">
        <div class="cl-sec-head">
            <h1><?= lang('estimates') ?></h1>
            <div class="cl-spacer"></div>
            <?php
            $es_created = can_action('14', 'created');
            $es_edited = can_action('14', 'edited');
            if (!empty($es_created) || !empty($es_edited)) {
                ?>
                <a href="<?= base_url() ?>admin/estimates/create/edit_estimates/c_<?= $client_details->client_id ?>"
                   class="cl-btn cl-btn-primary"><?= lang('new_estimate') ?></a>
            <?php } ?>
            <a data-toggle="modal" data-target="#myModal"
               href="<?= base_url() ?>admin/invoice/zipped/estimate/<?= $client_details->client_id ?>"
               class="cl-btn cl-btn-ghost"><?= lang('zip_estimate') ?></a>
        </div>

        <div class="tbl-toolbar">
            <div class="search-in"><span>🔍</span><input type="text"></div>
            <div style="flex:1"></div>
            <button class="cl-btn cl-btn-ghost">⌗ <?= lang('filter') ?></button>
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
            $total_estimate = 0;
            $client_estimates = get_result('tbl_estimates', array('client_id' => $client_details->client_id));
            if (!empty($client_estimates)) {
                foreach ($client_estimates as $key => $estimate) {
                    $total_estimate += $this->estimates_model->estimate_calculation('estimate_amount', $estimate->estimates_id);
                    ?>
                    <tr>
                        <td>
                            <a class="cl-nm"
                               href="<?= base_url() ?>admin/estimates/create/estimates_details//<?= $estimate->estimates_id ?>">
                                <?= html_escape($estimate->reference_no) ?>
                            </a>
                        </td>
                        <td><?= strftime(config_item('date_format'), strtotime($estimate->date_saved)); ?></td>
                        <td><?= strftime(config_item('date_format'), strtotime($estimate->due_date)); ?></td>
                        <td class="cl-num">
                            <?= display_money($this->estimates_model->estimate_calculation('estimate_amount', $estimate->estimates_id), client_currency($client_details->client_id)); ?>
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>
            </tbody>
        </table>
        
        <div class="cl-panel-foot">
            <span><?= lang('estimate') . ' ' . lang('amount') ?>: <strong class="cl-tag cl-tag-mint"><?= display_money($total_estimate, client_currency($client_details->client_id)) ?></strong></span>
        </div>
    </div>