<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<?php $hosting_id = isset($hosting_info) ? $hosting_info->id : ''; ?>
<?php $action = $hosting_id ? 'admin/server_management/add_hosting/' . $hosting_id : 'admin/server_management/add_hosting'; ?>

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
                                    <span class="input-group-btn">
                                        <a href="<?= base_url('admin/server_management/add_provider') ?>" class="btn btn-default" type="button">Add</a>
                                    </span>
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
                                <select name="server_type" class="form-control" required>
                                    <option value="">Select Type</option>
                                    <option value="Shared" <?= isset($hosting_info) && $hosting_info->server_type == 'Shared' ? 'selected' : '' ?>>Shared</option>
                                    <option value="VPS" <?= isset($hosting_info) && $hosting_info->server_type == 'VPS' ? 'selected' : '' ?>>VPS</option>
                                    <option value="Cloud" <?= isset($hosting_info) && $hosting_info->server_type == 'Cloud' ? 'selected' : '' ?>>Cloud</option>
                                    <option value="Dedicated" <?= isset($hosting_info) && $hosting_info->server_type == 'Dedicated' ? 'selected' : '' ?>>Dedicated</option>
                                </select>
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
                                <input type="text" name="ip_address" class="form-control" value="<?= isset($hosting_info) ? $hosting_info->ip_address : '' ?>">
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
                                <input type="date" name="expiry_date" class="form-control" value="<?= isset($hosting_info) ? $hosting_info->expiry_date : '' ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Plan <span class="text-danger">*</span></label>
                                <select name="plan" class="form-control" required>
                                    <option value="">Select Plan</option>
                                    <option value="Basic" <?= isset($hosting_info) && $hosting_info->plan == 'Basic' ? 'selected' : '' ?>>Basic</option>
                                    <option value="Standard" <?= isset($hosting_info) && $hosting_info->plan == 'Standard' ? 'selected' : '' ?>>Standard</option>
                                    <option value="Professional" <?= isset($hosting_info) && $hosting_info->plan == 'Professional' ? 'selected' : '' ?>>Professional</option>
                                    <option value="Enterprise" <?= isset($hosting_info) && $hosting_info->plan == 'Enterprise' ? 'selected' : '' ?>>Enterprise</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Price</label>
                                <input type="number" name="price" class="form-control" value="<?= isset($hosting_info) ? $hosting_info->price : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Project</label>
                                <select name="project_id" class="form-control">
                                    <option value="">Select Project</option>
                                    <?php if (!empty($projects)): ?>
                                        <?php foreach ($projects as $project): ?>
                                            <option value="<?= $project['project_id'] ?>" <?= isset($hosting_info) && $hosting_info->project_id == $project['project_id'] ? 'selected' : '' ?>><?= $project['project_name'] ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Client</label>
                                <div class="input-group">
                                    <select name="client_id" class="form-control">
                                        <option value="">Select Client</option>
                                        <?php if (!empty($clients)): ?>
                                            <?php foreach ($clients as $client): ?>
                                                <option value="<?= $client['client_id'] ?>" <?= isset($hosting_info) && $hosting_info->client_id == $client['client_id'] ? 'selected' : '' ?>><?= $client['name'] ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button">Add</button>
                                    </span>
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

<script>
$(document).ready(function() {
    var csrfToken = '<?= $this->security->get_csrf_hash() ?>';
    
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

    $('#provider_id').change(function() {
        var provider_id = $(this).val();
        if (provider_id) {
            $.ajax({
                url: '<?= base_url('admin/server_management/fetch_hosting_provider_url') ?>',
                type: 'POST',
                data: { provider_id: provider_id, csrf_token: csrfToken },
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
});
</script>