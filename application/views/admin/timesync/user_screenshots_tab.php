<div class="panel panel-custom">
    <div class="panel-heading"><h4 class="panel-title">Screenshots (<?= count($screenshots ?? []) ?>)</h4></div>
    <div class="panel-body">
        <div class="row">
            <?php if (!empty($screenshots)): ?>
                <?php foreach ($screenshots as $s): ?>
                    <div class="col-md-3 col-sm-4 col-xs-6 mb-sm">
                        <a href="javascript:void(0)" class="screenshot-thumbnail" data-id="<?= $s->id ?>">
                            <img src="<?= base_url('admin/timesync/view_image/' . $s->id) ?>" class="img-responsive img-thumbnail" style="height: 150px; object-fit: cover;">
                        </a>
                        <p class="text-center text-muted small"><?= date('M d, H:i', strtotime($s->captured_at)) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-md-12"><p class="text-center">No screenshots for this period</p></div>
            <?php endif; ?>
        </div>
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
                            <img id="screenshotFullImage" src="" class="img-responsive" style="max-height: 400px; display: inline-block;">
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
$(document).ready(function() {
    $('.screenshot-thumbnail').on('click', function() {
        var screenshotId = $(this).data('id');
        var modal = $('#screenshotModal');

        // Reset + show loading
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
                $('#screenshotTimestamp').text('Captured: ' + d.captured_at);

                var appHtml = '';
                if (d.app_usage && d.app_usage.length > 0) {
                    appHtml += '<div style="max-height:300px;overflow-y:auto;"><table class="table table-striped table-condensed" style="margin:0;">';
                    appHtml += '<thead><tr><th>#</th><th>Application</th><th>Window Title</th><th class="text-right">Time (s)</th></tr></thead><tbody>';
                    $.each(d.app_usage, function(i, app) {
                        var title = app.window_title ? app.window_title.replace(/</g, '&lt;') : '-';
                        var name = app.app_name ? app.app_name.replace(/</g, '&lt;') : '-';
                        appHtml += '<tr>';
                        appHtml += '<td>' + (i + 1) + '</td>';
                        appHtml += '<td>' + name + '</td>';
                        appHtml += '<td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="' + title + '">' + title + '</td>';
                        appHtml += '<td class="text-right">' + parseInt(app.total_seconds) + '</td>';
                        appHtml += '</tr>';
                    });
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
    });
});
</script>
