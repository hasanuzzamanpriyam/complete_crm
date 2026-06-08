<?= message_box('success'); ?>
<?= message_box('error'); ?>

<?php if (empty($selected_user)): ?>
    <div class="panel panel-custom">
        <header class="panel-heading">
            <div class="panel-title"><strong><?= lang('select_user') ?></strong></div>
        </header>
        <div class="panel-body">
            <?= lang('select_a_user_to_assign_permissions') ?>
        </div>
    </div>
<?php else: ?>
    <div class="panel panel-custom">
        <header class="panel-heading">
            <div class="panel-title">
                <strong><?= lang('permission_manager') ?>: <?= $selected_user->fullname ?> (<?= $selected_user->username ?>)</strong>
            </div>
        </header>
        <div class="panel-body">
            <p class="text-muted">
                <?= lang('permission_inherited') ?> &mdash;
                <span class="label label-info"><?= lang('permission_overridden') ?></span>
            </p>

            <?= form_open('admin/superadmin/save_permissions', array('role' => 'form')) ?>
            <input type="hidden" name="user_id" value="<?= $selected_user->user_id ?>">

            <table class="table table-striped" id="permission-grid">
                <thead>
                    <tr>
                        <th><?= lang('menu') ?></th>
                        <th class="col-sm-1 text-center"><?= lang('view') ?></th>
                        <th class="col-sm-1 text-center"><?= lang('create') ?></th>
                        <th class="col-sm-1 text-center"><?= lang('edit') ?></th>
                        <th class="col-sm-1 text-center"><?= lang('delete') ?></th>
                        <th class="col-sm-1 text-center">
                            <label class="checkbox-inline">
                                <input type="checkbox" id="select_all_perms" onchange="toggleAllPerms(this.checked)">
                                <?= lang('select_all') ?>
                            </label>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menu_tree as $menu): ?>
                        <?= _render_permission_row($menu, $designation_perms, $user_perms, 0, $parents) ?>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="form-group">
                <button type="submit" class="btn btn-primary"><?= lang('save') ?></button>
                <a href="<?= base_url('admin/superadmin/users') ?>" class="btn btn-default"><?= lang('cancel') ?></a>
            </div>
            <?= form_close() ?>
        </div>
    </div>
<?php endif; ?>

<script>
    function toggleAllPerms(checked) {
        document.querySelectorAll('#permission-grid tbody input[type="checkbox"]').forEach(function(cb) {
            cb.checked = checked;
        });
    }
</script>
