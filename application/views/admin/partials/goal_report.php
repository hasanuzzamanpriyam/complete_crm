<?php
$where = array();
if ($this->session->userdata('user_type') == 1) {
    $where = array('status' => 1);
} else {
    $t_where = array('for_staff' => 1);
    if (!empty($where)) {
        $where = array_merge($t_where, $where);
    }
}

$goal_report_order = get_row('tbl_dashboard', array('name' => 'goal_report') + $where);

if (!empty($goal_report_order)) {

// goal tracking
    if ($this->input->post('goal_month', TRUE)) { // if input year
        $data['goal_month'] = $this->input->post('goal_month', TRUE);
    } else { // else current year
        $data['goal_month'] = date('Y-m'); // get current year
    }
    
    $goal_report = $this->admin_model->get_goal_report($data['goal_month']);
    ?>
    <div class="panel panel-custom menu">
        <header class="panel-heading">
            <h3 class="panel-title"><?= lang('goal') . ' ' . lang('report') ?><span class="sub">Achievement vs target</span></h3>
        </header>
        <div class="panel-body">
            <p class="text-center ">
            <form role="form" id="form"
                  action="<?php echo base_url(); ?>admin/dashboard/index/goal_month"
                  method="post" class="form-horizontal form-groups-bordered hidden-xs">
                <div class="form-group">
                    <label
                            class="col-sm-3 control-label"> <?= lang('select') . ' ' . lang('month') ?>
                        <span
                                class="required">*</span></label>
                    <div class="col-sm-5">
                        <div class="input-group">
                            <input type="text" name="goal_month" id="goal_month_id" value="<?php
                            if (!empty($goal_month)) {
                                echo $goal_month;
                            }
                            ?>" class="form-control monthyear"><span class="input-group-addon"><a
                                        href="#"><i
                                            class="fa fa-calendar"></i></a></span>
                        </div>
                    </div>
                    <button type="submit" id="goal_report_btn" data-toggle="tooltip" data-placement="top" title="Search"
                            class="btn btn-custom"><i class="fa fa-search"></i></button>
                </div>
            </form>
            </p>
            <!--End select input year -->
            <!--Sales Chart Canvas -->
            <div class="goal-list">
                <?php if (!empty($goal_report)) {
                    foreach ($goal_report as $v_goal_report) {
                        $g_pct = ($v_goal_report->target > 0) ? min(100, round(((float) $v_goal_report->amount_or_count / (float) $v_goal_report->target) * 100)) : 0;
                        ?>
                        <div class="goal">
                            <div class="gr">
                                <span><?= lang($v_goal_report->type_name) ?></span>
                                <b style="color:#6d28d9"><?= $g_pct ?>%</b>
                            </div>
                            <div class="track"><span style="width:<?= $g_pct ?>%;background:#8b5cf6"></span></div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <p class="text-muted text-center"><?= lang('no_result_found') ?></p>
                <?php } ?>
            </div>
        </div><!-- ./box-body -->
    
    </div>
    <script type="text/javascript">
        $(document).ready(function () {
            initdatepicker();
        });
    </script>
<?php }
?>