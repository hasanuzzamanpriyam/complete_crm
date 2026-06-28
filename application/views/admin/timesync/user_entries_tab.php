<div class="panel panel-custom">
    <div class="panel-heading"><h4 class="panel-title">Time Entries</h4></div>
    <div class="panel-body">
        <table class="table table-striped DataTables">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Started</th>
                    <th>Stopped</th>
                    <th>Duration</th>
                    <th>Task</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($entries)): ?>
                    <?php foreach ($entries as $e): ?>
                        <tr>
                            <td><?= date('Y-m-d', strtotime($e->started_at)) ?></td>
                            <td><?= htmlspecialchars($e->type) ?></td>
                            <td><?= $e->started_at ? date('H:i:s', strtotime($e->started_at)) : '-' ?></td>
                            <td><?= $e->stopped_at ? date('H:i:s', strtotime($e->stopped_at)) : '-' ?></td>
                            <td><?= gmdate('H:i:s', $e->total_seconds) ?></td>
                            <td><a href="<?= base_url('admin/tasks/view/' . $e->task_id) ?>">#<?= $e->task_id ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">No entries found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
