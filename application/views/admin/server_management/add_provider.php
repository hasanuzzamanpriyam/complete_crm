<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Add Provider</h5>
            </div>
            <div class="card-body">
                <form>
                    <!-- Row 1 -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Provider Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" placeholder="e.g. GoDaddy, Namecheap" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Provider URL <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" placeholder="e.g. https://godaddy.com" required>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2 -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Provider Type <span class="text-danger">*</span></label>
                                <select class="form-control" required>
                                    <option value="">Select</option>
                                    <option value="Hosting">Hosting</option>
                                    <option value="Domain">Domain</option>
                                    <option value="Both">Both</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status <span class="text-danger">*</span></label>
                                <select class="form-control" required>
                                    <option value="">Select</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Row 3 -->
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea class="form-control" rows="4" placeholder="Enter provider description"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer bg-light">
                <a href="<?= base_url('admin/server_management/provider') ?>" class="text-muted mr-3">Cancel</a>
                <button type="submit" class="btn btn-danger"><i class="fas fa-check"></i> Save</button>
            </div>
        </div>
    </div>
</div>