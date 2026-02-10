<div class="panel panel-custom">
    <div class="panel-heading">
        <h3 class="panel-title">Notes</h3>
    </div>
    <div class="panel-body">
        <form action="<?= base_url('admin/' . $module . '/save_notes/' . $id) ?>" enctype="multipart/form-data"
              method="post" id="form" class="form-horizontal">
            <input type="hidden" name="module" value="<?php echo $module; ?>" class="form-control">
            <input type="hidden" name="module_field_id" value="<?php echo $module_field_id; ?>" class="form-control">
            <div class="form-group">
                <div class="col-lg-12">
                    <textarea class="form-control textarea" name="notes"></textarea>
                </div>
            </div>
            
            <div class="form-group">
                <div class="col-sm-2">
                    <button type="submit" id="sbtn" class="btn btn-primary"><?= lang('updates') ?></button>
                </div>
            </div>
        </form>
        <?php
        
        if (!empty($module_notes)) {
            ?>
            <hr class="mt-md mb-sm"/>
            <?php foreach ($module_notes as $v_notes) { ?>
                <div class="mb-mails col-sm-12" id="module_notes_<?php $v_notes->notes_id ?>">
                    <img alt="Mail Avatar" src="<?= base_url(staffImage($v_notes->user_id)) ?>"
                         class="mb-mail-avatar pull-left">
                    <div class="mb-mail-date pull-right">
                        <?php if (!empty($v_notes->last_contact)) { ?>
                            <span data-toggle="tooltip" title="<?= $v_notes->last_contact ?>"><i
                                        class="fa fa-phone-square text-success"></i></span>
                        <?php } ?>
                        <?= time_ago($v_notes->created_time) ?> <strong data-toggle="tooltip" data-placement="top"
                                                                        class="pointer" title=""
                                                                        data-fade-out-on-success="#module_notes_<?php $v_notes->notes_id ?>"
                                                                        data-act="ajax-request"
                                                                        data-action-url="<?= base_url('admin/' . $v_notes->module . '/delete_notes/' . $v_notes->notes_id . '/' . $v_notes->module_field_id) ?>"
                                                                        data-original-title="Delete"><i
                                    class="text-danger fa fa-trash-o"></i></strong>
                    </div>
                    <div class="mb-mail-meta">
                        <div class="pull-left">
                            <div class="mb-mail-from"><a
                                        href="<?= base_url('admin/user/user_details/' . $v_notes->user_id) ?>">
                                    <?= fullname($v_notes->user_id) ?></a>
                            </div>
                        </div>
                        <div class="mb-mail-preview"><?= $v_notes->notes ?></div>
                        <div class="mb-mail-album pull-left"></div>
                    
                    
                    </div>
                </div>
            <?php }
        } ?>
    </div>
</div>