<form id="billingStatusForm" action="<?= base_url('admin/ajax_api/add_billing_status') ?>" method="post">
    <div class="form-group">
        <label>Status Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" placeholder="e.g. Active, Pending, Expired" required>
    </div>
    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
</form>
