<?php $this->load->view('admin/client/_client_assets'); ?>
<div class="cl-root cl-tbl">
    <div class="cl-card">
        <div class="cl-sec-head">
            <h1><?= lang('all_proposals') ?></h1>
            <div class="cl-spacer"></div>
            <?php
            $prop_created = can_action('140', 'created');
            $prop_edited = can_action('140', 'edited');
            if (!empty($prop_created) || !empty($prop_edited)) {
                ?>
                <a href="<?= base_url() ?>admin/proposals/index/client/<?= $client_details->client_id ?>"
                   class="cl-btn cl-btn-primary"><?= lang('create_proposal') ?></a>
            <?php } ?>
            <a data-toggle="modal" data-target="#myModal"
               href="<?= base_url() ?>admin/invoice/zipped/proposal/<?= $client_details->client_id ?>"
               class="cl-btn cl-btn-ghost"><?= lang('zip_proposal') ?></a>
        </div>
        
        <table class="cl-tbl cl-mini" id="datatable_action" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?= lang('proposal') ?> #</th>
                <th><?= lang('proposal_date') ?></th>
                <th><?= lang('expire_date') ?></th>
                <th><?= lang('status') ?></th>
                <?php
                $edited = can_action('140', 'edited');
                $deleted = can_action('140', 'deleted');
                if (!empty($edited) || !empty($deleted)) {
                    ?>
                    <th class="cl-acts"><?= lang('action') ?></th>
                <?php } ?>
            </tr>
            </thead>
            <tbody>
            <?php
            $all_proposals_info = get_order_by('tbl_proposals', array('module' => 'client', 'module_id' => $client_details->client_id), 'proposals_id');
            
            if (!empty($all_proposals_info)) {
                foreach ($all_proposals_info as $v_proposals) {
                    $can_edit = $this->client_model->can_action('tbl_proposals', 'edit', array('proposals_id' => $v_proposals->proposals_id));
                    $can_delete = $this->client_model->can_action('tbl_proposals', 'delete', array('proposals_id' => $v_proposals->proposals_id));
                    
                    if ($v_proposals->status == 'pending') {
                        $label_class = "cl-tag-info";
                    } elseif ($v_proposals->status == 'accepted') {
                        $label_class = "cl-tag-success";
                    } else {
                        $label_class = "cl-tag-danger";
                    }
                    ?>
                    <tr>
                        <td>
                            <a class="cl-nm"
                               href="<?= base_url() ?>admin/proposals/index/proposals_details/<?= $v_proposals->proposals_id ?>">
                                <?= html_escape($v_proposals->reference_no) ?>
                            </a>
                            <?php if ($v_proposals->convert == 'Yes') {
                                if ($v_proposals->convert_module == 'invoice') {
                                    $c_url = base_url() . 'admin/invoice/manage_invoice/invoice_details/' . $v_proposals->convert_module_id;
                                    $text = lang('invoiced');
                                } else {
                                    $text = lang('estimated');
                                    $c_url = base_url() . 'admin/estimates/create/estimates_details/' . $v_proposals->convert_module_id;
                                }
                                if (!empty($c_url)) { ?>
                                    <div class="cl-hint">
                                        <a class="cl-money-pos" href="<?= $c_url ?>"><?= $text ?></a>
                                    </div>
                                <?php }
                            } ?>
                        </td>
                        <td><?= strftime(config_item('date_format'), strtotime($v_proposals->proposal_date)) ?></td>
                        <td>
                            <?= strftime(config_item('date_format'), strtotime($v_proposals->due_date)) ?>
                            <?php
                            if (strtotime($v_proposals->due_date) < strtotime(date('Y-m-d')) && $v_proposals->status == 'pending' || strtotime($v_proposals->due_date) < strtotime(date('Y-m-d')) && $v_proposals->status == ('draft')) { ?>
                                <span class="cl-tag cl-tag-danger"><?= lang('expired') ?></span>
                            <?php }
                            ?>
                        </td>
                        <td><span class="cl-tag <?= $label_class ?>"><?= lang($v_proposals->status) ?></span></td>
                        <?php if (!empty($edited) || !empty($deleted)) { ?>
                            <td class="cl-acts">
                                <?php if (!empty($can_edit) && !empty($edited)) { ?>
                                    <?= btn_edit('admin/proposals/index/edit_proposals/' . $v_proposals->proposals_id) ?>
                                <?php } ?>
                                <?php if (!empty($can_delete) && !empty($deleted)) { ?>
                                    <?= btn_delete('admin/proposals/delete/delete_proposals/' . $v_proposals->proposals_id) ?>
                                <?php } ?>
                                <?php if (!empty($can_edit) && !empty($edited)) { ?>
                                    <div class="cl-input-group">
                                        <button class="cl-btn cl-btn-ghost cl-btn-sm" data-toggle="dropdown">
                                            <?= lang('change_status') ?> <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu animated zoomIn">
                                            <li><a href="<?= base_url() ?>admin/proposals/index/email_proposals/<?= $v_proposals->proposals_id ?>"><?= lang('send_email') ?></a></li>
                                            <li><a href="<?= base_url() ?>admin/proposals/index/proposals_details/<?= $v_proposals->proposals_id ?>"><?= lang('view_details') ?></a></li>
                                            <li><a href="<?= base_url() ?>admin/proposals/index/proposals_history/<?= $v_proposals->proposals_id ?>"><?= lang('history') ?></a></li>
                                            <li><a href="<?= base_url() ?>admin/proposals/change_status/declined/<?= $v_proposals->proposals_id ?>"><?= lang('declined') ?></a></li>
                                            <li><a href="<?= base_url() ?>admin/proposals/change_status/accepted/<?= $v_proposals->proposals_id ?>"><?= lang('accepted') ?></a></li>
                                        </ul>
                                    </div>
                                <?php } ?>
                            </td>
                        <?php } ?>
                    </tr>
                    <?php
                }
            }
            ?>
            </tbody>
        </table>
    </div>
</div>