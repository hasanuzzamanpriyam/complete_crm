<form id="addClientForm" action="<?= base_url('admin/ajax_api/add_client') ?>" method="post">
    <div class="form-group">
        <label>Client Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" placeholder="Enter Client Name" required>
    </div>
    <div class="form-group">
        <label>Client Email <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control" placeholder="Enter Client Email" required>
    </div>
    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
</form>
