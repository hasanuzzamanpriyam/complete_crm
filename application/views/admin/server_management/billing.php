<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<div class="row mb-lg">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title"><?php echo lang('billing_order'); ?></h4>
                <a href="<?= base_url('admin/server_management/add_billing') ?>" class="btn btn-sm btn-danger"><i class="fa fa-plus"></i> Add Billing Order</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="billingTable">
                        <thead>
                            <tr>
                                <th><?= lang('billing_label') ?></th>
                                <th><?= lang('billing_type') ?></th>
                                <th><?= lang('billing_value') ?></th>
                                <th><?= lang('renewal_date') ?></th>
                                <th><?= lang('expiry_date') ?></th>
                                <th><?= lang('renew') ?></th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($all_billing)): ?>
                                <?php foreach ($all_billing as $billing): ?>
                                    <tr>
                                        <td><strong><?= $billing->label ?></strong></td>
                                        <td><span class="badge badge-info"><?= $billing->type ?></span></td>
                                        <td><?= $billing->currency ?> <?= number_format($billing->value, 2) ?></td>
                                        <td><?= $billing->renewal_date ?></td>
                                        <td><?= $billing->expiry_date ?></td>
                                        <td>
                                            <span class="badge <?= $billing->renew == 'Auto' ? 'badge-success' : 'badge-warning' ?>">
                                                <?= $billing->renew ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= base_url('admin/server_management/add_billing/' . $billing->id) ?>" class="btn btn-xs btn-outline-primary" title="Edit"><i class="fa fa-pencil"></i></a>
                                            <a href="<?= base_url('admin/server_management/delete_billing/' . $billing->id) ?>" class="btn btn-xs btn-outline-danger" title="Delete" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No billing orders found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#billingTable').DataTable();
    });
</script>
