<form id="billingFlagForm" action="<?= base_url('admin/ajax_api/add_billing_flag') ?>" method="post">
    <div class="form-group">
        <label>Flag Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" placeholder="e.g. Flag 1, Flag 2" required>
    </div>
    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
</form>
