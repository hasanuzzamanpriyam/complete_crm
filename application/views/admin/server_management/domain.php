<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<style>
    .badge-expired {
        background-color: #dc3545;
        color: #fff;
    }
    .badge-pending {
        background-color: #6c757d;
        color: #fff;
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
</style>

<div class="row mb-lg">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <!-- Top Filter Bar -->
                <div class="row mb-3 align-items-end">
                    <div class="col-md-2">
                        <label class="text-muted small">Duration</label>
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Start Date">
                            <span class="input-group-addon"><i class="fa fa-minus"></i></span>
                            <input type="text" class="form-control" placeholder="End Date">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="text-muted small">Status</label>
                        <select class="form-control">
                            <option value="">All</option>
                            <option value="Active">Active</option>
                            <option value="Pending">Pending</option>
                            <option value="Transferring">Transferring</option>
                            <option value="Expired">Expired</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="text-muted small">Provider</label>
                        <select class="form-control">
                            <option value="">All</option>
                            <option value="Google Workspace">Google Workspace</option>
                            <option value="GoDaddy">GoDaddy</option>
                            <option value="Namecheap">Namecheap</option>
                            <option value="Cloudflare">Cloudflare</option>
                            <option value="AWS">AWS</option>
                            <option value="MongoDB Atlas">MongoDB Atlas</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">&nbsp;</label>
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Start typing to search">
                            <span class="input-group-addon"><i class="fa fa-search"></i></span>
                        </div>
                    </div>
                    <div class="col-md-2 text-right">
                        <button class="btn btn-default"><i class="fa fa-filter"></i> Filters</button>
                    </div>
                </div>

                <!-- Action Buttons Row -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <a href="<?= base_url('admin/server_management/add_domain') ?>" class="btn btn-primary"><i class="fa fa-plus"></i> Add Domain</a>
                        <button class="btn btn-outline-secondary ml-2"><i class="fa fa-file-export"></i> Export</button>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th><input type="checkbox"></th>
                                <th>Domain Name <i class="fa fa-sort-up"></i><i class="fa fa-sort-down"></i></th>
                                <th>Provider <i class="fa fa-sort-up"></i><i class="fa fa-sort-down"></i></th>
                                <th>Domain Type <i class="fa fa-sort-up"></i><i class="fa fa-sort-down"></i></th>
                                <th>Status <i class="fa fa-sort-up"></i><i class="fa fa-sort-down"></i></th>
                                <th>Purchase Date <i class="fa fa-sort-up"></i><i class="fa fa-sort-down"></i></th>
                                <th>Expiry Date <i class="fa fa-sort-up"></i><i class="fa fa-sort-down"></i></th>
                                <th>Hosting <i class="fa fa-sort-up"></i><i class="fa fa-sort-down"></i></th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($domains)): ?>
                                <?php foreach ($domains as $domain): ?>
                                    <tr>
                                        <td><input type="checkbox"></td>
                                        <td><?= $domain['domain_name'] ?></td>
                                        <td><?= $domain['provider'] ?></td>
                                        <td><span class="badge badge-pill badge-domain-type"><?= $domain['domain_type'] ?></span></td>
                                        <td>
                                            <?php
                                            $badge_class = '';
                                            switch ($domain['status']) {
                                                case 'Expired':
                                                    $badge_class = 'badge-expired';
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
                                            }
                                            ?>
                                            <span class="badge badge-pill <?= $badge_class ?>"><?= $domain['status'] ?></span>
                                        </td>
                                        <td><?= $domain['purchase_date'] ?></td>
                                        <td><?= $domain['expiry_date'] ?></td>
                                        <td><?= $domain['hosting'] ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-link" data-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item" href="#">View</a>
                                                    <a class="dropdown-item" href="#">Edit</a>
                                                    <a class="dropdown-item" href="#">Delete</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No data available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Footer / Pagination -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <span class="text-muted">Show 
                            <select class="form-control form-control-sm d-inline-block" style="width: auto;">
                                <option>10</option>
                                <option>25</option>
                                <option>50</option>
                            </select> 
                            entries
                        </span>
                    </div>
                    <div class="col-md-6 text-right">
                        <span class="text-muted mr-2">Showing 1 to 10 of 20 entries</span>
                        <nav class="d-inline-block">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">Next</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>