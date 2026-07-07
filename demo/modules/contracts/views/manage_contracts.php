<div class="table-responsive">
    <table class="table table-striped DataTables bulk_table" id="DataTables" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th><?= lang('subject') ?></th>
            <th><?= lang('Customer') ?></th>
            <th><?= lang('Project') ?></th>
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
                list = base_url + "admin/contracts/contractsList";
                bulk_url = base_url + "admin/contracts/bulk_delete";
                <?php if (admin_head()) { ?>
                $('.filtered > .dropdown-toggle').on('click', function () {
                    if ($('.group').css('display') == 'block') {
                        $('.group').css('display', 'none');
                    } else {
                        $('.group').css('display', 'block')
                    }
                });
                $('.all_filter').on('click', function () {
                    $('.to_account').removeAttr("style");
                    $('.from_account').removeAttr("style");
                });
                $('.from_account li').on('click', function () {
                    if ($('.to_account').css('display') == 'block') {
                        $('.to_account').removeAttr("style");
                        $('.from_account').css('display', 'block');
                    } else {
                        $('.from_account').css('display', 'block')
                    }
                });

                $('.to_account li').on('click', function () {
                    if ($('.from_account').css('display') == 'block') {
                        $('.from_account').removeAttr("style");
                        $('.to_account').css('display', 'block');
                    } else {
                        $('.to_account').css('display', 'block');
                    }
                });
                $('.filter_by').on('click', function () {
                    $('.filter_by').removeClass('active');
                    $('.group').css('display', 'block');
                    $(this).addClass('active');
                    var filter_by = $(this).attr('id');
                    if (filter_by) {
                        filter_by = filter_by;
                    } else {
                        filter_by = '';
                    }
                    var search_type = $(this).attr('search-type');
                    if (search_type) {
                        search_type = '/' + search_type;
                    } else {
                        search_type = '';
                    }
                    table_url(base_url + "admin/contracts/contractsList/" + filter_by + search_type);
                });
                $('.filter_by_type').on('click', function () {
                    var filter_by = $(this).attr('id');
                    if (filter_by) {
                        filter_by = filter_by;
                    } else {
                        filter_by = '';
                    }
                    table_url(base_url + "admin/contracts/contractsList/" + filter_by);
                });
                <?php } ?>
            })(jQuery);
        </script>
    </table>
</div>