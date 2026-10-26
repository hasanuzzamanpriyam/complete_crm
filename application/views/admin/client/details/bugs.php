<div class="cl-card">
        <div class="cl-sec-head">
            <h1><?= lang('bugs') ?></h1>
            <div class="cl-spacer"></div>
            <?php
            $bugs_created = can_action('58', 'created');
            $bugs_edited = can_action('58', 'edited');
            if (!empty($bugs_created) || !empty($bugs_edited)) {
                ?>
                <a href="<?= base_url() ?>admin/bugs/create/<?= $client_details->primary_contact ?>"
                   class="cl-btn cl-btn-primary cl-btn-sm"><?= lang('new_bugs') ?></a>
            <?php } ?>
        </div>

        <div class="tbl-toolbar">
            <div class="search-in"><span>🔍</span><input type="text"></div>
            <div style="flex:1"></div>
            <button class="cl-btn cl-btn-ghost">⌗ <?= lang('filter') ?></button>
        </div>

        <table class="cl-tbl cl-mini" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?= lang('bug_title') ?></th>
                <th><?= lang('status') ?></th>
                <th><?= lang('priority') ?></th>
                <?php if ($this->session->userdata('user_type') == '1') { ?>
                    <th><?= lang('reporter') ?></th>
                <?php } ?>
                <th><?= lang('assigned_to') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $all_bug_info = $this->client_model->getClientData('tbl_bug', $client_details->client_id);
            $total_bugs = 0;
            if (!empty($all_bug_info)) {
                foreach ($all_bug_info as $v_bugs) {
                    $profile = $this->db->where(array('user_id' => $v_bugs->reporter))->get('tbl_account_details')->row();
                    if (!empty($profile->company) && $profile->company == $client_details->client_id) {
                        $total_bugs += 1;
                        $reporter = $this->db->where('user_id', $v_bugs->reporter)->get('tbl_users')->row();
                        if ($reporter->role_id == '1') {
                            $badge = 'danger';
                        } elseif ($reporter->role_id == '2') {
                            $badge = 'info';
                        } else {
                            $badge = 'primary';
                        }
                        
                        if ($v_bugs->bug_status == 'unconfirmed') {
                            $label = 'cl-tag-amber';
                        } elseif ($v_bugs->bug_status == 'confirmed') {
                            $label = 'cl-tag-info';
                        } elseif ($v_bugs->bug_status == 'in_progress') {
                            $label = 'cl-tag-info';
                        } elseif ($v_bugs->bug_status == 'resolved') {
                            $label = 'cl-tag-purple';
                        } else {
                            $label = 'cl-tag-success';
                        }
                        ?>
                        <tr>
                            <td>
                                <a class="cl-nm" style="<?php
                                    if ($v_bugs->bug_status == 'resolve') {
                                        echo 'text-decoration: line-through;';
                                    }
                                    ?>"
                                   href="<?= base_url() ?>admin/bugs/view_bug_details/<?= $v_bugs->bug_id ?>">
                                    <?= html_escape($v_bugs->bug_title) ?>
                                </a>
                            </td>
                            <td><span class="cl-tag <?= $label ?>"><?= lang($v_bugs->bug_status) ?></span></td>
                            <td>
                                <?php
                                if ($v_bugs->priority == 'High') {
                                    $plabel = 'cl-tag-danger';
                                } elseif ($v_bugs->priority == 'Medium') {
                                    $plabel = 'cl-tag-info';
                                } else {
                                    $plabel = 'cl-tag-primary';
                                }
                                ?>
                                <span class="cl-tag <?= $plabel ?>"><?= ucfirst($v_bugs->priority) ?></span>
                            </td>
                            <td>
                                <span class="cl-tag cl-tag-<?= $badge ?>"><?= html_escape($reporter->username) ?></span>
                            </td>
                            <td>
                                <?php
                                if ($v_bugs->permission != 'all') {
                                    $get_permission = json_decode($v_bugs->permission);
                                    
                                    if (!empty($get_permission)) {
                                        foreach ($get_permission as $permission => $v_permission) {
                                            $user_info = $this->db->where(array('user_id' => $permission))->get('tbl_users')->row();
                                            if ($user_info->role_id == 1) {
                                                $label = 'circle-danger';
                                            } else {
                                                $label = 'circle-success';
                                            }
                                            $profile_info = $this->db->where(array('user_id' => $permission))->get('tbl_account_details')->row();
                                            ?>
                                            <a href="#" data-toggle="tooltip" data-placement="top"
                                               title="<?= html_escape($profile_info->fullname) ?>">
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
                                                <span class="custom-permission circle <?= $label ?> circle-lg"></span>
                                            </a>
                                            <?php
                                        }
                                    }
                                } else { ?>
                                    <strong><?= lang('everyone') ?></strong>
                                    <i title="<?= lang('permission_for_all') ?>" class="fa fa-question-circle"
                                       data-toggle="tooltip" data-placement="top"></i>
                                    <?php
                                }
                                ?>
                                <?php if ($this->session->userdata('user_type') == 1) { ?>
                                    <span data-placement="top" data-toggle="tooltip"
                                          title="<?= lang('add_more') ?>">
                                        <a data-toggle="modal" data-target="#myModal"
                                           href="<?= base_url() ?>admin/bugs/update_users/<?= $v_bugs->bug_id ?>"
                                           class="cl-btn cl-btn-icon"><i class="fa fa-plus"></i></a>
                                    </span>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php
                    }
                }
            }
            ?>
            </tbody>
        </table>
    </div>