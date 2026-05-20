<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<div class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title">
            <strong><?= lang('ats_applications') ?></strong>
            <?php if (!empty($job_circulars)): ?>
                <div class="pull-right">
                    <select class="form-control input-sm" onchange="window.location='<?= base_url() ?>admin/job_circular/ats_applications/' + this.value" style="width:250px;display:inline-block;">
                        <option value=""><?= lang('select_job_circular') ?></option>
                        <?php foreach ($job_circulars as $jc): ?>
                            <option value="<?= $jc->job_circular_id ?>" <?= $job_circular_id == $jc->job_circular_id ? 'selected' : '' ?>><?= $jc->job_title ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="panel-body">
        <?php if (!empty($applications)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?= lang('name') ?></th>
                            <th><?= lang('email') ?></th>
                            <th><?= lang('mobile') ?></th>
                            <th><?= lang('ats_score') ?></th>
                            <th><?= lang('matched_skills') ?></th>
                            <th><?= lang('status') ?></th>
                            <th><?= lang('apply_date') ?></th>
                            <th><?= lang('action') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($applications as $app): ?>
                            <?php
                            $ats_score = isset($app->ats_score) ? (float) $app->ats_score : 0;
                            $matched = !empty($app->matched_skills) ? json_decode($app->matched_skills, true) : [];
                            $missing = !empty($app->missing_skills) ? json_decode($app->missing_skills, true) : [];

                            if ($ats_score >= 80) $badge = 'success';
                            elseif ($ats_score >= 50) $badge = 'warning';
                            elseif ($ats_score > 0) $badge = 'danger';
                            else $badge = 'default';
                            ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><strong><?= $app->name ?></strong></td>
                                <td><?= $app->email ?></td>
                                <td><?= $app->mobile ?></td>
                                <td>
                                    <div class="progress" style="width:120px;margin-bottom:0;height:22px;">
                                        <div class="progress-bar progress-bar-<?= $badge ?>" style="width:<?= $ats_score ?>%;line-height:22px;font-size:12px;"><?= number_format($ats_score, 1) ?>%</div>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($matched)): ?>
                                        <?php foreach (array_slice($matched, 0, 3) as $m): ?>
                                            <span class="label label-success" style="margin:1px;"><?= $m ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($matched) > 3): ?>
                                            <span class="label label-default">+<?= count($matched) - 3 ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $status_map = [0 => ['label' => 'unread', 'class' => 'warning'], 1 => ['label' => 'approved', 'class' => 'success'], 2 => ['label' => 'primary_selected', 'class' => 'primary'], 3 => ['label' => 'call_for_interview', 'class' => 'purple'], 4 => ['label' => 'rejected', 'class' => 'danger']];
                                    $st = $status_map[$app->application_status] ?? ['label' => 'unread', 'class' => 'default'];
                                    ?>
                                    <span class="label label-<?= $st['class'] ?>"><?= lang($st['label']) ?></span>
                                </td>
                                <td><?= display_date($app->apply_date) ?></td>
                                <td>
                                    <a href="<?= base_url() ?>admin/job_circular/ats_score_detail/<?= $app->job_appliactions_id ?>" class="btn btn-info btn-xs" data-toggle="modal" data-target="#myModal" title="<?= lang('ats_score_detail') ?>"><i class="fa fa-bar-chart"></i></a>
                                    <a href="<?= base_url() ?>admin/job_circular/download_resume/<?= $app->job_appliactions_id ?>" class="btn btn-purple btn-xs" title="<?= lang('download_resume') ?>"><i class="fa fa-download"></i></a>
                                    <a href="<?= base_url() ?>admin/job_circular/schedule_interview/<?= $app->job_appliactions_id ?>" class="btn btn-success btn-xs" data-toggle="modal" data-target="#myModal_lg" title="<?= lang('schedule_interview') ?>"><i class="fa fa-calendar"></i></a>
                                    <a href="<?= base_url() ?>admin/job_circular/create_offer/<?= $app->job_appliactions_id ?>" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#myModal_lg" title="<?= lang('create_offer') ?>"><i class="fa fa-file-text-o"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info"><?= lang('no_applications_found') ?></div>
        <?php endif; ?>
    </div>
</div>
