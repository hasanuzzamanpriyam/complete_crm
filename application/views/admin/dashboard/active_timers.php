<div class="panel panel-custom menu" style="height: 437px;overflow-y: scroll;">
    <header class="panel-heading mb0">
        <h3 class="panel-title"><?= lang('active_timers') ?></h3>
    </header>
    <div class="table-responsive">
        <table class="table table-striped m-b-none text-sm">
            <thead>
            <tr>
                <th><?= lang('task_name') ?> / <?= lang('project_name') ?></th>
                <?php if ($this->session->userdata('user_type') == '1') { ?>
                    <th><?= lang('user') ?></th>
                <?php } ?>
                <th><?= lang('start_time') ?></th>
                <th>Status</th>
                <th style="width: 150px;"><?= lang('action') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (!empty($active_timers)) {
                foreach ($active_timers as $v_timer):
                    $buttons = '';
                    $status_badge = '';
                    if (!empty($v_timer->task_id)) {
                        $item_info = get_row('tbl_task', array('task_id' => $v_timer->task_id));
                        if (!empty($item_info)) {
                            $name = $item_info->task_name;
                            $link = base_url() . 'admin/tasks/details/' . $v_timer->task_id;
                            
                            $status = $v_timer->timer_status;
                            $is_admin = ($this->session->userdata('user_type') == '1');
                            
                            // Build status badge
                            if ($status == 'on') {
                                $status_badge = '<span class="label label-success" style="padding: 4px 8px; font-weight: 600;"><i class="fa fa-refresh fa-spin"></i> Running</span>';
                            } elseif ($status == 'pause') {
                                $status_badge = '<span class="label label-warning" style="padding: 4px 8px; font-weight: 600;"><i class="fa fa-pause"></i> Paused</span>';
                            } elseif ($status == 'hold') {
                                $status_badge = '<span class="label label-danger" style="background-color: #f39c12; border-color: #e08e0b; padding: 4px 8px; font-weight: 600;"><i class="fa fa-hand-paper-o"></i> On Hold</span>';
                            } else {
                                $status_badge = '<span class="label label-default" style="padding: 4px 8px; font-weight: 600;">' . ucfirst($status) . '</span>';
                            }

                            // Build buttons
                            if ($status == 'on') {
                                if ($is_admin) {
                                    $hold_link = base_url() . 'admin/tasks/tasks_timer/hold/' . $v_timer->task_id;
                                    $buttons .= '<a class="btn btn-xs btn-warning" style="margin-right: 4px;" href="' . $hold_link . '" title="Hold Timer"><i class="fa fa-hand-paper-o"></i> Hold</a> ';
                                } else {
                                    $pause_link = base_url() . 'admin/tasks/tasks_timer/pause/' . $v_timer->task_id;
                                    $buttons .= '<a class="btn btn-xs btn-warning" style="margin-right: 4px;" href="' . $pause_link . '" title="Pause Timer"><i class="fa fa-pause"></i> Pause</a> ';
                                }
                                $stop_link = base_url() . 'admin/tasks/tasks_timer/off/' . $v_timer->task_id;
                                $buttons .= '<a class="btn btn-xs btn-danger" href="' . $stop_link . '" title="Stop Timer"><i class="fa fa-stop"></i> Stop</a>';
                            } else {
                                $resume_link = base_url() . 'admin/tasks/tasks_timer/on/' . $v_timer->task_id;
                                $buttons .= '<a class="btn btn-xs btn-success" style="margin-right: 4px;" href="' . $resume_link . '" title="Resume Timer"><i class="fa fa-play"></i> Resume</a> ';
                                
                                $stop_link = base_url() . 'admin/tasks/tasks_timer/off/' . $v_timer->task_id;
                                $buttons .= '<a class="btn btn-xs btn-danger" href="' . $stop_link . '" title="Stop Timer"><i class="fa fa-stop"></i> Stop</a>';
                            }
                        }
                    } elseif (!empty($v_timer->project_id)) {
                        $item_info = get_row('tbl_project', array('project_id' => $v_timer->project_id));
                        if (!empty($item_info)) {
                            $name = $item_info->project_name;
                            $link = base_url() . 'admin/projects/project_details/' . $v_timer->project_id;
                            
                            $status = $v_timer->timer_status;
                            if ($status == 'on') {
                                $status_badge = '<span class="label label-success" style="padding: 4px 8px; font-weight: 600;"><i class="fa fa-refresh fa-spin"></i> Running</span>';
                            } else {
                                $status_badge = '<span class="label label-default" style="padding: 4px 8px; font-weight: 600;">' . ucfirst($status) . '</span>';
                            }

                            $stop_link = base_url() . 'admin/projects/tasks_timer/off/' . $v_timer->project_id;
                            $buttons = '<a class="btn btn-xs btn-danger" href="' . $stop_link . '"><i class="fa fa-stop"></i> Stop</a>';
                        }
                    }
                    if (!empty($item_info)) {
                        ?>
                        <tr>
                            <td style="vertical-align: middle;">
                                <a class="text-info" style="font-weight: 600;" href="<?= $link ?>"><?= $name ?></a>
                            </td>
                            <?php if ($this->session->userdata('user_type') == '1') { ?>
                                <td style="vertical-align: middle;"><?= $v_timer->fullname ?></td>
                            <?php } ?>
                            <td style="vertical-align: middle;"><?= display_datetime($v_timer->start_time, true) ?></td>
                            <td style="vertical-align: middle;"><?= $status_badge ?></td>
                            <td style="vertical-align: middle;">
                                <?= $buttons ?>
                            </td>
                        </tr>
                        <?php
                    }
                endforeach;
            } else {
                ?>
                <tr>
                    <td colspan="<?= ($this->session->userdata('user_type') == '1') ? '5' : '4' ?>"><?= lang('no_timer_found') ?></td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
