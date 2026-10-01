<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/css/woocommerce_modern.css?v=2">
<div class="woo-modern">
    <h1 class="page-t"><?= lang('all') ?> <?= lang('stores') ?></h1>

    <div class="kpi-strip">
        <div class="kpi-grid">
            <?php foreach ($kpi as $k) { ?>
                <div class="kpi-card">
                    <div class="k-i <?= $k['cls'] ?>"><?= $k['icon'] ?></div>
                    <div><div class="v"><?= $k['val'] ?></div><div class="l"><?= $k['label'] ?></div></div>
                </div>
            <?php } ?>
        </div>
    </div>

    <section class="card">
        <div class="list-toolbar">
            <div class="tsearch">
                <span class="ico">&#9906;</span>
                <input type="text" class="woo-q" placeholder="<?= lang('search_stores') ?>">
            </div>
            <div class="tb-right">
                <button type="button" class="icon-btn woo-xls" title="<?= lang('export_excel') ?>"><i class="fa fa-file-excel-o"></i></button>
                <button type="button" class="icon-btn woo-csv" title="<?= lang('export_csv') ?>"><i class="fa fa-download"></i></button>
                <button type="button" class="icon-btn woo-pdf" title="<?= lang('export_pdf') ?>"><i class="fa fa-file-pdf-o"></i></button>
                <button type="button" class="icon-btn woo-print" title="<?= lang('print') ?>"><i class="fa fa-print"></i></button>
                <button type="button" class="icon-btn woo-sel" title="<?= lang('print_selected') ?>"><i class="fa fa-files-o"></i></button>
                <button type="button" class="icon-btn woo-del" title="<?= lang('bulk_delete') ?>"><i class="fa fa-trash-o"></i></button>
                <a href="<?= base_url() ?>woocommerce/new_stores" class="btn-new" data-toggle="modal"
                   data-target="#myModal_lg"><i class="fa fa-plus"></i> <?= lang('new') ?> <?= lang('stores') ?></a>
            </div>
        </div>
        <div class="tbl-wrap">
            <table class="table table-striped DataTables tbl" id="DataTables" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th><?= lang('store_name') ?></th>
                    <th><?= lang('assigned_to') ?></th>
                    <th><?= lang('synced') ?></th>
                    <th><?= lang('date_created') ?></th>
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
        list = base_url + "admin/woocommerce/storeslist";
        woo_opts = {
            ajaxData: function (d) {
                d.woo_store = WOO.store;
            },
            config: {
                dom: 'lrtip',
                columnDefs: [
                    {targets: [1, 2, 4], orderable: false},
                    {targets: [4], className: 't-r'}
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
<script>
    function wooco_test(el) {
        "use strict";
        let woob = $(el);
        let id = woob.data('id');
        $.post(base_url + "admin/woocommerce/test_connection/" + id, {})
            .done(function (response) {
                if (response) {
                    response = JSON.parse(response);
                    if (response.success == true) {
                        toastr['success'](response.message);
                        woob.button('reset');
                    } else {
                        toastr['warning'](response.message);
                        woob.button('reset');
                    }
                }
            });
    }

    function woo_reset(el) {
        "use strict";
        let woob = $(el);
        let id = woob.data('id');
        $.post(base_url + 'admin/woocommerce/reset/' + id, {})
            .done(function (response) {
                toastr['success']('<?= addslashes(lang('woocommerce_reset_successful')) ?>');
                woob.button('reset');
            });
    }

    function updateWooStore(el) {
        "use strict";
        let woob = $(el);
        let id = woob.data('id');
        $.post(base_url + 'admin/woocommerce/refresh/' + id, {})
            .done(function () {
                toastr['success']('<?= addslashes(lang('woocommerce_check_successful')) ?>');
                woob.button('reset');
            });
    }
</script>
