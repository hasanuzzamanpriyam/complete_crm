<?= message_box('success'); ?>
<?= message_box('error'); ?>

<div class="panel panel-custom">
    <header class="panel-heading">
        <div class="panel-title"><strong><?= lang('super_admin_settings') ?></strong></div>
    </header>
    <div class="panel-body">
        <?= form_open('admin/superadmin/settings', array('role' => 'form')) ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?= lang('settings') ?></th>
                    <th><?= lang('value') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($config)): ?>
                    <?php foreach ($config as $item): ?>
                        <tr>
                            <td><?= lang($item->config_key) ?: $item->config_key ?></td>
                            <td>
                                <input type="text" name="settings[<?= $item->config_key ?>]" value="<?= $item->value ?>" class="form-control">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" class="text-center text-muted"><?= lang('no_record_found') ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="form-group">
            <button type="submit" class="btn btn-primary"><?= lang('save') ?></button>
        </div>
        <?= form_close() ?>
    </div>
</div>
