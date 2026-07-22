<?php
function fmt_hms($sec) {
    $sec = max(0, (int)$sec);
    $h = floor($sec / 3600);
    $m = floor(($sec % 3600) / 60);
    $s = $sec % 60;
    return $h . 'h ' . $m . 'm ' . $s . 's';
}
function fmt_h($sec) {
    return round(max(0, (int)$sec) / 3600, 1);
}
$logged_hms = fmt_hms($total_logged_seconds);
$activity_hms = fmt_hms($total_activity_seconds);
$req_hms = fmt_hms($required_seconds);
$disc_sec = $discrepancy_seconds;
$is_shortage = $disc_sec < 0;
$disc_hms = fmt_hms(abs($disc_sec));
$base_url = base_url();

function sidebar_initials($name) {
    if (empty($name)) return '?';
    $parts = explode(' ', $name);
    $initials = '';
    foreach ($parts as $p) {
        $initials .= strtoupper($p[0] ?? '');
    }
    return substr($initials, 0, 2) ?: '?';
}
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
                    $has_avatar = !empty($u->avatar) && file_exists(FCPATH . $u->avatar);
                    $logged = fmt_hms($u->total_sec);
                    $activity = fmt_hms($u->activity_sec);
                ?>
                    <a href="<?= $base_url ?>admin/timesync?from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>&user_id=<?= $u->user_id ?>"
                       class="ts-user-item<?= $is_active ? ' active' : '' ?>"
                       data-user-id="<?= (int)$u->user_id ?>"
                       data-name="<?= htmlspecialchars(strtolower($u->fullname ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        <span class="ts-user-indicator"></span>
                        <?php if ($has_avatar): ?>
                            <img src="<?= base_url() . $u->avatar ?>" alt="" class="avatar" loading="lazy">
                        <?php else:
                            $sidebar_bg = 'ts-avatar-bg-' . ((int)$u->user_id % 10);
                            $sidebar_init = sidebar_initials($u->fullname);
                        ?>
                            <div class="avatar ts-avatar-fallback <?= $sidebar_bg ?>"><?= $sidebar_init ?></div>
                        <?php endif; ?>
                        <div class="ts-user-info">
                            <div class="ts-user-name">
                                <?= htmlspecialchars($u->fullname ?? 'User #' . $u->user_id) ?>
                                <span class="ts-user-badge ts-user-badge-offline">OFFLINE</span>
                                <?php if ($u->has_shortage): ?>
                                    <span class="badge-shortage">-<?= fmt_hms($u->shortage_hours * 3600) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="ts-user-meta">
                                <span class="logged-val"><?= $logged ?></span>
                                <span class="activity-val"><?= $activity ?></span>
                            </div>
                            <div class="ts-user-live-meta" style="display:none;"></div>
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
                <div class="ts-metric-value"><?= $logged_hms ?></div>
                <div style="font-size:12px;color:#6c757d;">In selected period</div>
            </div>
            <div class="ts-metric-card">
                <div class="ts-metric-label">Activity Time</div>
                <div class="ts-metric-value"><?= $activity_hms ?></div>
                <div class="ts-progress-track">
                    <div class="ts-progress-fill <?= $productive_ratio >= 70 ? 'good' : ($productive_ratio >= 40 ? 'warn' : 'bad') ?>" style="width:<?= min(100, $productive_ratio) ?>%;"></div>
                </div>
                <div style="font-size:12px;color:#6c757d;margin-top:4px;"><?= $productive_ratio ?>% productive</div>
            </div>
            <div class="ts-metric-card" style="display:flex;flex-direction:column;justify-content:center;">
                <div class="ts-metric-label">Required Hours</div>
                <div class="ts-metric-value"><?= $req_hms ?></div>
                                <div style="font-size:12px;color:#6c757d;"><?php if (!empty($selected_user_id)): ?>@ <?= number_format($required_daily_avg, 1) ?>h/day &mdash; <?= $adjusted_working_days ?? $working_days ?> working days<?php else: ?>Across <?= count($users) ?> users &times; <?= $working_days ?> working days<?php endif; ?></div>
            </div>
        </div>

        <?php if (!empty($selected_user_id) && isset($selected_user)):
            $header_has_avatar = !empty($selected_user->avatar) && file_exists(FCPATH . $selected_user->avatar);
            $header_bg = 'ts-avatar-bg-' . ((int)$selected_user->user_id % 10);
            $header_init = sidebar_initials($selected_user->fullname ?? '');
        ?>
        <div class="ts-panel" style="margin-bottom:12px;">
            <div class="ts-panel-heading">
                <?php if ($header_has_avatar): ?>
                    <img src="<?= base_url() . $selected_user->avatar ?>" alt="" style="width:28px;height:28px;border-radius:50%;object-fit:cover;">
                <?php else: ?>
                    <div class="ts-avatar-fallback <?= $header_bg ?>" style="width:28px;height:28px;font-size:0.75rem;"><?= $header_init ?></div>
                <?php endif; ?>
                <?= htmlspecialchars($selected_user->fullname ?? 'User') ?>
                <a href="<?= $base_url ?>admin/timesync/user/<?= (int)$selected_user->user_id ?>" class="pull-right" style="font-size:12px;font-weight:400;margin-left:auto;">Full Report &rarr;</a>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($is_shortage): ?>
        <div class="ts-alert ts-alert-danger">
            <div class="ts-alert-icon"><i class="fa fa-exclamation-triangle"></i></div>
            <div class="ts-alert-body">
                <div class="ts-alert-title">Time Shortage of <?= $disc_hms ?></div>
                <div class="ts-alert-sub">Required: <?= $req_hms ?> &middot; Logged: <?= $logged_hms ?> &middot; Period: <?= htmlspecialchars($from) ?> &rarr; <?= htmlspecialchars($to) ?> (<?= $adjusted_working_days ?? $working_days ?> working days<?php if (!empty($selected_user_id) && isset($adjusted_working_days) && $adjusted_working_days !== $working_days): ?>, <?= $working_days - $adjusted_working_days ?> leave days excluded<?php endif; ?>)</div>
            </div>
        </div>
        <?php elseif ($disc_sec > 0): ?>
        <div class="ts-alert ts-alert-success">
            <div class="ts-alert-icon"><i class="fa fa-check-circle"></i></div>
            <div class="ts-alert-body">
                <div class="ts-alert-title">Surplus of <?= $disc_hms ?></div>
                <div class="ts-alert-sub">Required: <?= $req_hms ?> &middot; Logged: <?= $logged_hms ?> &middot; Period: <?= htmlspecialchars($from) ?> &rarr; <?= htmlspecialchars($to) ?> (<?= $adjusted_working_days ?? $working_days ?> working days<?php if (!empty($selected_user_id) && isset($adjusted_working_days) && $adjusted_working_days !== $working_days): ?>, <?= $working_days - $adjusted_working_days ?> leave days excluded<?php endif; ?>)</div>
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

    var STATUS_LABEL = { active: 'Active', paused: 'Paused', idle: 'Idle', offline: 'Offline' };
    var STATUS_CLASS = { active: 'active', paused: 'paused', idle: 'idle', offline: 'offline' };

    function esc(s) {
        return $('<div>').text(s == null ? '' : s).html();
    }

    function sidebarLiveMetaHtml(u) {
        if (u.status === 'active') {
            return '<i class="fa fa-clock-o ts-status-live-clock"></i>'
                + ' <span class="live-tracker" data-start-time="' + (u.started_at || '') + '"><span class="timer-text">00:00:00</span></span>'
                + (u.current_task ? ' &middot; ' + esc(u.current_task) : '')
                + (u.current_window ? ' &mdash; ' + esc(u.current_window) : '');
        }
        if (u.status === 'paused') {
            return '<i class="fa fa-pause" style="color:#f59e0b;margin-right:4px;"></i> Timer paused'
                + (u.current_task ? ' &middot; ' + esc(u.current_task) : '');
        }
        if (u.status === 'idle') {
            return '<i class="fa fa-clock-o" style="color:#f97316;margin-right:4px;"></i> Online, no activity';
        }
        return '';
    }

    function sortTier(u) {
        if (u.status === 'active' && u.started_at) return 0;
        if (u.status === 'active') return 1;
        if (u.status === 'paused') return 2;
        if (u.status === 'idle') return 3;
        return 4;
    }

    function renderLive(data) {
        var users = data.users || [];
        var userMap = {};
        for (var i = 0; i < users.length; i++) {
            userMap[String(users[i].user_id)] = users[i];
        }

        var $list = $('#tsUserList');
        var $allItems = $list.find('.ts-user-item');
        var itemArr = [];

        $allItems.each(function() {
            var $el = $(this);
            var uid = String($el.data('user-id'));
            var u = userMap[uid];

            if (!u) {
                itemArr.push({ el: this, tier: 5, name: ($el.data('name') || '').toLowerCase() });
                return;
            }

            var cls = STATUS_CLASS[u.status] || 'offline';
            var badgeLabel = STATUS_LABEL[u.status] || u.status;

            $el.find('.ts-user-indicator').attr('class', 'ts-user-indicator' + (u.status === 'active' ? ' active' : ''));
            $el.find('.ts-user-badge').attr('class', 'ts-user-badge ts-user-badge-' + cls).text(badgeLabel);

            var $liveMeta = $el.find('.ts-user-live-meta');
            var liveHtml = sidebarLiveMetaHtml(u);

            if (liveHtml) {
                if ($liveMeta.is(':hidden')) $liveMeta.show();

                var isStillActive = u.status === 'active' && u.started_at;
                var wasActive = $liveMeta.find('.live-tracker').length > 0;

                if (isStillActive && wasActive) {
                    var $tracker = $liveMeta.find('.live-tracker').detach();
                    var $clock = $liveMeta.find('.ts-status-live-clock').detach();
                    $liveMeta.empty();
                    if ($clock.length) $liveMeta.append($clock);
                    $liveMeta.append(' ');
                    if ($tracker.length) $liveMeta.append($tracker);
                    var suffix = (u.current_task ? ' &middot; ' + esc(u.current_task) : '')
                        + (u.current_window ? ' &mdash; ' + esc(u.current_window) : '');
                    if (suffix) $liveMeta.append($.parseHTML(suffix));
                } else {
                    $liveMeta.html(liveHtml);
                }
            } else {
                $liveMeta.empty().hide();
            }

            itemArr.push({ el: this, tier: sortTier(u), name: ($el.data('name') || '').toLowerCase() });
        });

        itemArr.sort(function(a, b) {
            if (a.tier !== b.tier) return a.tier - b.tier;
            return a.name.localeCompare(b.name);
        });

        for (var j = 0; j < itemArr.length; j++) {
            $list.append(itemArr[j].el);
        }
    }

    function fetchLive() {
        $.getJSON('<?= $base_url ?>admin/timesync/live_users', function(data) {
            if (data && data.success) renderLive(data);
        });
    }

    fetchLive();
    setInterval(fetchLive, 5000);

    function tickTrackers() {
        var now = Date.now();
        $('.live-tracker').each(function() {
            var start = Number($(this).data('start-time'));
            if (!start) return;
            var diff = Math.floor((now - start) / 1000);
            if (diff < 0) diff = 0;
            var h = String(Math.floor(diff / 3600)).padStart(2, '0');
            var m = String(Math.floor((diff % 3600) / 60)).padStart(2, '0');
            var s = String(diff % 60).padStart(2, '0');
            $(this).find('.timer-text').text(h + ':' + m + ':' + s);
        });
    }
    setInterval(tickTrackers, 1000);
    tickTrackers();
});
</script>

