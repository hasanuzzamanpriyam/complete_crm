<div class="row">
    <div class="col-lg-12">
        <section class="panel panel-custom">
            <header class="panel-heading">
                <h3 class="panel-title"><?= $title ?? 'TimeSync Dashboard' ?></h3>
            </header>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="panel panel-info">
                            <div class="panel-body text-center">
                                <h2><?= $today_hours ?>h</h2>
                                <p class="text-muted">Today's Hours</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel panel-success">
                            <div class="panel-body text-center">
                                <h2><?= $week_hours ?>h</h2>
                                <p class="text-muted">This Week</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel panel-warning">
                            <div class="panel-body text-center">
                                <h2><?= $month_hours ?>h</h2>
                                <p class="text-muted">This Month</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel panel-primary">
                            <div class="panel-body text-center">
                                <h2><?= $active_users ?></h2>
                                <p class="text-muted">Active Users</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-lg">
                    <div class="col-md-6">
                        <div class="panel panel-custom">
                            <div class="panel-heading">
                                <h4 class="panel-title">Statistics</h4>
                            </div>
                            <div class="panel-body">
                                <table class="table table-striped">
                                    <tr>
                                        <td>Total Time Entries</td>
                                        <td><strong><?= $total_entries ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td>Total Screenshots</td>
                                        <td><strong><?= $total_screenshots ?></strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="panel panel-custom">
                            <div class="panel-heading">
                                <h4 class="panel-title">Top Users Today</h4>
                            </div>
                            <div class="panel-body">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Hours</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($top_users)): ?>
                                            <?php foreach ($top_users as $u): ?>
                                                <tr>
                                                    <td>
                                                        <a href="<?= base_url('admin/timesync/user/' . $u->user_id) ?>">
                                                            <?= htmlspecialchars($u->fullname ?? 'User #' . $u->user_id) ?>
                                                        </a>
                                                    </td>
                                                    <td><?= round($u->total_sec / 3600, 1) ?>h</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="2" class="text-center">No data yet</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
