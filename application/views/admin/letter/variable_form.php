<div class="panel panel-custom">
    <header class="panel-heading ">
        <div class="panel-title">
            <strong><?= !empty($variable_info) ? 'Edit Variable' : 'Add Variable' ?></strong>
        </div>
    </header>

    <div class="panel-body">
        <?= form_open(base_url('admin/letter/save_variable/' . (!empty($variable_info) ? $variable_info->id : '')), array('id' => 'variable_form', 'class' => 'form-horizontal', 'role' => 'form')); ?>

        <div class="form-group">
            <label class="col-lg-3 control-label">Variable Name <span class="required">*</span></label>
            <div class="col-lg-8">
                <input type="text" name="name" class="form-control" required
                       value="<?= !empty($variable_info) ? $variable_info->name : '' ?>"
                       placeholder="e.g. SPONSOR_NAME" <?= !empty($variable_info) ? 'readonly' : '' ?>>
                <span class="help-block">Use uppercase letters and underscores only. Will be used as ##VARIABLE_NAME##</span>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-3 control-label">Label <span class="required">*</span></label>
            <div class="col-lg-8">
                <input type="text" name="label" class="form-control" required
                       value="<?= !empty($variable_info) ? $variable_info->label : '' ?>"
                       placeholder="e.g. Sponsor Name">
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-3 control-label">Category</label>
            <div class="col-lg-8">
                <select name="category" class="form-control">
                    <option value="general" <?= !empty($variable_info) && $variable_info->category === 'general' ? 'selected' : '' ?>>General</option>
                    <option value="employee" <?= !empty($variable_info) && $variable_info->category === 'employee' ? 'selected' : '' ?>>Employee</option>
                    <option value="company" <?= !empty($variable_info) && $variable_info->category === 'company' ? 'selected' : '' ?>>Company</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-3 control-label">Default Value</label>
            <div class="col-lg-8">
                <input type="text" name="default_value" class="form-control"
                       value="<?= !empty($variable_info) ? htmlspecialchars($variable_info->default_value) : '' ?>"
                       placeholder="Optional default value">
                <span class="help-block">This value will be used unless overridden when generating a letter.</span>
            </div>
        </div>

        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-8">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-check"></i> Save
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
        </div>

        <?= form_close(); ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('#variable_form').on('submit', function (e) {
            e.preventDefault();
            var url = $(this).attr('action');

            $.ajax({
                type: 'POST',
                url: url,
                data: $(this).serialize(),
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        toastr.success(response.message);
                        $('#myModal').modal('hide');
                        if (typeof reload_table === 'function') {
                            reload_table();
                        } else if (typeof $('.DataTables').DataTable !== 'undefined') {
                            $('.DataTables').DataTable().ajax.reload(null, false);
                        }
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function () {
                    toastr.error('An error occurred while saving the variable.');
                }
            });
        });
    });
</script>
