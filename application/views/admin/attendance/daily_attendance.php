<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-custom">
            <div class="panel-heading">
                <div class="panel-title">
                    <strong>Daily Attendance (<?= date('d M, Y', strtotime($date)) ?>)</strong>
                </div>
            </div>
            <div class="panel-body">
                <form id="attendance-form" role="form" action="<?php echo base_url(); ?>admin/attendance/daily_attendance" method="post" class="form-horizontal">
                    <div class="form-group row">
                        <label class="col-sm-3 control-label"><?= lang('date') ?> <span class="required">*</span></label>
                        <div class="col-sm-5">
                            <div class="input-group">
                                <input type="text" name="date" class="form-control datepicker" value="<?= $date ?>" data-format="yyyy-mm-dd">
                                <div class="input-group-addon">
                                    <a href="#"><i class="fa fa-calendar"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <button type="submit" class="btn btn-primary"><?= lang('go') ?></button>
                        </div>
                    </div>
                </form>
                
                <hr/>

                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-striped DataTables " id="Transation_DataTables" cellspacing="0" width="100%" style="margin-bottom: 0px;">
                        <thead style="position: sticky; top: 0; background: white; z-index: 10; box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);">
                            <tr>
                                <th><?= lang('empid') ?></th>
                                <th><?= lang('name') ?></th>
                                <th><?= lang('designation') ?></th>
                                <th><?= lang('status') ?></th>
                                <th><?= lang('clock_in') ?> (<?= lang('first') ?>)</th>
                                <th><?= lang('clock_out') ?> (<?= lang('last') ?>)</th>
                                <th><?= lang('total_hours') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($all_employees)): foreach ($all_employees as $v_employee): ?>
                                <tr>
                                    <td><?php echo $v_employee->employment_id; ?></td>
                                    <td><?php echo $v_employee->fullname; ?></td>
                                    <td><?php echo $v_employee->designations; ?></td>
                                    <td>
                                        <?php if (!empty($v_employee->attendance)): ?>
                                            <?php if ($v_employee->attendance->clocking_status == 1): ?>
                                                <span class="label label-success"><?= lang('clock_in') ?></span>
                                            <?php else: ?>
                                                <span class="label label-danger"><?= lang('clock_out') ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="label label-default"><?= lang('absent') ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            if (!empty($v_employee->clocks)) {
                                                echo date('h:i A', strtotime($v_employee->clocks[0]->clockin_time));
                                            } else {
                                                echo '-';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                            if (!empty($v_employee->clocks)) {
                                                $last_clock = end($v_employee->clocks);
                                                echo !empty($last_clock->clockout_time) ? date('h:i A', strtotime($last_clock->clockout_time)) : '-';
                                            } else {
                                                echo '-';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                            $total_hh = 0; $total_mm = 0;
                                            if (!empty($v_employee->clocks)) {
                                                foreach($v_employee->clocks as $clock) {
                                                    if (!empty($clock->clockout_time)) {
                                                        $start = strtotime($clock->clockin_time);
                                                        $end = strtotime($clock->clockout_time);
                                                        $diff = $end - $start;
                                                        $total_hh += floor($diff / 3600);
                                                        $total_mm += floor(($diff % 3600) / 60);
                                                    }
                                                }
                                                // Handle overflow of minutes
                                                $total_hh += floor($total_mm / 60);
                                                $total_mm = $total_mm % 60;
                                                echo $total_hh . " h : " . $total_mm . " m";
                                            } else {
                                                echo '-';
                                            }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
