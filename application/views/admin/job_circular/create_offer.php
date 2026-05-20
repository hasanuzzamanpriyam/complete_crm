<div class="panel panel-custom">
    <div class="panel-heading">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?= lang('create_offer') ?></h4>
    </div>
    <div class="modal-body wrap-modal wrap" style="max-height:70vh;overflow-y:auto;">
        <form id="offerForm" onsubmit="saveOfferForm(event)" class="form-horizontal">
            <input type="hidden" name="offer_id" id="offer_id" value="">

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
                        <select class="form-control" name="job_appliactions_id" id="job_appliactions_id" required onchange="loadApplicationDetails()">
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
                <label class="col-sm-3 control-label"><?= lang('offer_template') ?></label>
                <div class="col-sm-8">
                    <select class="form-control" id="template_select" onchange="loadTemplate()">
                        <option value=""><?= lang('select_template') ?></option>
                        <?php foreach ($templates as $tpl): ?>
                            <option value="<?= $tpl->template_id ?>" <?= !empty($tpl->is_default) ? 'selected' : '' ?>><?= $tpl->template_name ?> <?= $tpl->is_default ? '(Default)' : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('offer_subject') ?> <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" name="offer_subject" id="offer_subject" value="<?= !empty($template_subject) ? $template_subject : '' ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('salary_offered') ?></label>
                <div class="col-sm-5">
                    <input type="text" class="form-control" name="salary_offered" id="salary_offered" placeholder="e.g., 50,000 BDT/month">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('joining_date') ?></label>
                <div class="col-sm-5">
                    <div class="input-group">
                        <input type="text" class="form-control datepicker" name="joining_date" id="joining_date">
                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('additional_terms') ?></label>
                <div class="col-sm-8">
                    <textarea class="form-control" name="additional_terms" id="additional_terms" rows="2" placeholder="e.g., Probation period: 3 months, Benefits: Health insurance"></textarea>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('offer_body') ?> <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <textarea class="form-control textarea_2" name="offer_body" id="offer_body" rows="12" required><?= !empty($template_body) ? $template_body : '' ?></textarea>
                    <small class="text-muted"><?= lang('offer_body_help') ?></small>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label"></label>
                <div class="col-sm-8">
                    <label><input type="checkbox" name="send_email" value="1" checked> <?= lang('send_offer_email') ?></label>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-3"></div>
                <div class="col-sm-5">
                    <button type="button" class="btn btn-warning" onclick="previewOffer()"><i class="fa fa-eye"></i> <?= lang('preview') ?></button>
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-primary btn-block"><?= lang('save') ?></button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?= lang('offer_preview') ?></h4>
            </div>
            <div class="modal-body" id="preview_content"></div>
        </div>
    </div>
</div>

<script>
function loadTemplate() {
    var templateId = $('#template_select').val();
    if (!templateId) return;
    $.ajax({
        url: '<?= base_url() ?>admin/job_circular/get_offer_template_ajax/' + templateId,
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                $('#offer_subject').val(res.subject);
                $('#offer_body').val(res.body);
            }
        }
    });
}

function previewOffer() {
    var formData = $('#offerForm').serialize();
    $.ajax({
        url: '<?= base_url() ?>admin/job_circular/preview_offer',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                $('#preview_content').html('<h5>' + res.subject + '</h5><hr>' + res.body);
                $('#previewModal').modal('show');
            }
        }
    });
}

function saveOfferForm(e) {
    e.preventDefault();
    var formData = $('#offerForm').serialize();
    $.ajax({
        url: '<?= base_url() ?>admin/job_circular/save_offer',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(res) {
            if (res.success) location.reload();
        }
    });
}
</script>
