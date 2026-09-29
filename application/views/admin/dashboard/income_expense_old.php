<?php
if ($this->session->userdata('user_type') == 1) {
    $where = array('report' => 0, 'status' => 1);
} else {
    $where = array('report' => 0, 'status' => 1, 'for_staff' => 1);
}
$income_expense_order = get_row('tbl_dashboard', array('name' => 'income_expense') + $where);

if (!empty($income_expense)) { ?>
    <div class="panel panel-custom menu">
        <header class="panel-heading">
            <h3 class="panel-title"><?= lang('income_expense') ?><span class="sub">Selected month</span></h3>
            <form role="form" id="form" action="<?php echo base_url(); ?>admin/dashboard/index/month" method="post" class="form-horizontal form-groups-bordered hidden-xs">
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?= lang('select') . ' ' . lang('month') ?>
                        <span class="required">*</span></label>
                    <div class="col-sm-5">
                        <div class="input-group">
                            <input type="text" id="income_vs_expense_month_id" name="month" value="<?php
                                                                                                    if (!empty($month)) {
                                                                                                        echo $month;
                                                                                                    }
                                                                                                    ?>" class="form-control monthyear"><span class="input-group-addon"><a href="#"><i class="fa fa-calendar"></i></a></span>
                        </div>
                    </div>
                    <button type="submit" id="income_vs_expense_btn" data-toggle="tooltip" data-placement="top" title="Search" class="btn btn-custom"><i class="fa fa-search"></i></button>
                </div>
            </form>
        </header>
        <div class="panel-body">
            <div class="two">
                <div>
                    <div class="stat-lbl"><?= lang('income') ?></div>
                    <div class="stat-big" style="color:#0d9488"><?= display_money($income_expense['total_income'], default_currency()); ?></div>
                </div>
                <div>
                    <div class="stat-lbl"><?= lang('expense') ?></div>
                    <div class="stat-big" style="color:#dc2626"><?= display_money($income_expense['total_expense'], default_currency()); ?></div>
                </div>
            </div>
            <?php
            $ive_max = max((float) $income_expense['total_income'], (float) $income_expense['total_expense']);
            ?>
            <div class="bars pair" style="height:80px">
                <i style="height:<?= $ive_max > 0 ? max(6, round(((float) $income_expense['total_income'] / $ive_max) * 100)) : 6 ?>%"></i>
                <i class="red" style="height:<?= $ive_max > 0 ? max(6, round(((float) $income_expense['total_expense'] / $ive_max) * 100)) : 6 ?>%"></i>
            </div>
            <div class="legend">
                <span><i style="background:#0d9488"></i><?= lang('income') ?></span>
                <span><i style="background:#dc2626"></i><?= lang('expense') ?></span>
            </div>
        </div><!-- /.box-body -->
    </div><!-- /.box -->

    <script type="text/javascript">
        $(document).ready(function() {
            initdatepicker();
        });
    </script>
<?php } ?>
