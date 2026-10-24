<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>
<?php
$all_customer_group = $this->db->where('type', 'client')->order_by('customer_group_id', 'DESC')->get('tbl_customer_group')->result();
$mdate = date('Y-m-d');
$last_7_days = date('Y-m-d', strtotime('today - 7 days'));
$all_goal_tracking = $this->client_model->get_permission('tbl_goal_tracking');

$all_goal = 0;
$wthout_all_goal = 0;
$direct_complete_achivement = 0;
$without_complete_achivement = 0;
if (!empty($all_goal_tracking)) {
    foreach ($all_goal_tracking as $v_goal_track) {
        $goal_achieve = $this->client_model->get_progress($v_goal_track, true);

        if ($v_goal_track->goal_type_id == 11) {

            if ($v_goal_track->end_date <= $mdate) { // check today is last date or not

                if ($v_goal_track->email_send == 'no') { // check mail are send or not
                    if ($v_goal_track->achievement <= $goal_achieve['achievement']) {
                        if ($v_goal_track->notify_goal_achive == 'on') { // check is notify is checked or not check
                            $this->client_model->send_goal_mail('goal_achieve', $v_goal_track);
                        }
                    } else {
                        if ($v_goal_track->notify_goal_not_achive == 'on') { // check is notify is checked or not check
                            $this->client_model->send_goal_mail('goal_not_achieve', $v_goal_track);
                        }
                    }
                }
            }
            $all_goal += $v_goal_track->achievement;
            $direct_complete_achivement += $goal_achieve['achievement'];
        }
        if ($v_goal_track->goal_type_id == 10) {

            if ($v_goal_track->end_date <= $mdate) { // check today is last date or not

                if ($v_goal_track->email_send == 'no') { // check mail are send or not
                    if ($v_goal_track->achievement <= $goal_achieve['achievement']) {
                        if ($v_goal_track->notify_goal_achive == 'on') { // check is notify is checked or not check
                            $this->client_model->send_goal_mail('goal_achieve', $v_goal_track);
                        }
                    } else {
                        if ($v_goal_track->notify_goal_not_achive == 'on') { // check is notify is checked or not check
                            $this->client_model->send_goal_mail('goal_not_achieve', $v_goal_track);
                        }
                    }
                }
            }
            $wthout_all_goal += $v_goal_track->achievement;
            $without_complete_achivement += $goal_achieve['achievement'];
        }
    }
}
// 30 days before

for ($iDay = 7; $iDay >= 0; $iDay--) {
    $date = date('Y-m-d', strtotime('today - ' . $iDay . 'days'));
    $where = array('date_added >=' => $date . " 00:00:00", 'date_added <=' => $date . " 23:59:59");
    $invoice_result[$date] = count(array($this->db->where($where)->get('tbl_client')->result()));
}

$all_terget_achievement = $this->db->where(array('goal_type_id' => 11, 'start_date >=' => $last_7_days, 'end_date <=' => $mdate))->get('tbl_goal_tracking')->result();
$without_terget_achievement = $this->db->where(array('goal_type_id' => 10, 'start_date >=' => $last_7_days, 'end_date <=' => $mdate))->get('tbl_goal_tracking')->result();
if (!empty($all_terget_achievement)) {
    $all_terget_achievement = $all_terget_achievement;
} else {
    $all_terget_achievement = array();
}
if (!empty($without_terget_achievement)) {
    $without_terget_achievement = $without_terget_achievement;
} else {
    $without_terget_achievement = array();
}
$terget_achievement = array_merge($all_terget_achievement, $without_terget_achievement);
$total_terget = 0;
if (!empty($terget_achievement)) {
    foreach ($terget_achievement as $v_terget) {
        $total_terget += $v_terget->achievement;
    }
}

$curency = $this->client_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');

// Shared client asset pipeline (client_modern.css).
$this->load->view('admin/client/_client_assets');
?>
<style type="text/css">
    /* =======================================================================
       Scoped to the manage-clients table ONLY (rooted in .cl-manage, which
       exists on no other page) — never global. Hides the stock DataTables
       export-button cluster (the bulk-delete button is the sole exception,
       revealed below) + the default search box (search is relocated into
       the cl toolbar) and restyles the remaining DataTables controls with
       design tokens. .dataTables_* / .dt-button DOM only exists inside a
       DataTables-initialized table, so detail-pane cl-tbl tables and every
       other module's tables are unaffected.
       ======================================================================= */
    .cl-manage .dataTables_wrapper .dataTables_filter {
        display: none;
    }

    .cl-manage .dataTables_wrapper .dt-buttons .dt-button:not(.custom-bulk-button) {
        display: none;
    }

    .cl-manage .dataTables_wrapper .row {
        margin: 0;
    }

    .cl-manage .dataTables_wrapper .dataTables_length {
        padding: var(--sp-3) var(--sp-4) 0;
        font-size: var(--fs-sm);
        color: var(--c-text-3);
    }

    .cl-manage .dataTables_wrapper .dataTables_length select {
        border: 1px solid var(--c-border);
        border-radius: var(--r-md);
        padding: 4px var(--sp-2);
        background: var(--c-surface);
        color: var(--c-text);
        margin: 0 var(--sp-1);
    }

    /* Relocated filter (moved into .cl-search — no longer inside the wrapper,
       so the display:none rule above stops matching it). */
    .cl-manage .dataTables_filter {
        flex: 1;
        display: flex;
        align-items: center;
    }

    .cl-manage .dataTables_filter label {
        display: flex;
        align-items: center;
        gap: var(--sp-2);
        width: 100%;
        margin: 0;
        font-weight: 400;
    }

    .cl-manage .dataTables_filter input {
        border: none;
        outline: none;
        background: transparent;
        flex: 1;
        width: auto;
        padding: 0;
        font-size: var(--fs-base);
        color: var(--c-text);
    }

    .cl-manage .dataTables_wrapper .dataTables_info {
        padding: var(--sp-3) var(--sp-4);
        font-size: var(--fs-sm);
        color: var(--c-text-3);
    }

    .cl-manage .dataTables_wrapper .dataTables_paginate .paginate_button {
        border: 1px solid var(--c-border);
        background: var(--c-surface);
        border-radius: var(--r-md);
        margin-left: var(--sp-1);
        color: var(--c-text-2) !important;
        box-shadow: none !important;
    }

    .cl-manage .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: var(--c-surface-2);
        color: var(--c-text) !important;
    }

    .cl-manage .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .cl-manage .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover,
    .cl-manage .dataTables_wrapper .dataTables_paginate .paginate_button.current:focus,
    .cl-manage .dataTables_wrapper .dataTables_paginate .paginate_button.active,
    .cl-manage .dataTables_wrapper .dataTables_paginate .paginate_button.active:hover,
    .cl-manage .dataTables_wrapper .dataTables_paginate .paginate_button.active:focus,
    .cl-manage .dataTables_wrapper .dataTables_paginate .paginate_button.active > a,
    .cl-manage .dataTables_wrapper .dataTables_paginate .paginate_button.active > a:hover {
        background: var(--c-accent) !important;
        color: var(--c-on-accent) !important;
        border-color: var(--c-accent) !important;
    }

    .cl-manage .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .cl-manage .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: var(--c-slate) !important;
        background: transparent;
    }
</style>
<div class="cl-root cl-manage">
    <?php if ($this->session->userdata('user_type') == 1) {
        $total_goal = $all_goal + $wthout_all_goal;
        $complete_achivement = $direct_complete_achivement + $without_complete_achivement;
        if (!empty($total_goal)) {
            if ($total_goal <= $complete_achivement) {
                $total_progress = 100;
            } else {
                $progress = ($complete_achivement / $total_goal) * 100;
                $total_progress = round($progress);
            }
        } else {
            $total_progress = 0;
        }
        ?>
        <div class="cl-card cl-goal-strip">
            <div class="cl-goal-cell">
                <div class="cl-big"><?= (int)$all_goal ?></div>
                <div class="cl-lbl"><?= lang('without_converted') ?></div>
                <div class="cl-meta">
                    <span class="cl-meta-num"><?= (int)$direct_complete_achivement ?></div>
                    <span><?= lang('completed') . ' ' . lang('achievements') ?></span>
                </div>
            </div>
            <div class="cl-goal-cell">
                <div class="cl-big"><?= (int)$wthout_all_goal ?></div>
                <div class="cl-lbl"><?= lang('converted_client') ?></div>
                <div class="cl-meta">
                    <span class="cl-meta-num"><?= (int)$without_complete_achivement ?></span>
                    <span><?= lang('completed') . ' ' . lang('achievements') ?></span>
                </div>
            </div>
            <div class="cl-goal-cell">
                <div class="cl-lbl cl-spark-label"><?= lang('new_clients_last_7_days') ?></div>
                <div class="cl-spark">
                    <?php
                    if (!empty($invoice_result)) {
                        $max_val = max($invoice_result);
                        foreach ($invoice_result as $v) {
                            $h = $max_val > 0 ? round(($v / $max_val) * 100) : 0;
                            $hi_class = ($v == $max_val && $v > 0) ? 'hi' : '';
                            echo '<span style="height:' . $h . '%;" class="' . $hi_class . '"></span>';
                        }
                    }
                    ?>
                </div>
                <div class="cl-spark-labels">
                    <?php
                    if (!empty($invoice_result)) {
                        foreach ($invoice_result as $date => $v) {
                            echo '<span>' . date('d', strtotime($date)) . '</span>';
                        }
                    }
                    ?>
                </div>
            </div>
            <div class="cl-goal-cell">
                <div class="cl-row2">
                    <div>
                        <div class="cl-lbl"><?= lang('goal_progress') ?></div>
                        <div class="cl-meta" style="font-size:12px"><?= lang('this_period') ?></div>
                    </div>
                    <div class="cl-donut" style="--pct: <?= $total_progress ?>%;">
                        <i><?= $total_progress ?>%</i>
                    </div>
                </div>
            </div>
        </div>
    <?php }

    $id = $this->uri->segment(5);
    $search_by = $this->uri->segment(4);
    $created = can_action('4', 'created');
    $edited = can_action('4', 'edited');
    $deleted = can_action('4', 'deleted');
    $is_department_head = is_department_head();
    if (!empty($created) || !empty($edited)) { ?>
        <div class="cl-card">
            <div class="cl-tabs">
                <a class="<?= $active == 1 ? 'active' : '' ?>"
                   href="<?= base_url('admin/client/manage_client') ?>"><?= lang('client_list') ?></a>
                <a class="<?= $active == 2 ? 'active' : '' ?>"
                   href="<?= base_url('admin/client/create_client') ?>"><?= lang('new_client') ?></a>
                <a href="<?= base_url() ?>admin/client/import"><?= lang('import') . ' ' . lang('client') ?></a>
            </div>
            <?php } else { ?>
        <div class="cl-card">
            <div class="cl-tabs">
                <a class="active"
                   href="<?= base_url('admin/client/manage_client') ?>"><?= lang('client_list') ?></a>
            </div>
            <?php } ?>
            <div class="cl-toolbar">
                <div class="cl-search" id="cl-manage-search">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>
                <?php
                if ($this->session->userdata('user_type') == 1 || !empty($is_department_head)) { ?>
                    <div class="btn-group _filter_data filtered" style="position:relative;" data-toggle="tooltip"
                         data-title="<?php echo lang('filter_by'); ?>">
                        <button type="button" class="cl-btn cl-btn-ghost dropdown-toggle" data-toggle="dropdown"
                                aria-haspopup="true"
                                aria-expanded="false">
                            <i class="fa fa-filter" aria-hidden="true"></i> <?php echo lang('filter_by'); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-left group" style="width:300px;">
                            <li class="filter_by"><a href="#"><?php echo lang('all'); ?></a></li>
                            <li class="divider"></li>
                            <?php if (count(array($all_customer_group)) > 0) { ?>
                                <?php foreach ($all_customer_group as $group) {
                                    ?>
                                    <li class="filter_by" id="<?= $group->customer_group_id ?>">
                                        <a href="#"><?php echo $group->customer_group; ?></a>
                                    </li>
                                <?php }
                                ?>
                                <div class="clearfix"></div>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>
                <div style="flex:1"></div>
                <?php if (!empty($created)) { ?>
                    <a href="<?= base_url('admin/client/create_client') ?>" class="cl-btn cl-btn-primary">
                        <i class="fa fa-plus"></i> <?= lang('new_client') ?>
                    </a>
                <?php } ?>
            </div>
            <style type="text/css">
                .custom-bulk-button {
                    display: initial;
                }
            </style>
            <div id="client_list" style="position: relative;">
                <table class="cl-tbl DataTables bulk_table " id="DataTables" cellspacing="0"
                       width="100%">
                    <thead>
                    <tr>
                        <?php if (!empty($deleted)) { ?>
                            <th data-orderable="false">
                                <div class="checkbox c-checkbox">
                                    <label class="needsclick">
                                        <input id="select_all" type="checkbox">
                                        <span class="fa fa-check"></span></label>
                                </div>
                            </th>
                        <?php } ?>
                        <th><?= lang('name') ?> </th>
                        <th><?= lang('contacts') ?></th>
                        <th><?= lang('primary_contact') ?></th>
                        <th class="cl-num"><?= lang('projects') ?> </th>
                        <th class="cl-num"><?= lang('due_amount') ?> </th>
                        <th class="cl-num"><?= lang('received_amount') ?> </th>
                        <th class="cl-num"><?= lang('expense') ?> </th>
                        <th><?= lang('group') ?> </th>
                        <?php $show_custom_fields = custom_form_table(12, null);
                        if (!empty($show_custom_fields)) {
                            foreach ($show_custom_fields as $c_label => $v_fields) {
                                if (!empty($c_label)) {
                                    ?>
                                    <th><?= $c_label ?> </th>
                                <?php }
                            }
                        }
                        ?>
                        <th class="hidden-print"><?= lang('action') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            list = base_url + "admin/client/clientList";
                            bulk_url = base_url + 'admin/client/bulk_delete';
                            $('.filtered > .dropdown-toggle').on('click', function () {
                                if ($('.group').css('display') == 'block') {
                                    $('.group').css('display', 'none');
                                } else {
                                    $('.group').css('display', 'block')
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
                                table_url(base_url + "admin/client/clientList/" +
                                    filter_by);
                            });
                        });
                    </script>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(function () {
            /* Relocate the DataTables search input into the cl toolbar (the plugin
               node is moved, so its keyup binding keeps working). Runs after
               fire_datatable()'s own ready handler via the timeout. */
            setTimeout(function () {
                var $filter = $('.cl-manage .dataTables_filter');
                if ($filter.length) {
                    $filter.find('label').contents().filter(function () {
                        return this.nodeType === 3;
                    }).remove();
                    $filter.appendTo('#cl-manage-search');
                }
            }, 0);
        });
    </script>
    <script type="text/javascript">
        function fetch_lat_long_from_google_cprofile() {
            var data = {};
            data.address = $('textarea[name="address"]').val();
            data.city = $('input[name="city"]').val();
            data.country = $('select[name="country"] option:selected').text();
            console.log(data);
            $('#gmaps-search-icon').removeClass('fa-google').addClass('fa-spinner fa-spin');
            $.post('<?= base_url() ?>admin/common/fetch_address_info_gmaps', data).done(function (data) {
                data = JSON.parse(data);
                $('#gmaps-search-icon').removeClass('fa-spinner fa-spin').addClass('fa-google');
                if (data.response.status == 'OK') {
                    $('input[name="latitude"]').val(data.lat);
                    $('input[name="longitude"]').val(data.lng);
                } else {
                    if (data.response.status == 'ZERO_RESULTS') {
                        toastr.warning("<?php echo lang('g_search_address_not_found'); ?>");
                    } else {
                        toastr.warning(data.response.status);
                    }
                }
            });
        }
    </script>