<?= message_box('success'); ?>
<?= message_box('error'); ?>

<div class="row">
    <div class="col-sm-3">
        <div class="panel panel-custom">
            <div class="panel-body">
                <div class="icon"><i class="fa fa-users"></i></div>
                <p class="text-muted font-bold"><?= lang('total_users') ?></p>
                <h3><?= $total_users ?></h3>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="panel panel-custom">
            <div class="panel-body">
                <div class="icon"><i class="fa fa-user-secret"></i></div>
                <p class="text-muted font-bold"><?= lang('total_super_admins') ?></p>
                <h3><?= $total_super_admins ?></h3>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="panel panel-custom">
            <div class="panel-body">
                <div class="icon"><i class="fa fa-user-md"></i></div>
                <p class="text-muted font-bold"><?= lang('total_admins') ?></p>
                <h3><?= $total_admins ?></h3>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="panel panel-custom">
            <div class="panel-body">
                <div class="icon"><i class="fa fa-user"></i></div>
                <p class="text-muted font-bold"><?= lang('total_staff') ?></p>
                <h3><?= $total_staff ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <div class="panel panel-custom">
            <header class="panel-heading">
                <div class="panel-title"><strong><?= lang('recent_activity') ?></strong></div>
            </header>
            <div class="panel-body">
                <?php if (!empty($recent_logs)): ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><?= lang('audit_action') ?></th>
                                <th><?= lang('audit_module') ?></th>
                                <th><?= lang('date') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_logs as $log): ?>
                                <tr>
                                    <td><?= lang($log->action) ?></td>
                                    <td><?= lang($log->module) ?></td>
                                    <td><?= time_ago($log->created_at) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted"><?= lang('no_audit_logs') ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="panel panel-custom">
            <header class="panel-heading">
                <div class="panel-title"><strong><?= lang('system_health') ?></strong></div>
            </header>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-6">
                        <strong><?= lang('database') ?>:</strong> <?= $db_size ?> MB
                    </div>
                    <div class="col-sm-6">
                        <strong>PHP:</strong> <?= $php_version ?>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-6">
                        <strong>CodeIgniter:</strong> <?= $ci_version ?>
                    </div>
                </div>
                <hr>
                <a href="<?= base_url('admin/superadmin/clear_cache') ?>" class="btn btn-danger" onclick="return confirm('<?= lang('cache_clear') ?>?')">
                    <i class="fa fa-trash"></i> <?= lang('cache_clear') ?>
                </a>
            </div>
        </div>
    </div>
</div>
