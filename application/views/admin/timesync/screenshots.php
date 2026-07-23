<?php
function dhaka_time($ts, $fmt = 'M d, Y h:i A') {
    if (!$ts) return '—';
    $dt = new DateTime($ts, new DateTimeZone('UTC'));
    $dt->setTimezone(new DateTimeZone('Asia/Dhaka'));
    return $dt->format($fmt);
}
?>
<div style="display:flex;align-items:center;flex-wrap:wrap;gap:12px;padding:8px 0;">
  <?php $this->load->view('admin/timesync/_date_navigation'); ?>
  <form method="get" style="margin-left:auto;" onsubmit="var f=this;['from','to','interval'].forEach(function(n){var h=document.createElement('input');h.type='hidden';h.name=n;h.value=document.getElementById('dn-'+n+'-hidden').value;f.appendChild(h);})">
    <div class="form-group" style="margin-bottom:0;">
      <select name="user_id" class="form-control" onchange="this.form.submit()">
        <option value="">All Users</option>
        <?php foreach ($users as $u): ?>
          <option value="<?= $u->user_id ?>" <?= $this->input->get('user_id') == $u->user_id ? 'selected' : '' ?>>
            <?= htmlspecialchars($u->fullname ?? $u->user_id) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </form>
</div>
<style>
  .activity-chart-wrap canvas { max-height: 160px; }
  .page-nav { text-align: center; margin-top: 20px; }
  .page-nav .pagination { margin: 0; }
  .screenshot-grid { display: flex; flex-wrap: wrap; gap: 16px; }
  .screenshot-card {
    width: calc(25% - 12px); border: 1px solid #192632ff; border-radius: 8px; overflow: hidden;
    background: #fff; transition: box-shadow .2s;
  }
  .screenshot-card:hover { box-shadow: 0 2px 12px rgba(0,0,0,.1); }
  .screenshot-card img { width: 100%; height: 150px; object-fit: cover; display: block; }
  .screenshot-card .card-footer {
    padding: 8px 10px; font-size: 12px; line-height: 1.4;
  }
  .screenshot-card .card-footer strong { display: block; font-size: 13px; }
  .screenshot-card .ss-delete-btn {
    position: absolute; top: 6px; right: 6px; background: rgba(220,53,69,.85); color: #fff;
    border: none; border-radius: 50%; width: 26px; height: 26px; font-size: 12px;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    opacity: 0; transition: opacity .2s;
  }
  .screenshot-card:hover .ss-delete-btn { opacity: 1; }
  .screenshot-card { position: relative; }
  .screenshot-card-deleted {
    width: calc(25% - 12px); border: 1px dashed #dc3545; border-radius: 8px; overflow: hidden;
    background: #fff5f5; display: flex; flex-direction: column; align-items: center;
    justify-content: center; padding: 20px 14px; text-align: center; min-height: 190px;
  }
  .screenshot-card-deleted .deleted-icon { font-size: 28px; color: #dc3545; margin-bottom: 8px; }
  .screenshot-card-deleted .deleted-text { font-size: 12px; color: #6c757d; line-height: 1.6; }
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

        <form method="get" class="form-inline mb-sm" style="margin-bottom:12px;">
          <div class="form-group">
            <label>Task ID: </label>
            <input type="number" name="task_id" class="form-control" value="<?= $this->input->get('task_id') ?>" placeholder="Task #" style="width:100px;" onchange="this.form.submit()">
          </div>
          <input type="hidden" name="user_id" value="<?= htmlspecialchars($this->input->get('user_id')) ?>">
          <input type="hidden" name="from" value="<?= htmlspecialchars($this->input->get('from')) ?>">
          <input type="hidden" name="to" value="<?= htmlspecialchars($this->input->get('to')) ?>">
          <input type="hidden" name="interval" value="<?= htmlspecialchars($this->input->get('interval')) ?>">
          <input type="hidden" name="page" value="1">
        </form>

        <?php if (!empty($screenshots)): ?>
          <div class="screenshot-grid">
            <?php foreach ($screenshots as $s): ?>
              <?php if (!empty($s->is_deleted)): ?>
                <div class="screenshot-card-deleted">
                  <div class="deleted-icon"><i class="fa fa-trash-o"></i></div>
                  <div class="deleted-text">
                    Screenshot deleted by <?= htmlspecialchars($s->deleted_by_name ?? 'Unknown') ?>
                    <br>on <?= dhaka_time($s->deleted_at, 'M d, Y h:i A') ?>
                  </div>
                </div>
              <?php else: ?>
                <div class="screenshot-card">
                  <a href="<?= base_url('admin/timesync/view_image/' . $s->id) ?>" target="_blank" rel="noopener">
                    <img data-ss-id="<?= $s->id ?>" class="ss-thumb" loading="lazy">
                  </a>
                  <button class="ss-delete-btn" data-id="<?= $s->id ?>" title="Delete screenshot"><i class="fa fa-trash"></i></button>
                  <div class="card-footer">
                    <strong><?= htmlspecialchars($s->fullname ?? 'User') ?></strong>
                    <?= dhaka_time($s->captured_at) ?>
                    <?php if (!empty($s->task_id)): ?>
                      <br><a href="<?= base_url('admin/tasks/view/' . $s->task_id) ?>"><?= htmlspecialchars($s->task_name ?? 'Task #' . $s->task_id) ?></a>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endif; ?>
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

// Delete screenshot handler
$(document).on('click', '.ss-delete-btn', function (e) {
  e.preventDefault();
  e.stopPropagation();
  var btn = $(this);
  var id = btn.data('id');
  if (!id) return;
  if (typeof Swal === 'undefined') {
    if (!confirm('Delete this screenshot?')) return;
    doDeleteScreenshot(id, btn);
    return;
  }
  Swal.fire({
    title: 'Delete Screenshot?',
    text: 'This action cannot be undone. The image file will be removed from disk.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    confirmButtonText: 'Yes, delete it'
  }).then(function (result) {
    if (result.isConfirmed) doDeleteScreenshot(id, btn);
  });
});

function doDeleteScreenshot(id, btn) {
  $.ajax({
    url: '<?= base_url("admin/timesync/delete_screenshot") ?>/' + id,
    method: 'POST',
    dataType: 'json',
    success: function (resp) {
      if (resp.success) {
        var card = btn.closest('.screenshot-card');
        var placeholder = $('<div class="screenshot-card-deleted">' +
          '<div class="deleted-icon"><i class="fa fa-trash-o"></i></div>' +
          '<div class="deleted-text">Screenshot deleted<br>just now</div></div>');
        card.fadeOut(200, function () { card.replaceWith(placeholder); });
        if (typeof Swal !== 'undefined') Swal.fire('Deleted', resp.message || 'Screenshot deleted.', 'success');
      } else {
        if (typeof Swal !== 'undefined') Swal.fire('Error', resp.message || 'Failed to delete.', 'error');
        else alert(resp.message || 'Failed to delete.');
      }
    },
    error: function () {
      if (typeof Swal !== 'undefined') Swal.fire('Error', 'Server error. Please try again.', 'error');
      else alert('Server error. Please try again.');
    }
  });
}
</script>
