<form id="billingTypeForm" action="<?= base_url('admin/ajax_api/add_billing_type') ?>" method="post">
    <div class="form-group">
        <label>Billing Type Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" placeholder="e.g. Domain, Hosting, SSL" required>
    </div>
    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
</form>
