<div style="display:flex;align-items:center;flex-wrap:wrap;gap:8px;padding:8px 0;">
  <?php $this->load->view('admin/timesync/_date_navigation'); ?>
  <form method="get" style="margin-left:auto;" onsubmit="var f=this;['from','to','interval'].forEach(function(n){var h=document.createElement('input');h.type='hidden';h.name=n;h.value=document.getElementById('dn-'+n+'-hidden').value;f.appendChild(h);})">
    <div class="form-group" style="margin-bottom:0;">
      <select name="user_id" class="form-control" onchange="this.form.submit()">
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
  </form>
</div>
<div class="row">
  <div class="col-lg-12">
    <section class="panel panel-custom">
      <header class="panel-heading">
        <h3 class="panel-title"><?= $title ?? 'App Usage Reports' ?></h3>
      </header>
      <div class="panel-body">

        <!-- Stats Row -->
        <div class="row mb-lg" style="margin-bottom:24px;">
          <div class="col-sm-4">
            <div class="panel panel-info teams-stat-card" style="border-radius:8px;">
              <div class="panel-body text-center">
                <div class="stat-value" style="font-size:28px;font-weight:700;line-height:1;"><?= round($usage_total_seconds / 3600, 1) ?>h</div>
                <div class="stat-label" style="font-size:12px;opacity:.8;margin-top:4px;">Total Tracked</div>
              </div>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="panel panel-success teams-stat-card" style="border-radius:8px;">
              <div class="panel-body text-center">
                <div class="stat-value" style="font-size:28px;font-weight:700;line-height:1;"><?= $usage_user_count ?></div>
                <div class="stat-label" style="font-size:12px;opacity:.8;margin-top:4px;">Active Users</div>
              </div>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="panel panel-warning teams-stat-card" style="border-radius:8px;">
              <div class="panel-body text-center">
                <div class="stat-value" style="font-size:28px;font-weight:700;line-height:1;"><?= count($user_scores) > 0 ? round(array_sum(array_column($user_scores, 'focus_score')) / count($user_scores)) : 0 ?>%</div>
                <div class="stat-label" style="font-size:12px;opacity:.8;margin-top:4px;">Avg Focus Score</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-lg" style="display:flex;flex-wrap:wrap;margin-bottom:24px;">
          <div class="col-md-6" style="display:flex;">
            <div class="panel panel-custom" style="display:flex;flex-direction:column;width:100%;">
              <div class="panel-heading"><h4 class="panel-title"><i class="fa fa-bar-chart"></i> Top 10 Applications</h4></div>
              <div class="panel-body" style="flex:1;position:relative;min-height:350px;">
                <div style="position:relative;height:100%;width:100%;">
                  <canvas id="appUsageChart"></canvas>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6" style="display:flex;">
            <div class="panel panel-custom" style="display:flex;flex-direction:column;width:100%;">
              <div class="panel-heading"><h4 class="panel-title"><i class="fa fa-pie-chart"></i> Focus Score Distribution</h4></div>
              <div class="panel-body" style="flex:1;position:relative;min-height:350px;">
                <div style="position:relative;height:100%;width:100%;">
                  <canvas id="focusDistChart"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- App Usage Breakdown Table (server-side DataTable) -->
        <div class="row">
          <div class="col-md-12">
            <div class="panel panel-custom">
              <div class="panel-heading">
                <h4 class="panel-title">App Usage Breakdown</h4>
              </div>
              <div class="panel-body">
                <table class="table table-striped" id="usageDataTable" width="100%">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>User</th>
                      <th>Application</th>
                      <th>Window Title</th>
                      <th>URL</th>
                      <th>Duration</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
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
$(document).ready(function () {
  requestAnimationFrame(function () {
    // Top apps bar chart
    var appLabels = <?= $chart_app_labels ?? '[]' ?>;
    var appValues = <?= $chart_app_values ?? '[]' ?>;
    var appCanvas = document.getElementById('appUsageChart');
    if (appLabels.length > 0 && appCanvas && appCanvas.parentElement.offsetWidth > 0) {
      new Chart(appCanvas, {
        type: 'bar',
        data: {
          labels: appLabels,
          datasets: [{
            label: 'Hours',
            data: appValues,
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
          }]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            x: { beginAtZero: true, title: { display: true, text: 'Hours' } },
            y: { title: { display: true, text: 'Application' } }
          }
        }
      });
    }

    // Focus distribution doughnut chart
    var focusLabels = <?= $chart_focus_labels ?? '[]' ?>;
    var focusValues = <?= $chart_focus_values ?? '[]' ?>;
    var focusCanvas = document.getElementById('focusDistChart');
    var focusSum = focusValues.reduce(function (a, b) { return a + b; }, 0);
    if (focusSum > 0 && focusCanvas && focusCanvas.parentElement.offsetWidth > 0) {
      new Chart(focusCanvas, {
        type: 'doughnut',
        data: {
          labels: focusLabels,
          datasets: [{
            data: focusValues,
            backgroundColor: [
              'rgba(220, 53, 69, 0.7)',
              'rgba(255, 193, 7, 0.7)',
              'rgba(40, 167, 69, 0.5)',
              'rgba(40, 167, 69, 0.9)'
            ],
            borderColor: ['#dc3545', '#ffc107', '#28a745', '#28a745'],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '60%',
          plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
          }
        }
      });
    }
  });

  // Server-side DataTable for usage breakdown
  var usageUrl = '<?= base_url('admin/timesync/usage_datatable') ?>' +
    '?user_id=<?= (int)$selected_user_id ?>&from=<?= urlencode($this->input->get('from') ?? date('Y-m-01')) ?>&to=<?= urlencode($this->input->get('to') ?? date('Y-m-d')) ?>';

  if ($('#usageDataTable').length) {
    $('#usageDataTable').DataTable({
      'retrieve': true,
      'responsive': true,
      'processing': true,
      'serverSide': true,
      'pageLength': 25,
      'aLengthMenu': [[10, 25, 50, 100], [10, 25, 50, 100]],
      'ajax': usageUrl,
      'columns': [
        { 'data': 0 },
        { 'data': 1 },
        { 'data': 2 },
        { 'data': 3 },
        { 'data': 4 },
        { 'data': 5, 'className': 'text-right' }
      ],
      'order': [[0, 'desc']],
      'dom': '<"row"<"col-xs-6"l><"col-xs-6"f>>r>t<"row"<"col-xs-6"i><"col-xs-6"p>>',
      'language': {
        'lengthMenu': '_MENU_ records per page',
        'search': '<i class="fa fa-search"></i>',
        'searchPlaceholder': 'Search...'
      }
    });
  }
});
</script>
