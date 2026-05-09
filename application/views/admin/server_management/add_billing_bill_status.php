<form id="billingBillStatusForm" action="<?= base_url('admin/ajax_api/add_billing_bill_status') ?>" method="post">
    <div class="form-group">
        <label>Bill Status Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" placeholder="e.g. Billed, Unbilled" required>
    </div>
    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
</form>
