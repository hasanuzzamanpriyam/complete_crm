<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><i class="fa fa-server text-primary"></i> <?= htmlspecialchars($hosting->title) ?></h4>
</div>
<div class="modal-body pb-0">
    <div class="row">
        <!-- Main Info -->
        <div class="col-md-7">
            <div class="panel panel-default border-none shadow-none">
                <div class="panel-heading bg-white border-none p-0 mb-3">
                    <h5 class="text-uppercase text-muted font-bold m-0" style="font-size: 11px; letter-spacing: 1px;">Hosting Details</h5>
                </div>
                <div class="panel-body p-0">
                    <table class="table table-details m-0">
                        <tr>
                            <td class="col-xs-5 text-muted border-none p-v-xs">Provider</td>
                            <td class="col-xs-7 font-bold border-none p-v-xs">
                                <?= !empty($hosting->provider) ? htmlspecialchars($hosting->provider) : '<span class="text-muted">-</span>' ?>
                                <?php if (!empty($hosting->provider_url)): ?>
                                    <a href="<?= $hosting->provider_url ?>" target="_blank" class="ml-2 text-info"><i class="fa fa-external-link"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-xs">Server Name</td>
                            <td class="border-none p-v-xs font-bold"><?= !empty($hosting->server_name) ? htmlspecialchars($hosting->server_name) : '<span class="text-muted">-</span>' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-xs">Server Type</td>
                            <td class="border-none p-v-xs"><span class="badge badge-info"><?= htmlspecialchars($hosting->server_type) ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-xs">Status</td>
                            <td class="border-none p-v-xs">
                                <?php
                                $badge_class = 'badge-secondary';
                                switch ($hosting->status) {
                                    case 'Active': $badge_class = 'badge-success'; break;
                                    case 'Expired': $badge_class = 'badge-danger'; break;
                                    case 'Pending': $badge_class = 'badge-warning'; break;
                                    case 'Suspended': $badge_class = 'badge-info'; break;
                                    case 'Cancelled': $badge_class = 'badge-danger'; break;
                                }
                                ?>
                                <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($hosting->status) ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-xs">Location</td>
                            <td class="border-none p-v-xs"><?= !empty($hosting->server_location) ? htmlspecialchars($hosting->server_location) : '<span class="text-muted">-</span>' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-xs">IP Address</td>
                            <td class="border-none p-v-xs font-bold"><?= !empty($hosting->ip_address) ? htmlspecialchars($hosting->ip_address) : '<span class="text-muted">-</span>' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-xs">Main Domain</td>
                            <td class="border-none p-v-xs"><?= !empty($hosting->main_domain) ? htmlspecialchars($hosting->main_domain) : '<span class="text-muted">-</span>' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-xs">Projects</td>
                            <td class="border-none p-v-xs small"><?= !empty($hosting->projects_names) ? htmlspecialchars($hosting->projects_names) : '<span class="text-muted">-</span>' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-xs">Clients</td>
                            <td class="border-none p-v-xs small"><?= !empty($hosting->clients_names) ? htmlspecialchars($hosting->clients_names) : '<span class="text-muted">-</span>' ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="panel panel-default border-none shadow-none mt-4">
                <div class="panel-heading bg-white border-none p-0 mb-3">
                    <h5 class="text-uppercase text-muted font-bold m-0" style="font-size: 11px; letter-spacing: 1px;">Dates & Billing</h5>
                </div>
                <div class="panel-body p-0">
                    <div class="row">
                        <div class="col-xs-4">
                            <div class="p-3 bg-light rounded text-center">
                                <div class="text-muted small text-uppercase">Purchased</div>
                                <div class="font-bold h4 m-0"><?= !empty($hosting->purchase_date) ? date('d M, Y', strtotime($hosting->purchase_date)) : '-' ?></div>
                            </div>
                        </div>
                        <div class="col-xs-4">
                            <div class="p-3 bg-light rounded text-center">
                                <div class="text-muted small text-uppercase">Expiry Date</div>
                                <div class="font-bold h4 m-0 text-danger"><?= date('d M, Y', strtotime($hosting->expiry_date)) ?></div>
                            </div>
                        </div>
                        <div class="col-xs-4">
                            <div class="p-3 bg-light rounded text-center">
                                <div class="text-muted small text-uppercase">Price</div>
                                <div class="font-bold h4 m-0 text-success"><?= $hosting->price ?> <?= !empty($hosting->currency_symbol) ? $hosting->currency_symbol : $hosting->currency_id ?></div>
                            </div>
                        </div>
                    </div>
                    <table class="table table-details mt-3">
                        <tr>
                            <td class="col-xs-5 text-muted border-none p-v-xs">Renewal Cycle</td>
                            <td class="col-xs-7 font-bold border-none p-v-xs"><?= $hosting->days ?> <?= $hosting->time_unit ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-xs">Renewal Mode</td>
                            <td class="border-none p-v-xs"><span class="label label-outline-<?= $hosting->renew == 'automatic' ? 'success' : 'warning' ?>"><?= ucfirst($hosting->renew) ?></span></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="panel panel-default border-none shadow-none mt-4">
                <div class="panel-heading bg-white border-none p-0 mb-2">
                    <h5 class="text-uppercase text-muted font-bold m-0" style="font-size: 11px; letter-spacing: 1px;">SSL & Notification</h5>
                </div>
                <div class="panel-body p-0">
                    <div class="d-flex align-items-center mb-2">
                        <div class="w-10 text-center"><i class="fa <?= $hosting->ssl_certificate ? 'fa-shield text-success' : 'fa-times-circle text-muted' ?>"></i></div>
                        <div class="ml-2 small text-muted">SSL Certificate: 
                            <span class="font-bold text-dark"><?= $hosting->ssl_certificate ? 'Yes' : 'No' ?></span>
                            <?php if ($hosting->ssl_certificate): ?>
                                <span class="ml-1 text-info small">(Exp: <?= !empty($hosting->ssl_expiry_date) ? date('d M, Y', strtotime($hosting->ssl_expiry_date)) : 'N/A' ?>)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($hosting->ssl_certificate && !empty($hosting->ssl_type)): ?>
                    <div class="ml-5 small text-muted mb-2">Type: <span class="text-dark"><?= htmlspecialchars($hosting->ssl_type) ?></span></div>
                    <?php endif; ?>

                    <hr class="m-v-xs" style="border-top-color: rgba(0,0,0,0.05)">
                    <div class="d-flex align-items-center">
                        <div class="w-10 text-center"><i class="fa <?= $hosting->expiry_notification ? 'fa-bell text-warning' : 'fa-bell-slash-o text-muted' ?>"></i></div>
                        <div class="ml-2 small text-muted">
                            Notification: <span class="font-bold text-dark"><?= $hosting->expiry_notification ? 'Enabled' : 'Disabled' ?></span>
                            <?php if ($hosting->expiry_notification): ?>
                                <div class="mt-1 xsmall text-muted">(<?= $hosting->notification_days ?> <?= $hosting->notification_time_unit ?> before expiry)</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar / Access Info -->
        <div class="col-md-5">
            <div class="panel panel-default bg-light-blue-50 border-none rounded">
                <div class="panel-body">
                    <h5 class="text-uppercase text-muted font-bold m-b-md" style="font-size: 11px; letter-spacing: 1px;">Hosting Access</h5>
                    
                    <div class="mb-3">
                        <label class="text-muted small m-0">Username</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control bg-white" value="<?= htmlspecialchars($hosting->username) ?>" readonly id="view_host_user_<?= $hosting->id ?>">
                            <span class="input-group-btn">
                                <button class="btn btn-default copy-btn" data-clipboard-target="#view_host_user_<?= $hosting->id ?>" type="button"><i class="fa fa-copy"></i></button>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small m-0">Password</label>
                        <div class="input-group input-group-sm">
                            <input type="password" class="form-control bg-white" value="<?= htmlspecialchars($hosting->password) ?>" readonly id="view_host_pass_<?= $hosting->id ?>">
                            <span class="input-group-btn">
                                <button class="btn btn-default toggle-view-pass" type="button"><i class="fa fa-eye"></i></button>
                                <button class="btn btn-default copy-btn" data-clipboard-target="#view_host_pass_<?= $hosting->id ?>" type="button"><i class="fa fa-copy"></i></button>
                            </span>
                        </div>
                    </div>

                    <div class="mb-2 small">
                        <span class="text-muted">cPanel URL:</span> 
                        <?php if (!empty($hosting->cpanel_url)): ?>
                            <a href="<?= $hosting->cpanel_url ?>" target="_blank" class="text-info"><?= $hosting->cpanel_url ?></a>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </div>
                    <div class="mb-2 small">
                        <span class="text-muted">Hostname:</span> 
                        <span class="font-bold"><?= $hosting->hostname ?: 'N/A' ?></span>
                    </div>

                    <hr class="m-v-md" style="border-top-color: rgba(0,0,0,0.05)">

                    <h5 class="text-uppercase text-muted font-bold m-b-md" style="font-size: 11px; letter-spacing: 1px;">FTP Credentials</h5>
                    <div class="mb-3">
                        <label class="text-muted small m-0">FTP Username</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control bg-white" value="<?= htmlspecialchars($hosting->ftp_username) ?>" readonly id="view_ftp_user_<?= $hosting->id ?>">
                            <span class="input-group-btn">
                                <button class="btn btn-default copy-btn" data-clipboard-target="#view_ftp_user_<?= $hosting->id ?>" type="button"><i class="fa fa-copy"></i></button>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small m-0">FTP Password</label>
                        <div class="input-group input-group-sm">
                            <input type="password" class="form-control bg-white" value="<?= htmlspecialchars($hosting->ftp_password) ?>" readonly id="view_ftp_pass_<?= $hosting->id ?>">
                            <span class="input-group-btn">
                                <button class="btn btn-default toggle-view-pass" type="button"><i class="fa fa-eye"></i></button>
                                <button class="btn btn-default copy-btn" data-clipboard-target="#view_ftp_pass_<?= $hosting->id ?>" type="button"><i class="fa fa-copy"></i></button>
                            </span>
                        </div>
                    </div>

                    <hr class="m-v-md" style="border-top-color: rgba(0,0,0,0.05)">

                    <h5 class="text-uppercase text-muted font-bold m-b-md" style="font-size: 11px; letter-spacing: 1px;">DNS Provider</h5>
                    <div class="mb-2 small"><span class="text-muted">Name:</span> <span class="font-bold"><?= $hosting->dns_provider_name ?: '-' ?></span></div>
                    <div class="mb-2 small"><span class="text-muted">Email:</span> <span class="font-bold"><?= $hosting->dns_email ?: '-' ?></span></div>
                    <div class="mb-2 small">
                        <span class="text-muted">Password:</span> 
                        <span class="password-mask" data-pass="<?= htmlspecialchars($hosting->dns_password) ?>">********</span>
                        <a href="javascript:void(0)" class="toggle-mask-pass ml-1"><i class="fa fa-eye"></i></a>
                    </div>
                </div>
            </div>

            <div class="panel panel-default border-none shadow-none mt-4">
                <div class="panel-heading bg-white border-none p-0 mb-2">
                    <h5 class="text-uppercase text-muted font-bold m-0" style="font-size: 11px; letter-spacing: 1px;">Server Nameservers</h5>
                </div>
                <div class="panel-body p-0">
                    <div class="small">
                        <?php if (!empty($hosting->nameservers)): ?>
                            <?php foreach (explode(',', $hosting->nameservers) as $ns): ?>
                                <div class="bg-light p-1 mb-1 rounded px-2"><?= htmlspecialchars(trim($ns)) ?></div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="text-muted">None specified</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Description -->
    <?php if (!empty($hosting->description)): ?>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="panel panel-default border-none shadow-none">
                <div class="panel-heading bg-white border-none p-0 mb-2">
                    <h5 class="text-uppercase text-muted font-bold m-0" style="font-size: 11px; letter-spacing: 1px;">Description</h5>
                </div>
                <div class="panel-body p-3 bg-light rounded italic text-muted">
                    <?= nl2br(htmlspecialchars($hosting->description)) ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<div class="modal-footer border-none">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    <a href="<?= base_url('admin/server_management/add_hosting/' . $hosting->id) ?>" class="btn btn-primary"><i class="fa fa-pencil"></i> Edit Hosting</a>
</div>

<style>
    .bg-light-blue-50 { background-color: #f0f7ff; }
    .border-none { border: none !important; }
    .shadow-none { box-shadow: none !important; }
    .p-v-xs { padding-top: 5px !important; padding-bottom: 5px !important; }
    .m-b-md { margin-bottom: 15px !important; }
    .m-v-md { margin-top: 20px !important; margin-bottom: 20px !important; }
    .rounded { border-radius: 8px !important; }
    .table-details td { vertical-align: middle; }
    .font-bold { font-weight: 600; }
    .italic { font-style: italic; }
    .ml-2 { margin-left: 10px; }
    .mt-4 { margin-top: 20px; }
    .mt-3 { margin-top: 15px; }
    .mb-3 { margin-bottom: 15px; }
    .mb-2 { margin-bottom: 10px; }
    .label-outline-success { border: 1px solid #27c24c; color: #27c24c; padding: 2px 6px; border-radius: 2px; font-size: 10px; }
    .label-outline-warning { border: 1px solid #fad733; color: #f6d433; padding: 2px 6px; border-radius: 2px; font-size: 10px; }
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

<style>
    .xsmall { font-size: 10px; }
    .m-v-xs { margin-top: 5px; margin-bottom: 5px; }
    .password-mask { font-family: monospace; }
</style>
