<?php $this->load->view('admin/client/_client_assets'); ?>
<div class="cl-modal">
    <div class="cl-modal-head">
        <h3>Quick Add Client</h3>
        <button type="button" class="cl-x" data-dismiss="modal">&times;</button>
    </div>
    <form id="quickAddClientForm" action="<?= base_url('admin/client/update_client') ?>" method="post" class="cl-form cl-modal-body">
        <div class="cl-frow">
            <label><?= lang('company_name') ?> <span class="cl-req">*</span></label>
            <input type="text" name="name" class="cl-input" placeholder="<?= lang('enter_company_name') ?>" required>
        </div>
        <div class="cl-frow">
            <label><?= lang('email') ?> <span class="cl-req">*</span></label>
            <input type="email" name="email" class="cl-input" placeholder="<?= lang('enter_email') ?>" required>
        </div>
        <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
        <div class="cl-modal-foot">
            <button type="button" class="cl-btn cl-btn-ghost" data-dismiss="modal"><?= lang('close') ?></button>
            <button type="submit" class="cl-btn cl-btn-primary"><?= lang('save') ?></button>
        </div>
    </form>
</div>

<script type="text/javascript">
$(document).on("submit", "form", function(event) {
    var form = $(event.target);
    if (form.attr('action') == '<?= base_url('admin/client/update_client') ?>') {
        event.preventDefault();
        $.ajax({
            type: form.attr('method'),
            url: form.attr('action'),
            data: form.serialize()
        }).done(function(response) {
            response = JSON.parse(response);
            if (response.status == 'success') {
                if (typeof(response.id) != 'undefined') {
                    var client_id = $('select[name="client_id"]');
                    var paid_by = $('select[name="paid_by"]');
                    if (client_id.length != 0) {
                        var groups = client_id;
                    } else if (paid_by.length != 0) {
                        var groups = paid_by;
                    }
                    groups.prepend('<option selected value="' + response.id + '">' + response.name + '</option>');
                    var select2Instance = groups.data('select2');
                    var resetOptions = select2Instance.options.options;
                    groups.select2('destroy').select2(resetOptions)
                }
            }
            toastr[response.status](response.message);
            $('#myModal').modal('hide');
        }).fail(function() {
            console.log('There was a problem with AJAX')
        });
    }
});
</script>