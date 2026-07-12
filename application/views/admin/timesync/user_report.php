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
            <form method="get" class="form-inline mb-lg">
              <input type="hidden" name="tab" value="<?= $active_tab ?>">
              <div class="form-group">
                <label>From: </label>
                <input type="date" name="from" class="form-control" value="<?= $from ?>">
              </div>
              <div class="form-group ml-sm">
                <label>To: </label>
                <input type="date" name="to" class="form-control" value="<?= $to ?>">
              </div>
              <button type="submit" class="btn btn-primary ml-sm">Filter</button>
            </form>
          </div>
        </div>

        <div class="row">
          <div class="col-md-3">
            <div class="panel panel-info" id="stat-total-hours">
              <div class="panel-body text-center">
                <h2><?= round($total_seconds / 3600, 1) ?>h</h2>
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

        <!-- Daily Hours Trend -->
        <div class="row mb-lg" style="margin-bottom:24px;">
          <div class="col-md-12">
            <div class="panel panel-custom">
              <div class="panel-heading"><h4 class="panel-title"><i class="fa fa-bar-chart"></i> Daily Hours</h4></div>
              <div class="panel-body">
                <canvas id="userDailyHoursChart" height="80"></canvas>
              </div>
            </div>
          </div>
        </div>

        <div class="row mt-lg">
          <div class="col-md-12">
            <ul class="nav nav-tabs" id="userTabs">
              <li class="<?= $active_tab === 'entries' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/timesync/user/' . $user_id . '?tab=entries&from=' . $from . '&to=' . $to) ?>">Time Entries</a>
              </li>
              <li class="<?= $active_tab === 'screenshots' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/timesync/user/' . $user_id . '?tab=screenshots&from=' . $from . '&to=' . $to) ?>">Screenshots</a>
              </li>
              <li class="<?= $active_tab === 'apps' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/timesync/user/' . $user_id . '?tab=apps&from=' . $from . '&to=' . $to) ?>">App Usage</a>
              </li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active">
                <?php if ($active_tab === 'entries'): ?>
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

<script src="<?= base_url() ?>assets/plugins/Chart.js/Chart.js"></script>
<script>
$(document).ready(function () {
  requestAnimationFrame(function () {
    var hoursLabels = <?= $chart_user_hours_labels ?? '[]' ?>;
    var hoursValues = <?= $chart_user_hours_values ?? '[]' ?>;
    var hrCanvas = document.getElementById('userDailyHoursChart');
    if (hoursLabels.length > 0 && hrCanvas && hrCanvas.parentElement.offsetWidth > 0) {
      new Chart(hrCanvas, {
        type: 'bar',
        data: {
          labels: hoursLabels,
          datasets: [{
            label: 'Hours',
            data: hoursValues,
            backgroundColor: 'rgba(35, 183, 229, 0.5)',
            borderColor: 'rgba(35, 183, 229, 1)',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: { legend: { display: false } },
          scales: {
            x: { ticks: { maxRotation: 45, font: { size: 9 } } },
            y: { beginAtZero: true, ticks: { font: { size: 9 } } }
          }
        }
      });
    }
  });
});
</script>
