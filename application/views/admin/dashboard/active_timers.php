<div class="panel panel-custom menu" style="height: 437px;overflow-y: scroll;">
    <header class="panel-heading mb0">
        <h3 class="panel-title"><?= lang('active_timers') ?></h3>
    </header>
    <div class="table-responsive">
        <table class="table table-striped m-b-none text-sm">
            <thead>
            <tr>
                <th><?= lang('task_name') ?> / <?= lang('project_name') ?></th>
                <th><?= lang('start_time') ?></th>
                <th><?= lang('action') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (!empty($active_timers)) {
                foreach ($active_timers as $v_timer): 
                    if (!empty($v_timer->task_id)) {
                        $item_info = get_row('tbl_task', array('task_id' => $v_timer->task_id));
                        if (!empty($item_info)) {
                            $name = $item_info->task_name;
                            $link = base_url() . 'admin/tasks/details/' . $v_timer->task_id;
                            $stop_link = base_url() . 'admin/tasks/tasks_timer/off/' . $v_timer->task_id;
                        }
                    } elseif (!empty($v_timer->project_id)) {
                        $item_info = get_row('tbl_project', array('project_id' => $v_timer->project_id));
                        if (!empty($item_info)) {
                            $name = $item_info->project_name;
                            $link = base_url() . 'admin/projects/project_details/' . $v_timer->project_id;
                            $stop_link = base_url() . 'admin/projects/tasks_timer/off/' . $v_timer->project_id;
                        }
                    }
                    if (!empty($item_info)) {
                    ?>
                    <tr>
                        <td>
                            <a class="text-info" href="<?= $link ?>"><?= $name ?></a>
                        </td>
                        <td><?= display_datetime($v_timer->start_time, true) ?></td>
                        <td>
                            <a class="btn btn-xs btn-danger" href="<?= $stop_link ?>"><?= lang('stop_timer') ?></a>
                        </td>
                    </tr>
                <?php
                    }
                endforeach;
            } else {
                ?>
                <tr>
                    <td colspan="3"><?= lang('no_timer_found') ?></td>
                </tr>
            <?php
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
