<div class="form-horizontal">
    <div class="panel panel-custom">
        <div class="panel-heading">
            <div class="panel-title">
                <strong><?= lang('timecard_details') ?></strong>
                <?php if (!empty($attendace_info)) { ?>
                    <div class="pull-right ">
                        <span><?php echo btn_pdf('admin/user/timecard_details_pdf/' . $profile_info->user_id . '/' . date('Y-n', strtotime($date))); ?></span>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="panel-body">
            <form id="attendance-form" role="form" enctype="multipart/form-data" action="<?php echo base_url(); ?>admin/user/user_details/<?= $profile_info->user_id ?>/timecard_details" method="post" class="form-horizontal form-groups-bordered">
                <div class="form-group">
                    <label for="field-1" class="col-sm-3 control-label"><?= lang('month') ?><span class="required"> *</span></label>
                    <div class="col-sm-5">
                        <div class="input-group">
                            <input type="text" class="form-control monthyear" value="<?php
                                                                                        if (!empty($date)) {
                                                                                            echo date('Y-n', strtotime($date));
                                                                                        }
                                                                                        ?>" name="date">
                            <div class="input-group-addon">
                                <a href="#"><i class="fa fa-calendar"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-2 ">
                        <button type="submit" id="sbtn" class="btn btn-primary"><?= lang('go') ?></button>
                    </div>
                    <div class="col-sm-2">
                        <select name="data_source" class="form-control" onchange="this.form.submit()">
                            <option value="attendance" <?= ($this->input->post('data_source') ?? 'attendance') === 'attendance' ? 'selected' : '' ?>><?= lang('attendance') ?></option>
                            <option value="timesync" <?= ($this->input->post('data_source')) === 'timesync' ? 'selected' : '' ?>>TimeSync Tracker</option>
                        </select>
                    </div>
                </div>
            </form>

            <?php if (($this->input->post('data_source') ?? 'attendance') === 'timesync'): ?>
                <?php if (!empty($timesync_summary)): ?>
                    <?php
                    $ts = $timesync_summary;
                    function sec_to_hms($s) {
                        $h = floor(abs($s) / 3600);
                        $m = floor((abs($s) % 3600) / 60);
                        return ($s < 0 ? '-' : '') . sprintf('%dh %02dm', $h, $m);
                    }
                    $surplus_str = $ts['surplus_sec'] > 0 ? '+' . sec_to_hms($ts['surplus_sec']) : '--';
                    $shortage_str = $ts['shortage_sec'] > 0 ? '-' . sec_to_hms($ts['shortage_sec']) : '--';
                    ?>
                    <div class="panel panel-custom">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <strong>TimeSync Monthly Summary &mdash; <?= date('F Y', strtotime($ts['ym'] . '-01')) ?></strong>
                            </h4>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-sm-3 text-center">
                                    <strong>Expected</strong>
                                    <h4><?= sec_to_hms($ts['adjusted_expected_sec']) ?></h4>
                                    <?php if ($ts['consumed_sec'] > 0): ?>
                                        <small class="text-muted">(gross: <?= sec_to_hms($ts['gross_expected_sec']) ?>, <?= sec_to_hms($ts['consumed_sec']) ?> OT applied)</small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-sm-3 text-center">
                                    <strong>Actual Tracked</strong>
                                    <h4><?= sec_to_hms($ts['actual_sec']) ?></h4>
                                </div>
                                <div class="col-sm-3 text-center">
                                    <strong>Surplus / Overtime</strong>
                                    <h4 class="text-success"><?= $surplus_str ?></h4>
                                </div>
                                <div class="col-sm-3 text-center">
                                    <strong>Shortage</strong>
                                    <h4 class="<?= $ts['shortage_sec'] > 0 ? 'text-danger' : '' ?>"><?= $shortage_str ?></h4>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-6">
                                    <strong>Overtime Balance Carried Forward:</strong>
                                    <span class="label label-info"><?= sec_to_hms($ts['carryover_out_sec']) ?></span>
                                    <small class="text-muted">(brought in: <?= sec_to_hms($ts['carryover_in_sec']) ?>)</small>
                                </div>
                                <div class="col-sm-6 text-right">
                                    <?php if ($ts['is_frozen']): ?>
                                        <span class="label label-success">&#10003; Frozen</span>
                                        <?php if (is_super_admin()): ?>
                                            <button type="button" class="btn btn-xs btn-warning" onclick="recalculateBalance(<?= $profile_info->user_id ?>, '<?= $ts['ym'] ?>')">Recalculate</button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="label label-warning">&#9675; Current Month (in progress)</span>
                                        <?php if ($ts['shortage_sec'] > 0 && $ts['month_over']): ?>
                                            <span class="label label-danger">&#9888; Shortage: <?= sec_to_hms($ts['shortage_sec']) ?></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Clock-In</th>
                                    <th>Clock-Out</th>
                                    <th>Actual</th>
                                    <th>Expected</th>
                                    <th>Disc.</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ts['daily_rows'] as $r): ?>
                                <tr>
                                    <td><?= date('D d M', strtotime($r['date'])) ?></td>
                                    <td><?= $r['clock_in'] ?></td>
                                    <td><?= $r['clock_out'] ?></td>
                                    <td><?= $r['actual_sec'] > 0 ? sec_to_hms($r['actual_sec']) : '--' ?></td>
                                    <td><?= $r['expected_sec'] > 0 ? sec_to_hms($r['expected_sec']) : '--' ?></td>
                                    <td class="<?= $r['discrepancy_sec'] < 0 ? 'text-danger' : ($r['discrepancy_sec'] > 0 ? 'text-success' : '') ?>">
                                        <?= $r['discrepancy_sec'] != 0 ? sec_to_hms($r['discrepancy_sec']) : '0' ?>
                                    </td>
                                    <td>
                                        <?php if ($r['status'] === 'weekly_holiday'): ?>
                                            <span class="label label-info std_p">&#127881; Fri</span>
                                        <?php elseif ($r['status'] === 'public_holiday'): ?>
                                            <span class="label label-info std_p">&#127881; Holiday</span>
                                        <?php elseif ($r['status'] === 'on_leave'): ?>
                                            <span class="label label-warning std_p">&#128197; On Leave</span>
                                        <?php elseif ($r['status'] === 'ot_applied_ok'): ?>
                                            <span class="label label-success">&#9889; OT Applied</span>
                                        <?php elseif ($r['status'] === 'ot_applied_short'): ?>
                                            <span class="label label-danger">&#9889; OT Applied</span>
                                        <?php elseif ($r['status'] === 'no_tracking'): ?>
                                            <span class="label label-default">&#9888; No Tracking</span>
                                        <?php elseif ($r['status'] === 'shortage'): ?>
                                            <span class="label label-danger">&#9888; Short</span>
                                        <?php else: ?>
                                            <span class="label label-success">&#10003;</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <?php if (!empty($attendace_info)) : ?>
                    <div class="row">
                        <div class="panel panel-custom ">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <strong><?= lang('works_hours_deatils') . ' ' ?><?php echo date('F-Y', strtotime($date));; ?></strong>
                                </h4>
                            </div>
                            <?php
                            foreach ($attendace_info as $week => $v_attndc_info) :
                            ?>
                                <div class="box-header" style="border-bottom: 1px solid red">
                                    <h4 class="box-title" style="font-size: 15px">
                                        <strong><?= lang('week') ?> : <?php echo $week; ?> </strong>
                                    </h4>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <?php
                                                if (!empty($v_attndc_info)) : foreach ($v_attndc_info as $date => $attendace) :
                                                        $total_hour = 0;
                                                        $total_minutes = 0;
                                                ?>
                                                        <th>
                                                            <?= strftime(config_item('date_format'), strtotime($date)) ?></th>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <?php
                                                if (!empty($v_attndc_info)) : foreach ($v_attndc_info as $date => $v_attendace) :
                                                        $total_hh = 0;
                                                        $total_mm = 0;
                                                ?>
                                                        <?php
                                                        if (!empty($v_attendace)) {
                                                            foreach ($v_attendace as $v_attandc) {
                                                                if (!is_object($v_attandc)) {
                                                                    continue;
                                                                }
                                                                if ($v_attandc->attendance_status == 1) {
                                                                    $startdatetime = strtotime($v_attandc->date_in . " " . $v_attandc->clockin_time);
                                                                    $enddatetime = strtotime($v_attandc->date_out . " " . $v_attandc->clockout_time);
                                                                    $difference = $enddatetime - $startdatetime;
                                                                    $years = abs(floor($difference / 31536000));
                                                                    $days = abs(floor(($difference - ($years * 31536000)) / 86400));
                                                                    $hours = abs(floor(($difference - ($years * 31536000) - ($days * 86400)) / 3600));
                                                                    $mins = abs(floor(($difference - ($years * 31536000) - ($days * 86400) - ($hours * 3600)) / 60));
                                                                    $total_mm += $mins;
                                                                    $total_hh += $hours;
                                                                } elseif ($v_attandc->attendance_status == 'H') {
                                                                    $holiday = 1;
                                                                } elseif ($v_attandc->attendance_status == '3') {
                                                                    $leave = 1;
                                                                } elseif ($v_attandc->attendance_status == '0') {
                                                                    $absent = 1;
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                        <td>
                                                            <?php
                                                            if ($total_mm > 59) {
                                                                $total_hh += intval($total_mm / 60);
                                                                $total_mm = intval($total_mm % 60);
                                                            }
                                                            $total_hour += $total_hh;
                                                            $total_minutes += $total_mm;
                                                            if ($total_hh != 0 || $total_mm != 0) {
                                                                echo $total_hh . " : " . $total_mm . " m";
                                                            } elseif (!empty($holiday)) {
                                                                echo '<span style="font-size: 12px;" class="label label-info std_p">' . lang('holiday') . '</span>';
                                                            } elseif (!empty($leave)) {
                                                                echo '<span style="font-size: 12px;" class="label label-warning std_p">' . lang('on_leave') . '</span>';
                                                            } elseif (!empty($absent)) {
                                                                echo '<span style="font-size: 12px;" class="label label-danger std_p">' . lang('absent') . '</span>';
                                                            } else {
                                                                echo $total_hh . " : " . $total_mm . " m";
                                                            }
                                                            ?>
                                                        </td>
                                                <?php
                                                        $holiday = NULL;
                                                        $leave = NULL;
                                                        $absent = NULL;
                                                    endforeach;
                                                endif;
                                                ?>
                                            </tr>
                                            <table>
                                                <tr>
                                                    <td colspan="2" class="text-right">
                                                        <strong style="margin-right: 10px; "><?= lang('total_working_hour') ?>
                                                            : </strong>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($total_minutes >= 60) {
                                                            $total_hour += intval($total_minutes / 60);
                                                            $total_minutes = intval($total_minutes % 60);
                                                        }
                                                        echo $total_hour . " : " . $total_minutes . " m";
                                                        ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function recalculateBalance(userId, yearMonth) {
    if (!confirm('Recalculate balance for ' + yearMonth + '? This will cascade to subsequent months.')) return;
    $.post('<?= base_url('admin/user/recalculate_balance') ?>', {
        user_id: userId,
        year_month: yearMonth
    }, function(res) {
        if (res.success) {
            location.reload();
        } else {
            alert('Recalculation failed: ' + (res.message || 'Unknown error'));
        }
    }, 'json');
}
</script>
