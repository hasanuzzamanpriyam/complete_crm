<?= message_box('success'); ?>
<?= message_box('error'); ?>

<div class="panel panel-custom">
    <header class="panel-heading">
        <div class="panel-title"><strong><?= lang('user_management') ?></strong></div>
    </header>
    <div class="panel-body">
        <table class="table table-striped" id="superadmin-user-list" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th><?= lang('name') ?></th>
                    <th><?= lang('username') ?></th>
                    <th><?= lang('email') ?></th>
                    <th><?= lang('department') ?></th>
                    <th><?= lang('designation') ?></th>
                    <th><?= lang('user_type') ?></th>
                    <th><?= lang('is_super_admin') ?></th>
                    <th><?= lang('active') ?></th>
                    <th><?= lang('permissions') ?></th>
                    <th><?= lang('action') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($all_users)): ?>
                    <?php foreach ($all_users as $i => $user): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= $user->fullname ?></td>
                            <td><?= $user->username ?></td>
                            <td><?= $user->email ?></td>
                            <td><?= $user->deptname ?? '-' ?></td>
                            <td><?= $user->designations ?? '-' ?></td>
                            <td>
                                <?php if ($user->role_id == 1): ?>
                                    <span class="label label-primary"><?= lang('admin') ?></span>
                                <?php elseif ($user->role_id == 2): ?>
                                    <span class="label label-success"><?= lang('client') ?></span>
                                <?php elseif ($user->role_id == 3): ?>
                                    <span class="label label-info"><?= lang('staff') ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user->is_super_admin): ?>
                                    <span class="label label-danger"><?= lang('yes') ?></span>
                                <?php else: ?>
                                    <span class="label label-default"><?= lang('no') ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user->activated == 1): ?>
                                    <span class="label label-success"><?= lang('active') ?></span>
                                <?php else: ?>
                                    <span class="label label-warning"><?= lang('deactive') ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                            <?php
                            $permCount = !empty($user->effective_permissions) ? count($user->effective_permissions) : 0;
                            if ($permCount > 0) {
                                // Build popover content
                                $content = '';
                                foreach ($user->effective_permissions as $mid => $perm) {
                                    $menuName = $menu_lookup[$mid] ?? $mid;
                                    $actions = [];
                                    if (!empty($perm->view)) $actions[] = 'V';
                                    if (!empty($perm->created)) $actions[] = 'C';
                                    if (!empty($perm->edited)) $actions[] = 'E';
                                    if (!empty($perm->deleted)) $actions[] = 'D';
                                    $content .= $menuName . ': ' . implode('', $actions) . "<br/>";
                                }
                                $content = htmlspecialchars($content, ENT_QUOTES);
                                ?>
                                <span class="label label-info" data-toggle="popover" data-html="true" title="<?= lang('permissions') ?>" data-content="<?= $content ?>">
                                    <?= $permCount ?> <?= lang('items') ?>
                                </span>
                            <?php } else { ?>
                                <span class="text-muted"><?= lang('no_permissions') ?></span>
                            <?php } ?>
                        </td>
                        <td>
                            <a href="<?= base_url('admin/superadmin/toggle_super_admin/' . $user->user_id) ?>" class="btn btn-xs btn-<?= $user->is_super_admin ? 'warning' : 'danger' ?>" onclick="return confirm('<?= $user->is_super_admin ? lang('remove_super_admin') : lang('make_super_admin') ?>?')">
                                <?= $user->is_super_admin ? lang('remove_super_admin') : lang('make_super_admin') ?>
                            </a>
                            <a href="<?= base_url('admin/superadmin/permissions/' . $user->user_id) ?>" class="btn btn-xs btn-info">
                                <i class="fa fa-lock"></i> <?= lang('permissions') ?>
                            </a>
                        </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<script>
    $(document).ready(function () {
        $('#superadmin-user-list').dataTable({
            "paging": true,
            "pageLength": 25,
            "searching": true,
            "ordering": true,
            "info": true,
            "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]]
        });
        // Initialize Bootstrap popovers for permission tooltips
        $('[data-toggle="popover"]').popover({trigger: 'hover', placement: 'right'});
    });
</script>
