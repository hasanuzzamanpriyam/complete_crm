<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<style>
    .datepicker {
        z-index: 1151 !important;
    }

    /* ERP Style Redesign */
    .erp-card {
        border: none;
        border-radius: 0;
        box-shadow: none !important;
        background-color: transparent;
    }

    .erp-card .card-body {
        padding: 0;
    }

    .erp-form label {
        font-size: 11px;
        font-weight: 600;
        color: #666;
        text-transform: uppercase;
        margin-bottom: 4px;
        letter-spacing: 0.3px;
    }

    .erp-form .form-control {
        border-radius: 2px;
        border: 1px solid #d2d6de;
        font-size: 13px;
        height: 34px;
        box-shadow: none;
        padding: 6px 10px;
        color: #333;
        transition: border-color .15s ease-in-out;
    }

    .erp-form .form-control:focus {
        border-color: #3c8dbc;
        box-shadow: none;
    }

    .erp-form textarea.form-control {
        height: auto;
    }

    /* Strict Input Group Attachments */
    .erp-form .input-group {
        display: flex;
        flex-wrap: nowrap;
        align-items: stretch;
        width: 100%;
    }

    .erp-form .input-group>.form-control:not(select),
    .erp-form .input-group .select2-container,
    .erp-form .input-group>select.form-control {
        flex: 1 1 auto;
        width: 1% !important;
        margin-bottom: 0;
    }

    .erp-form .input-group>.form-control,
    .erp-form .input-group>select.form-control:not(.select2-hidden-accessible),
    .erp-form .input-group .select2-container--bootstrap .select2-selection {
        border-radius: 2px 0 0 2px;
        border-right: none;
    }

    .erp-form .input-group-append {
        display: flex;
        margin-left: 0;
    }

    .erp-form .input-group-append .btn.quick-add-btn,
    .erp-form .input-group-append .btn.toggle-password {
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

    .erp-form .input-group-append .btn.toggle-password {
        background-color: #fff;
    }

    .erp-form .input-group-append .btn:hover {
        background-color: #e0e0e0;
        color: #333;
    }

    /* Price Input Specific Overrides */
    .erp-form .input-group-prepend .form-control {
        border-radius: 2px 0 0 2px;
        border-right: 1px solid #d2d6de;
        flex: 0 0 auto;
        width: auto !important;
        height: 34px;
        background-color: #f9f9f9;
    }

    .erp-form .price-group>input.form-control {
        border-radius: 0;
        border-left: none;
    }

    .erp-section-title {
        font-size: 12px;
        font-weight: bold;
        color: #333;
        border-bottom: 1px solid #eee;
        padding-bottom: 8px;
        margin-top: 30px;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .erp-form .form-group {
        margin-bottom: 15px;
    }

    /* Buttons */
    .erp-form .btn-success {
        background-color: #00a65a;
        border-color: #008d4c;
        font-size: 12px;
        font-weight: 600;
        padding: 6px 25px;
        border-radius: 2px;
        box-shadow: none;
        text-transform: uppercase;
    }

    .erp-form .btn-success:hover {
        background-color: #008d4c;
    }

    .erp-form .btn-cancel {
        font-size: 12px;
        font-weight: 500;
        padding: 6px 15px;
        color: #0073b7;
        background: transparent;
        border: none;
        text-decoration: none;
        margin-left: 10px;
    }

    .erp-form .btn-cancel:hover {
        text-decoration: underline;
        color: #005384;
    }

    /* Custom Fields specific */
    .custom-field-btn {
        font-size: 11px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 2px;
        background: #f4f4f4;
        border: 1px solid #ddd;
        color: #333;
        text-transform: uppercase;
    }

    .custom-field-btn:hover {
        background: #e4e4e4;
        color: #000;
    }

    .remove-custom-field {
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 2px;
    }

    /* Select2 Overrides */
    .select2-container--bootstrap .select2-selection {
        border: 1px solid #d2d6de;
        min-height: 34px;
        font-size: 13px;
        box-shadow: none;
    }

    .select2-container--bootstrap .select2-selection--single .select2-selection__rendered {
        padding-top: 4px;
        color: #333;
    }

    .select2-container--bootstrap .select2-selection--single .select2-selection__arrow {
        height: 32px;
    }

    .select2-container--bootstrap .select2-selection--multiple .select2-selection__choice {
        margin-top: 4px;
        font-size: 12px;
        background-color: #e4e4e4;
        border: 1px solid #ccc;
        color: #333;
    }

    .select2-container--bootstrap.select2-container--focus .select2-selection,
    .select2-container--bootstrap.select2-container--open .select2-selection {
        border-color: #3c8dbc;
        box-shadow: none;
    }

    /* Fix Select2 dropdown z-index for modals */
    .select2-container--open {
        z-index: 1060 !important;
    }

    .remove-custom-field {
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 2px;
        width: 5% !important;
    }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card erp-card">
            <div class="card-body">
                <form method="post" action="<?= base_url('admin/server_management/add_domain' . (!empty($domain_info) ? '/' . $domain_info->id : '')) ?>" class="erp-form">

                    <div class="erp-section-title" style="margin-top: 5px;">Domain Details</div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Domain Name <span class="text-danger">*</span></label>
                                <input type="text" name="domain_name" class="form-control" value="<?= !empty($domain_info) ? htmlspecialchars($domain_info->domain_name) : '' ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Provider <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select name="provider_id" id="provider_id" class="form-control" required>
                                        <option value="">Select Provider</option>
                                        <?php if (!empty($providers)): ?>
                                            <?php foreach ($providers as $provider): ?>
                                                <option value="<?= $provider['id'] ?>" <?= !empty($domain_info) && $domain_info->provider_id == $provider['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($provider['provider_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn quick-add-btn" data-type="provider" data-url="<?= base_url('admin/server_management/add_provider') ?>" title="Add New Provider" tabindex="-1">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Provider URL</label>
                                <input type="text" name="provider_url" id="provider_url" class="form-control" value="<?= !empty($domain_info) ? htmlspecialchars($domain_info->provider_url) : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Type <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select name="domain_type" id="domain_type_select" class="form-control" required>
                                        <option value="">Select Type</option>
                                        <?php if (!empty($domain_types)): ?>
                                            <?php foreach ($domain_types as $type): ?>
                                                <option value="<?= $type['domain_type'] ?>" <?= !empty($domain_info) && $domain_info->domain_type == $type['domain_type'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($type['domain_type']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn quick-add-btn" data-type="type" data-url="<?= base_url('admin/server_management/add_domain_type') ?>" title="Add New Type" tabindex="-1">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" name="date" class="form-control" value="<?= !empty($domain_info) ? $domain_info->date : '' ?>">
                            </div>
                        </div>
                    </div>

                    <div class="erp-section-title">Hosting & Credentials</div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Hosting</label>
                                <div class="input-group">
                                    <select name="hosting_id" id="hosting_id_select" class="form-control">
                                        <option value="">Select Hosting</option>
                                        <?php if (!empty($hostings)): ?>
                                            <?php foreach ($hostings as $hosting): ?>
                                                <option value="<?= $hosting['id'] ?>" <?= !empty($domain_info) && $domain_info->hosting_id == $hosting['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($hosting['hosting_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn quick-add-btn" data-type="hosting" data-url="<?= base_url('admin/server_management/add_hosting_type') ?>" title="Add New Hosting" tabindex="-1">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" value="<?= !empty($domain_info) ? htmlspecialchars($domain_info->username) : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" class="form-control" value="<?= !empty($domain_info) ? htmlspecialchars($domain_info->password) : '' ?>">
                                    <div class="input-group-append">
                                        <button class="btn toggle-password" type="button" tabindex="-1"><i class="fa fa-eye"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Status <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select name="status" id="domain_status_select" class="form-control" required>
                                        <option value="">Select Status</option>
                                        <?php if (!empty($domain_statuses)): ?>
                                            <?php foreach ($domain_statuses as $status): ?>
                                                <option value="<?= $status['status_name'] ?>" <?= !empty($domain_info) && $domain_info->status == $status['status_name'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($status['status_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn quick-add-btn" data-type="status" data-url="<?= base_url('admin/server_management/add_domain_status') ?>" title="Add New Status" tabindex="-1">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="erp-section-title">Billing & Service Terms</div>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Renewal Date <span class="text-danger">*</span></label>
                                <input type="date" name="purchase_date" id="purchase_date" class="form-control" value="<?= !empty($domain_info) ? $domain_info->purchase_date : '' ?>" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Expiry Date <span class="text-danger">*</span></label>
                                <input type="date" name="expiry_date" id="expiry_date" class="form-control" value="<?= !empty($domain_info) ? $domain_info->expiry_date : '' ?>" required readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Duration <span class="text-danger">*</span></label>
                                <input type="number" name="days" id="duration" class="form-control" value="<?= !empty($domain_info) ? $domain_info->days : '' ?>" placeholder="Enter duration" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Plan Mode <span class="text-danger">*</span></label>
                                <select name="time_unit" id="time_unit" class="form-control" required>
                                    <option value="Days" <?= !empty($domain_info) && $domain_info->time_unit == 'Days' ? 'selected' : '' ?>>Days</option>
                                    <option value="Weeks" <?= !empty($domain_info) && $domain_info->time_unit == 'Weeks' ? 'selected' : '' ?>>Weeks</option>
                                    <option value="Months" <?= !empty($domain_info) && $domain_info->time_unit == 'Months' ? 'selected' : '' ?>>Months</option>
                                    <option value="Years" <?= !empty($domain_info) && $domain_info->time_unit == 'Years' ? 'selected' : '' ?>>Years</option>
                                    <option value="Decade" <?= !empty($domain_info) && $domain_info->time_unit == 'Decade' ? 'selected' : '' ?>>Decade</option>
                                    <option value="Century" <?= !empty($domain_info) && $domain_info->time_unit == 'Century' ? 'selected' : '' ?>>Century</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Price</label>
                                <div class="input-group price-group">
                                    <div class="input-group-prepend">
                                        <select name="currency_id" class="form-control" id="currency_id">
                                            <option value="">Currency</option>
                                            <?php if (!empty($currencies)): ?>
                                                <?php foreach ($currencies as $currency): ?>
                                                    <option value="<?= $currency['code'] ?>" <?= !empty($domain_info) && $domain_info->currency_id == $currency['code'] ? 'selected' : '' ?>>
                                                        <?= $currency['code'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <input type="number" name="price" id="price_input" step="0.01" class="form-control" value="<?= !empty($domain_info) ? $domain_info->price : '' ?>">
                                </div>
                                <small class="text-muted d-block mt-1" id="conversion_result" style="font-size: 10px;"></small>
                            </div>
                        </div>
                    </div>

                    <div class="erp-section-title">Registrar Details</div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Registrar URL</label>
                                <input type="text" name="registrar_url" class="form-control" value="<?= !empty($domain_info) ? htmlspecialchars($domain_info->registrar_url) : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Registrar Username</label>
                                <input type="text" name="registrar_username" class="form-control" value="<?= !empty($domain_info) ? htmlspecialchars($domain_info->registrar_username) : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Registrar Password</label>
                                <div class="input-group">
                                    <input type="password" name="registrar_password" class="form-control" value="<?= !empty($domain_info) ? htmlspecialchars($domain_info->registrar_password) : '' ?>">
                                    <div class="input-group-append">
                                        <button class="btn toggle-password" type="button" tabindex="-1"><i class="fa fa-eye"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Registrar Status</label>
                                <select name="registrar_status" class="form-control">
                                    <option value="">Select Status</option>
                                    <option value="Active" <?= !empty($domain_info) && $domain_info->registrar_status == 'Active' ? 'selected' : '' ?>>Active</option>
                                    <option value="Pending" <?= !empty($domain_info) && $domain_info->registrar_status == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Expired" <?= !empty($domain_info) && $domain_info->registrar_status == 'Expired' ? 'selected' : '' ?>>Expired</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="erp-section-title">Assignments & Settings</div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Project</label>
                                <div class="input-group">
                                    <select name="project_id[]" id="project_id_select" class="form-control select_box" multiple="multiple" data-placeholder="Select Projects">
                                        <?php 
                                        $selected_projects = !empty($domain_info->project_id) ? explode(',', $domain_info->project_id) : array();
                                        if (!empty($projects)): 
                                            foreach ($projects as $project): 
                                        ?>
                                                <option value="<?= $project['project_id'] ?>" <?= in_array($project['project_id'], $selected_projects) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($project['project_name']) ?>
                                                </option>
                                        <?php 
                                            endforeach; 
                                        endif; 
                                        ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn quick-add-btn" data-type="project" data-url="<?= base_url('admin/projects/create') ?>" title="Add New Project" tabindex="-1">
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
                                    <select name="client_id[]" id="client_id_select" class="form-control select_box" multiple="multiple" data-placeholder="Select Clients">
                                        <?php 
                                        $selected_clients = !empty($domain_info->client_id) ? explode(',', $domain_info->client_id) : array();
                                        if (!empty($clients)): 
                                            foreach ($clients as $client): 
                                        ?>
                                                <option value="<?= $client['client_id'] ?>" <?= in_array($client['client_id'], $selected_clients) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($client['name']) ?>
                                                </option>
                                        <?php 
                                            endforeach; 
                                        endif; 
                                        ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn quick-add-btn" data-type="client" data-url="<?= base_url('admin/client/create_client') ?>" title="Add New Client" tabindex="-1">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Name Servers</label>
                                <div class="input-group">
                                    <select name="nameservers[]" class="form-control select_box" multiple="multiple" style="width: 100%">
                                        <?php
                                        $selected_nameservers = !empty($domain_info->nameservers) ? explode(',', $domain_info->nameservers) : array();
                                        if (!empty($nameservers)) {
                                            foreach ($nameservers as $ns) {
                                        ?>
                                                <option value="<?= $ns['name'] ?>" <?= in_array($ns['name'], $selected_nameservers) ? 'selected' : '' ?>><?= $ns['name'] ?></option>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn quick-add-btn" data-type="nameserver" data-url="<?= base_url('admin/server_management/add_nameserver') ?>" title="Add New Nameserver" tabindex="-1">
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
                                <label>Auto Renewal</label>
                                <?php $auto_renewal = !empty($domain_info) ? $domain_info->auto_renewal : 0; ?>
                                <select name="auto_renewal" class="form-control" id="auto_renewal">
                                    <option value="0" <?= $auto_renewal == 0 ? 'selected' : '' ?>>Manual</option>
                                    <option value="1" <?= $auto_renewal == 1 ? 'selected' : '' ?>>Auto</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Lock Domain</label>
                                <?php $is_locked = !empty($domain_info) ? $domain_info->is_locked : 0; ?>
                                <select name="is_locked" class="form-control" id="is_locked">
                                    <option value="0" <?= $is_locked == 0 ? 'selected' : '' ?>>Unlocked</option>
                                    <option value="1" <?= $is_locked == 1 ? 'selected' : '' ?>>Locked</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>For Sale</label>
                                <?php $is_for_sale = !empty($domain_info) ? $domain_info->is_for_sale : 0; ?>
                                <select name="is_for_sale" class="form-control" id="is_for_sale">
                                    <option value="0" <?= $is_for_sale == 0 ? 'selected' : '' ?>>No</option>
                                    <option value="1" <?= $is_for_sale == 1 ? 'selected' : '' ?>>Yes</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox" style="margin-top: 30px;">
                                    <input type="checkbox" name="whois_protection" class="custom-control-input" id="whois_protection" value="1" <?= !empty($domain_info) && $domain_info->whois_protection == 1 ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="whois_protection" style="text-transform: none; font-size: 13px;">WHOIS Protection</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="erp-section-title">Notification Settings</div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox" style="margin-top: 30px;">
                                    <input type="checkbox" name="expiry_notification" class="custom-control-input" id="expiry_notification" value="1" <?= !empty($domain_info) && $domain_info->expiry_notification == 1 ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="expiry_notification" style="text-transform: none; font-size: 13px;">Enable Expiry Notification</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4" id="notification_days_wrapper">
                            <div class="form-group">
                                <label>Notification Days Before</label>
                                <input type="number" name="notification_days" class="form-control" value="<?= !empty($domain_info) ? $domain_info->notification_days : 7 ?>">
                            </div>
                        </div>
                        <div class="col-md-5" id="notification_unit_wrapper">
                            <div class="form-group">
                                <label>Notification Time Unit</label>
                                <select name="notification_time_unit" class="form-control">
                                    <option value="Days" <?= !empty($domain_info) && $domain_info->notification_time_unit == 'Days' ? 'selected' : '' ?>>Days</option>
                                    <option value="Weeks" <?= !empty($domain_info) && $domain_info->notification_time_unit == 'Weeks' ? 'selected' : '' ?>>Weeks</option>
                                    <option value="Months" <?= !empty($domain_info) && $domain_info->notification_time_unit == 'Months' ? 'selected' : '' ?>>Months</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-end mt-4 mb-3" style="border-bottom: 1px solid #eee; padding-bottom: 8px;">
                        <div class="erp-section-title" style="border-bottom: none; margin: 0; padding: 0;">Custom Fields</div>
                        <button type="button" class="btn custom-field-btn" id="add_custom_field" tabindex="-1">
                            <i class="fa fa-plus"></i> Add Field
                        </button>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div id="custom_fields_container">
                                <?php
                                $custom_fields = !empty($domain_info->custom_fields) ? json_decode($domain_info->custom_fields, true) : array();
                                if (!empty($custom_fields)):
                                    foreach ($custom_fields as $field):
                                ?>
                                        <div class="row custom-field-row mb-2">
                                            <div class="col-md-5">
                                                <input type="text" name="custom_field_label[]" class="form-control" placeholder="Label" value="<?= htmlspecialchars($field['label']) ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" name="custom_field_value[]" class="form-control" placeholder="Value" value="<?= htmlspecialchars($field['value']) ?>">
                                            </div>
                                            <div class="col-md-1">
                                                <button type="button" class="btn btn-danger remove-custom-field w-100" tabindex="-1"><i class="fa fa-trash"></i></button>
                                            </div>
                                        </div>
                                <?php
                                    endforeach;
                                endif;
                                ?>
                            </div>
                            <div id="no_custom_fields_msg" style="display: <?= !empty($custom_fields) ? 'none' : 'block' ?>;">
                                <p class="text-muted text-center mb-0" style="font-size: 12px; font-style: italic;">No custom fields added. Click "Add Field" to add one.</p>
                            </div>
                        </div>
                    </div>

                    <div class="erp-section-title">Description</div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <textarea name="description" class="form-control" rows="3" placeholder="Enter description..."><?= !empty($domain_info) ? htmlspecialchars($domain_info->description) : '' ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4 mb-4">
                        <div class="col-md-12 text-left">
                            <button type="submit" class="btn btn-success">Save</button>
                            <a href="<?= base_url('admin/server_management/domain') ?>" class="btn-cancel">Cancel</a>
                        </div>
                    </div>

                    <script>
                        $(document).ready(function() {
                            var csrfToken = '<?= $this->security->get_csrf_hash() ?>';

                            // Initialize Select2
                            if ($.fn.select2) {
                                $('.select_box').select2({
                                    theme: 'bootstrap',
                                    width: '100%',
                                    placeholder: function() {
                                        return $(this).data('placeholder');
                                    },
                                    allowClear: true
                                });
                            }

                            // Toggle notification fields based on expiry_notification checkbox
                            function toggleNotificationFields() {
                                if ($('#expiry_notification').is(':checked')) {
                                    $('#notification_days_wrapper').show();
                                    $('#notification_unit_wrapper').show();
                                } else {
                                    $('#notification_days_wrapper').hide();
                                    $('#notification_unit_wrapper').hide();
                                }
                            }

                            // Run on page load
                            toggleNotificationFields();

                            // Toggle on change
                            $('#expiry_notification').change(function() {
                                toggleNotificationFields();
                            });

                            // Provider change handler
                            $('#provider_id').change(function() {
                                var provider_id = $(this).val();
                                if (provider_id) {
                                    $.ajax({
                                        url: '<?= base_url('admin/server_management/fetch_provider_url') ?>',
                                        type: 'POST',
                                        data: {
                                            provider_id: provider_id,
                                            csrf_token: csrfToken
                                        },
                                        dataType: 'json',
                                        success: function(response) {
                                            if (response.status === 'success') {
                                                $('#provider_url').val(response.provider_url);
                                            }
                                        },
                                        error: function() {
                                            console.error('Error fetching provider URL');
                                        }
                                    });
                                } else {
                                    $('#provider_url').val('');
                                }
                            });

                            // Expiry Date Calculation
                            function calculateExpiryDate() {
                                var purchaseDate = $('#purchase_date').val();
                                var duration = $('#duration').val();
                                var timeUnit = $('#time_unit').val();

                                if (purchaseDate && duration && timeUnit) {
                                    var date = new Date(purchaseDate);
                                    var amount = parseInt(duration);

                                    if (isNaN(amount)) return;

                                    switch (timeUnit) {
                                        case 'Days':
                                            date.setDate(date.getDate() + amount);
                                            break;
                                        case 'Weeks':
                                            date.setDate(date.getDate() + (amount * 7));
                                            break;
                                        case 'Months':
                                            date.setMonth(date.getMonth() + amount);
                                            break;
                                        case 'Years':
                                            date.setFullYear(date.getFullYear() + amount);
                                            break;
                                        case 'Decade':
                                            date.setFullYear(date.getFullYear() + (amount * 10));
                                            break;
                                        case 'Century':
                                            date.setFullYear(date.getFullYear() + (amount * 100));
                                            break;
                                    }

                                    var year = date.getFullYear();
                                    var month = ('0' + (date.getMonth() + 1)).slice(-2);
                                    var day = ('0' + date.getDate()).slice(-2);

                                    $('#expiry_date').val(year + '-' + month + '-' + day);
                                }
                            }

                            $('#purchase_date, #duration, #time_unit').on('change keyup', calculateExpiryDate);
                            calculateExpiryDate();

                            // Currency Conversion
                            function updateConversion() {
                                var price = $('#price_input').val();
                                var currency = $('#currency_id').val();

                                if (price && currency) {
                                    if (currency === 'BDT') {
                                        $('#conversion_result').text('');
                                        return;
                                    }

                                    $('#conversion_result').html('<i class="fa fa-spinner fa-spin"></i> Converting...');

                                    $.getJSON('https://api.exchangerate-api.com/v4/latest/' + currency, function(data) {
                                        if (data && data.rates && data.rates.BDT) {
                                            var rate = data.rates.BDT;
                                            var converted = (price * rate).toFixed(2);
                                            $('#conversion_result').text('≈ ' + converted + ' BDT');
                                        } else {
                                            $('#conversion_result').text('Rate unavailable');
                                        }
                                    }).fail(function() {
                                        $('#conversion_result').text('API Error');
                                    });
                                } else {
                                    $('#conversion_result').text('');
                                }
                            }

                            $('#price_input, #currency_id').on('change keyup', updateConversion);
                            updateConversion();

                            $('.toggle-password').click(function() {
                                var input = $(this).closest('.input-group').find('input');
                                var type = input.attr('type') === 'password' ? 'text' : 'password';
                                input.attr('type', type);
                                $(this).find('i').toggleClass('fa-eye fa-eye-slash');
                            });

                            // Fix Select2 focus issue in Bootstrap modals
                            if ($.fn.modal && $.fn.modal.Constructor) {
                                $.fn.modal.Constructor.prototype.enforceFocus = function() {};
                            }

                            $('#myModal').on('shown.bs.modal', function() {
                                // Remove tabindex to prevent focus issues with Select2 search
                                $(this).removeAttr('tabindex');
                                
                                if ($.fn.select2) {
                                    $(this).find('.select_box').each(function() {
                                        if (!$(this).hasClass('select2-hidden-accessible')) {
                                            $(this).select2({
                                                theme: 'bootstrap',
                                                width: '100%'
                                            });
                                        }
                                    });
                                }
                                if (typeof initdatepicker === 'function') {
                                    initdatepicker();
                                }
                                $(this).find('.start_date, .end_date, .datepicker').datepicker({
                                    autoclose: true,
                                    format: 'yyyy-mm-dd',
                                    todayBtn: "linked"
                                });
                                $(this).find('.start_date, .end_date, .datepicker').on('click', function() {
                                    $(this).datepicker('show');
                                });
                                $(this).find('.input-group-addon a').on('click', function(e) {
                                    e.preventDefault();
                                    $(this).parents('.input-group').find('input').focus();
                                });
                            });

                            // Quick Add Modal Logic
                            $('.quick-add-btn').click(function(e) {
                                e.preventDefault();
                                var btn = $(this);
                                currentTargetSelect = btn.closest('.input-group').find('select');
                                var type = btn.data('type');
                                var url = btn.data('url');

                                var titleMap = {
                                    'provider': 'Add New Provider',
                                    'project': 'Add New Project',
                                    'category': 'Add New Category',
                                    'client': 'Add New Client',
                                    'nameserver': 'Add New Nameserver',
                                    'type': 'Add New Domain Type',
                                    'status': 'Add New Domain Status',
                                    'hosting': 'Add New Hosting'
                                };
                                $('#myModal .modal-title').text(titleMap[type] || 'Add New');
                                $('#myModal .modal-body').html('<div class="text-center mt-3 mb-3"><i class="fa fa-spinner fa-spin fa-2x"></i> Loading...</div>');
                                $('#myModal').modal('show');

                                $.get(url, function(response) {
                                    $('#myModal .modal-content').html(response);
                                    // Re-initialize Select2 for the new content
                                    if ($.fn.select2) {
                                        $('#myModal').find('.select_box').each(function() {
                                            if ($(this).data('select2')) {
                                                $(this).select2('destroy');
                                            }
                                            $(this).select2({
                                                theme: 'bootstrap',
                                                width: '100%'
                                            });
                                        });
                                    }
                                    // Re-initialize datepicker if needed
                                    if (typeof initdatepicker === 'function') {
                                        initdatepicker();
                                    }
                                    $('#myModal').find('.start_date, .end_date, .datepicker').datepicker({
                                        autoclose: true,
                                        format: 'yyyy-mm-dd',
                                        todayBtn: "linked"
                                    });
                                }).fail(function() {
                                    $('#myModal .modal-body').html('<div class="alert alert-danger">Error: Not Found</div>');
                                });
                            });

                            // Handle modal form submissions via AJAX
                            $(document).on('submit', '#myModal form', function(e) {
                                var form = $(this);
                                var action = form.attr('action');
                                if (!action) return;

                                if (action.indexOf('admin/projects/saved_project') !== -1 ||
                                    action.indexOf('admin/projects/update_category') !== -1 ||
                                    action.indexOf('admin/client/save_client') !== -1 ||
                                    action.indexOf('admin/client/update_client') !== -1 ||
                                    action.indexOf('admin/server_management/add_nameserver') !== -1 ||
                                    action.indexOf('admin/server_management/add_provider') !== -1 ||
                                    action.indexOf('admin/server_management/add_domain_type') !== -1 ||
                                    action.indexOf('admin/server_management/add_domain_status') !== -1 ||
                                    action.indexOf('admin/server_management/add_hosting_type') !== -1) {

                                    e.preventDefault();

                                    var submitBtn = form.find('button[type="submit"]');
                                    var originalBtnText = submitBtn.text();
                                    submitBtn.prop('disabled', true).text('Saving...');

                                    $.ajax({
                                        type: "POST",
                                        url: action,
                                        data: form.serialize(),
                                        dataType: "json",
                                        success: function(response) {
                                            if (response.status === 'success') {
                                                var select = currentTargetSelect;
                                                if (!select || !select.length) {
                                                    if (action.indexOf('projects') !== -1) select = $('#project_id_select');
                                                    else if (action.indexOf('client') !== -1) select = $('#client_id_select');
                                                    else if (action.indexOf('nameserver') !== -1) select = $('select[name="nameservers[]"]');
                                                    else if (action.indexOf('provider') !== -1) select = $('#provider_id');
                                                    else if (action.indexOf('add_domain_type') !== -1) select = $('#domain_type_select');
                                                    else if (action.indexOf('add_domain_status') !== -1) select = $('#domain_status_select');
                                                    else if (action.indexOf('add_hosting_type') !== -1) select = $('#hosting_id_select');
                                                }

                                                if (select && select.length) {
                                                    var newOption = new Option(response.text || response.name || response.group, response.id, true, true);
                                                    select.append(newOption).trigger('change');
                                                }

                                                $('#myModal').modal('hide');
                                                if (typeof toastr !== 'undefined') {
                                                    toastr.success(response.message || 'Saved successfully');
                                                }
                                            } else {
                                                alert(response.message || 'Error occurred while saving');
                                            }
                                        },
                                        error: function(xhr, status, error) {
                                            alert('An error occurred. Please check the console.');
                                            console.error(xhr.responseText);
                                        },
                                        complete: function() {
                                            submitBtn.prop('disabled', false).text(originalBtnText);
                                        }
                                    });
                                }
                            });

                            // Custom Fields Logic
                            $('#add_custom_field').click(function() {
                                var row = `
                                    <div class="row custom-field-row mb-2">
                                        <div class="col-md-5">
                                            <input type="text" name="custom_field_label[]" class="form-control" placeholder="Label">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" name="custom_field_value[]" class="form-control" placeholder="Value">
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-danger remove-custom-field w-100" tabindex="-1"><i class="fa fa-trash"></i></button>
                                        </div>
                                    </div>`;
                                $('#custom_fields_container').append(row);
                                $('#no_custom_fields_msg').hide();
                            });

                            $(document).on('click', '.remove-custom-field', function() {
                                $(this).closest('.custom-field-row').remove();
                                if ($('#custom_fields_container .custom-field-row').length === 0) {
                                    $('#no_custom_fields_msg').show();
                                }
                            });

                            // Permission toggle logic for modals (Delegated)
                            $(document).on('change', '.permission_user_modal', function() {
                                var val = $(this).val();
                                if (val == 'custom_permission') {
                                    $('#permission_user_modal').show();
                                } else {
                                    $('#permission_user_modal').hide();
                                }
                            });

                            $(document).on('change', '.assigned_to_modal', function() {
                                var user_id = $(this).val();
                                if (this.checked) {
                                    $("#action_u_modal_" + user_id).show();
                                } else {
                                    $("#action_u_modal_" + user_id).hide();
                                }
                            });
                        });
                    </script>
                </form>
            </div>
        </div>
    </div>
</div>