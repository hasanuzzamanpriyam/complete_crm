<?php $this->load->view('admin/timesync/_date_navigation'); ?>
<div class="row">
    <div class="col-lg-12">
        <section class="panel panel-custom">
            <header class="panel-heading">
                <h3 class="panel-title"><?= $title ?? 'TimeSync Dashboard' ?></h3>
            </header>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-15 col-sm-3 col-xs-6">
                        <div class="panel" style="border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06);border:0;">
                            <div class="panel-body text-center" id="today_hours" style="background:linear-gradient(135deg,#5bc0de,#46b8da);color:#fff;padding:18px 8px;">
                                <i class="fa fa-clock-o" style="font-size:22px;opacity:.8;display:block;margin-bottom:4px;"></i>
                                <h2 style="margin:2px 0;font-weight:700;font-size:26px;"><?= $today_hours ?>h</h2>
                                <p style="margin:0;opacity:.85;font-size:12px;">Today</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-15 col-sm-3 col-xs-6">
                        <div class="panel" style="border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06);border:0;">
                            <div class="panel-body text-center" id="week_hours" style="background:linear-gradient(135deg,#5cb85c,#4cae4c);color:#fff;padding:18px 8px;">
                                <i class="fa fa-calendar" style="font-size:22px;opacity:.8;display:block;margin-bottom:4px;"></i>
                                <h2 style="margin:2px 0;font-weight:700;font-size:26px;"><?= $week_hours ?>h</h2>
                                <p style="margin:0;opacity:.85;font-size:12px;">This Week</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-15 col-sm-3 col-xs-6">
                        <div class="panel" style="border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06);border:0;">
                            <div class="panel-body text-center" id="month_hours" style="background:linear-gradient(135deg,#f0ad4e,#eea236);color:#fff;padding:18px 8px;">
                                <i class="fa fa-calendar-check-o" style="font-size:22px;opacity:.8;display:block;margin-bottom:4px;"></i>
                                <h2 style="margin:2px 0;font-weight:700;font-size:26px;"><?= $month_hours ?>h</h2>
                                <p style="margin:0;opacity:.85;font-size:12px;">This Month</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-15 col-sm-3 col-xs-6">
                        <div class="panel" style="border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06);border:0;">
                            <div class="panel-body text-center" id="active_users" style="background:linear-gradient(135deg,#428bca,#3071a9);color:#fff;padding:18px 8px;">
                                <i class="fa fa-users" style="font-size:22px;opacity:.8;display:block;margin-bottom:4px;"></i>
                                <h2 style="margin:2px 0;font-weight:700;font-size:26px;"><?= $active_users ?></h2>
                                <p style="margin:0;opacity:.85;font-size:12px;">Active Now</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-15 col-sm-3 col-xs-6">
                        <div class="panel" style="border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06);border:0;">
                            <div class="panel-body text-center" id="period_hours" style="background:linear-gradient(135deg,#d9534f,#c9302c);color:#fff;padding:18px 8px;">
                                <i class="fa fa-hourglass-half" style="font-size:22px;opacity:.8;display:block;margin-bottom:4px;"></i>
                                <h2 style="margin:2px 0;font-weight:700;font-size:26px;"><?= $period_hours ?>h</h2>
                                <p style="margin:0;opacity:.85;font-size:12px;">Period</p>
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

<script src="<?= base_url() ?>assets/plugins/Chart.js/Chart.js"></script>
<script>
$(function() {
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
});
</script>

