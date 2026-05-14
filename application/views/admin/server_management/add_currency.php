<?php if (!$this->input->is_ajax_request()): ?>
<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Add New Currency</h5>
            </div>
            <div class="card-body">
<?php else: ?>
<div class="panel panel-custom">
    <div class="panel-heading">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">Add New Currency</h4>
    </div>
<?php endif; ?>

<form id="currencyForm" action="<?= base_url('admin/ajax_api/add_currency') ?>" method="post" class="form-horizontal">
    <?php if ($this->input->is_ajax_request()): ?>
    <div class="modal-body">
    <?php endif; ?>

        <div class="form-group">
            <label class="col-sm-4 control-label">Currency Name <span class="text-danger">*</span></label>
            <div class="col-sm-7">
                <input type="text" name="name" class="form-control" placeholder="e.g. US Dollar" required>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-4 control-label">Currency Code <span class="text-danger">*</span></label>
            <div class="col-sm-7">
                <input type="text" name="code" class="form-control" placeholder="e.g. USD" required>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-4 control-label">Currency Symbol <span class="text-danger">*</span></label>
            <div class="col-sm-7">
                <input type="text" name="symbol" class="form-control" placeholder="e.g. $" required>
            </div>
        </div>
        <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">

    <?php if ($this->input->is_ajax_request()): ?>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
    <?php endif; ?>

<?php if (!$this->input->is_ajax_request()): ?>
            </div>
            <div class="card-footer bg-light">
                <button type="submit" class="btn btn-danger">Save</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        $("#currencyForm").validate({
            rules: {
                name: { required: true },
                code: { required: true },
                symbol: { required: true }
            },
            submitHandler: function (form) {
                <?php if ($this->input->is_ajax_request()): ?>
                var formData = $(form).serialize();
                $.ajax({
                    type: "POST",
                    url: $(form).attr('action'),
                    data: formData,
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 'success') {
                            // Update both possible dropdown IDs
                            var $select = $('#currency_id');
                            if (!$select.length) $select = $('#domain_currency_id');
                            
                            if ($select.length) {
                                var newOption = new Option(data.code, data.code, true, true);
                                $select.append(newOption).trigger('change');
                            }
                            
                            $('.modal').modal('hide');
                            if (typeof toastr !== 'undefined') {
                                toastr.success(data.message || 'Currency added successfully');
                            }
                        } else {
                            alert(data.message || 'Error occurred');
                        }
                    },
                    error: function(xhr) {
                        alert('An error occurred. Please try again.');
                    }
                });
                return false;
                <?php else: ?>
                form.submit();
                <?php endif; ?>
            }
        });
    });
</script>
