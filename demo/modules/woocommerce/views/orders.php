<?php echo message_box('success'); ?>
<?php echo message_box('error');
?>
<div class="panel panel-custom" style="border: none;" data-collapsed="0">
    <!-- Table -->
    <div class="panel-body">
        <table class="table table-striped DataTables " id="DataTables" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?= lang('order') ?>#</th>
                <th><?= lang('customer') ?></th>
                <th><?= lang('address') ?></th>
                <th><?= lang('phone_number') ?></th>
                <th><?= lang('total_spent') ?></th>
                <th><?= lang('order_date') ?></th>
                <th><?= lang('status') ?></th>
                <th><?= lang('invoice_number') ?></th>
                <!-- <th><?= lang('action') ?></th> -->
            </tr>
            </thead>
            <tbody>
            <script type="text/javascript">
                $(document).ready(function () {
                    list = base_url + "admin/woocommerce/orderslist";
                });
            </script>
            </tbody>
        </table>
    </div>
</div>