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

    .badge-active { background-color: #28a745; color: #fff; }
    .badge-pending { background-color: #ffc107; color: #212529; }
    .badge-expired { background-color: #dc3545; color: #fff; }
    .badge-info { background-color: #17a2b8; color: #fff; }

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
</style>

<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<div class="row mb-lg">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">

                <form id="filterForm" method="GET" action="<?= base_url('admin/server_management/billing') ?>">
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
                                <?php if (!empty($statuses)): ?>
                                    <?php foreach ($statuses as $status): ?>
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
                                <input type="text" name="search" id="customSearch" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['search']) ?>" placeholder="Start typing to search billings...">
                                <span class="input-group-addon" style="padding: 4px 10px; background: #fff;"><button type="submit" style="border:none; background:none; padding:0;"><i class="fa fa-search text-muted"></i></button></span>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="row mb-3 align-items-center">
                    <div class="col-md-12 text-right">
                        <a href="<?= base_url('admin/server_management/add_billing') ?>" class="btn btn-sm btn-danger"><i class="fa fa-plus"></i> Add Billing Order</a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover" id="billingTable">
                        <thead>
                            <tr>
                                <th><?= lang('billing_label') ?></th>
                                <th>Category</th>
                                <th>Provider</th>
                                <th>Client</th>
                                <th>Status</th>
                                <th>Next Renew</th>
                                <th>Price</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($all_billing)): ?>
                                <?php foreach ($all_billing as $billing): ?>
                                    <tr>
                                        <td><strong><?= $billing->label ?></strong></td>
                                        <td><span class="badge badge-info"><?= $billing->type ?></span></td>
                                        <td><?= !empty($billing->provider_name) ? $billing->provider_name : 'N/A' ?></td>
                                        <td><?= !empty($billing->client_name) ? $billing->client_name : 'N/A' ?></td>
                                        <td>
                                            <?php 
                                                if ($billing->status == 'Active') {
                                                    $status_class = 'active';
                                                } elseif ($billing->status == 'Pending') {
                                                    $status_class = 'pending';
                                                } elseif ($billing->status == 'Expired') {
                                                    $status_class = 'expired';
                                                } else {
                                                    $status_class = 'info';
                                                }
                                            ?>
                                            <span class="badge badge-<?= $status_class ?>"><?= $billing->status ?></span>
                                        </td>
                                        <td><?= $billing->renewal_date ?></td>
                                        <td><?= $billing->currency ?> <?= number_format((float)$billing->value, 2) ?></td>
                                        <td class="text-center">
                                            <a href="<?= base_url('admin/server_management/view_billing/' . $billing->id) ?>" class="btn-action" title="View Details" data-toggle="modal" data-target="#myModal"><i class="fa fa-list-alt"></i></a>
                                            <?php if (can_action_record($billing->permission, 'edit')): ?>
                                                <a href="<?= base_url('admin/server_management/add_billing/' . $billing->id) ?>" class="btn-action" title="Edit"><i class="fa fa-pencil"></i></a>
                                            <?php endif; ?>
                                            <?php if (can_action_record($billing->permission, 'delete')): ?>
                                                <a href="<?= base_url('admin/server_management/delete_billing/' . $billing->id) ?>" class="btn-action text-danger" title="Delete" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No billing orders found.</td>
                                </tr>
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
        
        // AJAX Loading Function
        function loadBillingData(url) {
            url = url || $('#filterForm').attr('action');
            var formData = $('#filterForm').serialize();
            var limit = $('#changeRowLimit').val();

            // Ensure limit is included
            if (formData.indexOf('limit=') === -1) {
                formData += '&limit=' + limit;
            }

            // Show loading state
            $('.table-responsive').css('opacity', '0.5');

            $.ajax({
                url: url,
                type: 'GET',
                data: formData,
                success: function(response) {
                    // Extract only the parts we need from the response
                    var $html = $($.parseHTML(response));
                    var newTable = $html.find('.table-responsive').html();
                    var newPagination = $html.find('#paginationContainer').html();
                    var newInfo = $html.find('.text-muted.small.mr-3').first().html();

                    $('.table-responsive').html(newTable);
                    $('#paginationContainer').html(newPagination);
                    $('.text-muted.small.mr-3').first().html(newInfo);

                    $('.table-responsive').css('opacity', '1');
                },
                error: function() {
                    $('.table-responsive').css('opacity', '1');
                    alert('Error loading data');
                }
            });
        }

        // Search & Filters
        $('#filter_status, #filter_provider, #start_date, #end_date').on('change', function() {
            loadBillingData();
        });

        // Auto-search with debounce
        var searchTimer;
        $('#customSearch').on('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                loadBillingData();
            }, 600);
        });

        // Search trigger on Enter
        $('#customSearch').on('keypress', function(e) {
            if (e.which == 13) {
                e.preventDefault();
                clearTimeout(searchTimer);
                loadBillingData();
            }
        });

        // Prevent form submission
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            loadBillingData();
        });

        // Handle Row Limit Change
        $('#changeRowLimit').on('change', function() {
            loadBillingData();
        });

        // Handle Pagination Click
        $(document).on('click', '#paginationContainer .page-link', function(e) {
            var href = $(this).attr('href');
            if (href && href !== '#' && !$(this).parent().hasClass('active')) {
                e.preventDefault();
                loadBillingData(href);
            }
        });

    });
</script>
