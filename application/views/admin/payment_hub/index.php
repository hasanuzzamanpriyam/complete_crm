<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-credit-card"></i> Payment Hub Overview</h3>
            </div>
            <div class="panel-body">

                <!-- KPI Cards -->
                <div class="row">
                    <div class="col-md-2 col-sm-4 col-xs-6">
                        <div class="panel panel-primary text-center">
                            <div class="panel-body">
                                <h2><?= $stats->total_count ?></h2>
                                <p>Total Transactions</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-xs-6">
                        <div class="panel panel-success text-center">
                            <div class="panel-body">
                                <h2><?= $stats->success_count ?></h2>
                                <p>Successful</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-xs-6">
                        <div class="panel panel-warning text-center">
                            <div class="panel-body">
                                <h2><?= ($stats->total_count - $stats->success_count - $stats->failed_count) ?></h2>
                                <p>Pending / Other</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-xs-6">
                        <div class="panel panel-danger text-center">
                            <div class="panel-body">
                                <h2><?= $stats->failed_count ?></h2>
                                <p>Failed</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-xs-6">
                        <div class="panel panel-info text-center">
                            <div class="panel-body">
                                <h2><?= display_money($stats->total_volume) ?></h2>
                                <p>Successful Volume</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-xs-6">
                        <div class="panel panel-default text-center">
                            <div class="panel-body">
                                <h2><?= display_money($stats->avg_success_amount) ?></h2>
                                <p>Avg. Success Txn</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Projects quick-list -->
                <h4><i class="fa fa-list"></i> Registered Projects
                    <a href="<?= base_url('admin/payment_hub/project_form') ?>" class="btn btn-success btn-xs pull-right">
                        <i class="fa fa-plus"></i> Add Project
                    </a>
                </h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Project Name</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($projects)): foreach ($projects as $p): ?>
                            <tr>
                                <td><?= $p->id ?></td>
                                <td><a href="<?= base_url('admin/payment_hub/project_detail/' . $p->id) ?>"><?= htmlspecialchars($p->project_name) ?></a></td>
                                <td>
                                    <span class="label label-<?= $p->status === 'active' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($p->status) ?>
                                    </span>
                                </td>
                                <td><?= $p->created_at ?></td>
                                <td>
                                    <a href="<?= base_url('admin/payment_hub/project_detail/' . $p->id) ?>" class="btn btn-xs btn-info" title="View"><i class="fa fa-eye"></i></a>
                                    <a href="<?= base_url('admin/payment_hub/project_form/' . $p->id) ?>" class="btn btn-xs btn-warning" title="Edit"><i class="fa fa-pencil"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="5" class="text-center text-muted">No projects registered yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <a href="<?= base_url('admin/payment_hub/transactions') ?>" class="btn btn-primary">
                    <i class="fa fa-list-alt"></i> View All Transactions
                </a>
            </div>
        </div>
    </div>
</div>
