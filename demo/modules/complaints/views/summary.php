<div class="clearfix">
    <p class="pull-left"><strong> <?php echo lang('complaint') . ' ' . lang('code'); ?> :</strong></p>
    <p class="pull-right mr"> <?php echo $tickets_info->ticket_code; ?></p>
</div>

<div class="clearfix">
    <p class="pull-left"><strong> <?php echo lang('complaint') . ' ' . lang('date'); ?> :</strong></p>
    <p class="pull-right mr"> <?php echo display_date($tickets_info->lodged_date); ?></p>
</div>

<div class="clearfix">
    <p class="pull-left"><strong> <?php echo lang('due_date'); ?> :</strong></p>
    <p class="pull-right mr"> <?php echo display_date($tickets_info->due_date); ?></p>
</div>


<?php if (!empty($tickets_info->complaints_type_name)) { ?>
    <div class="clearfix">
        <p class="pull-left"><strong> <?php echo lang('complaint_type'); ?> :</strong></p>
        <p class="pull-right mr"> <?php echo $tickets_info->complaints_type_name; ?></p>
    </div>
<?php } ?>
<div class="clearfix">
    <p class="pull-left"><strong> <?php echo lang('client_name'); ?> :</strong></p>
    <p class="pull-right mr"> <?php echo $tickets_info->client_name; ?></p>
</div>

<?php if ($tickets_info->status == 'resolved' || !empty($tickets_info->resolver_id)) { ?>
    <div class="clearfix">
        <p class="pull-left"><strong><?= lang('resloved_by') ?> : </strong></p>
        <p class="pull-right mr">
            <?php if (!empty($tickets_info->resolver_id)) { ?>
                <?= fullname($tickets_info->resolver_id) ?>
            <?php } ?>
        </p>
    </div>
    
    <div class="clearfix">
        <p class="pull-left"><strong><?= lang('signature') ?> : </strong></p>
        <p class="pull-right mr">
            <?php if (!empty($tickets_info->resolver_signature)) { ?>
                <img src="<?= base_url() ?>uploads/complaints_signatures/<?= $tickets_info->resolver_signature ?>"
                     class="img-responsive" alt="">
            <?php } ?>
        </p>
    </div>
    
    
    <div class="clearfix">
        <p class="pull-left"><strong><?= lang('signature') . ' ' . lang('date') ?> : </strong></p>
        <p class="pull-right mr">
            <?php if (!empty($tickets_info->resolver_signature_date)) { ?>
                <?= display_datetime($tickets_info->resolver_signature_date) ?>
            <?php } ?>
        </p>
    </div>
<?php } ?>

<?php if ($tickets_info->status == 'closed' || !empty($tickets_info->closer_id)) { ?>
    <div class="clearfix">
        <p class="pull-left"><strong><?= lang('closed_by') ?> : </strong></p>
        <p class="pull-right mr">
            <?php if (!empty($tickets_info->closer_id)) { ?>
                <?= fullname($tickets_info->closer_id) ?>
            <?php } ?>
        </p>
    </div>
    
    <div class="clearfix">
        <p class="pull-left"><strong><?= lang('signature') ?> : </strong></p>
        <p class="pull-right mr">
            <?php if (!empty($tickets_info->closer_signature)) { ?>
                <img src="<?= base_url() ?>uploads/complaints_signatures/<?= $tickets_info->closer_signature ?>"
                     class="img-responsive" alt="">
            <?php } ?>
        </p>
    </div>
    
    
    <div class="clearfix">
        <p class="pull-left"><strong><?= lang('signature') . ' ' . lang('date') ?> : </strong></p>
        <p class="pull-right mr">
            <?php if (!empty($tickets_info->closer_signature_date)) { ?>
                <?= display_datetime($tickets_info->closer_signature_date) ?>
            <?php } ?>
        </p>
    </div>
<?php } ?>