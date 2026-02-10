<div class="panel panel-custom">
    <div class="panel-heading">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                    class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><?= lang('new') . ' ' . lang('template') ?></h4>
    </div>
    <div class="modal-body wrap-modal wrap">
        
        <form action="<?= base_url('admin/' . $module . '/save_template/'); ?><?php
        if (!empty($template_info->template_id)) {
            echo $template_info->template_id;
        }
        ?>" class="form-horizontal form-groups-bordered" role="form" method="post" enctype="multipart/form-data"
              accept-charset="utf-8" novalidate="novalidate">
            <div class="modal-body clearfix">
                
                <div class="form-group">
                    <label class="col-lg-3 control-label"><?= lang('template_name') ?><span class="text-danger">*</span></label>
                    <div class="col-lg-6">
                        <input name="template_name" class="form-control" value="<?php
                        if (!empty($template_info->template_name)) {
                            echo $template_info->template_name;
                        }
                        ?>" required placeholder="<?= lang('template') . ' ' . lang('name') ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label"><?= lang('content') ?></label>
                    <div class="col-lg-6">
                        <textarea name="template_content" class="form-control textarea"
                                  placeholder="<?= lang('content') ?>"><?php
                            if (!empty($template_info->template_content)) {
                                echo $template_info->template_content;
                            }
                            ?></textarea>
                    </div>
                </div>
                <input type="hidden" name="module" value="<?php echo $module; ?>" class="form-control">
                <input type="hidden" name="module_field_id" value="<?php echo $module_field_id; ?>"
                       class="form-control">
            </div>
            <div id="file-modal-footer"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default cancel-upload" data-dismiss="modal"><span
                            class="fa fa-close"></span> <?php echo lang('close'); ?></button>
                <button type="submit" class="btn btn-primary "><span
                            class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
            </div>
        </form>
    
    
    </div>
</div>