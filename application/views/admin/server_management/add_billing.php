<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="<?= base_url('admin/server_management/add_billing') ?>" method="post" class="form-horizontal">
                    <input type="hidden" name="id" value="<?= !empty($billing_info->id) ? $billing_info->id : '' ?>">

                    <!-- COMMON DETAILS -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?= lang('subject') ?>/Title <span class="text-danger">*</span></label>
                                <input type="text" name="label" class="form-control" value="<?= !empty($billing_info->label) ? $billing_info->label : '' ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?= lang('related') ?> <span class="text-danger">*</span></label>
                                <select name="type" class="form-control select_box" style="width: 100%" required>
                                    <option value="Domain" <?= (!empty($billing_info->type) && $billing_info->type == 'Domain') ? 'selected' : '' ?>>Domain</option>
                                    <option value="Hosting" <?= (!empty($billing_info->type) && $billing_info->type == 'Hosting') ? 'selected' : '' ?>>Hosting</option>
                                    <option value="SSL" <?= (!empty($billing_info->type) && $billing_info->type == 'SSL') ? 'selected' : '' ?>>SSL</option>
                                    <option value="Other" <?= (!empty($billing_info->type) && $billing_info->type == 'Other') ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?= lang('provider') ?>*</label>
                                <select name="provider_id" class="form-control select_box" style="width: 100%">
                                    <option value=""><?= lang('select_provider') ?></option>
                                    <?php if (!empty($providers)) foreach ($providers as $provider): ?>
                                        <option value="<?= $provider['id'] ?>" <?= (!empty($billing_info->provider_id) && $billing_info->provider_id == $provider['id']) ? 'selected' : '' ?>><?= $provider['provider_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Flag*</label>
                                <select name="flag" class="form-control select_box" style="width: 100%">
                                    <option value="None" <?= (!empty($billing_info->flag) && $billing_info->flag == 'None') ? 'selected' : '' ?>>None</option>
                                    <option value="Flag 1" <?= (!empty($billing_info->flag) && $billing_info->flag == 'Flag 1') ? 'selected' : '' ?>>Flag 1</option>
                                    <option value="Flag 2" <?= (!empty($billing_info->flag) && $billing_info->flag == 'Flag 2') ? 'selected' : '' ?>>Flag 2</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- CONTACTS & ADDRESS DETAILS -->
                    <p class="text-muted text-uppercase mb-2" style="font-size: 11px; border-bottom: 1px solid #eee;">Contacts & Address Details</p>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Contact*</label>
                                <select name="contact_id" class="form-control select_box" style="width: 100%">
                                    <option value=""><?= lang('select_contact') ?></option>
                                    <?php if (!empty($staff_members)) foreach ($staff_members as $user): ?>
                                        <option value="<?= $user->user_id ?>" <?= (!empty($billing_info->contact_id) && $billing_info->contact_id == $user->user_id) ? 'selected' : '' ?>><?= $user->username ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Address</label>
                                <input type="text" name="address" class="form-control" value="<?= !empty($billing_info->address) ? $billing_info->address : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Contact Phone</label>
                                <input type="text" name="contact_phone" class="form-control" value="<?= !empty($billing_info->contact_phone) ? $billing_info->contact_phone : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Email*</label>
                                <input type="email" name="contact_email" class="form-control" value="<?= !empty($billing_info->contact_email) ? $billing_info->contact_email : '' ?>">
                            </div>
                        </div>
                    </div>

                    <!-- DATES & BILLING PERIODS -->
                    <p class="text-muted text-uppercase mb-2" style="font-size: 11px; border-bottom: 1px solid #eee;">Dates & Billing Periods</p>
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Registration Date*</label>
                                <div class="input-group">
                                    <input type="text" name="registration_date" class="form-control datepicker" value="<?= !empty($billing_info->registration_date) ? $billing_info->registration_date : '' ?>">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Buy Date*</label>
                                <div class="input-group">
                                    <input type="text" name="buy_date" class="form-control datepicker" value="<?= !empty($billing_info->buy_date) ? $billing_info->buy_date : '' ?>">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Duration*</label>
                                <div class="row">
                                    <div class="col-md-6 pr-0">
                                        <input type="number" name="duration" class="form-control" value="<?= !empty($billing_info->duration) ? $billing_info->duration : '' ?>">
                                    </div>
                                    <div class="col-md-6 pl-0">
                                        <select name="time_unit" class="form-control">
                                            <option value="Days" <?= (!empty($billing_info->time_unit) && $billing_info->time_unit == 'Days') ? 'selected' : '' ?>>Days</option>
                                            <option value="Months" <?= (!empty($billing_info->time_unit) && $billing_info->time_unit == 'Months') ? 'selected' : '' ?>>Months</option>
                                            <option value="Years" <?= (!empty($billing_info->time_unit) && $billing_info->time_unit == 'Years') ? 'selected' : '' ?>>Years</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Status*</label>
                                <select name="status" class="form-control select_box" style="width: 100%">
                                    <option value="Active" <?= (!empty($billing_info->status) && $billing_info->status == 'Active') ? 'selected' : '' ?>>Active</option>
                                    <option value="Pending" <?= (!empty($billing_info->status) && $billing_info->status == 'Pending') ? 'selected' : '' ?>>Pending</option>
                                    <option value="Expired" <?= (!empty($billing_info->status) && $billing_info->status == 'Expired') ? 'selected' : '' ?>>Expired</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Next Renew Date*</label>
                                <div class="input-group">
                                    <input type="text" name="renewal_date" class="form-control datepicker" value="<?= !empty($billing_info->renewal_date) ? $billing_info->renewal_date : '' ?>">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Price</label>
                                <div class="row">
                                    <div class="col-md-7 pr-0">
                                        <input type="number" step="0.01" name="value" class="form-control" value="<?= !empty($billing_info->value) ? $billing_info->value : '' ?>">
                                    </div>
                                    <div class="col-md-5 pl-0">
                                        <select name="currency" class="form-control">
                                            <?php foreach ($currencies as $currency): ?>
                                                <option value="<?= $currency['code'] ?>" <?= (!empty($billing_info->currency) && $billing_info->currency == $currency['code']) ? 'selected' : '' ?>><?= $currency['code'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BILLING DETAILS -->
                    <p class="text-muted text-uppercase mb-2" style="font-size: 11px; border-bottom: 1px solid #eee;">Billing Details</p>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Billing Cycle</label>
                                <select name="billing_cycle" class="form-control select_box" style="width: 100%">
                                    <option value="One Time" <?= (!empty($billing_info->billing_cycle) && $billing_info->billing_cycle == 'One Time') ? 'selected' : '' ?>>One Time</option>
                                    <option value="Recurring" <?= (!empty($billing_info->billing_cycle) && $billing_info->billing_cycle == 'Recurring') ? 'selected' : '' ?>>Recurring</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Last Billed Date</label>
                                <div class="input-group">
                                    <input type="text" name="last_billed_date" class="form-control datepicker" value="<?= !empty($billing_info->last_billed_date) ? $billing_info->last_billed_date : '' ?>">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Billing End Date</label>
                                <div class="input-group">
                                    <input type="text" name="billing_end_date" class="form-control datepicker" value="<?= !empty($billing_info->billing_end_date) ? $billing_info->billing_end_date : '' ?>">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Bill Status</label>
                                <select name="bill_status" class="form-control select_box" style="width: 100%">
                                    <option value="Billed" <?= (!empty($billing_info->bill_status) && $billing_info->bill_status == 'Billed') ? 'selected' : '' ?>>Billed</option>
                                    <option value="Unbilled" <?= (!empty($billing_info->bill_status) && $billing_info->bill_status == 'Unbilled') ? 'selected' : '' ?>>Unbilled</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- ASSIGNMENTS & RECURRING -->
                    <p class="text-muted text-uppercase mb-2" style="font-size: 11px; border-bottom: 1px solid #eee;">Assignments & Recurring</p>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Project</label>
                                <select name="project_id" class="form-control select_box" style="width: 100%">
                                    <option value=""><?= lang('select_project') ?></option>
                                    <?php if (!empty($projects)) foreach ($projects as $project): ?>
                                        <option value="<?= $project['project_id'] ?>" <?= (!empty($billing_info->project_id) && $billing_info->project_id == $project['project_id']) ? 'selected' : '' ?>><?= $project['project_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Client</label>
                                <select name="client_id" class="form-control select_box" style="width: 100%">
                                    <option value=""><?= lang('select_client') ?></option>
                                    <?php if (!empty($clients)) foreach ($clients as $client): ?>
                                        <option value="<?= $client['client_id'] ?>" <?= (!empty($billing_info->client_id) && $billing_info->client_id == $client['client_id']) ? 'selected' : '' ?>><?= $client['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Server Details</label>
                                <input type="text" name="server_details" class="form-control" value="<?= !empty($billing_info->server_details) ? $billing_info->server_details : '' ?>">
                            </div>
                        </div>
                    </div>

                    <!-- LOGIN DETAILS -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Manage</label>
                                <select name="manage" class="form-control">
                                    <option value="Internal" <?= (!empty($billing_info->manage) && $billing_info->manage == 'Internal') ? 'selected' : '' ?>>Internal</option>
                                    <option value="Client" <?= (!empty($billing_info->manage) && $billing_info->manage == 'Client') ? 'selected' : '' ?>>Client</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Login Details</label>
                                <input type="text" name="login_details" class="form-control" value="<?= !empty($billing_info->login_details) ? $billing_info->login_details : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Port</label>
                                <input type="text" name="port" class="form-control" value="<?= !empty($billing_info->port) ? $billing_info->port : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group pt-4">
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="secure_protocol" value="1" <?= (!empty($billing_info->secure_protocol) && $billing_info->secure_protocol == 1) ? 'checked' : '' ?>> Secure Protocol
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- NOTIFICATION SETTINGS -->
                    <p class="text-muted text-uppercase mb-2" style="font-size: 11px; border-bottom: 1px solid #eee;">Notification Settings</p>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="enable_expiry_notification" value="1" <?= (!empty($billing_info->enable_expiry_notification) && $billing_info->enable_expiry_notification == 1) ? 'checked' : '' ?>> Enable Expiry Notification
                                </label>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="enable_reminders_weekend" value="1" <?= (!empty($billing_info->enable_reminders_weekend) && $billing_info->enable_reminders_weekend == 1) ? 'checked' : '' ?>> Enable Reminders for Weekend
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- CUSTOM FIELDS -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Server Tags</label>
                                <input type="text" name="server_tags" class="form-control" value="<?= !empty($billing_info->server_tags) ? $billing_info->server_tags : '' ?>" placeholder="Tag1, Tag2">
                            </div>
                        </div>
                    </div>

                    <!-- DESCRIPTION -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="3"><?= !empty($billing_info->description) ? $billing_info->description : '' ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-light pl0">
                        <button type="submit" class="btn btn-success">SAVE</button>
                        <a href="<?= base_url('admin/server_management/billing') ?>" class="btn btn-default">Cancel</a>
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
        $('.select_box').select2();
    });
</script>