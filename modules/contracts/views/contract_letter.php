<style>
    .panel-heading {
        padding-bottom: 20px;
    }
</style>
<div class="panel panel-custom">
    <div class="panel-heading">
        <h3 class="panel-title"><?= $contract->subject ?>
            
            <?php
            $access = true;
            if (!empty(client_id())) {
                $access = false;
                if ($contract->visible_to_client = 'Yes') {
                    $access = true;
                }
            }
            if ($access == true && $contract->signed == 0 && $contract->marked_as_signed == 0) { ?>
                <a data-toggle="modal" data-target="#myModal"
                   href="<?= base_url() ?>contracts/contract/signature/<?= $contract->contract_id ?>"
                   class="hidden-print btn  btn-danger pull-right ml"><?php echo lang('sign'); ?></a>
            <?php } else { ?>
                <div class="hidden-print btn btn-success pull-right ml mb"><?php echo lang('signed'); ?></div>
            <?php } ?>
            
            <a href="<?= base_url() ?>contracts/contract/pdf_contract/<?= $contract->contract_id ?>"
               class=" hidden-print btn btn-warning pull-right mb">
                <i class="fa fa-file-pdf-o"></i> <?php echo lang('download'); ?></a>
            
            <?php if (!empty(client_id())) { ?>
                <a href="<?= base_url() ?>contracts/contract/list"
                   class="hidden-print btn btn-info pull-right mr">
                    <?php echo lang('back_to_contracts'); ?></a>
            <?php } else { ?>
                <a href="<?= base_url() ?>contracts/contract"
                   class="hidden-print btn btn-info pull-right mr">
                    <?php echo lang('back_to_contracts'); ?></a>
            <?php } ?>
        
        
        </h3>
    
    </div>
    <div class="panel-body row form-horizontal task_details">
        <div class="col-sm-8">
            <?= $contract->description; ?>
        </div>
        <div class="col-sm-4 mt">
            <?php
            $this->load->view('contract_summary', $rata['contract'] = $contract);
            ?>
        </div>
    </div>
</div>