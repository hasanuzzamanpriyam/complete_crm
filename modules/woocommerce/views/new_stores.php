<?php
if (!empty($stores)) {
    $userDetails = get_result('tbl_woocommerce_assigned', array('store_id' => $stores->store_id));
}
?>
<div class="panel panel-custom">
    <div class="panel-heading">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                    class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><?= lang('update') . ' ' . lang('stores') ?></h4>
    </div>
    <div class="modal-body wrap-modal wrap">
        <form role="form" id="form" data-parsley-validate="" novalidate="" enctype="multipart/form-data"
              action="<?php echo base_url(); ?>woocommerce/create_stores/<?= (!empty($stores->store_id) ? $stores->store_id : ''); ?>"
              method="post" class="form-horizontal form-groups-bordered">
            
            <div class="form-group">
                <label for="field-1" class="col-sm-4 control-label"><?= lang('store_name') ?> <span
                            class="required">*</span></label>
                
                <div class="col-sm-7">
                    <input type="text" name="store_name"
                           value="<?= (!empty($stores->store_name) ? $stores->store_name : ''); ?>" class="form-control"
                           required/>
                </div>
            </div>
            <div class="form-group">
                <label for="field-1" class="col-sm-4 control-label"><?= lang('woocommerce_website_url') ?> <span
                            class="required">*</span></label>
                
                <div class="col-sm-7">
                    <input type="text" name="url" value="<?= (!empty($stores->url) ? $stores->url : ''); ?>"
                           class="form-control" required/>
                </div>
            </div>
            <div class="form-group">
                <label for="field-1" class="col-sm-4 control-label"><?= lang('woocommerce_consumer_key') ?> <span
                            class="required">*</span></label>
                
                <div class="col-sm-7">
                    <input type="text" name="key" value="<?= (!empty($stores->key) ? $stores->key : ''); ?>"
                           class="form-control" required/>
                </div>
            </div>
            <div class="form-group">
                <label for="field-1" class="col-sm-4 control-label"><?= lang('woocommerce_consumer_secret') ?> <span
                            class="required">*</span></label>
                
                <div class="col-sm-7">
                    <input type="text" name="secret" value="<?= (!empty($stores->secret) ? $stores->secret : ''); ?>"
                           class="form-control" required/>
                </div>
            </div>
            <div class="form-group">
                <label for="field-1" class="col-sm-4 control-label"><?= lang('assignees') ?> <span
                            class="required">*</span></label>
                
                <div class="col-sm-7">
                    <select name="assignees[]" class="form-control selectpicker" data-width="100%" name="tax_rates_id[]"
                            multiple data-none-selected-text="<?= lang('select') . ' ' . lang('users') ?>" required>
                        <?php
                        $users = get_staff_details();
                        if (!empty($users)) {
                            foreach ($users as $user) { ?>
                                <option value="<?= $user->user_id ?>"
                                    <?php if (!empty($userDetails)) {
                                        foreach ($userDetails as $userDetail) {
                                            if ($userDetail->user_id == $user->user_id) {
                                                echo 'selected';
                                            }
                                        }
                                    } ?>
                                ><?= $user->fullname; ?></option>
                            <?php }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <!--hidden input values -->
            <div class="form-group">
                <div class="col-sm-offset-4 col-sm-2">
                    <button type="submit" id="file-save-button"
                            class="btn btn-primary btn-block"><?= lang('save') ?></button>
                </div>
            </div>
        </form>
    </div>
</div>
