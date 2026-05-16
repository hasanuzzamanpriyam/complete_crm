<div class="modal-header bg-primary text-white" style="border-top-left-radius: 8px; border-top-right-radius: 8px;">
    <button type="button" class="close text-white" data-dismiss="modal" style="opacity: 0.8;">&times;</button>
    <h4 class="modal-title" style="font-weight: 600;"><i class="fa fa-credit-card mr-2"></i> <?= htmlspecialchars($billing_info->label) ?> - Details</h4>
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
                            <td class="col-xs-5 text-muted border-none p-v-sm">Category</td>
                            <td class="col-xs-7 font-bold border-none p-v-sm text-dark"><span class="badge badge-soft-info"><?= htmlspecialchars($billing_info->type) ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-sm">Provider</td>
                            <td class="border-none p-v-sm text-dark"><?= !empty($billing_info->provider) ? htmlspecialchars($billing_info->provider) : '<span class="text-muted">-</span>' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-sm">Flag</td>
                            <td class="border-none p-v-sm text-dark"><?= htmlspecialchars($billing_info->flag) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-sm">Status</td>
                            <td class="border-none p-v-sm text-dark">
                                <?php 
                                    $badge_class = 'badge-soft-info';
                                    if ($billing_info->status == 'Active') $badge_class = 'badge-soft-success';
                                    elseif ($billing_info->status == 'Pending') $badge_class = 'badge-soft-warning';
                                    elseif ($billing_info->status == 'Expired') $badge_class = 'badge-soft-danger';
                                ?>
                                <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($billing_info->status) ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-sm">Renewal Mode</td>
                            <td class="border-none p-v-sm"><span class="badge badge-soft-secondary"><?= ucfirst($billing_info->renew) ?></span></td>
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
                                <div class="text-muted xsmall text-uppercase mb-1" style="letter-spacing: 0.5px;">Buy Date</div>
                                <div class="font-bold text-dark h5 m-0"><?= !empty($billing_info->buy_date) ? date('d M, Y', strtotime($billing_info->buy_date)) : '-' ?></div>
                            </div>
                        </div>
                        <div class="col-xs-4 px-1">
                            <div class="p-3 bg-soft-danger-light rounded shadow-xs text-center border border-danger-soft">
                                <div class="text-danger xsmall text-uppercase mb-1" style="letter-spacing: 0.5px;">Next Renew</div>
                                <div class="font-bold text-danger h5 m-0"><?= !empty($billing_info->renewal_date) ? date('d M, Y', strtotime($billing_info->renewal_date)) : '-' ?></div>
                            </div>
                        </div>
                        <div class="col-xs-4 pl-1">
                            <div class="p-3 bg-light rounded shadow-xs text-center border">
                                <div class="text-muted xsmall text-uppercase mb-1" style="letter-spacing: 0.5px;">Price</div>
                                <div class="font-bold text-success h5 m-0"><?= $billing_info->currency ?> <?= number_format((float)$billing_info->value, 2) ?></div>
                            </div>
                        </div>
                    </div>
                    <table class="table table-details m-0">
                        <tr>
                            <td class="col-xs-5 text-muted border-none p-v-sm">Billing Cycle</td>
                            <td class="col-xs-7 font-bold border-none p-v-sm text-dark"><?= $billing_info->billing_cycle ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-sm">Last Billed</td>
                            <td class="border-none p-v-sm text-dark"><?= !empty($billing_info->last_billed_date) ? date('d M, Y', strtotime($billing_info->last_billed_date)) : '-' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted border-none p-v-sm">Bill Status</td>
                            <td class="border-none p-v-sm text-dark"><?= $billing_info->bill_status ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="panel panel-default border-none shadow-none mt-2">
                <div class="panel-heading bg-transparent border-none p-0 mb-3">
                    <h5 class="text-uppercase text-muted font-bold m-0" style="font-size: 11px; letter-spacing: 1.2px;">Notifications & Security</h5>
                </div>
                <div class="panel-body p-0">
                    <div class="feature-item d-flex align-items-center mb-2 p-2 rounded <?= $billing_info->secure_protocol ? 'bg-soft-success-light border-success-soft' : '' ?>">
                        <div class="feature-icon"><i class="fa <?= $billing_info->secure_protocol ? 'fa-shield text-success' : 'fa-times-circle text-muted' ?>"></i></div>
                        <div class="ml-3 small text-muted">Secure Protocol: <span class="font-bold text-dark"><?= $billing_info->secure_protocol ? 'Yes' : 'No' ?></span></div>
                    </div>
                    
                    <div class="feature-item d-flex align-items-center mb-2 p-2 rounded <?= $billing_info->enable_expiry_notification ? 'bg-soft-warning-light border-warning-soft' : '' ?>">
                        <div class="feature-icon"><i class="fa <?= $billing_info->enable_expiry_notification ? 'fa-bell text-warning' : 'fa-bell-slash-o text-muted' ?>"></i></div>
                        <div class="ml-3 small text-muted">Expiry Notification: <span class="font-bold text-dark"><?= $billing_info->enable_expiry_notification ? 'Enabled' : 'Disabled' ?></span></div>
                    </div>

                    <?php if (!empty($billing_info->server_tags)): ?>
                    <div class="mt-3">
                        <span class="text-muted small">Tags:</span>
                        <div class="mt-1">
                            <?php foreach(explode(',', $billing_info->server_tags) as $tag): ?>
                                <span class="badge badge-soft-secondary mr-1"><?= trim($tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-5">
            <div class="panel panel-default bg-light-blue-50 border-none rounded-lg shadow-sm mb-4">
                <div class="panel-body p-4">
                    <h5 class="text-uppercase text-primary font-bold m-b-md" style="font-size: 11px; letter-spacing: 1.2px;">Contact Details</h5>
                    
                    <div class="mb-3">
                        <label class="text-muted small m-b-xs d-block">Contact Person</label>
                        <div class="font-bold text-dark"><?= !empty($billing_info->contact_person) ? htmlspecialchars($billing_info->contact_person) : 'N/A' ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small m-b-xs d-block">Email</label>
                        <div class="text-dark small"><i class="fa fa-envelope mr-2 text-muted"></i> <?= $billing_info->contact_email ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small m-b-xs d-block">Phone</label>
                        <div class="text-dark small"><i class="fa fa-phone mr-2 text-muted"></i> <?= $billing_info->contact_phone ?></div>
                    </div>

                    <div class="mb-4">
                        <label class="text-muted small m-b-xs d-block">Address</label>
                        <div class="text-muted italic small"><?= $billing_info->address ?></div>
                    </div>

                    <div class="separator mb-4"></div>

                    <h5 class="text-uppercase text-primary font-bold m-b-md" style="font-size: 11px; letter-spacing: 1.2px;">Assignments</h5>
                    
                    <div class="mb-3 small d-flex align-items-center justify-content-between bg-white p-2 rounded shadow-xs border">
                        <span class="text-muted">Project:</span> 
                        <span class="font-bold text-dark ml-2"><?= !empty($billing_info->project_name) ? htmlspecialchars($billing_info->project_name) : 'N/A' ?></span>
                    </div>

                    <div class="mb-3 small d-flex align-items-center justify-content-between bg-white p-2 rounded shadow-xs border">
                        <span class="text-muted">Client:</span> 
                        <span class="font-bold text-dark ml-2"><?= !empty($billing_info->client_name) ? htmlspecialchars($billing_info->client_name) : 'N/A' ?></span>
                    </div>

                    <div class="mb-4">
                        <label class="text-muted small m-b-xs d-block">Manage</label>
                        <div class="small font-bold text-dark"><?= $billing_info->manage ?></div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default border-none shadow-sm mt-2">
                <div class="panel-heading bg-transparent border-none px-4 pt-4 pb-0">
                    <h5 class="text-uppercase text-muted font-bold m-0" style="font-size: 11px; letter-spacing: 1.2px;">Login Details</h5>
                </div>
                <div class="panel-body px-4 pb-4">
                    <div class="well well-sm bg-white border-dashed text-dark small m-0" style="word-break: break-all;">
                        <?= nl2br(htmlspecialchars($billing_info->login_details)) ?>
                    </div>
                    <?php if (!empty($billing_info->port)): ?>
                    <div class="mt-2 small text-muted">Port: <span class="font-bold text-dark"><?= $billing_info->port ?></span></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Fields -->
    <?php 
    $custom_fields = !empty($billing_info->custom_fields) ? json_decode($billing_info->custom_fields, true) : [];
    if (!empty($custom_fields)) {
    ?>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="panel panel-default border-none shadow-sm" style="border-radius: 10px; overflow: hidden;">
                <div class="panel-heading bg-white border-none px-4 pt-4 pb-0">
                    <h5 class="text-uppercase text-muted font-bold m-0" style="font-size: 11px; letter-spacing: 1.2px;">Additional Info</h5>
                </div>
                <div class="panel-body p-4">
                    <div class="row">
                        <?php foreach ($custom_fields as $field) { ?>
                            <div class="col-md-4 mb-3">
                                <label class="text-muted xsmall text-uppercase d-block mb-1"><?= htmlspecialchars($field['label']) ?></label>
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
                                    <div class="font-bold text-dark"><?= htmlspecialchars($field['value']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>

    <!-- Description -->
    <?php if (!empty($billing_info->description)): ?>
    <div class="row mt-4 mb-4">
        <div class="col-md-12">
            <div class="panel panel-default border-none shadow-sm" style="border-radius: 10px; overflow: hidden;">
                <div class="panel-heading bg-white border-none px-4 pt-4 pb-0">
                    <h5 class="text-uppercase text-muted font-bold m-0" style="font-size: 11px; letter-spacing: 1.2px;">Notes / Description</h5>
                </div>
                <div class="panel-body p-4 italic text-muted" style="line-height: 1.6; background-color: #fcfdfe;">
                    <?= nl2br(htmlspecialchars($billing_info->description)) ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<div class="modal-footer bg-light border-none px-4 py-3" style="border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
    <button type="button" class="btn btn-link text-muted" data-dismiss="modal" style="text-decoration: none;">Close</button>
    <a href="<?= base_url('admin/server_management/add_billing/' . $billing_info->id) ?>" class="btn btn-primary px-4 shadow-sm" style="border-radius: 6px; font-weight: 600;"><i class="fa fa-pencil mr-2"></i> Edit Billing</a>
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
    .shadow-sm { box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.03) !important; }
    .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important; }
    .rounded-lg { border-radius: 10px !important; }
    .p-v-sm { padding-top: 8px !important; padding-bottom: 8px !important; }
    .m-b-md { margin-bottom: 15px !important; }
    .m-b-xs { margin-bottom: 5px !important; }
    .mr-1 { margin-right: 4px !important; }
    .mr-2 { margin-right: 8px !important; }
    .ml-2 { margin-left: 8px !important; }
    .ml-3 { margin-left: 12px !important; }
    .mt-2 { margin-top: 10px !important; }
    .mt-3 { margin-top: 15px !important; }
    .mt-4 { margin-top: 20px !important; }
    .mb-3 { margin-bottom: 12px !important; }
    .mb-4 { margin-bottom: 20px !important; }
    
    .d-flex { display: flex !important; }
    .align-items-center { align-items: center !important; }
    .justify-content-between { justify-content: space-between !important; }
    
    .table-details td { vertical-align: middle; font-size: 13px; }
    .font-bold { font-weight: 600; }
    .italic { font-style: italic; }
    .separator { height: 1px; background: linear-gradient(to right, rgba(0,0,0,0), rgba(0,0,0,0.05), rgba(0,0,0,0)); }
    
    .feature-icon { width: 24px; text-align: center; font-size: 16px; }
    
    .badge { padding: 5px 10px; border-radius: 4px; font-weight: 600; font-size: 11px; }
    .badge-soft-success { background-color: #c6f6d5; color: #22543d; }
    .badge-soft-danger { background-color: #fed7d7; color: #822727; }
    .badge-soft-warning { background-color: #fefcbf; color: #744210; }
    .badge-soft-info { background-color: #bee3f8; color: #2a4365; }
    .badge-soft-secondary { background-color: #e2e8f0; color: #2d3748; }

    .xsmall { font-size: 11px; }
</style>

<script>
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

