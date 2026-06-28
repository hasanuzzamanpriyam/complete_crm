<div class="panel panel-custom">
    <div class="panel-heading"><h4 class="panel-title">App Usage</h4></div>
    <div class="panel-body">
        <?php if (!empty($app_usage)): ?>
            <canvas id="appUsageChart" height="100"></canvas>
            <table class="table table-striped mt-lg">
                <thead>
                    <tr>
                        <th>Application</th>
                        <th>Total Time</th>
                        <th>Occurrences</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($app_usage as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a->app_name) ?></td>
                            <td><?= gmdate('H:i:s', $a->total_sec) ?></td>
                            <td><?= $a->occurrences ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">No app usage data for this period</p>
        <?php endif; ?>
    </div>
</div>
