<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/plugins/dropzone/dropzone.min.css">
<script type="text/javascript" src="<?= base_url() ?>assets/plugins/dropzone/dropzone.min.js"></script>
<script type="text/javascript" src="<?= base_url() ?>assets/plugins/dropzone/dropzone.custom.js"></script>

<div class="panel panel-custom">
    <div class="panel-heading">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                    class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><?= lang('update') . ' ' . lang('order') ?></h4>
    </div>
    <div class="modal-body wrap-modal wrap">
        <form role="form" id="form" data-parsley-validate="" novalidate="" enctype="multipart/form-data"
              action="<?php echo base_url(); ?>woocommerce/update_orders" method="post"
              class="form-horizontal form-groups-bordered">
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label"><?= lang('order_id') ?> <span
                            class="required">*</span></label>
                <div class="col-sm-8">
                    <input type="hidden" class="form-control" name="store_id" id="store_id" value="" readonly>
                    <input type="number" class="form-control" name="orderId" id="orderId"
                           value="<?= (!empty($orders->order_id) ? $orders->order_id : ''); ?>" readonly>
                </div>
            </div>
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label"><?= lang('status_select_one') ?> <span
                            class="required">*</span></label>
                <div class="col-sm-8">
                    <select name="status" class="form-control select_box" style="width: 100%" required id="status">
                        <option value="pending" <?php if ($orders->status == 'pending') {
                            echo "selected";
                        } ?>><?php echo lang('pending') ?></option>
                        <option value="processing" <?php if ($orders->status == 'processing') {
                            echo "selected";
                        } ?>><?php echo lang('processing') ?></option>
                        <option value="on-hold" <?php if ($orders->status == 'on-hold') {
                            echo "selected";
                        } ?>><?php echo lang('on_hold') ?></option>
                        <option value="completed" <?php if ($orders->status == 'completed') {
                            echo "selected";
                        } ?>><?php echo lang('completed') ?></option>
                        <option value="cancelled" <?php if ($orders->status == 'cancelled') {
                            echo "selected";
                        } ?>><?php echo lang('cancelled') ?></option>
                        <option value="refunded" <?php if ($orders->status == 'refunded') {
                            echo "selected";
                        } ?>><?php echo lang('refunded') ?></option>
                        <option value="failed" <?php if ($orders->status == 'failed') {
                            echo "selected";
                        } ?>><?php echo lang('failed') ?></option>
                    </select>
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