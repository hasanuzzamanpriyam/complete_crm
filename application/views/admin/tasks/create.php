<script src="<?php echo base_url(); ?>assets/plugins/bootstrap-tagsinput/fm.tagator.jquery.js"></script>
<?php include_once 'assets/admin-ajax.php'; ?>
<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<style>
    .note-editor .note-editable { height: 150px; }
    a:hover { text-decoration: none; }
    .custom-bulk-button { display: initial; }

    /* UI Slider Custom Styles */
    .ui-widget.ui-widget-content { border: 1px solid #dde6e9; }
    .ui-corner-all, .ui-corner-bottom, .ui-corner-left, .ui-corner-bl { border: 7px solid #28a9f1; }
    .ui-widget-content { border: 1px solid #dddddd; color: #333333; }
    .ui-slider { position: relative; text-align: left; }
    .ui-slider-horizontal { height: 1em; }
    .ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default,
    .ui-button, html .ui-button.ui-state-disabled:hover, html .ui-button.ui-state-disabled:active {
        border: 1px solid #1797be; background: #1797be; font-weight: normal; color: #454545;
    }
    .ui-slider-horizontal .ui-slider-handle { top: -.3em; margin-left: -.1em; margin-right: -.1em; }
    .ui-slider .ui-slider-handle:hover { background: #1797be; }
    .ui-slider .ui-slider-handle {
        position: absolute; z-index: 2; width: 1.2em; height: 1.5em; cursor: default;
        -ms-touch-action: none; touch-action: none;
    }
    .ui-state-disabled, .ui-widget-content .ui-state-disabled, .ui-widget-header .ui-state-disabled {
        opacity: .35; filter: Alpha(Opacity=35); background-image: none;
    }
    .ui-state-disabled { cursor: default !important; pointer-events: none; }
    .ui-slider.ui-state-disabled .ui-slider-handle, .ui-slider.ui-state-disabled .ui-slider-range { filter: inherit; }
    .ui-slider-range, .ui-widget-header, .ui-slider-handle:before, .list-group-item.active,
    .list-group-item.active:hover, .list-group-item.active:focus, .icon-frame {
        background-image: none; background: #28a9f1;
    }
</style>

<?php
    $created = can_action('54', 'created');
    $edited = can_action('54', 'edited');
    $deleted = can_action('54', 'deleted');

    $kanban = $this->session->userdata('task_kanban');
    $uri_segment = $this->uri->segment(4);
    $tasks = (!empty($kanban) || $uri_segment == 'kanban') ? 'kanban' : 'list';
    
    $text = ($tasks == 'kanban') ? 'list' : 'kanban';
    $btn = ($tasks == 'kanban') ? 'purple' : 'danger';
    
    $task_id = !empty($task_info) ? $task_info->task_id : null;
?>

<div class="mb-lg pull-left">
    <div class="pull-left pr-lg">
        <a href="<?= base_url() ?>admin/tasks/all_task/<?= $text ?>" class="btn btn-xs btn-<?= $btn ?> pull-right" data-toggle="tooltip" data-placement="top" title="<?= lang('switch_to_' . $text) ?>">
            <i class="fa fa-undo"> </i><?= ' ' . lang('switch_to_' . $text) ?>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="<?= $active == 1 ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/tasks/all_task') ?>"><?= lang('all_task') ?></a>
                </li>
                <?php if (!empty($created) || !empty($edited)) { ?>
                    <li class="<?= $active == 2 ? 'active' : '' ?>">
                        <a href="<?= base_url('admin/tasks/create') ?>"><?= lang('assign_task') ?></a>
                    </li>
                    <li>
                        <a class="import" href="<?= base_url() ?>admin/tasks/import"><?= lang('import') . ' ' . lang('tasks') ?></a>
                    </li>
                <?php } ?>
            </ul>

            <div class="tab-content bg-white">
                <div class="tab-pane <?= $active == 2 ? 'active' : '' ?>" id="assign_task" style="position: relative;">
                    <div class="box" style="border: none; padding-top: 15px;" data-collapsed="0">
                        <div class="panel-body row">
                            
                            <form data-parsley-validate="" data-parsley-excluded="input[type=button], input[type=submit], input[type=reset]" novalidate="" action="<?php echo base_url() ?>admin/tasks/save_task/<?php if (!empty($task_id)) echo $task_id; ?>" method="post" class="form-horizontal">
                                
                                <?php
                                    $project_id = !empty($task_info->project_id) ? $task_info->project_id : (!empty($project_id) ? $project_id : null);
                                    if ($project_id) echo '<input type="hidden" name="un_project_id" required class="form-control" value="'.$project_id.'" />';

                                    $opportunities_id = !empty($task_info->opportunities_id) ? $task_info->opportunities_id : (!empty($opportunities_id) ? $opportunities_id : null);
                                    if ($opportunities_id) echo '<input type="hidden" name="un_opportunities_id" required class="form-control" value="'.$opportunities_id.'" />';

                                    $leads_id = !empty($task_info->leads_id) ? $task_info->leads_id : (!empty($leads_id) ? $leads_id : null);
                                    if ($leads_id) echo '<input type="hidden" name="un_leads_id" required class="form-control" value="'.$leads_id.'" />';

                                    $bug_id = !empty($task_info->bug_id) ? $task_info->bug_id : (!empty($bug_id) ? $bug_id : null);
                                    if ($bug_id) echo '<input type="hidden" name="un_bug_id" required class="form-control" value="'.$bug_id.'" />';

                                    $goal_tracking_id = !empty($task_info->goal_tracking_id) ? $task_info->goal_tracking_id : (!empty($goal_tracking_id) ? $goal_tracking_id : null);
                                    if ($goal_tracking_id) echo '<input type="hidden" name="un_goal_tracking_id" required class="form-control" value="'.$goal_tracking_id.'" />';

                                    $sub_task_id = !empty($task_info->sub_task_id) ? $task_info->sub_task_id : (!empty($sub_task_id) ? $sub_task_id : null);
                                    if ($sub_task_id) echo '<input type="hidden" name="un_sub_task_id" required class="form-control" value="'.$sub_task_id.'" />';

                                    $transactions_id = !empty($task_info->transactions_id) ? $task_info->transactions_id : (!empty($transactions_id) ? $transactions_id : null);
                                    if ($transactions_id) echo '<input type="hidden" name="un_transactions_id" required class="form-control" value="'.$transactions_id.'" />';

                                    $domain_id = (!empty($task_info->module) && $task_info->module == 'domain') ? $task_info->module_field_id : (!empty($domain_id_from_url) ? $domain_id_from_url : null);
                                    if ($domain_id) echo '<input type="hidden" name="un_domain_id" required class="form-control" value="'.$domain_id.'" />';

                                    $server_hosting_id = (!empty($task_info->module) && $task_info->module == 'server_hosting') ? $task_info->module_field_id : (!empty($server_hosting_id_from_url) ? $server_hosting_id_from_url : null);
                                    if ($server_hosting_id) echo '<input type="hidden" name="un_server_hosting_id" required class="form-control" value="'.$server_hosting_id.'" />';
                                ?>

                                <div class="col-md-6">
                                    
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label"><?= lang('task_name') ?><span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" name="task_name" required class="form-control" value="<?php if (!empty($task_info->task_name)) echo $task_info->task_name; ?>" />
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-4 control-label"><?= lang('select') . ' ' . lang('categories') ?></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <?php
                                                    $selected = (!empty($task_info->category_id) ? $task_info->category_id : '');
                                                    echo form_dropdown('category_id', $all_customer_group, $selected, array('class' => 'form-control select_box', 'style' => 'width:100%'));
                                                ?>
                                                <?php if (!empty(can_action('125', 'created'))) { ?>
                                                    <div class="input-group-addon" title="<?= lang('new') . ' ' . lang('categories') ?>" data-toggle="tooltip" data-placement="top">
                                                        <a data-toggle="modal" data-target="#myModal" href="<?= base_url() ?>admin/tasks/new_category"><i class="fa fa-plus"></i></a>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group" id="border-none">
                                        <label for="field-1" class="col-sm-4 control-label"><?= lang('related_to') ?></label>
                                        <div class="col-sm-8">
                                            <select name="related_to" class="form-control" id="check_related" onchange="get_related_moduleName(this.value,null,4)">
                                                <option value="0"> <?= lang('none') ?> </option>
                                                <option value="project" <?= (!empty($project_id) ? 'selected' : '') ?>><?= lang('project') ?></option>
                                                <option value="opportunities" <?= (!empty($opportunities_id) ? 'selected' : '') ?>><?= lang('opportunities') ?></option>
                                                <option value="leads" <?= (!empty($leads_id) ? 'selected' : '') ?>><?= lang('leads') ?></option>
                                                <option value="bug" <?= (!empty($bug_id) ? 'selected' : '') ?>><?= lang('bugs') ?></option>
                                                <option value="goal" <?= (!empty($goal_tracking_id) ? 'selected' : '') ?>><?= lang('goal_tracking') ?></option>
                                                <option value="sub_task" <?= (!empty($sub_task_id) ? 'selected' : '') ?>><?= lang('tasks') ?></option>
                                                <option value="expenses" <?= (!empty($transactions_id) ? 'selected' : '') ?>><?= lang('expenses') ?></option>
                                                <option value="domain" <?= (!empty($domain_id) ? 'selected' : '') ?>>Domain</option>
                                                <option value="server_hosting" <?= (!empty($server_hosting_id) ? 'selected' : '') ?>>Server Hosting</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group" id="related_to"></div>

                                    <?php $payment_type = (!empty($task_info->payment_type) ? $task_info->payment_type : 'none'); ?>
                                    <div class="form-group" id="payment_type_group" style="display:none;">
                                        <label class="col-sm-4 control-label"><?= lang('Repeatation') ?><span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <select name="payment_type" class="form-control">
                                                <option value="none" <?= $payment_type == 'none' ? 'selected' : '' ?>><?= lang('none') ?></option>
                                                <option value="daily" <?= $payment_type == 'daily' ? 'selected' : '' ?>>Daily</option>
                                                <option value="monthly" <?= $payment_type == 'monthly' ? 'selected' : '' ?>>Monthly</option>
                                                <option value="bi-monthly" <?= $payment_type == 'bi-monthly' ? 'selected' : '' ?>>Bi-monthly</option>
                                                <option value="quarterly" <?= $payment_type == 'quarterly' ? 'selected' : '' ?>>Quarterly</option>
                                                <option value="yearly" <?= $payment_type == 'yearly' ? 'selected' : '' ?>>Yearly</option>
                                            </select>
                                            <div class="task-recurring-info" id="recurring_info" style="display:none;">
                                                <i class="fa fa-info-circle"></i>
                                                <span id="recurring_message"><?= lang('task_will_recur_based_on_payment_type') ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (empty($project_id)) { ?>
                                        <div class="form-group company" id="milestone_show">
                                            <label for="field-1" class="col-sm-4 control-label"><?= lang('milestones') ?> <span class="required">*</span></label>
                                            <div class="col-sm-8">
                                                <select name="milestones_id" id="milestone" class="form-control selectpicker company">
                                                    <?php
                                                    if (!empty($project_id)) {
                                                        $all_milestones_info = $this->db->where('project_id', $project_id)->get('tbl_milestones')->result();
                                                    } else {
                                                        $project_milestone = $this->db->get('tbl_project')->row();
                                                        $all_milestones_info = $this->db->where('project_id', $project_milestone->project_id)->get('tbl_milestones')->result();
                                                    }
                                                    if (!empty($all_milestones_info)) {
                                                        foreach ($all_milestones_info as $v_milestones) {
                                                            $selected = (!empty($task_info->milestones_id) && $v_milestones->milestones_id == $task_info->milestones_id) ? 'selected' : '';
                                                            echo "<option value='{$v_milestones->milestones_id}' {$selected}>{$v_milestones->milestone_name}</option>";
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    <?php } ?>

                                    <?php if (!empty($project_id)) : 
                                        $project_info = $this->db->where('project_id', $project_id)->get('tbl_project')->row();
                                        $all_project = $this->tasks_model->get_permission('tbl_project');
                                    ?>
                                        <div class="form-group <?= $project_id ? 'project_module' : 'company' ?>">
                                            <label for="field-1" class="col-sm-4 control-label"><?= lang('project') ?> <span class="required">*</span></label>
                                            <div class="col-sm-8">
                                                <select name="project_id" style="width: 100%" class="select_box <?= $project_id ? 'project_module' : 'company' ?>" required="1" onchange="get_milestone_by_id(this.value)">
                                                    <?php if (!empty($all_project)) {
                                                        foreach ($all_project as $v_project) {
                                                            $selected = ($v_project->project_id == $project_id) ? 'selected' : '';
                                                            echo "<option value='{$v_project->project_id}' {$selected}>{$v_project->project_name}</option>";
                                                        }
                                                    } ?>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group <?= $project_id ? 'milestone_module' : 'company' ?>" id="milestone_show">
                                            <label for="field-1" class="col-sm-4 control-label"><?= lang('milestones') ?> <span class="required">*</span></label>
                                            <div class="col-sm-8">
                                                <select name="milestones_id" id="milestone" class="form-control selectpicker <?= $project_id ? 'milestone_module' : 'company' ?>">
                                                    <option><?= lang('none') ?></option>
                                                    <?php
                                                    $all_milestones_info = $this->db->where('project_id', $project_id)->get('tbl_milestones')->result();
                                                    if (!empty($all_milestones_info)) {
                                                        foreach ($all_milestones_info as $v_milestones) {
                                                            $selected = (!empty($task_info->milestones_id) && $v_milestones->milestones_id == $task_info->milestones_id) ? 'selected' : '';
                                                            echo "<option value='{$v_milestones->milestones_id}' {$selected}>{$v_milestones->milestone_name}</option>";
                                                        }
                                                    } ?>
                                                </select>
                                            </div>
                                        </div>
                                    <?php endif ?>

                                    <?php 
                                        $rel_modules = [
                                            ['id' => $opportunities_id, 'class' => 'opportunities_module', 'lang' => 'opportunities', 'data' => $this->tasks_model->get_permission('tbl_opportunities'), 'val_col' => 'opportunities_id', 'name_col' => 'opportunity_name'],
                                            ['id' => $leads_id, 'class' => 'leads_module', 'lang' => 'leads', 'data' => $this->tasks_model->get_permission('tbl_leads'), 'val_col' => 'leads_id', 'name_col' => 'lead_name'],
                                            ['id' => $bug_id, 'class' => 'bugs_module', 'lang' => 'bugs', 'data' => $this->tasks_model->get_permission('tbl_bug'), 'val_col' => 'bug_id', 'name_col' => 'bug_title'],
                                            ['id' => $goal_tracking_id, 'class' => 'goal_tracking', 'lang' => 'goal_tracking', 'data' => $this->tasks_model->get_permission('tbl_goal_tracking'), 'val_col' => 'goal_tracking_id', 'name_col' => 'subject'],
                                            ['id' => $sub_task_id, 'class' => 'sub_tasks', 'lang' => 'tasks', 'data' => $this->tasks_model->get_permission('tbl_task'), 'val_col' => 'task_id', 'name_col' => 'task_name']
                                        ];

                                        foreach($rel_modules as $mod) {
                                            if (!empty($mod['id'])) {
                                                echo "<div class='form-group border-none {$mod['class']}'>";
                                                echo "<label class='col-sm-4 control-label'>" . lang('select') . " " . lang($mod['lang']) . " <span class='required'>*</span></label>";
                                                echo "<div class='col-sm-8'><select name='{$mod['val_col']}' style='width: 100%' class='select_box {$mod['class']}' required='1'>";
                                                if (!empty($mod['data'])) {
                                                    foreach ($mod['data'] as $row) {
                                                        $selected = ($row->{$mod['val_col']} == $mod['id']) ? 'selected' : '';
                                                        echo "<option value='{$row->{$mod['val_col']}}' {$selected}>{$row->{$mod['name_col']}}</option>";
                                                    }
                                                }
                                                echo "</select></div></div>";
                                            }
                                        }
                                    ?>

                                    <?php if (!empty($transactions_id)) : ?>
                                        <div class="form-group <?= $transactions_id ? 'expenses' : 'company' ?>" id="border-none">
                                            <label class="col-sm-4 control-label"><?= lang('select') . ' ' . lang('expenses') ?> <span class="required">*</span></label>
                                            <div class="col-sm-8">
                                                <select name="transactions_id" style="width: 100%" class="select_box <?= $transactions_id ? 'expenses' : 'company' ?>" required="1">
                                                    <?php
                                                    $all_expenses = $this->tasks_model->get_permission('tbl_transactions');
                                                    if (!empty($all_expenses)) {
                                                        foreach ($all_expenses as $v_expenses) {
                                                            $selected = (!empty($transactions_id) && $v_expenses->transactions_id == $transactions_id) ? 'selected' : '';
                                                            $ref = !empty($v_expenses->reference) ? '#' . $v_expenses->reference : '';
                                                            echo "<option value='{$v_expenses->transactions_id}' {$selected}>{$v_expenses->name} {$ref}</option>";
                                                        }
                                                    } ?>
                                                </select>
                                            </div>
                                        </div>
                                    <?php endif ?>

                                    <?php if (!empty($domain_id)) : ?>
                                        <div class="form-group <?= $domain_id ? 'domain' : 'company' ?>" id="border-none">
                                            <label class="col-sm-4 control-label"><?= lang('select') . ' Domain' ?> <span class="required">*</span></label>
                                            <div class="col-sm-8">
                                                <select name="domain_id" style="width: 100%" class="select_box <?= $domain_id ? 'domain' : 'company' ?>" required="1">
                                                    <?php
                                                    $all_domains = $this->db->get('tbldomains')->result();
                                                    if (!empty($all_domains)) {
                                                        foreach ($all_domains as $v_domain) {
                                                            $selected = (!empty($domain_id) && $v_domain->id == $domain_id) ? 'selected' : '';
                                                            echo "<option value='{$v_domain->id}' {$selected}>{$v_domain->domain_name}</option>";
                                                        }
                                                    } ?>
                                                </select>
                                            </div>
                                        </div>
                                    <?php endif ?>

                                    <?php if (!empty($server_hosting_id)) : ?>
                                        <div class="form-group <?= $server_hosting_id ? 'server_hosting' : 'company' ?>" id="border-none">
                                            <label class="col-sm-4 control-label"><?= lang('select') . ' Server Hosting' ?> <span class="required">*</span></label>
                                            <div class="col-sm-8">
                                                <select name="server_hosting_id" style="width: 100%" class="select_box <?= $server_hosting_id ? 'server_hosting' : 'company' ?>" required="1">
                                                    <?php
                                                    $all_hostings = $this->db->get('tblserver_hostings')->result();
                                                    if (!empty($all_hostings)) {
                                                        foreach ($all_hostings as $v_hosting) {
                                                            $selected = (!empty($server_hosting_id) && $v_hosting->id == $server_hosting_id) ? 'selected' : '';
                                                            echo "<option value='{$v_hosting->id}' {$selected}>{$v_hosting->title}</option>";
                                                        }
                                                    } ?>
                                                </select>
                                            </div>
                                        </div>
                                    <?php endif ?>

                                    <div class="form-group">
                                        <label class="col-lg-4 control-label"><?= lang('start_date') ?></label>
                                        <div class="col-lg-8">
                                            <div class="input-group">
                                                <input type="text" name="task_start_date" class="form-control start_date" value="<?= !empty($task_info->task_start_date) ? $task_info->task_start_date : '' ?>" data-date-format="<?= config_item('date_picker_format'); ?>">
                                                <div class="input-group-addon"><a href="#"><i class="fa fa-calendar"></i></a></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-4 control-label"><?= lang('due_date') ?><span class="required">*</span></label>
                                        <div class="col-lg-8">
                                            <div class="input-group">
                                                <input type="text" name="due_date" required="" value="<?= !empty($task_info->due_date) ? $task_info->due_date : '' ?>" class="form-control end_date" data-date-format="<?= config_item('date_picker_format'); ?>">
                                                <div class="input-group-addon"><a href="#"><i class="fa fa-calendar"></i></a></div>
                                            </div>
                                        </div>
                                    </div>

                                    <?= custom_form_Fields(3, $task_id); ?>

                                    <?php
                                        $permissionL = !empty($task_info->permission) ? $task_info->permission : null;
                                        echo get_permission(4, 8, $assign_user, $permissionL, lang('assined_to')); 
                                    ?>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label"><?= lang('project_hourly_rate') ?></label>
                                        <div class="col-sm-8">
                                            <input type="text" data-parsley-type="number" name="hourly_rate" class="form-control" value="<?php if (!empty($task_info->hourly_rate)) echo $task_info->hourly_rate; ?>" />
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label"><?= lang('estimated_hour') ?></label>
                                        <div class="col-sm-8">
                                            <input type="number" step="0.01" data-parsley-type="number" name="task_hour" class="form-control" value="<?php
                                                if (!empty($task_info->task_hour)) {
                                                    $result = explode(':', $task_info->task_hour);
                                                    $result1 = empty($result[1]) ? 0 : $result[1];
                                                    echo $result[0] . '.' . $result1;
                                                }
                                            ?>" />
                                        </div>
                                    </div>

                                    <?php $value = !empty($task_info) ? $this->tasks_model->get_task_progress($task_info->task_id) : 0; ?>
                                    <div class="form-group">
                                        <label class="col-lg-4 control-label"><?php echo lang('progress'); ?> </label>
                                        <div class="col-lg-8">
                                            <?php echo form_hidden('task_progress', $value); ?>
                                            <div class="project_progress_slider project_progress_slider_horizontal mbot15"></div>

                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    <div>
                                                        <div class="pull-left mt">
                                                            <?php echo lang('progress'); ?>
                                                            <span class="label_progress "><?php echo $value; ?>%</span>
                                                        </div>
                                                        <div class="checkbox c-checkbox pull-right" data-toggle="tooltip" data-placement="top" title="<?php echo lang('calculate_progress_through_sub_tasks'); ?>">
                                                            <label class="needsclick">
                                                                <input class="select_one" type="checkbox" <?php echo (!empty($task_info) && $task_info->calculate_progress == 'through_sub_tasks') ? 'checked' : ''; ?> name="calculate_progress" value="through_sub_tasks" id="through_sub_tasks">
                                                                <span class="fa fa-check"></span>
                                                                <small><?php echo lang('through_sub_tasks'); ?></small>
                                                            </label>
                                                        </div>
                                                        <div class="checkbox c-checkbox pull-right" data-toggle="tooltip" data-placement="top" title="<?php echo lang('calculate_progress_through_task_hours'); ?>">
                                                            <label class="needsclick">
                                                                <input class="select_one" type="checkbox" <?php echo (!empty($task_info) && $task_info->calculate_progress == 'through_tasks_hours') ? 'checked' : ''; ?> name="calculate_progress" value="through_tasks_hours" id="through_tasks_hours">
                                                                <span class="fa fa-check"></span>
                                                                <small><?php echo lang('through_tasks_hours'); ?></small>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <br>

                                    <div class="form-group" id="border-none">
                                        <label for="field-1" class="col-sm-4 control-label"><?= lang('task_status') ?> <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <select name="task_status" class="form-control" required>
                                                <option value="not_started" <?= (!empty($task_info->task_status) && $task_info->task_status == 'not_started' ? 'selected' : '') ?>><?= lang('not_started') ?></option>
                                                <option value="in_progress" <?= (!empty($task_info->task_status) && $task_info->task_status == 'in_progress' ? 'selected' : '') ?>><?= lang('in_progress') ?></option>
                                                <option value="completed" <?= (!empty($task_info->task_status) && $task_info->task_status == 'completed' ? 'selected' : '') ?>><?= lang('completed') ?></option>
                                                <option value="deferred" <?= (!empty($task_info->task_status) && $task_info->task_status == 'deferred' ? 'selected' : '') ?>><?= lang('deferred') ?></option>
                                                <option value="waiting_for_someone" <?= (!empty($task_info->task_status) && $task_info->task_status == 'waiting_for_someone' ? 'selected' : '') ?>><?= lang('waiting_for_someone') ?></option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="field-1" class="col-sm-4 control-label"><?= lang('priority') ?> <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <select name="priority" class="form-control" required>
                                                <option value="low" <?= (!empty($task_info->priority) && $task_info->priority == 'low' ? 'selected' : '') ?>><?= lang('low') ?></option>
                                                <option value="medium" <?= (!empty($task_info->priority) && $task_info->priority == 'medium' ? 'selected' : '') ?>><?= lang('medium') ?></option>
                                                <option value="high" <?= (!empty($task_info->priority) && $task_info->priority == 'high' ? 'selected' : '') ?>><?= lang('high') ?></option>
                                                <option value="urgent" <?= (!empty($task_info->priority) && $task_info->priority == 'urgent' ? 'selected' : '') ?>><?= lang('urgent') ?></option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="field-1" class="col-sm-4 control-label"><?= lang('reporting_to') ?> <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <select name="report_to" class="form-control select_box" style="width: 100%" required>
                                                <option value=""><?= lang('select') . ' ' . lang('reporting_to') ?></option>
                                                <?php if (!empty($assign_user)) : foreach ($assign_user as $v_user) : ?>
                                                    <option value="<?= $v_user->user_id ?>" <?= (!empty($task_info->report_to) && $task_info->report_to == $v_user->user_id ? 'selected' : '') ?>>
                                                        <?= fullname($v_user->user_id) ?> (<?= designation($v_user->user_id) ?>)
                                                    </option>
                                                <?php endforeach; endif; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="field-1" class="col-sm-4 control-label"><?= lang('tags') ?></label>
                                        <div class="col-sm-8">
                                            <input type="text" name="tags" data-role="tagsinput" class="form-control" value="<?= !empty($task_info->tags) ? $task_info->tags : '' ?>">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="field-1" class="col-sm-4 control-label"><?= lang('billable') ?> <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input data-toggle="toggle" name="billable" value="Yes" <?php echo (!empty($task_info) && $task_info->billable == 'Yes') ? 'checked' : ''; ?> data-on="<?= lang('yes') ?>" data-off="<?= lang('no') ?>" data-onstyle="success" data-offstyle="danger" type="checkbox">
                                        </div>
                                    </div>

                                    <?php if (!empty($project_id)) : ?>
                                        <div class="form-group">
                                            <label for="field-1" class="col-sm-4 control-label"><?= lang('visible_to_client') ?> <span class="required">*</span></label>
                                            <div class="col-sm-8">
                                                <input data-toggle="toggle" name="client_visible" value="Yes" <?php echo (!empty($task_info) && $task_info->client_visible == 'Yes') ? 'checked' : ''; ?> data-on="<?= lang('yes') ?>" data-off="<?= lang('no') ?>" data-onstyle="success" data-offstyle="danger" type="checkbox">
                                            </div>
                                        </div>
                                    <?php endif ?>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group mt-lg">
                                        <div class="col-sm-12">
                                            <label for="field-1" class="control-label"><?= lang('task_description') ?> <span class="required">*</span></label>
                                        </div>
                                        <div class="col-sm-12">
                                            <textarea class="form-control textarea" name="task_description" required><?php if (!empty($task_info->task_description)) echo $task_info->task_description; ?></textarea>
                                        </div>
                                    </div>

                                    <div class="btn-bottom-toolbar text-right">
                                        <?php if (!empty($task_info)) { ?>
                                            <button type="submit" class="btn btn-sm btn-primary"><?= lang('updates') ?></button>
                                            <button type="button" onclick="goBack()" class="btn btn-sm btn-danger"><?= lang('cancel') ?></button>
                                        <?php } else { ?>
                                            <button type="submit" class="btn btn-sm btn-primary"><?= lang('save') ?></button>
                                        <?php } ?>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url() ?>assets/js/jquery-ui.js"></script>

<?php 
    $direction = $this->session->userdata('direction');
    $RTL = (!empty($direction) && $direction == 'rtl') ? 'on' : config_item('RTL');
    if (!empty($RTL)) { 
?>
    <script type="text/javascript" src="<?= base_url() ?>assets/plugins/jquery-ui/jquery.ui.slider-rtl.js"></script>
<?php } ?>

<script>
    function togglePaymentTypeField() {
        var relVal = $('#check_related').val();
        var showPaymentType = relVal === 'expenses';
        var $group = $('#payment_type_group');
        if (showPaymentType) {
            $group.show();
            $group.find('select[name="payment_type"]').prop('required', true);
        } else {
            $group.hide();
            $group.find('select[name="payment_type"]').prop('required', false);
        }
    }

    $(document).ready(function() {
        // Toggle related payment type
        togglePaymentTypeField();
        $('#check_related').on('change', function() {
            togglePaymentTypeField();
        });

        var paymentType = $('select[name="payment_type"]').val();
        if (paymentType && paymentType !== '') {
            $('select[name="payment_type"]').trigger('change');
        }

        // Handle Progress Slider UI
        var progress_input = $('input[name="task_progress"]');
        
        <?php if ((!empty($task_info) && $task_info->calculate_progress == 'through_tasks_hours')) { ?>
            var progress_from_tasks = $('#through_tasks_hours');
        <?php } elseif ((!empty($task_info) && $task_info->calculate_progress == 'through_sub_tasks')) { ?>
            var progress_from_tasks = $('#through_sub_tasks');
        <?php } else { ?>
            var progress_from_tasks = $('.select_one');
        <?php } ?>

        var progress = progress_input.val();
        $('.project_progress_slider').slider({
            range: "min",
            <?php if (!empty($RTL)) { ?> isRTL: true, <?php } ?>
            min: 0,
            max: 100,
            value: progress,
            disabled: progress_from_tasks.prop('checked'),
            slide: function(event, ui) {
                progress_input.val(ui.value);
                $('.label_progress').html(ui.value + '%');
            }
        });

        progress_from_tasks.on('change', function() {
            var _checked = $(this).prop('checked');
            $('.project_progress_slider').slider({ disabled: _checked });
        });

        // Sync summernote with textarea for parsley validation
        $('.textarea').on('summernote.change', function(we, contents, $editable) {
            $(this).val(contents === '<p><br></p>' ? '' : contents);
            // Trigger parsley validation
            $(this).parsley().validate();
        });
    });
</script>