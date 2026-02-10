<?php if ($contract->contract_value != 0) { ?>
    <div class="clearfix">
        <p class="pull-left"><strong> <?php echo lang('contract_value'); ?> :</strong></p>
        <p class="pull-right mr"> <?php echo display_money($contract->contract_value); ?></p>
    </div>
<?php } ?>
    
    
    <div class="clearfix">
        <p class="pull-left"><strong> <?php echo lang('contract') . ' ' . lang('number'); ?> :</strong></p>
        <p class="pull-right mr"> <?php echo $contract->contract_id; ?></p>
    </div>
    
    <div class="clearfix">
        <p class="pull-left"><strong> <?php echo lang('start_date'); ?> :</strong></p>
        <p class="pull-right mr"> <?php echo display_date($contract->start_date); ?></p>
    </div>


<?php if (!empty($contract->end_date)) { ?>
    <div class="clearfix">
        <p class="pull-left"><strong> <?php echo lang('end_date'); ?> :</strong></p>
        <p class="pull-right mr"> <?php echo display_date($contract->end_date); ?></p>
    </div>
<?php } ?>

<?php if (!empty($contract->contract_type_name)) { ?>
    <div class="clearfix">
        <p class="pull-left"><strong> <?php echo lang('contract_type'); ?> :</strong></p>
        <p class="pull-right mr"> <?php echo $contract->contract_type_name; ?></p>
    </div>
<?php } ?>
<?php if (!empty($contract->client_name)) { ?>
    <div class="clearfix">
        <p class="pull-left"><strong> <?php echo lang('client'); ?> :</strong></p>
        <p class="pull-right mr"> <?php echo $contract->client_name; ?></p>
    </div>
<?php } ?>
<?php if (!empty($contract->project_name)) { ?>
    <div class="clearfix">
        <p class="pull-left"><strong> <?php echo lang('project_name'); ?> :</strong></p>
        <p class="pull-right mr"> <?php echo $contract->project_name; ?></p>
    </div>
<?php } ?>

<?php if ($contract->signed == 1) { ?>
    <div class="clearfix">
        <p class="pull-left"><strong><?= lang('signer') . ' ' . lang('name') ?> : </strong></p>
        <p class="pull-right mr">
            <?= $contract->acceptance_firstname ?>
        </p>
    </div>
    
    <div class="clearfix">
        <p class="pull-left"><strong><?= lang('signed') . ' ' . lang('date') ?> : </strong></p>
        <p class="pull-right mr">
            <?= display_datetime($contract->acceptance_date) ?>
        </p>
    </div>
    
    <div class="clearfix">
        <p class="pull-left"><strong><?= lang('ip_Address') ?> : </strong></p>
        <p class="pull-right mr">
            <?= $contract->acceptance_ip ?>
        </p>
    </div>
    
    <div class="clearfix">
        <p class="pull-left"><strong><?= lang('signature') ?> : </strong></p>
        <p class="pull-right mr">
            <img src="<?= base_url() ?>uploads/contracts_signatures/<?= $contract->signature ?>" class="img-responsive"
                 alt="">
        </p>
    </div>
<?php } ?>