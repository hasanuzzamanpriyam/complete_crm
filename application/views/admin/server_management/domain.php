<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<script src="<?= base_url() ?>assets/plugins/dataTables/js/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/plugins/dataTables/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>assets/plugins/dataTables/js/dataTables.bootstrap.min.js"></script>

<style>
    /* Design & UI Fixes */
    .card {
        border: none;
        border-radius: 8px;
    }

    .table thead th {
        background-color: #f8f9fa;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #dee2e6;
        vertical-align: middle;
    }

    .table td {
        vertical-align: middle;
        color: #495057;
        font-size: 13px;
    }

    /* Badges */
    .badge {
        padding: 5px 10px;
        font-weight: 500;
        border-radius: 4px;
        font-size: 11px;
    }

    .badge-expired {
        background-color: #dc3545;
        color: #fff;
    }

    .badge-pending {
        background-color: #ffc107;
        color: #212529;
    }

    .badge-transferring {
        background-color: #6c757d;
        color: #fff;
    }

    .badge-active {
        background-color: #28a745;
        color: #fff;
    }

    .badge-domain-type {
        background-color: #17a2b8;
        color: #fff;
    }

    .badge-secondary {
        background-color: #6c757d;
        color: #fff;
    }

    /* Action Button Styles */
    .btn-action {
        border: 1px solid #dee2e6;
        background: #fff;
        color: #6c757d;
        padding: 4px 8px;
        border-radius: 4px;
        transition: all 0.2s;
        display: inline-block;
    }

    .btn-action:hover {
        background: #f8f9fa;
        color: #333;
    }

    /* DataTables Overrides to match UI */
    .dataTables_wrapper .dataTables_filter {
        display: none;
    }

    .dataTables_wrapper .dataTables_length {
        display: none;
    }

    .dataTables_wrapper .dataTables_info {
        color: #6c757d;
        font-size: 13px;
        padding-top: 15px;
    }

    .pagination .page-link {
        color: #6c757d;
        border: 1px solid #dee2e6;
        padding: 5px 12px;
    }

    .pagination .page-item.active .page-link {
        background-color: #f44336;
        border-color: #f44336;
        color: #fff;
    }

    /* Sorting Icons */
    table.dataTable thead .sorting:after,
    table.dataTable thead .sorting_asc:after,
    table.dataTable thead .sorting_desc:after {
        font-family: 'FontAwesome';
        float: right;
        opacity: 0.5;
    }

    table.dataTable thead .sorting:after {
        content: "\f0dc";
    }

    table.dataTable thead .sorting_asc:after {
        content: "\f0de";
        opacity: 1;
    }

    table.dataTable thead .sorting_desc:after {
        content: "\f0dd";
        opacity: 1;
    }
    @media (min-width: 992px) {
    .col-md-2 {
        padding-left: 7%;
    }
}
</style>

<div class="row mb-lg">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">

                <form id="filterForm" method="GET" action="<?= base_url('admin/server_management/domain') ?>">
                <div class="row mb-4 align-items-end">
                    <div class="col-md-3">
                        <label class="text-muted small mb-1">Expiry Period</label>
                        <div class="input-group">
                            <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="<?= $filters['start_date'] ?>">
                            <span class="input-group-addon" style="padding: 4px 8px; background: #eee; border: 1px solid #ccc; border-left: none; border-right: none;"><i class="fa fa-minus text-muted" style="font-size:10px;"></i></span>
                            <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="<?= $filters['end_date'] ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="text-muted small mb-1">Status</label>
                        <select name="status" id="filter_status" class="form-control form-control-sm">
                            <option value="All" <?= $filters['status'] == 'All' ? 'selected' : '' ?>>All Status</option>
                            <?php if (!empty($domain_statuses)): ?>
                                <?php foreach ($domain_statuses as $status): ?>
                                    <option value="<?= htmlspecialchars($status['status_name']) ?>" <?= $filters['status'] == $status['status_name'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($status['status_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="text-muted small mb-1">Provider</label>
                        <select name="provider_id" id="filter_provider" class="form-control form-control-sm">
                            <option value="All" <?= $filters['provider_id'] == 'All' ? 'selected' : '' ?>>All Providers</option>
                            <?php if (!empty($providers)): ?>
                                <?php foreach ($providers as $provider): ?>
                                    <option value="<?= $provider['id'] ?>" <?= $filters['provider_id'] == $provider['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($provider['provider_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="text-muted small mb-1">&nbsp;</label>
                        <div class="input-group">
                            <input type="text" name="search" id="customSearch" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['search']) ?>" placeholder="Start typing to search domains...">
                            <span class="input-group-addon" style="padding: 4px 10px; background: #fff;"><button type="submit" style="border:none; background:none; padding:0;"><i class="fa fa-search text-muted"></i></button></span>
                        </div>
                    </div>
                </div>
                </form>

                <div class="row mb-3 align-items-center">
                    <div class="col-md-6">
                        <button id="bulkDeleteBtn" class="btn btn-sm btn-outline-danger" style="display: none;"><i class="fa fa-trash"></i> Delete Selected</button>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="<?= base_url('admin/server_management/hosting_names') ?>" class="btn btn-sm btn-info"><i class="fa fa-list"></i> Manage Hostings</a>
                        <a href="<?= base_url('admin/server_management/add_domain') ?>" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i> Add Domain</a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="domainDataTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 30px;"><input type="checkbox" id="selectAll"></th>
                                <th>Domain Name</th>
                                <th>Provider</th>
                                <th>Domain Type</th>
                                <th>Status</th>
                                <th>Expiry Date</th>
                                <th>Days Remaining</th>
                                <th>Hosting</th>
                                <th class="text-center" style="width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($domains)): ?>
                                <?php foreach ($domains as $domain): ?>
                                    <tr>
                                        <td><input type="checkbox" class="row-checkbox" value="<?= $domain['id'] ?>" <?= !empty($domain['is_locked']) ? 'disabled' : '' ?>></td>
                                        <td>
                                            <span class="font-weight-bold"><?= htmlspecialchars($domain['domain_name']) ?></span>
                                            <?php if (!empty($domain['is_locked'])): ?>
                                                <i class="fa fa-lock text-danger ml-1" title="Locked"></i>
                                            <?php endif; ?>
                                            <?php if (!empty($domain['is_for_sale'])): ?>
                                                <span class="badge badge-warning ml-1" style="background-color: #ffc107; color: #212529; font-size: 9px; padding: 2px 5px;">FOR SALE</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= !empty($domain['provider_name']) ? htmlspecialchars($domain['provider_name']) : '-' ?></td>
                                        <td><span class="badge badge-domain-type"><?= htmlspecialchars($domain['domain_type']) ?></span></td>
                                        <td>
                                            <?php
                                            $badge_class = '';
                                            $current_status = $domain['status'];
                                            
                                            // Fallback/Safety: Force Expired status in view if date has passed
                                            if ($domain['days_remaining'] < 0) {
                                                $current_status = 'Expired';
                                            }

                                            switch ($current_status) {
                                                case 'Expired':
                                                    $badge_class = 'badge-expired';
                                                    break;
                                                case 'Expiring':
                                                    $badge_class = 'badge-pending';
                                                    break;
                                                case 'Pending':
                                                    $badge_class = 'badge-pending';
                                                    break;
                                                case 'Transferring':
                                                    $badge_class = 'badge-transferring';
                                                    break;
                                                case 'Active':
                                                    $badge_class = 'badge-active';
                                                    break;
                                                default:
                                                    $badge_class = 'badge-secondary';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($current_status) ?></span>
                                        </td>
                                        <td><?= $domain['expiry_date'] ?></td>
                                        <td>
                                            <?php
                                            $days = $domain['days_remaining'];
                                            if ($days > 0):
                                            ?>
                                                <span class="badge badge-pill badge-active"><?= $days ?> days left</span>
                                            <?php elseif ($days == 0): ?>
                                                <span class="badge badge-pill badge-pending">Expires today</span>
                                            <?php else: ?>
                                                <span class="badge badge-pill badge-expired">Expired <?= abs($days) ?> days ago</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= !empty($domain['hosting_name']) ? htmlspecialchars($domain['hosting_name']) : '-' ?></td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="<?= base_url('admin/server_management/view_domain/' . $domain['id']) ?>" 
                                                   class="btn-action view-domain" 
                                                   data-id="<?= $domain['id'] ?>" 
                                                   title="View Details">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="<?= base_url('admin/server_management/add_domain/' . $domain['id']) ?>" class="btn-action <?= !empty($domain['is_locked']) ? 'disabled' : '' ?>" title="<?= !empty($domain['is_locked']) ? 'Locked' : 'Edit' ?>"><i class="fa fa-pencil-square-o"></i></a>
                                                
                                                <a href="javascript:void(0)" 
                                                   class="btn-action toggle-lock" 
                                                   data-id="<?= $domain['id'] ?>" 
                                                   data-status="<?= !empty($domain['is_locked']) ? 1 : 0 ?>"
                                                   title="<?= !empty($domain['is_locked']) ? 'Unlock Domain' : 'Lock Domain' ?>">
                                                    <i class="fa <?= !empty($domain['is_locked']) ? 'fa-lock text-danger' : 'fa-unlock text-success' ?>"></i>
                                                </a>

                                                <a href="<?= base_url('admin/server_management/delete_domain/' . $domain['id']) ?>" 
                                                   class="btn-action text-danger <?= !empty($domain['is_locked']) ? 'disabled' : '' ?>" 
                                                   title="<?= !empty($domain['is_locked']) ? 'Locked' : 'Delete' ?>" 
                                                   onclick="<?= !empty($domain['is_locked']) ? 'return false;' : "return confirm('Are you sure you want to delete this domain?')" ?>">
                                                    <i class="fa fa-trash-o"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="row mt-4 align-items-center">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <span class="text-muted small mr-3">
                                Showing <?= ($total_rows > 0) ? $offset + 1 : 0 ?> to <?= min($offset + $filters['limit'], $total_rows) ?> of <?= $total_rows ?> entries
                            </span>
                            <select id="changeRowLimit" class="form-control form-control-sm" style="width: 60px;">
                                <option value="10" <?= $filters['limit'] == 10 ? 'selected' : '' ?>>10</option>
                                <option value="25" <?= $filters['limit'] == 25 ? 'selected' : '' ?>>25</option>
                                <option value="50" <?= $filters['limit'] == 50 ? 'selected' : '' ?>>50</option>
                                <option value="100" <?= $filters['limit'] == 100 ? 'selected' : '' ?>>100</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-8 text-right" id="paginationContainer">
                        <?= $pagination ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        
        // Handle View Details
        $(document).on('click', '.view-domain', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var url = $(this).attr('href');
            
            $('#myModal').modal('show');
            $('#myModal .modal-content').html('<div class="modal-body text-center mt-3 mb-3"><i class="fa fa-spinner fa-spin fa-2x"></i> Loading Domain Details...</div>');
            
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    $('#myModal .modal-content').html(response);
                },
                error: function(xhr, status, error) {
                    $('#myModal .modal-content').html('<div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Error</h4></div><div class="modal-body"><div class="alert alert-danger">Error: Could not load domain details. Status: ' + status + '</div></div>');
                }
            });
        });

        // Handle Lock Toggle
        $(document).on('click', '.toggle-lock', function() {
            var btn = $(this);
            var id = btn.data('id');
            var currentStatus = btn.data('status');
            var newStatus = currentStatus == 1 ? 0 : 1;
            var icon = btn.find('i');
            
            $.ajax({
                url: '<?= base_url('admin/server_management/change_domain_lock/') ?>' + id + '/' + newStatus,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // Update UI
                        btn.data('status', newStatus);
                        if (newStatus == 1) {
                            icon.removeClass('fa-unlock text-success').addClass('fa-lock text-danger');
                            btn.attr('title', 'Unlock Domain');
                            btn.closest('tr').find('.btn-action:not(.toggle-lock)').addClass('disabled').attr('title', 'Locked');
                            btn.closest('tr').find('.row-checkbox').prop('disabled', true);
                        } else {
                            icon.removeClass('fa-lock text-danger').addClass('fa-unlock text-success');
                            btn.attr('title', 'Lock Domain');
                            btn.closest('tr').find('.btn-action:not(.toggle-lock)').removeClass('disabled');
                            btn.closest('tr').find('a[title="Edit"]').attr('title', 'Edit');
                            btn.closest('tr').find('a[title="Delete"]').attr('title', 'Delete');
                            btn.closest('tr').find('.row-checkbox').prop('disabled', false);
                        }
                        
                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message);
                        }
                    }
                }
            });
        });

        // Custom Date Range Filter Logic for DataTables
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var min = $('#start_date').val();
            var max = $('#end_date').val();
            var rowDate = new Date(data[5]); // Index 5 is Expiry Date

            if (!min && !max) {
                return true;
            }

            var minDate = min ? new Date(min) : null;
            var maxDate = max ? new Date(max) : null;

            if (
                (minDate === null && maxDate === null) ||
                (minDate === null && rowDate <= maxDate) ||
                (minDate <= rowDate && maxDate === null) ||
                (minDate <= rowDate && rowDate <= maxDate)
            ) {
                return true;
            }
            return false;
        });

        // Initialize DataTables
        var table = $('#domainDataTable').DataTable({
            "dom": "t",
            "pageLength": -1,
            "order": [[1, "asc"]],
            "columnDefs": [{
                "orderable": false,
                "targets": [0, 6, 8]
            }]
        });

        // Search & Filters
        // Handle Filters - Submit form on change
        $('#filter_status, #filter_provider, #start_date, #end_date').on('change', function() {
            $('#filterForm').submit();
        });

        // Search trigger on Enter
        $('#customSearch').on('keypress', function(e) {
            if (e.which == 13) {
                e.preventDefault();
                $('#filterForm').submit();
            }
        });

        $('#changeRowLimit').on('change', function() {
            var limit = $(this).val();
            // Add limit as a hidden input to filterForm and submit
            if ($('#limit_hidden').length == 0) {
                $('#filterForm').append('<input type="hidden" name="limit" id="limit_hidden">');
            }
            $('#limit_hidden').val(limit);
            $('#filterForm').submit();
        });

        // ----------------------------------------------------
        // Bulk Delete Selection Logic (DataTables API)
        // ----------------------------------------------------
        var $selectAll = $('#selectAll');
        var $bulkDeleteBtn = $('#bulkDeleteBtn');

        function updateBulkBtn() {
            var checkedCount = table.$('.row-checkbox:checked').length;
            var totalCount = table.rows({
                search: 'applied'
            }).count();

            if (checkedCount > 0) {
                $bulkDeleteBtn.fadeIn(200);
                if (checkedCount === totalCount) {
                    $bulkDeleteBtn.html('<i class="fa fa-trash"></i> Delete All (' + checkedCount + ')');
                } else {
                    $bulkDeleteBtn.html('<i class="fa fa-trash"></i> Delete Selected (' + checkedCount + ')');
                }
            } else {
                $bulkDeleteBtn.fadeOut(200);
            }
        }

        $selectAll.on('click', function() {
            var rows = table.rows({
                search: 'applied'
            }).nodes();
            $('.row-checkbox', rows).prop('checked', this.checked);
            updateBulkBtn();
        });

        $('#domainDataTable tbody').on('change', '.row-checkbox', function() {
            var checkedCount = table.$('.row-checkbox:checked').length;
            var totalCount = table.rows({
                search: 'applied'
            }).count();

            if (checkedCount === totalCount && totalCount > 0) {
                $selectAll.prop('checked', true);
            } else {
                $selectAll.prop('checked', false);
            }
            updateBulkBtn();
        });

        $bulkDeleteBtn.on('click', function() {
            var selectedIds = [];
            table.$('.row-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length > 0 && confirm('Are you sure you want to delete the selected ' + selectedIds.length + ' domains?')) {
                var form = $('<form>', {
                    'action': '<?= base_url('admin/server_management/delete_domain') ?>',
                    'method': 'POST'
                });

                $.each(selectedIds, function(i, id) {
                    form.append($('<input>', {
                        'type': 'hidden',
                        'name': 'ids[]',
                        'value': id
                    }));
                });

                $('body').append(form);
                form.submit();
            }
        });
    });
</script>