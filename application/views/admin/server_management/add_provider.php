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

        <!-- RBAC Section -->
        <?php
        $permissionL = 'all';
        if (!empty($provider_info->permission)) {
            $permissionL = $provider_info->permission;
        }
        ?>
        <div class="row mt-4">
            <div class="col-md-12">
                <div style="background: #fcfcfc; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                        <h4 style="margin: 0; font-size: 15px; font-weight: 700; color: #333; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-shield text-primary" style="margin-right: 8px;"></i> Record Access Permissions
                        </h4>
                        <div class="radio-inline p-0">
                            <label class="radio-inline c-radio">
                                <input type="radio" name="task_permission" value="everyone" <?= ($permissionL == 'all') ? 'checked' : '' ?> class="task_permission_radio_toggle">
                                <span class="fa fa-circle"></span> Everyone
                            </label>
                            <label class="radio-inline c-radio">
                                <input type="radio" name="task_permission" value="custom_permission" <?= ($permissionL != 'all') ? 'checked' : '' ?> class="task_permission_radio_toggle">
                                <span class="fa fa-circle"></span> Specific Users
                            </label>
                        </div>
                    </div>

                    <div class="task_permission_users_wrapper" style="display: <?= ($permissionL != 'all') ? 'block' : 'none' ?>;">
                        <div class="row">
                            <?php if (!empty($staff_members)): ?>
                                <?php foreach ($staff_members as $staff): ?>
                                    <div class="col-lg-4 col-md-6 mb-3">
                                        <?php
                                        $is_admin = ($staff->role_id == 1);
                                        $user_permission = null;
                                        if ($permissionL != 'all') {
                                            $decoded_permission = json_decode($permissionL, true);
                                            if (isset($decoded_permission[$staff->user_id])) {
                                                $user_permission = $decoded_permission[$staff->user_id];
                                            }
                                        }
                                        ?>
                                        <div style="padding: 0; border: 1px solid #e9ecef; border-radius: 8px; background: #fff; height: 100%; transition: all 0.2s ease;">
                                            <div class="checkbox c-checkbox m0">
                                                <label class="needsclick" style="margin-bottom: 0; display: flex; align-items: center; width: 100%; cursor: pointer; padding: 12px;">
                                                    <input type="checkbox" value="<?= $staff->user_id ?>" name="assigned_to[]" class="needsclick assigned_to_task <?= $is_admin ? 'is-admin' : '' ?>" <?= !empty($user_permission) ? 'checked' : '' ?>>
                                                    <span class="fa fa-check" style="margin-top: -10px; left: 12px;"></span>
                                                    <div style="display: flex; align-items: center; margin-left: 25px; flex: 1; overflow: hidden;">
                                                        <img src="<?= base_url() . (!empty($staff->avatar) ? $staff->avatar : 'assets/img/user/default.png') ?>" class="img-circle" style="width: 32px; height: 32px; border: 1px solid #eee; margin-right: 12px; flex-shrink: 0; object-fit: cover;">
                                                        <div style="overflow: hidden; line-height: 1.3;">
                                                            <div style="font-weight: 700; font-size: 13px; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($staff->username) ?></div>
                                                            <div style="font-size: 11px; color: #888; font-weight: 500; text-transform: uppercase; letter-spacing: 0.3px;">
                                                                <?= !empty($staff->designations) ? htmlspecialchars($staff->designations) : ($staff->role_id == 1 ? 'Admin' : 'Staff') ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="action_task_user mt-2" id="action_task_<?= $staff->user_id ?>" style="display: <?= !empty($user_permission) ? 'block' : 'none' ?>; padding-left: 28px;">
                                            <label class="checkbox-inline c-checkbox">
                                                <input type="checkbox" value="view" name="action_<?= $staff->user_id ?>[]" checked disabled>
                                                <span class="fa fa-check"></span> View
                                            </label>
                                            <label class="checkbox-inline c-checkbox">
                                                <input type="checkbox" value="edit" name="action_<?= $staff->user_id ?>[]" <?= ($is_admin || (!empty($user_permission) && in_array('edit', $user_permission))) ? 'checked' : '' ?> <?= $is_admin ? 'disabled' : '' ?>>
                                                <span class="fa fa-check"></span> Edit
                                            </label>
                                            <label class="checkbox-inline c-checkbox">
                                                <input type="checkbox" value="delete" name="action_<?= $staff->user_id ?>[]" <?= ($is_admin || (!empty($user_permission) && in_array('delete', $user_permission))) ? 'checked' : '' ?> <?= $is_admin ? 'disabled' : '' ?>>
                                                <span class="fa fa-check"></span> Delete
                                            </label>
                                            <input type="hidden" name="action_<?= $staff->user_id ?>[]" value="view">
                                            <?php if($is_admin): ?>
                                                <input type="hidden" name="action_<?= $staff->user_id ?>[]" value="edit">
                                                <input type="hidden" name="action_<?= $staff->user_id ?>[]" value="delete">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                $(document).on('change', '.task_permission_radio_toggle', function() {
                    var $form = $(this).closest('form');
                    var $userList = $form.find('.task_permission_users_wrapper');
                    if ($(this).val() == 'custom_permission') {
                        $userList.slideDown();
                        $userList.find('.assigned_to_task.is-admin').prop('checked', true).trigger('change');
                    } else {
                        $userList.slideUp();
                    }
                });

                $('.assigned_to_task').change(function() {
                    var user_id = $(this).val();
                    if ($(this).is(':checked')) {
                        $('#action_task_' + user_id).slideDown();
                    } else {
                        $('#action_task_' + user_id).slideUp();
                    }
                });
            });
        </script>

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