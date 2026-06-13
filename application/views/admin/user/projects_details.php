<div id="panelChart4" class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title"><?= lang('total') . ' ' . lang('project') . ' ' . lang('time_spent') ?></div>
    </div>
    <div class="panel-body">
        <?php
        $project_info = $this->user_model->my_permission('tbl_project', $profile_info->user_id);
        $project_time = 0;
        $project_time = $this->user_model->my_spent_time($profile_info->user_id, true);
        if (!empty($project_info)) {
            foreach ($project_info as $v_projects) {
            }
        }
        echo $this->user_model->get_time_spent_result($project_time)

        ?>
    </div>
</div>
<div id="panelChart5" class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title"><?= lang('project') . ' ' . lang('report') ?></div>
    </div>
    <div class="panel-body">
        <div class="project-chart-pie flot-chart"></div>
    </div>
</div>            

<?php

$started = 0;
$in_progress = 0;
$cancel = 0;
$completed = 0;
if (!empty($project_info)):
    foreach ($project_info as $v_project) :
        if ($v_project->project_status == 'started') {
            $started += count(array($v_project->project_status));
        }
        if ($v_project->project_status == 'in_progress') {
            $in_progress += count(array($v_project->project_status));
        }
        if ($v_project->project_status == 'completed') {
            $completed += count(array($v_project->project_status));
        }
        if ($v_project->project_status == 'cancel') {
            $cancel += count(array($v_project->project_status));
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
<?php if (!empty($started) || !empty($in_progress) || !empty($completed) || !empty($cancel)) { ?>
    <script type="text/javascript">
        $(document).ready(function () {
            // CHART PIE
            // -----------------------------------
            (function (window, document, $, undefined) {

                $(function () {

                    var data = [{
                        "label": "<?= lang('started')?>",
                        "color": "#ff902b",
                        "data": <?= $started?>
                    }, {
                        "label": "<?= lang('in_progress')?>",
                        "color": "#5d9cec",
                        "data": <?= $in_progress?>
                    }, {
                        "label": "<?= lang('completed')?>",
                        "color": "#23b7e5",
                        "data": <?= $completed?>
                    }, {
                        "label": "<?= lang('cancel')?>",
                        "color": "#7266ba",
                        "data": <?= $cancel?>
                    }];

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

                    var chart = $('.project-chart-pie');
                    if (chart.length)
                        $.plot(chart, data, options);

                });

            })(window, document, window.jQuery);
            // CHART BAR STACKED
            // -----------------------------------


        });

    </script>
<?php } ?>

<div class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title"><strong><?= lang('all') . ' ' . lang('project') ?></strong></div>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-striped" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th><?= lang('project_name') ?></th>
                        <th><?= lang('end_date') ?></th>
                        <th><?= lang('status') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $this->load->model('items_model');
                    if (!empty($project_info)) : foreach ($project_info as $v_project) :
                        $progress = $this->items_model->get_project_progress($v_project->project_id);
                        ?>
                        <tr>
                            <td>
                                <a class="text-info" href="<?= base_url() ?>admin/projects/project_details/<?= $v_project->project_id ?>"><?= $v_project->project_name ?></a>
                                <?php if (strtotime(date('Y-m-d')) > strtotime($v_project->end_date) && $progress < 100) { ?>
                                    <span class="label label-danger pull-right"><?= lang('overdue') ?></span>
                                <?php } ?>
                                <div class="progress progress-xs progress-striped active" style="margin-top: 5px; margin-bottom: 0px;">
                                    <div class="progress-bar progress-bar-<?php echo ($progress >= 100) ? 'success' : 'primary'; ?>"
                                         data-toggle="tooltip" data-original-title="<?= $progress ?>%"
                                         style="width: <?= $progress; ?>%"></div>
                                </div>
                            </td>
                            <td><?= strftime(config_item('date_format'), strtotime($v_project->end_date)) ?></td>
                            <td>
                                <?php
                                if (!empty($v_project->project_status)) {
                                    if ($v_project->project_status == 'completed') {
                                        $label = 'success';
                                    } elseif ($v_project->project_status == 'in_progress') {
                                        $label = 'primary';
                                    } elseif ($v_project->project_status == 'cancel') {
                                        $label = 'danger';
                                    } else {
                                        $label = 'warning';
                                    }
                                    echo "<span class='label label-{$label}'>" . lang($v_project->project_status) . "</span>";
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="3" class="text-center"><?= lang('no_data') ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>