<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<div class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title">
            <strong><?= lang('offer_letters') ?></strong>
            <div class="pull-right hidden-print">
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

<script>
function sendOffer(id) {
    if (!confirm('<?= lang('confirm_send_offer') ?>')) return;
    $.ajax({
        url: '<?= base_url() ?>admin/job_circular/update_offer_status_ajax',
        type: 'POST',
        data: {offer_id: id, status: 'sent'},
        dataType: 'json',
        success: function(res) { if (res.success) location.reload(); }
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
</script>
