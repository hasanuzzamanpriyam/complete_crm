<div class="panel panel-custom">
    <div class="panel-heading">
        <h4 class="panel-title">
            Monthly Biometric Report - <?php echo $month_name . ' ' . $year; ?>
            
            <div class="pull-right">
                <!-- Using window.print() for simple printing/PDF -->
                <button class="btn btn-xs btn-danger" onclick="window.print();" data-toggle="tooltip" data-placement="top" title="Print or Save as PDF">
                    <i class="fa fa-print"></i> Print / PDF
                </button>
                <a href="<?= base_url('admin/attendance/export_monthly_raw_logs/'.$user_id.'/'.$month_year) ?>" class="btn btn-xs btn-success" data-toggle="tooltip" data-placement="top" title="Export Excel / CSV">
                    <i class="fa fa-file-excel-o"></i> Export CSV
                </a>
                <a href="<?= base_url('admin/attendance/biometric_device_logs') ?>" class="btn btn-xs btn-default">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>
        </h4>
    </div>
    
    <div class="panel-body">
        <!-- User Details Section -->
        <div class="row mb-xl" style="padding-bottom: 20px; border-bottom: 1px solid #eee; margin-bottom: 20px;">
            <div class="col-sm-6">
                <table class="table table-bordered table-striped" style="margin-bottom:0;">
                    <tbody>
                        <tr>
                            <td class="col-sm-4"><strong>Employee Name</strong></td>
                            <td><?php echo isset($user_details->fullname) ? $user_details->fullname : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <td class="col-sm-4"><strong>Employment ID</strong></td>
                            <td><?php echo isset($user_details->employment_id) ? $user_details->employment_id : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <td class="col-sm-4"><strong>Department</strong></td>
                            <td><?php echo isset($user_details->deptname) ? $user_details->deptname : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <td class="col-sm-4"><strong>Designation</strong></td>
                            <td><?php echo isset($user_details->designations) ? $user_details->designations : 'N/A'; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-sm-6">
                <!-- Add any other specific boxes or leave empty for layout balance -->
            </div>
        </div>

        <!-- Attendance Logs Table -->
        <div class="row">
            <div class="col-sm-12">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="monthly_log_table" width="100%">
                        <thead>
                            <tr>
                                <th style="width: 20%;">Date</th>
                                <th style="width: 30%;">Clock In Time</th>
                                <th style="width: 30%;">Clock Out Time</th>
                                <th style="width: 20%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($logs)): foreach($logs as $log): ?>
                            <tr class="<?= !empty($log['clock_in_time']) ? 'success' : '' ?>">
                                <td><strong><?= date('d M, Y', strtotime($log['log_date'])) ?></strong></td>
                                <?php if(empty($log['clock_in_time'])): ?>
                                    <td class="text-center">-</td>
                                    <td class="text-center">-</td>
                                    <td><span class="label label-danger">Absent / No Log</span></td>
                                <?php else: ?>
                                    <td><span class="text-success"><?= date('h:i:s A', strtotime($log['clock_in_time'])) ?></span></td>
                                    <td>
                                        <?php if($log['clock_in_time'] === $log['clock_out_time']): ?>
                                            <span class="text-muted">-</span>
                                        <?php else: ?>
                                            <span class="text-warning"><?= date('h:i:s A', strtotime($log['clock_out_time'])) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="label label-success">Present</span></td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No attendance logs found for this month.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Optional: Print styling to hide unnecessary elements when printing */
@media print {
    body * {
        visibility: hidden;
    }
    .panel-custom, .panel-custom * {
        visibility: visible;
    }
    .panel-custom {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .pull-right, .navbar, .sidebar {
        display: none !important;
    }
}
</style>
<script>
    // simple initialization if needed
    $(document).ready(function() {
        // You can attach datatables if not inheriting from global class
    });
</script>
