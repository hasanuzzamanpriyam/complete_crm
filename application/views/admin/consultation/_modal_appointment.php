<?php
$a = $appointment;
$labels = array(
    'pending' => 'info',
    'confirmed' => 'success',
    'completed' => 'primary',
    'cancelled' => 'danger',
    'no_show' => 'warning',
);
$type = isset($labels[$a->status]) ? $labels[$a->status] : 'default';
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title">
        <?= lang('consultation_view_appointment') ?> #<?= $a->appointment_id ?>
    </h4>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label><?= lang('consultation_customer_name') ?></label>
                <p class="form-control-static"><?= htmlspecialchars($a->customer_name) ?></p>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label><?= lang('consultation_customer_email') ?></label>
                <p class="form-control-static"><?= htmlspecialchars($a->customer_email) ?></p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label><?= lang('consultation_consultant') ?></label>
                <p class="form-control-static"><?= htmlspecialchars($a->consultant_name) ?></p>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label><?= lang('consultation_consultation_type') ?></label>
                <p class="form-control-static"><?= htmlspecialchars($a->consultation_type) ?></p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label><?= lang('consultation_appointment_date') ?></label>
                <p class="form-control-static"><?= consultation_format_date($a->appointment_date) ?></p>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label><?= lang('consultation_appointment_time') ?></label>
                <p class="form-control-static">
                    <?= consultation_format_time($a->appointment_time) ?>
                    (<?= htmlspecialchars($a->customer_timezone) ?>)
                </p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label><?= lang('consultation_duration') ?></label>
                <p class="form-control-static"><?= $a->duration_minutes ?> <?= lang('consultation_minutes') ?></p>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label><?= lang('consultation_status') ?></label>
                <p class="form-control-static">
                    <span class="label label-<?= $type ?>"><?= lang('consultation_status_' . $a->status) ?></span>
                </p>
            </div>
        </div>
    </div>
    <?php if (!empty($a->customer_phone)) : ?>
        <div class="form-group">
            <label><?= lang('consultation_customer_phone') ?></label>
            <p class="form-control-static"><?= htmlspecialchars($a->customer_phone) ?></p>
        </div>
    <?php endif; ?>
    <?php if (!empty($a->company)) : ?>
        <div class="form-group">
            <label><?= lang('consultation_company') ?></label>
            <p class="form-control-static"><?= htmlspecialchars($a->company) ?></p>
        </div>
    <?php endif; ?>
    <?php if (!empty($a->notes)) : ?>
        <div class="form-group">
            <label><?= lang('consultation_notes') ?></label>
            <p class="form-control-static"><?= nl2br(htmlspecialchars($a->notes)) ?></p>
        </div>
    <?php endif; ?>
    <?php if (!empty($a->meeting_url)) : ?>
        <div class="form-group">
            <label><?= lang('consultation_meeting_link') ?></label>
            <p class="form-control-static">
                <a href="<?= htmlspecialchars($a->meeting_url) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($a->meeting_room) ?></a>
            </p>
        </div>
    <?php endif; ?>
    <?php if (!empty($a->meeting_password)) : ?>
        <div class="form-group">
            <label><?= lang('consultation_meeting_password') ?></label>
            <p class="form-control-static"><?= htmlspecialchars($a->meeting_password) ?></p>
        </div>
    <?php endif; ?>
    <div class="form-group">
        <label><?= lang('consultation_created_at') ?></label>
        <p class="form-control-static"><?= display_datetime($a->created_at) ?></p>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close') ?></button>
</div>
