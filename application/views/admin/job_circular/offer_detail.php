<div class="panel panel-custom">
    <div class="panel-heading">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?= lang('offer_detail') ?></h4>
    </div>
    <div class="panel-body form-horizontal">
        <div class="col-md-12">
            <div class="col-sm-4 text-right"><label class="control-label"><strong><?= lang('candidate') ?> :</strong></label></div>
            <div class="col-sm-8"><p class="form-control-static"><?= $offer->candidate_name ?> (<?= $offer->candidate_email ?>)</p></div>
        </div>
        <div class="col-md-12">
            <div class="col-sm-4 text-right"><label class="control-label"><strong><?= lang('job_title') ?> :</strong></label></div>
            <div class="col-sm-8"><p class="form-control-static"><?= $offer->job_title ?></p></div>
        </div>
        <div class="col-md-12">
            <div class="col-sm-4 text-right"><label class="control-label"><strong><?= lang('salary_offered') ?> :</strong></label></div>
            <div class="col-sm-8"><p class="form-control-static"><?= $offer->salary_offered ?: '-' ?></p></div>
        </div>
        <div class="col-md-12">
            <div class="col-sm-4 text-right"><label class="control-label"><strong><?= lang('joining_date') ?> :</strong></label></div>
            <div class="col-sm-8"><p class="form-control-static"><?= $offer->joining_date ? strftime(config_item('date_format'), strtotime($offer->joining_date)) : '-' ?></p></div>
        </div>
        <div class="col-md-12">
            <div class="col-sm-4 text-right"><label class="control-label"><strong><?= lang('status') ?> :</strong></label></div>
            <div class="col-sm-8">
                <p class="form-control-static">
                    <?php
                    $status_map = ['draft' => 'default', 'sent' => 'info', 'accepted' => 'success', 'declined' => 'danger', 'expired' => 'warning'];
                    $cls = $status_map[$offer->status] ?? 'default';
                    ?>
                    <span class="label label-<?= $cls ?>"><?= lang($offer->status) ?></span>
                </p>
            </div>
        </div>
        <?php if (!empty($offer->additional_terms)): ?>
            <div class="col-md-12">
                <div class="col-sm-4 text-right"><label class="control-label"><strong><?= lang('additional_terms') ?> :</strong></label></div>
                <div class="col-sm-8"><p class="form-control-static"><?= nl2br($offer->additional_terms) ?></p></div>
            </div>
        <?php endif; ?>
        <div class="col-md-12">
            <div class="col-sm-4 text-right"><label class="control-label"><strong><?= lang('offer_subject') ?> :</strong></label></div>
            <div class="col-sm-8"><p class="form-control-static"><?= $offer->offer_subject ?></p></div>
        </div>
        <div class="col-md-12">
            <div class="col-sm-4 text-right"><label class="control-label"><strong><?= lang('offer_body') ?> :</strong></label></div>
            <div class="col-sm-8"><div class="well" style="max-height:400px;overflow-y:auto;"><?= $offer->offer_body ?></div></div>
        </div>
        <div class="col-md-12">
            <div class="col-sm-4 text-right"><label class="control-label"><strong><?= lang('sent_at') ?> :</strong></label></div>
            <div class="col-sm-8"><p class="form-control-static"><?= $offer->sent_at ? $offer->sent_at : '-' ?></p></div>
        </div>
        <div class="col-md-12">
            <div class="col-sm-4 text-right"><label class="control-label"><strong><?= lang('responded_at') ?> :</strong></label></div>
            <div class="col-sm-8"><p class="form-control-static"><?= $offer->responded_at ? $offer->responded_at : '-' ?></p></div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close') ?></button>
    </div>
</div>
