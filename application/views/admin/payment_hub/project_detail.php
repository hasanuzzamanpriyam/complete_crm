<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-md-12">

        <!-- Credentials Panel -->
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-key"></i> Client Credentials — <?= htmlspecialchars($project->project_name) ?>
                    <a href="<?= base_url('admin/payment_hub/projects') ?>" class="btn btn-default btn-xs pull-right">← Back</a>
                </h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr><th>Project Name</th><td><?= htmlspecialchars($project->project_name) ?></td></tr>
                            <tr>
                                <th>Client ID</th>
                                <td>
                                    <code id="client-id"><?= htmlspecialchars($project->client_id) ?></code>
                                    <button class="btn btn-xs btn-default" onclick="copyValue('client-id')"><i class="fa fa-copy"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <th>Client Secret</th>
                                <td>
                                    <code id="client-secret"><?= htmlspecialchars($project->client_secret) ?></code>
                                    <button class="btn btn-xs btn-default" onclick="copyValue('client-secret')"><i class="fa fa-copy"></i></button>
                                </td>
                            </tr>
                            <tr><th>Callback URL</th><td><?= htmlspecialchars($project->callback_url ?: '—') ?></td></tr>
                            <tr><th>Webhook URL</th><td><?= htmlspecialchars($project->webhook_url ?: '—') ?></td></tr>
                            <tr>
                                <th>Status</th>
                                <td><span class="label label-<?= $project->status === 'active' ? 'success' : 'danger' ?>"><?= ucfirst($project->status) ?></span></td>
                            </tr>
                            <tr><th>Created</th><td><?= $project->created_at ?></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-info">
                            <div class="panel-heading"><h4 class="panel-title">Quick Integration Example</h4></div>
                            <div class="panel-body">
                                <p>Initiate a payment from any external project:</p>
                                <pre style="font-size:11px;">POST <?= base_url('api/v1/payments/initiate') ?>

Headers:
  Authorization: Bearer YOUR_TOKEN_HERE
  X-PH-Signature: HMAC_SHA256(secret, payload)
  X-PH-Timestamp: 1625097600

Body (JSON):
{
  "external_reference": "ORDER-123",
  "amount": 500,
  "currency": "BDT",
  "payment_method": "bkash",
  "success_url": "https://yoursite.com/success",
  "cancel_url": "https://yoursite.com/cancel"
}</pre>
                            </div>
                        </div>
                        <a href="<?= base_url('admin/payment_hub/regenerate_credentials/' . $project->id) ?>"
                           class="btn btn-warning"
                           onclick="return confirm('This will invalidate existing credentials. Are you sure?')">
                            <i class="fa fa-refresh"></i> Regenerate Credentials
                        </a>
                        <a href="<?= base_url('admin/payment_hub/project_form/' . $project->id) ?>" class="btn btn-default">
                            <i class="fa fa-pencil"></i> Edit Project
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Tokens Panel -->
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="pull-right">
                    <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#issueTokenModal">
                        <i class="fa fa-plus"></i> Issue New Token
                    </button>
                </div>
                <h3 class="panel-title"><i class="fa fa-shield"></i> API Access Tokens</h3>
            </div>
            <div class="panel-body">
                <?php if ($this->session->flashdata('token_raw')): ?>
                    <div class="alert alert-success">
                        <h4 style="margin-top:0"><i class="fa fa-check-circle"></i> Token Issued Successfully!</h4>
                        <p>Copy this token and secret now. <strong>They will never be shown again.</strong></p>
                        <hr>
                        <p><strong>Raw Token:</strong> <code id="raw-token"><?= $this->session->flashdata('token_raw') ?></code> <button class="btn btn-xs btn-default" onclick="copyValue('raw-token')"><i class="fa fa-copy"></i></button></p>
                        <p><strong>Signing Secret:</strong> <code id="signing-secret"><?= $this->session->flashdata('token_secret') ?></code> <button class="btn btn-xs btn-default" onclick="copyValue('signing-secret')"><i class="fa fa-copy"></i></button></p>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Prefix</th>
                                <th>IP Whitelist</th>
                                <th>Status</th>
                                <th>Last Used</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tokens)): foreach ($tokens as $token): ?>
                                <tr>
                                    <td><?= htmlspecialchars($token->token_name) ?></td>
                                    <td><code><?= htmlspecialchars($token->token_prefix) ?>...</code></td>
                                    <td>
                                        <span class="text-muted"><?= $this->api_tokens_model->format_whitelist($token->ip_whitelist) ?></span>
                                        <button class="btn btn-xs btn-link" data-toggle="modal" data-target="#whitelistModal<?= $token->id ?>">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                    </td>
                                    <td>
                                        <?php $lbl = ['active'=>'success', 'disabled'=>'warning', 'revoked'=>'danger'][$token->status]; ?>
                                        <span class="label label-<?= $lbl ?>"><?= ucfirst($token->status) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($token->last_used_at): ?>
                                            <?= $token->last_used_at ?> <br><small class="text-muted"><?= $token->last_used_ip ?></small>
                                        <?php else: ?>
                                            Never
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?= base_url('admin/payment_hub/toggle_token/' . $token->id) ?>" class="btn btn-xs btn-default" title="Toggle Status">
                                                <i class="fa <?= $token->status === 'active' ? 'fa-pause' : 'fa-play' ?>"></i>
                                            </a>
                                            <a href="<?= base_url('admin/payment_hub/revoke_token/' . $token->id) ?>" class="btn btn-xs btn-danger" title="Revoke Permanently" onclick="return confirm('Revoke this token permanently?')">
                                                <i class="fa fa-stop"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Whitelist Modal -->
                                <div class="modal fade" id="whitelistModal<?= $token->id ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <?php echo form_open('admin/payment_hub/update_whitelist/' . $token->id); ?>
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                <h4 class="modal-title">Edit IP Whitelist</h4>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label>Allowed IPs (comma separated)</label>
                                                    <input type="text" name="ip_whitelist" class="form-control" value="<?= implode(',', json_decode($token->ip_whitelist, true) ?: []) ?>" placeholder="e.g. 1.2.3.4, 5.6.7.8">
                                                    <p class="help-block">Leave empty to allow all IPs.</p>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                            </div>
                                            <?php echo form_close(); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; else: ?>
                                <tr><td colspan="6" class="text-center text-muted">No tokens issued yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Issue Token Modal -->
        <div class="modal fade" id="issueTokenModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <?php echo form_open('admin/payment_hub/issue_token/' . $project->id); ?>
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Issue New API Token</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Token Name</label>
                            <input type="text" name="token_name" class="form-control" placeholder="e.g. Staging Server" required>
                        </div>
                        <div class="form-group">
                            <label>Expires At (Optional)</label>
                            <input type="datetime-local" name="expires_at" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Issue Token</button>
                    </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>

        <!-- Transactions -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-list-alt"></i> Transaction History</h3>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
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
                                <td><?= htmlspecialchars($t->external_reference) ?></td>
                                <td><?= display_money($t->amount) ?> <?= $t->currency ?></td>
                                <td><?= ucfirst($t->payment_method ?: '—') ?></td>
                                <td><code><?= htmlspecialchars($t->gateway_transaction_id ?: '—') ?></code></td>
                                <td><span class="label label-<?= $badge ?>"><?= ucfirst($t->status) ?></span></td>
                                <td><?= $t->created_at ?></td>
                                <td>
                                    <a href="<?= base_url('admin/payment_hub/transaction_detail/' . $t->id) ?>" class="btn btn-xs btn-info"><i class="fa fa-eye"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="8" class="text-center text-muted">No transactions for this project yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(elementId) {
    var text = document.getElementById(elementId).textContent;
    navigator.clipboard.writeText(text).then(function() {
        alert('Copied!');
    });
}
function copyValue(elementId) {
    var text = document.getElementById(elementId).textContent;
    navigator.clipboard.writeText(text).then(function() {
        alert('Copied!');
    });
}
</script>
