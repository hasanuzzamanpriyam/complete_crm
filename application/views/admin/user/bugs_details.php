<div id="panelChart5" class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title"><?= lang('bugs') . ' ' . lang('report') ?></div>
    </div>
    <div class="panel-body">
        <div class="bar-chart-pie flot-chart"></div>
    </div>
</div>
<?php
$unconfirmed = 0;
$in_progress = 0;
$confirmed = 0;
$resolved = 0;
$verified = 0;

$bugs_info = $this->user_model->my_permission('tbl_bug', $profile_info->user_id);

if (!empty($bugs_info)):foreach ($bugs_info as $v_bugs):
    if ($v_bugs->bug_status == 'unconfirmed') {
        $unconfirmed += count($v_bugs->bug_status);
    }
    if ($v_bugs->bug_status == 'in_progress') {
        $in_progress += count($v_bugs->bug_status);
    }
    if ($v_bugs->bug_status == 'confirmed') {
        $confirmed += count($v_bugs->bug_status);
    }
    if ($v_bugs->bug_status == 'resolved') {
        $resolved += count($v_bugs->bug_status);
    }
    if ($v_bugs->bug_status == 'verified') {
        $verified += count($v_bugs->bug_status);
    }
endforeach;
endif;
?>
<script src="<?= base_url() ?>assets/plugins/Flot/jquery.flot.js"></script>
<script src="<?= base_url() ?>assets/plugins/Flot/jquery.flot.tooltip.min.js"></script>
<script src="<?= base_url() ?>assets/plugins/Flot/jquery.flot.resize.js"></script>
<script src="<?= base_url() ?>assets/plugins/Flot/jquery.flot.pie.js"></script>
<script src="<?= base_url() ?>assets/plugins/Flot/jquery.flot.time.js"></script>
<script src="<?= base_url() ?>assets/plugins/Flot/jquery.flot.categories.js"></script>
<script src="<?= base_url() ?>assets/plugins/Flot/jquery.flot.spline.min.js"></script>
<?php if (!empty($unconfirmed) || !empty($in_progress) || !empty($confirmed) || !empty($resolved) || !empty($verified)) {?>
    <script type="text/javascript">
        $(document).ready(function () {
            // CHART PIE
            // -----------------------------------
            (function (window, document, $, undefined) {

                $(function () {

                    var data = [{
                        "label": "<?= lang('unconfirmed')?>",
                        "color": "#ff902b",
                        "data": <?= $unconfirmed?>
                    }, {
                        "label": "<?= lang('in_progress')?>",
                        "color": "#5d9cec",
                        "data": <?= $in_progress?>
                    }, {
                        "label": "<?= lang('confirmed')?>",
                        "color": "#23b7e5",
                        "data": <?= $confirmed?>
                    }, {
                        "label": "<?= lang('resolved')?>",
                        "color": "#7266ba",
                        "data": <?= $resolved?>
                    }, {
                        "label": "<?= lang('verified')?>",
                        "color": "#27c24c",
                        "data": <?= $verified?>
                    },];

                    var options = {
                        series: {
                            pie: {
                                show: true,
                                innerRadius: 0,
                                label: {
                                    show: true,
                                    radius: 0.8,
                                    formatter: function (label, series) {
                                        return '<div class="flot-pie-label">' +
                                                //label + ' : ' +
                                            Math.round(series.percent) +
                                            '%</div>';
                                    },
                                    background: {
                                        opacity: 0.8,
                                        color: '#222'
                                    }
                                }
                            }
                        }
                    };

                    var chart = $('.bar-chart-pie');
                    if (chart.length)
                        $.plot(chart, data, options);

                });

            })(window, document, window.jQuery);
        });

    </script>
<?php } ?>

<div class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title"><strong><?= lang('all') . ' ' . lang('bugs') ?></strong></div>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-striped" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th><?= lang('bug_title') ?></th>
                        <th><?= lang('status') ?></th>
                        <th><?= lang('priority') ?></th>
                        <th><?= lang('reporter') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bugs_info)) : foreach ($bugs_info as $v_bug) :
                        $reporter = $this->db->where('user_id', $v_bug->reporter)->get('tbl_users')->row();
                        if (!empty($reporter->role_id) && $reporter->role_id == '1') {
                            $badge = 'danger';
                        } elseif (!empty($reporter->role_id) && $reporter->role_id == '2') {
                            $badge = 'info';
                        } else {
                            $badge = 'primary';
                        }
                        ?>
                        <tr>
                            <td>
                                <a class="text-info" style="<?php if ($v_bug->bug_status == 'resolve') { echo 'text-decoration: line-through;'; } ?>" href="<?= base_url() ?>admin/bugs/details/<?= $v_bug->bug_id ?>"><?= $v_bug->bug_title ?></a>
                            </td>
                            <td>
                                <?php
                                if ($v_bug->bug_status == 'unconfirmed') {
                                    $label = 'warning';
                                } elseif ($v_bug->bug_status == 'confirmed') {
                                    $label = 'info';
                                } elseif ($v_bug->bug_status == 'in_progress') {
                                    $label = 'primary';
                                } else {
                                    $label = 'success';
                                }
                                ?>
                                <span class="label label-<?= $label ?>"><?= lang($v_bug->bug_status) ?></span>
                            </td>
                            <td><?= ucfirst($v_bug->priority) ?></td>
                            <td>
                                <span class="badge btn-<?= $badge ?>"><?= fullname($v_bug->reporter) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="4" class="text-center"><?= lang('no_data') ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>