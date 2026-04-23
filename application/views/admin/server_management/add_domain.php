<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="post" action="<?= base_url('admin/server_management/add_domain' . (!empty($domain_info) ? '/' . $domain_info->id : '')) ?>">
                    <!-- Row 1 -->
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
                                    <span class="input-group-btn">
                                        <a href="<?= base_url('admin/server_management/add_provider') ?>" class="btn btn-default" target="_blank" title="Add New Provider"><i class="fa fa-plus"></i></a>
                                    </span>
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
                                <select name="domain_type" class="form-control" required>
                                    <option value="">Select Type</option>
                                    <option value="COM" <?= !empty($domain_info) && $domain_info->domain_type == 'COM' ? 'selected' : '' ?>>COM</option>
                                    <option value="NET" <?= !empty($domain_info) && $domain_info->domain_type == 'NET' ? 'selected' : '' ?>>NET</option>
                                    <option value="ORG" <?= !empty($domain_info) && $domain_info->domain_type == 'ORG' ? 'selected' : '' ?>>ORG</option>
                                    <option value="IO" <?= !empty($domain_info) && $domain_info->domain_type == 'IO' ? 'selected' : '' ?>>IO</option>
                                    <option value="DEV" <?= !empty($domain_info) && $domain_info->domain_type == 'DEV' ? 'selected' : '' ?>>DEV</option>
                                    <option value="TECH" <?= !empty($domain_info) && $domain_info->domain_type == 'TECH' ? 'selected' : '' ?>>TECH</option>
                                    <option value="CO" <?= !empty($domain_info) && $domain_info->domain_type == 'CO' ? 'selected' : '' ?>>CO</option>
                                    <option value="APP" <?= !empty($domain_info) && $domain_info->domain_type == 'APP' ? 'selected' : '' ?>>APP</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2 -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Hosting</label>
                                <select name="hosting_id" class="form-control">
                                    <option value="">Select Hosting</option>
                                    <?php if (!empty($hostings)): ?>
                                        <?php foreach ($hostings as $hosting): ?>
                                            <option value="<?= $hosting['id'] ?>" <?= !empty($domain_info) && $domain_info->hosting_id == $hosting['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($hosting['hosting_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
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
                                    <span class="input-group-btn">
                                        <button class="btn btn-default toggle-password" type="button"><i class="fa fa-eye"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-control" required>
                                    <option value="">Select Status</option>
                                    <option value="Active" <?= !empty($domain_info) && $domain_info->status == 'Active' ? 'selected' : '' ?>>Active</option>
                                    <option value="Pending" <?= !empty($domain_info) && $domain_info->status == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Transferring" <?= !empty($domain_info) && $domain_info->status == 'Transferring' ? 'selected' : '' ?>>Transferring</option>
                                    <option value="Expired" <?= !empty($domain_info) && $domain_info->status == 'Expired' ? 'selected' : '' ?>>Expired</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Row 3 -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Purchase Date <span class="text-danger">*</span></label>
                                <input type="date" name="purchase_date" class="form-control" value="<?= !empty($domain_info) ? $domain_info->purchase_date : '' ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Expiry Date <span class="text-danger">*</span></label>
                                <input type="date" name="expiry_date" class="form-control" value="<?= !empty($domain_info) ? $domain_info->expiry_date : '' ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Price</label>
                                <input type="number" name="price" step="0.01" class="form-control" value="<?= !empty($domain_info) ? $domain_info->price : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Plan <span class="text-danger">*</span></label>
                                <select name="plan" class="form-control" required>
                                    <option value="">Select Plan</option>
                                    <option value="Basic" <?= !empty($domain_info) && $domain_info->plan == 'Basic' ? 'selected' : '' ?>>Basic</option>
                                    <option value="Standard" <?= !empty($domain_info) && $domain_info->plan == 'Standard' ? 'selected' : '' ?>>Standard</option>
                                    <option value="Professional" <?= !empty($domain_info) && $domain_info->plan == 'Professional' ? 'selected' : '' ?>>Professional</option>
                                    <option value="Enterprise" <?= !empty($domain_info) && $domain_info->plan == 'Enterprise' ? 'selected' : '' ?>>Enterprise</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Row 4 -->
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
                                    <span class="input-group-btn">
                                        <button class="btn btn-default toggle-password" type="button"><i class="fa fa-eye"></i></button>
                                    </span>
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

                    <!-- Row 5 -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Project</label>
                                <select name="project_id" class="form-control">
                                    <option value="">Select Project</option>
                                    <?php if (!empty($projects)): ?>
                                        <?php foreach ($projects as $project): ?>
                                            <option value="<?= $project['project_id'] ?>" <?= !empty($domain_info) && $domain_info->project_id == $project['project_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($project['project_name']) ?>
                                            </option>
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
                                                <option value="<?= $client['client_id'] ?>" <?= !empty($domain_info) && $domain_info->client_id == $client['client_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($client['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- General Toggles -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" name="auto_renewal" class="custom-control-input" id="auto_renewal" value="1" <?= !empty($domain_info) && $domain_info->auto_renewal == 1 ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="auto_renewal">Auto Renewal</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" name="whois_protection" class="custom-control-input" id="whois_protection" value="1" <?= !empty($domain_info) && $domain_info->whois_protection == 1 ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="whois_protection">WHOIS Protection</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notification Settings -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" name="expiry_notification" class="custom-control-input" id="expiry_notification" value="1" <?= !empty($domain_info) && $domain_info->expiry_notification == 1 ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="expiry_notification">Expiry Notification</label>
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

                    <!-- Description -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Enter description..."><?= !empty($domain_info) ? htmlspecialchars($domain_info->description) : '' ?></textarea>
                            </div>
                        </div>
                    </div>

<script>
$(document).ready(function() {
    var csrfToken = '<?= $this->security->get_csrf_hash() ?>';
    
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

                            $('.toggle-password').click(function() {
                                var input = $(this).closest('.input-group').find('input');
                                var type = input.attr('type') === 'password' ? 'text' : 'password';
                                input.attr('type', type);
                                $(this).find('i').toggleClass('fa-eye fa-eye-slash');
                            });
                        });
                    </script>

                    <!-- Footer -->
                    <div class="card-footer text-left">
                        <a href="<?= base_url('admin/server_management/domain') ?>" class="btn btn-link">Cancel</a>
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>