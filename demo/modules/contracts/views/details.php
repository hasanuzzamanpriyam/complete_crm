<div class="panel panel-custom">
    <div class="panel-heading">
        <h3 class="panel-title"><?= $contract->subject ?>
            <div class="pull-right ml-sm ">
                
                <?php if ($contract->signed == 0 && $contract->marked_as_signed == 0) { ?>
                    <div class="hidden-print btn btn-xs  btn-danger pull-right ml"><?php echo lang('not_signed'); ?></div>
                <?php } else { ?>
                    <div class="hidden-print btn btn-xs btn-success pull-right ml"><?php echo lang('signed'); ?></div>
                <?php } ?>
            </div>
            
            <a href="<?= base_url('admin/contracts/send_email/' . $contract->contract_id) ?>" data-toggle="tooltip"
               data-placement="top" title="" class="btn btn-xs btn-primary pull-right" data-original-title="Send Email">
                <i class="fa fa-envelope-o"></i>
            </a>
            <a onclick="print_invoice('print_invoice')" href="#" data-toggle="tooltip" data-placement="top" title=""
               data-original-title="Print" class="mr-sm btn btn-xs btn-danger pull-right">
                <i class="fa fa-print"></i>
            </a>
            <a href="<?= base_url() ?>contracts/contract/pdf_contract/<?= $contract->contract_id ?>"
               data-toggle="tooltip" data-placement="top" title="" data-original-title="PDF"
               class="btn btn-xs btn-success pull-right mr-sm">
                <i class="fa fa-file-pdf-o"></i>
            </a>
            
            <a href="<?= base_url('admin/contracts/new_contract/' . $contract->contract_id) ?>" data-toggle="tooltip"
               data-placement="top" title="<?= lang('edit_contract') ?>"
               class="btn btn-xs btn-primary pull-right mr-sm"><i class="fa fa-pencil-square-o"></i></a>
        
        </h3>
    </div>
    <div class="panel-body row form-horizontal task_details" id="print_invoice">
        <div class="col-xs-8">
            <?= $contract->description; ?>
        </div>
        <div class="col-sm-4 mt">
            <?php
            $this->load->view('contract_summary', $rata['contract'] = $contract);
            ?>
        </div>
    </div>

</div>

<script type="text/javascript">
    function print_invoice(print_invoice) {
        var printContents = document.getElementById(print_invoice).innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
    }
</script>