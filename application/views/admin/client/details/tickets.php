<?php $this->load->view('admin/client/_client_assets'); ?>
<div class="cl-root cl-tbl">
    <div class="cl-card">
        <div class="cl-sec-head">
            <h1><?= lang('tickets') ?></h1>
            <div class="cl-spacer"></div>
            <?php
            $edited = can_action('58', 'edited');
            if (!empty($edited)) {
                ?>
                <a href="<?= base_url() ?>admin/tickets/create/edit_tickets/<?= $primary_contact ?>"
                   class="cl-btn cl-btn-primary cl-btn-sm"><?= lang('new_ticket') ?></a>
            <?php } ?>
        </div>
        
        <table class="cl-tbl cl-mini" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?= lang('subject') ?></th>
                <th class="cl-num"><?= lang('date') ?></th>
                <?php if ($this->session->userdata('user_type') == '1') { ?>
                    <th><?= lang('reporter') ?></th>
                <?php } ?>
                <th><?= lang('status') ?></th>
                <th class="cl-acts"><?= lang('action') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $all_tickets_info = $this->client_model->getClientData('tbl_tickets', $client_details->client_id);
            if (!empty($all_tickets_info)) {
                foreach ($all_tickets_info as $v_tickets_info) {
                    if ($v_tickets_info->reporter != 0) {
                        $profile_info = $this->db->where(array('user_id' => $v_tickets_info->reporter))->get('tbl_account_details')->row();
                        if (!empty($profile_info->company) && $profile_info->company == $client_details->client_id) {
                            if ($v_tickets_info->status == 'open') {
                                $s_label = 'cl-tag-danger';
                            } elseif ($v_tickets_info->status == 'closed') {
                                $s_label = 'cl-tag-success';
                            } else {
                                $s_label = 'cl-tag-slate';
                            }
                            ?>
                            <tr>
                                <td>
                                    <a class="cl-nm"
                                       href="<?= base_url() ?>admin/tickets/tickets_details/<?= $v_tickets_info->tickets_id ?>">
                                        <?= html_escape($v_tickets_info->subject) ?>
                                    </a>
                                </td>
                                <td class="cl-num"><?= strftime(config_item('date_format'), strtotime($v_tickets_info->created)) ?></td>
                                <?php if ($this->session->userdata('user_type') == '1') { ?>
                                    <td>
                                        <a class="cl-cell-name" href="<?= base_url() ?>admin/user/user_details/<?= $profile_info->user_id ?>">
                                            <?php if (!empty($profile_info)) { ?>
                                                <div class="cl-avatar cl-av-mint">
                                                    <?php
                                                    $words = preg_split('/\s+/', trim($profile_info->fullname));
                                                    $initials = '';
                                                    foreach ($words as $w) {
                                                        if ($w !== '') $initials .= mb_substr($w, 0, 1);
                                                    }
                                                    echo strtoupper(mb_substr($initials, 0, 2)) ?: 'CL';
                                                    ?>
                                                </div>
                                            <?php } ?>
                                            <span class="cl-nm"><?= html_escape($profile_info->fullname) ?></span>
                                        </a>
                                    </td>
                                <?php } ?>
                                <?php
                                if ($v_tickets_info->status == 'in_progress') {
                                    $status = 'In Progress';
                                } else {
                                    $status = $v_tickets_info->status;
                                }
                                ?>
                                <td><span class="cl-tag <?= $s_label ?>"><?= ucfirst($status) ?></span></td>
                                <td class="cl-acts">
                                    <?= btn_edit('admin/tickets/create/edit_tickets/' . $v_tickets_info->tickets_id) ?>
                                    <?= btn_delete('admin/tickets/delete/delete_tickets/' . $v_tickets_info->tickets_id) ?>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                }
            }
            ?>
            </tbody>
        </table>
    </div>
</div>