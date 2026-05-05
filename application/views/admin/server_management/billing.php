<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<script src="<?= base_url() ?>assets/plugins/dataTables/js/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/plugins/dataTables/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>assets/plugins/dataTables/js/dataTables.bootstrap.min.js"></script>

<style>
    /* DataTables Custom Styling */
    .dataTables_wrapper .dataTables_filter {
        float: right;
        margin-bottom: 10px;
    }
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #ddd;
        padding: 5px 10px;
        border-radius: 2px;
        margin-left: 10px;
        outline: none;
    }
    .dataTables_wrapper .dataTables_length {
        float: left;
        margin-bottom: 10px;
    }
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #ddd;
        padding: 5px;
        border-radius: 2px;
        outline: none;
    }
    .dataTables_wrapper .dataTables_info {
        float: left;
        margin-top: 15px;
        color: #666;
        font-size: 13px;
    }
    .dataTables_wrapper .dataTables_paginate {
        float: right;
        margin-top: 15px;
    }
    .dataTables_paginate .paginate_button {
        display: inline-block;
        padding: 5px 12px;
        border: 1px solid #ddd;
        margin-left: -1px;
        cursor: pointer;
        color: #333 !important;
        background: #fff;
        text-decoration: none;
    }
    .dataTables_paginate .paginate_button.current {
        background: #f44336 !important;
        color: #fff !important;
        border-color: #f44336;
        z-index: 2;
    }
    .dataTables_paginate .paginate_button:hover:not(.current) {
        background: #eee !important;
    }
    .dataTables_paginate .paginate_button.disabled {
        cursor: default;
        opacity: 0.5;
        color: #999 !important;
    }
    /* Hide empty pagination boxes if any */
    .dataTables_paginate .paginate_button:empty {
        display: none !important;
    }
</style>

<style>
    /* ERP Style Redesign - Flat Admin Theme */
    .erp-container {
        font-family: 'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif;
    }

    .erp-card {
        border: none;
        border-radius: 0;
        box-shadow: none !important;
        background-color: transparent;
    }

    .erp-card .card-body {
        padding: 0;
    }

    .erp-section-title {
        font-size: 13px;
        font-weight: 600;
        color: #444;
        padding-bottom: 10px;
        margin-top: 20px;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .erp-form label {
        font-size: 11px;
        font-weight: 600;
        color: #777;
        text-transform: uppercase;
        margin-bottom: 5px;
        letter-spacing: 0.5px;
    }

    .erp-form .form-control {
        border-radius: 2px;
        border: 1px solid #d2d6de;
        font-size: 13px;
        height: 36px;
        box-shadow: none;
        padding: 6px 12px;
        color: #555;
        background-color: #fff;
    }

    .erp-form .form-control:focus {
        border-color: #3c8dbc;
        box-shadow: none;
    }

    /* Table Styling */
    .erp-table {
        background-color: #fff;
        border: 1px solid #f4f4f4;
        margin-bottom: 30px;
    }

    .erp-table thead th {
        background-color: #fff;
        font-size: 11px;
        text-transform: uppercase;
        color: #444;
        font-weight: 600;
        border-bottom: 2px solid #f4f4f4 !important;
        border-top: none !important;
        padding: 12px 10px;
    }

    .erp-table tbody td {
        font-size: 13px;
        color: #444;
        padding: 12px 10px;
        vertical-align: middle;
        border-top: 1px solid #f4f4f4;
    }
    
    .erp-table-empty {
        padding: 20px !important;
        color: #999 !important;
        font-size: 14px;
        background-color: #fff;
    }

    /* Buttons */
    .btn-erp-success {
        background-color: #00a65a;
        border-color: #008d4c;
        font-size: 13px;
        font-weight: 600;
        padding: 8px 20px;
        border-radius: 2px;
        color: #fff;
    }

    .btn-erp-success:hover, .btn-erp-success:focus {
        background-color: #008d4c;
        color: #fff;
    }

    .btn-erp-info {
        background-color: #00c0ef;
        border-color: #00acd6;
        font-size: 12px;
        font-weight: 600;
        padding: 6px 15px;
        border-radius: 2px;
        color: #fff;
    }

    .btn-erp-info:hover, .btn-erp-info:focus {
        background-color: #00acd6;
        color: #fff;
    }

    .btn-erp-danger {
        background-color: #f56954;
        border-color: #f4543c;
        color: #fff;
        border-radius: 2px;
        height: 36px;
        width: 100%;
    }

    .btn-erp-danger:hover {
        background-color: #d73925;
        color: #fff;
    }

    /* Input Group Fixes */
    .billing-item-row .input-group {
        display: flex;
        flex-wrap: nowrap;
    }

    .billing-item-row .currency-wrapper select {
        border-radius: 2px 0 0 2px;
        border-right: none;
        background-color: #f9f9f9;
        width: 85px !important;
        padding: 6px 5px;
    }

    .billing-item-row .input-group > .form-control:not(:first-child) {
        border-radius: 0 2px 2px 0;
    }

    .billing-item-row .input-group > .form-control:first-child:not(:last-child) {
        border-radius: 2px 0 0 2px;
    }
</style>

<div class="row erp-container">
    <div class="col-md-12">
        <div class="card erp-card">
            <div class="card-body">
                <!-- Add New Billing Form (Collapsible) -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="pull-right">
                            <button class="btn btn-erp-info" type="button" data-toggle="collapse" data-target="#addNewBillingCollapse" aria-expanded="false" aria-controls="addNewBillingCollapse">
                                <i class="fa fa-plus"></i> <?= lang('add_billing_item') ?: 'ADD NEW BILLING ITEMS' ?>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="collapse" id="addNewBillingCollapse">
                    <div class="card card-body mb-5" style="background: #fdfdfd; border: 1px solid #eee; padding: 25px; border-radius: 4px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                        <div class="erp-section-title" style="margin-top: 0; border-bottom: 2px solid #eee; margin-bottom: 25px;"><?= lang('add_billing_item') ?: 'ADD NEW BILLING ITEMS' ?></div>
                        <form id="billing_form" class="erp-form">
                            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                            
                            <div id="billing_items_container">
                                <div class="billing-item-container mb-4" style="border-bottom: 2px solid #f4f4f4; padding-bottom: 20px; margin-bottom: 30px !important;">
                                    <div class="row billing-item-row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><?= lang('billing_label') ?: 'LABEL' ?></label>
                                                <input type="text" name="label[]" class="form-control" placeholder="Enter Label" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><?= lang('billing_value') ?: 'VALUE' ?></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend currency-wrapper" style="display: none;">
                                                        <select name="currency[]" class="form-control">
                                                            <option value="">Select</option>
                                                            <?php if (!empty($currencies)): ?>
                                                                <?php foreach ($currencies as $currency): ?>
                                                                    <option value="<?= $currency['code'] ?>"><?= $currency['code'] ?></option>
                                                                  <?php endforeach; ?>
                                                            <?php endif; ?>
                                                        </select>
                                                    </div>
                                                    <input type="text" name="value[]" class="form-control" placeholder="Enter value" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><?= lang('billing_type') ?: 'BILLING TYPE' ?></label>
                                                <select name="type[]" class="form-control type-selector">
                                                    <option value="text">Text</option>
                                                    <option value="date">Date</option>
                                                    <option value="currency">Currency</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-erp-danger remove-row" title="Remove Item"><i class="fa fa-times"></i></button>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label><?= lang('renewal_date') ?: 'RENEWAL DATE' ?></label>
                                                <input type="date" name="renewal_date[]" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label><?= lang('expiry_date') ?: 'EXPIRY DATE' ?></label>
                                                <input type="date" name="expiry_date[]" class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><?= lang('duration') ?: 'DURATION' ?></label>
                                                <input type="number" name="duration[]" class="form-control" placeholder="Enter duration">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label><?= lang('time_unit') ?: 'TIME UNIT' ?></label>
                                                <select name="time_unit[]" class="form-control">
                                                    <option value="Days">Days</option>
                                                    <option value="Weeks">Weeks</option>
                                                    <option value="Months">Months</option>
                                                    <option value="Years">Years</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><?= lang('renew') ?: 'RENEW' ?></label>
                                                <select name="renew[]" class="form-control">
                                                    <option value="manual">Manual</option>
                                                    <option value="automatic">Automatic</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <button type="button" id="add_more_billing" class="btn btn-erp-info"><i class="fa fa-plus"></i> Add Another Item</button>
                                </div>
                            </div>

                            <div class="row mt-4" style="margin-top: 30px;">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-erp-success" id="save_btn" style="padding: 10px 30px;">Save Billing Items</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Existing Billing Table -->
                <div class="erp-section-title"><?= lang('existing_billing') ?: 'EXISTING BILLING ORDERS' ?></div>
                <div class="table-responsive" style="margin-top: 20px;">
                    <table id="billingDataTable" class="table table-hover erp-table">
                        <thead>
                            <tr>
                                <th><?= lang('billing_label') ?: 'LABEL' ?></th>
                                <th><?= lang('billing_value') ?: 'VALUE' ?></th>
                                <th><?= lang('billing_type') ?: 'BILLING TYPE' ?></th>
                                <th><?= lang('currency') ?: 'CURRENCY' ?></th>
                                <th><?= lang('renewal_date') ?: 'RENEWAL DATE' ?></th>
                                <th><?= lang('expiry_date') ?: 'EXPIRY DATE' ?></th>
                                <th><?= lang('remaining') ?: 'REMAINING' ?></th>
                                <th><?= lang('duration') ?: 'DURATION' ?></th>
                                <th><?= lang('renew') ?: 'RENEW' ?></th>
                                <th style="width: 100px;" class="text-center"><?= lang('action') ?: 'ACTION' ?></th>
                            </tr>
                        </thead>
                        <tbody id="billing_table_body">
                            <?php if (!empty($billings)): ?>
                                <?php foreach ($billings as $billing): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($billing['label']) ?></td>
                                        <td><?= htmlspecialchars($billing['value']) ?></td>
                                        <td><?= ucfirst($billing['type']) ?></td>
                                        <td><?= !empty($billing['currency']) ? htmlspecialchars($billing['currency']) : '-' ?></td>
                                        <td><?= !empty($billing['renewal_date']) ? date('M j, Y', strtotime($billing['renewal_date'])) : '-' ?></td>
                                        <td><?= !empty($billing['expiry_date']) ? date('M j, Y', strtotime($billing['expiry_date'])) : '-' ?></td>
                                        <td>
                                            <?php
                                            if (!empty($billing['expiry_date']) && $billing['expiry_date'] != '1000-01-01' && $billing['expiry_date'] != '0000-00-00') {
                                                $expiry = new DateTime($billing['expiry_date']);
                                                $now = new DateTime(date('Y-m-d'));
                                                if ($expiry < $now) {
                                                    echo '<span class="text-danger" style="font-weight:bold;">Expired</span>';
                                                } else {
                                                    $diff = $now->diff($expiry);
                                                    if ($diff->days == 0) {
                                                        echo '<span class="text-warning" style="font-weight:bold;">Expires Today</span>';
                                                    } else {
                                                        echo $diff->days . ' Days';
                                                    }
                                                }
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td><?= !empty($billing['duration']) ? htmlspecialchars($billing['duration']) . ' ' . ($billing['time_unit'] ?? '') : '-' ?></td>
                                        <td><?= !empty($billing['renew']) ? ucfirst($billing['renew']) : '-' ?></td>
                                        <td class="text-center">
                                            <a href="javascript:void(0)" class="text-primary edit-billing-btn" 
                                               data-id="<?= $billing['id'] ?>" 
                                               data-label="<?= htmlspecialchars($billing['label']) ?>"
                                               data-value="<?= htmlspecialchars($billing['value']) ?>"
                                               data-type="<?= $billing['type'] ?>"
                                               data-currency="<?= $billing['currency'] ?>"
                                               data-renewal="<?= $billing['renewal_date'] ?>"
                                               data-expiry="<?= $billing['expiry_date'] ?>"
                                               data-duration="<?= $billing['duration'] ?>"
                                               data-timeunit="<?= $billing['time_unit'] ?>"
                                               data-renew="<?= $billing['renew'] ?>"
                                               title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <a href="<?= base_url('admin/server_management/delete_billing/' . $billing['id']) ?>" class="text-danger ml-2" title="Delete" onclick="return confirm('Are you sure you want to delete this item?')">
                                                <i class="fa fa-times"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center erp-table-empty">No billing orders found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Add more rows
    $('#add_more_billing').click(function() {
        var newContainer = $('.billing-item-container:first').clone();
        newContainer.find('input').val('');
        // Reset selects to first option
        newContainer.find('select').each(function() {
            $(this).val($(this).find('option:first').val());
        });
        // Special handling for type selector to hide currency wrapper
        newContainer.find('.type-selector').val('text');
        newContainer.find('.currency-wrapper').hide();
        newContainer.find('input[name="value[]"]').attr('type', 'text');
        
        $('#billing_items_container').append(newContainer);
    });

    // Remove row
    $(document).on('click', '.remove-row', function() {
        if ($('.billing-item-container').length > 1) {
            $(this).closest('.billing-item-container').remove();
        } else {
            // Optional: clear values instead of alert if only one row is left
            var container = $(this).closest('.billing-item-container');
            container.find('input').val('');
            container.find('select').each(function() {
                $(this).val($(this).find('option:first').val());
            });
            container.find('.currency-wrapper').hide();
            container.find('input[name="value[]"]').attr('type', 'text');
        }
    });

    // Auto-calculate Expiry Date
    function updateExpiryDate(container) {
        var renewalDate = container.find('input[name="renewal_date[]"]').val();
        var duration = container.find('input[name="duration[]"]').val();
        var timeUnit = container.find('select[name="time_unit[]"]').val();
        var expiryInput = container.find('input[name="expiry_date[]"]');

        if (renewalDate && duration && timeUnit) {
            var date = new Date(renewalDate);
            duration = parseInt(duration);
            
            if (isNaN(duration) || duration <= 0) return;

            if (timeUnit === 'Days') {
                date.setDate(date.getDate() + duration);
            } else if (timeUnit === 'Weeks') {
                date.setDate(date.getDate() + (duration * 7));
            } else if (timeUnit === 'Months') {
                date.setMonth(date.getMonth() + duration);
            } else if (timeUnit === 'Years') {
                date.setFullYear(date.getFullYear() + duration);
            }

            var year = date.getFullYear();
            var month = ('0' + (date.getMonth() + 1)).slice(-2);
            var day = ('0' + date.getDate()).slice(-2);
            expiryInput.val(year + '-' + month + '-' + day);
        }
    }

    $(document).on('change keyup', 'input[name="renewal_date[]"], input[name="duration[]"], select[name="time_unit[]"]', function() {
        var container = $(this).closest('.billing-item-container');
        updateExpiryDate(container);
    });

    // Handle type changes
    $(document).on('change', '.type-selector', function() {
        var type = $(this).val();
        var row = $(this).closest('.billing-item-row');
        var valueInput = row.find('input[name="value[]"]');
        var currencyWrapper = row.find('.currency-wrapper');
        
        if (type === 'date') {
            valueInput.attr('type', 'date');
            currencyWrapper.hide();
        } else if (type === 'currency') {
            valueInput.attr('type', 'number');
            valueInput.attr('step', '0.01');
            currencyWrapper.show();
        } else {
            valueInput.attr('type', 'text');
            currencyWrapper.hide();
        }
    });

    // AJAX Form Submission
    $('#billing_form').submit(function(e) {
        e.preventDefault();
        var $btn = $('#save_btn');
        var originalText = $btn.text();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: '<?= base_url('admin/server_management/save_billing') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message);
                    } else {
                        alert(response.message);
                    }
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(response.message);
                    } else {
                        alert(response.message);
                    }
                    $btn.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                alert('An error occurred while processing the request.');
                $btn.prop('disabled', false).text(originalText);
            }
        });
    });
});
</script>

<!-- Edit Billing Modal -->
<div class="modal fade" id="editBillingModal" tabindex="-1" role="dialog" aria-labelledby="editBillingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 2px;">
            <div class="modal-header" style="background-color: #f4f4f4; border-bottom: 1px solid #ddd;">
                <h5 class="modal-title" id="editBillingModalLabel" style="font-size: 14px; font-weight: bold; color: #333; text-transform: uppercase;">Edit Billing Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="edit_billing_form" class="erp-form">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?= lang('billing_label') ?: 'LABEL' ?></label>
                                <input type="text" name="label" id="edit_label" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?= lang('billing_type') ?: 'BILLING TYPE' ?></label>
                                <select name="type" id="edit_type" class="form-control edit-type-selector">
                                    <option value="text">Text</option>
                                    <option value="date">Date</option>
                                    <option value="currency">Currency</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><?= lang('billing_value') ?: 'VALUE' ?></label>
                                <div class="input-group">
                                    <div class="input-group-prepend edit-currency-wrapper" style="display: none;">
                                        <select name="currency" id="edit_currency" class="form-control" style="width: 85px; border-radius: 2px 0 0 2px; border-right: none; background-color: #f9f9f9;">
                                            <option value="">Select</option>
                                            <?php if (!empty($currencies)): ?>
                                                <?php foreach ($currencies as $currency): ?>
                                                    <option value="<?= $currency['code'] ?>"><?= $currency['code'] ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <input type="text" name="value" id="edit_value" class="form-control" required style="border-radius: 0 2px 2px 0;">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?= lang('renewal_date') ?: 'RENEWAL DATE' ?></label>
                                <input type="date" name="renewal_date" id="edit_renewal_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?= lang('expiry_date') ?: 'EXPIRY DATE' ?></label>
                                <input type="date" name="expiry_date" id="edit_expiry_date" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-2">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('duration') ?: 'DURATION' ?></label>
                                <input type="number" name="duration" id="edit_duration" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('time_unit') ?: 'TIME UNIT' ?></label>
                                <select name="time_unit" id="edit_time_unit" class="form-control">
                                    <option value="Days">Days</option>
                                    <option value="Weeks">Weeks</option>
                                    <option value="Months">Months</option>
                                    <option value="Years">Years</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('renew') ?: 'RENEW' ?></label>
                                <select name="renew" id="edit_renew" class="form-control">
                                    <option value="manual">Manual</option>
                                    <option value="automatic">Automatic</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background-color: #f9f9f9; border-top: 1px solid #ddd;">
                    <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 2px; font-size: 12px; font-weight: 600; text-transform: uppercase;">Close</button>
                    <button type="submit" class="btn btn-erp-success" id="update_btn" style="padding: 6px 20px;">Update Billing Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Edit Button Click
    $(document).on('click', '.edit-billing-btn', function() {
        var btn = $(this);
        $('#edit_id').val(btn.data('id'));
        $('#edit_label').val(btn.data('label'));
        $('#edit_value').val(btn.data('value'));
        $('#edit_type').val(btn.data('type')).trigger('change');
        $('#edit_currency').val(btn.data('currency'));
        $('#edit_renewal_date').val(btn.data('renewal'));
        $('#edit_expiry_date').val(btn.data('expiry'));
        $('#edit_duration').val(btn.data('duration'));
        $('#edit_time_unit').val(btn.data('timeunit'));
        $('#edit_renew').val(btn.data('renew'));
        
        $('#editBillingModal').modal('show');
    });

    // Handle type changes in Edit Modal
    $(document).on('change', '.edit-type-selector', function() {
        var type = $(this).val();
        var valueInput = $('#edit_value');
        var currencyWrapper = $('.edit-currency-wrapper');
        
        if (type === 'date') {
            valueInput.attr('type', 'date');
            currencyWrapper.hide();
            valueInput.css('border-radius', '2px');
        } else if (type === 'currency') {
            valueInput.attr('type', 'number');
            valueInput.attr('step', '0.01');
            currencyWrapper.show();
            valueInput.css('border-radius', '0 2px 2px 0');
        } else {
            valueInput.attr('type', 'text');
            currencyWrapper.hide();
            valueInput.css('border-radius', '2px');
        }
    });

    // Auto-calculate Expiry Date in Edit Modal
    $(document).on('change keyup', '#edit_renewal_date, #edit_duration, #edit_time_unit', function() {
        var renewalDate = $('#edit_renewal_date').val();
        var duration = $('#edit_duration').val();
        var timeUnit = $('#edit_time_unit').val();
        var expiryInput = $('#edit_expiry_date');

        if (renewalDate && duration && timeUnit) {
            var date = new Date(renewalDate);
            duration = parseInt(duration);
            if (isNaN(duration) || duration <= 0) return;

            if (timeUnit === 'Days') date.setDate(date.getDate() + duration);
            else if (timeUnit === 'Weeks') date.setDate(date.getDate() + (duration * 7));
            else if (timeUnit === 'Months') date.setMonth(date.getMonth() + duration);
            else if (timeUnit === 'Years') date.setFullYear(date.getFullYear() + duration);

            var year = date.getFullYear();
            var month = ('0' + (date.getMonth() + 1)).slice(-2);
            var day = ('0' + date.getDate()).slice(-2);
            expiryInput.val(year + '-' + month + '-' + day);
        }
    });

    // Edit AJAX Submission
    $('#edit_billing_form').submit(function(e) {
        e.preventDefault();
        var $btn = $('#update_btn');
        var originalText = $btn.text();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');
        
        $.ajax({
            url: '<?= base_url('admin/server_management/edit_billing') ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    if (typeof toastr !== 'undefined') toastr.success(response.message);
                    else alert(response.message);
                    setTimeout(function() { location.reload(); }, 1000);
                } else {
                    if (typeof toastr !== 'undefined') toastr.error(response.message);
                    else alert(response.message);
                    $btn.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                alert('An error occurred.');
                $btn.prop('disabled', false).text(originalText);
            }
        });
    });

    // Initialize DataTables
    $('#billingDataTable').DataTable({
        "order": [], // Initial order
        "pageLength": 10,
        "pagingType": "simple_numbers",
        "language": {
            "search": "Search:",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "paginate": {
                "next": "Next",
                "previous": "Previous"
            }
        },
        "columnDefs": [{
            "orderable": false,
            "targets": [9] // Action column
        }]
    });
});
</script>