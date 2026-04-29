<form id="addProjectForm" action="<?= base_url('admin/ajax_api/add_project') ?>" method="post">
    <div class="form-group">
        <label>Project Name <span class="text-danger">*</span></label>
        <input type="text" name="project_name" class="form-control" placeholder="Enter Project Name" required>
    </div>
    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
</form>
