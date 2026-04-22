<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Add Provider</h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('admin/server_management/add_provider') ?>" method="post" accept-charset="utf-8" id="providerForm">
                    <input type="hidden" name="provider_id" value="<?= !empty($provider_info->id) ? $provider_info->id : '' ?>">
                    <!-- Row 1 -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Provider Name <span class="text-danger">*</span></label>
                                <input type="text" name="provider_name" class="form-control" placeholder="e.g. GoDaddy, Namecheap" value="<?= set_value('provider_name', !empty($provider_info->provider_name) ? $provider_info->provider_name : '') ?>" required>
                                <?= form_error('provider_name', '<small class="text-danger">', '</small>') ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Provider URL <span class="text-danger">*</span></label>
                                <input type="text" name="provider_url" class="form-control" placeholder="e.g. https://godaddy.com" value="<?= set_value('provider_url', !empty($provider_info->provider_url) ? $provider_info->provider_url : '') ?>" required>
                                <?= form_error('provider_url', '<small class="text-danger">', '</small>') ?>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2 -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Provider Type <span class="text-danger">*</span></label>
                                <select name="provider_type" class="form-control" required>
                                    <option value="">Select</option>
                                    <option value="Hosting" <?= set_select('provider_type', 'Hosting', (!empty($provider_info->provider_type) && $provider_info->provider_type == 'Hosting')) ?>>Hosting</option>
                                    <option value="Domain" <?= set_select('provider_type', 'Domain', (!empty($provider_info->provider_type) && $provider_info->provider_type == 'Domain')) ?>>Domain</option>
                                    <option value="Both" <?= set_select('provider_type', 'Both', (!empty($provider_info->provider_type) && $provider_info->provider_type == 'Both')) ?>>Both</option>
                                </select>
                                <?= form_error('provider_type', '<small class="text-danger">', '</small>') ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-control" required>
                                    <option value="">Select</option>
                                    <option value="Active" <?= set_select('status', 'Active', (!empty($provider_info->status) && $provider_info->status == 'Active')) ?>>Active</option>
                                    <option value="Inactive" <?= set_select('status', 'Inactive', (!empty($provider_info->status) && $provider_info->status == 'Inactive')) ?>>Inactive</option>
                                </select>
                                <?= form_error('status', '<small class="text-danger">', '</small>') ?>
                            </div>
                        </div>
                    </div>

                    <!-- Row 3 -->
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Enter provider description"><?= set_value('description', !empty($provider_info->description) ? $provider_info->description : '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer bg-light">
                <a href="<?= base_url('admin/server_management/provider') ?>" class="text-muted mr-3">Cancel</a>
                <button type="submit" form="providerForm" class="btn btn-danger">Save</button>
            </div>
        </div>
    </div>
</div>