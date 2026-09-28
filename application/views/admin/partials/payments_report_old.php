<?php
$action = $this->uri->segment(4);
if ($this->session->userdata('user_type') == 1) {
    $where = array('status' => 1);
} else {
    $t_where = array('for_staff' => 1);
    $where = $where + $t_where;
}
$payments_report_order = get_row('tbl_dashboard', array('name' => 'payments_report') + $where);
if (!empty($payments_report_order)) {
    $yearly_overview = $this->admin_model->get_yearly_overview($yearly);
?>


    <div class="panel panel-custom menu">
        <header class="panel-heading">
            <h3 class="panel-title"><?= lang('payments_report') ?><span class="sub">12-month collections</span></h3>
        </header>
        <div class="panel-body">
            <form role="form" id="form" action="<?php echo base_url(); ?>admin/dashboard/index/payments" method="post" class="form-horizontal form-groups-bordered hidden-xs">
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?= lang('select') . ' ' . lang('year') ?>
                        <span class="required">*</span></label>
                    <div class="col-sm-5">
                        <div class="input-group">
                            <input type="text" name="yearly" id="payment_year_id" value="<?php
                                                                                            if (!empty($yearly)) {
                                                                                                echo $yearly;
                                                                                            }
                                                                                            ?>" class="form-control years"><span class="input-group-addon"><a href="#"><i class="fa fa-calendar"></i></a></span>
                        </div>
                    </div>
                    <button type="submit" data-toggle="tooltip" data-placement="top" title="Search" id="payments_report_btn" class="btn btn-custom"><i class="fa fa-search"></i></button>
                </div>
            </form>
            <div class="bars" style="height:100px">
                <?php
                if (!empty($yearly_overview)) {
                    $pay_max = 0;
                    foreach ($yearly_overview as $v_pay) {
                        $pay_max = max($pay_max, (float) $v_pay);
                    }
                    foreach ($yearly_overview as $v_pay) {
                        $pay_h = $pay_max > 0 ? max(6, round(((float) $v_pay / $pay_max) * 100)) : 6;
                        ?><i style="height:<?= $pay_h ?>%"></i><?php
                    }
                }
                ?>
            </div>
            <div class="xaxis">
                <?php for ($i = 1; $i <= 12; $i++) {
                    ?><span><?= date('M', strtotime($yearly . '-' . $i)) ?></span><?php
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