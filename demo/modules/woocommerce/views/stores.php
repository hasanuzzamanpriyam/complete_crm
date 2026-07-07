<?php echo message_box('success'); ?>
<?php echo message_box('error');
?>
<div class="panel panel-custom" style="border: none;" data-collapsed="0">
    <div class="panel-heading">
        <div class="panel-title">
            <?= lang('all') . ' ' . lang('stores') ?>
            <div class="pull-right hidden-print" style="padding-top: 0px;padding-bottom: 8px">
                <a href="<?= base_url() ?>woocommerce/new_stores" class="btn btn-xs btn-info" data-toggle="modal"
                   data-placement="top" data-target="#myModal_lg">
                    <i class="fa fa-plus "></i> <?= ' ' . lang('new') . ' ' . lang('stores') ?></a>
            </div>
        </div>
    </div>
    <!-- Table -->
    <div class="panel-body">
        <table class="table table-striped DataTables " id="DataTables" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?= lang('store_name') ?></th>
                <th><?= lang('assigned_to') ?></th>
                <th><?= lang('date_created') ?></th>
                <th><?= lang('action') ?></th>
            </tr>
            </thead>
            <tbody>
            <script type="text/javascript">
                $(document).ready(function () {
                    list = base_url + "admin/woocommerce/storeslist";
                });
            </script>
            </tbody>
        </table>
    </div>
</div>
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
                toastr['success']('woocommerce reset successfully')
                woob.button('reset');
            });

    }

    function updateWooStore(el) {
        "use strict";
        let woob = $(el);
        let id = woob.data('id');
        $.post(base_url + 'admin/woocommerce/refresh/' + id, {})
            .done(function () {
                toastr['success']('woocommerce check successfully')
                woob.button('reset');
            });

    }
</script>