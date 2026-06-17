<div class="row">
    <div class="col-lg-12">
        <section class="panel panel-custom">
            <header class="panel-heading">
                <h3 class="panel-title"><?= $title ?? 'App Usage Reports' ?></h3>
            </header>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12">
                        <form method="get" class="form-inline mb-lg">
                            <div class="form-group">
                                <label>User: </label>
                                <select name="user_id" class="form-control">
                                    <option value="">All Users</option>
                                    <?php if (!empty($users)): ?>
                                        <?php foreach ($users as $u): ?>
                                            <option value="<?= $u->user_id ?>" <?= $selected_user_id == $u->user_id ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($u->fullname ?? 'User #' . $u->user_id) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="form-group ml-sm">
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

                <?php if (!empty($user_scores)): ?>
                <div class="row mb-lg">
                    <?php foreach ($user_scores as $uid => $score): ?>
                    <div class="col-md-3">
                        <div class="panel panel-info">
                            <div class="panel-body text-center">
                                <h2><?= round($score['total_seconds'] / 3600, 1) ?>h</h2>
                                <p class="text-muted">Total Hours</p>
                                <div class="progress" style="height: 8px; margin-bottom: 5px;">
                                    <div class="progress-bar progress-bar-success" style="width: <?= $score['focus_score'] ?>%"></div>
                                </div>
                                <p class="small">Focus: <?= $score['focus_score'] ?>%</p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-custom">
                            <div class="panel-heading">
                                <h4 class="panel-title">App Usage Breakdown</h4>
                            </div>
                            <div class="panel-body">
                                <table class="table table-striped DataTables">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>User</th>
                                            <th>Application</th>
                                            <th>Window Title</th>
                                            <th>Duration</th>
                                            <th>% of Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($records)): ?>
                                            <?php
                                            $date_user_key = '';
                                            $total_seconds_for_user_date = 0;
                                            ?>
                                            <?php foreach ($records as $r): ?>
                                                <?php
                                                $current_key = $r->recorded_at . '_' . $r->user_id;
                                                if ($current_key !== $date_user_key) {
                                                    $date_user_key = $current_key;
                                                    $uid = $r->user_id;
                                                    $total_seconds_for_user_date = $user_scores[$uid]['total_seconds'] ?? 1;
                                                }
                                                $pct = $total_seconds_for_user_date > 0
                                                    ? round(($r->total_seconds / $total_seconds_for_user_date) * 100, 1)
                                                    : 0;
                                                ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($r->recorded_at) ?></td>
                                                    <td><?= htmlspecialchars($r->fullname ?? $r->username ?? 'User #' . $r->user_id) ?></td>
                                                    <td><?= htmlspecialchars($r->app_name) ?></td>
                                                    <td class="max-w-xs truncate" title="<?= htmlspecialchars($r->window_title ?? '') ?>">
                                                        <?= htmlspecialchars(mb_substr($r->window_title ?? '—', 0, 60)) ?>
                                                    </td>
                                                    <td><?= gmdate('H:i:s', $r->total_seconds) ?></td>
                                                    <td>
                                                        <div class="progress" style="height: 6px; margin-bottom: 2px; min-width: 80px; display: inline-block; vertical-align: middle;">
                                                            <div class="progress-bar" style="width: <?= $pct ?>%"></div>
                                                        </div>
                                                        <span class="small"><?= $pct ?>%</span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="6" class="text-center">No app usage data found for this period</td></tr>
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
