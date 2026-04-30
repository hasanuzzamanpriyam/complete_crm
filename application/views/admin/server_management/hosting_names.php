<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<script src="<?= base_url() ?>assets/plugins/dataTables/js/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/plugins/dataTables/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>assets/plugins/dataTables/js/dataTables.bootstrap.min.js"></script>

<style>
    .card {
        border: none;
        border-radius: 8px;
        background: #fff;
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
</style>

<div class="row mb-lg">
    <div class="col-sm-12 text-right">
        <a href="<?= base_url('admin/server_management/add_hosting_type') ?>" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#myModal"><i class="fa fa-plus"></i> Add Hosting</a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="hostingNamesTable">
                        <thead>
                            <tr>
                                <th>Hosting Name</th>
                                <th>Created At</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($hostings)): ?>
                                <?php foreach ($hostings as $hosting): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($hosting['hosting_name']) ?></td>
                                        <td><?= !empty($hosting['created_at']) ? $hosting['created_at'] : '-' ?></td>
                                        <td class="text-center">
                                            <a href="<?= base_url('admin/server_management/delete_hosting_name/' . $hosting['id']) ?>" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure you want to delete this hosting?')"><i class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
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
        $('#hostingNamesTable').DataTable();
    });
</script>
