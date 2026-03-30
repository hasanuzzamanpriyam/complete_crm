<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-<?= isset($project) ? 'pencil' : 'plus' ?>"></i>
                    <?= isset($project) ? 'Edit Project: ' . htmlspecialchars($project->project_name) : 'Register New Project' ?>
                </h3>
            </div>
            <div class="panel-body">
                <form method="post" action="<?= base_url('admin/payment_hub/save_project/' . (isset($project) ? $project->id : '')) ?>">

                    <div class="form-group">
                        <label for="project_name">Project Name <span class="text-danger">*</span></label>
                        <input type="text" name="project_name" id="project_name" class="form-control"
                               placeholder="e.g. My Laravel Shop"
                               value="<?= isset($project) ? htmlspecialchars($project->project_name) : '' ?>" required>
                        <p class="help-block">A descriptive name to identify this external project.</p>
                    </div>

                    <div class="form-group">
                        <label for="callback_url">Default Callback URL</label>
                        <input type="url" name="callback_url" id="callback_url" class="form-control"
                               placeholder="https://yourproject.com/payment/callback"
                               value="<?= isset($project) ? htmlspecialchars($project->callback_url) : '' ?>">
                        <p class="help-block">Where users are redirected after the payment is complete.</p>
                    </div>

                    <div class="form-group">
                        <label for="webhook_url">Webhook URL</label>
                        <input type="url" name="webhook_url" id="webhook_url" class="form-control"
                               placeholder="https://yourproject.com/api/payment-webhook"
                               value="<?= isset($project) ? htmlspecialchars($project->webhook_url) : '' ?>">
                        <p class="help-block">CRM will POST payment status updates to this URL in real-time.</p>
                    </div>

                    <?php if (isset($project)): ?>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="active"   <?= $project->status === 'active'   ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $project->status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> <?= isset($project) ? 'Update Project' : 'Create Project' ?>
                        </button>
                        <a href="<?= base_url('admin/payment_hub/projects') ?>" class="btn btn-default">Cancel</a>
                    </div>

                </form>
            </div>
        </div>

        <?php if (!isset($project)): ?>
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            <strong>Note:</strong> After creating the project, a unique <strong>API Key</strong> and <strong>API Secret</strong>
            will be generated automatically. Share these with your external project's developers.
        </div>
        <?php endif; ?>
    </div>
</div>
