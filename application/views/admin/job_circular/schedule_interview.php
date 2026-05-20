<div class="panel panel-custom">
    <div class="panel-heading">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?= lang('schedule_interview') ?></h4>
    </div>
    <div class="modal-body wrap-modal wrap">
        <form id="interviewForm" onsubmit="saveInterviewForm(event)" class="form-horizontal">
            <input type="hidden" name="interview_id" id="interview_id" value="">

            <?php if (!empty($application)): ?>
                <input type="hidden" name="job_appliactions_id" id="job_appliactions_id" value="<?= $application->job_appliactions_id ?>">
                <input type="hidden" name="job_circular_id" id="job_circular_id" value="<?= $application->job_circular_id ?>">
                <div class="alert alert-info">
                    <strong><?= lang('candidate') ?>:</strong> <?= $application->name ?> (<?= $application->email ?>)<br>
                    <strong><?= lang('job_title') ?>:</strong> <?= $application->job_title ?>
                    <?php if (isset($application->ats_score)): ?>
                        <br><strong><?= lang('ats_score') ?>:</strong> <?= number_format($application->ats_score, 1) ?>%
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?= lang('select_application') ?> <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <select class="form-control" name="job_appliactions_id" id="job_appliactions_id" required>
                            <option value=""><?= lang('select') ?></option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?= lang('job_circular') ?> <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <select class="form-control" name="job_circular_id" id="job_circular_id" required>
                            <option value=""><?= lang('select') ?></option>
                            <?php foreach ($job_circulars as $jc): ?>
                                <option value="<?= $jc->job_circular_id ?>"><?= $jc->job_title ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('interview_type') ?> <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <select class="form-control" name="interview_type" id="interview_type" required onchange="toggleInterviewFields()">
                        <option value="online"><?= lang('online') ?></option>
                        <option value="face_to_face"><?= lang('face_to_face') ?></option>
                        <option value="phone"><?= lang('phone') ?></option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('interview_date') ?> <span class="text-danger">*</span></label>
                <div class="col-sm-5">
                    <div class="input-group">
                        <input type="text" class="form-control datepicker" name="interview_date" required>
                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('interview_time') ?> <span class="text-danger">*</span></label>
                <div class="col-sm-5">
                    <input type="time" class="form-control" name="interview_time" required>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('interviewer_name') ?></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" name="interviewer_name" value="<?= $this->session->userdata('name') ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('interviewer_email') ?></label>
                <div class="col-sm-8">
                    <input type="email" class="form-control" name="interviewer_email" value="<?= $this->session->userdata('email') ?>">
                </div>
            </div>

            <div class="form-group" id="meeting_link_group">
                <label class="col-sm-3 control-label"><?= lang('meeting_link') ?></label>
                <div class="col-sm-8">
                    <input type="url" class="form-control" name="meeting_link" placeholder="https://meet.jit.si/room-name or Zoom link">
                </div>
            </div>

            <div class="form-group" id="location_group" style="display:none;">
                <label class="col-sm-3 control-label"><?= lang('location_details') ?></label>
                <div class="col-sm-8">
                    <textarea class="form-control" name="location_details" rows="2" placeholder="Office address, floor, room number"></textarea>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('interview_notes') ?></label>
                <div class="col-sm-8">
                    <textarea class="form-control" name="interview_notes" rows="3"></textarea>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label"></label>
                <div class="col-sm-8">
                    <label>
                        <input type="checkbox" name="send_email" value="1" checked> <?= lang('send_interview_email') ?>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-3"></div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-primary btn-block"><?= lang('save') ?></button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function toggleInterviewFields() {
    var type = $('#interview_type').val();
    $('#meeting_link_group').toggle(type === 'online');
    $('#location_group').toggle(type === 'face_to_face');
}

function saveInterviewForm(e) {
    e.preventDefault();
    var formData = $('#interviewForm').serialize();
    $.ajax({
        url: '<?= base_url() ?>admin/job_circular/save_interview',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                location.reload();
            } else {
                alert('Error saving interview');
            }
        }
    });
}
</script>
