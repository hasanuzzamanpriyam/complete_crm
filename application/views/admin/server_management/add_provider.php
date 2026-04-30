<?php if (!$this->input->is_ajax_request()): ?>
<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Add Provider</h5>
            </div>
            <div class="card-body">
<?php else: ?>
<div class="panel panel-custom">
    <div class="panel-heading">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">Add Provider</h4>
    </div>
<?php endif; ?>

<form action="<?= base_url('admin/server_management/add_provider') ?>" method="post" accept-charset="utf-8" id="providerForm" class="form-horizontal">
    <?php if ($this->input->is_ajax_request()): ?>
    <div class="modal-body">
    <?php endif; ?>

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

    <?php if ($this->input->is_ajax_request()): ?>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
    <?php endif; ?>

<?php if (!$this->input->is_ajax_request()): ?>
            </div>
            <div class="card-footer bg-light">
                <a href="<?= base_url('admin/server_management/provider') ?>" class="text-muted mr-3">Cancel</a>
                <button type="submit" class="btn btn-danger">Save</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        // Validation with AJAX submission
        $("#providerForm").validate({
            rules: {
                provider_name: {
                    required: true
                },
                provider_url: {
                    required: true
                },
                provider_type: {
                    required: true
                },
                status: {
                    required: true
                }
            },
            submitHandler: function (form) {
                <?php if ($this->input->is_ajax_request()): ?>
                var formData = $(form).serialize();
                $.ajax({
                    type: "POST",
                    url: $(form).attr('action'),
                    data: formData,
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 'success') {
                            // Update dropdown if it exists
                            var $select = $('#provider_id');
                            if ($select.length) {
                                var newOption = new Option(data.text, data.id, true, true);
                                $select.append(newOption).trigger('change');
                                
                                // Specific refresh for Select2 if active
                                if ($select.hasClass("select2-hidden-accessible")) {
                                    $select.select2('destroy').select2();
                                }
                            }
                            
                            // Close modal with multiple fallbacks
                            $('#myModal').modal('hide');
                            $('.modal').modal('hide');
                            
                            // Cleanup backdrop manually if it sticks
                            setTimeout(function() {
                                if ($('.modal-backdrop').length) {
                                    $('.modal-backdrop').remove();
                                    $('body').removeClass('modal-open');
                                }
                            }, 500);
                            
                            // Notify user
                            if (typeof toastr !== 'undefined') {
                                toastr.success(data.message || 'Provider added successfully');
                            }
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(data.message || 'Error occurred');
                            } else {
                                alert(data.message);
                            }
                        }
                    },
                    error: function(xhr) {
                        alert('An error occurred while saving. Please try again.');
                        console.log(xhr.responseText);
                    }
                });
                return false;
                <?php else: ?>
                form.submit();
                <?php endif; ?>
            }
        });
    });
</script>