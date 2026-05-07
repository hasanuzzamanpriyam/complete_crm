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

    .badge-active {
        background-color: #28a745;
        color: #fff;
    }

    .badge-inactive {
        background-color: #6c757d;
        color: #fff;
    }

    .badge-hosting,
    .badge-both {
        background-color: #ffc107;
        color: #212529;
    }

    .badge-domain {
        background-color: #17a2b8;
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
</style>

<div class="row mb-lg">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row mb-4 align-items-end">
                    <div class="col-md-2">
                        <label class="text-muted small mb-1">Status</label>
                        <select id="filter_status" class="form-control form-control-sm">
                            <option value="">All</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="text-muted small mb-1">Provider Type</label>
                        <select id="filter_type" class="form-control form-control-sm">
                            <option value="">All</option>
                            <option value="Hosting">Hosting</option>
                            <option value="Domain">Domain</option>
                            <option value="Both">Both</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small mb-1">&nbsp;</label>
                        <div class="input-group">
                            <input type="text" id="customSearch" class="form-control form-control-sm" placeholder="Start typing to search">
                            <span class="input-group-addon" style="padding: 4px 10px; background: #fff;"><i class="fa fa-search text-muted"></i></span>
                        </div>
                    </div>
                    <div class="col-md-4 text-right">
                        <button id="bulkDeleteBtn" class="btn btn-sm btn-outline-danger mr-2" style="display: none;"><i class="fa fa-trash"></i> Bulk Delete</button>
                        <a href="<?= base_url('admin/server_management/add_provider') ?>" class="btn btn-sm btn-danger ml-2"><i class="fa fa-plus"></i> Add Provider</a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="providerDataTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 30px;"><input type="checkbox" id="selectAll"></th>
                                <th>Provider Name</th>
                                <th>Provider URL</th>
                                <th>Provider Type</th>
                                <th>Status</th>
                                <th class="text-center" style="width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($providers)): ?>
                                <?php foreach ($providers as $provider): ?>
                                    <?php
                                    $status_class = (strtolower($provider['status']) == 'active') ? 'badge-active' : 'badge-inactive';
                                    $type_class = 'badge-' . strtolower($provider['provider_type']);
                                    ?>
                                    <tr>
                                        <td><input type="checkbox" class="row-checkbox" value="<?= $provider['id'] ?>"></td>
                                        <td><span class="font-weight-bold"><?= $provider['provider_name'] ?></span></td>
                                        <td><a href="<?= (preg_match('#^[^/:]+://#', $provider['provider_url'])) ? $provider['provider_url'] : 'http://' . $provider['provider_url'] ?>" target="_blank" class="text-info"><?= $provider['provider_url'] ?></a></td>
                                        <td><span class="badge <?= $type_class ?>"><?= $provider['provider_type'] ?></span></td>
                                        <td><span class="badge <?= $status_class ?>"><?= $provider['status'] ?></span></td>
                                        <td class="text-center">
                                            <a href="<?= base_url('admin/server_management/add_provider/' . $provider['id']) ?>" class="btn-action" title="Edit"><i class="fa fa-pencil-square-o"></i></a>
                                            <a href="<?= base_url('admin/server_management/delete_provider/' . $provider['id']) ?>" class="btn-action text-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this provider?')"><i class="fa fa-trash-o"></i></a>
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
                            <select id="changeRowLimit" class="form-control form-control-sm" style="width: 60px;">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-8 text-right" id="paginationContainer">
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        // Initialize DataTables
        var table = $('#providerDataTable').DataTable({
            "dom": "itp", // Only show Info, Table, and Pagination
            "pageLength": 10,
            "order": [
                [1, "asc"]
            ], // Sort by Provider Name by default
            "columnDefs": [{
                    "orderable": false,
                    "targets": [0, 5]
                } // Disable sorting for checkbox and action columns
            ],
            "language": {
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "paginate": {
                    "previous": "Previous",
                    "next": "Next"
                }
            },
            "drawCallback": function() {
                // 1. Grab the pagination and info elements directly without looking for a parent row
                var $paginate = $('.dataTables_paginate').detach();
                var $info = $('.dataTables_info').detach();

                // 2. Clear the custom container and inject the elements directly into it
                $('#paginationContainer').empty().append($info).append($paginate);

                // 3. Apply your custom UI styles to match your theme
                $('#paginationContainer .pagination').addClass('pagination-sm mb-0 justify-content-end');
                $info.css({
                    'display': 'inline-block',
                    'margin-right': '20px',
                    'padding-top': '0'
                });
            }
        });

        // Custom Search
        $('#customSearch').on('keyup', function() {
            table.search(this.value).draw();
        });

        // Dropdown Filters
        $('#filter_status').on('change', function() {
            table.column(4).search(this.value).draw();
        });

        $('#filter_type').on('change', function() {
            table.column(3).search(this.value).draw();
        });

        // Row Limit Change
        $('#changeRowLimit').on('change', function() {
            table.page.len(this.value).draw();
        });

        // ----------------------------------------------------
        // Bulk Delete Selection Logic (DataTables API Updated)
        // ----------------------------------------------------
        var $selectAll = $('#selectAll');
        var $bulkDeleteBtn = $('#bulkDeleteBtn');

        function updateBulkBtn() {
            // Count checked boxes across ALL pages
            var checkedCount = table.$('.row-checkbox:checked').length;
            var totalCount = table.rows({ search: 'applied' }).count();

            if (checkedCount > 0) {
                $bulkDeleteBtn.fadeIn(200);
                
                // Dynamically update text based on selection
                if (checkedCount === totalCount) {
                    $bulkDeleteBtn.html('<i class="fa fa-trash"></i> Delete All (' + checkedCount + ')');
                } else {
                    $bulkDeleteBtn.html('<i class="fa fa-trash"></i> Delete Selected (' + checkedCount + ')');
                }
            } else {
                $bulkDeleteBtn.fadeOut(200);
            }
        }

        // Handle "Select All" click
        $selectAll.on('click', function() {
            // Select rows matching current search filters
            var rows = table.rows({ search: 'applied' }).nodes();
            $('.row-checkbox', rows).prop('checked', this.checked);
            updateBulkBtn();
        });

        // Use event delegation on tbody so clicks on paginated rows are caught
        $('#providerDataTable tbody').on('change', '.row-checkbox', function() {
            var checkedCount = table.$('.row-checkbox:checked').length;
            var totalCount = table.rows({ search: 'applied' }).count();

            // Uncheck "Select All" if not everything is manually checked
            if (checkedCount === totalCount && totalCount > 0) {
                $selectAll.prop('checked', true);
            } else {
                $selectAll.prop('checked', false);
            }
            updateBulkBtn();
        });

        // Bulk Delete Submission
        $bulkDeleteBtn.on('click', function() {
            var selectedIds = [];
            
            // Extract IDs using the DataTables API to get hidden rows too
            table.$('.row-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length > 0 && confirm('Are you sure you want to delete the selected ' + selectedIds.length + ' providers?')) {
                var form = $('<form>', {
                    'action': '<?= base_url('admin/server_management/delete_provider') ?>',
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