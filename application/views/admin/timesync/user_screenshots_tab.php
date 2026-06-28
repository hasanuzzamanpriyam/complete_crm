<div class="panel panel-custom">
    <div class="panel-heading"><h4 class="panel-title">Screenshots (<?= count($screenshots ?? []) ?>)</h4></div>
    <div class="panel-body">
        <div class="row">
            <?php if (!empty($screenshots)): ?>
                <?php foreach ($screenshots as $s): ?>
                    <div class="col-md-3 col-sm-4 col-xs-6 mb-sm">
                        <a href="<?= base_url('admin/timesync/view_image/' . $s->id) ?>" target="_blank" rel="noopener">
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
