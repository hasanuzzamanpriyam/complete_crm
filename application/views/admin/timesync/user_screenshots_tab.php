<?php
function dhaka_time($ts, $fmt = 'M d, h:i A') {
    if (!$ts) return '—';
    $dt = new DateTime($ts, new DateTimeZone('UTC'));
    $dt->setTimezone(new DateTimeZone('Asia/Dhaka'));
    return $dt->format($fmt);
}
?>
<style>
  .ss-grid { display: flex; flex-wrap: wrap; gap: 12px; }
  .ss-card {
    width: calc(25% - 9px); border: 1px solid #e9ecef; border-radius: 6px; overflow: hidden; background: #fff;
    transition: box-shadow .2s;
  }
  .ss-card:hover { box-shadow: 0 2px 10px rgba(0,0,0,.1); }
  .ss-card img { width: 100%; height: 130px; object-fit: cover; display: block; cursor: pointer; }
  .ss-card .ss-label { padding: 6px 8px; font-size: 11px; color: #6c757d; text-align: center; }
  @media (max-width: 992px) { .ss-card { width: calc(33.33% - 8px); } }
  @media (max-width: 768px) { .ss-card { width: calc(50% - 6px); } }
  @media (max-width: 480px) { .ss-card { width: 100%; } }
</style>

<div class="panel panel-custom">
  <div class="panel-heading">
    <h4 class="panel-title">Screenshots (<?= count($screenshots ?? []) ?>)</h4>
    <span class="pull-right text-muted small">Page <?= $ss_page ?> of <?= $ss_total_pages ?></span>
  </div>
  <div class="panel-body">
    <?php if (!empty($screenshots)): ?>
      <div class="ss-grid">
        <?php foreach ($screenshots as $s): ?>
          <div class="ss-card">
            <a href="javascript:void(0)" class="screenshot-thumbnail" data-id="<?= $s->id ?>">
              <img data-ss-id="<?= $s->id ?>" class="ss-thumb" loading="lazy">
            </a>
            <div class="ss-label"><?= dhaka_time($s->captured_at) ?></div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if ($ss_total_pages > 1): ?>
        <div class="text-center" style="margin-top:16px;">
          <ul class="pagination" style="margin:0;">
            <li class="<?= $ss_page <= 1 ? 'disabled' : '' ?>">
              <a href="<?= base_url('admin/timesync/user/' . $user_id . '?tab=screenshots&from=' . $from . '&to=' . $to . '&ss_page=1') ?>">&laquo;</a>
            </li>
            <li class="<?= $ss_page <= 1 ? 'disabled' : '' ?>">
              <a href="<?= base_url('admin/timesync/user/' . $user_id . '?tab=screenshots&from=' . $from . '&to=' . $to . '&ss_page=' . ($ss_page - 1)) ?>">&lsaquo;</a>
            </li>
            <?php
              $s = max(1, $ss_page - 2);
              $e = min($ss_total_pages, $ss_page + 2);
              for ($p = $s; $p <= $e; $p++):
            ?>
              <li class="<?= $p === $ss_page ? 'active' : '' ?>">
                <a href="<?= base_url('admin/timesync/user/' . $user_id . '?tab=screenshots&from=' . $from . '&to=' . $to . '&ss_page=' . $p) ?>"><?= $p ?></a>
              </li>
            <?php endfor; ?>
            <li class="<?= $ss_page >= $ss_total_pages ? 'disabled' : '' ?>">
              <a href="<?= base_url('admin/timesync/user/' . $user_id . '?tab=screenshots&from=' . $from . '&to=' . $to . '&ss_page=' . ($ss_page + 1)) ?>">&rsaquo;</a>
            </li>
            <li class="<?= $ss_page >= $ss_total_pages ? 'disabled' : '' ?>">
              <a href="<?= base_url('admin/timesync/user/' . $user_id . '?tab=screenshots&from=' . $from . '&to=' . $to . '&ss_page=' . $ss_total_pages) ?>">&raquo;</a>
            </li>
          </ul>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <div class="text-center" style="padding:40px 0;color:#6c757d;">
        <i class="fa fa-camera" style="font-size:36px;display:block;margin-bottom:8px;color:#dde1e7;"></i>
        No screenshots for this period
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Screenshot Details Modal -->
<div class="modal fade" id="screenshotModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="screenshotModalTitle">Screenshot Details</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12 text-center mb-sm" id="screenshotImageContainer">
            <a id="screenshotFullLink" href="#" target="_blank" rel="noopener">
              <img id="screenshotFullImage" src="" class="img-responsive" style="max-height:400px;display:inline-block;">
            </a>
          </div>
        </div>
        <div class="row mb-sm" id="screenshotKpiRow">
          <div class="col-sm-4">
            <div class="panel panel-info text-center" style="margin-bottom:0;">
              <div class="panel-body">
                <h3 id="screenshotActivityPct">0%</h3>
                <p class="text-muted">Activity Score</p>
              </div>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="panel panel-success text-center" style="margin-bottom:0;">
              <div class="panel-body">
                <h3 id="screenshotKeystrokes">0</h3>
                <p class="text-muted">Keystrokes</p>
              </div>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="panel panel-warning text-center" style="margin-bottom:0;">
              <div class="panel-body">
                <h3 id="screenshotMouseClicks">0</h3>
                <p class="text-muted">Mouse Clicks</p>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="panel panel-custom" style="margin-bottom:0;">
              <div class="panel-heading"><h4 class="panel-title">Active Windows (5-min Window)</h4></div>
              <div class="panel-body" id="screenshotAppUsageContainer">
                <p class="text-muted text-center">Loading...</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <span id="screenshotTimestamp" class="pull-left text-muted"></span>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
function fmtDuration(sec) {
  sec = parseInt(sec) || 0;
  var h = Math.floor(sec / 3600);
  var m = Math.floor((sec % 3600) / 60);
  var s = sec % 60;
  var parts = [];
  if (h > 0) parts.push(h + 'h');
  if (m > 0) parts.push(m + 'm');
  parts.push(s + 's');
  return parts.join(' ');
}

$(document).ready(function () {
  $('.screenshot-thumbnail').on('click', function () {
    var screenshotId = $(this).data('id');
    var modal = $('#screenshotModal');
    $('#screenshotFullImage').attr('src', '');
    $('#screenshotActivityPct').text('...');
    $('#screenshotKeystrokes').text('...');
    $('#screenshotMouseClicks').text('...');
    $('#screenshotAppUsageContainer').html('<p class="text-muted text-center">Loading...</p>');
    $('#screenshotTimestamp').text('');
    $('#screenshotModalTitle').text('Screenshot #' + screenshotId);
    modal.modal('show');
    $.ajax({
      url: '<?= base_url("admin/timesync/get_screenshot_details") ?>/' + screenshotId,
      method: 'GET',
      dataType: 'json',
      success: function (resp) {
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
          $.each(d.app_usage, function (i, app) {
            var title = app.window_title ? app.window_title.replace(/</g, '&lt;') : '-';
            var name = app.app_name ? app.app_name.replace(/</g, '&lt;') : '-';
            appHtml += '<tr><td>' + (i + 1) + '</td><td>' + name + '</td>';
            appHtml += '<td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="' + title + '">' + title + '</td>';
            appHtml += '<td class="text-right">' + fmtDuration(app.total_seconds) + '</td></tr>';
          });
          appHtml += '</tbody></table></div>';
        } else {
          appHtml = '<p class="text-muted text-center">No app usage data available for this interval.</p>';
        }
        $('#screenshotAppUsageContainer').html(appHtml);
      },
      error: function () {
        $('#screenshotAppUsageContainer').html('<p class="text-danger text-center">Failed to load screenshot details.</p>');
      }
    });
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
