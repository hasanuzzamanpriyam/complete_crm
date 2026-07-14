<?php
function fmt_hms($sec) {
    $sec = max(0, (int)$sec);
    $h = floor($sec / 3600);
    $m = floor(($sec % 3600) / 60);
    if ($h > 0) return $h . 'h ' . $m . 'm';
    return $m . 'm';
}
function fmt_h($sec) {
    return round(max(0, (int)$sec) / 3600, 1);
}
$logged_h = fmt_h($total_logged_seconds);
$activity_h = fmt_h($total_activity_seconds);
$req_h = fmt_h($required_seconds);
$disc_sec = $discrepancy_seconds;
$is_shortage = $disc_sec < 0;
$disc_h = fmt_h(abs($disc_sec));
$base_url = base_url();
?>
<link rel="stylesheet" href="<?= $base_url ?>assets/css/timesync-dashboard.css">
<?php $this->load->view('admin/timesync/_date_navigation'); ?>
<div class="ts-dash-wrapper">
    <aside class="ts-sidebar">
        <div class="ts-sidebar-header">
            <input type="text" id="tsUserSearch" placeholder="Search users..." autocomplete="off">
        </div>
        <div class="ts-user-list" id="tsUserList">
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $u):
                    $is_active = !empty($selected_user_id) && (int)$selected_user_id === (int)$u->user_id;
                    $avatar_url = get_avatar_url($u->avatar, $u->fullname);
                    $logged = fmt_h($u->total_sec);
                    $activity = fmt_h($u->activity_sec);
                ?>
                    <a href="<?= $base_url ?>admin/timesync?from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>&user_id=<?= $u->user_id ?>"
                       class="ts-user-item<?= $is_active ? ' active' : '' ?>"
                       data-name="<?= htmlspecialchars(strtolower($u->fullname ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        <img src="<?= $avatar_url ?>" alt="" class="avatar" loading="lazy">
                        <div class="ts-user-info">
                            <div class="ts-user-name">
                                <?= htmlspecialchars($u->fullname ?? 'User #' . $u->user_id) ?>
                                <?php if ($u->has_shortage): ?>
                                    <span class="badge-shortage">-<?= fmt_h($u->shortage_hours * 3600) ?>h</span>
                                <?php endif; ?>
                            </div>
                            <div class="ts-user-meta">
                                <span class="logged-val"><?= $logged ?>h</span>
                                <span class="activity-val"><?= $activity ?>h</span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="ts-placeholder">No users found</div>
            <?php endif; ?>
        </div>
    </aside>
    <main class="ts-main">
        <div class="ts-metric-row">
            <div class="ts-metric-card">
                <div class="ts-metric-label">Time Logged</div>
                <div class="ts-metric-value"><?= $logged_h ?>h</div>
                <div style="font-size:12px;color:#6c757d;">In selected period</div>
            </div>
            <div class="ts-metric-card">
                <div class="ts-metric-label">Activity Time</div>
                <div class="ts-metric-value"><?= $activity_h ?>h</div>
                <div class="ts-progress-track">
                    <div class="ts-progress-fill <?= $productive_ratio >= 70 ? 'good' : ($productive_ratio >= 40 ? 'warn' : 'bad') ?>" style="width:<?= min(100, $productive_ratio) ?>%;"></div>
                </div>
                <div style="font-size:12px;color:#6c757d;margin-top:4px;"><?= $productive_ratio ?>% productive</div>
            </div>
            <div class="ts-metric-card" style="display:flex;flex-direction:column;justify-content:center;">
                <div class="ts-metric-label">Required Hours</div>
                <div class="ts-metric-value"><?= $req_h ?>h</div>
                                <div style="font-size:12px;color:#6c757d;"><?php if (!empty($selected_user_id)): ?>@ <?= number_format($required_daily_avg, 1) ?>h/day &mdash; <?= $adjusted_working_days ?? $working_days ?> working days<?php else: ?>Across <?= count($users) ?> users &times; <?= $working_days ?> working days<?php endif; ?></div>
            </div>
        </div>

        <?php if (!empty($selected_user_id) && isset($selected_user)): ?>
        <div class="ts-panel" style="margin-bottom:12px;">
            <div class="ts-panel-heading">
                <img src="<?= get_avatar_url($selected_user->avatar ?? null, $selected_user->fullname ?? null) ?>" alt="" style="width:28px;height:28px;border-radius:50%;object-fit:cover;">
                <?= htmlspecialchars($selected_user->fullname ?? 'User') ?>
                <a href="<?= $base_url ?>admin/timesync/user/<?= (int)$selected_user->user_id ?>" class="pull-right" style="font-size:12px;font-weight:400;margin-left:auto;">Full Report &rarr;</a>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($is_shortage): ?>
        <div class="ts-alert ts-alert-danger">
            <div class="ts-alert-icon"><i class="fa fa-exclamation-triangle"></i></div>
            <div class="ts-alert-body">
                <div class="ts-alert-title">Time Shortage of <?= $disc_h ?></div>
                <div class="ts-alert-sub">Required: <?= $req_h ?>h &middot; Logged: <?= $logged_h ?>h &middot; Period: <?= htmlspecialchars($from) ?> &rarr; <?= htmlspecialchars($to) ?> (<?= $adjusted_working_days ?? $working_days ?> working days<?php if (!empty($selected_user_id) && isset($adjusted_working_days) && $adjusted_working_days !== $working_days): ?>, <?= $working_days - $adjusted_working_days ?> leave days excluded<?php endif; ?>)</div>
            </div>
        </div>
        <?php elseif ($disc_sec > 0): ?>
        <div class="ts-alert ts-alert-success">
            <div class="ts-alert-icon"><i class="fa fa-check-circle"></i></div>
            <div class="ts-alert-body">
                <div class="ts-alert-title">Surplus of <?= $disc_h ?></div>
                <div class="ts-alert-sub">Required: <?= $req_h ?>h &middot; Logged: <?= $logged_h ?>h &middot; Period: <?= htmlspecialchars($from) ?> &rarr; <?= htmlspecialchars($to) ?> (<?= $adjusted_working_days ?? $working_days ?> working days<?php if (!empty($selected_user_id) && isset($adjusted_working_days) && $adjusted_working_days !== $working_days): ?>, <?= $working_days - $adjusted_working_days ?> leave days excluded<?php endif; ?>)</div>
            </div>
        </div>
        <?php endif; ?>

        <div class="ts-panel">
            <div class="ts-panel-heading"><i class="fa fa-calendar-o"></i> Days Off / Leaves</div>
            <div class="ts-panel-body">
                <?php if (!empty($holidays)): ?>
                    <h5 style="margin:0 0 8px 0;font-size:13px;font-weight:600;">Company Holidays</h5>
                    <ul style="list-style:none;padding:0;margin:0 0 12px 0;">
                        <?php foreach ($holidays as $h):
                            $hd_from = htmlspecialchars($h->start_date);
                            $hd_to = $h->end_date !== $h->start_date ? ' &rarr; ' . htmlspecialchars($h->end_date) : '';
                        ?>
                            <li style="padding:2px 0;font-size:12px;">
                                <span style="color:#e74c3c;margin-right:6px;">&#9679;</span>
                                <?= $hd_from . $hd_to ?>
                                <?php if (!empty($h->event_name)): ?>
                                    &mdash; <?= htmlspecialchars($h->event_name) ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (!empty($user_leaves)): ?>
                    <h5 style="margin:0 0 8px 0;font-size:13px;font-weight:600;">Approved Leaves</h5>
                    <ul style="list-style:none;padding:0;margin:0;">
                        <?php foreach ($user_leaves as $l):
                            $lf = htmlspecialchars($l->leave_start_date);
                            $lt = $l->leave_end_date !== $l->leave_start_date ? ' &rarr; ' . htmlspecialchars($l->leave_end_date) : '';
                        ?>
                            <li style="padding:2px 0;font-size:12px;">
                                <span style="color:#f39c12;margin-right:6px;">&#9679;</span>
                                <?= $lf . $lt ?>
                                <?php if (!empty($l->reason)): ?>
                                    &mdash; <?= htmlspecialchars($l->reason) ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (empty($holidays) && empty($user_leaves)): ?>
                    <div class="ts-placeholder">No days off in this period.</div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
$(function() {
    var $search = $('#tsUserSearch');
    var $items = $('#tsUserList').find('.ts-user-item');
    $search.on('input', function() {
        var q = this.value.toLowerCase().trim();
        $items.each(function() {
            var name = $(this).data('name') || '';
            $(this).toggle(name.indexOf(q) !== -1);
        });
    });
});
</script>

