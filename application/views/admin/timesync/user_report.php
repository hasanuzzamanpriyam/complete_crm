<?php $this->load->view('admin/timesync/_date_navigation'); ?>
<div class="row">
  <div class="col-lg-12">
    <section class="panel panel-custom">
      <header class="panel-heading">
        <h3 class="panel-title"><?= $title ?> - <?= htmlspecialchars($user->fullname ?? $user->username) ?></h3>
        <?php if (!empty($user_teams)): ?>
          <div class="mt-sm" style="margin-top:8px;">
            <strong>Teams:</strong>
            <?php foreach ($user_teams as $ut): ?>
              <span class="badge bg-primary" style="margin-left:4px; background:#23b7e5;"><?= htmlspecialchars($ut->name) ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </header>
      <div class="panel-body">
        <div class="row">
          <div class="col-md-12">
            <form method="get" class="form-inline mb-lg" onsubmit="var f=this;['from','to','interval'].forEach(function(n){var h=document.createElement('input');h.type='hidden';h.name=n;h.value=document.getElementById('dn-'+n+'-hidden').value;f.appendChild(h);})">
              <input type="hidden" name="tab" value="<?= $active_tab ?>">
            </form>
          </div>
        </div>

        <div class="row">
          <div class="col-md-3">
            <div class="panel panel-info" id="stat-total-hours">
              <div class="panel-body text-center">
                <h2><?= floor($total_seconds / 3600) ?>h <?= floor(($total_seconds % 3600) / 60) ?>m <?= ($total_seconds % 60) ?>s</h2>
                <p class="text-muted">Total Hours</p>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="panel panel-success" id="stat-entry-count">
              <div class="panel-body text-center">
                <h2><?= $entry_count ?></h2>
                <p class="text-muted">Entries</p>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="panel panel-warning" id="stat-screenshot-count">
              <div class="panel-body text-center">
                <h2><?= $screenshot_count ?></h2>
                <p class="text-muted">Screenshots</p>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="panel panel-primary" id="stat-day-count">
              <div class="panel-body text-center">
                <h2><?= $day_count ?></h2>
                <p class="text-muted">Active Days</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Live Status + Current Task/Window -->
        <div class="row mt-lg" id="live-widget-row">
          <div class="col-md-3">
            <div class="panel panel-default" id="stat-status">
              <div class="panel-body text-center">
                <h2 style="margin:0;" id="live-status-badge"><span class="label label-default">Offline</span></h2>
                <p class="text-muted" style="margin:8px 0 0;">Status</p>
                <p class="text-muted small" id="live-status-detail" style="margin:0;"></p>
              </div>
            </div>
          </div>
          <div class="col-md-9">
            <div class="panel panel-info" id="stat-current">
              <div class="panel-body">
                <div class="row">
                  <div class="col-md-6">
                    <div><strong>Current Task:</strong> <span id="live-current-task">&mdash;</span></div>
                    <div class="mt-sm"><strong>Timer:</strong> <span id="live-current-timer" style="font-variant-numeric:tabular-nums;">00:00:00</span> <span class="text-muted small" id="live-current-timer-note"></span></div>
                  </div>
                  <div class="col-md-6">
                    <div><strong>Application:</strong> <span id="live-current-app">&mdash;</span></div>
                    <div class="mt-sm"><strong>Window:</strong> <span id="live-current-window">&mdash;</span></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Daily Hours Trend (replaced by Activity Timeline) -->
        <div class="row mt-lg">
          <div class="col-md-12">
            <ul class="nav nav-tabs" id="userTabs">
              <li class="<?= $active_tab === 'timeline' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/timesync/user/' . $user_id . '?tab=timeline&from=' . $from . '&to=' . $to . '&interval=' . urlencode($interval)) ?>">Activity Timeline</a>
              </li>
              <li class="<?= $active_tab === 'entries' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/timesync/user/' . $user_id . '?tab=entries&from=' . $from . '&to=' . $to . '&interval=' . urlencode($interval)) ?>">Time Entries</a>
              </li>
              <li class="<?= $active_tab === 'screenshots' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/timesync/user/' . $user_id . '?tab=screenshots&from=' . $from . '&to=' . $to . '&interval=' . urlencode($interval)) ?>">Screenshots</a>
              </li>
              <li class="<?= $active_tab === 'apps' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/timesync/user/' . $user_id . '?tab=apps&from=' . $from . '&to=' . $to . '&interval=' . urlencode($interval)) ?>">App Usage</a>
              </li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active">
                <?php if ($active_tab === 'timeline'): ?>
                  <?php $this->load->view('admin/timesync/user_timeline_tab', [
                    'timeline_days' => $timeline_days ?? [],
                    'analytics' => $analytics ?? [],
                    'user_id' => $user_id,
                    'from' => $from,
                    'to' => $to,
                  ]); ?>
                <?php elseif ($active_tab === 'entries'): ?>
                  <?php $this->load->view('admin/timesync/user_entries_tab', [
                    'entries' => $entries ?? [],
                    'entry_page' => $entry_page ?? 1,
                    'entry_total_pages' => $entry_total_pages ?? 1,
                    'user_id' => $user_id,
                    'from' => $from,
                    'to' => $to,
                    'tab' => 'entries',
                  ]); ?>
                <?php elseif ($active_tab === 'screenshots'): ?>
                  <?php $this->load->view('admin/timesync/user_screenshots_tab', [
                    'screenshots' => $screenshots ?? [],
                    'ss_page' => $ss_page ?? 1,
                    'ss_total_pages' => $ss_total_pages ?? 1,
                    'user_id' => $user_id,
                    'from' => $from,
                    'to' => $to,
                  ]); ?>
                <?php elseif ($active_tab === 'apps'): ?>
                  <?php $this->load->view('admin/timesync/user_apps_tab', [
                    'app_usage' => $app_usage ?? [],
                    'app_page' => $app_page ?? 1,
                    'app_total_pages' => $app_total_pages ?? 1,
                    'user_id' => $user_id,
                    'from' => $from,
                    'to' => $to,
                    'interval' => $interval ?? 'daily',
                  ]); ?>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>

<?php if ($active_tab === 'timeline'): ?>
<script>
(function() {
  if (window.__tlReportInit) return;
  window.__tlReportInit = true;

  var userId = <?= json_encode($user_id) ?>;
  var fallbackFrom = '<?= $from ?>';
  var fallbackTo = '<?= $to ?>';

  function isTimelineActive() {
    var a = $('#userTabs li.active a');
    return a.length && a.attr('href').indexOf('tab=timeline') !== -1;
  }

  function getFromTo() {
    return {
      from: ($('#dn-from-hidden').val() || fallbackFrom),
      to:   ($('#dn-to-hidden').val()   || fallbackTo)
    };
  }

  function refreshFromAjax() {
    if (!isTimelineActive()) return;
    if (typeof window.loadTimeline !== 'function') return;
    var p = getFromTo();
    window.loadTimeline(userId, p.from, p.to);
  }

  $(document).one('ready', function() {
    if (typeof window.initTimesyncTimeline === 'function') {
      window.initTimesyncTimeline();
    }
  });

  $(document).on('timesync:spa_loaded', function() {
    refreshFromAjax();
  });

  $(document).off('click.tlTab').on('click.tlTab', '.col-lg-12 #userTabs a', function(e) {
    var href = $(this).attr('href');
    if (!href || href.indexOf('tab=timeline') === -1) return;
    e.preventDefault();
    window.history.pushState({}, '', href);
    $('#userTabs li').removeClass('active');
    $(this).parent().addClass('active');
    refreshFromAjax();
  });
})();
</script>
<?php endif; ?>

<?php if (isset($user_id) && $user_id): ?>
<script>
(function() {
  if (window.__userLiveInit) return;
  window.__userLiveInit = true;

  var POLL_INTERVAL_MS = 5000;
  var USER_ID = <?= (int)$user_id ?>;
  var LIVE_BASE = '<?= base_url('admin/timesync/user_live_data') ?>/' + USER_ID;
  var BATCH_SS_URL = '<?= base_url('admin/timesync/batch_thumbnails') ?>';
  var SS_DETAIL_URL = '<?= base_url('admin/timesync/get_screenshot_details') ?>/';

  function todayStr() {
    var d = new Date();
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  }

  function rangeIncludesToday() {
    var from = $('#dn-from-hidden').val();
    var to = $('#dn-to-hidden').val();
    if (!from || !to) return false;
    return from <= todayStr() && todayStr() <= to;
  }

  function getActiveTab() {
    var a = $('#userTabs li.active a');
    if (!a.length) return 'timeline';
    var href = a.attr('href') || '';
    var m = href.match(/[?&]tab=([^&]+)/);
    return m ? m[1] : 'timeline';
  }

  function fmtHMS(sec) {
    sec = Math.max(0, Math.floor(sec) || 0);
    return Math.floor(sec / 3600) + 'h ' + Math.floor((sec % 3600) / 60) + 'm ' + (sec % 60) + 's';
  }

  function fmtHHMMSS(sec) {
    sec = Math.max(0, Math.floor(sec) || 0);
    var h = String(Math.floor(sec / 3600)).padStart(2, '0');
    var m = String(Math.floor((sec % 3600) / 60)).padStart(2, '0');
    var s = String(sec % 60).padStart(2, '0');
    return h + ':' + m + ':' + s;
  }

  function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  function dhakaDateYmd(iso) {
    if (!iso) return '-';
    var d = new Date(iso.replace(' ', 'T') + 'Z');
    if (isNaN(d.getTime())) return '-';
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  }

  function dhakaTime(iso) {
    if (!iso) return '-';
    var d = new Date(iso.replace(' ', 'T') + 'Z');
    if (isNaN(d.getTime())) return '-';
    return d.toLocaleString('en-US', { hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true, timeZone: 'Asia/Dhaka' });
  }

  function dhakaLabel(iso) {
    if (!iso) return '-';
    var d = new Date(iso.replace(' ', 'T') + 'Z');
    if (isNaN(d.getTime())) return '-';
    return d.toLocaleString('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true, timeZone: 'Asia/Dhaka' });
  }

  var STATUS_META = {
    active:  { label: 'Active',  cls: 'label-success' },
    paused:  { label: 'Paused',  cls: 'label-warning' },
    idle:    { label: 'Idle',    cls: 'label-primary' },
    offline: { label: 'Offline', cls: 'label-default' }
  };
  var STATUS_BORDER = { active: '#5cb85c', paused: '#f0ad4e', idle: '#337ab7', offline: '#777777' };

  var lastSignature = null;
  var lastEntriesSig = null;
  var lastAppsSig = null;
  var maxKnownSsId = 0;
  var currentTask = null;
  var timerInterval = null;

  function seedKnownSsIds() {
    maxKnownSsId = 0;
    $('.ss-thumb').each(function() {
      var id = parseInt($(this).data('ss-id'), 10);
      if (id && id > maxKnownSsId) maxKnownSsId = id;
    });
  }

  function updateStats(s) {
    $('#stat-total-hours h2').text(fmtHMS(s.total_seconds));
    $('#stat-entry-count h2').text(s.entry_count);
    $('#stat-screenshot-count h2').text(s.screenshot_count);
    $('#stat-day-count h2').text(s.day_count);
  }

  function fmt12(dateStr) {
    if (!dateStr) return '';
    var d = new Date(dateStr.replace(/-/g, '/'));
    if (isNaN(d.getTime())) return dateStr;
    var h = d.getHours();
    var m = ('0' + d.getMinutes()).slice(-2);
    var s = ('0' + d.getSeconds()).slice(-2);
    var ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12;
    if (h === 0) h = 12;
    var mo = ('0' + (d.getMonth() + 1)).slice(-2);
    var day = ('0' + d.getDate()).slice(-2);
    return d.getFullYear() + '-' + mo + '-' + day + ' ' + h + ':' + m + ':' + s + ' ' + ampm;
  }

  function updatePresence(p) {
    var meta = STATUS_META[p.status] || STATUS_META.offline;
    var liveNow = p.is_active_now ? ' <i class="fa fa-circle" style="color:#5cb85c;font-size:10px;"></i>' : '';
    $('#live-status-badge').html('<span class="label ' + meta.cls + '">' + meta.label + '</span>' + liveNow);
    var detail = p.last_active_ping ? ('Last ping ' + fmt12(p.last_active_ping)) : 'No heartbeat yet';
    $('#live-status-detail').text(detail);
    $('#stat-status').css('border-top-color', STATUS_BORDER[p.status] || STATUS_BORDER.offline);
  }

  function updateCurrent(c) {
    currentTask = c;
    $('#live-current-task').text(c.is_running && c.task_name ? c.task_name : '\u2014');
    $('#live-current-app').text(c.app_name || '\u2014');
    $('#live-current-window').text(c.window_title || '\u2014');
    if (c.is_running && c.started_at) {
      var paused = c.paused_at && !c.resumed_at;
      $('#live-current-timer-note').text(paused ? '(paused)' : '(running)');
      if (!timerInterval) {
        timerInterval = setInterval(tickLiveTimer, 1000);
      }
      tickLiveTimer();
    } else {
      $('#live-current-timer').text('00:00:00');
      $('#live-current-timer-note').text('');
      if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
      }
    }
  }

  function tickLiveTimer() {
    if (!currentTask || !currentTask.is_running || !currentTask.started_at) return;
    var sec;
    var paused = currentTask.paused_at && !currentTask.resumed_at;
    if (paused) {
      sec = currentTask.total_seconds || 0;
    } else {
      var wall = Math.max(0, Math.floor((Date.now() - currentTask.started_at) / 1000));
      sec = Math.max(currentTask.total_seconds || 0, wall);
    }
    $('#live-current-timer').text(fmtHHMMSS(sec));
  }

  function expandedDayDates() {
    var out = [];
    $('.tl-day').each(function() {
      var body = this.querySelector('.tl-day-body');
      if (body && body.style.maxHeight !== '0px') out.push(this.getAttribute('data-day'));
    });
    return out;
  }

  function reapplyExpanded(dates) {
    if (!dates || !dates.length) return;
    var set = {};
    for (var i = 0; i < dates.length; i++) set[dates[i]] = 1;
    $('.tl-day').each(function() {
      if (!set[this.getAttribute('data-day')]) return;
      var body = this.querySelector('.tl-day-body');
      var chev = this.querySelector('.tl-day-chevron');
      if (body) {
        body.style.maxHeight = body.scrollHeight + 500 + 'px';
        body.style.opacity = '1';
        setTimeout(function() { body.style.maxHeight = 'none'; }, 400);
      }
      if (chev) chev.classList.remove('collapsed');
    });
  }

  function openScreenshotDetail(id) {
    var modal = $('#screenshotModal');
    if (!modal.length) return;
    $('#screenshotFullImage').attr('src', '');
    $('#screenshotActivityPct').text('...');
    $('#screenshotKeystrokes').text('...');
    $('#screenshotMouseClicks').text('...');
    $('#screenshotAppUsageContainer').html('<p class="text-muted text-center">Loading...</p>');
    $('#screenshotTimestamp').text('');
    $('#screenshotModalTitle').text('Screenshot #' + id);
    modal.modal('show');
    $.ajax({
      url: SS_DETAIL_URL + id,
      method: 'GET',
      dataType: 'json',
      success: function(resp) {
        if (!resp.success) {
          $('#screenshotAppUsageContainer').html('<p class="text-danger text-center">Failed to load details</p>');
          return;
        }
        var d = resp.data;
        $('#screenshotFullImage').attr('src', d.file_url);
        $('#screenshotFullLink').attr('href', d.file_url);
        $('#screenshotActivityPct').text(d.activity_percentage + '%');
        $('#screenshotKeystrokes').text(d.keystroke_count);
        $('#screenshotMouseClicks').text(d.mouse_click_count);
        var capDate = new Date(d.captured_at.replace(' ', 'T') + 'Z');
        var capStr = capDate.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true, timeZone: 'Asia/Dhaka' });
        $('#screenshotTimestamp').text('Captured: ' + capStr);
        var appHtml = '';
        if (d.app_usage && d.app_usage.length > 0) {
          appHtml += '<div style="max-height:300px;overflow-y:auto;"><table class="table table-striped table-condensed" style="margin:0;">';
          appHtml += '<thead><tr><th>#</th><th>Application</th><th>Window Title</th><th class="text-right">Duration</th></tr></thead><tbody>';
          for (var i = 0; i < d.app_usage.length; i++) {
            var app = d.app_usage[i];
            var title = app.window_title ? String(app.window_title).replace(/</g, '&lt;') : '-';
            var name = app.app_name ? String(app.app_name).replace(/</g, '&lt;') : '-';
            appHtml += '<tr><td>' + (i + 1) + '</td><td>' + name + '</td>';
            appHtml += '<td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="' + title + '">' + title + '</td>';
            appHtml += '<td class="text-right">' + fmtHMS(app.total_seconds) + '</td></tr>';
          }
          appHtml += '</tbody></table></div>';
        } else {
          appHtml = '<p class="text-muted text-center">No app usage data available for this interval.</p>';
        }
        $('#screenshotAppUsageContainer').html(appHtml);
      },
      error: function() {
        $('#screenshotAppUsageContainer').html('<p class="text-danger text-center">Failed to load screenshot details.</p>');
      }
    });
  }

  function prependScreenshots(list) {
    if (!list || !list.length) return;
    var newItems = [];
    for (var i = 0; i < list.length; i++) {
      var id = parseInt(list[i].id, 10);
      if (id > maxKnownSsId) newItems.push(list[i]);
    }
    if (!newItems.length) return;
    var ids = [];
    for (var j = 0; j < newItems.length; j++) ids.push(newItems[j].id);
    $.get(BATCH_SS_URL, { ids: ids.join(',') }, function(resp) {
      if (!resp.success || !resp.data) return;
      var $grid = $('.ss-grid');
      if (!$grid.length) return;
      var html = '';
      for (var k = 0; k < newItems.length; k++) {
        var s = newItems[k];
        var url = resp.data[s.id];
        if (!url) continue;
        html += '<div class="ss-card" style="position:relative;">'
          + '<a href="javascript:void(0)" class="screenshot-thumbnail-live" data-id="' + s.id + '">'
          + '<img data-ss-id="' + s.id + '" class="ss-thumb" src="' + url + '" loading="lazy" style="cursor:pointer;"></a>'
          + '<button class="ss-delete-btn" data-id="' + s.id + '" title="Delete screenshot"><i class="fa fa-trash"></i></button>'
          + '<div class="ss-label">' + dhakaLabel(s.captured_at) + '</div>'
          + '</div>';
      }
      $grid.prepend(html);
      $grid.find('.screenshot-thumbnail-live').each(function() {
        var sid = $(this).data('id');
        $(this).on('click', function(e) {
          e.preventDefault();
          openScreenshotDetail(sid);
        });
      });
      for (var m = 0; m < newItems.length; m++) {
        var nid = parseInt(newItems[m].id, 10);
        if (nid > maxKnownSsId) maxKnownSsId = nid;
      }
      var $title = $('.tab-pane .panel-title').filter(function() { return /^Screenshots/.test($(this).text()); });
      if ($title.length) {
        var m2 = $title.text().match(/\((\d+)\)/);
        var cur = m2 ? parseInt(m2[1], 10) || 0 : 0;
        $title.text('Screenshots (' + (cur + newItems.length) + ')');
      }
    });
  }

  function panelOfTitle(re) {
    return $('.tab-pane .panel-title').filter(function() { return re.test($(this).text()); }).closest('.panel');
  }

  function rebuildEntries(list) {
    var $panel = panelOfTitle(/Time Entries/);
    if (!$panel.length || !list.length) return;
    var $tbody = $panel.find('tbody');
    if (!$tbody.length) return;
    var html = '';
    for (var i = 0; i < list.length; i++) {
      var e = list[i];
      html += '<tr>'
        + '<td>' + dhakaDateYmd(e.started_at) + '</td>'
        + '<td>' + escapeHtml(e.type) + '</td>'
        + '<td>' + dhakaTime(e.started_at) + '</td>'
        + '<td>' + (e.stopped_at ? dhakaTime(e.stopped_at) : 'Running') + '</td>'
        + '<td>' + fmtHHMMSS(e.total_seconds) + '</td>'
        + '<td>' + (e.task_name ? escapeHtml(e.task_name) : '#' + e.task_id) + '</td>'
        + '</tr>';
    }
    $tbody.html(html);
  }

  function rebuildApps(list) {
    var $panel = panelOfTitle(/App Usage/);
    if (!$panel.length || !list.length) return;
    var $tbody = $panel.find('tbody');
    if (!$tbody.length) return;
    var html = '';
    for (var i = 0; i < list.length; i++) {
      var a = list[i];
      var urlHtml = a.url ? '<a href="' + escapeHtml(a.url) + '" target="_blank" rel="noopener">' + escapeHtml(String(a.url).substring(0, 50)) + '</a>' : '-';
      html += '<tr>'
        + '<td>' + escapeHtml(a.app_name) + '</td>'
        + '<td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="' + escapeHtml(a.window_title) + '">' + escapeHtml(a.window_title || '-') + '</td>'
        + '<td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + urlHtml + '</td>'
        + '<td>' + fmtHHMMSS(a.total_seconds) + '</td>'
        + '<td>' + dhakaTime(a.recorded_at) + '</td>'
        + '<td>' + dhakaDateYmd(a.recorded_at) + '</td>'
        + '</tr>';
    }
    $tbody.html(html);
  }

  function entriesSig(list) {
    return (list.length ? String(list[0].id) : '0') + ':' + list.length;
  }

  function appsSig(list) {
    return (list.length ? String(list[0].recorded_at) : '0') + ':' + list.length;
  }

  function fetchLive() {
    if (document.hidden) return;
    if (!rangeIncludesToday()) return;
    var tab = getActiveTab();
    var params = {
      from: $('#dn-from-hidden').val(),
      to: $('#dn-to-hidden').val(),
      tab: tab
    };
    $.getJSON(LIVE_BASE, params)
      .done(function(resp) {
        if (!resp || !resp.success) return;
        updateStats(resp.stats);
        updatePresence(resp.presence);
        updateCurrent(resp.current);

        if (resp.signature && resp.signature !== lastSignature) {
          lastSignature = resp.signature;
          if (typeof window.renderTimeline === 'function' && resp.timeline) {
            var expanded = expandedDayDates();
            window.renderTimeline(resp.timeline);
            if (typeof window.renderAnalytics === 'function' && resp.analytics) {
              window.renderAnalytics(resp.analytics);
            }
            reapplyExpanded(expanded);
          }
        }

        if (tab === 'screenshots') {
          prependScreenshots(resp.screenshots || []);
        } else if (tab === 'entries') {
          var esig = entriesSig(resp.entries || []);
          if (esig !== lastEntriesSig) {
            lastEntriesSig = esig;
            if (resp.entries && resp.entries.length) rebuildEntries(resp.entries);
          }
        } else if (tab === 'apps') {
          var asig = appsSig(resp.app_usage || []);
          if (asig !== lastAppsSig) {
            lastAppsSig = asig;
            if (resp.app_usage && resp.app_usage.length) rebuildApps(resp.app_usage);
          }
        }
      });
  }

  document.addEventListener('visibilitychange', function() {
    if (!document.hidden) fetchLive();
  });

  $(function() {
    seedKnownSsIds();
    fetchLive();
    setInterval(fetchLive, POLL_INTERVAL_MS);
  });
})();
</script>
<?php endif; ?>

