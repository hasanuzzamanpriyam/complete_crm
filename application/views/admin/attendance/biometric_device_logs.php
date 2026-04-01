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
                                    <td><?= date('d M, Y h:i:s A', strtotime($log->timestamp)) ?></td>
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
                                            <a href="<?= base_url('admin/attendance/biometric_mapping/'.$log->device_user_id) ?>" class="btn btn-primary btn-xs">
                                                <i class="fa fa-link"></i> Map User
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
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
