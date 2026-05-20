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

        <?php if (!empty($interviews)): ?>
            <div class="table-responsive">
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
</script>
