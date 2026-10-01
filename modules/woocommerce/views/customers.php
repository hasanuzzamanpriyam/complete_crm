<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/css/woocommerce_modern.css?v=2">
<div class="woo-modern">
    <h1 class="page-t"><?= lang('customers') ?></h1>

    <div class="kpi-strip">
        <div class="kpi-grid">
            <?php foreach ($kpi as $k) { ?>
                <div class="kpi-card">
                    <div class="k-i <?= $k['cls'] ?>"><?= $k['icon'] ?></div>
                    <div>
                        <div class="v"><?= $k['val'] ?></div>
                        <div class="l"><?= $k['label'] ?></div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <section class="card">
        <div class="list-toolbar">
            <div class="tsearch">
                <span class="ico">&#9906;</span>
                <input type="text" class="woo-q" placeholder="<?= lang('search_customers') ?>">
            </div>
            <div class="dd-wrap woo-dd" data-key="store">
                <button type="button" class="filter-pill"><span class="c-dot"></span><span
                        class="pill-label"><?= lang('all_stores') ?></span>&#9662;</button>
                <div class="dd">
                    <div class="dd-item on" data-v="" data-label="<?= lang('all_stores') ?>"><span class="ck">&#10003;</span><?= lang('all_stores') ?></div>
                    <?php foreach ($woo_stores as $st) { ?>
                        <div class="dd-item" data-v="<?= (int) $st->store_id ?>" data-label="<?= htmlspecialchars($st->store_name, ENT_QUOTES, 'UTF-8') ?>"><span class="ck"></span><?= htmlspecialchars($st->store_name, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php } ?>
                </div>
            </div>
            <div class="tb-right">
                <button type="button" class="icon-btn woo-xls" title="<?= lang('export_excel') ?>"><i class="fa fa-file-excel-o"></i></button>
                <button type="button" class="icon-btn woo-csv" title="<?= lang('export_csv') ?>"><i class="fa fa-download"></i></button>
                <button type="button" class="icon-btn woo-pdf" title="<?= lang('export_pdf') ?>"><i class="fa fa-file-pdf-o"></i></button>
                <button type="button" class="icon-btn woo-print" title="<?= lang('print') ?>"><i class="fa fa-print"></i></button>
                <button type="button" class="icon-btn woo-sel" title="<?= lang('print_selected') ?>"><i class="fa fa-files-o"></i></button>
                <button type="button" class="icon-btn woo-del" title="<?= lang('bulk_delete') ?>"><i class="fa fa-trash-o"></i></button>
            </div>
        </div>
        <div class="tbl-wrap">
            <table class="table table-striped DataTables tbl" id="DataTables" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th><?= lang('customer') ?></th>
                    <th><?= lang('email') ?></th>
                    <th><?= lang('phone') ?></th>
                    <th><?= lang('stores') ?></th>
                    <th class="t-r"><?= lang('orders') ?></th>
                    <th class="t-r"><?= lang('action') ?></th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>
</div>
<script type="text/javascript" src="<?= base_url() ?>assets/js/woocommerce-modern.js"></script>
<script type="text/javascript">
    $(function () {
        list = base_url + "admin/woocommerce/customerlist";
        woo_opts = {
            ajaxData: function (d) {
                d.woo_store = WOO.store;
            },
            config: {
                dom: 'lrtip',
                columnDefs: [
                    {targets: [4, 5], orderable: false},
                    {targets: [4, 5], className: 't-r'}
                ],
                oLanguage: {
                    sLengthMenu: "_MENU_",
                    sZeroRecords: "<?= lang('nothing_found_sorry') ?>",
                    sInfoEmpty: "<?= lang('no_record_available') ?>",
                    sInfo: "<?= lang('showing') ?> _START_–_END_ <?= lang('of') ?> _TOTAL_",
                    sInfoFiltered: "",
                    zeroRecords: "<?= lang('nothing_found_sorry') ?>",
                    infoEmpty: "<?= lang('no_record_available') ?>",
                    info: "<?= lang('showing') ?> _START_–_END_ <?= lang('of') ?> _TOTAL_",
                    infoFiltered: ""
                }
            },
            onInit: function (api) {
                WOO.bind(api);
            }
        };
    });
</script>
