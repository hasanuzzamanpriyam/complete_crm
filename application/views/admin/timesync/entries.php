<div class="row">
    <div class="col-lg-12">
        <section class="panel panel-custom">
            <header class="panel-heading">
                <h3 class="panel-title"><?= $title ?? 'Time Entries' ?></h3>
            </header>
            <div class="panel-body">
                <table class="table table-striped DataTables">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Task</th>
                            <th>Started</th>
                            <th>Stopped</th>
                            <th>Duration</th>
                            <th>Type</th>
                            <th>Running</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($entries)): ?>
                            <?php foreach ($entries as $e): ?>
                                <tr>
                                    <td><?= $e->id ?></td>
                                    <td><?= htmlspecialchars($e->fullname ?? 'N/A') ?></td>
                                    <td>
                                        <?php if (!empty($e->task_id)): ?>
                                            <a href="<?= base_url('admin/tasks/view/' . $e->task_id) ?>">
                                                <?= htmlspecialchars($e->task_name ?? '#' . $e->task_id) ?>
                                            </a>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $e->started_at ?></td>
                                    <td><?= $e->stopped_at ?? '—' ?></td>
                                    <td><?= gmdate('H:i:s', $e->total_seconds) ?></td>
                                    <td><?= htmlspecialchars($e->type ?? '—') ?></td>
                                    <td><?= $e->is_running ? '<span class="label label-success">Yes</span>' : 'No' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center">No entries found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
