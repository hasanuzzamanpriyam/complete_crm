<div class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title">
            <?php echo lang('contracts') . ' ' . lang('list'); ?>
        </div>
    </div>
    <div class="panel-body">
        
        <div class="table-responsive">
            <table class="table table-striped DataTables bulk_table" id="DataTables" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th><?= lang('subject') ?></th>
                    <th><?= lang('type') ?></th>
                    <th><?= lang('value') ?></th>
                    <th><?= lang('start_date') ?></th>
                    <th><?= lang('end_date') ?></th>
                    <th><?= lang('signature') ?></th>
                    <th class="col-options no-sort"><?= lang('action') ?></th>
                </tr>
                </thead>
                <tbody>
                </tbody>
                
                <script type="text/javascript">
                    (function ($) {
                        "use strict";
                        list = base_url + "contracts/contract/contractsList";

                    })(jQuery);
                </script>
            </table>
        </div>
    </div>
</div>