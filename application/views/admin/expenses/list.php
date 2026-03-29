<div class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title">
            <strong><?= $title ?></strong>
            <div class="pull-right hidden-print">
                <a href="<?= base_url('admin/expenses/create') ?>" class="btn btn-xs btn-info">
                    <i class="fa fa-plus"></i> <?= lang('add_schedule') ?: 'New Schedule' ?>
                </a>
            </div>
        </div>
    </div>
    
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered DataTables" id="expense_schedules_table">
                <thead>
                    <tr>
                        <th style="width: 20%"><?= lang('task_name') ?: 'Task Name' ?></th>
                        <th style="width: 35%"><?= lang('description') ?: 'Description' ?></th>
                        <th style="width: 10%"><?= lang('payment_type') ?: 'Repeat Rate' ?></th>
                        <th style="width: 10%"><?= lang('amount') ?: 'Amount' ?></th>
                        <th style="width: 10%"><?= lang('action') ?: 'Action' ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($schedules)) : ?>
                        <?php foreach ($schedules as $schedule) : ?>
                            <tr>
                                <td>
                                    <strong><?= $schedule['task_name'] ?></strong>
                                </td>
                                <td><?= $schedule['description'] ?></td>
                                <td><span class="label label-primary"><?= ucfirst($schedule['payment_type']) ?></span></td>
                                <td><?= number_format($schedule['amount'], 2) ?></td>
                                <td>
                                    <a href="<?= base_url('admin/expenses/edit/' . $schedule['id']) ?>" 
                                       class="btn btn-xs btn-primary">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="<?= base_url('admin/expenses/delete/' . $schedule['id']) ?>" 
                                       class="btn btn-xs btn-danger" 
                                       onclick="return confirm('<?= lang('delete_alert') ?: 'Are you sure you want to delete this schedule? This will also purge associated occurrence records limitlessly.' ?>');">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6" class="text-center"><?= lang('nothing_to_display') ?: 'No active schedules found.' ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
