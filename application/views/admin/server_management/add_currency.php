<form id="currencyForm" action="<?= base_url('admin/ajax_api/add_currency') ?>" method="post">
    <div class="form-group">
        <label>Currency Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" placeholder="e.g. US Dollar" required>
    </div>
    <div class="form-group">
        <label>Currency Code <span class="text-danger">*</span></label>
        <input type="text" name="code" class="form-control" placeholder="e.g. USD" required>
    </div>
    <div class="form-group">
        <label>Currency Symbol <span class="text-danger">*</span></label>
        <input type="text" name="symbol" class="form-control" placeholder="e.g. $" required>
    </div>
    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
</form>
