<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-cubes"></i> Registered Projects
                    <a href="<?= base_url('admin/payment_hub/project_form') ?>" class="btn btn-success btn-xs pull-right">
                        <i class="fa fa-plus"></i> Add New Project
                    </a>
                </h3>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Project Name</th>
                                <th>Client ID</th>
                                <th>Webhook URL</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th style="width:140px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($projects)): foreach ($projects as $p): ?>
                            <tr>
                                <td><?= $p->id ?></td>
                                <td><strong><?= htmlspecialchars($p->project_name) ?></strong></td>
                                <td><code><?= htmlspecialchars($p->client_id) ?></code></td>
                                <td><?= !empty($p->webhook_url) ? '<a href="' . htmlspecialchars($p->webhook_url) . '" target="_blank">' . htmlspecialchars($p->webhook_url) . '</a>' : '<span class="text-muted">—</span>' ?></td>
                                <td>
                                    <span class="label label-<?= $p->status === 'active' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($p->status) ?>
                                    </span>
                                </td>
                                <td><?= $p->created_at ?></td>
                                <td>
                                    <a href="<?= base_url('admin/payment_hub/project_detail/' . $p->id) ?>" class="btn btn-xs btn-info" title="Details"><i class="fa fa-eye"></i></a>
                                    <a href="<?= base_url('admin/payment_hub/project_form/' . $p->id) ?>" class="btn btn-xs btn-warning" title="Edit"><i class="fa fa-pencil"></i></a>
                                    <a href="<?= base_url('admin/payment_hub/toggle_project/' . $p->id) ?>"
                                       class="btn btn-xs <?= $p->status === 'active' ? 'btn-default' : 'btn-success' ?>"
                                       title="<?= $p->status === 'active' ? 'Deactivate' : 'Activate' ?>">
                                        <i class="fa <?= $p->status === 'active' ? 'fa-pause' : 'fa-play' ?>"></i>
                                    </a>
                                    <a href="#" onclick="if(confirm('Delete this project and all its transactions?')) window.location='<?= base_url('admin/payment_hub/delete_project/' . $p->id) ?>';"
                                       class="btn btn-xs btn-danger" title="Delete"><i class="fa fa-trash-o"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="7" class="text-center text-muted">No projects registered. <a href="<?= base_url('admin/payment_hub/project_form') ?>">Add one now.</a></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
