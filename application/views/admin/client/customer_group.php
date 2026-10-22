<?php
echo message_box('success');
echo message_box('error');
$created = can_action('125', 'created');
$edited = can_action('125', 'edited');
?>

<?php $this->load->view('admin/client/_client_assets'); ?>
<div class="cl-modal">
    <div class="cl-modal-head">
        <h3><?= lang('customer_group') ?></h3>
        <button type="button" class="cl-x" data-dismiss="modal">&times;</button>
    </div>
    <?php if (!empty($created) || !empty($edited)) { ?>
        <?php echo form_open(base_url('admin/client/update_customer_group'), array('id' => 'group_modal', 'class' => 'cl-form cl-form-card')); ?>
        <div class="cl-modal-body">
            <div class="cl-frow">
                <label><?= lang('customer_group') ?></label>
                <input type="text" name="customer_group" class="cl-input"
                       placeholder="<?= lang('customer_group') ?>" required>
            </div>
            
            <div class="cl-frow">
                <label><?= lang('description') ?></label>
                <input type="text" name="description" class="cl-input"
                       placeholder="<?= lang('description') ?>">
            </div>
        </div>
        <div class="cl-modal-foot">
            <button type="submit" class="cl-btn cl-btn-primary"><?= lang('save') ?></button>
            <button type="button" class="cl-btn cl-btn-ghost" data-dismiss="modal"><?= lang('close') ?></button>
        </div>
        <?php echo form_close(); ?>
    <?php } ?>
</div>

<script type="text/javascript">
$(document).on("submit", "form", function (event) {
    var form = $(event.target);
    if (form.attr('action') == '<?= base_url('admin/client/update_customer_group')?>') {
        event.preventDefault();
        $.ajax({
            type: form.attr('method'),
            url: form.attr('action'),
            data: form.serialize()
        }).done(function (response) {
            response = JSON.parse(response);
            if (response.status == 'success') {
                if (typeof (response.id) != 'undefined') {
                    var groups = $('select[name="customer_group_id"]');
                    groups.prepend('<option selected value="' + response.id + '">' + response.group + '</option>');
                    var select2Instance = groups.data('select2');
                    var resetOptions = select2Instance.options.options;
                    groups.select2('destroy').select2(resetOptions)
                }
                toastr[response.status](response.message);
                $('#myModal').modal('hide');
            }
        }).fail(function () {
            alert('There was a problem with AJAX');
        });
    }
});
</script>