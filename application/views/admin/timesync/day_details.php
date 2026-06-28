<div class="row">
    <div class="col-lg-12">
        <section class="panel panel-custom">
            <header class="panel-heading">
                <h3 class="panel-title"><?= $title ?? ('Details for ' . $selected_date) ?></h3>
            </header>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12">
                        <h4><?= $selected_date ?> — Total: <?= round($total_seconds / 3600, 1) ?>h (<?= $total_seconds ?>s)</h4>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Task</th>
                                    <th>Start</th>
                                    <th>Stop</th>
                                    <th>Duration</th>
                                    <th>Running</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($entries)): ?>
                                    <?php foreach ($entries as $e): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($e->fullname ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($e->task_name ?? 'N/A') ?></td>
                                            <td><?= $e->started_at ?></td>
                                            <td><?= $e->stopped_at ?? 'Running' ?></td>
                                            <td><?= round($e->total_seconds / 3600, 2) ?>h</td>
                                            <td><?= $e->is_running ? 'Yes' : 'No' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center">No entries for this date</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>