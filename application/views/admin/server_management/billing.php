<style>
    .badge {
        padding: 5px 10px;
        font-weight: 500;
        border-radius: 4px;
        font-size: 11px;
    }
    .badge-active { background-color: #28a745; color: #fff; }
    .badge-pending { background-color: #ffc107; color: #212529; }
    .badge-expired { background-color: #dc3545; color: #fff; }
    .badge-info { background-color: #17a2b8; color: #fff; }
</style>

<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<div class="row mb-lg">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <!-- <h4 class="card-title"><?php echo lang('billing_order'); ?></h4> -->
                <a href="<?= base_url('admin/server_management/add_billing') ?>" class="btn btn-sm btn-danger"><i class="fa fa-plus"></i> Add Billing Order</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="billingTable">
                        <thead>
                            <tr>
                                <th><?= lang('billing_label') ?></th>
                                <th>Category</th>
                                <th>Provider</th>
                                <th>Client</th>
                                <th>Status</th>
                                <th>Next Renew</th>
                                <th>Price</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($all_billing)): ?>
                                <?php foreach ($all_billing as $billing): ?>
                                    <tr>
                                        <td><strong><?= $billing->label ?></strong></td>
                                        <td><span class="badge badge-info"><?= $billing->type ?></span></td>
                                        <td><?= !empty($billing->provider_name) ? $billing->provider_name : 'N/A' ?></td>
                                        <td><?= !empty($billing->client_name) ? $billing->client_name : 'N/A' ?></td>
                                        <td>
                                            <?php 
                                                if ($billing->status == 'Active') {
                                                    $status_class = 'active';
                                                } elseif ($billing->status == 'Pending') {
                                                    $status_class = 'pending';
                                                } elseif ($billing->status == 'Expired') {
                                                    $status_class = 'expired';
                                                } else {
                                                    $status_class = 'info';
                                                }
                                            ?>
                                            <span class="badge badge-<?= $status_class ?>"><?= $billing->status ?></span>
                                        </td>
                                        <td><?= $billing->renewal_date ?></td>
                                        <td><?= $billing->currency ?> <?= number_format((float)$billing->value, 2) ?></td>
                                        <td class="text-center">
                                            <a href="<?= base_url('admin/server_management/view_billing/' . $billing->id) ?>" class="btn btn-xs btn-outline-info" title="View Details" data-toggle="modal" data-target="#myModal"><i class="fa fa-list-alt"></i></a>
                                            <a href="<?= base_url('admin/server_management/add_billing/' . $billing->id) ?>" class="btn btn-xs btn-outline-primary" title="Edit"><i class="fa fa-pencil"></i></a>
                                            <a href="<?= base_url('admin/server_management/delete_billing/' . $billing->id) ?>" class="btn btn-xs btn-outline-danger" title="Delete" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No billing orders found.</td>
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
