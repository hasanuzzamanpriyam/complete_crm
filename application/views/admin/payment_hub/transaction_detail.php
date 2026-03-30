<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$badge_map = ['success'=>'success','pending'=>'warning','failed'=>'danger','cancelled'=>'default'];
$badge = $badge_map[$txn->status] ?? 'default';
$raw = !empty($txn->raw_response) ? json_decode($txn->raw_response, true) : [];
?>
<div class="row">
    <div class="col-md-8">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-info-circle"></i> Transaction #<?= $txn->id ?>
                    <div class="pull-right">
                        <?php if ($txn->status === 'success'): ?>
                        <button type="button" class="btn btn-warning btn-xs" data-toggle="modal" data-target="#refundModal">
                            <i class="fa fa-undo"></i> Process Refund
                        </button>
                        <?php endif; ?>
                        <a href="<?= base_url('admin/payment_hub/transactions') ?>" class="btn btn-default btn-xs">← Back</a>
                    </div>
                </h3>
            </div>
            <div class="panel-body">
                <table class="table table-bordered">
                    <tr><th style="width:35%">CRM Transaction ID</th><td><strong><?= $txn->id ?></strong></td></tr>
                    <tr><th>Client</th><td><?= htmlspecialchars($txn->client_name) ?></td></tr>
                    <tr><th>External Reference</th><td><?= htmlspecialchars($txn->external_reference) ?></td></tr>
                    <tr><th>Amount</th><td><?= display_money($txn->amount) ?> <?= $txn->currency ?></td></tr>
                    <tr><th>Gateway</th><td><?= htmlspecialchars($txn->gateway_name) ?></td></tr>
                    <tr>
                        <th>Status</th>
                        <td><span class="label label-<?= $badge ?>"><?= ucfirst($txn->status) ?></span></td>
                    </tr>
                    <tr><th>Created At</th><td><?= $txn->created_at ?></td></tr>
                    <tr><th>Updated At</th><td><?= $txn->updated_at ?></td></tr>
                </table>

                <hr>

                <!-- Gateway Attempts -->
                <h4><i class="fa fa-refresh"></i> Gateway Attempts</h4>
                <div class="table-responsive">
                    <table class="table table-condensed table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Action</th>
                                <th>Gateway TXN ID</th>
                                <th>Status</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($attempts)): foreach ($attempts as $a): ?>
                            <tr>
                                <td><?= $a->id ?></td>
                                <td><?= ucfirst($a->action) ?></td>
                                <td><code><?= htmlspecialchars($a->gateway_transaction_id ?: '—') ?></code></td>
                                <td><span class="label label-<?= $badge_map[$a->status] ?? 'default' ?>"><?= $a->status ?></span></td>
                                <td><?= $a->created_at ?></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="5" class="text-center text-muted">No gateway attempts recorded.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <hr>

                <!-- Webhook History -->
                <h4><i class="fa fa-envelope"></i> Outgoing Webhooks</h4>
                <div class="table-responsive">
                    <table class="table table-condensed table-bordered">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>URL</th>
                                <th>HTTP</th>
                                <th>Response</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($webhooks)): foreach ($webhooks as $w): ?>
                            <tr>
                                <td><?= $w->created_at ?></td>
                                <td><small><?= htmlspecialchars($w->url) ?></small></td>
                                <td><span class="label label-<?= $w->response_code == 200 ? 'success' : 'danger' ?>"><?= $w->response_code ?></span></td>
                                <td><small><code><?= substr(htmlspecialchars($w->response_body), 0, 50) ?>...</code></small></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="4" class="text-center text-muted">No webhooks sent yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="panel panel-info">
            <div class="panel-heading"><h3 class="panel-title"><i class="fa fa-history"></i> Internal Logs</h3></div>
            <div class="panel-body">
                <ul class="list-unstyled" style="font-size:12px;">
                    <?php if (!empty($logs)): foreach ($logs as $l): ?>
                    <li style="margin-bottom:8px;">
                        <span class="text-muted">[<?= $l->created_at ?>]</span> <span class="label label-default"><?= $l->level ?></span><br>
                        <?= htmlspecialchars($l->message) ?>
                    </li>
                    <?php endforeach; else: ?>
                    <li class="text-muted">No internal logs for this transaction.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title"><i class="fa fa-code"></i> Last Raw Response</h3></div>
            <div class="panel-body">
                <?php if (!empty($raw)): ?>
                <pre style="max-height:400px;overflow:auto;font-size:11px;"><?= htmlspecialchars(json_encode($raw, JSON_PRETTY_PRINT)) ?></pre>
                <?php else: ?>
                <p class="text-muted">No response data recorded.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo form_open('admin/payment_hub/process_refund/' . $txn->id); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Process Refund</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    You can process a full or partial refund for this transaction.
                </div>
                <div class="form-group">
                    <label>Amount (Leave empty for full refund)</label>
                    <input type="number" step="0.01" name="amount" class="form-control" placeholder="<?= $txn->amount ?>">
                    <p class="help-block">Original amount: <?= display_money($txn->amount) ?> <?= $txn->currency ?></p>
                </div>
                <div class="form-group">
                    <label>Reason for Refund</label>
                    <textarea name="reason" class="form-control" placeholder="e.g. Customer requested cancellation" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-warning">Confirm Refund</button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
