<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><i class="fa fa-globe text-primary"></i> <?= htmlspecialchars($domain->domain_name) ?></h4>
</div>
<div class="modal-body pb-0">
    <div class="row">
        <!-- Main Info -->
        <div class="col-md-7">
            <div class="panel panel-default border-none shadow-none">
                <div class="panel-heading bg-white border-none p-0 mb-3">
                    <h5 class="text-uppercase text-muted font-bold m-0" style="font-size: 11px; letter-spacing: 1px;">General Information</h5>
                </div>
                <div class="panel-body p-0">
                    <table class="table table-details m-0">
                        <tr>
                            <td class="col-xs-5 text-muted border-none p-v-xs">Provider</td>
                            <td class="col-xs-7 font-bold border-none p-v-xs">
                                <?= !empty($domain->provider) ? htmlspecialchars($domain->provider) : '<span class="text-muted">-</span>' ?>
                                <?php if (!empty($domain->provider_url)): ?>
                                    <a href="<?= $domain->provider_url ?>" target="_blank" class="ml-2 text-info"><i class="fa fa-external-link"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-xs">Domain Type</td>
                            <td class="border-none p-v-xs"><span class="badge badge-info"><?= htmlspecialchars($domain->domain_type) ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-xs">Status</td>
                            <td class="border-none p-v-xs">
                                <?php
                                $badge_class = 'badge-secondary';
                                switch ($domain->status) {
                                    case 'Active': $badge_class = 'badge-success'; break;
                                    case 'Expired': $badge_class = 'badge-danger'; break;
                                    case 'Pending': $badge_class = 'badge-warning'; break;
                                    case 'Transferring': $badge_class = 'badge-info'; break;
                                }
                                ?>
                                <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($domain->status) ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-xs">Hosting</td>
                            <td class="border-none p-v-xs"><?= !empty($domain->hosting) ? htmlspecialchars($domain->hosting) : '<span class="text-muted">Not Assigned</span>' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-xs">Project</td>
                            <td class="border-none p-v-xs"><?= !empty($domain->project) ? htmlspecialchars($domain->project) : '<span class="text-muted">-</span>' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-xs">Client</td>
                            <td class="border-none p-v-xs"><?= !empty($domain->client_name) ? htmlspecialchars($domain->client_name) : '<span class="text-muted">-</span>' ?></td>
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
                                <div class="text-muted small text-uppercase">Date</div>
                                <div class="font-bold h4 m-0"><?= !empty($domain->date) ? date('d M, Y', strtotime($domain->date)) : '-' ?></div>
                            </div>
                        </div>
                        <div class="col-xs-4">
                            <div class="p-3 bg-light rounded text-center">
                                <div class="text-muted small text-uppercase">Renewal Date</div>
                                <div class="font-bold h4 m-0"><?= date('d M, Y', strtotime($domain->purchase_date)) ?></div>
                            </div>
                        </div>
                        <div class="col-xs-4">
                            <div class="p-3 bg-light rounded text-center">
                                <div class="text-muted small text-uppercase">Expiry Date</div>
                                <div class="font-bold h4 m-0 text-danger"><?= date('d M, Y', strtotime($domain->expiry_date)) ?></div>
                            </div>
                        </div>
                    </div>
                    <table class="table table-details mt-3">
                        <tr>
                            <td class="col-xs-5 text-muted border-none p-v-xs">Duration</td>
                            <td class="col-xs-7 font-bold border-none p-v-xs"><?= $domain->days ?> <?= $domain->time_unit ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-xs">Price</td>
                            <td class="border-none p-v-xs font-bold text-success"><?= $domain->price ?> <?= $domain->currency_id ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar / Additional Info -->
        <div class="col-md-5">
            <div class="panel panel-default bg-light-blue-50 border-none rounded">
                <div class="panel-body">
                    <h5 class="text-uppercase text-muted font-bold m-b-md" style="font-size: 11px; letter-spacing: 1px;">Access Details</h5>
                    
                    <div class="mb-3">
                        <label class="text-muted small m-0">Username</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control bg-white" value="<?= htmlspecialchars($domain->username) ?>" readonly id="view_user_<?= $domain->id ?>">
                            <span class="input-group-btn">
                                <button class="btn btn-default copy-btn" data-clipboard-target="#view_user_<?= $domain->id ?>" type="button"><i class="fa fa-copy"></i></button>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small m-0">Password</label>
                        <div class="input-group input-group-sm">
                            <input type="password" class="form-control bg-white" value="<?= htmlspecialchars($domain->password) ?>" readonly id="view_pass_<?= $domain->id ?>">
                            <span class="input-group-btn">
                                <button class="btn btn-default toggle-view-pass" type="button"><i class="fa fa-eye"></i></button>
                                <button class="btn btn-default copy-btn" data-clipboard-target="#view_pass_<?= $domain->id ?>" type="button"><i class="fa fa-copy"></i></button>
                            </span>
                        </div>
                    </div>

                    <hr class="m-v-md" style="border-top-color: rgba(0,0,0,0.05)">

                    <h5 class="text-uppercase text-muted font-bold m-b-md" style="font-size: 11px; letter-spacing: 1px;">Registrar Info</h5>
                    
                    <div class="mb-2 small">
                        <span class="text-muted">URL:</span> 
                        <?php if (!empty($domain->registrar_url)): ?>
                            <a href="<?= $domain->registrar_url ?>" target="_blank" class="text-info"><?= $domain->registrar_url ?></a>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </div>
                    <div class="mb-2 small">
                        <span class="text-muted">Status:</span> 
                        <span class="label label-default"><?= $domain->registrar_status ?: 'N/A' ?></span>
                    </div>
                </div>
            </div>

            <div class="panel panel-default border-none shadow-none mt-4">
                <div class="panel-heading bg-white border-none p-0 mb-2">
                    <h5 class="text-uppercase text-muted font-bold m-0" style="font-size: 11px; letter-spacing: 1px;">System Status</h5>
                </div>
                <div class="panel-body p-0">
                    <div class="d-flex align-items-center mb-2">
                        <div class="w-10 text-center"><i class="fa <?= $domain->auto_renewal ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i></div>
                        <div class="ml-2 small text-muted">Auto Renewal: <span class="font-bold text-dark"><?= $domain->auto_renewal ? 'Enabled' : 'Disabled' ?></span></div>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div class="w-10 text-center"><i class="fa <?= $domain->is_locked ? 'fa-lock text-danger' : 'fa-unlock text-success' ?>"></i></div>
                        <div class="ml-2 small text-muted">Locked Status: <span class="font-bold text-dark"><?= $domain->is_locked ? 'Locked' : 'Unlocked' ?></span></div>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div class="w-10 text-center"><i class="fa <?= $domain->is_for_sale ? 'fa-tag text-warning' : 'fa-circle-o text-muted' ?>"></i></div>
                        <div class="ml-2 small text-muted">For Sale: <span class="font-bold text-dark"><?= $domain->is_for_sale ? 'Yes' : 'No' ?></span></div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="w-10 text-center"><i class="fa <?= $domain->whois_protection ? 'fa-shield text-info' : 'fa-times-circle text-muted' ?>"></i></div>
                        <div class="ml-2 small text-muted">WHOIS Privacy: <span class="font-bold text-dark"><?= $domain->whois_protection ? 'Protected' : 'No' ?></span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Name Servers -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="panel panel-default border-none shadow-none bg-light p-3 rounded">
                <h5 class="text-uppercase text-muted font-bold m-b-sm" style="font-size: 11px; letter-spacing: 1px;">Nameservers</h5>
                <?php if (!empty($domain->nameservers)): ?>
                    <div class="d-flex flex-wrap">
                        <?php foreach (explode(',', $domain->nameservers) as $ns): ?>
                            <span class="badge badge-secondary m-r-xs m-b-xs" style="font-size: 12px; font-weight: normal; padding: 6px 12px;"><?= htmlspecialchars($ns) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <span class="text-muted">No nameservers specified</span>
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
            <h5 class="text-uppercase text-muted font-bold m-b-sm" style="font-size: 11px; letter-spacing: 1px;">Custom Fields</h5>
            <div class="row">
                <?php foreach ($custom_fields as $field): ?>
                    <div class="col-md-4 mb-3">
                        <div class="bg-white border rounded p-2">
                            <label class="text-muted small m-0 d-block"><?= htmlspecialchars($field['label']) ?></label>
                            <span class="font-bold"><?= htmlspecialchars($field['value']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Description -->
    <?php if (!empty($domain->description)): ?>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="panel panel-default border-none shadow-none">
                <div class="panel-heading bg-white border-none p-0 mb-2">
                    <h5 class="text-uppercase text-muted font-bold m-0" style="font-size: 11px; letter-spacing: 1px;">Description</h5>
                </div>
                <div class="panel-body p-3 bg-light rounded italic text-muted">
                    <?= nl2br(htmlspecialchars($domain->description)) ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<div class="modal-footer border-none">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    <a href="<?= base_url('admin/server_management/add_domain/' . $domain->id) ?>" class="btn btn-primary"><i class="fa fa-pencil"></i> Edit Domain</a>
</div>

<style>
    .bg-light-blue-50 { background-color: #f0f7ff; }
    .border-none { border: none !important; }
    .shadow-none { box-shadow: none !important; }
    .p-v-xs { padding-top: 5px !important; padding-bottom: 5px !important; }
    .m-b-md { margin-bottom: 15px !important; }
    .m-b-sm { margin-bottom: 10px !important; }
    .m-v-md { margin-top: 20px !important; margin-bottom: 20px !important; }
    .m-r-xs { margin-right: 5px !important; }
    .m-b-xs { margin-bottom: 5px !important; }
    .rounded { border-radius: 8px !important; }
    .table-details td { vertical-align: middle; }
    .font-bold { font-weight: 600; }
    .italic { font-style: italic; }
    .w-10 { width: 25px; }
    .ml-2 { margin-left: 10px; }
    .mt-4 { margin-top: 20px; }
    .mt-3 { margin-top: 15px; }
    .mb-3 { margin-bottom: 15px; }
    .mb-2 { margin-bottom: 10px; }
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
</script>
