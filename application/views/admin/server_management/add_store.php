<form id="storeForm" action="<?= base_url('admin/ajax_api/add_store') ?>" method="post">
    <div class="form-group">
        <label>Store Name <span class="text-danger">*</span></label>
        <input type="text" name="store_name" class="form-control" placeholder="e.g. Main Store, Online Store" required>
    </div>
    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
</form>
