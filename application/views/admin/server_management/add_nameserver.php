<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<form id="nameserverForm" action="<?= base_url('admin/server_management/add_nameserver') ?>" method="post">
    <div class="modal-body">
        <div class="form-group">
            <label>Nameserver Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="e.g., ns1.example.com" required>
            <small class="form-text text-muted">Enter the nameserver address</small>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>
