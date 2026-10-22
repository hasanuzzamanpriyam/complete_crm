<?php $this->load->view('admin/client/_client_assets'); ?>
<div class="cl-root cl-tbl">
    <div class="cl-card">
        <div class="cl-sec-head">
            <h1><?= lang('project') ?></h1>
            <div class="cl-spacer"></div>
            <?php
            $pro_created = can_action('57', 'created');
            $pro_edited = can_action('57', 'edited');
            if (!empty($pro_created) || !empty($pro_edited)) {
                ?>
                <a href="<?= base_url() ?>admin/projects/create/client_project/<?= $client_details->client_id ?>"
                   class="cl-btn cl-btn-primary cl-btn-sm"><?= lang('new_project') ?></a>
            <?php } ?>
        </div>
        
        <table class="cl-tbl cl-mini" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?= lang('project_name') ?></th>
                <th><?= lang('end_date') ?></th>
                <th><?= lang('status') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $all_project = get_order_by('tbl_project', array('client_id' => $client_details->client_id), 'project_id');
            if (!empty($all_project)) : foreach ($all_project as $v_project) :
                $progress = $this->items_model->get_project_progress($v_project->project_id);
                ?>
                <tr>
                    <td>
                        <a class="cl-nm"
                           href="<?= base_url() ?>admin/projects/project_details/<?= $v_project->project_id ?>">
                            <?= html_escape($v_project->project_name) ?>
                        </a>
                        <?php if (strtotime(date('Y-m-d')) > strtotime($v_project->end_date) && $progress < 100) { ?>
                            <span class="cl-tag cl-tag-danger"><?= lang('overdue') ?></span>
                        <?php } ?>
                        
                        <div class="cl-progress" style="margin-top: 4px;">
                            <div class="cl-progress-bar" style="width: <?= $progress; ?>%"></div>
                        </div>
                    </td>
                    <td><?= strftime(config_item('date_format'), strtotime($v_project->end_date)) ?></td>
                    <td>
                        <?php
                        if (!empty($v_project->project_status)) {
                            $status_class = '';
                            $status_text = lang($v_project->project_status);
                            if ($v_project->project_status == 'completed') {
                                $status_class = 'cl-tag-success';
                            } elseif ($v_project->project_status == 'in_progress') {
                                $status_class = 'cl-tag-info';
                            } elseif ($v_project->project_status == 'cancel') {
                                $status_class = 'cl-tag-danger';
                            } else {
                                $status_class = 'cl-tag-amber';
                            }
                            echo "<span class=\"cl-tag $status_class\">$status_text</span>";
                        }
                        ?> 
                    </td>
                </tr>
            <?php
            endforeach;
            endif;
            ?>
            </tbody>
        </table>
    </div>
</div>