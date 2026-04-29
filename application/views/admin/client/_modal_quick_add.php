<div class="panel panel-custom">
    <div class="panel-heading">
        <h4>Quick Add Client</h4>
    </div>
    <div class="panel-body">
        <form id="quickAddClientForm" action="<?= base_url('admin/client/update_client') ?>" method="post">
            <div class="form-group">
                <label>Company Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="Enter company name" required>
            </div>
            <div class="form-group">
                <label>Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" placeholder="Enter email" required>
            </div>
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
        </form>
    </div>
</div>
