<div class="panel panel-custom">
    <div class="panel-heading">
        <h3 class="panel-title"><?= lang('renewal_history') ?>
            <div class="pull-right hidden-print">
                <a href="<?= base_url('admin/' . $module . '/renew/' . $module_field_id); ?>"
                   class="btn btn-xs btn-warning" data-toggle="modal" data-placement="top" data-target="#myModal">
                    <i class="fa fa-plus "></i> <?= lang('renew') ?></a>
            </div>
        </h3>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-striped DataTables bulk_table" id="DataTables" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th><?= lang('new_start_date') ?></th>
                    <th><?= lang('old_start_date') ?></th>
                    <th><?= lang('new_end_date') ?></th>
                    <th><?= lang('old_end_date') ?></th>
                    <th><?= lang('new_value') ?></th>
                    <th><?= lang('old_value') ?></th>
                    <th><?= lang('renewed_by') ?></th>
                    <th><?= lang('date') ?></th>
                    <th class="col-options no-sort"><?= lang('action') ?></th>
                </tr>
                </thead>
                <tbody>
                </tbody>
                
                <script type="text/javascript">
                    (function ($) {
                        "use strict";
                        list = base_url + "admin/contracts/contractsHistory";
                    })(jQuery);
                </script>
            </table>
        </div>
    </div>
</div>

