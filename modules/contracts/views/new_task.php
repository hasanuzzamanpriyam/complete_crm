<style>
    .w-100 {
        width: 100% !important;
    }
</style>
<div class="panel panel-custom">
    <div class="panel-heading">
        <h3 class="panel-title"><?= lang('new_task'); ?></h3>
    </div>
    <div class="panel-body chat" id="chat-box">
        <form action="<?= base_url('admin/' . $module . '/save_task') ?>/<?php if (!empty($task_info->task_id)) echo $task_info->task_id; ?>"
              enctype="multipart/form-data" method="post" id="form" class="form-horizontal">
            <input type="hidden" name="module" value="<?php echo $module; ?>" class="form-control">
            <input type="hidden" name="module_field_id" value="<?php echo $module_field_id; ?>" class="form-control">
            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('task_name') ?><span class="required">*</span></label>
                <div class="col-sm-5">
                    <input type="text" name="task_name" required class="form-control"
                           value="<?php if (!empty($task_info->task_name)) echo $task_info->task_name; ?>"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('select') . ' ' . lang('categories') ?></label>
                <div class="col-sm-5">
                    <select name="category_id" class="form-control select_box w-100">
                        <?php
                        if (!empty($all_customer_group)) {
                            foreach ($all_customer_group as $customer_group) : ?>
                                <option value="<?= $customer_group->customer_group_id ?>" <?php
                                if (!empty($task_info->category_id) && $task_info->category_id == $customer_group->customer_group_id) {
                                    echo 'selected';
                                } ?>><?= $customer_group->customer_group; ?></option>
                            <?php endforeach;
                        } ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group" id="related_to">
            
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?= lang('start_date') ?></label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" name="task_start_date" class="form-control start_date" value="<?php
                        if (!empty($task_info->task_start_date)) {
                            echo $task_info->task_start_date;
                        } ?>" data-date-format="<?= config_item('date_picker_format'); ?>">
                        <div class="input-group-addon">
                            <a href="#"><i class="fa fa-calendar"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="col-lg-3 control-label"><?= lang('due_date') ?><span class="required">*</span></label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" name="due_date" required="" value="<?php
                        if (!empty($task_info->due_date)) {
                            echo $task_info->due_date;
                        }
                        ?>" class="form-control end_date" data-date-format="<?= config_item('date_picker_format'); ?>">
                        <div class="input-group-addon">
                            <a href="#"><i class="fa fa-calendar"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('project_hourly_rate') ?></label>
                <div class="col-sm-5">
                    <input type="text" data-parsley-type="number" name="hourly_rate" class="form-control"
                           value="<?php if (!empty($task_info->hourly_rate)) echo $task_info->hourly_rate; ?>"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('estimated_hour') ?></label>
                <div class="col-sm-5">
                    <input type="number" step="0.01" data-parsley-type="number" name="task_hour" class="form-control"
                           value="<?php
                           if (!empty($task_info->task_hour)) {
                               $result = explode(':', $task_info->task_hour);
                               if (empty($result[1])) {
                                   $result1 = 0;
                               } else {
                                   $result1 = $result[1];
                               }
                               echo $result[0] . '.' . $result1;
                           }
                           ?>"/>
                </div>
            
            </div>
            <script src="<?= base_url() ?>assets/js/jquery-ui.js"></script>
            <?php $direction = $this->session->userdata('direction');
            if (!empty($direction) && $direction == 'rtl') {
                $RTL = 'on';
            } else {
                $RTL = config_item('RTL');
            }
            ?>
            <?php
            if (!empty($RTL)) { ?>
                <!-- bootstrap-editable -->
                <script type="text/javascript"
                        src="<?= base_url() ?>assets/plugins/jquery-ui/jquery.ui.slider-rtl.js"></script>
            <?php }
            ?>
            <style>
                .ui-widget.ui-widget-content {
                    border: 1px solid #dde6e9;
                }

                .ui-corner-all,
                .ui-corner-bottom,
                .ui-corner-left,
                .ui-corner-bl {
                    border: 7px solid #28a9f1;
                }

                .ui-widget-content {
                    border: 1px solid #dddddd;
                    /*background: #E1E4E9;*/
                    color: #333333;
                }

                .ui-slider {
                    position: relative;
                    text-align: left;
                }

                .ui-slider-horizontal {
                    height: 1em;
                }

                .ui-state-default,
                .ui-widget-content .ui-state-default,
                .ui-widget-header .ui-state-default,
                .ui-button,
                html .ui-button.ui-state-disabled:hover,
                html .ui-button.ui-state-disabled:active {
                    border: 1px solid #1797be;
                    background: #1797be;
                    font-weight: normal;
                    color: #454545;
                }

                .ui-slider-horizontal .ui-slider-handle {
                    top: -.3em;
                    margin-left: -.1em;;
                    margin-right: -.1em;;
                }

                .ui-slider .ui-slider-handle:hover {
                    background: #1797be;
                }

                .ui-slider .ui-slider-handle {
                    position: absolute;
                    z-index: 2;
                    width: 1.2em;;
                    height: 1.5em;
                    cursor: default;
                    -ms-touch-action: none;
                    touch-action: none;

                }

                .ui-state-disabled,
                .ui-widget-content .ui-state-disabled,
                .ui-widget-header .ui-state-disabled {
                    opacity: .35;
                    filter: Alpha(Opacity=35);
                    background-image: none;
                }

                .ui-state-disabled {
                    cursor: default !important;
                    pointer-events: none;
                }

                .ui-slider.ui-state-disabled .ui-slider-handle,
                .ui-slider.ui-state-disabled .ui-slider-range {
                    filter: inherit;
                }

                .ui-slider-range,
                .ui-widget-header,
                .ui-slider-handle:before,
                .list-group-item.active,
                .list-group-item.active:hover,
                .list-group-item.active:focus,
                .icon-frame {
                    background-image: none;
                    background: #28a9f1;
                }
            </style>
            
            <?php
            if (!empty($task_info)) {
                $value = $this->tasks_model->get_task_progress($task_info->task_id);
            } else {
                $value = 0;
            }
            ?>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('progress'); ?> </label>
                <div class="col-lg-5">
                    <?php echo form_hidden('task_progress', $value); ?>
                    <div class="project_progress_slider project_progress_slider_horizontal mbot15"></div>
                    
                    <div class="input-group">
                        <span class="input-group-addon">
                            <div class="">
                                <div class="pull-left mt">
                                    <?php echo lang('progress'); ?>
                                    <span class="label_progress "><?php echo $value; ?>%</span>
                                </div>
                                <div class="checkbox c-checkbox pull-right" data-toggle="tooltip" data-placement="top"
                                     title="<?php echo lang('calculate_progress_through_sub_tasks'); ?>">
                                    <label class="needsclick">
                                        <input class="select_one"
                                               type="checkbox" <?php if ((!empty($task_info) && $task_info->calculate_progress == 'through_sub_tasks')) {
                                            echo 'checked';
                                        } ?> name="calculate_progress" value="through_sub_tasks" id="through_sub_tasks">
                                        <span class="fa fa-check"></span>
                                        <small><?php echo lang('through_sub_tasks'); ?></small>
                                    </label>
                                </div>
                                <div class="checkbox c-checkbox pull-right" data-toggle="tooltip" data-placement="top"
                                     title="<?php echo lang('calculate_progress_through_task_hours'); ?>">
                                    <label class="needsclick">
                                        <input class="select_one"
                                               type="checkbox" <?php if ((!empty($task_info) && $task_info->calculate_progress == 'through_tasks_hours')) {
                                            echo 'checked';
                                        } ?> name="calculate_progress" value="through_tasks_hours"
                                               id="through_tasks_hours">
                                        <span class="fa fa-check"></span>
                                        <small><?php echo lang('through_tasks_hours'); ?></small>
                                    </label>
                                </div>
                            </div>
                        </span>
                    </div>
                </div>
            </div>
            <script>
                (function ($) {
                    "use strict";
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
                        <?php
                        if (!empty($RTL)) { ?>
                        isRTL: true,
                        <?php }
                        ?>
                        min: 0,
                        max: 100,
                        value: progress,
                        disabled: progress_from_tasks.prop('checked'),
                        slide: function (event, ui) {
                            progress_input.val(ui.value);
                            $('.label_progress').html(ui.value + '%');
                        }
                    });
                    progress_from_tasks.on('change', function () {
                        var _checked = $(this).prop('checked');
                        $('.project_progress_slider').slider({
                            disabled: _checked,
                        });
                    });
                })(jQuery);
            </script>
            <div class="form-group" id="border-none">
                <label for="field-1" class="col-sm-3 control-label"><?= lang('task_status') ?> <span
                            class="required">*</span></label>
                <div class="col-sm-5">
                    <select name="task_status" class="form-control" required>
                        <option value="not_started" <?= (!empty($task_info->task_status) && $task_info->task_status == 'not_started' ? 'selected' : '') ?>> <?= lang('not_started') ?> </option>
                        <option value="in_progress" <?= (!empty($task_info->task_status) && $task_info->task_status == 'in_progress' ? 'selected' : '') ?>> <?= lang('in_progress') ?> </option>
                        <option value="completed" <?= (!empty($task_info->task_status) && $task_info->task_status == 'completed' ? 'selected' : '') ?>> <?= lang('completed') ?> </option>
                        <option value="deferred" <?= (!empty($task_info->task_status) && $task_info->task_status == 'deferred' ? 'selected' : '') ?>> <?= lang('deferred') ?> </option>
                        <option value="waiting_for_someone" <?= (!empty($task_info->task_status) && $task_info->task_status == 'waiting_for_someone' ? 'selected' : '') ?>> <?= lang('waiting_for_someone') ?> </option>
                    </select>
                </div>
            </div>
            <?php
            if (!empty($task_info)) {
                $task_id = $task_info->task_id;
            } else {
                $task_id = null;
            }
            ?>
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label"><?= lang('tags') ?>
                </label>
                <div class="col-sm-8">
                    <input type="text" name="tags" data-role="tagsinput" class="form-control" value="<?php
                    if (!empty($task_info->tags)) {
                        echo $task_info->tags;
                    }
                    ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label"><?= lang('task_description') ?>
                </label>
                <div class="col-sm-8">
                    <textarea class="form-control textarea"
                              name="task_description"><?php if (!empty($task_info->task_description)) echo $task_info->task_description; ?></textarea>
                </div>
            </div>
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label"><?= lang('billable') ?>
                    <span class="required">*</span></label>
                <div class="col-sm-8">
                    <input data-toggle="toggle" name="billable" value="Yes" <?php
                    if (!empty($task_info) && $task_info->billable == 'Yes') {
                        echo 'checked';
                    }
                    ?> data-on="<?= lang('yes') ?>" data-off="<?= lang('no') ?>" data-onstyle="success"
                           data-offstyle="danger" type="checkbox">
                </div>
            </div>
            <?php if (!empty($project_id)) : ?>
                <div class="form-group">
                    <label for="field-1" class="col-sm-3 control-label"><?= lang('visible_to_client') ?>
                        <span class="required">*</span></label>
                    <div class="col-sm-8">
                        <input data-toggle="toggle" name="client_visible" value="Yes" <?php
                        if (!empty($task_info) && $task_info->client_visible == 'Yes') {
                            echo 'checked';
                        }
                        ?> data-on="<?= lang('yes') ?>" data-off="<?= lang('no') ?>" data-onstyle="success"
                               data-offstyle="danger" type="checkbox">
                    </div>
                </div>
            <?php endif ?>
            
            
            <?= custom_form_Fields(3, $task_id); ?>
            
            <?php
            $permissionL = null;
            if (!empty($task_info->permission)) {
                $permissionL = $task_info->permission;
            }
            ?>
            <?= get_permission(3, 9, $assign_user, $permissionL, lang('assined_to')); ?>
            
            <div class="btn-bottom-toolbar text-right">
                <?php
                if (!empty($task_info)) { ?>
                    <button type="submit" class="btn btn-sm btn-primary"><?= lang('updates') ?></button>
                    <button type="button" onclick="goBack()"
                            class="btn btn-sm btn-danger"><?= lang('cancel') ?></button>
                <?php } else {
                    ?>
                    <button type="submit" class="btn btn-sm btn-primary"><?= lang('save') ?></button>
                <?php }
                ?>
            </div>
        </form>
    </div>
</div>