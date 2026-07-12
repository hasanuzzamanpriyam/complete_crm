<style>
  .activity-chart-wrap canvas { max-height: 160px; }
  .page-nav { text-align: center; margin-top: 20px; }
  .page-nav .pagination { margin: 0; }
  .screenshot-grid { display: flex; flex-wrap: wrap; gap: 16px; }
  .screenshot-card {
    width: calc(25% - 12px); border: 1px solid #e9ecef; border-radius: 8px; overflow: hidden;
    background: #fff; transition: box-shadow .2s;
  }
  .screenshot-card:hover { box-shadow: 0 2px 12px rgba(0,0,0,.1); }
  .screenshot-card img { width: 100%; height: 150px; object-fit: cover; display: block; }
  .screenshot-card .card-footer {
    padding: 8px 10px; font-size: 12px; line-height: 1.4;
  }
  .screenshot-card .card-footer strong { display: block; font-size: 13px; }
  @media (max-width: 992px) { .screenshot-card { width: calc(33.33% - 11px); } }
  @media (max-width: 768px) { .screenshot-card { width: calc(50% - 8px); } }
  @media (max-width: 480px) { .screenshot-card { width: 100%; } }
</style>

<div class="row">
  <div class="col-lg-12">
    <section class="panel panel-custom">
      <header class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-camera"></i> <?= $title ?? 'Screenshots' ?></h3>
      </header>
      <div class="panel-body">

        <div class="row mb-lg" style="margin-bottom:24px;">
          <div class="col-md-3">
            <div class="panel panel-info teams-stat-card" style="border-radius:8px;">
              <div class="panel-body text-center">
                <div class="stat-value" style="font-size:28px;font-weight:700;line-height:1;"><?= $screenshot_count ?></div>
                <div class="stat-label" style="font-size:12px;opacity:.8;margin-top:4px;">This Page</div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="panel panel-success teams-stat-card" style="border-radius:8px;">
              <div class="panel-body text-center">
                <div class="stat-value" style="font-size:28px;font-weight:700;line-height:1;"><?= $total_screenshots ?></div>
                <div class="stat-label" style="font-size:12px;opacity:.8;margin-top:4px;">Total All Time</div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="panel panel-custom" style="margin-bottom:0;">
              <div class="panel-heading" style="padding:6px 12px;">
                <h4 class="panel-title" style="font-size:12px;"><i class="fa fa-line-chart"></i> Screenshots per Day (14 days)</h4>
              </div>
              <div class="panel-body" style="padding:8px;">
                <canvas id="screenshotTrendChart" style="max-height:80px;"></canvas>
              </div>
            </div>
          </div>
        </div>

        <form method="get" class="form-inline mb-lg">
          <div class="form-group">
            <label>User: </label>
            <select name="user_id" class="form-control">
              <option value="">All Users</option>
              <?php foreach ($users as $u): ?>
                <option value="<?= $u->user_id ?>" <?= $this->input->get('user_id') == $u->user_id ? 'selected' : '' ?>>
                  <?= htmlspecialchars($u->fullname ?? $u->user_id) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group ml-sm">
            <label>Task ID: </label>
            <input type="number" name="task_id" class="form-control" value="<?= $this->input->get('task_id') ?>" placeholder="Task #" style="width:100px;">
          </div>
          <div class="form-group ml-sm">
            <label>From: </label>
            <input type="date" name="from" class="form-control" value="<?= $this->input->get('from') ?>">
          </div>
          <div class="form-group ml-sm">
            <label>To: </label>
            <input type="date" name="to" class="form-control" value="<?= $this->input->get('to') ?>">
          </div>
          <input type="hidden" name="page" value="1">
          <button type="submit" class="btn btn-primary ml-sm">Filter</button>
        </form>

        <?php if (!empty($screenshots)): ?>
          <div class="screenshot-grid">
            <?php foreach ($screenshots as $s): ?>
              <div class="screenshot-card">
                <a href="<?= base_url('admin/timesync/view_image/' . $s->id) ?>" target="_blank" rel="noopener">
                  <img data-ss-id="<?= $s->id ?>" class="ss-thumb" loading="lazy">
                </a>
                <div class="card-footer">
                  <strong><?= htmlspecialchars($s->fullname ?? 'User') ?></strong>
                  <?= date('M d, Y H:i', strtotime($s->captured_at)) ?>
                  <?php if (!empty($s->task_id)): ?>
                    <br><a href="<?= base_url('admin/tasks/view/' . $s->task_id) ?>"><?= htmlspecialchars($s->task_name ?? 'Task #' . $s->task_id) ?></a>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Pagination -->
          <?php if ($total_pages > 1): ?>
            <div class="page-nav">
              <ul class="pagination">
                <?php
                  $qp = $_GET;
                  $base = 'admin/timesync/screenshots';
                ?>
                <li class="<?= $page <= 1 ? 'disabled' : '' ?>">
                  <a href="<?= base_url($base . '?' . http_build_query(array_merge($qp, ['page' => 1]))) ?>">&laquo; First</a>
                </li>
                <li class="<?= $page <= 1 ? 'disabled' : '' ?>">
                  <a href="<?= base_url($base . '?' . http_build_query(array_merge($qp, ['page' => $page - 1]))) ?>">&lsaquo; Prev</a>
                </li>
                <?php
                  $start_p = max(1, $page - 2);
                  $end_p = min($total_pages, $page + 2);
                  for ($p = $start_p; $p <= $end_p; $p++):
                ?>
                  <li class="<?= $p === $page ? 'active' : '' ?>">
                    <a href="<?= base_url($base . '?' . http_build_query(array_merge($qp, ['page' => $p]))) ?>"><?= $p ?></a>
                  </li>
                <?php endfor; ?>
                <li class="<?= $page >= $total_pages ? 'disabled' : '' ?>">
                  <a href="<?= base_url($base . '?' . http_build_query(array_merge($qp, ['page' => $page + 1]))) ?>">Next &rsaquo;</a>
                </li>
                <li class="<?= $page >= $total_pages ? 'disabled' : '' ?>">
                  <a href="<?= base_url($base . '?' . http_build_query(array_merge($qp, ['page' => $total_pages]))) ?>">Last &raquo;</a>
                </li>
              </ul>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <div class="empty-teams" style="text-align:center;padding:60px 20px;color:#6c757d;">
            <i class="fa fa-camera" style="font-size:48px;color:#dde1e7;display:block;margin-bottom:16px;"></i>
            <h4>No Screenshots Found</h4>
            <p class="text-muted">Try adjusting your filters.</p>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </div>
</div>

<script src="<?= base_url() ?>assets/plugins/Chart.js/Chart.js"></script>
<script>
$(document).ready(function () {
  requestAnimationFrame(function () {
    var trendLabels = <?= $chart_trend_labels ?? '[]' ?>;
    var trendValues = <?= $chart_trend_values ?? '[]' ?>;
    var trCanvas = document.getElementById('screenshotTrendChart');
    if (trendLabels.length > 0 && trCanvas && trCanvas.parentElement.offsetWidth > 0) {
      new Chart(trCanvas, {
        type: 'line',
        data: {
          labels: trendLabels,
          datasets: [{
            label: 'Screenshots',
            data: trendValues,
            borderColor: 'rgba(35, 183, 229, 1)',
            backgroundColor: 'rgba(35, 183, 229, 0.1)',
            fill: true,
            tension: 0.3,
            pointRadius: 2,
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: { legend: { display: false } },
          scales: {
            x: { display: false },
            y: { beginAtZero: true, display: false }
          },
          elements: { point: { radius: 0 } }
        }
      });
    }
  });
});

// Batch load screenshot thumbnails
$(document).ready(function () {
  var ids = [];
  $('.ss-thumb').each(function () {
    var id = $(this).data('ss-id');
    if (id) ids.push(id);
  });
  if (ids.length === 0) return;
  $.get('<?= base_url("admin/timesync/batch_thumbnails") ?>', { ids: ids.join(',') }, function (resp) {
    if (resp.success && resp.data) {
      $('.ss-thumb').each(function () {
        var id = $(this).data('ss-id');
        if (resp.data[id]) {
          $(this).attr('src', resp.data[id]);
        }
      });
    }
  });
});
</script>
