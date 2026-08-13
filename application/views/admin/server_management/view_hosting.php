<div class="modal-header bg-primary text-white" style="border-top-left-radius: 8px; border-top-right-radius: 8px;">
    <button type="button" class="close text-white" data-dismiss="modal" style="opacity: 0.8;">&times;</button>
    <h4 class="modal-title" style="font-weight: 600;"><i class="fa fa-server mr-2"></i> <?= htmlspecialchars($hosting->title) ?></h4>
</div>
<div class="modal-body pb-0" style="background-color: #fbfcfd;">
    <div class="row">
        <!-- Main Info -->
        <div class="col-md-7">
            <div class="panel panel-default border-none shadow-sm mb-4" style="border-radius: 10px; overflow: hidden;">
                <div class="panel-heading bg-white border-none px-4 pt-4 pb-0">
                    <h5 class="text-uppercase text-muted font-bold m-0" style="font-size: 11px; letter-spacing: 1.2px;">Hosting Details</h5>
                </div>
                <div class="panel-body px-4 pb-4">
                    <table class="table table-details m-0">
                        <tr>
                            <td class="col-xs-5 text-muted border-none p-v-sm">Provider</td>
                            <td class="col-xs-7 font-bold border-none p-v-sm text-dark">
                                <?= !empty($hosting->provider) ? htmlspecialchars($hosting->provider) : '<span class="text-muted">-</span>' ?>
                                <?php if (!empty($hosting->provider_url)): ?>
                                    <a href="<?= (preg_match('#^[^/:]+://#', $hosting->provider_url)) ? $hosting->provider_url : 'http://' . $hosting->provider_url ?>" target="_blank" class="ml-2 text-info" title="Visit Provider"><i class="fa fa-external-link"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-sm">Server Name</td>
                            <td class="border-none p-v-sm font-bold text-dark"><?= !empty($hosting->server_name) ? htmlspecialchars($hosting->server_name) : '<span class="text-muted italic">-</span>' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-sm">Server Type</td>
                            <td class="border-none p-v-sm text-dark"><span class="badge badge-soft-info"><?= htmlspecialchars($hosting->server_type) ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-sm">Status</td>
                            <td class="border-none p-v-sm text-dark">
                                <?php
                                $badge_class = 'badge-soft-secondary';
                                $v_status = $hosting->status;
                                if (!empty($hosting->purchase_date) && strtotime($hosting->purchase_date) < strtotime(date('Y-m-d')) && $v_status != 'Cancelled' && $v_status != 'Suspended') {
                                    $v_status = 'Expired';
                                }
                                switch ($v_status) {
                                    case 'Active': $badge_class = 'badge-soft-success'; break;
                                    case 'Expiring': $badge_class = 'badge-soft-warning'; break;
                                    case 'Expired': $badge_class = 'badge-soft-danger'; break;
                                    case 'Pending': $badge_class = 'badge-soft-warning'; break;
                                    case 'Suspended': $badge_class = 'badge-soft-info'; break;
                                    case 'Cancelled': $badge_class = 'badge-soft-danger'; break;
                                }
                                ?>
                                <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($v_status) ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-sm">Location</td>
                            <td class="border-none p-v-sm text-dark"><?= !empty($hosting->server_location) ? '<i class="fa fa-map-marker mr-1 text-muted"></i> ' . htmlspecialchars($hosting->server_location) : '<span class="text-muted">-</span>' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-sm">IP Address</td>
                            <td class="border-none p-v-sm font-bold text-primary"><?= !empty($hosting->ip_address) ? htmlspecialchars($hosting->ip_address) : '<span class="text-muted">-</span>' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-sm">Main Domain</td>
                            <td class="border-none p-v-sm text-dark font-bold"><?= !empty($hosting->main_domain_name) ? htmlspecialchars($hosting->main_domain_name) : '<span class="text-muted">-</span>' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-sm">Hosted Domains</td>
                            <td class="border-none p-v-sm text-dark font-bold"><?= !empty($hosting->hosted_domains_names) ? htmlspecialchars($hosting->hosted_domains_names) : '<span class="text-muted">-</span>' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-sm">Projects</td>
                            <td class="border-none p-v-sm small text-dark"><?= !empty($hosting->projects_names) ? htmlspecialchars($hosting->projects_names) : '<span class="text-muted">-</span>' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-sm">Clients</td>
                            <td class="border-none p-v-sm small text-dark"><?= !empty($hosting->clients_names) ? htmlspecialchars($hosting->clients_names) : '<span class="text-muted">-</span>' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-sm">Registered Date</td>
                            <td class="border-none p-v-sm font-bold text-dark"><?= !empty($hosting->date) ? date('d M, Y', strtotime($hosting->date)) : '<span class="text-muted">-</span>' ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="panel panel-default border-none shadow-sm mb-4" style="border-radius: 10px; overflow: hidden;">
                <div class="panel-heading bg-white border-none px-4 pt-4 pb-0">
                    <h5 class="text-uppercase text-muted font-bold m-0" style="font-size: 11px; letter-spacing: 1.2px;">Dates & Billing</h5>
                </div>
                <div class="panel-body px-4 pb-4">
                    <div class="row mb-4">
                        <div class="col-xs-4 pr-1">
                            <div class="p-3 bg-light rounded shadow-xs text-center border">
                                <div class="text-muted xsmall text-uppercase mb-1" style="letter-spacing: 0.5px;">Exp Date</div>
                                <div class="font-bold text-dark h5 m-0"><?= !empty($hosting->purchase_date) ? date('d M, Y', strtotime($hosting->purchase_date)) : '-' ?></div>
                            </div>
                        </div>
                        <div class="col-xs-4 px-1">
                            <div class="p-3 bg-soft-danger-light rounded shadow-xs text-center border border-danger-soft">
                                <div class="text-danger xsmall text-uppercase mb-1" style="letter-spacing: 0.5px;">Future Exp Date</div>
                                <div class="font-bold text-danger h5 m-0"><?= !empty($hosting->expiry_date) ? date('d M, Y', strtotime($hosting->expiry_date)) : '-' ?></div>
                            </div>
                        </div>
                        <div class="col-xs-4 pl-1">
                            <div class="p-3 bg-light rounded shadow-xs text-center border">
                                <div class="text-muted xsmall text-uppercase mb-1" style="letter-spacing: 0.5px;">Price</div>
                                <div class="font-bold text-success h5 m-0"><?= $hosting->price ?> <?= !empty($hosting->currency_symbol) ? $hosting->currency_symbol : $hosting->currency_id ?></div>
                            </div>
                        </div>
                    </div>
                    <table class="table table-details m-0">
                        <tr>
                            <td class="col-xs-5 text-muted border-none p-v-sm">Renewal Cycle</td>
                            <td class="col-xs-7 font-bold border-none p-v-sm text-dark"><?= $hosting->days ?> <?= $hosting->time_unit ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-sm">Renewal Mode</td>
                            <td class="border-none p-v-sm">
                                <span class="badge badge-soft-<?= $hosting->renew == 'automatic' ? 'success' : 'warning' ?>">
                                    <i class="fa <?= $hosting->renew == 'automatic' ? 'fa-refresh' : 'fa-hand-paper-o' ?> mr-1"></i>
                                    <?= ucfirst($hosting->renew) ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="panel panel-default border-none shadow-none mt-2">
                <div class="panel-heading bg-transparent border-none p-0 mb-3">
                    <h5 class="text-uppercase text-muted font-bold m-0" style="font-size: 11px; letter-spacing: 1.2px;">SSL & Notification</h5>
                </div>
                <div class="panel-body p-0">
                    <div class="feature-item d-flex align-items-center mb-2 p-2 rounded <?= $hosting->ssl_certificate ? 'bg-soft-success-light border-success-soft' : '' ?>">
                        <div class="feature-icon"><i class="fa <?= $hosting->ssl_certificate ? 'fa-shield text-success' : 'fa-times-circle text-muted' ?>"></i></div>
                        <div class="ml-3 small text-muted">SSL Certificate: 
                            <span class="font-bold text-dark"><?= $hosting->ssl_certificate ? 'Active' : 'No' ?></span>
                            <?php if ($hosting->ssl_certificate): ?>
                                <span class="ml-2 text-info font-bold">(Exp: <?= !empty($hosting->ssl_expiry_date) ? date('d M, Y', strtotime($hosting->ssl_expiry_date)) : 'N/A' ?>)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($hosting->ssl_certificate && !empty($hosting->ssl_type)): ?>
                    <div class="ml-5 small text-muted mb-3"><i class="fa fa-caret-right mr-1"></i> SSL Type: <span class="text-dark font-bold"><?= htmlspecialchars($hosting->ssl_type) ?></span></div>
                    <?php endif; ?>

                    <div class="feature-item d-flex align-items-center p-2 rounded bg-soft-warning-light border-warning-soft">
                        <div class="feature-icon"><i class="fa <?= $hosting->expiry_notification ? 'fa-bell text-warning' : 'fa-bell-slash-o text-muted' ?>"></i></div>
                        <div class="ml-3 small text-muted">
                            Notification: <span class="font-bold text-dark"><?= $hosting->expiry_notification ? 'Enabled' : 'Disabled' ?></span>
                            <?php if ($hosting->expiry_notification): ?>
                                <div class="mt-1 xsmall text-muted italic">(<?= $hosting->notification_days ?> <?= $hosting->notification_time_unit ?> before expiry)</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar / Access Info -->
        <div class="col-md-5">
            <div class="panel panel-default bg-light-blue-50 border-none rounded-lg shadow-sm mb-4">
                <div class="panel-body p-4">
                    <h5 class="text-uppercase text-primary font-bold m-b-md" style="font-size: 11px; letter-spacing: 1.2px;">Hosting Access</h5>
                    
                    <div class="mb-3">
                        <label class="text-muted small m-b-xs d-block">Username</label>
                        <div class="input-group input-group-sm shadow-xs">
                            <input type="text" class="form-control bg-white border-none" value="<?= htmlspecialchars($hosting->username) ?>" readonly id="view_host_user_<?= $hosting->id ?>">
                            <span class="input-group-btn">
                                <button class="btn btn-white copy-btn border-none" data-clipboard-target="#view_host_user_<?= $hosting->id ?>" type="button"><i class="fa fa-copy"></i></button>
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="text-muted small m-b-xs d-block">Password</label>
                        <div class="input-group input-group-sm shadow-xs">
                            <input type="password" class="form-control bg-white border-none" value="<?= htmlspecialchars($hosting->password) ?>" readonly id="view_host_pass_<?= $hosting->id ?>">
                            <span class="input-group-btn">
                                <button class="btn btn-white toggle-view-pass border-none" type="button"><i class="fa fa-eye"></i></button>
                                <button class="btn btn-white copy-btn border-none" data-clipboard-target="#view_host_pass_<?= $hosting->id ?>" type="button"><i class="fa fa-copy"></i></button>
                            </span>
                        </div>
                    </div>

                    <div class="mb-4 small d-flex align-items-center justify-content-between bg-white p-2 rounded shadow-xs border">
                        <span class="text-muted">cPanel URL:</span> 
                        <?php if (!empty($hosting->cpanel_url)): ?>
                            <a href="<?= (preg_match('#^[^/:]+://#', $hosting->cpanel_url)) ? $hosting->cpanel_url : 'http://' . $hosting->cpanel_url ?>" target="_blank" class="text-info font-bold text-truncate ml-2" style="max-width: 150px;"><?= $hosting->cpanel_url ?></a>
                        <?php else: ?>
                            <span class="text-muted italic">N/A</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-4 small d-flex align-items-center justify-content-between bg-white p-2 rounded shadow-xs border">
                        <span class="text-muted">Hostname:</span> 
                        <span class="font-bold text-dark ml-2 text-truncate" style="max-width: 150px;"><?= $hosting->hostname ?: 'N/A' ?></span>
                    </div>

                    <div class="separator mb-4"></div>

                    <h5 class="text-uppercase text-primary font-bold m-b-md" style="font-size: 11px; letter-spacing: 1.2px;">FTP Credentials</h5>
                    <div class="mb-3">
                        <label class="text-muted small m-b-xs d-block">FTP Username</label>
                        <div class="input-group input-group-sm shadow-xs">
                            <input type="text" class="form-control bg-white border-none" value="<?= htmlspecialchars($hosting->ftp_username) ?>" readonly id="view_ftp_user_<?= $hosting->id ?>">
                            <span class="input-group-btn">
                                <button class="btn btn-white copy-btn border-none" data-clipboard-target="#view_ftp_user_<?= $hosting->id ?>" type="button"><i class="fa fa-copy"></i></button>
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="text-muted small m-b-xs d-block">FTP Password</label>
                        <div class="input-group input-group-sm shadow-xs">
                            <input type="password" class="form-control bg-white border-none" value="<?= htmlspecialchars($hosting->ftp_password) ?>" readonly id="view_ftp_pass_<?= $hosting->id ?>">
                            <span class="input-group-btn">
                                <button class="btn btn-white toggle-view-pass border-none" type="button"><i class="fa fa-eye"></i></button>
                                <button class="btn btn-white copy-btn border-none" data-clipboard-target="#view_ftp_pass_<?= $hosting->id ?>" type="button"><i class="fa fa-copy"></i></button>
                            </span>
                        </div>
                    </div>

                    <div class="separator mb-4"></div>

                    <h5 class="text-uppercase text-primary font-bold m-b-md" style="font-size: 11px; letter-spacing: 1.2px;">DNS Provider</h5>
                    <div class="mb-2 small d-flex justify-content-between"><span class="text-muted">Name:</span> <span class="font-bold text-dark"><?= $hosting->dns_provider_name ?: '-' ?></span></div>
                    <div class="mb-2 small d-flex justify-content-between"><span class="text-muted">Email:</span> <span class="font-bold text-dark"><?= $hosting->dns_email ?: '-' ?></span></div>
                    <div class="mb-2 small d-flex justify-content-between">
                        <span class="text-muted">Password:</span> 
                        <div class="d-flex align-items-center">
                            <span class="password-mask font-bold text-dark mr-2" data-pass="<?= htmlspecialchars($hosting->dns_password) ?>">********</span>
                            <a href="javascript:void(0)" class="toggle-mask-pass text-info"><i class="fa fa-eye"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default border-none shadow-sm mt-2">
                <div class="panel-heading bg-transparent border-none p-0 mb-3">
                    <h5 class="text-uppercase text-muted font-bold m-0" style="font-size: 11px; letter-spacing: 1.2px;">Server Nameservers</h5>
                </div>
                <div class="panel-body p-0">
                    <div class="d-flex flex-wrap">
                        <?php if (!empty($hosting->nameservers)): ?>
                            <?php foreach (explode(',', $hosting->nameservers) as $ns): ?>
                                <div class="ns-badge m-r-xs m-b-xs small">
                                    <i class="fa fa-server mr-1 text-muted"></i> <?= htmlspecialchars(trim($ns)) ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center p-3 border border-dashed rounded text-muted italic w-100">None specified</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Additional Custom Fields -->
    <?php
    $custom_fields = !empty($hosting->custom_fields) ? json_decode($hosting->custom_fields, true) : [];
    if (!empty($custom_fields)):
    ?>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="panel panel-default border-none shadow-sm" style="border-radius: 10px; overflow: hidden;">
                <div class="panel-heading bg-white border-none px-4 pt-4 pb-0">
                    <h5 class="text-uppercase text-muted font-bold m-0" style="font-size: 11px; letter-spacing: 1.2px;">Additional Information</h5>
                </div>
                <div class="panel-body p-4">
                    <div class="row">
                        <?php foreach ($custom_fields as $field): ?>
                            <div class="col-md-6 mb-3">
                                <div class="bg-white p-2 rounded shadow-xs border d-flex justify-content-between align-items-center" style="min-height: 40px;">
                                    <span class="text-muted small"><?= htmlspecialchars($field['label']) ?>:</span>
                                    <?php if (isset($field['type']) && $field['type'] == 'password'): ?>
                                        <div class="d-flex align-items-center">
                                            <span class="password-mask font-bold text-dark mr-2" data-pass="<?= htmlspecialchars($field['value']) ?>">********</span>
                                            <a href="javascript:void(0)" class="toggle-mask-pass text-info"><i class="fa fa-eye"></i></a>
                                        </div>
                                    <?php elseif (isset($field['type']) && $field['type'] == 'file'): ?>
                                        <span class="font-bold text-dark ml-2">
                                            <?php if (!empty($field['value'])): ?>
                                                <a href="<?= base_url($field['value']) ?>" target="_blank" class="text-info"><i class="fa fa-download"></i> Download</a>
                                            <?php else: ?>
                                                <span class="text-muted italic">No file</span>
                                            <?php endif; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="font-bold text-dark ml-2"><?= htmlspecialchars($field['value']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Description -->
    <?php if (!empty($hosting->description)): ?>
    <div class="row mt-4 mb-4">
        <div class="col-md-12">
            <div class="panel panel-default border-none shadow-sm" style="border-radius: 10px; overflow: hidden;">
                <div class="panel-heading bg-white border-none px-4 pt-4 pb-0">
                    <h5 class="text-uppercase text-muted font-bold m-0" style="font-size: 11px; letter-spacing: 1.2px;">Hosting Notes</h5>
                </div>
                <div class="panel-body p-4 italic text-muted" style="line-height: 1.6; background-color: #fcfdfe;">
                    <?= nl2br(htmlspecialchars($hosting->description)) ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<div class="modal-footer bg-light border-none px-4 py-3" style="border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
    <button type="button" class="btn btn-link text-muted" data-dismiss="modal" style="text-decoration: none;">Close</button>
    <a href="<?= base_url('admin/server_management/add_hosting/' . $hosting->id) ?>" class="btn btn-primary px-4 shadow-sm" style="border-radius: 6px; font-weight: 600;"><i class="fa fa-pencil mr-2"></i> Edit Hosting</a>
</div>

<style>
    /* Premium Utilities */
    .bg-light-blue-50 { background-color: #f4f8fd; }
    .bg-soft-danger-light { background-color: #fff5f5; }
    .bg-soft-warning-light { background-color: #fffcf0; }
    .bg-soft-success-light { background-color: #f0fff4; }
    .border-none { border: none !important; }
    .border-dashed { border-style: dashed !important; }
    .border-danger-soft { border-color: #feb2b2 !important; }
    .border-warning-soft { border-color: #faf089 !important; }
    .border-success-soft { border-color: #9ae6b4 !important; }
    .shadow-none { box-shadow: none !important; }
    .shadow-sm { box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.03) !important; }
    .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important; }
    .rounded-lg { border-radius: 10px !important; }
    .p-v-sm { padding-top: 8px !important; padding-bottom: 8px !important; }
    .m-b-md { margin-bottom: 15px !important; }
    .m-b-xs { margin-bottom: 5px !important; }
    .m-r-xs { margin-right: 8px !important; }
    .mr-1 { margin-right: 4px !important; }
    .mr-2 { margin-right: 8px !important; }
    .ml-1 { margin-left: 4px !important; }
    .ml-2 { margin-left: 8px !important; }
    .ml-3 { margin-left: 12px !important; }
    .ml-5 { margin-left: 20px !important; }
    .mt-2 { margin-top: 10px !important; }
    .mt-4 { margin-top: 20px !important; }
    .mb-2 { margin-bottom: 8px !important; }
    .mb-3 { margin-bottom: 12px !important; }
    .mb-4 { margin-bottom: 20px !important; }
    
    /* Flexbox Shims */
    .d-flex { display: flex !important; }
    .flex-wrap { flex-wrap: wrap !important; }
    .align-items-center { align-items: center !important; }
    .justify-content-between { justify-content: space-between !important; }
    
    /* Components */
    .table-details td { vertical-align: middle; font-size: 13px; }
    .font-bold { font-weight: 600; }
    .italic { font-style: italic; }
    .separator { height: 1px; background: linear-gradient(to right, rgba(0,0,0,0), rgba(0,0,0,0.05), rgba(0,0,0,0)); }
    
    .ns-badge { 
        background: #fff; 
        border: 1px solid #e2e8f0; 
        padding: 5px 10px; 
        border-radius: 6px; 
        font-size: 11px; 
        color: #4a5568;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    
    .feature-icon { width: 24px; text-align: center; font-size: 16px; }
    
    /* Badge Styling */
    .badge { padding: 5px 10px; border-radius: 4px; font-weight: 600; font-size: 11px; }
    .badge-soft-success { background-color: #c6f6d5; color: #22543d; }
    .badge-soft-danger { background-color: #fed7d7; color: #822727; }
    .badge-soft-warning { background-color: #fefcbf; color: #744210; }
    .badge-soft-info { background-color: #bee3f8; color: #2a4365; }
    .badge-soft-secondary { background-color: #e2e8f0; color: #2d3748; }

    .btn-white { background: #fff; color: #4a5568; border: 1px solid #e2e8f0; }
    .btn-white:hover { background: #f7fafc; }
    
    .xsmall { font-size: 11px; }
    .password-mask { font-family: monospace; letter-spacing: 1px; }
</style>

<script>
    $('.toggle-view-pass').click(function() {
        var input = $(this).closest('.input-group').find('input');
        var type = input.attr('type') === 'password' ? 'text' : 'password';
        input.attr('type', type);
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });

    $('.toggle-mask-pass').click(function() {
        var span = $(this).siblings('.password-mask');
        var pass = span.data('pass');
        var isMasked = span.text() === '********';
        
        if (isMasked) {
            span.text(pass);
            $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            span.text('********');
            $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    $('.copy-btn').click(function() {
        var target = $($(this).data('clipboard-target'));
        target.select();
        document.execCommand("copy");
        
        var btn = $(this);
        var originalHtml = btn.html();
        btn.html('<i class="fa fa-check text-success"></i>');
        setTimeout(function() {
            btn.html(originalHtml);
        }, 1500);
    });
</script>
