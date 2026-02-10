<style>
    .mtn {
        margin-top: -6px !important;
    }
</style>
<div class="panel panel-custom">
    <div class="panel-heading">
        <h3 class="panel-title"><?= $tickets_info->subject ?>
            <div class="pull-right ml-sm mtn">
                
                <?php
                if (empty($tickets_info->closer_id) || empty($tickets_info->resolver_id)) { ?>
                    <a data-toggle="modal" data-target="#myModal"
                       href="<?= base_url() ?>complaints/complaint/signature/<?= $tickets_info->tickets_id ?>"
                       class="hidden-print btn btn-xs btn-warning pull-right mt-sm"><?php echo lang('sign'); ?></a>
                <?php }
                if ($tickets_info->status == 'resolved') { ?>
                    <div class="hidden-print btn btn-xs  btn-success pull-right mt-sm mr-sm"><?php echo lang('resolved'); ?></div>
                <?php } elseif ($tickets_info->status == 'closed') { ?>
                    <div class="hidden-print btn btn-xs btn-danger pull-right mt-sm mr-sm"><?php echo lang('closed'); ?></div>
                <?php } ?>
            </div>
            <a onclick="print_invoice('print_invoice')" href="#" data-toggle="tooltip" data-placement="top" title=""
               data-original-title="Print" class="mr-sm btn btn-xs btn-danger pull-right">
                <i class="fa fa-print"></i>
            </a>
            
            <a href="<?= base_url('admin/complaints/new_complaint/' . $tickets_info->tickets_id) ?>"
               data-toggle="tooltip" data-placement="top" title="<?= lang('edit_complaint') ?>"
               class="btn btn-xs btn-primary pull-right mr-sm"><i class="fa fa-pencil-square-o"></i></a>
        
        </h3>
    </div>
    <div class="panel-body row form-horizontal task_details">
        <div class="col-xs-8">
            <?= $tickets_info->body; ?>
        </div>
        <div class="col-sm-4 mt">
            <?php
            $this->load->view('summary', $rata['tickets_info'] = $tickets_info);
            ?>
        </div>
    </div>

</div>