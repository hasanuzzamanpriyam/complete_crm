<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<div class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title">
            <strong><?= lang('offer_letters') ?></strong>
            <div class="pull-right hidden-print">
                <button class="btn btn-xs btn-warning" onclick="openBulkOfferModal()">
                    <i class="fa fa-file-text-o"></i> <?= lang('bulk_send_offers') ?>
                </button>
                <a href="<?= base_url() ?>admin/job_circular/create_offer" class="btn btn-xs btn-info" data-toggle="modal" data-target="#myModal_lg">
                    <i class="fa fa-plus"></i> <?= lang('create_offer') ?>
                </a>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php if (!empty($offers)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?= lang('candidate') ?></th>
                            <th><?= lang('job_title') ?></th>
                            <th><?= lang('salary_offered') ?></th>
                            <th><?= lang('joining_date') ?></th>
                            <th><?= lang('status') ?></th>
                            <th><?= lang('sent_at') ?></th>
                            <th><?= lang('action') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($offers as $offer): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><strong><?= $offer->candidate_name ?></strong><br><small class="text-muted"><?= $offer->candidate_email ?></small></td>
                                <td><?= $offer->job_title ?></td>
                                <td><?= $offer->salary_offered ?: '-' ?></td>
                                <td><?= $offer->joining_date ? strftime(config_item('date_format'), strtotime($offer->joining_date)) : '-' ?></td>
                                <td>
                                    <?php
                                    $status_map = ['draft' => 'default', 'sent' => 'info', 'accepted' => 'success', 'declined' => 'danger', 'expired' => 'warning'];
                                    $cls = $status_map[$offer->status] ?? 'default';
                                    ?>
                                    <span class="label label-<?= $cls ?>"><?= lang($offer->status) ?></span>
                                </td>
                                <td><?= $offer->sent_at ? display_datetime($offer->sent_at) : '-' ?></td>
                                <td>
                                    <a href="<?= base_url() ?>admin/job_circular/offer_detail/<?= $offer->offer_id ?>" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#myModal_lg" title="<?= lang('view') ?>"><i class="fa fa-eye"></i></a>
                                    <?php if ($offer->status == 'draft'): ?>
                                        <button onclick="sendOffer(<?= $offer->offer_id ?>)" class="btn btn-success btn-xs" title="<?= lang('send_offer') ?>"><i class="fa fa-envelope"></i></button>
                                    <?php endif; ?>
                                    <?php if ($offer->status == 'sent'): ?>
                                        <button onclick="updateOfferStatus(<?= $offer->offer_id ?>, 'accepted')" class="btn btn-success btn-xs" title="<?= lang('mark_accepted') ?>"><i class="fa fa-check"></i></button>
                                        <button onclick="updateOfferStatus(<?= $offer->offer_id ?>, 'declined')" class="btn btn-danger btn-xs" title="<?= lang('mark_declined') ?>"><i class="fa fa-times"></i></button>
                                    <?php endif; ?>
                                    <button onclick="deleteOffer(<?= $offer->offer_id ?>)" class="btn btn-default btn-xs" title="<?= lang('delete') ?>"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info"><?= lang('no_offers_found') ?></div>
        <?php endif; ?>
    </div>
</div>

<!-- Bulk Send Offers Modal -->
<div class="modal fade" id="bulkOfferModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-file-text-o"></i> <?= lang('bulk_send_offers') ?></h4>
            </div>
            <div class="modal-body" style="max-height:70vh;overflow-y:auto;">
                <form id="bulkOfferForm" class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?= lang('job_circular') ?> <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <select class="form-control" name="job_circular_id" id="bulk_offer_job_circular" onchange="loadBulkOfferApplicants()" required>
                                <option value=""><?= lang('select_job_circular') ?></option>
                                <?php foreach ($job_circulars as $jc): ?>
                                    <option value="<?= $jc->job_circular_id ?>"><?= $jc->job_title ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div id="bulk_offer_applicants_section" style="display:none;">
                        <div class="form-group">
                            <label class="col-sm-3 control-label"><?= lang('select_applicants') ?> <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <div class="row">
                                    <div class="col-xs-6">
                                        <button type="button" class="btn btn-default btn-xs" onclick="toggleAllBulkOfferApplicants(true)"><i class="fa fa-check-square"></i> <?= lang('select_all') ?></button>
                                        <button type="button" class="btn btn-default btn-xs" onclick="toggleAllBulkOfferApplicants(false)"><i class="fa fa-square-o"></i> <?= lang('deselect_all') ?></button>
                                    </div>
                                    <div class="col-xs-6 text-right">
                                        <span id="bulk_offer_selected_count" class="label label-info" style="font-size:12px;padding:4px 8px;">0 <?= lang('selected') ?></span>
                                    </div>
                                </div>
                                <div id="bulk_offer_applicants_container" style="margin-top:10px;max-height:200px;overflow-y:auto;border:1px solid #ddd;padding:5px;"></div>
                            </div>
                        </div>

                        <hr style="margin:15px 0;">

                        <div class="form-group">
                            <label class="col-sm-3 control-label"><?= lang('offer_template') ?></label>
                            <div class="col-sm-8">
                                <select class="form-control" name="template_id" id="bulk_offer_template" onchange="loadBulkOfferTemplate()">
                                    <option value=""><?= lang('select_template') ?></option>
                                    <?php foreach ($this->recruitment_model->get_offer_templates() as $tpl): ?>
                                        <option value="<?= $tpl->template_id ?>" <?= !empty($tpl->is_default) ? 'selected' : '' ?>><?= $tpl->template_name ?> <?= $tpl->is_default ? '(Default)' : '' ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label"><?= lang('salary_offered') ?></label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="salary_offered" placeholder="e.g., 50,000 BDT/month (optional)">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label"><?= lang('joining_date') ?> <span class="text-danger">*</span></label>
                            <div class="col-sm-5">
                                <div class="input-group">
                                    <input type="text" class="form-control datepicker" name="joining_date" required>
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label"><?= lang('additional_terms') ?></label>
                            <div class="col-sm-8">
                                <textarea class="form-control" name="additional_terms" rows="2" placeholder="e.g., Probation period: 3 months"></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label"></label>
                            <div class="col-sm-8">
                                <label><input type="checkbox" name="send_email" value="1" checked> <?= lang('send_offer_email') ?></label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close') ?></button>
                <button type="button" class="btn btn-primary" id="btn_bulk_offer_submit" onclick="submitBulkOffer()" disabled><i class="fa fa-paper-plane"></i> <?= lang('create_and_send_offers') ?></button>
            </div>
        </div>
    </div>
</div>

<script>
function sendOffer(id) {
    if (!confirm('<?= lang('confirm_send_offer') ?>')) return;
    $.ajax({
        url: '<?= base_url() ?>admin/job_circular/send_offer_email/' + id,
        type: 'POST',
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                location.reload();
            } else {
                alert('Error: ' + res.message);
            }
        }
    });
}

function updateOfferStatus(id, status) {
    $.ajax({
        url: '<?= base_url() ?>admin/job_circular/update_offer_status_ajax',
        type: 'POST',
        data: {offer_id: id, status: status},
        dataType: 'json',
        success: function(res) { if (res.success) location.reload(); }
    });
}

function deleteOffer(id) {
    if (!confirm('<?= lang('confirm_delete') ?>')) return;
    $.ajax({
        url: '<?= base_url() ?>admin/job_circular/delete_offer/' + id,
        type: 'POST',
        dataType: 'json',
        success: function(res) { if (res.success) location.reload(); }
    });
}

function openBulkOfferModal() {
    $('#bulkOfferForm')[0].reset();
    $('#bulk_offer_applicants_section').hide();
    $('#bulk_offer_applicants_container').html('');
    $('#bulk_offer_selected_count').text('0 <?= lang('selected') ?>');
    $('#btn_bulk_offer_submit').prop('disabled', true);
    $('#bulkOfferModal').modal('show');
}

function loadBulkOfferApplicants() {
    var jobCircularId = $('#bulk_offer_job_circular').val();
    if (!jobCircularId) {
        $('#bulk_offer_applicants_section').hide();
        $('#btn_bulk_offer_submit').prop('disabled', true);
        return;
    }

    $('#bulk_offer_applicants_container').html('<p class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</p>');
    $('#bulk_offer_applicants_section').show();

    $.ajax({
        url: '<?= base_url() ?>admin/job_circular/get_applications_for_offer/' + jobCircularId,
        dataType: 'json',
        success: function(res) {
            if (res.success && res.applications.length > 0) {
                var html = '';
                res.applications.forEach(function(app) {
                    html += '<label style="display:block;margin:4px 0;padding:5px;border-bottom:1px solid #eee;">';
                    html += '<input type="checkbox" class="bulk_offer_applicant_checkbox" value="' + app.job_appliactions_id + '" onchange="updateBulkOfferSelectedCount()"> ';
                    html += '<strong>' + app.name + '</strong> (' + app.email + ')';
                    html += ' <span class="label label-default" style="font-size:10px;">ATS: ' + (app.ats_score || 0) + '%</span>';
                    html += '</label>';
                });
                $('#bulk_offer_applicants_container').html(html);
            } else {
                $('#bulk_offer_applicants_container').html('<p class="text-muted"><?= lang('no_applicants_found') ?></p>');
            }
            updateBulkOfferSelectedCount();
        },
        error: function() {
            $('#bulk_offer_applicants_container').html('<p class="text-danger">Error loading applicants</p>');
        }
    });
}

function toggleAllBulkOfferApplicants(checked) {
    $('.bulk_offer_applicant_checkbox').prop('checked', checked);
    updateBulkOfferSelectedCount();
}

function updateBulkOfferSelectedCount() {
    var count = $('.bulk_offer_applicant_checkbox:checked').length;
    $('#bulk_offer_selected_count').text(count + ' <?= lang('selected') ?>');
    $('#btn_bulk_offer_submit').prop('disabled', count === 0);
}

function loadBulkOfferTemplate() {
    var templateId = $('#bulk_offer_template').val();
    if (!templateId) return;
    $.ajax({
        url: '<?= base_url() ?>admin/job_circular/get_offer_template_ajax/' + templateId,
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                // Template loaded - will be used when creating offers
            }
        }
    });
}

function submitBulkOffer() {
    var selectedIds = [];
    $('.bulk_offer_applicant_checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) {
        alert('Please select at least one applicant');
        return;
    }

    var formData = $('#bulkOfferForm').serialize() + '&selected_ids=' + selectedIds.join(',');
    var $btn = $('#btn_bulk_offer_submit');
    var originalText = $btn.html();
    $btn.html('<i class="fa fa-spinner fa-spin"></i> Creating Offers...').prop('disabled', true);

    $.ajax({
        url: '<?= base_url() ?>admin/job_circular/bulk_create_offers',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                alert(res.message);
                $('#bulkOfferModal').modal('hide');
                location.reload();
            } else {
                alert('Error: ' + res.message);
                $btn.html(originalText).prop('disabled', false);
            }
        },
        error: function() {
            alert('Error creating bulk offers');
            $btn.html(originalText).prop('disabled', false);
        }
    });
}
</script>
