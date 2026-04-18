<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-custom">
            <div class="panel-heading">
                <div class="panel-title" style="display: flex; justify-content: space-between; align-items: center;">
                    <strong>Biometric Device Logs (Last 500 Taps)</strong>

                    <!-- View Monthly Logs Form -->
                    <form action="<?= base_url('admin/attendance/view_monthly_raw_logs') ?>" method="post" class="form-inline" style="margin: 0;">
                        <input type="month" name="month" class="form-control input-sm" required style="width: auto;" value="<?= date('Y-m') ?>">
                        <select name="user_id" class="form-control select_box input-sm" style="width: auto;" required>
                            <option value="">Select User for Export</option>
                            <?php if (!empty($all_employee)): foreach ($all_employee as $dept_name => $v_all_employee) : ?>
                                <optgroup label="<?php echo $dept_name; ?>">
                                    <?php if (!empty($v_all_employee)): foreach ($v_all_employee as $v_employee) : ?>
                                        <option value="<?php echo $v_employee->user_id; ?>"><?php echo $v_employee->fullname . ' (' . $v_employee->employment_id . ')'; ?></option>
                                    <?php endforeach; endif; ?>
                                </optgroup>
                            <?php endforeach; endif; ?>
                        </select>
                        <button type="submit" class="btn btn-sm btn-info" title="View Monthly Report">
                            <i class="fa fa-eye"></i> View Report
                        </button>
                    </form>
                </div>
            </div>
            <div class="panel-body">
                <p class="text-muted">This table shows every raw signal received from the biometric machine. Use this to find the Device IDs of unmapped employees.</p>
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-striped DataTables " id="Transation_DataTables" cellspacing="0" width="100%" style="margin-bottom: 0px;">
                        <thead style="position: sticky; top: 0; background: white; z-index: 10; box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);">
                            <tr>
                                <th>Date</th>
                                <th>Device User ID</th>
                                <th>Employee Name</th>
                                <th>Clock In</th>
                                <th>Clock Out</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($all_logs)): foreach ($all_logs as $log): ?>
                                    <tr>
                                        <td>
                                            <?php echo date('d M, Y', strtotime($log->log_date)); ?>
                                        </td>
                                        <td><span class="label label-info"><?= $log->device_user_id ?></span></td>
                                        <td>
                                            <?php if (!empty($log->fullname)): ?>
                                                <?= $log->fullname ?>
                                            <?php else: ?>
                                                <span class="text-danger"><i>Unmapped User</i></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="text-success"><strong>[<?= date('h:i:s A', strtotime($log->clock_in_time)) ?>]</strong></span>
                                        </td>
                                        <td>
                                            <?php if ($log->clock_in_time === $log->clock_out_time): ?>
                                                <span class="text-muted">-</span>
                                            <?php else: ?>
                                                <span class="text-warning"><strong>[<?= date('h:i:s A', strtotime($log->clock_out_time)) ?>]</strong></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (empty($log->fullname)): ?>
                                                <a href="<?= base_url('admin/attendance/biometric_mapping/' . $log->device_user_id) ?>" class="btn btn-primary btn-xs">
                                                    <i class="fa fa-link"></i> Map User
                                                </a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                            <?php endforeach;
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var lastLogId = <?php echo !empty($all_logs) ? max(array_column($all_logs, 'max_id')) : '0'; ?>;

    function refreshBiometricLogs() {
        $.ajax({
            url: '<?= base_url("api/biometric_attendance/get_latest_logs") ?>',
            type: 'GET',
            data: {
                after_id: lastLogId
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.logs.length > 0) {
                    // Because logs are grouped by day and user, dynamically rebuilding the grouped DOM
                    // is complex. We do a soft page reload when new data actually arrives.
                    window.location.reload();
                }
            },
            error: function(err) {
                console.error('Error refreshing biometric logs:', err);
            }
        });
    }

    // Start auto-refresh polling 
    $(document).ready(function() {
        setInterval(refreshBiometricLogs, 30000); // 30 seconds
    });
</script>