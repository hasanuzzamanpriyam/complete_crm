<div class="panel panel-custom">
    <div class="panel-heading">
        <h4>Quick Add Project</h4>
    </div>
    <div class="panel-body">
        <form id="quickAddProjectForm" action="<?= base_url('admin/projects/saved_project') ?>" method="post">
            <div class="form-group">
                <label>Project Name <span class="text-danger">*</span></label>
                <input type="text" name="project_name" class="form-control" placeholder="Enter project name" required>
            </div>
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
        </form>
    </div>
</div>
