<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><?= !empty($billing_info) ? 'Edit Billing Order' : 'Add New Billing Order' ?></h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('admin/server_management/add_billing') ?>" method="post" class="form-horizontal">
                    <input type="hidden" name="id" value="<?= !empty($billing_info->id) ? $billing_info->id : '' ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?= lang('billing_label') ?> <span class="text-danger">*</span></label>
                                <input type="text" name="label" class="form-control" value="<?= !empty($billing_info->label) ? $billing_info->label : '' ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?= lang('billing_type') ?> <span class="text-danger">*</span></label>
                                <select name="type" class="form-control" required>
                                    <option value="Domain" <?= (!empty($billing_info->type) && $billing_info->type == 'Domain') ? 'selected' : '' ?>>Domain</option>
                                    <option value="Hosting" <?= (!empty($billing_info->type) && $billing_info->type == 'Hosting') ? 'selected' : '' ?>>Hosting</option>
                                    <option value="SSL" <?= (!empty($billing_info->type) && $billing_info->type == 'SSL') ? 'selected' : '' ?>>SSL</option>
                                    <option value="Other" <?= (!empty($billing_info->type) && $billing_info->type == 'Other') ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?= lang('billing_value') ?> <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="value" class="form-control" value="<?= !empty($billing_info->value) ? $billing_info->value : '' ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?= lang('currency') ?></label>
                                <select name="currency" class="form-control">
                                    <?php foreach ($currencies as $currency): ?>
                                        <option value="<?= $currency['code'] ?>" <?= (!empty($billing_info->currency) && $billing_info->currency == $currency['code']) ? 'selected' : '' ?>><?= $currency['code'] ?> (<?= $currency['symbol'] ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?= lang('renewal_date') ?></label>
                                <div class="input-group">
                                    <input type="text" name="renewal_date" class="form-control datepicker" value="<?= !empty($billing_info->renewal_date) ? $billing_info->renewal_date : '' ?>">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?= lang('expiry_date') ?></label>
                                <div class="input-group">
                                    <input type="text" name="expiry_date" class="form-control datepicker" value="<?= !empty($billing_info->expiry_date) ? $billing_info->expiry_date : '' ?>">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('duration') ?></label>
                                <input type="number" name="duration" class="form-control" value="<?= !empty($billing_info->duration) ? $billing_info->duration : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('time_unit') ?></label>
                                <select name="time_unit" class="form-control">
                                    <option value="Days" <?= (!empty($billing_info->time_unit) && $billing_info->time_unit == 'Days') ? 'selected' : '' ?>>Days</option>
                                    <option value="Months" <?= (!empty($billing_info->time_unit) && $billing_info->time_unit == 'Months') ? 'selected' : '' ?>>Months</option>
                                    <option value="Years" <?= (!empty($billing_info->time_unit) && $billing_info->time_unit == 'Years') ? 'selected' : '' ?>>Years</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('renew') ?></label>
                                <select name="renew" class="form-control">
                                    <option value="Auto" <?= (!empty($billing_info->renew) && $billing_info->renew == 'Auto') ? 'selected' : '' ?>>Auto</option>
                                    <option value="Manual" <?= (!empty($billing_info->renew) && $billing_info->renew == 'Manual') ? 'selected' : '' ?>>Manual</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-light pl0">
                        <a href="<?= base_url('admin/server_management/billing') ?>" class="btn btn-default">Cancel</a>
                        <button type="submit" class="btn btn-danger">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
    });
</script>
