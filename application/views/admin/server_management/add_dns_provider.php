<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<form id="dnsProviderForm" action="<?= base_url('admin/server_management/add_dns_provider') ?>" method="post">
    <div class="modal-body">
        <div class="form-group">
            <label>DNS Provider Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="e.g., Cloudflare, Google DNS" required>
            <small class="form-text text-muted">Enter the name of the DNS provider</small>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>
