<?php
$fo_year = !empty($finance_year) ? $finance_year : date('Y');
$fo_max = 0;
if (!empty($finance_overview_by_year)) {
    foreach ($finance_overview_by_year as $v_fo) {
        $fo_max = max($fo_max, (float) $v_fo['total_income']);
    }
}
?>
<div class="hero g1 menu">
    <div class="hero-top">
        <div>
            <div class="h-title"><?= lang('finance') ?> <?= lang('overview') ?></div>
            <div class="h-sub" style="margin-top:0"><?= $fo_year ?></div>
        </div>
        <form role="form" action="<?php echo base_url(); ?>admin/dashboard/finance_overview" method="post" class="form-horizontal form-groups-bordered">
            <input type="text" id="finance_overview_year" name="finance_overview" value="<?php echo $fo_year; ?>" class="form-control years">
            <button type="button" style="font-size: 13px" id="finance_overview_btn" data-toggle="tooltip" data-placement="top" title="Search" class="btn btn-custom"><i class="fa fa-search"></i></button>
        </form>
    </div>
    <div class="h-grid">
        <div>
            <div class="lbl"><?= lang('total_annual') ?> <?= lang('income') ?></div>
            <div class="val" style="font-size:22px"><?= display_money($total_annual['total_income'], default_currency()); ?></div>
        </div>
        <div>
            <div class="lbl"><?= lang('total_annual') ?> <?= lang('expense') ?></div>
            <div class="val" style="font-size:22px"><?= display_money($total_annual['total_expense'], default_currency()); ?></div>
        </div>
    </div>
    <div class="h-bars">
        <?php if (!empty($finance_overview_by_year)) {
            foreach ($finance_overview_by_year as $m_name => $v_fo) {
                $fo_h = $fo_max > 0 ? max(6, round(((float) $v_fo['total_income'] / $fo_max) * 100)) : 6;
                $fo_hi = ((float) $v_fo['total_income'] >= $fo_max && $fo_max > 0) ? ' hi' : '';
                ?><i class="<?= $fo_hi ?>" style="height:<?= $fo_h ?>%"></i><?php
            }
        } ?>
    </div>
    <div class="hero-foot">
        <span class="h-sub" style="margin:0">Jan - Dec <?= $fo_year ?></span>
        <span class="h-chip mint"><?= lang('profit') ?> <?= display_money($total_annual['total_profit'], default_currency()); ?></span>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        initdatepicker();
    });
</script>
