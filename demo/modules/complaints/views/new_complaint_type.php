<?php
echo message_box('success');
echo message_box('error');
$created = can_action('128', 'created');
$edited = can_action('128', 'edited');
?>
<div class="panel panel-custom">
    <header class="panel-heading ">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                class="sr-only">Close</span></button>
        <?= lang('new') . ' ' . lang('complaint_type') ?></header>
    <?php
    //if (!empty($created) || !empty($edited)) { ?>
        <form method="post" id="lead_sources" action="<?= base_url() ?>admin/complaints/save_new_complaint_type/<?= $id ?>"
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
    <?php //} ?>
</div>
