<?php
if ($this->session->userdata('user_type') == 1) {
    $where = array('report' => 0, 'status' => 1);
} else {
    $where = array('report' => 0, 'status' => 1, 'for_staff' => 1);
}
$income_report_order = get_row('tbl_dashboard', array('name' => 'income_report') + $where);
if ($this->session->userdata('user_type') == '1') { ?>

    <div class="panel panel-custom menu">
        <header class="panel-heading">
            <h3 class="panel-title"><?= lang('income_report') ?><span class="sub">12-month trend</span></h3>
            <form role="form" id="form" action="<?php echo base_url(); ?>admin/dashboard/index/Income" method="post" class="form-horizontal form-groups-bordered hidden-xs">
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?= lang('select') . ' ' . lang('year') ?>
                        <span class="required">*</span></label>
                    <div class="col-sm-5">
                        <div class="input-group">
                            <input type="text" id="income_report_id" name="Income" value="<?php

                                                                                            if (!empty($Income)) {
                                                                                                echo $Income;
                                                                                            }
                                                                                            ?>" class="form-control years"><span class="input-group-addon"><a href="#"><i class="fa fa-calendar"></i></a></span>
                        </div>
                    </div>
                    <button type="submit" id="income_report_btn" data-toggle="tooltip" data-placement="top" title="Search" class="btn btn-custom"><i class="fa fa-search"></i></button>
                </div>
            </form>
        </header>
        <div class="panel-body">
            <div class="bars">
                <?php
                if (!empty($all_income) && !empty($income_report_order)) {
                    $inc_max = 0;
                    foreach ($all_income as $v_inc) {
                        $inc_max = max($inc_max, (float) $v_inc);
                    }
                    foreach ($all_income as $v_inc) {
                        $inc_h = $inc_max > 0 ? max(6, round(((float) $v_inc / $inc_max) * 100)) : 6;
                        ?><i style="height:<?= $inc_h ?>%"></i><?php
                    }
                }
                ?>
            </div>
            <div class="xaxis">
                <?php if (!empty($all_income)) {
                    foreach ($all_income as $name => $v_inc) {
                        ?><span><?= date('M', strtotime($year . '-' . $name)) ?></span><?php
                    }
                } ?>
            </div>
        </div><!-- ./box-body -->

    </div>
    <script type="text/javascript">
        $(document).ready(function() {
            initdatepicker();
        });
    </script>


<?php } ?>