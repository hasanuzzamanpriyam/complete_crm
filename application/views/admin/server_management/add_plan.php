<div class="panel panel-custom">
    <div class="panel-heading">
        <h4 class="modal-title"><?= lang('add_plan') ?></h4>
    </div>
    <div class="panel-body">
        <form id="planForm" action="<?= base_url('admin/server_management/add_plan') ?>" method="post">
            <div class="form-group">
                <label>Plan Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Basic, Standard, Professional" required>
            </div>
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
        </form>
    </div>
</div>
