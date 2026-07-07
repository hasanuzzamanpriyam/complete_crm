<style type="text/css">
    .custom-bulk-button {
        display: initial;
    }

    .pa-md {
        padding-top: 8px !important;
        padding-bottom: 8px !important;
    }

    .width300 {
        width: 300px !important;
    }
</style>


<?= message_box('success'); ?>
<?= message_box('error'); ?>

<?php $is_department_head = is_department_head();
if ($active == '1' && ($this->session->userdata('user_type') == 1 || !empty($is_department_head))) { ?>
    <div id="state_report">
        <div id="complaints_state_report_div">
        </div>
    </div>
    
    
    <div class="btn-group pull-right btn-with-tooltip-group _filter_data filtered" data-toggle="tooltip"
         data-title="<?php echo lang('filter_by'); ?>">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
            <i class="fa fa-filter" aria-hidden="true"></i>
        </button>
        <ul class="dropdown-menu group animated zoomIn width300">
            <li class="filter_by all_filter"><a href="#"><?php echo lang('all'); ?></a></li>
            <li class="divider"></li>
            
            <li class="dropdown-submenu pull-left  " id="from_account">
                <a href="#" tabindex="-1"><?php echo lang('by') . ' ' . lang('client'); ?></a>
                <ul class="dropdown-menu dropdown-menu-left from_account">
                    <?php
                    if (!empty($all_client)) {
                        foreach ($all_client as $v_client) {
                            ?>
                            <li class="filter_by" id="<?= $v_client->client_id ?>" search-type="by_client">
                                <a href="#"><?php echo $v_client->name; ?></a>
                            </li>
                        <?php }
                    }
                    ?>
                </ul>
            </li>
            <div class="clearfix"></div>
            <li class="dropdown-submenu pull-left " id="by_type">
                <a href="#" tabindex="-1"><?php echo lang('by') . ' ' . lang('type'); ?></a>
                <ul class="dropdown-menu dropdown-menu-left by_type">
                    <?php
                    if (!empty($all_complaints_types)) { ?>
                        <?php foreach ($all_complaints_types as $c_type) {
                            ?>
                            <li class="filter_by" id="<?= $c_type->id ?>" search-type="by_type">
                                <a href="#"><?php echo $c_type->name; ?></a>
                            </li>
                        <?php }
                        ?>
                        <div class="clearfix"></div>
                    <?php } ?>
                </ul>
            </li>
        
        </ul>
    </div>
<?php } ?>

<div class="row">
    <div class="col-sm-12">
        <div class="nav-tabs-custom">
            <!-- Tabs within a box -->
            <ul class="nav nav-tabs">
                <li class="<?= $active == 1 ? 'active' : ''; ?>"><a
                            href="<?= base_url() ?>admin/complaints"><?= lang('complaints') ?></a>
                </li>
                
                <li class="<?= $active == 2 ? 'active' : ''; ?>"><a
                            href="<?= base_url() ?>admin/complaints/new_complaint"><?= lang('new_complaint') ?></a>
                </li>
                <li class="<?= $active == 3 ? 'active' : ''; ?>"><a
                            href="<?= base_url() ?>admin/complaints/complaint_types"><?= lang('complaint_types') ?></a>
                </li>
                <?php if ($active == 3) { ?>
                    <div class="pull-right hidden-print mr pa-md">
                        <a href="<?= base_url('admin/' . $module . '/new_complaint_type'); ?>"
                           class="btn btn-sm btn-primary" data-toggle="modal" data-placement="top"
                           data-target="#myModal">
                            <i class="fa fa-plus "></i> <?= lang('add_type') ?></a>
                    </div>
                <?php } ?>
            </ul>
            <div class="tab-content bg-white">
                <div class="tab-pane active">
                    <?= $page_content; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    (function ($) {
        "use strict";
        ins_data(base_url + 'admin/complaints/complaints_state_report');
    })(jQuery);
</script>