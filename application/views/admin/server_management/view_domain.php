<div class="modal-header bg-primary text-white" style="border-top-left-radius: 8px; border-top-right-radius: 8px;">
    <button type="button" class="close text-white" data-dismiss="modal" style="opacity: 0.8;">&times;</button>
    <h4 class="modal-title" style="font-weight: 600;"><i class="fa fa-globe mr-2"></i> <?= htmlspecialchars($domain->domain_name) ?></h4>
</div>
<div class="modal-body pb-0" style="background-color: #fbfcfd;">
    <div class="row">
        <!-- Main Info -->
        <div class="col-md-7">
            <div class="panel panel-default border-none shadow-sm mb-4" style="border-radius: 10px; overflow: hidden;">
                <div class="panel-heading bg-white border-none px-4 pt-4 pb-0">
                    <h5 class="text-uppercase text-muted font-bold m-0" style="font-size: 11px; letter-spacing: 1.2px;">General Information</h5>
                </div>
                <div class="panel-body px-4 pb-4">
                    <table class="table table-details m-0">
                        <tr>
                            <td class="col-xs-5 text-muted border-none p-v-sm">Provider</td>
                            <td class="col-xs-7 font-bold border-none p-v-sm text-dark">
                                <?= !empty($domain->provider) ? htmlspecialchars($domain->provider) : '<span class="text-muted">-</span>' ?>
                                <?php if (!empty($domain->provider_url)): ?>
                                    <a href="<?= (preg_match('#^[^/:]+://#', $domain->provider_url)) ? $domain->provider_url : 'http://' . $domain->provider_url ?>" target="_blank" class="ml-2 text-info" title="Visit Provider"><i class="fa fa-external-link"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-sm">Domain Type</td>
                            <td class="border-none p-v-sm text-dark"><span class="badge badge-soft-info"><?= htmlspecialchars($domain->domain_type) ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-sm">Status</td>
                            <td class="border-none p-v-sm text-dark">
                                <?php
                                $badge_class = 'badge-soft-secondary';
                                switch ($domain->status) {
                                    case 'Active': $badge_class = 'badge-soft-success'; break;
                                    case 'Expired': $badge_class = 'badge-soft-danger'; break;
                                    case 'Pending': $badge_class = 'badge-soft-warning'; break;
                                    case 'Transferring': $badge_class = 'badge-soft-info'; break;
                                    case 'Expiring': $badge_class = 'badge-soft-warning'; break;
                                }
                                ?>
                                <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($domain->status) ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-sm">Hosting</td>
                            <td class="border-none p-v-sm text-dark"><?= !empty($domain->hosting) ? '<span class="font-bold">' . htmlspecialchars($domain->hosting) . '</span>' : '<span class="text-muted italic">Not Assigned</span>' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-sm">Project</td>
                            <td class="border-none p-v-sm text-dark"><?= !empty($domain->project) ? htmlspecialchars($domain->project) : '<span class="text-muted">-</span>' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-sm">Client</td>
                            <td class="border-none p-v-sm text-dark"><?= !empty($domain->client_name) ? htmlspecialchars($domain->client_name) : '<span class="text-muted">-</span>' ?></td>
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
                                <div class="text-muted xsmall text-uppercase mb-1" style="letter-spacing: 0.5px;">Registered</div>
                                <div class="font-bold text-dark h5 m-0"><?= !empty($domain->date) ? date('d M, Y', strtotime($domain->date)) : '-' ?></div>
                            </div>
                        </div>
                        <div class="col-xs-4 px-1">
                            <div class="p-3 bg-light rounded shadow-xs text-center border">
                                <div class="text-muted xsmall text-uppercase mb-1" style="letter-spacing: 0.5px;">Exp Date</div>
                                <div class="font-bold text-dark h5 m-0"><?= !empty($domain->purchase_date) ? date('d M, Y', strtotime($domain->purchase_date)) : '-' ?></div>
                            </div>
                        </div>
                        <div class="col-xs-4 pl-1">
                            <div class="p-3 bg-soft-danger-light rounded shadow-xs text-center border border-danger-soft">
                                <div class="text-danger xsmall text-uppercase mb-1" style="letter-spacing: 0.5px;">Future Exp Date</div>
                                <div class="font-bold text-danger h5 m-0"><?= !empty($domain->expiry_date) ? date('d M, Y', strtotime($domain->expiry_date)) : '-' ?></div>
                            </div>
                        </div>
                    </div>
                    <table class="table table-details m-0">
                        <tr>
                            <td class="col-xs-5 text-muted border-none p-v-sm">Duration</td>
                            <td class="col-xs-7 font-bold border-none p-v-sm text-dark"><?= $domain->days ?> <?= $domain->time_unit ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-sm">Price</td>
                            <td class="border-none p-v-sm font-bold text-success" style="font-size: 16px;"><?= $domain->price ?> <?= $domain->currency_id ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar / Additional Info -->
        <div class="col-md-5">
            <div class="panel panel-default bg-light-blue-50 border-none rounded-lg shadow-sm mb-4">
                <div class="panel-body p-4">
                    <h5 class="text-uppercase text-primary font-bold m-b-md" style="font-size: 11px; letter-spacing: 1.2px;">Access Details</h5>
                    
                    <div class="mb-3">
                        <label class="text-muted small m-b-xs d-block">Username</label>
                        <div class="input-group input-group-sm shadow-xs">
                            <input type="text" class="form-control bg-white border-none" value="<?= htmlspecialchars($domain->username) ?>" readonly id="view_user_<?= $domain->id ?>">
                            <span class="input-group-btn">
                                <button class="btn btn-white copy-btn border-none" data-clipboard-target="#view_user_<?= $domain->id ?>" type="button"><i class="fa fa-copy"></i></button>
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="text-muted small m-b-xs d-block">Password</label>
                        <div class="input-group input-group-sm shadow-xs">
                            <input type="password" class="form-control bg-white border-none" value="<?= htmlspecialchars($domain->password) ?>" readonly id="view_pass_<?= $domain->id ?>">
                            <span class="input-group-btn">
                                <button class="btn btn-white toggle-view-pass border-none" type="button"><i class="fa fa-eye"></i></button>
                                <button class="btn btn-white copy-btn border-none" data-clipboard-target="#view_pass_<?= $domain->id ?>" type="button"><i class="fa fa-copy"></i></button>
                            </span>
                        </div>
                    </div>

                    <div class="separator mb-4"></div>

                    <h5 class="text-uppercase text-primary font-bold m-b-md" style="font-size: 11px; letter-spacing: 1.2px;">Registrar Info</h5>
                    
                    <div class="mb-3">
                        <label class="text-muted small m-b-xs d-block">Registrar URL</label>
                        <?php if (!empty($domain->registrar_url)): ?>
                            <div class="text-truncate">
                                <a href="<?= (preg_match('#^[^/:]+://#', $domain->registrar_url)) ? $domain->registrar_url : 'http://' . $domain->registrar_url ?>" target="_blank" class="text-info font-bold small"><i class="fa fa-link mr-1"></i> <?= $domain->registrar_url ?></a>
                            </div>
                        <?php else: ?>
                            <span class="text-muted small italic">No URL specified</span>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small m-b-xs d-block">Registrar Username</label>
                        <div class="input-group input-group-sm shadow-xs">
                            <input type="text" class="form-control bg-white border-none" value="<?= htmlspecialchars($domain->registrar_username) ?>" readonly id="view_reg_user_<?= $domain->id ?>">
                            <span class="input-group-btn">
                                <button class="btn btn-white copy-btn border-none" data-clipboard-target="#view_reg_user_<?= $domain->id ?>" type="button"><i class="fa fa-copy"></i></button>
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="text-muted small m-b-xs d-block">Registrar Password</label>
                        <div class="input-group input-group-sm shadow-xs">
                            <input type="password" class="form-control bg-white border-none" value="<?= htmlspecialchars($domain->registrar_password) ?>" readonly id="view_reg_pass_<?= $domain->id ?>">
                            <span class="input-group-btn">
                                <button class="btn btn-white toggle-view-pass border-none" type="button"><i class="fa fa-eye"></i></button>
                                <button class="btn btn-white copy-btn border-none" data-clipboard-target="#view_reg_pass_<?= $domain->id ?>" type="button"><i class="fa fa-copy"></i></button>
                            </span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between bg-white p-2 rounded shadow-xs border">
                        <span class="text-muted small">Registrar Status:</span> 
                        <span class="label label-default" style="border-radius: 4px;"><?= $domain->registrar_status ?: 'N/A' ?></span>
                    </div>
                </div>
            </div>

            <div class="panel panel-default border-none shadow-none mt-2">
                <div class="panel-heading bg-transparent border-none p-0 mb-3">
                    <h5 class="text-uppercase text-muted font-bold m-0" style="font-size: 11px; letter-spacing: 1.2px;">System Features</h5>
                </div>
                <div class="panel-body p-0">
                    <div class="feature-item d-flex align-items-center mb-2 p-2 rounded">
                        <div class="feature-icon"><i class="fa <?= $domain->auto_renewal ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i></div>
                        <div class="ml-3 small text-muted">Auto Renewal: <span class="font-bold text-dark"><?= $domain->auto_renewal ? 'Enabled' : 'Disabled' ?></span></div>
                    </div>
                    <div class="feature-item d-flex align-items-center mb-2 p-2 rounded">
                        <div class="feature-icon"><i class="fa <?= $domain->is_locked ? 'fa-lock text-danger' : 'fa-unlock text-success' ?>"></i></div>
                        <div class="ml-3 small text-muted">Locked Status: <span class="font-bold text-dark"><?= $domain->is_locked ? 'Locked' : 'Unlocked' ?></span></div>
                    </div>
                    <div class="feature-item d-flex align-items-center mb-2 p-2 rounded">
                        <div class="feature-icon"><i class="fa <?= $domain->is_for_sale ? 'fa-tag text-warning' : 'fa-circle-o text-muted' ?>"></i></div>
                        <div class="ml-3 small text-muted">For Sale: <span class="font-bold text-dark"><?= $domain->is_for_sale ? 'Yes' : 'No' ?></span></div>
                    </div>
                    <div class="feature-item d-flex align-items-center mb-2 p-2 rounded">
                        <div class="feature-icon"><i class="fa <?= $domain->whois_protection ? 'fa-shield text-info' : 'fa-times-circle text-muted' ?>"></i></div>
                        <div class="ml-3 small text-muted">WHOIS Privacy: <span class="font-bold text-dark"><?= $domain->whois_protection ? 'Protected' : 'No' ?></span></div>
                    </div>
                    <div class="feature-item d-flex align-items-center p-2 rounded bg-soft-warning-light border-warning-soft">
                        <div class="feature-icon"><i class="fa <?= $domain->expiry_notification ? 'fa-bell text-warning' : 'fa-bell-slash-o text-muted' ?>"></i></div>
                        <div class="ml-3 small text-muted">
                            Notification: <span class="font-bold text-dark"><?= $domain->expiry_notification ? 'Enabled' : 'Disabled' ?></span>
                            <?php if ($domain->expiry_notification): ?>
                                <div class="mt-1 xsmall text-muted italic">(<?= $domain->notification_days ?> <?= $domain->notification_time_unit ?> before expiry)</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Name Servers -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="panel panel-default border-none shadow-sm bg-white p-4 rounded-lg">
                <h5 class="text-uppercase text-muted font-bold m-b-md" style="font-size: 11px; letter-spacing: 1.2px;">Nameservers</h5>
                <?php if (!empty($domain->nameservers)): ?>
                    <div class="d-flex flex-wrap">
                        <?php foreach (explode(',', $domain->nameservers) as $ns): ?>
                            <div class="ns-badge m-r-sm m-b-sm">
                                <i class="fa fa-server mr-2 text-muted" style="font-size: 10px;"></i><?= htmlspecialchars(trim($ns)) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center p-3 border border-dashed rounded text-muted italic">No nameservers specified</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Custom Fields -->
    <?php 
    $custom_fields = !empty($domain->custom_fields) ? json_decode($domain->custom_fields, true) : array();
    if (!empty($custom_fields)): 
    ?>
    <div class="row mt-4">
        <div class="col-md-12">
            <h5 class="text-uppercase text-muted font-bold m-b-md px-1" style="font-size: 11px; letter-spacing: 1.2px;">Custom Fields</h5>
            <div class="row">
                <?php foreach ($custom_fields as $field): ?>
                    <div class="col-md-4 mb-3">
                        <div class="bg-white border rounded-lg p-3 shadow-xs">
                            <label class="text-muted xsmall m-0 d-block text-uppercase" style="letter-spacing: 0.5px;"><?= htmlspecialchars($field['label']) ?></label>
                            <?php if (isset($field['type']) && $field['type'] == 'password'): ?>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="password-mask font-bold text-dark mr-2" data-pass="<?= htmlspecialchars($field['value']) ?>">********</span>
                                    <a href="javascript:void(0)" class="toggle-mask-pass text-info"><i class="fa fa-eye"></i></a>
                                </div>
                            <?php elseif (isset($field['type']) && $field['type'] == 'file'): ?>
                                <div class="font-bold text-dark">
                                    <?php if (!empty($field['value'])): ?>
                                        <a href="<?= base_url($field['value']) ?>" target="_blank" class="text-info"><i class="fa fa-download"></i> Download</a>
                                    <?php else: ?>
                                        <span class="text-muted italic">No file</span>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="font-bold text-dark"><?= htmlspecialchars($field['value']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Description -->
    <?php if (!empty($domain->description)): ?>
    <div class="row mt-4 mb-4">
        <div class="col-md-12">
            <div class="panel panel-default border-none shadow-sm" style="border-radius: 10px; overflow: hidden;">
                <div class="panel-heading bg-white border-none px-4 pt-4 pb-0">
                    <h5 class="text-uppercase text-muted font-bold m-0" style="font-size: 11px; letter-spacing: 1.2px;">Additional Notes</h5>
                </div>
                <div class="panel-body p-4 italic text-muted" style="line-height: 1.6; background-color: #fcfdfe;">
                    <?= nl2br(htmlspecialchars($domain->description)) ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<div class="modal-footer bg-light border-none px-4 py-3" style="border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
    <button type="button" class="btn btn-link text-muted" data-dismiss="modal" style="text-decoration: none;">Close</button>
    <a href="<?= base_url('admin/server_management/add_domain/' . $domain->id) ?>" class="btn btn-primary px-4 shadow-sm" style="border-radius: 6px; font-weight: 600;"><i class="fa fa-pencil mr-2"></i> Edit Domain</a>
</div>

<style>
    /* Premium Utilities */
    .bg-light-blue-50 { background-color: #f4f8fd; }
    .bg-soft-danger-light { background-color: #fff5f5; }
    .bg-soft-warning-light { background-color: #fffcf0; }
    .border-none { border: none !important; }
    .border-dashed { border-style: dashed !important; }
    .border-danger-soft { border-color: #feb2b2 !important; }
    .border-warning-soft { border-color: #faf089 !important; }
    .shadow-none { box-shadow: none !important; }
    .shadow-sm { box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.03) !important; }
    .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important; }
    .rounded-lg { border-radius: 10px !important; }
    .p-v-sm { padding-top: 8px !important; padding-bottom: 8px !important; }
    .m-b-md { margin-bottom: 15px !important; }
    .m-b-xs { margin-bottom: 5px !important; }
    .m-r-sm { margin-right: 10px !important; }
    .mr-1 { margin-right: 4px !important; }
    .mr-2 { margin-right: 8px !important; }
    .ml-2 { margin-left: 8px !important; }
    .ml-3 { margin-left: 12px !important; }
    .mt-4 { margin-top: 20px !important; }
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
        padding: 6px 12px; 
        border-radius: 6px; 
        font-size: 12px; 
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
</style>

<script>
    $('.toggle-view-pass').click(function() {
        var input = $(this).closest('.input-group').find('input');
        var type = input.attr('type') === 'password' ? 'text' : 'password';
        input.attr('type', type);
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
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
</script>
