<?= message_box('success'); ?>
<?= message_box('error'); ?>

<div class="panel panel-custom">
    <header class="panel-heading">
        <div class="panel-title"><strong><?= lang('audit_logs') ?></strong></div>
    </header>
    <div class="panel-body">
        <!-- Filters -->
        <form method="get" action="<?= base_url('admin/superadmin/audit_logs') ?>" class="form-inline mb-lg">
            <div class="form-group">
                <label><?= lang('audit_user') ?>:</label>
                <select name="user_id" class="form-control">
                    <option value=""><?= lang('all') ?></option>
                    <?php foreach ($users_filter as $u): ?>
                        <option value="<?= $u->user_id ?>" <?= $this->input->get('user_id') == $u->user_id ? 'selected' : '' ?>>
                            <?= $u->username ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><?= lang('audit_action') ?>:</label>
                <select name="action" class="form-control">
                    <option value=""><?= lang('all') ?></option>
                    <?php foreach ($actions_filter as $a): ?>
                        <option value="<?= $a->action ?>" <?= $this->input->get('action') == $a->action ? 'selected' : '' ?>>
                            <?= $a->action ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><?= lang('audit_module') ?>:</label>
                <select name="module" class="form-control">
                    <option value=""><?= lang('all') ?></option>
                    <?php foreach ($modules_filter as $m): ?>
                        <option value="<?= $m->module ?>" <?= $this->input->get('module') == $m->module ? 'selected' : '' ?>>
                            <?= $m->module ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><?= lang('from') ?>:</label>
                <input type="date" name="from" class="form-control" value="<?= $this->input->get('from') ?>">
            </div>
            <div class="form-group">
                <label><?= lang('to') ?>:</label>
                <input type="date" name="to" class="form-control" value="<?= $this->input->get('to') ?>">
            </div>
            <button type="submit" class="btn btn-default"><?= lang('search') ?></button>
        </form>

        <table class="table table-striped" id="audit-logs-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th><?= lang('audit_user') ?></th>
                    <th><?= lang('audit_action') ?></th>
                    <th><?= lang('audit_module') ?></th>
                    <th><?= lang('audit_ip') ?></th>
                    <th><?= lang('audit_details') ?></th>
                    <th><?= lang('audit_date') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($logs)): ?>
                    <?php foreach ($logs as $i => $log): ?>
                        <tr>
                            <td><?= $offset + $i + 1 ?></td>
                            <td><?= $log->user_id ? $log->user_id : '-' ?></td>
                            <td><?= $log->action ?></td>
                            <td><?= $log->module ?></td>
                            <td><?= $log->ip_address ?></td>
                            <td><?= $log->details ?? '-' ?></td>
                            <td><?= date('Y-m-d H:i', strtotime($log->created_at)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center"><?= lang('no_audit_logs') ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($total > $per_page): ?>
            <div class="text-center">
                <ul class="pagination">
                    <?php for ($p = 0; $p < $total; $p += $per_page): ?>
                        <li class="<?= $offset == $p ? 'active' : '' ?>">
                            <a href="<?= current_url() ?>?per_page=<?= $p ?><?= !empty($this->input->get('user_id')) ? '&user_id=' . $this->input->get('user_id') : '' ?><?= !empty($this->input->get('action')) ? '&action=' . $this->input->get('action') : '' ?><?= !empty($this->input->get('module')) ? '&module=' . $this->input->get('module') : '' ?><?= !empty($this->input->get('from')) ? '&from=' . $this->input->get('from') : '' ?><?= !empty($this->input->get('to')) ? '&to=' . $this->input->get('to') : '' ?>">
                                <?= ($p / $per_page) + 1 ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

