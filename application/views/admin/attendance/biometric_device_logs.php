<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-custom">
            <div class="panel-heading">
                <div class="panel-title">
                    <strong>Biometric Device Logs (Last 500 Taps)</strong>
                </div>
            </div>
            <div class="panel-body">
                <p class="text-muted">This table shows every raw signal received from the biometric machine. Use this to find the Device IDs of unmapped employees.</p>
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-striped DataTables " id="Transation_DataTables" cellspacing="0" width="100%" style="margin-bottom: 0px;">
                        <thead style="position: sticky; top: 0; background: white; z-index: 10; box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);">
                            <tr>
                                <th><?= lang('time') ?></th>
                                <th>Device User ID</th>
                                <th>Employee Name</th>
                                <th>Sync Result</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($all_logs)): foreach ($all_logs as $log): ?>
                                    <tr>
                                        <td>
                                            <?php
                                            $device_parsed = strtotime($log->timestamp);
                                            $received_parsed = !empty($log->created_at) ? strtotime($log->created_at) : false;

                                            // Show server receive time (real-time ingestion) first.
                                            if ($received_parsed !== false) {
                                                echo date('d M, Y h:i:s A', $received_parsed);
                                                echo '<br><small class="text-muted" title="Data received time from server">(received)</small>';
                                            } else {
                                                echo '<span class="text-danger"><strong>Invalid Receive Time</strong></span>';
                                            }

                                            // Add device timestamp if available for reference
                                            if ($device_parsed !== false && $device_parsed >= strtotime('2000-01-01')) {
                                                echo '<br><small class="text-info" title="Time reported by biometric device">Device: ' . date('d M, Y h:i:s A', $device_parsed) . '</small>';
                                            } elseif (!empty($log->timestamp) && $log->timestamp !== '0000-00-00 00:00:00') {
                                                echo '<br><small class="text-warning">Device timestamp invalid: ' . htmlspecialchars($log->timestamp) . '</small>';
                                            }
                                            ?>
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
                                            <?php if ($log->processed == 1): ?>
                                                <span class="label label-success">Processed (New)</span>
                                            <?php else: ?>
                                                <span class="label label-warning">Ignored / Duplicate</span>
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
    // Auto-refresh biometric logs every 5 seconds to show new taps in real-time
    var lastLogId = <?php echo !empty($all_logs) ? $all_logs[0]->id : '0'; ?>;

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
                    var tbody = $('#Transation_DataTables tbody');

                    // Prepend new logs to the top
                    response.logs.forEach(function(log) {
                        var newRow = createLogRow(log);
                        tbody.prepend(newRow);
                        lastLogId = Math.max(lastLogId, log.id);
                    });

                    // Keep only last 500 rows
                    var rows = tbody.find('tr');
                    if (rows.length > 500) {
                        rows.slice(500).remove();
                    }
                }
            },
            error: function(err) {
                console.error('Error refreshing biometric logs:', err);
            }
        });
    }

    function createLogRow(log) {
        var receivedTime = new Date(log.created_at).toLocaleString();
        var deviceTime = log.timestamp && log.timestamp !== '0000-00-00 00:00:00' ?
            new Date(log.timestamp).toLocaleString() :
            '';

        var timeHtml = receivedTime + '<br><small class="text-muted">(received)</small>';
        if (deviceTime) {
            timeHtml += '<br><small class="text-info">Device: ' + deviceTime + '</small>';
        }

        var fullname = log.fullname ? log.fullname : '<span class="text-danger"><i>Unmapped User</i></span>';
        var status = log.processed === 1 ?
            '<span class="label label-success">Processed (New)</span>' :
            '<span class="label label-warning">Ignored / Duplicate</span>';

        var action = !log.fullname ?
            '<a href="<?= base_url("admin/attendance/biometric_mapping/") ?>' + log.device_user_id + '" class="btn btn-primary btn-xs"><i class="fa fa-link"></i> Map User</a>' :
            '-';

        return '<tr>' +
            '<td>' + timeHtml + '</td>' +
            '<td><span class="label label-info">' + log.device_user_id + '</span></td>' +
            '<td>' + fullname + '</td>' +
            '<td>' + status + '</td>' +
            '<td>' + action + '</td>' +
            '</tr>';
    }

    // Start auto-refresh on page load
    $(document).ready(function() {
        setInterval(refreshBiometricLogs, 30000); // Refresh every 30 seconds
    });
</script>