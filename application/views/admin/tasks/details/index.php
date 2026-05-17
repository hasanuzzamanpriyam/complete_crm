<?php
$edited = can_action('54', 'edited');
$can_edit = $this->tasks_model->can_action('tbl_task', 'edit', array('task_id' => $task_details->task_id));
$where = array('user_id' => $this->session->userdata('user_id'), 'module_id' => $task_details->task_id, 'module_name' => 'tasks');
$check_existing = $this->tasks_model->check_by($where, 'tbl_pinaction');
if (!empty($check_existing)) {
    $url = 'remove_todo/' . $check_existing->pinaction_id;
    $btn = 'danger';
    $title = lang('remove_todo');
} else {
    $url = 'add_todo_list/tasks/' . $task_details->task_id;
    $btn = 'warning';
    $title = lang('add_todo_list');
}

$task_time = $this->tasks_model->task_spent_time_by_id($task_details->task_id);
if ($task_details->timer_status == 'on') {
    $task_time += (time() - $task_details->start_time);
}
$estimate_hours = $task_details->task_hour;
$percentage = $this->tasks_model->get_estime_time($estimate_hours);
?>
<div class="panel panel-custom">
    <div class="panel-heading">
        <h3 class="panel-title">
            <?php if (!empty($task_details->task_name)) echo $task_details->task_name; ?>
            <div class="pull-right ml-sm">
                <a data-toggle="tooltip" data-placement="top" title="<?= $title ?>"
                    href="<?= base_url() ?>admin/projects/<?= $url ?>"
                    class="btn-xs btn btn-<?= $btn ?>"><i class="fa fa-thumb-tack"></i></a>
            </div>
            <div class="pull-right ml-sm">
                <a data-toggle="tooltip" data-placement="top" title="<?= lang('export_report') ?>"
                    href="<?= base_url() ?>admin/tasks/export_report/<?= $task_details->task_id ?>"
                    class="btn-xs btn btn-success"><i class="fa fa-file-pdf-o"></i></a>
            </div>
            <?php

            if (!empty($can_edit) && !empty($edited)) {
            ?>
                <span class="btn-xs pull-right"><a
                        href="<?= base_url() ?>admin/tasks/create/<?= $task_details->task_id ?>"><?= lang('edit') . ' ' . lang('task') ?></a>
                </span>
            <?php } ?>


        </h3>
    </div>
    <?php
    $p_category = $this->db->where('customer_group_id', $task_details->category_id)->get('tbl_customer_group')->row();
    if (!empty($p_category)) {
        $pc_name = $p_category->customer_group;
    } else {
        $pc_name = '-';
    }
    ?>
    <div class="panel-body form-horizontal task_details">
        <?php $task_details_view = config_item('task_details_view');
        if (!empty($task_details_view) && $task_details_view == '2') {
        ?>
            <div class="row">
                <div class="col-md-3 br">
                    <p class="lead bb"></p>
                    <form class="form-horizontal p-20">
                        <div class="form-group">
                            <div class="col-sm-4"><strong><?= lang('task_name') ?> :</strong></div>
                            <div class="col-sm-8">
                                <?php
                                if (!empty($task_details->task_name)) {
                                    echo $task_details->task_name;
                                }
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4"><strong><?= lang('categories') ?> :</strong></div>
                            <div class="col-sm-8">
                                <?php
                                if (!empty($pc_name)) {
                                    echo $pc_name;
                                }
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4"><strong><?= lang('tags') ?> :</strong></div>
                            <div class="col-sm-8">
                                <?php
                                if (!empty($task_details)) {
                                    echo get_tags($task_details->tags, true);
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                        if (!empty($task_details->project_id)) :
                            $project_info = $this->db->where('project_id', $task_details->project_id)->get('tbl_project')->row();
                            $milestones_info = $this->db->where('milestones_id', $task_details->milestones_id)->get('tbl_milestones')->row();
                        ?>
                            <div class="form-group ">
                                <div class="col-sm-4"><strong><?= lang('project_name') ?>
                                        :</strong></div>
                                <div class="col-sm-8 ">
                                    <?php if (!empty($project_info->project_name)) echo $project_info->project_name; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-4"><strong><?= lang('milestone') ?>
                                        :</strong></div>
                                <div class="col-sm-8 ">
                                    <?php if (!empty($milestones_info->milestone_name)) echo $milestones_info->milestone_name; ?>
                                </div>
                            </div>
                        <?php endif ?>
                        <?php
                        if (!empty($task_details->opportunities_id)) :
                            $opportunity_info = $this->db->where('opportunities_id', $task_details->opportunities_id)->get('tbl_opportunities')->row();
                        ?>
                            <div class="form-group">
                                <div class="col-sm-4"><strong
                                        class="mr-sm"><?= lang('opportunity_name') ?></strong></div>
                                <div class="col-sm-8">
                                    <?php if (!empty($opportunity_info->opportunity_name)) echo $opportunity_info->opportunity_name; ?>
                                </div>
                            </div>
                        <?php endif ?>

                        <?php
                        if (!empty($task_details->leads_id)) :
                            $leads_info = $this->db->where('leads_id', $task_details->leads_id)->get('tbl_leads')->row();
                        ?>
                            <div class="form-group">
                                <div class="col-sm-4"><strong
                                        class="mr-sm"><?= lang('leads_name') ?></strong>
                                </div>
                                <div class="col-sm-8">
                                    <?php if (!empty($leads_info->lead_name)) echo $leads_info->lead_name; ?>
                                </div>
                            </div>
                        <?php endif ?>

                        <?php
                        if (!empty($task_details->bug_id)) :
                            $bugs_info = $this->db->where('bug_id', $task_details->bug_id)->get('tbl_bug')->row();
                        ?>
                            <div class="form-group">
                                <div class="col-sm-4"><strong
                                        class="mr-sm"><?= lang('bug_title') ?></strong>
                                </div>
                                <div class="col-sm-8">
                                    <?php if (!empty($bugs_info->bug_title)) echo $bugs_info->bug_title; ?>
                                </div>
                            </div>
                        <?php endif ?>
                        <?php
                        if (!empty($task_details->goal_tracking_id)) :
                            $goal_tracking_info = $this->db->where('goal_tracking_id', $task_details->goal_tracking_id)->get('tbl_goal_tracking')->row();
                        ?>
                            <div class="form-group">
                                <div class="col-sm-4"><strong
                                        class="mr-sm"><?= lang('goal_tracking') ?></strong></div>
                                <div class="col-sm-8">
                                    <?php if (!empty($goal_tracking_info->subject)) echo $goal_tracking_info->subject; ?>
                                </div>
                            </div>
                        <?php endif ?>
                        <?php
                        if (!empty($task_details->sub_task_id)) :
                            $sub_task = $this->db->where('task_id', $task_details->sub_task_id)->get('tbl_task')->row();
                        ?>
                            <div class="form-group">
                                <div class="col-sm-4"><strong
                                        class="mr-sm"><?= lang('sub_tasks') ?></strong>
                                </div>
                                <div class="col-sm-8">
                                    <?php if (!empty($sub_task->task_name)) echo $sub_task->task_name; ?>
                                </div>
                            </div>
                        <?php endif ?>
                        <?php
                        if ($task_details->module == 'domain' && !empty($task_details->module_field_id)) :
                            $domain_info = $this->db->where('id', $task_details->module_field_id)->get('tbldomains')->row();
                        ?>
                            <div class="form-group">
                                <div class="col-sm-4"><strong
                                        class="mr-sm">Domain</strong>
                                </div>
                                <div class="col-sm-8">
                                    <?php if (!empty($domain_info->domain_name)) echo $domain_info->domain_name; ?>
                                </div>
                            </div>
                        <?php endif ?>
                        <?php
                        if ($task_details->module == 'server_hosting' && !empty($task_details->module_field_id)) :
                            $hosting_info = $this->db->where('id', $task_details->module_field_id)->get('tblserver_hostings')->row();
                        ?>
                            <div class="form-group">
                                <div class="col-sm-4"><strong
                                        class="mr-sm">Server Hosting</strong>
                                </div>
                                <div class="col-sm-8">
                                    <?php if (!empty($hosting_info->title)) echo $hosting_info->title; ?>
                                </div>
                            </div>
                        <?php endif ?>

                        <div class="form-group">
                            <div class="col-sm-4"><strong><?= lang('start_date') ?> :</strong></div>
                            <div class="col-sm-8">
                                <?php
                                if (!empty($task_details->task_start_date)) {
                                    echo strftime(config_item('date_format'), strtotime($task_details->task_start_date));
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                        $due_date = $task_details->due_date;
                        $due_time = strtotime($due_date);
                        $current_time = strtotime(date('Y-m-d'));
                        if ($current_time > $due_time && $task_details->task_status != 'completed') {
                            $text = 'text-danger';
                        } else {
                            $text = null;
                        }
                        ?>
                        <div class="form-group">
                            <div class="col-sm-4"><strong class="<?= $text ?>"><?= lang('due_date') ?>
                                    :</strong></div>
                            <div class="col-sm-8 <?= $text ?>">
                                <?php
                                if (!empty($task_details->due_date)) {
                                    echo strftime(config_item('date_format'), strtotime($task_details->due_date));
                                }
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4"><strong><?= lang('task_status') ?>
                                    :</strong></div>
                            <div class="col-sm-8">
                                <?php
                                $disabled = null;
                                if ($task_details->task_status == 'completed') {
                                    $label = 'success';
                                    $disabled = 'disabled';
                                } elseif ($task_details->task_status == 'not_started') {
                                    $label = 'info';
                                } elseif ($task_details->task_status == 'deferred') {
                                    $label = 'danger';
                                } else {
                                    $label = 'warning';
                                }
                                ?>
                                <div class="label label-<?= $label ?>  ">
                                    <?= lang($task_details->task_status) ?></div>
                                <?php
                                ?>
                                <?php if (!empty($can_edit) && !empty($edited)) { ?>
                                    <div class="btn-group">
                                        <button class="btn btn-xs btn-success dropdown-toggle"
                                            data-toggle="dropdown">
                                            <?= lang('change') ?>
                                            <span class="caret"></span></button>
                                        <ul class="dropdown-menu animated zoomIn">
                                            <li>
                                                <a href="<?= base_url() ?>admin/tasks/change_status/<?= $task_details->task_id . '/not_started' ?>"><?= lang('not_started') ?></a>
                                            </li>
                                            <li>
                                                <a href="<?= base_url() ?>admin/tasks/change_status/<?= $task_details->task_id . '/in_progress' ?>"><?= lang('in_progress') ?></a>
                                            </li>
                                            <li>
                                                <a href="<?= base_url() ?>admin/tasks/change_status/<?= $task_details->task_id . '/completed' ?>"><?= lang('completed') ?></a>
                                            </li>
                                            <li>
                                                <a href="<?= base_url() ?>admin/tasks/change_status/<?= $task_details->task_id . '/deferred' ?>"><?= lang('deferred') ?></a>
                                            </li>
                                            <li>
                                                <a href="<?= base_url() ?>admin/tasks/change_status/<?= $task_details->task_id . '/waiting_for_someone' ?>"><?= lang('waiting_for_someone') ?></a>
                                            </li>
                                        </ul>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <?php
                        $priority_label = 'info';
                        if ($task_details->priority == 'medium') {
                            $priority_label = 'primary';
                        } elseif ($task_details->priority == 'high') {
                            $priority_label = 'warning';
                        } elseif ($task_details->priority == 'urgent') {
                            $priority_label = 'danger';
                        }
                        ?>
                        <div class="form-group">
                            <div class="col-sm-4"><strong><?= lang('priority') ?>
                                    :</strong></div>
                            <div class="col-sm-8">
                                <span class="label label-<?= $priority_label ?>"><?= lang($task_details->priority) ?></span>
                            </div>
                        </div>
                        <?php if (!empty($task_details->report_to)) :
                            $report_to_info = $this->db->where('user_id', $task_details->report_to)->get('tbl_account_details')->row();
                        ?>
                            <div class="form-group">
                                <div class="col-sm-4"><strong><?= lang('reporting_to') ?>
                                        :</strong></div>
                                <div class="col-sm-8">
                                    <?= fullname($task_details->report_to) ?>
                                </div>
                            </div>
                        <?php endif ?>
                    </form>
                </div>

                <div class="col-md-3 br">
                    <p class="lead bb"></p>
                    <form class="form-horizontal p-20">
                        <div class="form-group">
                            <div class="col-sm-4"><strong><?= lang('timer_status') ?>:</strong></div>
                            <div class="col-sm-8">
                                <?php
                                $timer_state = $task_details->timer_status;
                                $is_admin = ($this->session->userdata('user_type') == '1');
                                $this_permission = $this->tasks_model->can_action('tbl_task', 'view', array('task_id' => $task_details->task_id), true);
                                
                                if ($timer_state == 'on') { ?>
                                    <span class="label label-success" style="padding: 3px 6px; font-weight: 600;"><i class="fa fa-refresh fa-spin"></i> Running</span>
                                    <?php if (!empty($this_permission)) { ?>
                                        <?php if ($is_admin) { ?>
                                            <a class="btn btn-xs btn-warning" style="margin-left: 4px;"
                                                href="<?= base_url() ?>admin/tasks/tasks_timer/hold/<?= $task_details->task_id ?>"><i class="fa fa-hand-paper-o"></i> Hold</a>
                                        <?php } else { ?>
                                            <a class="btn btn-xs btn-warning" style="margin-left: 4px;"
                                                href="<?= base_url() ?>admin/tasks/tasks_timer/pause/<?= $task_details->task_id ?>"><i class="fa fa-pause"></i> Pause</a>
                                        <?php } ?>
                                        <a class="btn btn-xs btn-danger" style="margin-left: 4px;"
                                            href="<?= base_url() ?>admin/tasks/tasks_timer/off/<?= $task_details->task_id ?>"><i class="fa fa-stop"></i> Stop</a>
                                    <?php }
                                } elseif ($timer_state == 'pause') { ?>
                                    <span class="label label-warning" style="padding: 3px 6px; font-weight: 600;"><i class="fa fa-pause"></i> Paused</span>
                                    <?php if (!empty($this_permission)) { ?>
                                        <a class="btn btn-xs btn-success <?= $disabled ?>" style="margin-left: 4px;"
                                            href="<?= base_url() ?>admin/tasks/tasks_timer/on/<?= $task_details->task_id ?>"><i class="fa fa-play"></i> Resume</a>
                                        <a class="btn btn-xs btn-danger" style="margin-left: 4px;"
                                            href="<?= base_url() ?>admin/tasks/tasks_timer/off/<?= $task_details->task_id ?>"><i class="fa fa-stop"></i> Stop</a>
                                    <?php }
                                } elseif ($timer_state == 'hold') { ?>
                                    <span class="label label-danger" style="background-color: #f39c12; border-color: #e08e0b; padding: 3px 6px; font-weight: 600;"><i class="fa fa-hand-paper-o"></i> On Hold</span>
                                    <?php if (!empty($this_permission)) { ?>
                                        <a class="btn btn-xs btn-success <?= $disabled ?>" style="margin-left: 4px;"
                                            href="<?= base_url() ?>admin/tasks/tasks_timer/on/<?= $task_details->task_id ?>"><i class="fa fa-play"></i> Resume</a>
                                        <a class="btn btn-xs btn-danger" style="margin-left: 4px;"
                                            href="<?= base_url() ?>admin/tasks/tasks_timer/off/<?= $task_details->task_id ?>"><i class="fa fa-stop"></i> Stop</a>
                                    <?php }
                                } else { ?>
                                    <span class="label label-danger" style="padding: 3px 6px; font-weight: 600;">Off</span>
                                    <?php if (!empty($this_permission)) { ?>
                                        <a class="btn btn-xs btn-success <?= $disabled ?>" style="margin-left: 4px;"
                                            href="<?= base_url() ?>admin/tasks/tasks_timer/on/<?= $task_details->task_id ?>"><i class="fa fa-play"></i> Start</a>
                                    <?php }
                                }
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4"><strong><?= lang('project_hourly_rate') ?> :</strong>
                            </div>
                            <div class="col-sm-8">
                                <?php
                                if (!empty($task_details->hourly_rate)) {
                                    echo $task_details->hourly_rate;
                                }
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4"><strong><?= lang('created_by') ?> :</strong></div>
                            <div class="col-sm-8">
                                <?php
                                if (!empty($task_details->created_by)) {
                                    echo fullname($task_details->created_by);
                                }
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4">
                                <small><?= lang('created_date') ?> :</small>
                            </div>
                            <div class="col-sm-8">
                                <?php
                                if (!empty($task_details->due_date)) {
                                    echo strftime(config_item('date_format'), strtotime($task_details->task_created_date)) . ' ' . display_time($task_details->task_created_date);
                                }
                                ?>
                            </div>
                        </div>

                    </form>
                </div>

                <div class="col-md-3 br">
                    <p class="lead bb"></p>
                    <form class="form-horizontal p-20">

                        <?php $show_custom_fields = custom_form_label(3, $task_details->task_id);

                        if (!empty($show_custom_fields)) {
                            foreach ($show_custom_fields as $c_label => $v_fields) {
                                if (!empty($v_fields)) {
                        ?>
                                    <div class="form-group">
                                        <div class="col-sm-4"><strong><?= $c_label ?> :</strong></div>
                                        <div class="col-sm-8">
                                            <?= $v_fields ?>
                                        </div>
                                    </div>
                        <?php }
                            }
                        }
                        ?>

                        <div class="form-group">
                            <div class="col-sm-4"><strong><?= lang('estimated_hour') ?>
                                    :</strong></div>
                            <div class="col-sm-8 ">
                                <?php if (!empty($task_details->task_hour)) echo $task_details->task_hour; ?>
                                <?= lang('hours') ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4"><strong><?= lang('billable') ?>
                                    :</strong></div>
                            <div class="col-sm-8 ">
                                <?php if (!empty($task_details->billable)) {
                                    if ($task_details->billable == 'Yes') {
                                        $billable = 'success';
                                        $text = lang('yes');
                                    } else {
                                        $billable = 'danger';
                                        $text = lang('no');
                                    };
                                } else {
                                    $billable = '';
                                    $text = '-';
                                }; ?>
                                <strong class="label label-<?= $billable ?>">
                                    <?= $text ?>
                                </strong>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4"><strong><?= lang('participants') ?>
                                    :</strong></div>
                            <div class="col-sm-8 ">
                                <?php
                                if ($task_details->permission != 'all') {
                                    $get_permission = json_decode($task_details->permission);
                                    if (is_object($get_permission)) :
                                        foreach ($get_permission as $permission => $v_permission) :
                                            $user_info = $this->db->where(array('user_id' => $permission))->get('tbl_users')->row();
                                            if ($user_info->role_id == 1) {
                                                $label = 'circle-danger';
                                            } else {
                                                $label = 'circle-success';
                                            }
                                            $profile_info = $this->db->where(array('user_id' => $permission))->get('tbl_account_details')->row();
                                ?>


                                            <a href="#" data-toggle="tooltip" data-placement="top"
                                                title="<?= $profile_info->fullname ?>"><img
                                                    src="<?= base_url() . $profile_info->avatar ?>"
                                                    class="img-circle img-xs" alt="">
                                                <span class="custom-permission circle <?= $label ?>  circle-lg"></span>
                                            </a>
                                    <?php
                                        endforeach;
                                    endif;
                                } else { ?><strong><?= lang('everyone') ?></strong>
                                    <i title="<?= lang('permission_for_all') ?>"
                                        class="fa fa-question-circle" data-toggle="tooltip"
                                        data-placement="top"></i>

                                <?php
                                }
                                ?>
                                <?php
                                $can_edit = $this->tasks_model->can_action('tbl_task', 'edit', array('task_id' => $task_details->task_id));
                                if (!empty($can_edit) && !empty($edited)) {
                                ?>
                                    <span data-placement="top" data-toggle="tooltip"
                                        title="<?= lang('add_more') ?>">
                                        <a data-toggle="modal" data-target="#myModal"
                                            href="<?= base_url() ?>admin/tasks/update_users/<?= $task_details->task_id ?>"
                                            class="text-default ml"><i class="fa fa-plus"></i></a>
                                    </span>
                                <?php
                                }
                                ?>

                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-3">
                    <p class="lead bb"></p>
                    <form class="form-horizontal p-20">

                        <?php
                        // $task_time already calculated at top
                        ?>
                        <div id="live_task_timer">
                            <?= $this->tasks_model->get_time_spent_result($task_time) ?>
                        </div>
                        <?php
                        if (!empty($task_details->billable) && $task_details->billable == 'Yes') {
                            $total_time = $task_time / 3600;
                            $total_cost = $total_time * $task_details->hourly_rate;
                            $currency = $this->db->where('code', config_item('default_currency'))->get('tbl_currencies')->row();
                        ?>
                            <h2 class="text-center"><?= lang('total_bill') ?>
                                : <span class="total_bill"><?= display_money($total_cost, $currency->symbol) ?></span></h2>
                        <?php }
                        $estimate_hours = $task_details->task_hour;
                        $percentage = $this->tasks_model->get_estime_time($estimate_hours);

                        if ($task_time < $percentage) {
                            $total_time = $percentage - $task_time;
                            $worked = '<storng style="font-size: 15px;"  class="required worked_status">' . lang('left_works') . '</storng>';
                        } else {
                            $total_time = $task_time - $percentage;
                            $worked = '<storng style="font-size: 15px" class="required worked_status">' . lang('extra_works') . '</storng>';
                        }

                        ?>
                        <div class="text-center">
                            <div class="">
                                <?= $worked ?>
                            </div>
                            <div class="live_remaining_time">
                                <?= $this->tasks_model->get_spent_time($total_time) ?>
                            </div>
                        </div>
                    </form>
                </div>

            </div>

            <div class="row">
                <div class="col-md-6 br ">
                    <p class="lead bb"></p>
                    <form class="form-horizontal p-20">
                        <blockquote style="font-size: 12px;word-wrap: break-word;"><?php
                                                                                    if (!empty($task_details->task_description)) {
                                                                                        echo $task_details->task_description;
                                                                                    }
                                                                                    ?></blockquote>
                    </form>
                </div>
                <div class="col-md-6">
                    <p class="lead bb"></p>
                    <form class="form-horizontal p-20">
                        <div class="col-sm-12">
                            <strong><?= lang('completed') ?>:</strong>
                        </div>
                        <div class="col-sm-12">
                            <?php
                            if ($task_details->task_progress < 49) {
                                $progress = 'progress-bar-danger';
                            } elseif ($task_details->task_progress > 50 && $task_details->task_progress < 99) {
                                $progress = 'progress-bar-primary';
                            } else {
                                $progress = 'progress-bar-success';
                            }
                            ?>
                            <span class="">
                                <div class="mt progress progress-striped ">
                                    <div class="progress-bar <?= $progress ?> " data-toggle="tooltip"
                                        data-original-title="<?= $task_details->task_progress ?>%"
                                        style="width: <?= $task_details->task_progress ?>%"></div>
                                </div>
                            </span>
                        </div>
                    </form>
                </div>
            </div>
        <?php } else { ?>
            <div class="form-group col-sm-6">
                <label class="control-label col-sm-5"><strong><?= lang('task_name') ?>
                        :</strong></label>
                <div class="col-sm-7 ">
                    <p class="form-control-static"><?= ($task_details->task_name) ?></p>
                </div>
            </div>

            <div class="form-group  col-sm-6">
                <label class="control-label col-sm-4"><strong><?= lang('categories') ?>
                        :</strong></label>
                <div class="col-sm-8">
                    <p class="form-control-static"><?php if (!empty($pc_name)) echo $pc_name; ?></p>
                </div>
            </div>
            <div class="form-group  col-sm-6">
                <label class="control-label col-sm-5"><strong><?= lang('tags') ?>
                        :</strong></label>
                <div class="col-sm-7">
                    <p class="form-control-static"><?php
                                                    if (!empty($task_details)) {
                                                        echo get_tags($task_details->tags, true);
                                                    }
                                                    ?></p>
                </div>
            </div>

            <div class="form-group col-sm-6">
                <label class="control-label col-sm-4"><strong><?= lang('task_status') ?>
                        :</strong></label>
                <div class="pull-left mt">
                    <?php
                    $disabled = null;
                    if ($task_details->task_status == 'completed') {
                        $label = 'success';
                        $disabled = 'disabled';
                    } elseif ($task_details->task_status == 'not_started') {
                        $label = 'info';
                    } elseif ($task_details->task_status == 'deferred') {
                        $label = 'danger';
                    } else {
                        $label = 'warning';
                    }
                    ?>
                    <p class="form-control-static label label-<?= $label ?>  ">
                        <?= lang($task_details->task_status) ?></p>
                </div>
                <?php if (!empty($can_edit) && !empty($edited)) { ?>
                    <div class="col-sm-1 mt">
                        <div class="btn-group">
                            <button class="btn btn-xs btn-success dropdown-toggle"
                                data-toggle="dropdown">
                                <?= lang('change') ?>
                                <span class="caret"></span></button>
                            <ul class="dropdown-menu animated zoomIn">
                                <li>
                                    <a href="<?= base_url() ?>admin/tasks/change_status/<?= $task_details->task_id . '/not_started' ?>"><?= lang('not_started') ?></a>
                                </li>
                                <li>
                                    <a href="<?= base_url() ?>admin/tasks/change_status/<?= $task_details->task_id . '/in_progress' ?>"><?= lang('in_progress') ?></a>
                                </li>
                                <li>
                                    <a href="<?= base_url() ?>admin/tasks/change_status/<?= $task_details->task_id . '/completed' ?>"><?= lang('completed') ?></a>
                                </li>
                                <li>
                                    <a href="<?= base_url() ?>admin/tasks/change_status/<?= $task_details->task_id . '/deferred' ?>"><?= lang('deferred') ?></a>
                                </li>
                                <li>
                                    <a href="<?= base_url() ?>admin/tasks/change_status/<?= $task_details->task_id . '/waiting_for_someone' ?>"><?= lang('waiting_for_someone') ?></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="form-group  col-sm-6">
                <label class="control-label col-sm-5"><strong><?= lang('priority') ?>
                        :</strong></label>
                <div class="col-sm-7 mt">
                    <?php
                    $priority_label = 'info';
                    if ($task_details->priority == 'medium') {
                        $priority_label = 'primary';
                    } elseif ($task_details->priority == 'high') {
                        $priority_label = 'warning';
                    } elseif ($task_details->priority == 'urgent') {
                        $priority_label = 'danger';
                    }
                    ?>
                    <span class="label label-<?= $priority_label ?>"><?= lang($task_details->priority) ?></span>
                </div>
            </div>
            <div class="form-group  col-sm-6">
                <label class="control-label col-sm-4"><strong><?= lang('reporting_to') ?>
                        :</strong></label>
                <div class="col-sm-8 ">
                    <p class="form-control-static"><?php
                                                    if (!empty($task_details->report_to)) {
                                                        $report_to_info = $this->db->where('user_id', $task_details->report_to)->get('tbl_account_details')->row();
                                                        echo (!empty($report_to_info->fullname) ? $report_to_info->fullname : '-');
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?></p>
                </div>
            </div>
            <div class="form-group  col-sm-6">
                <label class="control-label col-sm-4"><strong><?= lang('timer_status') ?>
                        :</strong></label>
                <div class="col-sm-8 mt">
                    <?php
                    $timer_state = $task_details->timer_status;
                    $is_admin = ($this->session->userdata('user_type') == '1');
                    $this_permission = $this->tasks_model->can_action('tbl_task', 'view', array('task_id' => $task_details->task_id), true);
                    
                    if ($timer_state == 'on') { ?>
                        <span class="label label-success" style="padding: 3px 6px; font-weight: 600;"><i class="fa fa-refresh fa-spin"></i> Running</span>
                        <?php if (!empty($this_permission)) { ?>
                            <?php if ($is_admin) { ?>
                                <a class="btn btn-xs btn-warning" style="margin-left: 4px;"
                                    href="<?= base_url() ?>admin/tasks/tasks_timer/hold/<?= $task_details->task_id ?>"><i class="fa fa-hand-paper-o"></i> Hold</a>
                            <?php } else { ?>
                                <a class="btn btn-xs btn-warning" style="margin-left: 4px;"
                                    href="<?= base_url() ?>admin/tasks/tasks_timer/pause/<?= $task_details->task_id ?>"><i class="fa fa-pause"></i> Pause</a>
                            <?php } ?>
                            <a class="btn btn-xs btn-danger" style="margin-left: 4px;"
                                href="<?= base_url() ?>admin/tasks/tasks_timer/off/<?= $task_details->task_id ?>"><i class="fa fa-stop"></i> Stop</a>
                        <?php }
                    } elseif ($timer_state == 'pause') { ?>
                        <span class="label label-warning" style="padding: 3px 6px; font-weight: 600;"><i class="fa fa-pause"></i> Paused</span>
                        <?php if (!empty($this_permission)) { ?>
                            <a class="btn btn-xs btn-success <?= $disabled ?>" style="margin-left: 4px;"
                                href="<?= base_url() ?>admin/tasks/tasks_timer/on/<?= $task_details->task_id ?>"><i class="fa fa-play"></i> Resume</a>
                            <a class="btn btn-xs btn-danger" style="margin-left: 4px;"
                                href="<?= base_url() ?>admin/tasks/tasks_timer/off/<?= $task_details->task_id ?>"><i class="fa fa-stop"></i> Stop</a>
                        <?php }
                    } elseif ($timer_state == 'hold') { ?>
                        <span class="label label-danger" style="background-color: #f39c12; border-color: #e08e0b; padding: 3px 6px; font-weight: 600;"><i class="fa fa-hand-paper-o"></i> On Hold</span>
                        <?php if (!empty($this_permission)) { ?>
                            <a class="btn btn-xs btn-success <?= $disabled ?>" style="margin-left: 4px;"
                                href="<?= base_url() ?>admin/tasks/tasks_timer/on/<?= $task_details->task_id ?>"><i class="fa fa-play"></i> Resume</a>
                            <a class="btn btn-xs btn-danger" style="margin-left: 4px;"
                                href="<?= base_url() ?>admin/tasks/tasks_timer/off/<?= $task_details->task_id ?>"><i class="fa fa-stop"></i> Stop</a>
                        <?php }
                    } else { ?>
                        <span class="label label-danger" style="padding: 3px 6px; font-weight: 600;">Off</span>
                        <?php if (!empty($this_permission)) { ?>
                            <a class="btn btn-xs btn-success <?= $disabled ?>" style="margin-left: 4px;"
                                href="<?= base_url() ?>admin/tasks/tasks_timer/on/<?= $task_details->task_id ?>"><i class="fa fa-play"></i> Start</a>
                        <?php }
                    }
                    ?>
                </div>
            </div>


            <?php
            if (!empty($task_details->project_id)) :
                $project_info = $this->db->where('project_id', $task_details->project_id)->get('tbl_project')->row();
                $milestones_info = $this->db->where('milestones_id', $task_details->milestones_id)->get('tbl_milestones')->row();
            ?>
                <div class="form-group  col-sm-6">
                    <label class="control-label col-sm-5"><strong><?= lang('project_name') ?>
                            :</strong></label>
                    <div class="col-sm-7 ">
                        <p class="form-control-static">
                            <?php if (!empty($project_info->project_name)) echo $project_info->project_name; ?>
                        </p>
                    </div>
                </div>
                <div class="form-group  col-sm-6">
                    <label class="control-label col-sm-4"><strong><?= lang('milestone') ?>
                            :</strong></label>
                    <div class="col-sm-8 ">
                        <p class="form-control-static">
                            <?php if (!empty($milestones_info->milestone_name)) echo $milestones_info->milestone_name; ?>
                        </p>
                    </div>
                </div>
            <?php endif ?>
            <?php
            if (!empty($task_details->opportunities_id)) :
                $opportunity_info = $this->db->where('opportunities_id', $task_details->opportunities_id)->get('tbl_opportunities')->row();
            ?>
                <div class="form-group  col-sm-10">
                    <label class="control-label col-sm-3 "><strong
                            class="mr-sm"><?= lang('opportunity_name') ?></strong></label>
                    <div class="col-sm-8 " style="margin-left: -5px;">
                        <p class="form-control-static">
                            <?php if (!empty($opportunity_info->opportunity_name)) echo $opportunity_info->opportunity_name; ?>
                        </p>
                    </div>
                </div>
            <?php endif ?>

            <?php
            if (!empty($task_details->leads_id)) :
                $leads_info = $this->db->where('leads_id', $task_details->leads_id)->get('tbl_leads')->row();
            ?>
                <div class="form-group  col-sm-10">
                    <label class="control-label col-sm-3 "><strong
                            class="mr-sm"><?= lang('leads_name') ?></strong></label>
                    <div class="col-sm-8 " style="margin-left: -5px;">
                        <p class="form-control-static">
                            <?php if (!empty($leads_info->lead_name)) echo $leads_info->lead_name; ?></p>
                    </div>
                </div>
            <?php endif ?>

            <?php
            if (!empty($task_details->bug_id)) :
                $bugs_info = $this->db->where('bug_id', $task_details->bug_id)->get('tbl_bug')->row();
            ?>
                <div class="form-group  col-sm-10">
                    <label class="control-label col-sm-3 "><strong
                            class="mr-sm"><?= lang('bug_title') ?></strong></label>
                    <div class="col-sm-8 " style="margin-left: -5px;">
                        <p class="form-control-static">
                            <?php if (!empty($bugs_info->bug_title)) echo $bugs_info->bug_title; ?></p>
                    </div>
                </div>
            <?php endif ?>
            <?php
            if (!empty($task_details->goal_tracking_id)) :
                $goal_tracking_info = $this->db->where('goal_tracking_id', $task_details->goal_tracking_id)->get('tbl_goal_tracking')->row();
            ?>
                <div class="form-group  col-sm-10">
                    <label class="control-label col-sm-3 "><strong
                            class="mr-sm"><?= lang('goal_tracking') ?></strong></label>
                    <div class="col-sm-8 " style="margin-left: -5px;">
                        <p class="form-control-static">
                            <?php if (!empty($goal_tracking_info->subject)) echo $goal_tracking_info->subject; ?>
                        </p>
                    </div>
                </div>
            <?php endif ?>
            <?php
            if (!empty($task_details->sub_task_id)) :
                $sub_task = $this->db->where('task_id', $task_details->sub_task_id)->get('tbl_task')->row();
            ?>
                <div class="form-group  col-sm-10">
                    <label class="control-label col-sm-3 "><strong
                            class="mr-sm"><?= lang('sub_tasks') ?></strong></label>
                    <div class="col-sm-8 " style="margin-left: -5px;">
                        <p class="form-control-static">
                            <?php if (!empty($sub_task->task_name)) echo $sub_task->task_name; ?></p>
                    </div>
                </div>
            <?php endif ?>
            <?php
            if ($task_details->module == 'domain' && !empty($task_details->module_field_id)) :
                $domain_info = $this->db->where('id', $task_details->module_field_id)->get('tbldomains')->row();
            ?>
                <div class="form-group  col-sm-10">
                    <label class="control-label col-sm-3 "><strong
                            class="mr-sm">Domain</strong></label>
                    <div class="col-sm-8 " style="margin-left: -5px;">
                        <p class="form-control-static">
                            <?php if (!empty($domain_info->domain_name)) echo $domain_info->domain_name; ?></p>
                    </div>
                </div>
            <?php endif ?>
            <?php
            if ($task_details->module == 'server_hosting' && !empty($task_details->module_field_id)) :
                $hosting_info = $this->db->where('id', $task_details->module_field_id)->get('tblserver_hostings')->row();
            ?>
                <div class="form-group  col-sm-10">
                    <label class="control-label col-sm-3 "><strong
                            class="mr-sm">Server Hosting</strong></label>
                    <div class="col-sm-8 " style="margin-left: -5px;">
                        <p class="form-control-static">
                            <?php if (!empty($hosting_info->title)) echo $hosting_info->title; ?></p>
                    </div>
                </div>
            <?php endif ?>
            <div class="form-group  col-sm-6">
                <label class="control-label col-sm-5"><strong><?= lang('start_date') ?>
                        :</strong></label>
                <div class="col-sm-7 ">
                    <p class="form-control-static"><?php
                                                    if (!empty($task_details->task_start_date)) {
                                                        echo strftime(config_item('date_format'), strtotime($task_details->task_start_date));
                                                    }
                                                    ?></p>
                </div>
            </div>
            <div class="form-group  col-sm-6">
                <?php
                $due_date = $task_details->due_date;
                $due_time = strtotime($due_date);
                $current_time = strtotime(date('Y-m-d'));
                if ($current_time > $due_time) {
                    $text = 'text-danger';
                } else {
                    $text = null;
                }
                ?>

                <label class="control-label col-sm-4"><strong
                        class="<?= $text ?>"><?= lang('due_date') ?>
                        :</strong></label>
                <div class="col-sm-8 ">
                    <p class="form-control-static"><?php
                                                    if (!empty($task_details->due_date)) {
                                                        echo strftime(config_item('date_format'), strtotime($task_details->due_date));
                                                    }
                                                    ?></p>

                </div>
            </div>
            <div class="form-group  col-sm-6">
                <label class="control-label col-sm-5"><strong><?= lang('created_by') ?>
                        :</strong></label>
                <div class="col-sm-7 ">
                    <p class="form-control-static"><?php
                                                    if (!empty($task_details->created_by)) {
                                                        echo $this->db->where('user_id', $task_details->created_by)->get('tbl_account_details')->row()->fullname;
                                                    }
                                                    ?></p>

                </div>
            </div>
            <div class="form-group  col-sm-6">
                <label class="control-label col-sm-4"><strong><?= lang('created_date') ?>
                        :</strong></label>
                <div class="col-sm-8 ">
                    <p class="form-control-static"><?php
                                                    if (!empty($task_details->due_date)) {
                                                        echo strftime(config_item('date_format'), strtotime($task_details->task_created_date));
                                                    }
                                                    ?></p>

                </div>
            </div>
            <div class="form-group  col-sm-6">
                <label class="control-label col-sm-5"><strong><?= lang('project_hourly_rate') ?>
                        :</strong></label>
                <div class="col-sm-7 ">
                    <p class="form-control-static"><?php
                                                    if (!empty($task_details->hourly_rate)) {
                                                        echo $task_details->hourly_rate;
                                                    }
                                                    ?></p>
                </div>
            </div>

            <?php $show_custom_fields = custom_form_label(3, $task_details->task_id);

            if (!empty($show_custom_fields)) {
                foreach ($show_custom_fields as $c_label => $v_fields) {
                    if (!empty($v_fields)) {
                        if (count(array($v_fields)) == 1) {
                            $col = 'col-sm-10';
                            $sub_col = 'col-sm-3';
                            $style = 'padding-left:8px';
                        } else {
                            $col = 'col-sm-6';
                            $sub_col = 'col-sm-5';
                            $style = null;
                        }

            ?>
                        <div class="form-group  <?= $col ?>" style="<?= $style ?>">
                            <label class="control-label <?= $sub_col ?>"><strong><?= $c_label ?>
                                    :</strong></label>
                            <div class="col-sm-7 ">
                                <p class="form-control-static">
                                    <strong><?= $v_fields ?></strong>
                                </p>
                            </div>
                        </div>
            <?php }
                }
            }
            ?>
            <div class="form-group  col-sm-6">
                <label class="control-label col-sm-5"><strong><?= lang('estimated_hour') ?>
                        :</strong></label>
                <div class="col-sm-7 ">
                    <p class="form-control-static">
                        <strong><?php if (!empty($task_details->task_hour)) echo $task_details->task_hour; ?>
                            <?= lang('hours') ?></strong>
                    </p>
                </div>
            </div>
            <div class="form-group  col-sm-6">
                <label class="control-label col-sm-5"><strong><?= lang('billable') ?>
                        :</strong></label>
                <div class="col-sm-7 ">
                    <p class="form-control-static">
                        <?php if (!empty($task_details->billable)) {
                            if ($task_details->billable == 'Yes') {
                                $billable = 'success';
                                $text = lang('yes');
                            } else {
                                $billable = 'danger';
                                $text = lang('no');
                            };
                        } else {
                            $billable = '';
                            $text = '-';
                        }; ?>
                        <strong class="label label-<?= $billable ?>">
                            <?= $text ?>
                        </strong>
                    </p>
                </div>
            </div>
            <div class="form-group  col-sm-6">
                <label class="control-label col-sm-4"><strong><?= lang('participants') ?>
                        :</strong></label>
                <div class="col-sm-8 ">
                    <?php
                    if (!empty($task_details->permission) && $task_details->permission != 'all') {
                        $get_permission = json_decode($task_details->permission);
                        if (is_object($get_permission) && !empty($get_permission)) :
                            foreach ($get_permission as $permission => $v_permission) :
                                $user_info = $this->db->where(array('user_id' => $permission))->get('tbl_users')->row();
                                if (!empty($user_info)) {
                                    if ($user_info->role_id == 1) {
                                        $label = 'circle-danger';
                                    } else {
                                        $label = 'circle-success';
                                    }
                                    $profile_info = $this->db->where(array('user_id' => $permission))->get('tbl_account_details')->row();
                    ?>


                                    <a href="#" data-toggle="tooltip" data-placement="top"
                                        title="<?= $profile_info->fullname ?>"><img
                                            src="<?= base_url() . $profile_info->avatar ?>"
                                            class="img-circle img-xs" alt="">
                                        <span class="custom-permission circle <?= $label ?>  circle-lg"></span>
                                    </a>
                        <?php
                                }
                            endforeach;
                        endif;
                    } else { ?>
                        <p class="form-control-static"><strong><?= lang('everyone') ?></strong>
                            <i title="<?= lang('permission_for_all') ?>" class="fa fa-question-circle"
                                data-toggle="tooltip" data-placement="top"></i>

                        <?php
                    }
                        ?>
                        <?php
                        $can_edit = $this->tasks_model->can_action('tbl_task', 'edit', array('task_id' => $task_details->task_id));
                        if (!empty($can_edit) && !empty($edited)) {
                        ?>
                            <span data-placement="top" data-toggle="tooltip"
                                title="<?= lang('add_more') ?>">
                                <a data-toggle="modal" data-target="#myModal"
                                    href="<?= base_url() ?>admin/tasks/update_users/<?= $task_details->task_id ?>"
                                    class="text-default ml"><i class="fa fa-plus"></i></a>
                            </span>
                        </p>
                    <?php
                        }
                    ?>

                </div>

                

            <?php } ?>

            </div>
            <?php if (!empty($task_details->task_description)) : ?>
                    <div class="form-group col-sm-12" style="margin-top: 15px; margin-bottom: 15px;">
                        <div class="col-sm-12">
                            <div style="background: #f9f9f9; border-left: 4px solid #5d9cec; padding: 15px; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                <label style="display: block; margin-bottom: 10px; color: #5d9cec; font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">
                                    <i class="fa fa-align-left mr-sm"></i> <?= lang('description') ?>
                                </label>
                                <div style="font-size: 14px; line-height: 1.6; color: #444; word-wrap: break-word;">
                                    <?= $task_details->task_description ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-group  col-sm-10">
                    <label class="control-label col-sm-3 "><strong class="mr-sm"><?= lang('completed') ?>
                            :</strong></label>
                    <div class="col-sm-9 " style="margin-left: -5px;">
                        <?php
                        if ($task_details->task_progress < 49) {
                            $progress = 'progress-bar-danger';
                        } elseif ($task_details->task_progress > 50 && $task_details->task_progress < 99) {
                            $progress = 'progress-bar-primary';
                        } else {
                            $progress = 'progress-bar-success';
                        }
                        ?>
                        <span class="">
                            <div class="mt progress progress-striped ">
                                <div class="progress-bar <?= $progress ?> " data-toggle="tooltip"
                                    data-original-title="<?= $task_details->task_progress ?>%"
                                    style="width: <?= $task_details->task_progress ?>%"></div>
                            </div>
                        </span>
                    </div>
                </div>
                
                <div class="form-group col-sm-12">
                    <?php
                    // $task_time already calculated at top
                    ?>
                    <div id="live_task_timer_2">
                        <?= $this->tasks_model->get_time_spent_result($task_time) ?>
                    </div>
                    <?php
                    if (!empty($task_details->billable) && $task_details->billable == 'Yes') {
                        $total_time = $task_time / 3600;
                        $total_cost = $total_time * $task_details->hourly_rate;
                        $currency = $this->db->where('code', config_item('default_currency'))->get('tbl_currencies')->row();
                    ?>
                        <h2 class="text-center"><?= lang('total_bill') ?>
                            : <span class="total_bill"><?= display_money($total_cost, $currency->symbol) ?></span></h2>
                    <?php }
                    $estimate_hours = $task_details->task_hour;
                    $percentage = $this->tasks_model->get_estime_time($estimate_hours);

                    if ($task_time < $percentage) {
                        $total_time = $percentage - $task_time;
                        $worked = '<storng style="font-size: 15px;"  class="required worked_status">' . lang('left_works') . '</storng>';
                    } else {
                        $total_time = $task_time - $percentage;
                        $worked = '<storng style="font-size: 15px" class="required worked_status">' . lang('extra_works') . '</storng>';
                    }

                    ?>
                    <div class="text-center">
                        <div class="">
                            <?= $worked ?>
                        </div>
                        <div class="live_remaining_time">
                            <?= $this->tasks_model->get_spent_time($total_time) ?>
                        </div>
                    </div>

                </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            <?php if ($task_details->timer_status == 'on') : ?>
                var timer_seconds = <?= $task_time ?>;
                var percentage = <?= $percentage ?>;
                var hourly_rate = <?= !empty($task_details->hourly_rate) ? $task_details->hourly_rate : 0 ?>;
                var currency_symbol = '<?= !empty($currency->symbol) ? $currency->symbol : '$' ?>';
                var lang_left_works = '<?= lang('left_works') ?>';
                var lang_extra_works = '<?= lang('extra_works') ?>';
                var lang_hours = '<?= lang('hours') ?>';
                var lang_minutes = '<?= lang('minutes') ?>';
                var lang_seconds = '<?= lang('seconds') ?>';

                var timer_interval = setInterval(function() {
                    timer_seconds++;

                    // Update Spent Time
                    var hours = Math.floor(timer_seconds / 3600);
                    var minutes = Math.floor((timer_seconds % 3600) / 60);
                    var seconds = timer_seconds % 60;

                    $('.timer').each(function() {
                        var $this = $(this);
                        $this.find('li:eq(0)').contents().first()[0].textContent = hours;
                        $this.find('li:eq(2)').contents().first()[0].textContent = minutes;
                        $this.find('li:eq(4)').contents().first()[0].textContent = seconds;
                    });

                    // Update Total Bill
                    if (hourly_rate > 0) {
                        var total_cost = (timer_seconds / 3600) * hourly_rate;
                        $('.total_bill').text(currency_symbol + total_cost.toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                    }

                    // Update Remaining/Extra Time
                    var total_time;
                    if (timer_seconds < percentage) {
                        total_time = percentage - timer_seconds;
                        $('.worked_status').text(lang_left_works);
                    } else {
                        total_time = timer_seconds - percentage;
                        $('.worked_status').text(lang_extra_works);
                    }

                    var r_hours = Math.floor(total_time / 3600);
                    var r_minutes = Math.floor((total_time % 3600) / 60);
                    var r_seconds = total_time % 60;

                    $('.live_remaining_time').each(function() {
                        $(this).html(r_hours + ' <strong> ' + lang_hours + ' </strong> : ' + r_minutes + ' <strong> ' + lang_minutes + ' </strong> : ' + r_seconds + ' <strong> ' + lang_seconds + ' </strong>');
                    });

                }, 1000);
            <?php endif; ?>
        });
    </script>