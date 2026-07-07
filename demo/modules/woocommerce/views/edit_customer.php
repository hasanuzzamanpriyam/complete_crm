<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/plugins/dropzone/dropzone.min.css">
<script type="text/javascript" src="<?= base_url() ?>assets/plugins/dropzone/dropzone.min.js"></script>
<script type="text/javascript" src="<?= base_url() ?>assets/plugins/dropzone/dropzone.custom.js"></script>

<div class="panel panel-custom">
    <div class="panel-heading">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                    class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><?= lang('update') . ' ' . lang('customers') ?></h4>
    </div>
    <div class="modal-body wrap-modal wrap">
        <form role="form" id="form" data-parsley-validate="" novalidate="" enctype="multipart/form-data"
              action="<?php echo base_url(); ?>woocommerce/update/customer/<?= (!empty($customers->id) ? $customers->id : ''); ?>"
              method="post" class="form-horizontal form-groups-bordered">
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label"><?= lang('username') ?> <span
                            class="required">*</span></label>
                <div class="col-sm-8">
                    <input type="hidden" class="form-control" name="custId" id="custId"
                           value="<?= (!empty($customers->woo_customer_id) ? $customers->woo_customer_id : ''); ?>"
                           readonly>
                    <input type="text" class="form-control" name="username" id="username" required
                           value="<?= (!empty($customers->username) ? $customers->username : ''); ?>" readonly>
                </div>
            </div>
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label"><?= lang('email') ?> <span class="required">*</span></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" name="email" id="email" required
                           value="<?= (!empty($customers->email) ? $customers->email : ''); ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label"><?= lang('first_name') ?> <span
                            class="required">*</span></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" required name="first_name" id="first_name"
                           value="<?= (!empty($customers->first_name) ? $customers->first_name : ''); ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label"><?= lang('last_name') ?> <span
                            class="required">*</span></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" required name="last_name" id="last_name"
                           value="<?= (!empty($customers->last_name) ? $customers->last_name : ''); ?>">
                </div>
            </div>
            
            
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-2">
                    <button type="submit" id="file-save-button"
                            class="btn btn-primary btn-block"><?= lang('update') ?></button>
                </div>
            </div>
        </form>
    </div>
</div>