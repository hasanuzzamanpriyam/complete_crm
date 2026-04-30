<div class="panel panel-custom">
    <div class="panel-heading">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">Add New Domain Type</h4>
    </div>
    <form id="add_domain_type_form" action="<?= base_url() ?>admin/server_management/add_domain_type" method="post" class="form-horizontal">
        <div class="modal-body">
            <div class="form-group">
                <label class="col-sm-3 control-label">Domain Type <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <input type="text" name="domain_type" class="form-control" placeholder="e.g. COM, NET, ORG" required>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#add_domain_type_form").validate({
            rules: {
                domain_type: {
                    required: true
                }
            },
            submitHandler: function (form) {
                var data = $(form).serialize();
                $.ajax({
                    type: "POST",
                    url: $(form).attr('action'),
                    data: data,
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 'success') {
                            if ($('#domain_type_select').length) {
                                var newOption = new Option(data.text, data.id, true, true);
                                $('#domain_type_select').append(newOption).trigger('change');
                            }
                            $('#myModal').modal('hide');
                        } else {
                            alert(data.message);
                        }
                    }
                });
                return false;
            }
        });
    });
</script>
