<style>
    .badge {
        padding: 5px 10px;
        font-weight: 500;
        border-radius: 4px;
        font-size: 11px;
    }
    .badge-active { background-color: #28a745; color: #fff; }
    .badge-pending { background-color: #ffc107; color: #212529; }
    .badge-expired { background-color: #dc3545; color: #fff; }
    .badge-info { background-color: #17a2b8; color: #fff; }
</style>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= $billing_info->label ?> - Details</h4>
</div>
<div class="modal-body pb-0">
    <div class="row">
        <div class="col-md-6">
            <p><strong>Title:</strong> <?= $billing_info->label ?></p>
            <p><strong>Category:</strong> <?= $billing_info->type ?></p>
            <p><strong>Provider:</strong> <?= !empty($billing_info->provider) ? $billing_info->provider : 'N/A' ?></p>
            <p><strong>Flag:</strong> <?= $billing_info->flag ?></p>
        </div>
        <div class="col-md-6">
            <p><strong>Status:</strong> 
                <?php 
                    $status_class = 'info';
                    if ($billing_info->status == 'Active') $status_class = 'active';
                    elseif ($billing_info->status == 'Pending') $status_class = 'pending';
                    elseif ($billing_info->status == 'Expired') $status_class = 'expired';
                ?>
                <span class="badge badge-<?= $status_class ?>"><?= $billing_info->status ?></span>
            </p>
            <p><strong>Price:</strong> <?= $billing_info->currency ?> <?= number_format($billing_info->value, 2) ?></p>
            <p><strong>Renew:</strong> <?= $billing_info->renew ?></p>
        </div>
    </div>
    <hr class="mt-0">
    <div class="row">
        <div class="col-md-6">
            <h5 class="text-primary mt-0">Contact Details</h5>
            <p><strong>Contact Person:</strong> <?= !empty($billing_info->contact_person) ? $billing_info->contact_person : 'N/A' ?></p>
            <p><strong>Email:</strong> <?= $billing_info->contact_email ?></p>
            <p><strong>Phone:</strong> <?= $billing_info->contact_phone ?></p>
            <p><strong>Address:</strong> <?= $billing_info->address ?></p>
        </div>
        <div class="col-md-6">
            <h5 class="text-primary mt-0">Billing Periods</h5>
            <p><strong>Registration Date:</strong> <?= $billing_info->registration_date ?></p>
            <p><strong>Buy Date:</strong> <?= $billing_info->buy_date ?></p>
            <p><strong>Duration:</strong> <?= $billing_info->duration ?> <?= $billing_info->time_unit ?></p>
            <p><strong>Next Renew Date:</strong> <?= $billing_info->renewal_date ?></p>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-6">
            <h5 class="text-primary mt-0">Billing Info</h5>
            <p><strong>Billing Cycle:</strong> <?= $billing_info->billing_cycle ?></p>
            <p><strong>Last Billed:</strong> <?= $billing_info->last_billed_date ?></p>
            <p><strong>Billing End:</strong> <?= $billing_info->billing_end_date ?></p>
            <p><strong>Bill Status:</strong> <?= $billing_info->bill_status ?></p>
        </div>
        <div class="col-md-6">
            <h5 class="text-primary mt-0">Assignments</h5>
            <p><strong>Project:</strong> <?= !empty($billing_info->project_name) ? $billing_info->project_name : 'N/A' ?></p>
            <p><strong>Client:</strong> <?= !empty($billing_info->client_name) ? $billing_info->client_name : 'N/A' ?></p>
            <p><strong>Server Details:</strong> <?= $billing_info->server_details ?></p>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-6">
            <h5 class="text-primary mt-0">Login & Security</h5>
            <p><strong>Manage:</strong> <?= $billing_info->manage ?></p>
            <p><strong>Login Details:</strong> <?= $billing_info->login_details ?></p>
            <p><strong>Port:</strong> <?= $billing_info->port ?></p>
            <p><strong>Secure Protocol:</strong> <?= $billing_info->secure_protocol ? 'Yes' : 'No' ?></p>
        </div>
        <div class="col-md-6">
            <h5 class="text-primary mt-0">Notifications</h5>
            <p><strong>Expiry Notification:</strong> <?= $billing_info->enable_expiry_notification ? 'Enabled' : 'Disabled' ?></p>
            <p><strong>Weekend Reminders:</strong> <?= $billing_info->enable_reminders_weekend ? 'Enabled' : 'Disabled' ?></p>
            <p><strong>Tags:</strong> <?= $billing_info->server_tags ?></p>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-12">
            <h5 class="text-primary mt-0">Description</h5>
            <div class="well well-sm">
                <?= nl2br($billing_info->description) ?>
            </div>
        </div>
    </div>
    <?php 
    $custom_fields = !empty($billing_info->custom_fields) ? json_decode($billing_info->custom_fields, true) : [];
    if (!empty($custom_fields)) {
    ?>
    <hr>
    <div class="row">
        <div class="col-md-12">
            <h5 class="text-primary mt-0">Additional Labels</h5>
            <div class="row">
                <?php foreach ($custom_fields as $field) { ?>
                    <div class="col-md-4">
                        <p><strong><?= $field['label'] ?>:</strong> <?= $field['value'] ?></p>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php } ?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>
