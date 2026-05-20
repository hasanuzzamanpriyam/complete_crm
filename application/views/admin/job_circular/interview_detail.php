<div class="panel panel-custom">
    <div class="panel-heading">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?= lang('interview_detail') ?></h4>
    </div>
    <div class="panel-body form-horizontal">
        <div class="col-md-12">
            <div class="col-sm-4 text-right"><label class="control-label"><strong><?= lang('candidate') ?> :</strong></label></div>
            <div class="col-sm-8"><p class="form-control-static"><?= $interview->candidate_name ?> (<?= $interview->candidate_email ?>)</p></div>
        </div>
        <div class="col-md-12">
            <div class="col-sm-4 text-right"><label class="control-label"><strong><?= lang('job_title') ?> :</strong></label></div>
            <div class="col-sm-8"><p class="form-control-static"><?= $interview->job_title ?></p></div>
        </div>
        <div class="col-md-12">
            <div class="col-sm-4 text-right"><label class="control-label"><strong><?= lang('interview_type') ?> :</strong></label></div>
            <div class="col-sm-8"><p class="form-control-static"><?= ucfirst(str_replace('_', ' ', $interview->interview_type)) ?></p></div>
        </div>
        <div class="col-md-12">
            <div class="col-sm-4 text-right"><label class="control-label"><strong><?= lang('date') ?> :</strong></label></div>
            <div class="col-sm-8"><p class="form-control-static"><?= strftime(config_item('date_format'), strtotime($interview->interview_date)) ?></p></div>
        </div>
        <div class="col-md-12">
            <div class="col-sm-4 text-right"><label class="control-label"><strong><?= lang('time') ?> :</strong></label></div>
            <div class="col-sm-8"><p class="form-control-static"><?= date('g:i A', strtotime($interview->interview_time)) ?></p></div>
        </div>
        <div class="col-md-12">
            <div class="col-sm-4 text-right"><label class="control-label"><strong><?= lang('interviewer') ?> :</strong></label></div>
            <div class="col-sm-8"><p class="form-control-static"><?= $interview->interviewer_name ?> (<?= $interview->interviewer_email ?>)</p></div>
        </div>
        <?php if (!empty($interview->meeting_link)): ?>
            <div class="col-md-12">
                <div class="col-sm-4 text-right"><label class="control-label"><strong><?= lang('meeting_link') ?> :</strong></label></div>
                <div class="col-sm-8"><p class="form-control-static"><a href="<?= $interview->meeting_link ?>" target="_blank"><?= $interview->meeting_link ?></a></p></div>
            </div>
        <?php endif; ?>
        <?php if (!empty($interview->location_details)): ?>
            <div class="col-md-12">
                <div class="col-sm-4 text-right"><label class="control-label"><strong><?= lang('location') ?> :</strong></label></div>
                <div class="col-sm-8"><p class="form-control-static"><?= $interview->location_details ?></p></div>
            </div>
        <?php endif; ?>
        <?php if (!empty($interview->interview_notes)): ?>
            <div class="col-md-12">
                <div class="col-sm-4 text-right"><label class="control-label"><strong><?= lang('notes') ?> :</strong></label></div>
                <div class="col-sm-8"><p class="form-control-static"><?= nl2br($interview->interview_notes) ?></p></div>
            </div>
        <?php endif; ?>
        <div class="col-md-12">
            <div class="col-sm-4 text-right"><label class="control-label"><strong><?= lang('status') ?> :</strong></label></div>
            <div class="col-sm-8">
                <p class="form-control-static">
                    <?php
                    $status_map = ['scheduled' => 'info', 'completed' => 'success', 'cancelled' => 'danger', 'no_show' => 'warning', 'rescheduled' => 'default'];
                    $cls = $status_map[$interview->status] ?? 'default';
                    ?>
                    <span class="label label-<?= $cls ?>"><?= lang($interview->status) ?></span>
                </p>
            </div>
        </div>
        <?php if (!empty($interview->feedback)): ?>
            <div class="col-md-12">
                <div class="col-sm-4 text-right"><label class="control-label"><strong><?= lang('feedback') ?> :</strong></label></div>
                <div class="col-sm-8"><p class="form-control-static"><?= nl2br($interview->feedback) ?></p></div>
            </div>
        <?php endif; ?>
        <?php if (!empty($interview->rating)): ?>
            <div class="col-md-12">
                <div class="col-sm-4 text-right"><label class="control-label"><strong><?= lang('rating') ?> :</strong></label></div>
                <div class="col-sm-8">
                    <p class="form-control-static">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fa fa-star <?= $i <= $interview->rating ? 'text-warning' : 'text-muted' ?>"></i>
                        <?php endfor; ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>
        <div class="col-md-12">
            <div class="col-sm-4 text-right"><label class="control-label"><strong><?= lang('email_sent') ?> :</strong></label></div>
            <div class="col-sm-8"><p class="form-control-static"><?= $interview->email_sent ? lang('yes') . ' (' . $interview->email_sent_at . ')' : lang('no') ?></p></div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-warning" onclick="resendInterviewEmail(<?= $interview->interview_id ?>)"><i class="fa fa-envelope"></i> <?= lang('send_interview') ?></button>
        <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close') ?></button>
    </div>
</div>

<script>
function resendInterviewEmail(interviewId) {
    if (!confirm('<?= lang('confirm_send_interview') ?>')) return;
    $.ajax({
        url: '<?= base_url() ?>admin/job_circular/resend_interview_email/' + interviewId,
        type: 'POST',
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                alert('<?= lang('email_sent') ?>: ' + res.message);
                location.reload();
            } else {
                alert('Error: ' + res.message);
            }
        },
        error: function() {
            alert('Failed to resend email');
        }
    });
}
</script>
