<?php
echo message_box('success');
echo message_box('error');
$created = can_action_by_label('contracts', 'created');
$edited = can_action_by_label('contracts', 'edited');
?>
<div class="panel panel-custom">
    <header class="panel-heading ">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                    class="sr-only">Close</span></button>
        <?= lang('new') . ' ' . lang('contract_type') ?></header>
    <?php
    if (!empty($created) || !empty($edited)) { ?>
        <form method="post" id="new_contract_type"
              action="<?= base_url() ?>admin/contracts/save_new_contract_type/<?= $id ?>"
              class="form-horizontal" data-parsley-validate="" novalidate="">
            <div class="form-group">
                <label
                        class="col-sm-3 control-label"><?= lang('type_name') ?></label>
                <div class="col-sm-5">
                    <input type="text" name="name" class="form-control" value="<?= set_value('name', $type_name) ?>"
                           placeholder="<?= lang('type_name') ?>" required>
                </div>
            </div>
            <div class="form-group mt">
                <label class="col-lg-3"></label>
                <div class="col-lg-3">
                    <button type="submit"
                            class="btn btn-sm btn-primary"><?= lang('save') ?></button>
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close') ?></button>
                </div>
            </div>
        </form>
    <?php } ?>
</div>

<script type="text/javascript">
    $(document).on("submit", "form#new_contract_type", function (event) {
        var form = $(event.target);
        var id = form.attr('id');
        event.preventDefault();
        $.ajax({
            type: form.attr('method'),
            url: form.attr('action'),
            data: form.serialize()
        }).done(function (response) {
            response = JSON.parse(response);
            if (response.status == 'success') {
                var groups = $('select[name="contract_type"]');
                // check if select is exist then append new option and selected it in select2
                if (groups.length) {
                    groups.prepend($('<option selected value="' + response.id + '">' + response.name + '</option>'));
                    groups.select2({
                        theme: "bootstrap",
                        placeholder: "<?= lang('select') . ' ' . lang('contract_type') ?>",
                        width: '100%'
                    });
                } else {
                    // reload the page if select is not exist
                    location.reload();
                }
                toastr[response.status](response.message);
            }
            $('#myModal').modal('hide');
        }).fail(function () {
            console.log('There was a problem with AJAX')
        });
    })
</script>