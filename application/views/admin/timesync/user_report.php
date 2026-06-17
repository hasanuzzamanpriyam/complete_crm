<div class="row">
    <div class="col-lg-12">
        <section class="panel panel-custom">
            <header class="panel-heading">
                <h3 class="panel-title"><?= $title ?> - <?= htmlspecialchars($user->fullname ?? $user->username) ?></h3>
            </header>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12">
                        <form method="get" class="form-inline mb-lg">
                            <div class="form-group">
                                <label>From: </label>
                                <input type="date" name="from" class="form-control" value="<?= $this->input->get('from') ?? date('Y-m-01') ?>">
                            </div>
                            <div class="form-group ml-sm">
                                <label>To: </label>
                                <input type="date" name="to" class="form-control" value="<?= $this->input->get('to') ?? date('Y-m-d') ?>">
                            </div>
                            <button type="submit" class="btn btn-primary ml-sm">Filter</button>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="panel panel-info">
                            <div class="panel-body text-center">
                                <h2><?= round($total_seconds / 3600, 1) ?>h</h2>
                                <p class="text-muted">Total Hours</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel panel-success">
                            <div class="panel-body text-center">
                                <h2><?= count($entries) ?></h2>
                                <p class="text-muted">Entries</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel panel-warning">
                            <div class="panel-body text-center">
                                <h2><?= count($screenshots) ?></h2>
                                <p class="text-muted">Screenshots</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-lg">
                    <div class="col-md-12">
                        <div class="panel panel-custom">
                            <div class="panel-heading">
                                <h4 class="panel-title">Time Entries</h4>
                            </div>
                            <div class="panel-body">
                                <table class="table table-striped DataTables">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Started</th>
                                            <th>Stopped</th>
                                            <th>Duration</th>
                                            <th>Task ID</th>
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
                                                    <td>
                                                        <a href="<?= base_url('admin/tasks/view/' . $e->task_id) ?>">
                                                            #<?= $e->task_id ?>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="6" class="text-center">No entries found</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-lg">
                    <div class="col-md-12">
                        <div class="panel panel-custom">
                            <div class="panel-heading">
                                <h4 class="panel-title">Screenshots (<?= count($screenshots) ?>)</h4>
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <?php if (!empty($screenshots)): ?>
                                        <?php foreach ($screenshots as $s): ?>
                                            <div class="col-md-3 col-sm-4 col-xs-6 mb-sm">
                                                <a href="<?= base_url('admin/timesync/view_image/' . $s->id) ?>" target="_blank" rel="noopener">
                                                    <img src="<?= base_url('admin/timesync/view_image/' . $s->id) ?>" class="img-responsive img-thumbnail" style="height: 150px; object-fit: cover;">
                                                </a>
                                                <p class="text-center text-muted small">
                                                    <?= date('M d, H:i', strtotime($s->captured_at)) ?>
                                                </p>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="col-md-12">
                                            <p class="text-center">No screenshots for this period</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
