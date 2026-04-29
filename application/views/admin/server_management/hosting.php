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

    .badge-cancelled {
        background-color: #6c757d;
        color: #fff;
    }

    .badge-pending {
        background-color: #20c997;
        color: #fff;
    }

    .badge-active {
        background-color: #28a745;
        color: #fff;
    }

    .badge-suspended {
        background-color: #ffc107;
        color: #212529;
    }

    .badge-expired {
        background-color: #dc3545;
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
        margin: 0 2px;
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
        background-color: #d32f2f;
        border-color: #d32f2f;
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

    /* Premium Dropdown Styling */
    select.form-control-sm {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%236c757d' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 8px center;
        padding-right: 28px !important;
        background-color: #fff;
        border: 1px solid #ced4da;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        cursor: pointer;
    }

    select.form-control-sm:focus {
        border-color: #d32f2f;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(211, 47, 47, 0.1);
    }

    .ml-lg {
        margin-left: 68px !important;
    }

    @media (min-width: 992px) {
        .col-md-5 {
            width: 34.666667%;
        }
    }

    table.dataTable tbody th,
    table.dataTable tbody td {
        padding: 8px -1px;
    }
</style>

<div class="row mb-lg">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">

                <div class="row mb-4 align-items-end">
                    <div class="col-md-3">
                        <label class="text-muted small mb-1">Expiry Period</label>
                        <div class="input-group">
                            <input type="date" id="start_date" class="form-control form-control-sm">
                            <span class="input-group-addon" style="padding: 4px 8px; background: #f8f9fa; border: 1px solid #ccc; border-left: none; border-right: none;"><i class="fa fa-minus text-muted" style="font-size:10px;"></i></span>
                            <input type="date" id="end_date" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-md-2 ml-lg">
                        <label class="text-muted small mb-1 ">Status</label>
                        <select id="filter_status" class="form-control form-control-sm">
                            <option value="">All</option>
                            <option value="Active">Active</option>
                            <option value="Suspended">Suspended</option>
                            <option value="Pending">Pending</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="Expired">Expired</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="text-muted small mb-1">Provider Name</label>
                        <select id="filter_provider" class="form-control form-control-sm">
                            <option value="">All</option>
                            <?php if (!empty($providers)): ?>
                                <?php foreach ($providers as $provider): ?>
                                    <option value="<?= htmlspecialchars($provider['provider_name']) ?>"><?= htmlspecialchars($provider['provider_name']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="text-muted small mb-1">&nbsp;</label>
                        <div class="input-group">
                            <input type="text" id="customSearch" class="form-control form-control-sm" placeholder="Start typing to search hostings...">
                            <span class="input-group-addon" style="padding: 4px 10px; background: #fff;"><i class="fa fa-search text-muted"></i></span>
                        </div>
                    </div>
                </div>

                <div class="row mb-3 align-items-center">
                    <div class="col-md-6">
                        <button id="bulkDeleteBtn" class="btn btn-sm btn-outline-danger" style="display: none;"><i class="fa fa-trash"></i> Delete Selected</button>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="<?= base_url('admin/server_management/add_hosting') ?>" class="btn btn-sm btn-danger"><i class="fa fa-plus"></i> Add Hosting</a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="hostingDataTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 30px;"><input type="checkbox" id="selectAll"></th>
                                <th>Title</th>
                                <th>Provider Name</th>
                                <th>Server Type</th>
                                <th>Status</th>
                                <th>Expiry Date</th>
                                <th>Days Remaining</th>
                                <th class="text-center" style="width: 110px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($hostings)): ?>
                                <?php foreach ($hostings as $hosting): ?>
                                    <tr>
                                        <td><input type="checkbox" class="row-checkbox" value="<?= $hosting['id'] ?>"></td>
                                        <td><span class="font-weight-bold"><?= htmlspecialchars($hosting['title']) ?></span></td>
                                        <td><?= htmlspecialchars($hosting['provider_name']) ?></td>
                                        <td><?= htmlspecialchars($hosting['server_type']) ?></td>
                                        <td>
                                            <?php
                                            $badge_class = '';
                                            switch ($hosting['status']) {
                                                case 'Cancelled':
                                                    $badge_class = 'badge-cancelled';
                                                    break;
                                                case 'Pending':
                                                    $badge_class = 'badge-pending';
                                                    break;
                                                case 'Active':
                                                    $badge_class = 'badge-active';
                                                    break;
                                                case 'Suspended':
                                                    $badge_class = 'badge-suspended';
                                                    break;
                                                case 'Expired':
                                                    $badge_class = 'badge-expired';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-pill <?= $badge_class ?>"><?= htmlspecialchars($hosting['status']) ?></span>
                                        </td>
                                        <td><?= $hosting['expiry_date'] ?></td>
                                        <td>
                                            <?php
                                            $days = $hosting['days_remaining'];
                                            if ($days > 0):
                                            ?>
                                                <span class="badge badge-pill badge-active"><?= $days ?> days left</span>
                                            <?php elseif ($days == 0): ?>
                                                <span class="badge badge-pill badge-suspended">Expires today</span>
                                            <?php else: ?>
                                                <span class="badge badge-pill badge-expired">Expired <?= abs($days) ?> days ago</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="#" class="btn-action text-info" title="View" data-toggle="modal" data-target="#viewModal_<?= $hosting['id'] ?>"><i class="fa fa-eye"></i></a>
                                            <a href="<?= base_url('admin/server_management/add_hosting/' . $hosting['id']) ?>" class="btn-action" title="Edit"><i class="fa fa-pencil-square-o"></i></a>
                                            <a href="<?= base_url('admin/server_management/delete_hosting/' . $hosting['id']) ?>" class="btn-action text-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this hosting?')"><i class="fa fa-trash-o"></i></a>
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

<?php if (!empty($hostings)): ?>
    <?php foreach ($hostings as $hosting): ?>
        <?php
        $badge_class = '';
        switch ($hosting['status']) {
            case 'Cancelled':
                $badge_class = 'badge-cancelled';
                break;
            case 'Pending':
                $badge_class = 'badge-pending';
                break;
            case 'Active':
                $badge_class = 'badge-active';
                break;
            case 'Suspended':
                $badge_class = 'badge-suspended';
                break;
            case 'Expired':
                $badge_class = 'badge-expired';
                break;
        }
        ?>
        <div class="modal fade" id="viewModal_<?= $hosting['id'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content text-left">
                    <div class="modal-header">
                        <h5 class="modal-title">Hosting Details - <?= htmlspecialchars($hosting['title']) ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <th style="width: 40%;">Provider Name</th>
                                        <td>: <?= htmlspecialchars($hosting['provider_name']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Server Type</th>
                                        <td>: <?= htmlspecialchars($hosting['server_type']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Server Location</th>
                                        <td>: <?= htmlspecialchars($hosting['server_location'] ?? 'N/A') ?></td>
                                    </tr>
                                    <tr>
                                        <th>IP Address</th>
                                        <td>: <?= htmlspecialchars($hosting['ip_address'] ?? 'N/A') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Price</th>
                                        <td>: <?= htmlspecialchars($hosting['price']) ?> <?= htmlspecialchars($hosting['currency_id'] ?? '') ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <th style="width: 40%;">Status</th>
                                        <td>: <span class="badge badge-pill <?= $badge_class ?>"><?= htmlspecialchars($hosting['status']) ?></span></td>
                                    </tr>
                                    <tr>
                                        <th>Purchase Date</th>
                                        <td>: <?= $hosting['purchase_date'] ?></td>
                                    </tr>
                                    <tr>
                                        <th>Expiry Date</th>
                                        <td>: <?= $hosting['expiry_date'] ?></td>
                                    </tr>
                                    <tr>
                                        <th>CPanel URL</th>
                                        <td>: <?php
                                            if (!empty($hosting['cpanel_url'])) {
                                                $url = htmlspecialchars($hosting['cpanel_url']);
                                                $link_url = (strpos($hosting['cpanel_url'], 'http') === 0) ? $hosting['cpanel_url'] : 'http://' . $hosting['cpanel_url'];
                                                echo '<a href="' . htmlspecialchars($link_url) . '" target="_blank">' . $url . '</a>';
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?></td>
                                    </tr>
                                    <tr>
                                        <th>Username</th>
                                        <td>: <?= htmlspecialchars($hosting['username'] ?? 'N/A') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Password</th>
                                        <td>: <?= htmlspecialchars($hosting['password'] ?? 'N/A') ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-1 mb-2">FTP & SSL Information</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <th style="width: 20%;">FTP Username</th>
                                        <td style="width: 30%;">: <?= htmlspecialchars($hosting['ftp_username'] ?? 'N/A') ?></td>
                                        <th style="width: 20%;">FTP Password</th>
                                        <td style="width: 30%;">: <?= htmlspecialchars($hosting['ftp_password'] ?? 'N/A') ?></td>
                                    </tr>
                                    <tr>
                                        <th>SSL Certificate</th>
                                        <td>: <?= !empty($hosting['ssl_certificate']) ? 'Yes' : 'No' ?></td>
                                        <th>SSL Expiry</th>
                                        <td>: <?= htmlspecialchars($hosting['ssl_expiry_date'] ?? 'N/A') ?></td>
                                    </tr>
                                    <tr>
                                        <th>SSL Type</th>
                                        <td colspan="3">: <?= htmlspecialchars($hosting['ssl_type'] ?? 'N/A') ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <?php if (!empty($hosting['description'])): ?>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <h6 class="border-bottom pb-1 mb-2">Description</h6>
                                    <p class="text-muted small"><?= nl2br(htmlspecialchars($hosting['description'])) ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<script type="text/javascript">
    $(document).ready(function() {

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
        var table = $('#hostingDataTable').DataTable({
            "dom": "itp",
            "pageLength": 10,
            "order": [
                [1, "asc"]
            ], // Sort by Title by default
            "columnDefs": [{
                    "orderable": false,
                    "targets": [0, 6, 7]
                } // Disable sorting for checkbox, days remaining, and action columns
            ],
            "language": {
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "paginate": {
                    "previous": "Previous",
                    "next": "Next"
                }
            },
            "drawCallback": function() {
                var $paginate = $('.dataTables_paginate').detach();
                var $info = $('.dataTables_info').detach();

                $('#paginationContainer').empty().append($info).append($paginate);

                $('#paginationContainer .pagination').addClass('pagination-sm mb-0 justify-content-end pagination-custom');
                $info.css({
                    'display': 'inline-block',
                    'margin-right': '20px',
                    'padding-top': '0'
                });
            }
        });

        // Search & Filters
        $('#customSearch').on('keyup', function() {
            table.search(this.value).draw();
        });

        $('#filter_status').on('change', function() {
            table.column(4).search(this.value).draw(); // Index 4 is Status
        });

        $('#filter_provider').on('change', function() {
            table.column(2).search(this.value).draw(); // Index 2 is Provider Name
        });

        $('#start_date, #end_date').on('change', function() {
            table.draw();
        });

        $('#changeRowLimit').on('change', function() {
            table.page.len(this.value).draw();
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

        $('#hostingDataTable tbody').on('change', '.row-checkbox', function() {
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

            if (selectedIds.length > 0 && confirm('Are you sure you want to delete the selected ' + selectedIds.length + ' hostings?')) {
                var form = $('<form>', {
                    'action': '<?= base_url('admin/server_management/delete_hosting') ?>',
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