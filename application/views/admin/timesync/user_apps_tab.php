<div class="panel panel-custom">
    <div class="panel-heading">
        <h4 class="panel-title">App Usage</h4>
        <span class="pull-right text-muted small">Page <?= $app_page ?> of <?= $app_total_pages ?></span>
    </div>
    <div class="panel-body">
        <?php if (!empty($app_usage)): ?>
            <!-- <canvas id="appUsageChart" height="100"></canvas> -->
            <table class="table table-striped mt-lg">
                <thead>
                    <tr>
                        <th>Application</th>
                        <th>Window Title</th>
                        <th>URL</th>
                        <th>Duration</th>
                        <th>Time</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($app_usage as $a): ?>
                        <?php
                            $tz_utc = new DateTimeZone('UTC');
                            $tz_local = new DateTimeZone(date_default_timezone_get());
                            $dt = new DateTime($a->recorded_at, $tz_utc);
                            $dt->setTimezone($tz_local);
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($a->app_name) ?></td>
                            <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($a->window_title ?? '') ?>"><?= htmlspecialchars($a->window_title ?? '-') ?></td>
                            <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($a->url ?? '') ?>"><?= !empty($a->url) ? '<a href="'.htmlspecialchars($a->url, ENT_QUOTES, 'UTF-8').'" target="_blank" rel="noopener">'.htmlspecialchars(mb_substr($a->url, 0, 50), ENT_QUOTES, 'UTF-8').'</a>' : '-' ?></td>
                            <td><?= gmdate('H:i:s', $a->total_seconds) ?></td>
                            <td class="ts-log-time"><?= $dt->format('h:i:s A') ?></td>
                            <td><?= $dt->format('M d, Y') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($app_total_pages > 1): ?>
                <div class="text-center" style="margin-top:12px;">
                    <ul class="pagination" style="margin:0;">
                        <li class="<?= $app_page <= 1 ? 'disabled' : '' ?>">
                            <a href="<?= base_url('admin/timesync/user/' . $user_id . '?tab=apps&from=' . $from . '&to=' . $to . '&interval=' . urlencode($interval) . '&app_page=1') ?>">&laquo;</a>
                        </li>
                        <li class="<?= $app_page <= 1 ? 'disabled' : '' ?>">
                            <a href="<?= base_url('admin/timesync/user/' . $user_id . '?tab=apps&from=' . $from . '&to=' . $to . '&interval=' . urlencode($interval) . '&app_page=' . ($app_page - 1)) ?>">&lsaquo;</a>
                        </li>
                        <?php
                            $s = max(1, $app_page - 2);
                            $e = min($app_total_pages, $app_page + 2);
                            for ($p = $s; $p <= $e; $p++):
                        ?>
                            <li class="<?= $p === $app_page ? 'active' : '' ?>">
                                <a href="<?= base_url('admin/timesync/user/' . $user_id . '?tab=apps&from=' . $from . '&to=' . $to . '&interval=' . urlencode($interval) . '&app_page=' . $p) ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="<?= $app_page >= $app_total_pages ? 'disabled' : '' ?>">
                            <a href="<?= base_url('admin/timesync/user/' . $user_id . '?tab=apps&from=' . $from . '&to=' . $to . '&interval=' . urlencode($interval) . '&app_page=' . ($app_page + 1)) ?>">&rsaquo;</a>
                        </li>
                        <li class="<?= $app_page >= $app_total_pages ? 'disabled' : '' ?>">
                            <a href="<?= base_url('admin/timesync/user/' . $user_id . '?tab=apps&from=' . $from . '&to=' . $to . '&interval=' . urlencode($interval) . '&app_page=' . $app_total_pages) ?>">&raquo;</a>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-center">No app usage data for this period</p>
        <?php endif; ?>
    </div>
</div>
