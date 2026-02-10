<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/plugins/dropzone/dropzone.min.css">
<script type="text/javascript" src="<?= base_url() ?>assets/plugins/dropzone/dropzone.min.js"></script>
<script type="text/javascript" src="<?= base_url() ?>assets/plugins/dropzone/dropzone.custom.js"></script>
<div class="panel panel-custom">
    <div class="panel-heading">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                    class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><?= lang('update') . ' ' . lang('products') ?></h4>
    </div>
    <div class="modal-body wrap-modal wrap">
        <form role="form" id="form" data-parsley-validate="" novalidate="" enctype="multipart/form-data"
              action="<?php echo base_url(); ?>woocommerce/update/product/" <?= (!empty($products->product_id) ? $products->product_id : ''); ?>
              method="post" class="form-horizontal form-groups-bordered">
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label"><?= lang('client') ?> <span
                            class="required">*</span></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" name="productId" id="productId"
                           value="<?= (!empty($products->product_id) ? $products->product_id : ''); ?>" readonly>
                </div>
            </div>
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label"><?= lang('name') ?> <span class="required">*</span></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" name="name" id="name" required
                           value="<?= (!empty($products->name) ? $products->name : ''); ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label"><?= lang('price') ?> <span class="required">*</span></label>
                <div class="col-sm-8">
                    <input type="number" class="form-control" name="regular_price" id="price" required
                           value="<?= (!empty($products->price) ? $products->price : ''); ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label"><?= lang('status') ?> <span
                            class="required">*</span></label>
                <div class="col-sm-8">
                    <select name="status" class="form-control select_box" style="width: 100%" required id="status">
                        <option value="publish" <?= $products->status == 'publish' ? 'selected' : '' ?>><?php echo lang('publish') ?></option>
                        <option value="draft" <?= $products->status == 'draft' ? 'selected' : '' ?>><?php echo lang('draft') ?></option>
                        <option value="pending" <?= $products->status == 'pending' ? 'selected' : '' ?>><?php echo lang('pending') ?></option>
                        <option value="private" <?= $products->status == 'private' ? 'selected' : '' ?>><?php echo lang('private') ?></option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label"><?= lang('description') ?> <span
                            class="required">*</span></label>
                
                <div class="col-sm-8">
                    <textarea name="short_description" id="short_description" class="form-control"
                              rows="4"><?= (!empty($products->short_description) ? $products->short_description : ''); ?></textarea>
                
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