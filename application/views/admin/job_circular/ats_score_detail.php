<div class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title">
            <strong><?= lang('ats_score_detail') ?> - <?= $application->name ?></strong>
        </div>
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
    </div>
    <div class="panel-body">
        <?php
        $ats_score = isset($application->ats_score) ? (float) $application->ats_score : 0;
        if ($ats_score >= 80) $color = 'success';
        elseif ($ats_score >= 50) $color = 'warning';
        elseif ($ats_score > 0) $color = 'danger';
        else $color = 'default';
        ?>

        <div class="row">
            <div class="col-md-6">
                <h4><?= lang('ats_score') ?></h4>
                <div class="progress" style="height:30px;">
                    <div class="progress-bar progress-bar-<?= $color ?>" style="width:<?= $ats_score ?>%;line-height:30px;font-size:16px;font-weight:bold;"><?= number_format($ats_score, 1) ?>%</div>
                </div>
                <?php if ($ats_score >= 80): ?>
                    <p class="text-success"><i class="fa fa-check-circle"></i> <?= lang('excellent_match') ?></p>
                <?php elseif ($ats_score >= 50): ?>
                    <p class="text-warning"><i class="fa fa-exclamation-circle"></i> <?= lang('good_match') ?></p>
                <?php else: ?>
                    <p class="text-danger"><i class="fa fa-times-circle"></i> <?= lang('low_match') ?></p>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <h4><?= lang('application_info') ?></h4>
                <p><strong><?= lang('job_title') ?>:</strong> <?= $application->job_title ?></p>
                <p><strong><?= lang('email') ?>:</strong> <?= $application->email ?></p>
                <p><strong><?= lang('mobile') ?>:</strong> <?= $application->mobile ?></p>
                <p><strong><?= lang('apply_date') ?>:</strong> <?= display_date($application->apply_date) ?></p>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-6">
                <h4><i class="fa fa-check-circle text-success"></i> <?= lang('matched_skills') ?> (<?= count($matched_skills) ?>)</h4>
                <?php if (!empty($matched_skills)): ?>
                    <?php foreach ($matched_skills as $skill): ?>
                        <?php $detail = isset($skill_match_details[$skill]) ? $skill_match_details[$skill] : []; ?>
                        <span class="label label-success" style="margin:3px;padding:6px 12px;font-size:13px;">
                            <?= $skill ?>
                            <small style="opacity:0.8;">(<?= isset($detail['method']) ? $detail['method'] : 'match' ?>)</small>
                        </span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted"><?= lang('no_matched_skills') ?></p>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <h4><i class="fa fa-times-circle text-danger"></i> <?= lang('missing_skills') ?> (<?= count($missing_skills) ?>)</h4>
                <?php if (!empty($missing_skills)): ?>
                    <?php foreach ($missing_skills as $skill): ?>
                        <span class="label label-danger" style="margin:3px;padding:6px 12px;font-size:13px;"><?= $skill ?></span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-success"><i class="fa fa-check"></i> <?= lang('all_skills_matched') ?></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($application->resume_text)): ?>
            <hr>
            <h4><?= lang('extracted_resume_text') ?></h4>
            <div class="well" style="max-height:300px;overflow-y:auto;font-size:12px;">
                <?= nl2br(htmlspecialchars(substr($application->resume_text, 0, 3000))) ?>
                <?php if (strlen($application->resume_text) > 3000): ?>...<?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close') ?></button>
        <a href="<?= base_url() ?>admin/job_circular/reparse_resume/<?= $application->job_appliactions_id ?>" class="btn btn-warning" onclick="reparseResume(event, <?= $application->job_appliactions_id ?>)"><i class="fa fa-refresh"></i> <?= lang('reparse_resume') ?></a>
    </div>
</div>

<script>
function reparseResume(e, id) {
    e.preventDefault();
    $.ajax({
        url: '<?= base_url() ?>admin/job_circular/reparse_resume/' + id,
        type: 'POST',
        dataType: 'json',
        beforeSend: function() { $('#myModal .modal-footer').append('<span id="reparse_loading"> <i class="fa fa-spinner fa-spin"></i> Parsing...</span>'); },
        success: function(res) {
            $('#reparse_loading').remove();
            if (res.success) {
                location.reload();
            } else {
                alert(res.message);
            }
        }
    });
}
</script>
