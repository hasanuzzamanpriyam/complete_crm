<div class="row">
    <div class="col-lg-12">
        <section class="panel panel-custom">
            <header class="panel-heading">
                <h3 class="panel-title"><?= $title ?? 'TimeSync Dashboard' ?></h3>
                <span class="pull-right">
                    <form method="get" class="form-inline" style="display:inline-block;">
                        <div class="form-group" style="margin:0 5px;">
                            <input type="date" name="from" class="form-control input-sm" value="<?= $from ?>" style="height:28px;font-size:12px;">
                        </div>
                        <div class="form-group" style="margin:0 5px;">
                            <input type="date" name="to" class="form-control input-sm" value="<?= $to ?>" style="height:28px;font-size:12px;">
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    </form>
                </span>
            </header>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-15 col-sm-3 col-xs-6">
                        <div class="panel panel-info">
                            <div class="panel-body text-center" id="today_hours">
                                <h2><?= $today_hours ?>h</h2>
                                <p class="text-muted">Today</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-15 col-sm-3 col-xs-6">
                        <div class="panel panel-success">
                            <div class="panel-body text-center" id="week_hours">
                                <h2><?= $week_hours ?>h</h2>
                                <p class="text-muted">This Week</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-15 col-sm-3 col-xs-6">
                        <div class="panel panel-warning">
                            <div class="panel-body text-center" id="month_hours">
                                <h2><?= $month_hours ?>h</h2>
                                <p class="text-muted">This Month</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-15 col-sm-3 col-xs-6">
                        <div class="panel panel-primary">
                            <div class="panel-body text-center" id="active_users">
                                <h2><?= $active_users ?></h2>
                                <p class="text-muted">Active Now</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-15 col-sm-3 col-xs-6">
                        <div class="panel panel-danger">
                            <div class="panel-body text-center" id="period_hours">
                                <h2><?= $period_hours ?>h</h2>
                                <p class="text-muted">Period</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-lg" id="userGridCard">
                    <div class="col-md-4">
                        <div class="panel panel-custom">
                            <div class="panel-heading"><h4 class="panel-title">Daily Hours</h4></div>
                            <div class="panel-body">
                                <canvas id="dailyHoursChart" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="panel panel-custom">
                            <div class="panel-heading"><h4 class="panel-title">User Distribution</h4></div>
                            <div class="panel-body">
                                <canvas id="userDistChart" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="panel panel-custom">
                            <div class="panel-heading"><h4 class="panel-title">Top Users</h4></div>
                            <div class="panel-body" style="max-height:250px;overflow-y:auto;">
                                <table class="table table-condensed table-striped" style="margin:0;">
                                    <thead><tr><th>User</th><th>Hours</th><th>Entries</th></tr></thead>
                                    <tbody>
                                        <?php if (!empty($user_distribution_raw)): ?>
                                            <?php foreach ($user_distribution_raw as $u): ?>
                                                <tr>
                                                    <td><a href="<?= base_url('admin/timesync/user/' . $u->user_id) ?>"><?= htmlspecialchars($u->fullname ?? 'User #' . $u->user_id) ?></a></td>
                                                    <td><?= round($u->total_sec / 3600, 1) ?></td>
                                                    <td><?= $u->entry_count ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="3" class="text-center">No data</td></tr>
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
                            <div class="panel-heading"><h4 class="panel-title">User Activity Grid</h4></div>
                            <div class="panel-body" style="max-height:400px;overflow-y:auto;">
                                <table class="table table-striped table-condensed DataTables">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Hours</th>
                                            <th>Entries</th>
                                            <th>Screenshots</th>
                                            <th>Status</th>
                                            <th>Last Active</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($user_grid)): ?>
                                            <?php foreach ($user_grid as $u): ?>
                                                <tr>
                                                    <td>
                                                        <a href="<?= base_url('admin/timesync/user/' . $u->user_id) ?>">
                                                            <?= htmlspecialchars($u->fullname ?? 'User #' . $u->user_id) ?>
                                                        </a>
                                                    </td>
                                                    <td><?= round($u->total_sec / 3600, 1) ?>h</td>
                                                    <td><?= $u->entry_count ?></td>
                                                    <td><?= $u->screenshot_count ?></td>
                                                    <td><?= !empty($u->last_active) ? date('M d, H:i', strtotime($u->last_active)) : '—' ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="5" class="text-center">No activity data</td></tr>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function() {
    var labels = <?= $daily_chart_labels ?? '[]' ?>;
    var values = <?= $daily_chart_values ?? '[]' ?>;
    var dist = <?= $user_distribution ?? '[]' ?>;

    if (labels.length > 0 && document.getElementById('dailyHoursChart')) {
        new Chart(document.getElementById('dailyHoursChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Hours',
                    data: values,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    if (dist.length > 0 && document.getElementById('userDistChart')) {
        var distLabels = dist.map(function(d) { return d.fullname ? d.fullname.substring(0, 10) : 'User ' + d.user_id; });
        var distValues = dist.map(function(d) { return d.total_sec / 3600; });
        new Chart(document.getElementById('userDistChart'), {
            type: 'doughnut',
            data: {
                labels: distLabels,
                datasets: [{
                    data: distValues,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(255, 159, 64, 0.6)',
                        'rgba(201, 203, 207, 0.6)',
                        'rgba(34, 139, 34, 0.6)',
                        'rgba(220, 20, 60, 0.6)',
                        'rgba(0, 0, 139, 0.6)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } }
            }
        });
    }
})();
</script>

