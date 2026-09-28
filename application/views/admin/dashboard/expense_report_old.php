<?php
if ($this->session->userdata('user_type') == 1) {
    $where = array('report' => 0, 'status' => 1);
} else {
    $where = array('report' => 0, 'status' => 1, 'for_staff' => 1);
}
$expense_report_order = get_row('tbl_dashboard', array('name' => 'expense_report') + $where);

?>

<div class="panel panel-custom menu">
    <header class="panel-heading">
        <h3 class="panel-title"><?= lang('expense_report') ?><span class="sub">12-month trend</span></h3>
    </header>
    <div class="panel-body">
        <form role="form" id="form" action="<?php echo base_url(); ?>admin/dashboard" method="post" class="form-horizontal form-groups-bordered hidden-xs">
            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('select') . ' ' . lang('year') ?>
                    <span class="required">*</span></label>
                <div class="col-sm-5">
                    <div class="input-group">
                        <input type="text" id="expense_report_id" name="year" value="<?php
                                                                                        if (!empty($year)) {
                                                                                            echo $year;
                                                                                        }
                                                                                        ?>" class="form-control years"><span class="input-group-addon"><a href="#"><i class="fa fa-calendar"></i></a></span>
                    </div>
                </div>
                <button type="submit" id="expense_report_btn" data-toggle="tooltip" data-placement="top" title="Search" class="btn btn-custom"><i class="fa fa-search"></i></button>
            </div>
        </form>
        <!--End select input year -->
        <div class="bars">
            <?php
            if (!empty($all_expense) && !empty($expense_report_order)) {
                $exp_max = 0;
                foreach ($all_expense as $v_exp) {
                    $exp_max = max($exp_max, (float) $v_exp);
                }
                foreach ($all_expense as $v_exp) {
                    $exp_h = $exp_max > 0 ? max(6, round(((float) $v_exp / $exp_max) * 100)) : 6;
                    ?><i class="red" style="height:<?= $exp_h ?>%"></i><?php
                }
            }
            ?>
        </div>
        <div class="xaxis">
            <?php if (!empty($all_expense)) {
                foreach ($all_expense as $name => $v_exp) {
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