<style type="text/css">
    .custom-bulk-button {
        display: initial;
    }

    .wd-300 {
        width: 300px !important;
    }
</style>


<?= message_box('success'); ?>
<?= message_box('error');

$created = can_action_by_label('contracts', 'created');
$edited = can_action_by_label('contracts', 'edited');
$deleted = can_action_by_label('contracts', 'deleted');
?>

<?php $is_department_head = is_department_head();
if ($active == '1' && ($this->session->userdata('user_type') == 1 || !empty($is_department_head))) { ?>
    <div id="state_report ">
        <div class="row mb">
            <div class="col-lg-3">
                <!-- START widget-->
                <div class="panel widget">
                    <div class="row row-table row-flush">
                        <div class="col-xs-4 bg-success text-center">
                            <em class="fa fa-bullseye fa-2x"></em>
                        </div>
                        <div class="col-xs-8">
                            <div class="panel-body text-center">
                                <h4 class="mt0"><?php echo $count_active; ?></h4>
                                <p class="mb0 text-muted"><a class="filter_by_type" id="active"
                                                             href="#"><?php echo lang('active_contracts'); ?></a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END widget-->
            </div>
            <div class="col-lg-3">
                <!-- START widget-->
                <div class="panel widget">
                    <div class="row row-table row-flush">
                        <div class="col-xs-4 bg-danger text-center">
                            <em class="fa fa-bullseye fa-2x"></em>
                        </div>
                        <div class="col-xs-8">
                            <div class="panel-body text-center">
                                <h4 class="mt0"><?php echo $count_expired; ?></h4>
                                <p class="mb0 text-muted"><a class="filter_by_type" id="expired"
                                                             href="#"><?php echo lang('expired_contracts'); ?></a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END widget-->
            </div>
            <div class="col-lg-3">
                <!-- START widget-->
                <div class="panel widget">
                    <div class="row row-table row-flush">
                        <div class="col-xs-4 bg-warning text-center">
                            <em class="fa fa-bullseye fa-2x"></em>
                        </div>
                        <div class="col-xs-8">
                            <div class="panel-body text-center">
                                <h4 class="mt0"><?php echo count($expiring); ?></h4>
                                <p class="mb0 text-muted"><a class="filter_by_type" id="expiring"
                                                             href="#"><?php echo lang('expiring_contracts'); ?></a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END widget-->
            </div>
            <div class="col-lg-3">
                <!-- START widget-->
                <div class="panel widget">
                    <div class="row row-table row-flush">
                        <div class="col-xs-4 bg-success text-center">
                            <em class="fa fa-bullseye fa-2x"></em>
                        </div>
                        <div class="col-xs-8">
                            <div class="panel-body text-center">
                                <h4 class="mt0"><?php echo $count_recently_created; ?></h4>
                                <p class="mb0 text-muted"><a class="filter_by_type" id="recently_created"
                                                             href="#"><?php echo lang('recent_contracts'); ?></a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END widget-->
            </div>
        </div>
    </div>

<?php } ?>

<div class="row">
    <div class="col-sm-12">
        <div class="nav-tabs-custom">
            <!-- Tabs within a box -->
            <ul class="nav nav-tabs">
                <li class="<?= $active == 1 ? 'active' : ''; ?>"><a
                            href="<?= base_url() ?>admin/contracts"><?= lang('contracts') ?></a>
                </li>
                
                <li class="<?= $active == 2 ? 'active' : ''; ?>"><a
                            href="<?= base_url() ?>admin/contracts/new_contract"><?= lang('new_contract') ?></a>
                </li>
                <li class="<?= $active == 3 ? 'active' : ''; ?>"><a
                            href="<?= base_url() ?>admin/contracts/contract_types"><?= lang('contract_types') ?></a>
                </li>
                <?php if ($active == 3) { ?>
                    <li class="pull-right hidden-print mr">
                        <a href="<?= base_url('admin/' . $module . '/new_contract_type'); ?>" data-toggle="modal"
                           data-placement="top"
                           data-target="#myModal">
                            <i class="fa fa-plus "></i> <?= lang('add_type') ?></a>
                    </li>
                <?php } ?>
                <?php if ($active == 1) { ?>
                    <li class="pull-right hidden-print mr mt-sm">
                        <div class="btn-group pull-right btn-with-tooltip-group _filter_data filtered"
                             data-toggle="tooltip"
                             data-title="<?php echo lang('filter_by'); ?>">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false">
                                <i class="fa fa-filter" aria-hidden="true"></i>
                            </button>
                            <ul class="dropdown-menu group animated zoomIn wd-300">
                                <li class="filter_by all_filter"><a href="#"><?php echo lang('all'); ?></a></li>
                                <li class="divider"></li>
                                <li class="dropdown-submenu pull-left  " id="from_account">
                                    <a href="#" tabindex="-1"><?php echo lang('by') . ' ' . lang('client'); ?></a>
                                    <ul class="dropdown-menu dropdown-menu-left from_account">
                                        <?php
                                        if (!empty($all_client)) {
                                            foreach ($all_client as $v_client) {
                                                ?>
                                                <li class="filter_by" id="<?= $v_client->client_id ?>"
                                                    search-type="by_client">
                                                    <a href="#"><?php echo $v_client->name; ?></a>
                                                </li>
                                            <?php }
                                        }
                                        ?>
                                    </ul>
                                </li>
                                <div class="clearfix"></div>
                                <li class="dropdown-submenu pull-left  " id="by_project">
                                    <a href="#" tabindex="-1"><?php echo lang('by') . ' ' . lang('project'); ?></a>
                                    <ul class="dropdown-menu dropdown-menu-left from_account">
                                        <?php
                                        if (!empty($cproject_info)) {
                                            foreach ($cproject_info as $v_cproject) {
                                                ?>
                                                <li class="filter_by" id="<?= $v_cproject->project_id ?>"
                                                    search-type="by_project">
                                                    <a href="#"><?php echo $v_cproject->project_name; ?></a>
                                                </li>
                                            <?php }
                                        }
                                        ?>
                                    </ul>
                                </li>
                                
                                <div class="clearfix"></div>
                                <li class="dropdown-submenu pull-left " id="by_type">
                                    <a href="#" tabindex="-1"><?php echo lang('by') . ' ' . lang('type'); ?></a>
                                    <ul class="dropdown-menu dropdown-menu-left by_type" style="">
                                        <?php
                                        if (!empty($all_contracts_types)) { ?>
                                            <?php foreach ($all_contracts_types as $c) {
                                                ?>
                                                <li class="filter_by" id="<?= $c->id ?>" search-type="by_type">
                                                    <a href="#"><?php echo $c->name; ?></a>
                                                </li>
                                            <?php }
                                            ?>
                                            <div class="clearfix"></div>
                                        <?php } ?>
                                    </ul>
                                </li>
                            
                            </ul>
                        </div>
                    </li>
                <?php }
                ?>
            </ul>
            <div class="tab-content bg-white">
                <div class="tab-pane active">
                    <?= $page_content; ?>
                </div>
            </div>
        </div>
    </div>
</div>