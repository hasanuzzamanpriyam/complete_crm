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

    .password-toggle {
        cursor: pointer;
        color: #d32f2f;
        border-bottom: 1px dashed #d32f2f;
        font-family: monospace;
        padding: 2px 4px;
        transition: all 0.3s;
    }

    .password-toggle:hover {
        background: rgba(211, 47, 47, 0.05);
    }
</style>

<div class="row mb-lg">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">

                <form id="filterForm" method="GET" action="<?= base_url('admin/server_management/hosting') ?>">
                <div class="row mb-4 align-items-end">
                    <div class="col-md-3">
                        <label class="text-muted small mb-1">Expiry Period</label>
                        <div class="input-group">
                            <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="<?= $filters['start_date'] ?>">
                            <span class="input-group-addon" style="padding: 4px 8px; background: #f8f9fa; border: 1px solid #ccc; border-left: none; border-right: none;"><i class="fa fa-minus text-muted" style="font-size:10px;"></i></span>
                            <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="<?= $filters['end_date'] ?>">
                        </div>
                    </div>
                    <div class="col-md-2 ml-lg">
                        <label class="text-muted small mb-1 ">Status</label>
                        <select name="status" id="filter_status" class="form-control form-control-sm">
                            <option value="All" <?= $filters['status'] == 'All' ? 'selected' : '' ?>>All Status</option>
                            <option value="Active" <?= $filters['status'] == 'Active' ? 'selected' : '' ?>>Active</option>
                            <option value="Expiring" <?= $filters['status'] == 'Expiring' ? 'selected' : '' ?>>Expiring</option>
                            <option value="Suspended" <?= $filters['status'] == 'Suspended' ? 'selected' : '' ?>>Suspended</option>
                            <option value="Pending" <?= $filters['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Cancelled" <?= $filters['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            <option value="Expired" <?= $filters['status'] == 'Expired' ? 'selected' : '' ?>>Expired</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="text-muted small mb-1">Provider Name</label>
                        <select name="provider_id" id="filter_provider" class="form-control form-control-sm">
                            <option value="All" <?= $filters['provider_id'] == 'All' ? 'selected' : '' ?>>All Providers</option>
                            <?php if (!empty($providers)): ?>
                                <?php foreach ($providers as $provider): ?>
                                    <option value="<?= $provider['id'] ?>" <?= $filters['provider_id'] == $provider['id'] ? 'selected' : '' ?>><?= htmlspecialchars($provider['provider_name']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="text-muted small mb-1">&nbsp;</label>
                        <div class="input-group">
                            <input type="text" name="search" id="customSearch" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['search']) ?>" placeholder="Start typing to search hostings...">
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
                                <th>Username</th>
                                <th>IP Address</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Exp Date</th>
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
                                        <td><span class="text-muted small"><?= htmlspecialchars($hosting['username'] ?? 'N/A') ?></span></td>
                                        <td><code class="small"><?= htmlspecialchars($hosting['ip_address'] ?? 'N/A') ?></code></td>
                                        <td class="font-weight-bold"><?= htmlspecialchars($hosting['price']) ?> <small class="text-muted"><?= htmlspecialchars($hosting['currency_id'] ?? '') ?></small></td>
                                        <td>
                                            <?php
                                            $badge_class = '';
                                            $current_status = $hosting['status'];

                                            // Fallback/Safety: Force Expired status in view if date has passed
                                            if ($hosting['days_remaining'] < 0) {
                                                $current_status = 'Expired';
                                            }

                                            switch ($current_status) {
                                                case 'Cancelled':
                                                    $badge_class = 'badge-cancelled';
                                                    break;
                                                case 'Pending':
                                                    $badge_class = 'badge-pending';
                                                    break;
                                                case 'Active':
                                                    $badge_class = 'badge-active';
                                                    break;
                                                case 'Expiring':
                                                    $badge_class = 'badge-suspended'; // Using yellow/orange for expiring
                                                    break;
                                                case 'Suspended':
                                                    $badge_class = 'badge-suspended';
                                                    break;
                                                case 'Expired':
                                                    $badge_class = 'badge-expired';
                                                    break;
                                                default:
                                                    $badge_class = 'badge-secondary';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-pill <?= $badge_class ?>"><?= htmlspecialchars($current_status) ?></span>
                                        </td>
                                        <td><?= $hosting['purchase_date'] ?></td>
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
                                            <?php if (can_action_record($hosting['permission'], 'edit')): ?>
                                                <a href="<?= base_url('admin/server_management/add_hosting/' . $hosting['id']) ?>" class="btn-action" title="Edit"><i class="fa fa-pencil-square-o"></i></a>
                                            <?php endif; ?>
                                            <?php if (can_action_record($hosting['permission'], 'delete')): ?>
                                                <a href="<?= base_url('admin/server_management/delete_hosting/' . $hosting['id']) ?>" class="btn-action text-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this hosting?')"><i class="fa fa-trash-o"></i></a>
                                            <?php endif; ?>
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

<div id="modalsContainer">
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
                                        <th>Provider URL</th>
                                        <td>: <?php
                                            if (!empty($hosting['provider_url'])) {
                                                $url = htmlspecialchars($hosting['provider_url']);
                                                $link_url = (strpos($hosting['provider_url'], 'http') === 0) ? $hosting['provider_url'] : 'http://' . $hosting['provider_url'];
                                                echo '<a href="' . htmlspecialchars($link_url) . '" target="_blank">' . $url . '</a>';
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?></td>
                                    </tr>
                                    <tr>
                                        <th>Server Type</th>
                                        <td>: <?= htmlspecialchars($hosting['server_type']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Server Name</th>
                                        <td>: <?= htmlspecialchars($hosting['server_name'] ?? 'N/A') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Hostname</th>
                                        <td>: <?= htmlspecialchars($hosting['hostname'] ?? 'N/A') ?></td>
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
                                        <td>: 
                                            <?php
                                            $modal_status = $hosting['status'];
                                            if ($hosting['days_remaining'] < 0) $modal_status = 'Expired';
                                            $modal_badge = '';
                                            switch ($modal_status) {
                                                case 'Active': $modal_badge = 'badge-active'; break;
                                                case 'Expiring': $modal_badge = 'badge-suspended'; break;
                                                case 'Expired': $modal_badge = 'badge-expired'; break;
                                                case 'Suspended': $modal_badge = 'badge-suspended'; break;
                                                case 'Pending': $modal_badge = 'badge-pending'; break;
                                                case 'Cancelled': $modal_badge = 'badge-cancelled'; break;
                                                default: $modal_badge = 'badge-secondary';
                                            }
                                            ?>
                                            <span class="badge badge-pill <?= $modal_badge ?>"><?= htmlspecialchars($modal_status) ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width: 40%;">Registered Date</th>
                                        <td>: <?= !empty($hosting['date']) ? $hosting['date'] : '-' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Exp Date</th>
                                        <td>: <?= $hosting['purchase_date'] ?></td>
                                    </tr>
                                    <tr>
                                        <th>Future Exp Date</th>
                                        <td>: <?= $hosting['expiry_date'] ?></td>
                                    </tr>
                                    <tr>
                                        <th>Duration</th>
                                        <td>: <?= htmlspecialchars($hosting['days']) ?> <?= htmlspecialchars($hosting['time_unit']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Renew Type</th>
                                        <td>: <span class="badge badge-pill <?= $hosting['renew'] == 'automatic' ? 'badge-active' : 'badge-suspended' ?>"><?= ucfirst($hosting['renew']) ?></span></td>
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
                                        <td>: <?php if (!empty($hosting['password'])): ?>
                                                <span class="password-toggle" data-password="<?= htmlspecialchars($hosting['password']) ?>" title="Click to show/hide">••••••••</span>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6 border-right">
                                <h6 class="border-bottom pb-1 mb-2">FTP Information</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <th style="width: 40%;">FTP Username</th>
                                        <td>: <?= htmlspecialchars($hosting['ftp_username'] ?? 'N/A') ?></td>
                                    </tr>
                                    <tr>
                                        <th>FTP Password</th>
                                        <td>: <?php if (!empty($hosting['ftp_password'])): ?>
                                                <span class="password-toggle" data-password="<?= htmlspecialchars($hosting['ftp_password']) ?>" title="Click to show/hide">••••••••</span>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="border-bottom pb-1 mb-2">DNS Provider Credentials</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <th style="width: 40%;">DNS Provider</th>
                                        <td>: <?= htmlspecialchars($hosting['dns_provider_name'] ?? 'N/A') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Login Email</th>
                                        <td>: <?= htmlspecialchars($hosting['dns_email'] ?? 'N/A') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Login Password</th>
                                        <td>: <?php if (!empty($hosting['dns_password'])): ?>
                                                <span class="password-toggle" data-password="<?= htmlspecialchars($hosting['dns_password']) ?>" title="Click to show/hide">••••••••</span>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6 border-right">
                                <h6 class="border-bottom pb-1 mb-2">SSL Settings</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <th style="width: 40%;">SSL Certificate</th>
                                        <td>: <?= !empty($hosting['ssl_certificate']) ? '<span class="text-success">Enabled</span>' : '<span class="text-danger">Disabled</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <th>SSL Type</th>
                                        <td>: <?= htmlspecialchars($hosting['ssl_type'] ?? 'N/A') ?></td>
                                    </tr>
                                    <tr>
                                        <th>SSL Expiry</th>
                                        <td>: <?= htmlspecialchars($hosting['ssl_expiry_date'] ?? 'N/A') ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="border-bottom pb-1 mb-2">Notification Settings</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <th style="width: 40%;">Expiry Alert</th>
                                        <td>: <?= !empty($hosting['expiry_notification']) ? '<span class="text-success">Active</span>' : '<span class="text-danger">Inactive</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Notify Before</th>
                                        <td>: <?= !empty($hosting['expiry_notification']) ? htmlspecialchars($hosting['notification_days']) . ' ' . htmlspecialchars($hosting['notification_time_unit']) : 'N/A' ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-1 mb-2">Assignments & Additional Info</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <th style="width: 20%;">Main Domain(s)</th>
                                        <td>: <?php
                                            if (!empty($hosting['main_domain'])) {
                                                $domain_ids = explode(',', $hosting['main_domain']);
                                                $domain_names = [];
                                                foreach ($domain_ids as $id) {
                                                    foreach ($domains as $d) {
                                                        if ($d['id'] == $id) {
                                                            $domain_names[] = $d['domain_name'];
                                                            break;
                                                        }
                                                    }
                                                }
                                                echo !empty($domain_names) ? implode(', ', $domain_names) : $hosting['main_domain'];
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?></td>
                                    </tr>
                                    <tr>
                                        <th>Nameservers</th>
                                        <td>: <?= htmlspecialchars($hosting['nameservers'] ?? 'N/A') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Project(s)</th>
                                        <td>: <?php
                                            if (!empty($hosting['project_id'])) {
                                                $project_ids = explode(',', $hosting['project_id']);
                                                $project_names = [];
                                                foreach ($project_ids as $id) {
                                                    foreach ($projects as $p) {
                                                        if ($p['project_id'] == $id) {
                                                            $project_names[] = $p['project_name'];
                                                            break;
                                                        }
                                                    }
                                                }
                                                echo !empty($project_names) ? implode(', ', $project_names) : $hosting['project_id'];
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?></td>
                                    </tr>
                                    <tr>
                                        <th>Client(s)</th>
                                        <td>: <?php
                                            if (!empty($hosting['client_id'])) {
                                                $client_ids = explode(',', $hosting['client_id']);
                                                $client_names = [];
                                                foreach ($client_ids as $id) {
                                                    foreach ($clients as $c) {
                                                        if ($c['client_id'] == $id) {
                                                            $client_names[] = $c['name'];
                                                            break;
                                                        }
                                                    }
                                                }
                                                echo !empty($client_names) ? implode(', ', $client_names) : $hosting['client_id'];
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <?php if (!empty($hosting['ssl_info'])): ?>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <h6 class="border-bottom pb-1 mb-2">SSL Certificate Info</h6>
                                    <pre class="bg-light p-2 small"><?= htmlspecialchars($hosting['ssl_info']) ?></pre>
                                </div>
                            </div>
                        <?php endif; ?>

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
</div>

<script type="text/javascript">
    $(document).ready(function() {

        // Custom Date Range Filter Logic for DataTables
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var min = $('#start_date').val();
            var max = $('#end_date').val();
            var rowDate = new Date(data[7]); // Index 7 is Expiry Date (Updated from 5 after adding Username, IP, Price and removing Server Type)

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
        var table;
        function initDataTable() {
            if ($.fn.DataTable.isDataTable('#hostingDataTable')) {
                $('#hostingDataTable').DataTable().destroy();
            }
            table = $('#hostingDataTable').DataTable({
                "dom": "t",
                "pageLength": -1,
                "order": [[1, "asc"]],
                "columnDefs": [{
                    "orderable": false,
                    "targets": [0, 8, 9]
                }]
            });
        }
        initDataTable();

        // AJAX Loading Function
        function loadHostingData(url) {
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
                    var $html = $('<div/>').append($.parseHTML(response));
                    var newTable = $html.find('.table-responsive').html();
                    var newPagination = $html.find('#paginationContainer').html();
                    var newInfo = $html.find('.text-muted.small.mr-3').first().html();
                    var newModals = $html.find('#modalsContainer').html();

                    $('.table-responsive').html(newTable);
                    $('#paginationContainer').html(newPagination);
                    $('.text-muted.small.mr-3').first().html(newInfo);
                    $('#modalsContainer').html(newModals);
                    
                    $('.table-responsive').css('opacity', '1');
                    
                    // Re-initialize DataTable and Bulk Actions
                    initDataTable();
                    $('#selectAll').prop('checked', false);
                    updateBulkBtn();
                },
                error: function() {
                    $('.table-responsive').css('opacity', '1');
                    alert('Error loading data');
                }
            });
        }

        // Search & Filters
        // Handle Filters - Use AJAX instead of submit
        $('#filter_status, #filter_provider, #start_date, #end_date').on('change', function() {
            loadHostingData();
        });

        // Auto-search with debounce
        var searchTimer;
        $('#customSearch').on('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                loadHostingData();
            }, 600);
        });

        // Search trigger on Enter
        $('#customSearch').on('keypress', function(e) {
            if (e.which == 13) {
                e.preventDefault();
                clearTimeout(searchTimer);
                loadHostingData();
            }
        });

        // Prevent form submission
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            loadHostingData();
        });

        // Handle Row Limit Change
        $('#changeRowLimit').on('change', function() {
            loadHostingData();
        });

        // Handle Pagination Click
        $(document).on('click', '#paginationContainer .page-link', function(e) {
            var href = $(this).attr('href');
            if (href && href !== '#' && !$(this).parent().hasClass('active')) {
                e.preventDefault();
                loadHostingData(href);
            }
        });

        // ----------------------------------------------------
        // Bulk Delete Selection Logic (DataTables API)
        // ----------------------------------------------------
        var $selectAll = $('#selectAll');
        var $bulkDeleteBtn = $('#bulkDeleteBtn');

        function updateBulkBtn() {
            if (!table) return;
            var checkedCount = $('.row-checkbox:checked').length;
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

        $(document).on('click', '#selectAll', function() {
            var checked = this.checked;
            $('.row-checkbox').each(function() {
                if (!$(this).prop('disabled')) {
                    $(this).prop('checked', checked);
                }
            });
            updateBulkBtn();
        });

        $(document).on('change', '.row-checkbox', function() {
            var totalAvailable = $('.row-checkbox:not(:disabled)').length;
            var checkedCount = $('.row-checkbox:checked').length;

            if (checkedCount === totalAvailable && totalAvailable > 0) {
                $('#selectAll').prop('checked', true);
            } else {
                $('#selectAll').prop('checked', false);
            }
            updateBulkBtn();
        });

        $bulkDeleteBtn.on('click', function() {
            var selectedIds = [];
            $('.row-checkbox:checked').each(function() {
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

        // Password Toggle Logic
        $(document).on('click', '.password-toggle', function() {
            var $this = $(this);
            var password = $this.data('password');
            if ($this.text() === '••••••••') {
                $this.text(password);
            } else {
                $this.text('••••••••');
            }
        });
    });
</script>