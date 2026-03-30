<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-plug"></i> Payment Gateways
                    <a href="<?= base_url('admin/payment_hub') ?>" class="btn btn-default btn-xs pull-right">← Dashboard</a>
                </h3>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Status</th>
                                <th>Default</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($gateways)): foreach ($gateways as $g): ?>
                            <tr>
                                <td><?= $g->id ?></td>
                                <td><strong><?= htmlspecialchars($g->name) ?></strong></td>
                                <td><code><?= htmlspecialchars($g->gateway_slug) ?></code></td>
                                <td>
                                    <span class="label label-<?= $g->status === 'active' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($g->status) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($g->is_default): ?>
                                        <span class="label label-primary"><i class="fa fa-star"></i> Default</span>
                                    <?php else: ?>
                                        <a href="<?= base_url('admin/payment_hub/set_default_gateway/' . $g->id) ?>" class="btn btn-xs btn-default" title="Set as Default">Make Default</a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= base_url('admin/payment_hub/toggle_gateway/' . $g->id) ?>" 
                                       class="btn btn-xs <?= $g->status === 'active' ? 'btn-warning' : 'btn-success' ?>" 
                                       title="<?= $g->status === 'active' ? 'Disable' : 'Enable' ?>">
                                        <i class="fa <?= $g->status === 'active' ? 'fa-pause' : 'fa-play' ?>"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="6" class="text-center text-muted">No gateways configured.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="alert alert-info" style="margin-top:20px;">
                    <h4 style="margin-top:0"><i class="fa fa-info-circle"></i> Adding New Gateways</h4>
                    <p>To add a new payment gateway:</p>
                    <ol>
                        <li>Create a new driver class in <code>application/libraries/Payment_gateways/</code> extending <code>Base_gateway</code>.</li>
                        <li>Implement <code>initiate()</code>, <code>verify()</code>, and <code>refund()</code> methods.</li>
                        <li>Register the gateway in the database table <code>tbl_payment_gateways</code>.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
