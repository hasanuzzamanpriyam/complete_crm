<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>
<style>
    .note-editor .note-editable {
        height: 150px;
    }

    .bp0 {
        border: 0px !important;
        padding: 0px !important;
    }

    .float-none {
        float: none !important;
    }
</style>

<div class="row mt-lg">
    <div class="col-sm-2">
        <!-- Tabs within a box -->
        <ul class=" nav nav-pills nav-stacked navbar-custom-nav">
            <li class="<?php if ($active == 'details') echo 'active'; ?>"><a
                        href="<?= base_url('admin/complaints/details/' . $id); ?>"><?= lang('complaint'); ?></a>
            </li>
            <li class="<?php if ($active == 'attachments') echo 'active'; ?>"><a
                        href="<?= base_url('admin/complaints/attachments/' . $id); ?>"><?= lang('attachments'); ?>
                </a></li>
            <li class="<?php if ($active == 'comments') echo 'active'; ?>"><a
                        href="<?php echo base_url('admin/complaints/comments/' . $id); ?>"><?= lang('comments'); ?>
                </a></li>
            <li class="<?php if ($active == 'tasks') echo 'active'; ?>"><a
                        href="<?= base_url('admin/complaints/tasks/' . $id); ?>"><?= lang('tasks'); ?>
                </a></li>
            <li class="<?php if ($active == 'notes') echo 'active'; ?>"><a
                        href="<?= base_url('admin/complaints/notes/' . $id); ?>"
                        aria-expanded="false"><?= lang('notes'); ?> </a>
            </li>
        </ul>
    </div>
    <div class="col-sm-10">
        
        <div class="tab-content bp0">
            
            <?= $page_content ?>
        
        </div>
    </div>
</div>
<script type="text/javascript">
    (function ($) {
        "use strict";
        var maxAppend = 0;
        $("#add_more").on("click", function () {
            if (maxAppend >= 4) {
                alert("Maximum 5 File is allowed");
            } else {
                var add_new = $('<div class="form-group mt-0"">\n\
                    <label for="field-1" class="col-sm-3 control-label"><?= lang('upload_file') ?></label>\n\
        <div class="col-sm-5">\n\
        <div class="fileinput fileinput-new" data-provides="fileinput">\n\
<span class="btn btn-default btn-file"><span class="fileinput-new" >Select file</span><span class="fileinput-exists" >Change</span><input type="file" name="task_files[]" ></span> <span class="fileinput-filename"></span><a href="#" class="close fileinput-exists float-none" data-dismiss="fileinput">&times;</a></div></div>\n\<div class="col-sm-2">\n\<strong>\n\
<a href="javascript:void(0);" class="remCF"><i class="fa fa-times"></i>&nbsp;Remove</a></strong></div>');
                maxAppend++;
                $("#add_new").append(add_new);
            }
        });

        $("#add_new").on('click', '.remCF', function () {
            $(this).parent().parent().parent().remove();
        });
    })(jQuery);
</script>