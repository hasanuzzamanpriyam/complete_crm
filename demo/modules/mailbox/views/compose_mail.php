<link href="<?php echo base_url() ?>asset/css/select2.css" rel="stylesheet"/>
<script src="<?php echo base_url() ?>asset/js/select2.js"></script>
<form role="form" id="form" enctype="multipart/form-data" action="<?php echo base_url() ?>admin/mailbox/send_mail/<?php
if (!empty($get_draft_info->draft_id)) {
    echo $get_draft_info->draft_id;
}
?>" method="post" class="form-horizontal form-groups-bordered" data-parsley-validate novalidate>
    <!-- Content Header (Page header) -->
    <?php
    $to = '';
    $subject = '';
    $body = '';
    $method = $this->uri->segment(6);
    $cc = '';
    if ($action_type == 'inbox') {
        if ($method == 'reply') {
            $subject = 'RE: ' . $inbox_info->subject;
            $all_to = unserialize($inbox_info->to);;
        } else if ($method == 'replyall') {
            $subject = 'RE: ' . $inbox_info->subject;
            $all_to[] = $inbox_info->from;
            $allto = unserialize($inbox_info->to);
            if (empty($allto)) {
                $allto = $inbox_info->to;
            }
            $all_to[] = $allto;
            $cc = $inbox_info->cc;
        } else {
            $subject = 'FW: ' . $inbox_info->subject;
            $body = $inbox_info->message_body;
            $uploaded_file = json_decode($inbox_info->attach_file);
        }
    } else if (!empty($get_draft_info)) {
        $all_to = unserialize($get_draft_info->to);
        if ($method == 'reply') {
            $subject = 'RE: ' . $get_draft_info->subject;
        } else if ($method == 'replyall') {
            $subject = 'RE: ' . $get_draft_info->subject;
        } else {
            $subject = 'FW: ' . $get_draft_info->subject;
            $body = $get_draft_info->message_body;
        }
    }
    ?>
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-body">
                <div class="form-group col-md-12">
                    <select multiple="multiple" required="" placeholder="<?= lang('to') ?>" name="to[]"
                            data-width="100%" class="select_2_to">
                        <option value=""></option>
                        <?php
                        if (!empty($get_user_info)) : foreach ($get_user_info as $v_user_info) :
                            $user = $this->mailbox_model->check_by(array('user_id' => $v_user_info->user_id), 'tbl_account_details');
                            if (!empty($user)) {
                                if ($v_user_info->role_id == 1) {
                                    $role = lang('admin');
                                } elseif ($v_user_info->role_id == 3) {
                                    $role = lang('staff');
                                } else {
                                    $role = lang('client');
                                }
                                ?>
                                <option value="<?php echo $v_user_info->email ?>"><?php echo $user->fullname . ' (<small>' . $role . '</small> )' ?></option>
                                <?php
                            }
                        endforeach;
                            ?>
                        <?php endif; ?>
                        <?php
                        if (!empty($all_to)) {
                            foreach ($all_to as $v_email) { ?>
                                <option value="<?= $v_email ?>" selected><?= $v_email ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group col-md-12">
                    <input class="form-control" value="<?= set_value('cc', $cc) ?>" type="text" name="cc"
                           placeholder="<?= lang('cc:') ?>"/>
                </div>
                <div class="form-group col-md-12">
                    <input class="form-control" value="<?= set_value('subject', $subject) ?>" type="text" required=""
                           name="subject"
                           placeholder="Subject:"/>
                </div>
                <div class="form-group col-md-12">
                    <textarea class="form-control text-justify textarea_"
                              name="message_body"><?= set_value('message_body', $body) ?></textarea>
                </div>
                <div class="form-group col-md-12">
                    <div id="comments_file-dropzone" class="dropzone mb15">
                    
                    </div>
                    <div id="comments_file-dropzone-scrollbar">
                        <div id="comments_file-previews">
                            <div id="file-upload-row" class="mt pull-left">
                                
                                <div class="preview box-content pr-lg w-100">
                                    <span data-dz-remove class="pull-right pointer">
                                        <i class="fa fa-times"></i>
                                    </span>
                                    <img data-dz-thumbnail class="upload-thumbnail-sm"/>
                                    <input class="file-count-field" type="hidden" name="files[]" value=""/>
                                    <div class="mb progress progress-striped upload-progress-sm active mt-sm"
                                         role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                        <div class="progress-bar progress-bar-success w-0" data-dz-uploadprogress></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    if (!empty($uploaded_file)) {
                        foreach ($uploaded_file as $v_files_image) { ?>
                            <div class="pull-left mt pr-lg mb w-100">
                                <span data-dz-remove class="pull-right existing_image pointer"><i
                                            class="fa fa-times"></i></span>
                                <?php if ($v_files_image->is_image == 1) { ?>
                                    <img data-dz-thumbnail
                                         src="<?php echo base_url('modules/mailbox/') . html_escape($v_files_image->path) ?>"
                                         class="upload-thumbnail-sm"/>
                                <?php } else { ?>
                                    <span data-toggle="tooltip" data-placement="top"
                                          title="<?= html_escape($v_files_image->fileName) ?>"
                                          class="mailbox-attachment-icon"><i
                                                class="fa fa-file-text-o"></i></span>
                                <?php } ?>
                                
                                <input type="hidden" name="path[]" value="<?php echo html_escape($v_files_image->path); ?>">
                                <input type="hidden" name="fileName[]" value="<?php echo html_escape($v_files_image->fileName); ?>">
                                <input type="hidden" name="fullPath[]" value="<?php echo html_escape($v_files_image->fullPath); ?>">
                                <input type="hidden" name="size[]" value="<?php echo html_escape($v_files_image->size); ?>">
                                <input type="hidden" name="is_image[]" value="<?php echo html_escape($v_files_image->is_image); ?>">
                            </div>
                        <?php }; ?>
                    <?php }; ?>
                    <script type="text/javascript">
                        $(function () {
                            'use strict';
                            $(".existing_image").on("click", function () {
                                $(this).parent().remove();
                            });
                            var fileSerial = 0;
                            var previewNode = document.querySelector("#file-upload-row");
                            previewNode.id = "";
                            var previewTemplate = previewNode.parentNode.innerHTML;
                            previewNode.parentNode.removeChild(previewNode);
                            Dropzone.autoDiscover = false;
                            var projectFilesDropzone = new Dropzone("#comments_file-dropzone", {
                                url: "<?= base_url() ?>admin/global_controller/upload_file",
                                thumbnailWidth: 80,
                                thumbnailHeight: 80,
                                parallelUploads: 20,
                                previewTemplate: previewTemplate,
                                dictDefaultMessage: '<?php echo lang("file_upload_instruction"); ?>',
                                autoQueue: true,
                                previewsContainer: "#comments_file-previews",
                                clickable: true,
                                accept: function (file, done) {
                                    if (file.name.length > 200) {
                                        done("Filename is too long.");
                                        $(file.previewTemplate).find(".description-field").remove();
                                    }
                                    $.ajax({
                                        url: "<?= base_url() ?>admin/global_controller/validate_project_file",
                                        data: {
                                            file_name: file.name,
                                            file_size: file.size
                                        },
                                        cache: false,
                                        type: 'POST',
                                        dataType: "json",
                                        success: function (response) {
                                            if (response.success) {
                                                fileSerial++;
                                                $(file.previewTemplate).find(".description-field").attr("name", "comment_" + fileSerial);
                                                $(file.previewTemplate).append("<input type='hidden' name='file_name_" + fileSerial + "' value='" + file.name + "' />\n\
                                                                        <input type='hidden' name='file_size_" + fileSerial + "' value='" + file.size + "' />");
                                                $(file.previewTemplate).find(".file-count-field").val(fileSerial);
                                                done();
                                            } else {
                                                $(file.previewTemplate).find("input").remove();
                                                done(response.message);
                                            }
                                        }
                                    });
                                },
                                processing: function () {
                                    $("#file-save-button").prop("disabled", true);
                                },
                                queuecomplete: function () {
                                    $("#file-save-button").prop("disabled", false);
                                },
                                fallback: function () {
                                    $("body").addClass("dropzone-disabled");
                                    $('.modal-dialog').find('[type="submit"]').removeAttr('disabled');

                                    $("#comments_file-dropzone").hide();

                                    $("#file-modal-footer").prepend("<button id='add-more-file-button' type='button' class='btn  btn-default pull-left'><i class='fa fa-plus-circle'></i> " + "<?php echo lang("add_more"); ?>" + "</button>");

                                    $("#file-modal-footer").on("click", "#add-more-file-button", function () {
                                        var newFileRow = "<div class='file-row pb pt10 b-b mb10'>" +
                                            "<div class='pb clearfix '><button type='button' class='btn btn-xs btn-danger pull-left mr remove-file'><i class='fa fa-times'></i></button> <input class='pull-left' type='file' name='manualFiles[]' /></div>" +
                                            "<div class='mb5 pb5'><input class='form-control description-field cursor-auto'  name='comment[]'  type='text' placeholder='<?php echo lang("comment") ?>' /></div>" +
                                            "</div>";
                                        $("#comments_file-previews").prepend(newFileRow);
                                    });
                                    $("#add-more-file-button").trigger("click");
                                    $("#comments_file-previews").on("click", ".remove-file", function () {
                                        $(this).closest(".file-row").remove();
                                    });
                                },
                                success: function (file) {
                                    setTimeout(function () {
                                        $(file.previewElement).find(".progress-striped").removeClass("progress-striped").addClass("progress-bar-success");
                                    }, 1000);
                                }
                            });

                        })
                    </script>
                </div>
            </div><!-- /.box-body -->
            <div class="box-footer">
                <div class="pull-right">
                    <button name="draf" value="1" class="btn btn-default"><i
                                class="fa fa-pencil"></i> <?= lang('draft') ?></button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-envelope-o"></i> <?= lang('send') ?>
                    </button>
                </div>
                <button onclick="return confirm('<?= lang('discard_msg') ?>')" class="btn btn-default" name="discard"
                        value="2" data-toggle="tooltip" data-placement="top" title="Close"><i class="fa fa-times"></i>
                    Discard
                </button>
            </div>
        </div><!-- /. box -->
    </div><!-- /.col -->
</form>
