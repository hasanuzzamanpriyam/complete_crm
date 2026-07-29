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

