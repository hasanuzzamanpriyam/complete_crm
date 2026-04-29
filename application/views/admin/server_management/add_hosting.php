<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<?php $hosting_id = isset($hosting_info) ? $hosting_info->id : ''; ?>
<?php $action = $hosting_id ? 'admin/server_management/add_hosting/' . $hosting_id : 'admin/server_management/add_hosting'; ?>

<link rel="stylesheet" href="<?= base_url() ?>assets/plugins/select2/dist/css/select2.min.css">
<link rel="stylesheet" href="<?= base_url() ?>assets/plugins/select2/dist/css/select2-bootstrap.min.css">
<script src="<?= base_url() ?>assets/plugins/select2/dist/js/select2.min.js"></script>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="post" action="<?= base_url($action) ?>">
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
                                        <button type="button" class="btn btn-outline-secondary quick-add-btn" data-type="provider" data-url="<?= base_url('admin/server_management/add_provider') ?>">
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
                                        <button type="button" class="btn btn-outline-secondary quick-add-btn" data-type="server_type" data-url="<?= base_url('admin/ajax_api/add_server_type') ?>">
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
                                        <button type="button" class="btn btn-outline-secondary quick-add-btn" data-type="domain" data-url="<?= base_url('admin/server_management/add_domain') ?>">
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
                                        <button type="button" class="btn btn-outline-secondary quick-add-btn" data-type="nameserver" data-url="<?= base_url('admin/server_management/add_nameserver') ?>">
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
                                <small class="text-muted">IPv4 or IPv6 compatible</small>
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
                                    <span class="input-group-btn">
                                        <button class="btn btn-default toggle-password" type="button" data-target="password"><i class="fa fa-eye"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Purchase Date <span class="text-danger">*</span></label>
                                <input type="date" name="purchase_date" class="form-control" value="<?= isset($hosting_info) ? $hosting_info->purchase_date : '' ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Expiry Date <span class="text-danger">*</span></label>
                                <input readonly type="date" name="expiry_date" class="form-control" value="<?= isset($hosting_info) ? $hosting_info->expiry_date : '' ?>" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Duration <span class="text-danger">*</span></label>
                                <input type="number" name="days" class="form-control" value="<?= isset($hosting_info) ? $hosting_info->days : '' ?>" placeholder="Enter duration" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Time Unit <span class="text-danger">*</span></label>
                                <select name="time_unit" class="form-control" id="time_unit" required>
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
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <select name="currency_id" class="form-control" id="currency_id" required>
                                            <option value="">Select Currency</option>
                                            <?php if (!empty($currencies)): ?>
                                                <?php foreach ($currencies as $currency): ?>
                                                    <option value="<?= $currency['code'] ?>" 
                                                            data-rate="<?= isset($currency['rate']) ? $currency['rate'] : 0 ?>"
                                                            <?= isset($hosting_info) && $hosting_info->currency_id == $currency['code'] ? 'selected' : '' ?>>
                                                        <?= $currency['code'] ?> (<?= $currency['symbol'] ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <input type="number" step="0.01" name="price" class="form-control" id="price_input" value="<?= isset($hosting_info) ? $hosting_info->price : '' ?>" required>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary quick-add-btn" data-type="currency" data-url="<?= base_url('admin/ajax_api/add_currency') ?>">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-1" id="bdt_conversion_text">Equivalent: <span id="bdt_amount">0.00</span> BDT</small>
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
                                        <button type="button" class="btn btn-outline-secondary quick-add-btn" data-type="project" data-url="<?= base_url('admin/ajax_api/add_project') ?>">
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
                                        <button type="button" class="btn btn-outline-secondary quick-add-btn" data-type="client" data-url="<?= base_url('admin/ajax_api/add_client') ?>">
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
                                    <span class="input-group-btn">
                                        <button class="btn btn-default toggle-password" type="button" data-target="ftp_password"><i class="fa fa-eye"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 mb-3">
                        <h5>SSL Settings</h5>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" name="ssl_certificate" class="custom-control-input" id="ssl_certificate" <?= isset($hosting_info) && $hosting_info->ssl_certificate ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="ssl_certificate">SSL Certificate</label>
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
                                <textarea name="ssl_info" class="form-control" rows="3"><?= isset($hosting_info) ? htmlspecialchars($hosting_info->ssl_info) : '' ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 mb-3">
                        <h5>Notification Settings</h5>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" name="expiry_notification" class="custom-control-input" id="expiry_notification" <?= isset($hosting_info) && $hosting_info->expiry_notification ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="expiry_notification">Expiry Notification</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4" id="notification_days_wrapper">
                            <div class="form-group">
                                <label>Notification Days Before</label>
                                <input type="number" name="notification_days" class="form-control" value="<?= isset($hosting_info) ? $hosting_info->notification_days : '7' ?>">
                            </div>
                        </div>
                        <div class="col-md-5" id="notification_unit_wrapper">
                            <div class="form-group">
                                <label>Notification Time Unit</label>
                                <select name="notification_time_unit" class="form-control">
                                    <option value="Days" <?= isset($hosting_info) && $hosting_info->notification_time_unit == 'Days' ? 'selected' : '' ?>>Days</option>
                                    <option value="Weeks" <?= isset($hosting_info) && $hosting_info->notification_time_unit == 'Weeks' ? 'selected' : '' ?>>Weeks</option>
                                    <option value="Months" <?= isset($hosting_info) && $hosting_info->notification_time_unit == 'Months' ? 'selected' : '' ?>>Months</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 mb-3">
                        <h5>Description</h5>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <textarea name="description" class="form-control" rows="4" placeholder="Enter description..."><?= isset($hosting_info) ? $hosting_info->description : '' ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12 text-left">
                            <button type="submit" class="btn btn-success">Save</button>
                            <a href="<?= base_url('admin/server_management/hosting') ?>" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="universalQuickAddModal" tabindex="-1" role="dialog" aria-labelledby="universalQuickAddModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="universalQuickAddModalLabel">Add New</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center" id="modalLoader">
                    <i class="fa fa-spinner fa-spin fa-2x"></i> Loading...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="universalModalSubmitBtn">Save</button>
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
    $('.quick-add-btn').click(function(e) {
        e.preventDefault();
        var btn = $(this);
        currentTargetSelect = btn.closest('.input-group').find('select');
        currentType = btn.data('type');
        var url = btn.data('url');
        
        var titleMap = {
            'provider': 'Add New Provider',
            'server_type': 'Add New Server Type',
            'plan': 'Add New Plan',
            'project': 'Add New Project',
            'client': 'Add New Client',
            'currency': 'Add New Currency',
            'domain': 'Add New Domain',
            'nameserver': 'Add New Nameserver'
        };
        $('#universalQuickAddModalLabel').text(titleMap[currentType] || 'Add New');
        
        $('#universalQuickAddModal .modal-body').html('<div class="text-center mt-3 mb-3"><i class="fa fa-spinner fa-spin fa-2x"></i> Loading...</div>');
        $('#universalQuickAddModal').modal('show');
        
        $.get(url, function(response) {
            var wrapper = $('<div>').html(response);
            var form = wrapper.find('form').first();
            if (!form.length) {
                form = wrapper.filter('form').first();
            }
            if (form.length) {
                var formAction = form.attr('action');
                if (formAction && formAction.indexOf('http') === -1 && formAction.indexOf(base_url) === -1) {
                    formAction = base_url + formAction;
                }
                form.removeAttr('action').data('action', formAction);
                form.addClass('quick-add-form');
                $('#universalQuickAddModal .modal-body').html(form);
            } else {
                // If it's just a raw form string (like from our new views)
                if (response.indexOf('<form') !== -1) {
                    $('#universalQuickAddModal .modal-body').html(response);
                } else {
                    $('#universalQuickAddModal .modal-body').html('<div class="alert alert-danger">Failed to load form</div>');
                }
            }
        }).fail(function(xhr) {
            $('#universalQuickAddModal .modal-body').html('<div class="alert alert-danger">Error: Not Found</div>');
        });
    });

    // Universal Modal Submit Button
    $('#universalModalSubmitBtn').off('click').on('click', function() {
        var form = $('#universalQuickAddModal .modal-body').find('form');
        if (form.length) {
            form.submit();
        }
    });
    
    // Modal Form Submission via AJAX
    $(document).off('submit', '#universalQuickAddModal form').on('submit', '#universalQuickAddModal form', function(e) {
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
                    currentTargetSelect.append($('<option>', {
                        value: response.id,
                        text: response.text || response.name,
                        selected: true
                    }));
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
        var purchaseDate = $('input[name="purchase_date"]').val();
        var duration = $('input[name="days"]').val();
        var timeUnit = $('select[name="time_unit"]').val();
        
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
            $('input[name="expiry_date"]').val(expiryDate);
        }
    }
    
    $('input[name="purchase_date"], input[name="days"], select[name="time_unit"]').on('change', calculateExpiryDate);
    $('input[name="days"]').on('keyup', calculateExpiryDate);
});
</script>
