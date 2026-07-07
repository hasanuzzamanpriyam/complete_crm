<div class="nav-tabs-custom ">
    <!-- Tabs within a box -->
    <ul class="nav nav-tabs">
        <li class="active"><a href="#manageTasks" data-toggle="tab">All Task</a>
        </li>
        <li class=""><a href="<?= base_url('admin/' . $module_name . '/new_task/' . $module_name . '/' . $id) ?>">New
                Tasks</a>
        </li>
    </ul>
    <div class="tab-content bg-white">
        <!-- ************** general *************-->
        <div class="tab-pane active" id="manageTasks">
            
            <div class="box pt-1" data-collapsed="0">
                <div class="box-body">
                    <table class="table table-hover" id="">
                        <thead>
                        <tr>
                            <th data-check-all="">
                            
                            </th>
                            <th class="col-sm-4">Task Name</th>
                            <th class="col-sm-2">Due Date</th>
                            <th class="col-sm-1">Status</th>
                            <th class="col-sm-1">Progress</th>
                            <th class="col-sm-3">Change / View</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if (!empty($module_task_info)) : foreach ($module_task_info as $key => $v_task) :
                            ?>
                            <tr id="leads_tasks_<?= $v_task->task_id ?>">
                                <td class="col-sm-1">
                                    <div class="is_complete checkbox c-checkbox">
                                        <label>
                                            <input class="position-absolute" type="checkbox"
                                                   data-id="<?= $v_task->task_id ?>"<?php
                                            if ($v_task->task_progress >= 100) {
                                                echo 'checked';
                                            }
                                            ?>>
                                            <span class="fa fa-check"></span>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <a style="<?php
                                    if ($v_task->task_progress >= 100) {
                                        echo 'text-decoration: line-through;';
                                    }
                                    ?>"
                                       href="<?= base_url() ?>admin/tasks/view_task_details/<?= $v_task->task_id ?>"><?php echo $v_task->task_name; ?></a>
                                </td>
                                <td><?php
                                    $due_date = $v_task->due_date;
                                    $due_time = strtotime($due_date);
                                    $current_time = strtotime(date('Y-m-d'));
                                    ?>
                                    <?= strftime(config_item('date_format'), strtotime($due_date)) ?>
                                    <?php if ($current_time > $due_time && $v_task->task_progress < 100) { ?>
                                        <span class="label label-danger"><?= lang('overdue') ?></span>
                                    <?php } ?>
                                </td>
                                <td><?php
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
                                    <span class="label label-<?= $label ?>"><?= lang($v_task->task_status) ?> </span>
                                </td>
                                <td>
                                    <div class="inline ">
                                        <div class="easypiechart text-success m-0"
                                             data-percent="<?= $v_task->task_progress ?>" data-line-width="5"
                                             data-track-Color="#f0f0f0" data-bar-color="#<?php
                                        if ($v_task->task_progress == 100) {
                                            echo '8ec165';
                                        } else {
                                            echo 'fb6b5b';
                                        }
                                        ?>" data-rotate="270" data-scale-Color="false" data-size="50"
                                             data-animate="2000">
                                                                    <span class="small text-muted"><?= $v_task->task_progress ?>
                                                                        %</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php echo ajax_anchor(base_url("admin/tasks/delete_task/" . $v_task->task_id), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#leads_tasks_" . $v_task->task_id)); ?>
                                    <?php echo btn_edit('admin/' . $module_name . '/new_task/' . $v_task->task_id) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
