<div id="panelChart4" class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title"><?= lang('total') . ' ' . lang('task') . ' ' . lang('time_spent') ?></div>
    </div>
    <div class="panel-body">
        <div class="form-group col-sm-12">
            <?php
            $tasks_info = $this->user_model->my_permission('tbl_task', $profile_info->user_id);
            if (!empty($tasks_info)) {
                foreach ($tasks_info as $key => $v_tasks) {
                    $assigned = false;
                    if ($v_tasks->permission != 'all') {
                        $decoded = json_decode($v_tasks->permission, true);
                        if (is_array($decoded) && isset($decoded[$profile_info->user_id])) {
                            $assigned = true;
                        }
                    }
                    if (!$assigned) {
                        unset($tasks_info[$key]);
                    }
                }
            }
            $task_time = 0;
            $task_time = $this->user_model->my_spent_time($profile_info->user_id);
            if (!empty($tasks_info)) {
                foreach ($tasks_info as $v_u_tasks) {
                }
            }
            echo $this->user_model->get_time_spent_result($task_time)
            ?>
        </div>
    </div>
</div>
<div id="panelChart5" class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title"><?= lang('task') . ' ' . lang('report') ?></div>
    </div>
    <div class="panel-body">
        <div class="chart-pie flot-chart"></div>
    </div>
</div>
<?php

$not_started = 0;
$in_progress = 0;
$completed = 0;
$deferred = 0;
$waiting_for_someone = 0;

if (!empty($tasks_info)):foreach ($tasks_info as $v_tasks):
    if ($v_tasks->task_status == 'not_started') {
        $not_started += count(array($v_tasks->task_status));
    }
    if ($v_tasks->task_status == 'in_progress') {
        $in_progress += count(array($v_tasks->task_status));
    }
    if ($v_tasks->task_status == 'completed') {
        $completed += count(array($v_tasks->task_status));
    }
    if ($v_tasks->task_status == 'deferred') {
        $deferred += count(array($v_tasks->task_status));
    }
    if ($v_tasks->task_status == 'waiting_for_someone') {
        $waiting_for_someone += count(array($v_tasks->task_status));
    }
endforeach;
endif;
?>
<script src="<?= base_url() ?>assets/plugins/Flot/jquery.flot.js"></script>
<script src="<?= base_url() ?>assets/plugins/Flot/jquery.flot.tooltip.min.js"></script>
<script src="<?= base_url() ?>assets/plugins/Flot/jquery.flot.resize.js"></script>
<script src="<?= base_url() ?>assets/plugins/Flot/jquery.flot.pie.js"></script>
<script src="<?= base_url() ?>assets/plugins/Flot/jquery.flot.time.js"></script>
<script src="<?= base_url() ?>assets/plugins/Flot/jquery.flot.categories.js"></script>
<script src="<?= base_url() ?>assets/plugins/Flot/jquery.flot.spline.min.js"></script>
<?php if (!empty($not_started) || !empty($in_progress) || !empty($completed) || !empty($deferred) || !empty($waiting_for_someone)) { ?>
    <script type="text/javascript">
        $(document).ready(function () {
            // CHART PIE
            // -----------------------------------
            (function (window, document, $, undefined) {

                $(function () {

                    var data = [{
                        "label": "<?= lang('not_started')?>",
                        "color": "#23b7e5",
                        "data": <?= $not_started?>
                    }, {
                        "label": "<?= lang('in_progress')?>",
                        "color": "#ff902b",
                        "data": <?= $in_progress?>
                    }, {
                        "label": "<?= lang('completed')?>",
                        "color": "#27c24c",
                        "data": <?= $completed?>
                    }, {
                        "label": "<?= lang('deferred')?>",
                        "color": "#f05050",
                        "data": <?= $deferred?>
                    }, {
                        "label": "<?= lang('waiting_for_someone')?>",
                        "color": "#ff902b",
                        "data": <?= $waiting_for_someone?>
                    },];

                    var options = {
                        series: {
                            pie: {
                                show: true,
                                innerRadius: 0,
                                label: {
                                    show: true,
                                    radius: 0.8,
                                    formatter: function (label, series) {
                                        return '<div class="flot-pie-label">' +
                                                //label + ' : ' +
                                            Math.round(series.percent) +
                                            '%</div>';
                                    },
                                    background: {
                                        opacity: 0.8,
                                        color: '#222'
                                    }
                                }
                            }
                        }
                    };

                    var chart = $('.chart-pie');
                    if (chart.length)
                        $.plot(chart, data, options);

                });

            })(window, document, window.jQuery);

        });

    </script>
<?php } ?>

<div class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title"><strong><?= lang('all') . ' ' . lang('tasks') ?></strong></div>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-striped" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th><?= lang('task_name') ?></th>
                        <th><?= lang('due_date') ?></th>
                        <th><?= lang('progress') ?></th>
                        <th><?= lang('status') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tasks_info)) : foreach ($tasks_info as $v_task) : ?>
                        <tr>
                            <td>
                                <a class="text-info" style="<?php if ($v_task->task_status == 'completed') { echo 'text-decoration: line-through;'; } ?>" href="<?= base_url() ?>admin/tasks/details/<?= $v_task->task_id ?>"><?= $v_task->task_name ?></a>
                            </td>
                            <td>
                                <?php
                                $due_date = $v_task->due_date;
                                $due_time = strtotime($due_date);
                                $current_time = strtotime(date('Y-m-d'));
                                ?>
                                <?= strftime(config_item('date_format'), strtotime($due_date)) ?>
                                <?php if ($current_time > $due_time && $v_task->task_progress < 100) { ?>
                                    <span class="label label-danger"><?= lang('overdue') ?></span>
                                <?php } ?>
                            </td>
                            <td>
                                <div class="progress progress-xs progress-striped active" style="margin-top: 5px; margin-bottom: 0px;">
                                    <div class="progress-bar progress-bar-<?php echo ($v_task->task_progress >= 100) ? 'success' : 'primary'; ?>"
                                         data-toggle="tooltip" data-original-title="<?= $v_task->task_progress ?>%"
                                         style="width: <?= $v_task->task_progress; ?>%"></div>
                                </div>
                            </td>
                            <td>
                                <?php
                                if ($v_task->task_status == 'completed') {
                                    $label = 'success';
                                } elseif ($v_task->task_status == 'not_started') {
                                    $label = 'info';
                                } elseif ($v_task->task_status == 'deferred') {
                                    $label = 'danger';
                                } else {
                                    $label = 'warning';
                                }
                                ?>
                                <span class="label label-<?= $label ?>"><?= lang($v_task->task_status) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="4" class="text-center"><?= lang('no_data') ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>