<?php

$my_task = $this->admin_model->my_permission('tbl_task', $this->session->userdata('user_id'));
$prio_map = array(
    'low' => '#10b981',
    'medium' => '#f59e0b',
    'high' => '#8b5cf6',
    'urgent' => '#dc2626',
);
?>

<div class="panel panel-custom menu">
    <header class="panel-heading mb0">
        <h3 class="panel-title"><?= lang('my_tasks') ?><span class="sub">Assigned to me</span></h3>
    </header>
    <div class="rowlist">
        <?php
        if (!empty($my_task)):
            foreach ($my_task as $v_my_task):
                if ($v_my_task->task_status == 'not_started' || $v_my_task->task_status == 'in_progress' || $v_my_task->task_progress < 100) {
                    $due_ts = strtotime($v_my_task->due_date);
                    $day_diff = (int)floor(($due_ts - strtotime(date('Y-m-d'))) / 86400);
                    $is_late = ($day_diff < 0 && $v_my_task->task_progress < 100);

                    if ($day_diff === 0) {
                        $due_label = 'Today';
                    } elseif ($day_diff === 1) {
                        $due_label = 'Tomorrow';
                    } elseif ($day_diff === -1) {
                        $due_label = 'Yesterday';
                    } elseif ($day_diff < -1) {
                        $due_label = lang('overdue');
                    } elseif ($day_diff <= 6) {
                        $due_label = date('D', $due_ts);
                    } else {
                        $due_label = strftime(config_item('date_format'), $due_ts);
                    }

                    $priority = isset($v_my_task->priority) ? $v_my_task->priority : null;
                    $dot = (is_string($priority) && isset($prio_map[$priority])) ? $prio_map[$priority] : '#94a3b8';
                    ?>
                    <div class="todo">
                        <span class="prio" style="background: <?= $dot ?>"></span>
                        <a class="tt" href="<?= base_url() ?>admin/tasks/details/<?= $v_my_task->task_id ?>"><?= $v_my_task->task_name ?></a>
                        <span class="due-lbl<?= $is_late ? ' late' : '' ?>"><?= $due_label ?></span>
                    </div>
                    <?php
                }
            endforeach;
        else:
            ?>
            <div class="empty"><?= lang('no_data') ?></div>
            <?php
        endif;
        ?>
    </div>
</div>
