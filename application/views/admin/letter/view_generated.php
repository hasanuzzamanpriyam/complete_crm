<?= message_box('success'); ?>
<?= message_box('error'); ?>

<div class="panel panel-custom">
    <header class="panel-heading ">
        <div class="panel-title">
            <strong>View Letter - <?= !empty($letter->employee_name) ? $letter->employee_name : 'N/A' ?></strong>
            <div class="pull-right">
                <a href="<?= base_url('admin/letter/download_pdf/' . $letter->id) ?>" class="btn btn-xs btn-info">
                    <i class="fa fa-download"></i> Download PDF
                </a>
                <a href="<?= base_url('admin/letter/print_letter/' . $letter->id) ?>" class="btn btn-xs btn-default" target="_blank">
                    <i class="fa fa-print"></i> Print
                </a>
                <a href="<?= base_url('admin/letter/add_generate/' . $letter->id) ?>" class="btn btn-xs btn-primary">
                    <i class="fa fa-edit"></i> Edit
                </a>
            </div>
        </div>
    </header>

    <div class="panel-body">
        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-body" style="padding: <?= (int)$letter->margin_top ?>px <?= (int)$letter->margin_right ?>px <?= (int)$letter->margin_bottom ?>px <?= (int)$letter->margin_left ?>px; background: #fff; min-height: 400px;">
                        <?php if (!empty(config_item('company_logo'))): ?>
                            <div style="text-align:center;margin-bottom:20px;">
                                <img src="<?= base_url() . config_item('company_logo') ?>" style="max-height:80px;width:auto;">
                            </div>
                        <?php endif; ?>
                        <?= !empty($letter->content) ? $letter->content : '<p class="text-muted">No content</p>' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
