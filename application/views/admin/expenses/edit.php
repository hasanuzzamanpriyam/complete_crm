<div class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title">
            <strong><?= $title ?></strong>
            <div class="pull-right">
                <a href="<?= base_url('admin/expenses') ?>" class="btn btn-xs btn-default">
                    <i class="fa fa-arrow-left"></i> <?= lang('back') ?: 'Back' ?>
                </a>
            </div>
        </div>
    </div>
    
    <div class="panel-body">
        <form id="edit_expense_form" method="POST" action="<?= base_url('admin/expenses/update_expense/' . $expense['id']) ?>" class="form-horizontal">
            
            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('task_name') ?: 'Task Name' ?> <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <input type="text" name="task_name" class="form-control" required value="<?= $expense['task_name'] ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('payment_type') ?: 'Payment Type' ?> <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <select name="payment_type" class="form-control" required>
                        <option value="daily" <?= $expense['payment_type'] == 'daily' ? 'selected' : '' ?>>Daily</option>
                        <option value="monthly" <?= $expense['payment_type'] == 'monthly' ? 'selected' : '' ?>>Monthly</option>
                        <option value="bi-monthly" <?= $expense['payment_type'] == 'bi-monthly' ? 'selected' : '' ?>>Bi-monthly</option>
                        <option value="quarterly" <?= $expense['payment_type'] == 'quarterly' ? 'selected' : '' ?>>Quarterly</option>
                        <option value="yearly" <?= $expense['payment_type'] == 'yearly' ? 'selected' : '' ?>>Yearly</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('last_payment_date') ?: 'Anchor Date' ?> <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <div class="input-group">
                        <input type="date" name="last_payment_date" class="form-control" required value="<?= $expense['last_payment_date'] ?>">
                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('amount') ?: 'Amount' ?> <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <div class="input-group">
                        <input type="number" step="0.01" name="amount" class="form-control" required value="<?= $expense['amount'] ?>">
                        <div class="input-group-addon">$</div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('description') ?: 'Description' ?></label>
                <div class="col-sm-6">
                    <textarea name="description" class="form-control" rows="4"><?= $expense['description'] ?></textarea>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-6">
                    <button type="submit" id="btn_update_expense" class="btn btn-primary margin"><?= lang('update') ?: 'Update Schedule' ?></button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#edit_expense_form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = $('#btn_update_expense');
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');

            $.ajax({
                type: 'POST',
                url: form.attr('action'),
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        window.location.href = "<?= base_url('admin/expenses') ?>";
                    } else {
                        alert(response.message);
                        btn.prop('disabled', false).html('<?= lang('update') ?: 'Update Schedule' ?>');
                    }
                },
                error: function() {
                    alert("A network error occurred.");
                    btn.prop('disabled', false).html('<?= lang('update') ?: 'Update Schedule' ?>');
                }
            });
        });
    });
</script>
