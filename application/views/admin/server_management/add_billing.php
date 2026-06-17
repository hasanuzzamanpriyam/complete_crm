<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<link rel="stylesheet" href="<?= base_url() ?>assets/plugins/select2/dist/css/select2.min.css">
<link rel="stylesheet" href="<?= base_url() ?>assets/plugins/select2/dist/css/select2-bootstrap.min.css">
<script src="<?= base_url() ?>assets/plugins/select2/dist/js/select2.min.js"></script>

<style>
    /* ERP Style Redesign */
    .erp-card { border: none; border-radius: 4px; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important; background-color: #fff; margin-bottom: 20px; }
    .erp-card .card-body { padding: 25px; }
    
    .erp-form label { font-size: 11px; font-weight: 600; color: #666; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.3px; display: block; }
    .erp-form .form-control { border-radius: 2px; border: 1px solid #d2d6de; font-size: 13px; height: 34px; box-shadow: none; padding: 6px 10px; color: #333; transition: border-color .15s ease-in-out; width: 100%;}
    .erp-form .form-control:focus { border-color: #3c8dbc; box-shadow: none; }
    .erp-form textarea.form-control { height: auto; }
    
    .erp-form .input-group { display: flex; flex-wrap: nowrap; align-items: stretch; width: 100%; }
    .erp-form .input-group > .form-control:not(select),
    .erp-form .input-group .select2-container { flex: 1 1 auto; width: 1% !important; margin-bottom: 0; }
    
    .erp-form .input-group > .form-control,
    .erp-form .input-group > select.form-control:not(.select2-hidden-accessible),
    .erp-form .input-group .select2-container--bootstrap .select2-selection { border-radius: 2px 0 0 2px; border-right: none; }

    .erp-form .input-group-append { display: flex; margin-left: 0; }
    
    .erp-form .input-group-append .btn.quick-add-btn {
        border-radius: 0 2px 2px 0;
        height: 34px;
        background-color: #f4f4f4;
        border: 1px solid #d2d6de;
        color: #555;
        padding: 4px 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
        transition: background-color 0.15s ease;
    }
    .erp-form .input-group-append .btn:hover { background-color: #e0e0e0; color: #333; }
    
    .erp-section-title { font-size: 12px; font-weight: bold; color: #333; border-bottom: 1px solid #eee; padding-bottom: 8px; margin-top: 25px; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.5px; }
    .erp-form .form-group { margin-bottom: 18px; }
    
    .erp-form .btn-success { background-color: #00a65a; border-color: #008d4c; font-size: 12px; font-weight: 600; padding: 8px 30px; border-radius: 2px; box-shadow: none; text-transform: uppercase; }
    .erp-form .btn-success:hover { background-color: #008d4c; }
    .erp-form .btn-cancel { font-size: 12px; font-weight: 500; padding: 8px 15px; color: #0073b7; background: transparent; border: none; text-decoration: none; margin-left: 10px; display: inline-block;}
    .erp-form .btn-cancel:hover { text-decoration: underline; color: #005384; }

    .select2-container--bootstrap .select2-selection { border-radius: 2px !important; border: 1px solid #d2d6de !important; height: 34px !important; }
    .input-group .select2-container--bootstrap .select2-selection { border-radius: 2px 0 0 2px !important; border-right: none !important; }

    /* Hide nested headers and footers in quick-add modal to prevent double buttons */
    #universalQuickAddModal .modal-body .modal-footer,
    #universalQuickAddModal .modal-body .modal-header,
    #universalQuickAddModal .modal-body .panel-heading,
    #universalQuickAddModal .modal-body .btn-bottom-toolbar,
    #universalQuickAddModal .modal-body .card-header,
    #universalQuickAddModal .modal-body .card-footer,
    #universalQuickAddModal .modal-body .nav-tabs {
        display: none !important;
    }
    #universalQuickAddModal .modal-body .panel-body,
    #universalQuickAddModal .modal-body .card-body,
    #universalQuickAddModal .modal-body .modal-body {
        padding: 10px 0 !important;
    }
    #universalQuickAddModal .modal-body .panel,
    #universalQuickAddModal .modal-body .card {
        border: none !important;
        box-shadow: none !important;
        margin-bottom: 0 !important;
    }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card erp-card shadow-sm">
            <div class="card-body">
                <form action="<?= base_url('admin/server_management/add_billing') ?>" method="post" class="erp-form" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= !empty($billing_info->id) ? $billing_info->id : '' ?>">

                    <div class="erp-section-title" style="margin-top: 0;">General Information</div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?= lang('subject') ?>/Title <span class="text-danger">*</span></label>
                                <input type="text" name="label" class="form-control" value="<?= !empty($billing_info->label) ? $billing_info->label : '' ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?= lang('related') ?> <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select name="type" class="form-control select_box" required>
                                        <option value="">Select Type</option>
                                        <?php if (!empty($billing_types)) foreach ($billing_types as $type): ?>
                                            <option value="<?= $type['name'] ?>" <?= (!empty($billing_info->type) && $billing_info->type == $type['name']) ? 'selected' : '' ?>><?= $type['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn quick-add-btn" data-type="billing_type" data-url="<?= base_url('admin/ajax_api/add_billing_type') ?>" tabindex="-1">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?= lang('provider') ?>*</label>
                                <div class="input-group">
                                    <select name="provider_id" class="form-control select_box">
                                        <option value=""><?= lang('select_provider') ?></option>
                                        <?php if (!empty($providers)) foreach ($providers as $provider): ?>
                                            <option value="<?= $provider['id'] ?>" <?= (!empty($billing_info->provider_id) && $billing_info->provider_id == $provider['id']) ? 'selected' : '' ?>><?= $provider['provider_name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn quick-add-btn" data-type="provider" data-url="<?= base_url('admin/server_management/add_provider') ?>" tabindex="-1">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Flag*</label>
                                <div class="input-group">
                                    <select name="flag" class="form-control select_box">
                                        <option value="None">None</option>
                                        <?php if (!empty($billing_flags)) foreach ($billing_flags as $flag): ?>
                                            <?php if ($flag['name'] == 'None') continue; ?>
                                            <option value="<?= $flag['name'] ?>" <?= (!empty($billing_info->flag) && $billing_info->flag == $flag['name']) ? 'selected' : '' ?>><?= $flag['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn quick-add-btn" data-type="billing_flag" data-url="<?= base_url('admin/ajax_api/add_billing_flag') ?>" tabindex="-1">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="erp-section-title">Contacts & Address</div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Contact Person*</label>
                                <select name="contact_id" class="form-control select_box">
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

                    <div class="erp-section-title">Dates & Periods</div>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Registration Date*</label>
                                <input type="date" name="registration_date" class="form-control" value="<?= !empty($billing_info->registration_date) ? $billing_info->registration_date : '' ?>">
                        </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Buy Date*</label>
                                <input type="date" name="buy_date" id="buy_date" class="form-control" value="<?= !empty($billing_info->buy_date) ? $billing_info->buy_date : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Duration*</label>
                                <div class="input-group">
                                    <input type="number" name="duration" id="duration" class="form-control" style="width: 40% !important; flex: none !important;" value="<?= !empty($billing_info->duration) ? $billing_info->duration : '' ?>">
                                    <select name="time_unit" id="time_unit" class="form-control" style="border-left: none;">
                                        <option value="Days" <?= (!empty($billing_info->time_unit) && $billing_info->time_unit == 'Days') ? 'selected' : '' ?>>Days</option>
                                        <option value="Months" <?= (!empty($billing_info->time_unit) && $billing_info->time_unit == 'Months') ? 'selected' : '' ?>>Months</option>
                                        <option value="Years" <?= (!empty($billing_info->time_unit) && $billing_info->time_unit == 'Years') ? 'selected' : '' ?>>Years</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Status*</label>
                                <div class="input-group">
                                    <select name="status" class="form-control select_box">
                                        <?php if (!empty($billing_status_list)) foreach ($billing_status_list as $status): ?>
                                            <option value="<?= $status['name'] ?>" <?= (!empty($billing_info->status) && $billing_info->status == $status['name']) ? 'selected' : '' ?>><?= $status['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn quick-add-btn" data-type="billing_status" data-url="<?= base_url('admin/ajax_api/add_billing_status') ?>" tabindex="-1">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Next Renew Date*</label>
                                <input type="date" name="renewal_date" id="renewal_date" class="form-control" value="<?= !empty($billing_info->renewal_date) ? $billing_info->renewal_date : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Price</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="value" class="form-control" style="width: 50% !important; flex: none !important;" value="<?= !empty($billing_info->value) ? $billing_info->value : '' ?>">
                                    <select name="currency" class="form-control" style="border-left: none;">
                                        <?php foreach ($currencies as $currency): ?>
                                            <option value="<?= $currency['code'] ?>" <?= (!empty($billing_info->currency) && $billing_info->currency == $currency['code']) ? 'selected' : '' ?>><?= $currency['code'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="erp-section-title">Billing Details</div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Billing Cycle</label>
                                <select name="billing_cycle" class="form-control select_box">
                                    <option value="One Time" <?= (!empty($billing_info->billing_cycle) && $billing_info->billing_cycle == 'One Time') ? 'selected' : '' ?>>One Time</option>
                                    <option value="Recurring" <?= (!empty($billing_info->billing_cycle) && $billing_info->billing_cycle == 'Recurring') ? 'selected' : '' ?>>Recurring</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Last Billed Date</label>
                                <input type="date" name="last_billed_date" class="form-control" value="<?= !empty($billing_info->last_billed_date) ? $billing_info->last_billed_date : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Billing End Date</label>
                                <input type="date" name="billing_end_date" class="form-control" value="<?= !empty($billing_info->billing_end_date) ? $billing_info->billing_end_date : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Bill Status</label>
                                <div class="input-group">
                                    <select name="bill_status" class="form-control select_box">
                                        <?php if (!empty($billing_bill_status_list)) foreach ($billing_bill_status_list as $bs): ?>
                                            <option value="<?= $bs['name'] ?>" <?= (!empty($billing_info->bill_status) && $billing_info->bill_status == $bs['name']) ? 'selected' : '' ?>><?= $bs['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn quick-add-btn" data-type="billing_bill_status" data-url="<?= base_url('admin/ajax_api/add_billing_bill_status') ?>" tabindex="-1">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="erp-section-title">Assignments & Infrastructure</div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Project</label>
                                <div class="input-group">
                                    <select name="project_id" class="form-control select_box">
                                        <option value=""><?= lang('select_project') ?></option>
                                        <?php if (!empty($projects)) foreach ($projects as $project): ?>
                                            <option value="<?= $project['project_id'] ?>" <?= (!empty($billing_info->project_id) && $billing_info->project_id == $project['project_id']) ? 'selected' : '' ?>><?= $project['project_name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn quick-add-btn" data-type="project" data-url="<?= base_url('admin/ajax_api/add_project') ?>" tabindex="-1">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Client</label>
                                <div class="input-group">
                                    <select name="client_id" class="form-control select_box">
                                        <option value=""><?= lang('select_client') ?></option>
                                        <?php if (!empty($clients)) foreach ($clients as $client): ?>
                                            <option value="<?= $client['client_id'] ?>" <?= (!empty($billing_info->client_id) && $billing_info->client_id == $client['client_id']) ? 'selected' : '' ?>><?= $client['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn quick-add-btn" data-type="client" data-url="<?= base_url('admin/ajax_api/add_client') ?>" tabindex="-1">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Store Name</label>
                                <div class="input-group">
                                    <select name="store_id" class="form-control select_box">
                                        <option value="">Select Store</option>
                                        <?php if (!empty($stores)) foreach ($stores as $store): ?>
                                            <option value="<?= $store['store_id'] ?>" <?= (!empty($billing_info->store_id) && $billing_info->store_id == $store['store_id']) ? 'selected' : '' ?>><?= $store['store_name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn quick-add-btn" data-type="store" data-url="<?= base_url('admin/ajax_api/add_store') ?>" tabindex="-1">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Manage</label>
                                <div class="input-group">
                                    <select name="manage" class="form-control select_box">
                                        <?php if (!empty($billing_manage_list)) foreach ($billing_manage_list as $m): ?>
                                            <option value="<?= $m['name'] ?>" <?= (!empty($billing_info->manage) && $billing_info->manage == $m['name']) ? 'selected' : '' ?>><?= $m['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn quick-add-btn" data-type="billing_manage" data-url="<?= base_url('admin/ajax_api/add_billing_manage') ?>" tabindex="-1">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Server Details</label>
                                <input type="text" name="server_details" class="form-control" value="<?= !empty($billing_info->server_details) ? $billing_info->server_details : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Login Details</label>
                                <textarea name="login_details" class="form-control" rows="2"><?= !empty($billing_info->login_details) ? $billing_info->login_details : '' ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label>Port</label>
                                <input type="text" name="port" class="form-control" value="<?= !empty($billing_info->port) ? $billing_info->port : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group pt-4">
                                <label class="checkbox-inline" style="text-transform: none; font-weight: normal;">
                                    <input type="checkbox" name="secure_protocol" value="1" <?= (!empty($billing_info->secure_protocol) && $billing_info->secure_protocol == 1) ? 'checked' : '' ?>> Secure
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="erp-section-title">Notification & Tags</div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="checkbox" style="margin-bottom: 10px;">
                                <label style="text-transform: none; font-weight: normal;">
                                    <input type="checkbox" name="enable_expiry_notification" value="1" <?= (!empty($billing_info->enable_expiry_notification) && $billing_info->enable_expiry_notification == 1) ? 'checked' : '' ?>> Enable Expiry Notification
                                </label>
                            </div>
                            <!-- RBAC Section -->
                            <div style="background: #fcfcfc; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; margin-top: 15px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                                    <h4 style="margin: 0; font-size: 15px; font-weight: 700; color: #333; text-transform: uppercase; letter-spacing: 0.5px;">
                                        <i class="fa fa-shield text-primary" style="margin-right: 8px;"></i> Record Access Permissions
                                    </h4>
                                    <div class="radio-inline p-0">
                                        <label class="radio-inline c-radio">
                                            <input type="radio" name="task_permission" value="everyone" <?= (empty($permissionL) || $permissionL == 'all') ? 'checked' : '' ?>>
                                            <span class="fa fa-circle"></span> Everyone
                                        </label>
                                        <label class="radio-inline c-radio">
                                            <input type="radio" name="task_permission" value="custom_permission" <?= (!empty($permissionL) && $permissionL != 'all') ? 'checked' : '' ?>>
                                            <span class="fa fa-circle"></span> Specific Users
                                        </label>
                                    </div>
                                </div>

                                <div id="task_permission_users" style="display: <?= (!empty($permissionL) && $permissionL != 'all') ? 'block' : 'none' ?>;">
                                    <div class="row">
                                        <?php if (!empty($staff_members)): ?>
                                            <?php foreach ($staff_members as $staff): ?>
                                                <div class="col-lg-6 col-md-6 mb-3">
                                                    <?php
                                                    $is_admin = ($staff->role_id == 1);
                                                    $user_permission = null;
                                                    if (!empty($permissionL) && $permissionL != 'all') {
                                                        $decoded_permission = json_decode($permissionL, true);
                                                        if (isset($decoded_permission[$staff->user_id])) {
                                                            $user_permission = $decoded_permission[$staff->user_id];
                                                        }
                                                    }
                                                    ?>
                                                    <div style="padding: 0; border: 1px solid #e9ecef; border-radius: 8px; background: #fff; height: 100%; transition: all 0.2s ease;">
                                                        <div class="checkbox c-checkbox m0">
                                                            <label class="needsclick" style="margin-bottom: 0; display: flex; align-items: center; width: 100%; cursor: pointer; padding: 12px;">
                                                                <input type="checkbox" value="<?= $staff->user_id ?>" name="assigned_to[]" class="needsclick assigned_to_task <?= $is_admin ? 'is-admin' : '' ?>" <?= !empty($user_permission) ? 'checked' : '' ?>>
                                                                <span class="fa fa-check" style="margin-top: -10px; left: 12px;"></span>
                                                                <div style="display: flex; align-items: center; margin-left: 25px; flex: 1; overflow: hidden;">
                                                                    <img src="<?= base_url(get_avatar_url($staff->avatar ?? null)) ?>" class="img-circle" style="width: 32px; height: 32px; border: 1px solid #eee; margin-right: 12px; flex-shrink: 0; object-fit: cover;">
                                                                    <div style="overflow: hidden; line-height: 1.3;">
                                                                        <div style="font-weight: 700; font-size: 13px; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($staff->username) ?></div>
                                                                        <div style="font-size: 11px; color: #888; font-weight: 500; text-transform: uppercase; letter-spacing: 0.3px;">
                                                                            <?= !empty($staff->designations) ? htmlspecialchars($staff->designations) : ($staff->role_id == 1 ? 'Admin' : 'Staff') ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="action_task_user mt-2" id="action_task_<?= $staff->user_id ?>" style="display: <?= !empty($user_permission) ? 'block' : 'none' ?>; padding-left: 28px;">
                                                        <label class="checkbox-inline c-checkbox">
                                                            <input type="checkbox" value="view" name="action_<?= $staff->user_id ?>[]" checked disabled>
                                                            <span class="fa fa-check"></span> View
                                                        </label>
                                                        <label class="checkbox-inline c-checkbox">
                                                            <input type="checkbox" value="edit" name="action_<?= $staff->user_id ?>[]" <?= ($is_admin || (!empty($user_permission) && in_array('edit', $user_permission))) ? 'checked' : '' ?> <?= $is_admin ? 'disabled' : '' ?>>
                                                            <span class="fa fa-check"></span> Edit
                                                        </label>
                                                        <label class="checkbox-inline c-checkbox">
                                                            <input type="checkbox" value="delete" name="action_<?= $staff->user_id ?>[]" <?= ($is_admin || (!empty($user_permission) && in_array('delete', $user_permission))) ? 'checked' : '' ?> <?= $is_admin ? 'disabled' : '' ?>>
                                                            <span class="fa fa-check"></span> Delete
                                                        </label>
                                                        <input type="hidden" name="action_<?= $staff->user_id ?>[]" value="view">
                                                        <?php if($is_admin): ?>
                                                            <input type="hidden" name="action_<?= $staff->user_id ?>[]" value="edit">
                                                            <input type="hidden" name="action_<?= $staff->user_id ?>[]" value="delete">
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Server Tags</label>
                                <input type="text" name="server_tags" class="form-control" value="<?= !empty($billing_info->server_tags) ? $billing_info->server_tags : '' ?>" placeholder="Tag1, Tag2">
                            </div>
                        </div>
                    </div>

                    <div class="erp-section-title">Additional Labels</div>
                    <div id="custom_fields_container">
                        <?php 
                        $custom_fields = !empty($billing_info->custom_fields) ? json_decode($billing_info->custom_fields, true) : [];
                        if (!empty($custom_fields)) {
                            foreach ($custom_fields as $index => $field) {
                        ?>
                            <div class="row custom-field-row" style="margin-bottom: 10px;">
                                <div class="col-md-3">
                                    <label>Label</label>
                                    <input type="text" name="custom_field_label[<?= $index ?>]" class="form-control" placeholder="Label" value="<?= $field['label'] ?>">
                                </div>
                                <div class="col-md-3">
                                    <label>Type</label>
                                    <select name="custom_field_type[<?= $index ?>]" class="form-control field-type-select">
                                        <option value="text" <?= (isset($field['type']) && $field['type'] == 'text') ? 'selected' : '' ?>>Text</option>
                                        <option value="password" <?= (isset($field['type']) && $field['type'] == 'password') ? 'selected' : '' ?>>Password</option>
                                        <option value="file" <?= (isset($field['type']) && $field['type'] == 'file') ? 'selected' : '' ?>>File</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label>Value</label>
                                    <input type="<?= (isset($field['type']) && $field['type'] != 'file') ? $field['type'] : 'text' ?>" name="custom_field_value[<?= $index ?>]" class="form-control field-value-input" placeholder="Value" value="<?= htmlspecialchars($field['value']) ?>">
                                    <input type="hidden" name="custom_field_existing_value[<?= $index ?>]" value="<?= htmlspecialchars($field['value']) ?>">
                                    <?php if (isset($field['type']) && $field['type'] == 'file' && !empty($field['value'])): ?>
                                        <small class="text-info d-block mt-1">Current file: <a href="<?= base_url($field['value']) ?>" target="_blank"><?= basename($field['value']) ?></a></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-2">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-danger btn-sm remove-custom-field" style="display: block;"><i class="fa fa-trash"></i></button>
                                </div>
                            </div>
                        <?php 
                            }
                        }
                        ?>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <button type="button" id="add_custom_field" class="btn btn-info btn-xs"><i class="fa fa-plus"></i> Add Label</button>
                        </div>
                    </div>

                    <div class="erp-section-title">Description</div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <textarea name="description" class="form-control" rows="3"><?= !empty($billing_info->description) ? $billing_info->description : '' ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4 mb-4">
                        <div class="col-md-12 text-left">
                            <button type="submit" class="btn btn-success">SAVE BILLING</button>
                            <a href="<?= base_url('admin/server_management/billing') ?>" class="btn-cancel">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Structure -->
<div class="modal fade" id="universalQuickAddModal" tabindex="-1" role="dialog" aria-labelledby="universalQuickAddModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content" style="border-radius: 4px;">
            <div class="modal-header" style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                <h5 class="modal-title" id="universalQuickAddModalLabel" style="font-size: 14px; font-weight: bold; color: #333; text-transform: uppercase;">Add New</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center" id="modalLoader">
                    <i class="fa fa-spinner fa-spin fa-2x"></i> Loading...
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #dee2e6; background-color: #f8f9fa;">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="font-size: 12px; font-weight: 600;">Close</button>
                <button type="button" class="btn btn-primary" id="universalModalSubmitBtn" style="font-size: 12px; font-weight: 600; background-color: #3c8dbc; border-color: #367fa9;">Save</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('.select_box').select2({
            theme: 'bootstrap',
            width: '100%'
        });

        // Quick Add Modal Logic
        var currentTargetSelect = null;
        var csrfHash = '<?= $this->security->get_csrf_hash() ?>';

        $('.quick-add-btn').on('click', function() {
            var url = $(this).data('url');
            currentTargetSelect = $(this).closest('.form-group').find('select');
            
            $('#modalLoader').show();
            $('#universalQuickAddModal .modal-body').html('<div class="text-center" id="modalLoader"><i class="fa fa-spinner fa-spin fa-2x"></i> Loading...</div>');
            $('#universalQuickAddModal').modal('show');

            $.get(url, function(data) {
                $('#universalQuickAddModal .modal-body').html(data);
            }).fail(function() {
                $('#universalQuickAddModal .modal-body').html('<div class="alert alert-danger">Error loading content. Please try again.</div>');
            });
        });

        $('#universalModalSubmitBtn').on('click', function() {
            var form = $('#universalQuickAddModal form');
            var url = form.attr('action');
            var formData = form.serialize();

            $.post(url, formData, function(response) {
                if (response.status === 'success') {
                    var newOption = new Option(response.text, response.id, true, true);
                    currentTargetSelect.append(newOption).trigger('change');
                    $('#universalQuickAddModal').modal('hide');
                } else {
                    alert(response.message || 'An error occurred');
                }
            }, 'json');
        });

        // Automatic Renewal Date Calculation
        function calculateRenewalDate() {
            var buyDateVal = $('#buy_date').val();
            var duration = parseInt($('#duration').val());
            var timeUnit = $('#time_unit').val();

            if (buyDateVal && !isNaN(duration)) {
                var buyDate = new Date(buyDateVal);
                var renewalDate = new Date(buyDateVal);

                if (timeUnit === 'Days') {
                    renewalDate.setDate(buyDate.getDate() + duration);
                } else if (timeUnit === 'Months') {
                    renewalDate.setMonth(buyDate.getMonth() + duration);
                } else if (timeUnit === 'Years') {
                    renewalDate.setFullYear(buyDate.getFullYear() + duration);
                }

                var yyyy = renewalDate.getFullYear();
                var mm = String(renewalDate.getMonth() + 1).padStart(2, '0');
                var dd = String(renewalDate.getDate()).padStart(2, '0');
                
                $('#renewal_date').val(yyyy + '-' + mm + '-' + dd);
            }
        }

        function calculateDuration() {
            var buyDateVal = $('#buy_date').val();
            var renewalDateVal = $('#renewal_date').val();

            if (buyDateVal && renewalDateVal) {
                var buyDate = new Date(buyDateVal);
                var renewalDate = new Date(renewalDateVal);
                
                if (renewalDate <= buyDate) return;

                // Try Years
                var years = renewalDate.getFullYear() - buyDate.getFullYear();
                var tempDate = new Date(buyDateVal);
                tempDate.setFullYear(buyDate.getFullYear() + years);
                if (tempDate.getTime() === renewalDate.getTime() && years > 0) {
                    $('#duration').val(years);
                    $('#time_unit').val('Years');
                    return;
                }

                // Try Months
                var months = (renewalDate.getFullYear() - buyDate.getFullYear()) * 12 + (renewalDate.getMonth() - buyDate.getMonth());
                tempDate = new Date(buyDateVal);
                tempDate.setMonth(buyDate.getMonth() + months);
                if (tempDate.getTime() === renewalDate.getTime() && months > 0) {
                    $('#duration').val(months);
                    $('#time_unit').val('Months');
                    return;
                }

                // Default to Days
                var diffTime = renewalDate - buyDate;
                var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                $('#duration').val(diffDays);
                $('#time_unit').val('Days');
            }
        }

        $('#buy_date, #duration, #time_unit').on('change input', function() {
            calculateRenewalDate();
        });

        $('#renewal_date').on('change input', function() {
            calculateDuration();
        });

        // RBAC Logic
        $('input[name="task_permission"]').change(function() {
            if ($(this).val() == 'custom_permission') {
                $('#task_permission_users').slideDown();
                $('.assigned_to_task.is-admin').prop('checked', true).trigger('change');
            } else {
                $('#task_permission_users').slideUp();
            }
        });

        $(document).on('change', '.assigned_to_task', function() {
            var user_id = $(this).val();
            if ($(this).is(':checked')) {
                $('#action_task_' + user_id).slideDown();
            } else {
                $('#action_task_' + user_id).slideUp();
            }
        });

        var customFieldCounter = <?= !empty($custom_fields) ? count($custom_fields) : 0 ?>;
        $('#add_custom_field').on('click', function() {
            var index = customFieldCounter++;
            var html = '<div class="row custom-field-row" style="margin-bottom: 10px;">' +
                       '    <div class="col-md-3">' +
                       '        <label>Label</label>' +
                       '        <input type="text" name="custom_field_label[' + index + ']" class="form-control" placeholder="Label">' +
                       '    </div>' +
                       '    <div class="col-md-3">' +
                       '        <label>Type</label>' +
                       '        <select name="custom_field_type[' + index + ']" class="form-control field-type-select">' +
                       '            <option value="text">Text</option>' +
                       '            <option value="password">Password</option>' +
                       '            <option value="file">File</option>' +
                       '        </select>' +
                       '    </div>' +
                       '    <div class="col-md-4">' +
                       '        <label>Value</label>' +
                       '        <input type="text" name="custom_field_value[' + index + ']" class="form-control field-value-input" placeholder="Value">' +
                       '        <input type="hidden" name="custom_field_existing_value[' + index + ']" value="">' +
                       '    </div>' +
                       '    <div class="col-md-2">' +
                       '        <label>&nbsp;</label>' +
                       '        <button type="button" class="btn btn-danger btn-sm remove-custom-field" style="display: block;"><i class="fa fa-trash"></i></button>' +
                       '    </div>' +
                       '</div>';
            $('#custom_fields_container').append(html);
        });

        $(document).on('change', '.field-type-select', function() {
            var type = $(this).val();
            var row = $(this).closest('.custom-field-row');
            var input = row.find('.field-value-input');
            input.attr('type', type);
            if (type === 'file') {
                input.val('');
            }
        });

        $(document).on('click', '.remove-custom-field', function() {
            $(this).closest('.custom-field-row').remove();
        });
    });
</script>