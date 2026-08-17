<?= message_box('success'); ?>
<?= message_box('error'); ?>

<div class="panel panel-custom">
    <header class="panel-heading ">
        <div class="panel-title">
            <strong>View Letter - <?= !empty($letter->employee_name) ? $letter->employee_name : 'N/A' ?></strong>
            <div class="pull-right no-print">
                <label class="btn btn-xs btn-warning" style="cursor:pointer;margin-right:4px;">
                    <input type="checkbox" id="toggle-logo" style="position:relative;top:2px;margin-right:4px;">
                    Toggle Logo
                </label>
                <a href="<?= base_url('admin/letter/download_pdf/' . $letter->id) ?>" class="btn btn-xs btn-info no-print">
                    <i class="fa fa-download"></i> Download PDF
                </a>
                <a href="<?= base_url('admin/letter/print_letter/' . $letter->id) ?>" id="print-link" class="btn btn-xs btn-default no-print" target="_blank">
                    <i class="fa fa-print"></i> Print
                </a>
                <a href="<?= base_url('admin/letter/add_generate/' . $letter->id) ?>" class="btn btn-xs btn-primary no-print">
                    <i class="fa fa-edit"></i> Edit
                </a>
            </div>
        </div>
    </header>

    <div class="panel-body">
        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-body" id="letter-container" style="padding: <?= (int)$letter->margin_top ?>px <?= (int)$letter->margin_right ?>px <?= (int)$letter->margin_bottom ?>px <?= (int)$letter->margin_left ?>px; background: #fff; min-height: 400px;">
                        <?php if (!empty(config_item('company_logo'))): ?>
                            <div style="text-align:center;margin-bottom:20px;" class="company-logo no-print">
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

<style>
    @page {
        size: A4 portrait;
        margin: 2.54cm;
    }
    @media print {
        .no-print { display: none !important; }
        .hide-logo .company-logo { display: none !important; }
    }
</style>

<script type="text/javascript">
    (function () {
        var toggle = document.getElementById('toggle-logo');
        var container = document.getElementById('letter-container');
        var printLink = document.getElementById('print-link');
        if (!toggle || !container || !printLink) { return; }

        toggle.addEventListener('change', function () {
            container.classList.toggle('hide-logo', toggle.checked);
            var base = printLink.getAttribute('href').split('?')[0];
            printLink.setAttribute('href', toggle.checked ? base + '?hide_logo=1' : base);
        });
    })();
</script>
