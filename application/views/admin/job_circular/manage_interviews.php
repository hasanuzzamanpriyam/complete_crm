<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<div class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title">
            <strong><?= lang('interview_schedule') ?></strong>
            <div class="pull-right hidden-print">
                <a href="<?= base_url() ?>admin/job_circular/schedule_interview" class="btn btn-xs btn-info" data-toggle="modal" data-target="#myModal_lg">
                    <i class="fa fa-plus"></i> <?= lang('schedule_interview') ?>
                </a>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="row" style="margin-bottom:15px;">
            <div class="col-md-2">
                <select class="form-control input-sm" id="filter_status" onchange="filterInterviews()">
                    <option value=""><?= lang('all_status') ?></option>
                    <option value="scheduled"><?= lang('scheduled') ?></option>
                    <option value="completed"><?= lang('completed') ?></option>
                    <option value="cancelled"><?= lang('cancelled') ?></option>
                    <option value="no_show"><?= lang('no_show') ?></option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-control input-sm" id="filter_type" onchange="filterInterviews()">
                    <option value=""><?= lang('all_types') ?></option>
                    <option value="online"><?= lang('online') ?></option>
                    <option value="face_to_face"><?= lang('face_to_face') ?></option>
                    <option value="phone"><?= lang('phone') ?></option>
                </select>
            </div>
        </div>

        <div class="panel panel-default" style="margin-bottom:20px;">
            <div class="panel-heading"><strong><i class="fa fa-envelope"></i> <?= lang('bulk_schedule_interview') ?></strong></div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-4">
                        <select class="form-control" id="bulk_job_circular" onchange="loadApplicantsForBulk()">
                            <option value=""><?= lang('select_job_circular') ?></option>
                            <?php foreach ($job_circulars as $jc): ?>
                                <option value="<?= $jc->job_circular_id ?>"><?= $jc->job_title ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary btn-block" onclick="loadApplicantsForBulk()"><i class="fa fa-users"></i> <?= lang('load_applicants') ?></button>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-success btn-block" id="btn_bulk_send" onclick="openBulkModal()" disabled><i class="fa fa-envelope"></i> <?= lang('send_bulk_emails') ?></button>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-default btn-block" onclick="selectAllApplicants()"><i class="fa fa-check-square"></i> <?= lang('select_all') ?></button>
                    </div>
                    <div class="col-md-2">
                        <span id="selected_count" class="label label-info" style="font-size:14px;padding:6px 10px;">0 <?= lang('selected') ?></span>
                    </div>
                </div>
                <div id="applicants_table_container" style="margin-top:15px;"></div>
            </div>
        </div>

        <?php if (!empty($interviews)): ?>
            <div class="table-responsive">
                <h4><?= lang('scheduled_interviews') ?></h4>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?= lang('candidate') ?></th>
                            <th><?= lang('job_title') ?></th>
                            <th><?= lang('date') ?></th>
                            <th><?= lang('time') ?></th>
                            <th><?= lang('type') ?></th>
                            <th><?= lang('interviewer') ?></th>
                            <th><?= lang('status') ?></th>
                            <th><?= lang('action') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($interviews as $int): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><strong><?= $int->candidate_name ?></strong><br><small class="text-muted"><?= $int->candidate_email ?></small></td>
                                <td><?= $int->job_title ?></td>
                                <td><?= strftime(config_item('date_format'), strtotime($int->interview_date)) ?></td>
                                <td><?= date('g:i A', strtotime($int->interview_time)) ?></td>
                                <td>
                                    <?php
                                    $type_icons = ['online' => 'fa-video-camera', 'face_to_face' => 'fa-building', 'phone' => 'fa-phone'];
                                    $icon = $type_icons[$int->interview_type] ?? 'fa-calendar';
                                    ?>
                                    <i class="fa <?= $icon ?>"></i> <?= ucfirst(str_replace('_', ' ', $int->interview_type)) ?>
                                </td>
                                <td><?= $int->interviewer_name ?></td>
                                <td>
                                    <?php
                                    $status_map = ['scheduled' => 'info', 'completed' => 'success', 'cancelled' => 'danger', 'no_show' => 'warning', 'rescheduled' => 'default'];
                                    $cls = $status_map[$int->status] ?? 'default';
                                    ?>
                                    <span class="label label-<?= $cls ?>"><?= lang($int->status) ?></span>
                                </td>
                                <td>
                                    <a href="<?= base_url() ?>admin/job_circular/interview_detail/<?= $int->interview_id ?>" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#myModal_lg" title="<?= lang('view') ?>"><i class="fa fa-eye"></i></a>
                                    <?php if ($int->status == 'scheduled'): ?>
                                        <button onclick="updateInterviewStatus(<?= $int->interview_id ?>, 'completed')" class="btn btn-success btn-xs" title="<?= lang('mark_completed') ?>"><i class="fa fa-check"></i></button>
                                        <button onclick="updateInterviewStatus(<?= $int->interview_id ?>, 'cancelled')" class="btn btn-danger btn-xs" title="<?= lang('cancel') ?>"><i class="fa fa-times"></i></button>
                                    <?php endif; ?>
                                    <button onclick="deleteInterview(<?= $int->interview_id ?>)" class="btn btn-default btn-xs" title="<?= lang('delete') ?>"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info"><?= lang('no_interviews_found') ?></div>
        <?php endif; ?>
    </div>
</div>

<!-- Bulk Interview Modal -->
<div class="modal fade" id="bulkInterviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?= lang('bulk_schedule_interview') ?></h4>
            </div>
            <div class="modal-body">
                <form id="bulkInterviewForm" class="form-horizontal">
                    <input type="hidden" id="bulk_selected_ids" name="selected_ids" value="">
                    <input type="hidden" id="bulk_job_circular_id" name="job_circular_id" value="">
                    
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?= lang('interview_type') ?> <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <select class="form-control" name="interview_type" id="bulk_interview_type" required onchange="toggleBulkFields()">
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

                    <div class="form-group" id="bulk_meeting_link_group">
                        <label class="col-sm-3 control-label"><?= lang('meeting_link') ?></label>
                        <div class="col-sm-8">
                            <input type="url" class="form-control" name="meeting_link" placeholder="https://meet.jit.si/room-name or Zoom link">
                        </div>
                    </div>

                    <div class="form-group" id="bulk_location_group" style="display:none;">
                        <label class="col-sm-3 control-label"><?= lang('location_details') ?></label>
                        <div class="col-sm-8">
                            <textarea class="form-control" name="location_details" rows="2" placeholder="Office address, floor, room number"></textarea>
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

                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?= lang('interview_notes') ?></label>
                        <div class="col-sm-8">
                            <textarea class="form-control" name="interview_notes" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label"></label>
                        <div class="col-sm-8">
                            <label><input type="checkbox" name="send_email" value="1" checked> <?= lang('send_interview_email') ?></label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close') ?></button>
                <button type="button" class="btn btn-primary" id="btn_bulk_submit" onclick="submitBulkInterview()"><i class="fa fa-paper-plane"></i> <?= lang('schedule_and_send') ?></button>
            </div>
        </div>
    </div>
</div>

<script>
function updateInterviewStatus(id, status) {
    if (!confirm('<?= lang('confirm_status_change') ?>')) return;
    $.ajax({
        url: '<?= base_url() ?>admin/job_circular/update_interview_status',
        type: 'POST',
        data: {interview_id: id, status: status},
        dataType: 'json',
        success: function(res) { if (res.success) location.reload(); }
    });
}

function deleteInterview(id) {
    if (!confirm('<?= lang('confirm_delete') ?>')) return;
    $.ajax({
        url: '<?= base_url() ?>admin/job_circular/delete_interview/' + id,
        type: 'POST',
        dataType: 'json',
        success: function(res) { if (res.success) location.reload(); }
    });
}

function filterInterviews() {
    var status = $('#filter_status').val();
    var type = $('#filter_type').val();
    var url = '<?= base_url() ?>admin/job_circular/manage_interviews?';
    if (status) url += 'status=' + status + '&';
    if (type) url += 'interview_type=' + type;
    window.location = url;
}

function loadApplicantsForBulk() {
    var jobCircularId = $('#bulk_job_circular').val();
    if (!jobCircularId) {
        alert('<?= lang('select_job_circular') ?>');
        return;
    }

    $('#applicants_table_container').html('<p class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</p>');
    $('#btn_bulk_send').prop('disabled', true);
    $('#selected_count').text('0 <?= lang('selected') ?>');

    $.ajax({
        url: '<?= base_url() ?>admin/job_circular/get_applications_for_interview/' + jobCircularId,
        dataType: 'json',
        success: function(res) {
            if (res.success && res.applications.length > 0) {
                var html = '<table class="table table-striped table-hover"><thead><tr>';
                html += '<th><input type="checkbox" id="select_all_bulk" onchange="toggleSelectAllBulk(this)"></th>';
                html += '<th><?= lang('name') ?></th><th><?= lang('email') ?></th><th><?= lang('mobile') ?></th>';
                html += '<th><?= lang('ats_score') ?></th><th><?= lang('apply_on') ?></th>';
                html += '</tr></thead><tbody>';

                res.applications.forEach(function(app) {
                    html += '<tr>';
                    html += '<td><input type="checkbox" class="bulk_applicant_checkbox" value="' + app.job_appliactions_id + '" onchange="updateSelectedCount()"></td>';
                    html += '<td><strong>' + app.name + '</strong></td>';
                    html += '<td>' + app.email + '</td>';
                    html += '<td>' + (app.mobile || '-') + '</td>';
                    html += '<td>' + (app.ats_score ? app.ats_score + '%' : 'N/A') + '</td>';
                    html += '<td>' + (app.apply_date ? new Date(app.apply_date).toLocaleDateString() : '-') + '</td>';
                    html += '</tr>';
                });

                html += '</tbody></table>';
                $('#applicants_table_container').html(html);
            } else {
                $('#applicants_table_container').html('<div class="alert alert-info"><?= lang('no_applicants_found') ?></div>');
            }
        },
        error: function() {
            $('#applicants_table_container').html('<div class="alert alert-danger">Error loading applicants</div>');
        }
    });
}

function toggleSelectAllBulk(checkbox) {
    $('.bulk_applicant_checkbox').prop('checked', checkbox.checked);
    updateSelectedCount();
}

function selectAllApplicants() {
    var allChecked = $('.bulk_applicant_checkbox').length > 0 && $('.bulk_applicant_checkbox:checked').length === $('.bulk_applicant_checkbox').length;
    $('.bulk_applicant_checkbox').prop('checked', !allChecked);
    $('#select_all_bulk').prop('checked', !allChecked);
    updateSelectedCount();
}

function updateSelectedCount() {
    var count = $('.bulk_applicant_checkbox:checked').length;
    $('#selected_count').text(count + ' <?= lang('selected') ?>');
    $('#btn_bulk_send').prop('disabled', count === 0);
}

function openBulkModal() {
    var selectedIds = [];
    $('.bulk_applicant_checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) {
        alert('Please select at least one applicant');
        return;
    }

    $('#bulk_selected_ids').val(selectedIds.join(','));
    $('#bulk_job_circular_id').val($('#bulk_job_circular').val());
    $('#bulkInterviewModal').modal('show');
}

function toggleBulkFields() {
    var type = $('#bulk_interview_type').val();
    $('#bulk_meeting_link_group').toggle(type === 'online');
    $('#bulk_location_group').toggle(type === 'face_to_face');
}

function submitBulkInterview() {
    var formData = $('#bulkInterviewForm').serialize();
    var $btn = $('#btn_bulk_submit');
    var originalText = $btn.html();
    $btn.html('<i class="fa fa-spinner fa-spin"></i> Sending...').prop('disabled', true);

    $.ajax({
        url: '<?= base_url() ?>admin/job_circular/bulk_schedule_interview',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                alert(res.message);
                $('#bulkInterviewModal').modal('hide');
                location.reload();
            } else {
                alert('Error: ' + res.message);
                $btn.html(originalText).prop('disabled', false);
            }
        },
        error: function() {
            alert('Error sending bulk emails');
            $btn.html(originalText).prop('disabled', false);
        }
    });
}
</script>
