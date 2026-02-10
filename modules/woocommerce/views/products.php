<?php echo message_box('success'); ?>
<?php echo message_box('error');
?>
<div class="panel panel-custom" style="border: none;" data-collapsed="0">
    <!-- Table -->
    <div class="panel-body">
        <table class="table table-striped DataTables " id="DataTables" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?= lang('name') ?></th>
                <th><?= lang('status') ?></th>
                <th><?= lang('price') ?></th>
                <th><?= lang('sales') ?></th>
                <th><?= lang('picture') ?></th>
                <th><?= lang('view') ?></th>
            </tr>
            </thead>
            <tbody>
            <script type="text/javascript">
                $(document).ready(function () {
                    list = base_url + "woocommerce/productlist";
                });
            </script>
            </tbody>
        </table>
    </div>
</div>