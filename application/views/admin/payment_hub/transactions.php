<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-list-alt"></i> All Transactions
                    <a href="<?= base_url('admin/payment_hub') ?>" class="btn btn-default btn-xs pull-right">← Dashboard</a>
                </h3>
            </div>
            <div class="panel-body">

                <!-- Filter bar -->
                <form method="get" action="<?= base_url('admin/payment_hub/transactions') ?>" class="form-inline" style="margin-bottom:15px;">
                    <div class="form-group">
                        <label>Client</label>
                        <select name="client_id" class="form-control input-sm">
                            <option value="">All Clients</option>
                            <?php foreach ($clients as $c): ?>
                            <option value="<?= $c->id ?>" <?= ($filters['client_id'] == $c->id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c->project_name) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Gateway</label>
                        <select name="gateway_id" class="form-control input-sm">
                            <option value="">All Gateways</option>
                            <?php foreach ($gateways as $g): ?>
                            <option value="<?= $g->id ?>" <?= ($filters['gateway_id'] == $g->id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($g->name) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control input-sm">
                            <option value="">All Statuses</option>
                            <?php foreach (['pending','success','failed','cancelled'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($filters['status'] == $s) ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>From</label>
                        <input type="date" name="date_from" class="form-control input-sm" value="<?= $filters['date_from'] ?>">
                    </div>
                    <div class="form-group">
                        <label>To</label>
                        <input type="date" name="date_to" class="form-control input-sm" value="<?= $filters['date_to'] ?>">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Filter</button>
                    <a href="<?= base_url('admin/payment_hub/transactions') ?>" class="btn btn-default btn-sm">Reset</a>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped" id="transactions-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Project</th>
                                <th>External Ref</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Gateway TXN ID</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($transactions)): foreach ($transactions as $t): ?>
                            <?php
                                $badge_map = ['success'=>'success','pending'=>'warning','failed'=>'danger','cancelled'=>'default'];
                                $badge = $badge_map[$t->status] ?? 'default';
                            ?>
                            <tr>
                                <td><?= $t->id ?></td>
                                <td><?= htmlspecialchars($t->client_name) ?></td>
                                <td><?= htmlspecialchars($t->external_reference) ?></td>
                                <td><?= display_money($t->amount) ?> <?= $t->currency ?></td>
                                <td><?= htmlspecialchars($t->gateway_name ?: '—') ?></td>
                                <td><small><code><?= htmlspecialchars($t->external_reference) ?></code></small></td>
                                <td><span class="label label-<?= $badge ?>"><?= ucfirst($t->status) ?></span></td>
                                <td><?= $t->created_at ?></td>
                                <td>
                                    <a href="<?= base_url('admin/payment_hub/transaction_detail/' . $t->id) ?>" class="btn btn-xs btn-info"><i class="fa fa-eye"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="9" class="text-center text-muted">No transactions found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
