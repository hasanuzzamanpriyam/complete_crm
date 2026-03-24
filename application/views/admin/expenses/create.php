<div class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title">
            <strong><?= $title ?></strong>
            <div class="pull-right">
                <a href="<?= base_url('admin/expenses') ?>" class="btn btn-xs btn-default">
                    <i class="fa fa-arrow-left"></i> <?= lang('back') ?: 'Back to Schedules' ?>
                </a>
            </div>
        </div>
    </div>
    
    <div class="panel-body">
        <form id="add_expense_form" method="POST" action="<?= base_url('admin/expenses/add_expense') ?>" class="form-horizontal">
            
            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('task_name') ?: 'Task Name' ?> <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <input type="text" name="task_name" class="form-control" required placeholder="e.g. Server Hosting">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('payment_type') ?: 'Payment Type' ?> <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <select name="payment_type" class="form-control" required>
                        <option value="daily">Daily</option>
                        <option value="monthly" selected>Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('last_payment_date') ?: 'Initial Date' ?> <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <div class="input-group">
                        <input type="date" name="last_payment_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('amount') ?: 'Amount' ?> <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <div class="input-group">
                        <input type="number" step="0.01" name="amount" class="form-control" required placeholder="0.00">
                        <div class="input-group-addon">$</div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label"><?= lang('description') ?: 'Description' ?></label>
                <div class="col-sm-6">
                    <textarea name="description" class="form-control" rows="4"></textarea>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-6">
                    <button type="submit" id="btn_submit_expense" class="btn btn-primary margin"><?= lang('save') ?: 'Save Schedule' ?></button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Handle the AJAX submission to natively hook into the JSON response
    $(document).ready(function() {
        $('#add_expense_form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = $('#btn_submit_expense');
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                type: 'POST',
                url: form.attr('action'),
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Show success alert and forcefully redirect back to the list
                        alert(response.message);
                        window.location.href = "<?= base_url('admin/expenses') ?>";
                    } else {
                        // Display error string
                        alert(response.message);
                        btn.prop('disabled', false).html('<?= lang('save') ?: 'Save Schedule' ?>');
                    }
                },
                error: function() {
                    alert("A network error occurred while submitting.");
                    btn.prop('disabled', false).html('<?= lang('save') ?: 'Save Schedule' ?>');
                }
            });
        });
    });
</script>
