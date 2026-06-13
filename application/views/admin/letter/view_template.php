<div class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title">
            <strong><?= !empty($template_info) ? $template_info->title : '' ?></strong>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label">Description</label>
                    <div class="well well-sm" style="background:#fff;border:1px solid #ddd;min-height:200px;padding:15px;">
                        <?= !empty($template_info->description) ? $template_info->description : '<p class="text-muted">No description</p>' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
