<?php echo message_box('success'); ?>
<?php echo message_box('error');
$created = can_action('100', 'created');
$edited = can_action('100', 'edited');
$deleted = can_action('100', 'deleted');
?>
<div class="panel panel-custom" style="border: none;" data-collapsed="0">
    <!-- Table -->
    <div class="panel-body">
        <table class="table table-striped DataTables " id="DataTables" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?= lang('id') ?></th>
                <th><?= lang('user_name') ?></th>
                <th><?= lang('name') ?></th>
                <th><?= lang('phone_number') ?></th>
                <th><?= lang('email') ?></th>
                <th><?= lang('avatar') ?></th>
                <th><?= lang('action') ?></th>
            </tr>
            </thead>
            <tbody>
            <script type="text/javascript">
                $(document).ready(function () {
                    list = base_url + "woocommerce/customerlist";
                });
            </script>
            </tbody>
        </table>
    </div>
</div>