<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>
<?php if (validation_errors()) { ?>
    <div class="alert alert-danger"><?= validation_errors() ?></div>
<?php } ?>

<?php $hosting_id = isset($hosting_info) ? $hosting_info->id : ''; ?>
<?php $action = $hosting_id ? 'admin/server_management/add_hosting/' . $hosting_id : 'admin/server_management/add_hosting'; ?>

<link rel="stylesheet" href="<?= base_url() ?>assets/plugins/select2/dist/css/select2.min.css">
<link rel="stylesheet" href="<?= base_url() ?>assets/plugins/select2/dist/css/select2-bootstrap.min.css">
<script src="<?= base_url() ?>assets/plugins/select2/dist/js/select2.min.js"></script>

<style>
    /* ERP Style Redesign */
    .erp-card { border: none; border-radius: 0; box-shadow: none !important; background-color: transparent; }
    .erp-card .card-body { padding: 0; }
    
    .erp-form label { font-size: 11px; font-weight: 600; color: #666; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.3px; }
    .erp-form .form-control { border-radius: 2px; border: 1px solid #d2d6de; font-size: 13px; height: 34px; box-shadow: none; padding: 6px 10px; color: #333; transition: border-color .15s ease-in-out;}
    .erp-form .form-control:focus { border-color: #3c8dbc; box-shadow: none; }
    .erp-form textarea.form-control { height: auto; }
    
    /* ---------------------------------------------------
       Strict Input Group Attachments (Fix for Select2 & Buttons) 
       --------------------------------------------------- */
    .erp-form .input-group { display: flex; flex-wrap: nowrap; align-items: stretch; width: 100%; }
    
    /* Ensure the inputs/selects take up the remaining space */
    .erp-form .input-group > .form-control:not(select),
    .erp-form .input-group .select2-container { flex: 1 1 auto; width: 1% !important; margin-bottom: 0; }
    
    /* Flatten borders for the attached look */
    .erp-form .input-group > .form-control,
    .erp-form .input-group > select.form-control:not(.select2-hidden-accessible),
    .erp-form .input-group .select2-container--bootstrap .select2-selection { border-radius: 2px 0 0 2px; border-right: none; }

    /* The Plus/Eye Button Wrapper */
    .erp-form .input-group-append { display: flex; margin-left: 0; }
    
    /* The Attached Button Styling */
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
    .erp-form .input-group-append .btn.toggle-password { background-color: #fff; } /* Eye icons white bg */
    
    .erp-form .input-group-append .btn:hover { background-color: #e0e0e0; color: #333; }

    /* Price Input Specific Overrides (Prepend + Input + Append) */
    .erp-form .input-group-prepend .form-control { border-radius: 2px 0 0 2px; border-right: 1px solid #d2d6de; flex: 0 0 auto; width: auto !important; height: 34px; }
    .erp-form .price-group > input.form-control { border-radius: 0; border-left: none; }
    
    /* --------------------------------------------------- */
    
    .erp-section-title { font-size: 12px; font-weight: bold; color: #333; border-bottom: 1px solid #eee; padding-bottom: 8px; margin-top: 30px; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.5px; }
    .erp-form .form-group { margin-bottom: 15px; }
    
    /* Buttons */
    .erp-form .btn-success { background-color: #00a65a; border-color: #008d4c; font-size: 12px; font-weight: 600; padding: 6px 25px; border-radius: 2px; box-shadow: none; text-transform: uppercase; }
    .erp-form .btn-success:hover { background-color: #008d4c; }
    .erp-form .btn-cancel { font-size: 12px; font-weight: 500; padding: 6px 15px; color: #0073b7; background: transparent; border: none; text-decoration: none; margin-left: 10px;}
    .erp-form .btn-cancel:hover { text-decoration: underline; color: #005384; }

    /* Select2 Overrides */
    .select2-container--bootstrap .select2-selection--multiple .select2-selection__choice { 
        margin-top: 5px !important; 
        margin-right: 5px !important; 
        font-size: 12px !important; 
        background-color: #f0f0f0 !important; 
        border: 1px solid #ccc !important; 
        color: #333 !important; 
        padding: 2px 10px !important; 
        border-radius: 3px !important;
        line-height: 1.4 !important;
    }
    .select2-container--bootstrap .select2-selection--multiple { 
        min-height: 34px !important; 
        height: auto !important; 
        padding-bottom: 5px !important;
        border-radius: 2px !important;
    }
    .select2-container--bootstrap .select2-selection--multiple .select2-search--inline .select2-search__field {
        margin-top: 5px !important;
        height: 24px !important;
    }
    .datepicker, .ui-datepicker { z-index: 9999 !important; }
    
    /* Select2 Dropdown Z-Index Fix for Modals */
    .select2-container--open { z-index: 9999999 !important; }
    .select2-dropdown { z-index: 9999999 !important; }
    
    /* Ensure input-group doesn't collapse Select2 */
    .erp-form .input-group .select2-container--bootstrap {
        flex: 1 1 auto !important;
        width: 1% !important;
    }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card erp-card">
            <div class="card-body">
                <form method="post" action="<?= base_url($action) ?>" class="erp-form">
                    <div class="erp-section-title" style="margin-top: 5px;">Hosting Details</div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="<?= isset($hosting_info) ? $hosting_info->title : '' ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Provider Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select name="provider_id" class="form-control" id="provider_id" required>
                                        <option value="">Select Provider</option>
                                        <?php if (!empty($providers)): ?>
                                            <?php foreach ($providers as $provider): ?>
                                                <option value="<?= $provider['id'] ?>" <?= isset($hosting_info) && $hosting_info->provider_id == $provider['id'] ? 'selected' : '' ?>><?= $provider['provider_name'] ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
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
                                <label>Provider URL</label>
                                <input type="text" name="provider_url" class="form-control" value="<?= isset($hosting_info) ? $hosting_info->provider_url : '' ?>" id="provider_url">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Server Type <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select name="server_type" class="form-control" required>
                                        <option value="">Select Type</option>
                                        <?php if (!empty($server_types)): ?>
                                            <?php foreach ($server_types as $type): ?>
                                                <option value="<?= $type['name'] ?>" <?= isset($hosting_info) && $hosting_info->server_type == $type['name'] ? 'selected' : '' ?>><?= $type['name'] ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn quick-add-btn" data-type="server_type" data-url="<?= base_url('admin/ajax_api/add_server_type') ?>" tabindex="-1">
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
                                <label>Server Name</label>
                                <input type="text" name="server_name" class="form-control" value="<?= isset($hosting_info) ? $hosting_info->server_name : '' ?>" placeholder="Enter server name">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Main Domain</label>
                                <div class="input-group">
                                    <select name="main_domain[]" class="form-control select2" id="main_domain" multiple data-placeholder="Select Domain(s)">
                                        <?php 
                                        $selected_domains = isset($hosting_info) && $hosting_info->main_domain ? explode(',', $hosting_info->main_domain) : [];
                                        ?>
                                        <?php if (!empty($domains)): ?>
                                            <?php foreach ($domains as $domain): ?>
                                                <option value="<?= $domain['id'] ?>" <?= in_array($domain['id'], $selected_domains) ? 'selected' : '' ?>><?= $domain['domain_name'] ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn quick-add-btn" data-type="domain" data-url="<?= base_url('admin/server_management/add_domain') ?>" tabindex="-1">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Hostname</label>
                                <input type="text" name="hostname" class="form-control" value="<?= isset($hosting_info) ? $hosting_info->hostname : '' ?>" placeholder="Enter hostname">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Nameservers</label>
                                <div class="input-group">
                                    <select name="nameservers[]" class="form-control select2" id="nameservers" multiple data-placeholder="Select Nameserver(s)">
                                        <?php 
                                        $selected_nameservers = isset($hosting_info) && $hosting_info->nameservers ? explode(',', $hosting_info->nameservers) : [];
                                        ?>
                                        <?php if (!empty($nameservers)): ?>
                                            <?php foreach ($nameservers as $ns): ?>
                                                <option value="<?= $ns['name'] ?>" <?= in_array($ns['name'], $selected_nameservers) ? 'selected' : '' ?>><?= $ns['name'] ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn quick-add-btn" data-type="nameserver" data-url="<?= base_url('admin/server_management/add_nameserver') ?>" tabindex="-1">
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
                                <label>Server Location</label>
                                <input type="text" name="server_location" class="form-control" value="<?= isset($hosting_info) ? $hosting_info->server_location : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>IP Address</label>
                                <input type="text" name="ip_address" class="form-control" id="ip_address" value="<?= isset($hosting_info) ? $hosting_info->ip_address : '' ?>" placeholder="e.g. 192.168.1.1 or 2001:db8::1">
                                <small class="text-muted" style="font-size:10px;">IPv4 or IPv6 compatible</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>CPanel URL</label>
                                <input type="text" name="cpanel_url" class="form-control" value="<?= isset($hosting_info) ? $hosting_info->cpanel_url : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" value="<?= isset($hosting_info) ? $hosting_info->username : '' ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" class="form-control" value="<?= isset($hosting_info) ? $hosting_info->password : '' ?>" id="password">
                                    <div class="input-group-append">
                                        <button class="btn toggle-password" type="button" data-target="password" tabindex="-1"><i class="fa fa-eye"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="erp-section-title">DNS Provider Credentials</div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>DNS Provider</label>
                                <?php
                                $selected_dns_provider_name = '';
                                if (isset($hosting_info) && !empty($hosting_info->dns_provider_name)) {
                                    $selected_dns_provider_name = $hosting_info->dns_provider_name;
                                } elseif (isset($hosting_info) && !empty($hosting_info->dns_provider_id) && !empty($providers)) {
                                    foreach ($providers as $provider) {
                                        if ($hosting_info->dns_provider_id == $provider['id']) {
                                            $selected_dns_provider_name = $provider['provider_name'];
                                            break;
                                        }
                                    }
                                }
                                ?>
                                <div class="input-group">
                                    <select name="dns_provider_name" class="form-control select2-tags" id="dns_provider_name" data-placeholder="Select or type DNS provider">
                                        <option value=""></option>
                                        <?php if (!empty($dns_providers)): ?>
                                            <?php foreach ($dns_providers as $provider): ?>
                                                <option value="<?= htmlspecialchars($provider['name']) ?>" <?= (isset($hosting_info) && $hosting_info->dns_provider_name == $provider['name']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($provider['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <?php if (!empty($selected_dns_provider_name) && !in_array($selected_dns_provider_name, array_column($dns_providers ?: [], 'name'))): ?>
                                            <option value="<?= htmlspecialchars($selected_dns_provider_name) ?>" selected><?= htmlspecialchars($selected_dns_provider_name) ?></option>
                                        <?php endif; ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn quick-add-btn" data-type="dns_provider" data-url="<?= base_url('admin/server_management/add_dns_provider') ?>" tabindex="-1">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>DNS Login Email</label>
                                <input type="email" name="dns_email" class="form-control" value="<?= isset($hosting_info) ? $hosting_info->dns_email : '' ?>" placeholder="Cloudflare login email">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>DNS Login Password</label>
                                <div class="input-group">
                                    <input type="password" name="dns_password" class="form-control" value="<?= isset($hosting_info) ? $hosting_info->dns_password : '' ?>" id="dns_password" placeholder="Cloudflare login password">
                                    <div class="input-group-append">
                                        <button class="btn toggle-password" type="button" data-target="dns_password" tabindex="-1"><i class="fa fa-eye"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="erp-section-title">Billing & Service Terms</div>

                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Registration Date</label>
                                <input type="date" name="date" class="form-control" value="<?= isset($hosting_info) ? $hosting_info->date : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Exp Date <span class="text-danger">*</span></label>
                                <input type="date" name="purchase_date" id="h_purchase_date" class="form-control" value="<?= isset($hosting_info) ? $hosting_info->purchase_date : '' ?>" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Duration <span class="text-danger">*</span></label>
                                <input type="number" name="days" id="h_days" class="form-control" value="<?= isset($hosting_info) ? $hosting_info->days : '' ?>" placeholder="Enter duration" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Time Unit <span class="text-danger">*</span></label>
                                <select name="time_unit" class="form-control" id="h_time_unit" required>
                                    <option value="Days" <?= isset($hosting_info) && $hosting_info->time_unit == 'Days' ? 'selected' : '' ?>>Days</option>
                                    <option value="Weeks" <?= isset($hosting_info) && $hosting_info->time_unit == 'Weeks' ? 'selected' : '' ?>>Weeks</option>
                                    <option value="Months" <?= isset($hosting_info) && $hosting_info->time_unit == 'Months' ? 'selected' : '' ?>>Months</option>
                                    <option value="Years" <?= isset($hosting_info) && $hosting_info->time_unit == 'Years' ? 'selected' : '' ?>>Years</option>
                                    <option value="Decade" <?= isset($hosting_info) && $hosting_info->time_unit == 'Decade' ? 'selected' : '' ?>>Decade</option>
                                    <option value="Century" <?= isset($hosting_info) && $hosting_info->time_unit == 'Century' ? 'selected' : '' ?>>Century</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Future Exp Date <span class="text-danger">*</span></label>
                                <input readonly type="date" name="expiry_date" id="h_expiry_date" class="form-control" value="<?= isset($hosting_info) ? $hosting_info->expiry_date : '' ?>" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Renew <span class="text-danger">*</span></label>
                                <?php $selected_renew = isset($hosting_info) && !empty($hosting_info->renew) ? $hosting_info->renew : 'manual'; ?>
                                <select name="renew" class="form-control" required>
                                    <option value="manual" <?= $selected_renew == 'manual' ? 'selected' : '' ?>>Manual</option>
                                    <option value="automatic" <?= $selected_renew == 'automatic' ? 'selected' : '' ?>>Automatic</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Price <span class="text-danger">*</span></label>
                                <div class="input-group price-group">
                                    <div class="input-group-prepend">
                                        <select name="currency_id" class="form-control" id="currency_id" required>
                                            <option value="">Currency</option>
                                            <?php if (!empty($currencies)): ?>
                                                <?php foreach ($currencies as $currency): ?>
                                                    <option value="<?= $currency['code'] ?>" 
                                                            data-rate="<?= isset($currency['rate']) ? $currency['rate'] : 0 ?>"
                                                            <?= isset($hosting_info) && $hosting_info->currency_id == $currency['code'] ? 'selected' : '' ?>>
                                                        <?= $currency['code'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <input type="number" step="0.01" name="price" class="form-control" id="price_input" value="<?= isset($hosting_info) ? $hosting_info->price : '' ?>" required>
                                    <div class="input-group-append">
                                        <button type="button" class="btn quick-add-btn" data-type="currency" data-url="<?= base_url('admin/ajax_api/add_currency') ?>" tabindex="-1">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-1" id="bdt_conversion_text" style="font-size:10px;">Equivalent: <span id="bdt_amount">0.00</span> BDT</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Project</label>
                                <div class="input-group">
                                    <select name="project_id[]" class="form-control select2" id="project_id" multiple data-placeholder="Select Project(s)">
                                        <?php 
                                        $selected_projects = isset($hosting_info) && $hosting_info->project_id ? explode(',', $hosting_info->project_id) : [];
                                        ?>
                                        <?php if (!empty($projects)): ?>
                                            <?php foreach ($projects as $project): ?>
                                                <option value="<?= $project['project_id'] ?>" <?= in_array($project['project_id'], $selected_projects) ? 'selected' : '' ?>><?= $project['project_name'] ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
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
                                    <select name="client_id[]" class="form-control select2" id="client_id" multiple data-placeholder="Select Client(s)">
                                        <?php 
                                        $selected_clients = isset($hosting_info) && $hosting_info->client_id ? explode(',', $hosting_info->client_id) : [];
                                        ?>
                                        <?php if (!empty($clients)): ?>
                                            <?php foreach ($clients as $client): ?>
                                                <option value="<?= $client['client_id'] ?>" <?= in_array($client['client_id'], $selected_clients) ? 'selected' : '' ?>><?= $client['name'] ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
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
                                <label>Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-control" required>
                                    <option value="">Select Status</option>
                                    <option value="Active" <?= isset($hosting_info) && $hosting_info->status == 'Active' ? 'selected' : '' ?>>Active</option>
                                    <option value="Pending" <?= isset($hosting_info) && $hosting_info->status == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Suspended" <?= isset($hosting_info) && $hosting_info->status == 'Suspended' ? 'selected' : '' ?>>Suspended</option>
                                    <option value="Cancelled" <?= isset($hosting_info) && $hosting_info->status == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>FTP Username</label>
                                <input type="text" name="ftp_username" class="form-control" value="<?= isset($hosting_info) ? $hosting_info->ftp_username : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>FTP Password</label>
                                <div class="input-group">
                                    <input type="password" name="ftp_password" class="form-control" value="<?= isset($hosting_info) ? $hosting_info->ftp_password : '' ?>" id="ftp_password">
                                    <div class="input-group-append">
                                        <button class="btn toggle-password" type="button" data-target="ftp_password" tabindex="-1"><i class="fa fa-eye"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="erp-section-title">SSL Settings</div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox" style="margin-top: 30px;">
                                    <input type="checkbox" name="ssl_certificate" class="custom-control-input" id="ssl_certificate" <?= isset($hosting_info) && $hosting_info->ssl_certificate ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="ssl_certificate" style="text-transform: none; font-size: 13px;">SSL Certificate Enabled</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>SSL Expiry Date</label>
                                <input type="date" name="ssl_expiry_date" class="form-control" value="<?= isset($hosting_info) ? $hosting_info->ssl_expiry_date : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>SSL Type</label>
                                <select name="ssl_type" class="form-control">
                                    <option value="">Select Type</option>
                                    <option value="Free" <?= isset($hosting_info) && $hosting_info->ssl_type == 'Free' ? 'selected' : '' ?>>Free</option>
                                    <option value="Paid" <?= isset($hosting_info) && $hosting_info->ssl_type == 'Paid' ? 'selected' : '' ?>>Paid</option>
                                    <option value="Wildcard" <?= isset($hosting_info) && $hosting_info->ssl_type == 'Wildcard' ? 'selected' : '' ?>>Wildcard</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>SSL Certificate Information</label>
                                <textarea name="ssl_info" class="form-control" rows="2"><?= isset($hosting_info) ? htmlspecialchars($hosting_info->ssl_info) : '' ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="erp-section-title">Notification & RBAC Settings</div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox" style="margin-top: 5px;">
                                    <input type="checkbox" name="expiry_notification" class="custom-control-input" id="expiry_notification" <?= isset($hosting_info) && $hosting_info->expiry_notification ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="expiry_notification" style="text-transform: none; font-size: 13px;">Enable Expiry Notification</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3" id="notification_days_wrapper">
                            <div class="form-group">
                                <label style="font-size: 11px; color: #777;">Days Before</label>
                                <input type="number" name="notification_days" class="form-control input-sm" value="<?= isset($hosting_info) ? $hosting_info->notification_days : '7' ?>">
                            </div>
                        </div>
                        <div class="col-md-3" id="notification_unit_wrapper">
                            <div class="form-group">
                                <label style="font-size: 11px; color: #777;">Time Unit</label>
                                <select name="notification_time_unit" class="form-control input-sm">
                                    <option value="Days" <?= isset($hosting_info) && $hosting_info->notification_time_unit == 'Days' ? 'selected' : '' ?>>Days</option>
                                    <option value="Weeks" <?= isset($hosting_info) && $hosting_info->notification_time_unit == 'Weeks' ? 'selected' : '' ?>>Weeks</option>
                                    <option value="Months" <?= isset($hosting_info) && $hosting_info->notification_time_unit == 'Months' ? 'selected' : '' ?>>Months</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div style="background: #f8f9fa; padding: 10px 15px; border-radius: 6px; border: 1px solid #e9ecef;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="create_calendar_task" class="custom-control-input" id="create_calendar_task" value="1">
                                    <label class="custom-control-label" for="create_calendar_task" style="text-transform: none; font-size: 13px; font-weight: 600;">Create Task in Calendar for Renewal</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="erp-section-title mt-4">Record Access Permissions</div>
                    <div class="row">
                        <div class="col-md-12">
                            <div style="display: flex; align-items: center; flex-wrap: wrap; background: #fff; padding: 10px 15px; border-radius: 6px; border: 1px solid #eee;">
                                <span style="font-size: 12px; font-weight: 600; color: #555; text-transform: uppercase; margin-right: 20px;">Who can access this record:</span>
                                
                                <div class="radio-inline c-radio needsclick m0" style="display: inline-block;">
                                    <label class="needsclick" style="text-transform: none; font-size: 13px; font-weight: normal; margin-bottom: 0;">
                                        <input type="radio" name="task_permission" value="everyone" <?= (empty($permissionL) || $permissionL == 'all') ? 'checked' : '' ?> class="task_permission_radio">
                                        <span class="fa fa-circle"></span> Everyone
                                    </label>
                                </div>
                                <div class="radio-inline c-radio needsclick m0 ml-3" style="display: inline-block; margin-left: 20px;">
                                    <label class="needsclick" style="text-transform: none; font-size: 13px; font-weight: normal; margin-bottom: 0;">
                                        <input type="radio" name="task_permission" value="custom_permission" <?= (!empty($permissionL) && $permissionL != 'all') ? 'checked' : '' ?> class="task_permission_radio">
                                        <span class="fa fa-circle"></span> Custom Permission
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Permission User List -->
                    <div id="task_permission_users" style="display: <?= (!empty($permissionL) && $permissionL != 'all') ? 'block' : 'none' ?>; margin-top: 20px; padding: 20px; background: #fff; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <label style="font-weight: 700; font-size: 14px; margin-bottom: 15px; display: block; color: #333; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #eee; padding-bottom: 10px;">Select Specific Users & Permissions</label>
                        <div class="row">
                            <?php if (!empty($staff_members)): ?>
                                <?php foreach ($staff_members as $staff): ?>
                                    <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                                        <div class="user-permission-item" style="padding: 10px; border: 1px solid #e9ecef; border-radius: 6px; background: #fdfdfd; height: 100%;">
                                            <?php
                                            $is_admin = ($staff->role_id == 1);
                                            $role_badge = $is_admin
                                                ? '<span class="label label-danger" style="font-size: 10px; padding: 2px 6px; margin-left: 5px; vertical-align: middle;">Admin</span>'
                                                : '<span class="label label-info" style="font-size: 10px; padding: 2px 6px; margin-left: 5px; vertical-align: middle;">Staff</span>';
                                            
                                            $user_permission = array();
                                            if (!empty($permissionL) && $permissionL != 'all') {
                                                $all_permission = json_decode($permissionL, true);
                                                if (!empty($all_permission[$staff->user_id])) {
                                                    $user_permission = $all_permission[$staff->user_id];
                                                }
                                            }
                                            ?>
                                            <div style="padding: 0; border: 1px solid #e9ecef; border-radius: 8px; background: #fff; height: 100%; transition: all 0.2s ease;">
                                                <div class="checkbox c-checkbox m0">
                                                    <label class="needsclick" style="margin-bottom: 0; display: flex; align-items: center; width: 100%; cursor: pointer; padding: 12px;">
                                                        <input type="checkbox" value="<?= $staff->user_id ?>" name="assigned_to[]" class="needsclick assigned_to_task <?= $is_admin ? 'is-admin' : '' ?>" <?= !empty($user_permission) ? 'checked' : '' ?>>
                                                        <span class="fa fa-check" style="margin-top: -10px; left: 12px;"></span>
                                                        <div style="display: flex; align-items: center; margin-left: 25px; flex: 1; overflow: hidden;">
                                                            <img src="<?= base_url() . (!empty($staff->avatar) ? $staff->avatar : 'assets/img/user/default.png') ?>" class="img-circle" style="width: 32px; height: 32px; border: 1px solid #eee; margin-right: 12px; flex-shrink: 0; object-fit: cover;">
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
                                            
                                            <div class="action_task_user mt-2" id="action_task_<?= $staff->user_id ?>" style="display: <?= !empty($user_permission) ? 'block' : 'none' ?>; padding-left: 28px; border-top: 1px dashed #eee; pt-2;">
                                                <div style="display: flex; flex-wrap: wrap; margin-top: 8px;">
                                                    <label class="checkbox-inline c-checkbox m0" style="font-size: 11px; margin-right: 15px; margin-bottom: 5px;">
                                                        <input checked type="checkbox" name="action_<?= $staff->user_id ?>[]" disabled value="view">
                                                        <span class="fa fa-check"></span> View
                                                    </label>
                                                    <label class="checkbox-inline c-checkbox m0" style="font-size: 11px; margin-right: 15px; margin-bottom: 5px;">
                                                        <input type="checkbox" name="action_<?= $staff->user_id ?>[]" value="edit" <?= ($is_admin || in_array('edit', $user_permission)) ? 'checked' : '' ?> <?= $is_admin ? 'disabled' : '' ?>>
                                                        <span class="fa fa-check"></span> Edit
                                                    </label>
                                                    <label class="checkbox-inline c-checkbox m0" style="font-size: 11px; margin-right: 0; margin-bottom: 5px;">
                                                        <input type="checkbox" name="action_<?= $staff->user_id ?>[]" value="delete" <?= ($is_admin || in_array('delete', $user_permission)) ? 'checked' : '' ?> <?= $is_admin ? 'disabled' : '' ?>>
                                                        <span class="fa fa-check"></span> Delete
                                                    </label>
                                                </div>
                                                <input type="hidden" name="action_<?= $staff->user_id ?>[]" value="view">
                                                <?php if($is_admin): ?>
                                                    <input type="hidden" name="action_<?= $staff->user_id ?>[]" value="edit">
                                                    <input type="hidden" name="action_<?= $staff->user_id ?>[]" value="delete">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="erp-section-title">Description</div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <textarea name="description" class="form-control" rows="3" placeholder="Enter description..."><?= isset($hosting_info) ? $hosting_info->description : '' ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4 mb-4">
                        <div class="col-md-12 text-left">
                            <button type="submit" class="btn btn-success">Save</button>
                            <a href="<?= base_url('admin/server_management/hosting') ?>" class="btn-cancel">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="universalQuickAddModal" tabindex="-1" role="dialog" aria-labelledby="universalQuickAddModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 2px;">
            <div class="modal-header" style="background-color: #f4f4f4; border-bottom: 1px solid #ddd;">
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
            <div class="modal-footer" style="border-top: 1px solid #ddd; background-color: #f9f9f9;">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 2px; font-size: 12px; font-weight: 600; text-transform: uppercase;">Close</button>
                <button type="button" class="btn btn-primary" id="universalModalSubmitBtn" style="border-radius: 2px; font-size: 12px; font-weight: 600; text-transform: uppercase;">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap',
        width: '100%'
    });

    $('.select2-tags').select2({
        theme: 'bootstrap',
        width: '100%',
        tags: true,
        placeholder: function() {
            return $(this).data('placeholder');
        },
        allowClear: true
    });


    // IP Address Validation (IPv4/IPv6)
    $('#ip_address').on('change', function() {
        var ip = $(this).val().trim();
        if (ip === '') return;

        var ipv4Regex = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
        var ipv6Regex = /^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))$/;

        if (!ipv4Regex.test(ip) && !ipv6Regex.test(ip)) {
            alert('Invalid IP Address! Please enter a valid IPv4 or IPv6 address.');
            $(this).val('').focus();
        }
    });

    // CSRF Configuration
    var csrfName = '<?= $this->security->get_csrf_token_name() ?>';
    var csrfHash = '<?= $this->security->get_csrf_hash() ?>';
    var base_url = '<?= base_url() ?>';
    var currentTargetSelect = null;
    var currentType = null;

    // Existing Notification Logic
    $('#notification_days_wrapper, #notification_unit_wrapper').hide();
    if ($('#expiry_notification').is(':checked')) {
        $('#notification_days_wrapper, #notification_unit_wrapper').show();
    }
    $('#expiry_notification').change(function() {
        if ($(this).is(':checked')) {
            $('#notification_days_wrapper, #notification_unit_wrapper').slideDown();
        } else {
            $('#notification_days_wrapper, #notification_unit_wrapper').slideUp();
        }
    });

    // The Calendar Task checkbox is now independent of RBAC
    $('#create_calendar_task').change(function() {
        // No longer toggles the RBAC section
    });

    // Toggle custom permission user list based on radio selection
    $('.task_permission_radio').on('click', function() {
        if ($(this).val() === 'custom_permission') {
            $('#task_permission_users').slideDown();
            $('.assigned_to_task.is-admin').prop('checked', true).trigger('change');
        } else {
            $('#task_permission_users').slideUp();
        }
    });

    // Toggle per-user action checkboxes when user is assigned
    $(document).on('change', '.assigned_to_task', function() {
        var userId = $(this).val();
        if (this.checked) {
            $('#action_task_' + userId).slideDown();
        } else {
            $('#action_task_' + userId).slideUp();
        }
    });

    // Existing Provider URL Fetch
    $('#provider_id').change(function() {
        var provider_id = $(this).val();
        if (provider_id) {
            $.ajax({
                url: base_url + 'admin/server_management/fetch_hosting_provider_url',
                type: 'POST',
                data: { provider_id: provider_id, csrf_test_name: csrfHash },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#provider_url').val(response.provider_url);
                    }
                },
                error: function() {
                    console.log('Error fetching provider URL');
                }
            });
        } else {
            $('#provider_url').val('');
        }
    });

    // Existing Password Toggle
    $('.toggle-password').click(function() {
        var target = $(this).data('target');
        var input = $('#' + target);
        var icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Quick Add Modal Logic
    // Use delegated event with namespacing to prevent double-binding
    $(document).off('click.quickAdd').on('click.quickAdd', '.quick-add-btn', function(e) {
        e.preventDefault();
        var btn = $(this);
        var type = btn.data('type');
        var url = btn.data('url');
        
        // Cache the select element related to this button
        currentTargetSelect = btn.closest('.input-group').find('select');
        currentType = type;

        var titleMap = {
            'provider': 'Add New Provider',
            'server_type': 'Add New Server Type',
            'domain': 'Add New Domain',
            'nameserver': 'Add New Nameserver',
            'dns_provider': 'Add New DNS Provider',
            'currency': 'Add New Currency',
            'project': 'Add New Project',
            'client': 'Add New Client'
        };

        $('#universalQuickAddModalLabel').text(titleMap[type] || 'Add New');
        $('#universalQuickAddModal .modal-body').html('<div class="text-center mt-4 mb-4"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Loading...</div>');
        $('#universalQuickAddModal').modal('show');

        $.get(url, function(response) {
            var wrapper = $('<div>').html(response);
            var form = wrapper.find('form').first();
            
            if (form.length > 0) {
                // Remove ALL buttons and footers from the loaded form content aggressively
                form.find('.modal-footer, .card-footer, .panel-footer, .btn-bottom-toolbar, button[type="submit"], button.btn-success, .text-left').remove();
                
                // Remove any panel headers/titles inside the body to avoid double titles
                form.find('.panel-heading, .card-header').remove();
                
                $('#universalQuickAddModal .modal-body').html(form);
            } else {
                // If it's just a raw form string or something else
                $('#universalQuickAddModal .modal-body').html(response);
            }
            
            // Re-initialize UI components in the new content after a short delay
            setTimeout(function() {
                var container = $('#universalQuickAddModal');
                // Re-initialize Select2
                if ($.fn.select2) {
                    container.find('.select_box, .select_multi, .select2, .select2-tags').each(function() {
                        if ($(this).data('select2')) {
                            $(this).select2('destroy');
                        }
                        var options = {
                            theme: 'bootstrap',
                            width: '100%'
                        };
                        if ($(this).hasClass('select2-tags')) {
                            options.tags = true;
                        }
                        $(this).select2(options);
                    });
                }
                
                // Re-initialize Datepickers
                if ($.fn.datepicker) {
                    container.find('.datepicker, .start_date, .end_date').datepicker({
                        autoclose: true,
                        format: 'yyyy-mm-dd',
                        todayBtn: "linked"
                    });
                }
            }, 100);
            
        }).fail(function() {
            $('#universalQuickAddModal .modal-body').html('<div class="alert alert-danger">Error: Could not load the form.</div>');
        });
    });

    // Universal Modal Submit Button
    $('#universalModalSubmitBtn').off('click').on('click', function() {
        var form = $('#universalQuickAddModal .modal-body').find('form');
        if (form.length) {
            // Use trigger('submit') to ensure jQuery submit handlers are fired
            form.trigger('submit');
        }
    });
    
    // Modal Form Submission via AJAX
    $(document).off('submit.quickAdd', '#universalQuickAddModal form').on('submit.quickAdd', '#universalQuickAddModal form', function(e) {
        e.preventDefault();
        var form = $(this);
        var action = form.data('action') || form.attr('action');
        if (!action) return alert('Form action not found');
        
        if (action.indexOf('http') === -1 && action.indexOf(base_url) === -1) {
            action = base_url + action;
        }
        
        var originalBtnText = $('#universalModalSubmitBtn').text();
        $('#universalModalSubmitBtn').prop('disabled', true).text('Saving...');
        
        $.ajax({
            url: action,
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && currentTargetSelect) {
                    var newOption = new Option(response.text || response.name, response.id, true, true);
                    currentTargetSelect.append(newOption).trigger('change');
                    $('#universalQuickAddModal').modal('hide');
                } else {
                    alert(response.message || 'Error adding record');
                }
            },
            error: function(xhr) {
                console.log('Error response:', xhr.responseText);
                alert('Error: ' + xhr.statusText + ' - Check console for details');
            },
            complete: function() {
                $('#universalModalSubmitBtn').prop('disabled', false).text(originalBtnText);
            }
        });
    });

    // Reset Modal on Hide
    $('#universalQuickAddModal').on('hidden.bs.modal', function() {
        $(this).find('.modal-body').empty();
        currentTargetSelect = null;
        currentType = null;
    });

    // Real-Time Currency Conversion
    function updateBdtConversion() {
        var price = parseFloat($('#price_input').val()) || 0;
        var selectedOption = $('#currency_id option:selected');
        var currencyCode = selectedOption.val();
        
        if (!currencyCode) {
            $('#bdt_amount').text('0.00');
            return;
        }

        if (currencyCode === 'BDT' || currencyCode === 'BAN') {
            $('#bdt_amount').text(price.toFixed(2));
            return;
        }

        var liveRate = parseFloat(selectedOption.data('live-rate')) || 0;
        
        if (liveRate === 0) {
            $('#bdt_amount').html('<i class="fa fa-spinner fa-spin"></i> Fetching...');
            $.getJSON('https://api.exchangerate-api.com/v4/latest/' + currencyCode, function(data) {
                if (data && data.rates && data.rates.BDT) {
                    var rate = data.rates.BDT;
                    selectedOption.data('live-rate', rate);
                    $('#bdt_amount').text((price * rate).toFixed(2));
                } else {
                    $('#bdt_amount').text('Rate unavailable');
                }
            }).fail(function() {
                $('#bdt_amount').text('API Error');
            });
            return;
        }
        $('#bdt_amount').text((price * liveRate).toFixed(2));
    }

    updateBdtConversion();
    $('#price_input').keyup(updateBdtConversion);
    $('#currency_id').change(updateBdtConversion);

    // Auto-calculate expiry date
    function calculateExpiryDate() {
        var purchaseDate = $('#h_purchase_date').val();
        var duration = $('#h_days').val();
        var timeUnit = $('#h_time_unit').val();
        
        if (purchaseDate && duration && timeUnit) {
            var date = new Date(purchaseDate);
            var value = parseInt(duration);
            
            switch(timeUnit) {
                case 'Days':
                    date.setDate(date.getDate() + value);
                    break;
                case 'Weeks':
                    date.setDate(date.getDate() + (value * 7));
                    break;
                case 'Months':
                    date.setMonth(date.getMonth() + value);
                    break;
                case 'Years':
                    date.setFullYear(date.getFullYear() + value);
                    break;
                case 'Decade':
                    date.setFullYear(date.getFullYear() + (value * 10));
                    break;
                case 'Century':
                    date.setFullYear(date.getFullYear() + (value * 100));
                    break;
            }
            
            var expiryDate = date.toISOString().split('T')[0];
            $('#h_expiry_date').val(expiryDate);
        }
    }
    
    $('#h_purchase_date, #h_days, #h_time_unit').on('change', calculateExpiryDate);
    $('#h_days').on('keyup', calculateExpiryDate);
    calculateExpiryDate();
});
</script>