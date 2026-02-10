<?php include_once 'assets/admin-ajax.php'; ?>
<style>
    .select_box {
        width: 100% !important;
    }

    .wd-100 {
        width: 100px !important;
    }

    .pointer {
        cursor: pointer !important;
    }

    .cursor-auto {
        cursor: auto !important;
    }
</style>
<form method="post" data-parsley-validate="" novalidate="" id="new_form" action="" enctype="multipart/form-data"
      class="form-horizontal">
    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('complaint_code') ?> <span class="text-danger">*</span></label>
        <div class="col-lg-5">
            <input type="text" class="form-control" value="<?php
            $this->load->helper('string');
            if (!empty($tickets_info)) {
                echo $tickets_info->ticket_code;
            } else {
                echo strtoupper(random_string('alnum', 7));
            }
            ?>" name="ticket_code">
        </div>
    </div>
    <?php $projects = $this->uri->segment(4);
    if ($projects != 'project_tickets') {
        ?>
        <input type="hidden" value="<?php echo $this->uri->segment(3) ?>" class="form-control" name="status">
    <?php } ?>
    
    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('description') ?> <span class="text-danger">*</span></label>
        <div class="col-lg-5">
            <textarea name="subject" class="form-control" placeholder="<?= lang('complaint_subject') ?>"
                      required><?php if (!empty($tickets_info)) {
                    echo $tickets_info->subject;
                } ?></textarea></div>
    </div>
    
    <div class="form-group">
        <label for="client" class="col-lg-3 control-label"><?= lang('client') ?> <span
                    class="text-danger">*</span></label>
        <div class="col-lg-5">
            <div class="input-group">
                <select name="client" id="client" class="form-control select_box" required="">
                    <option value=""><?= lang('select_client') ?></option>
                    <?php
                    $all_client = $this->db->get('tbl_client')->result();
                    if (!empty($all_client)) {
                        foreach ($all_client as $v_client) {
                            ?>
                            <option value="<?= $v_client->client_id ?>" <?php
                            if (!empty($tickets_info) && $tickets_info->client == $v_client->client_id) {
                                echo 'selected';
                            } else if (!empty($client_id) && $client_id == $v_client->client_id) {
                                echo 'selected';
                            }
                            ?>><?= $v_client->name ?></option>
                            <?php
                        }
                    }
                    $acreated = can_action('4', 'created');
                    ?>
                </select>
                <?php if (!empty($acreated)) { ?>
                    <div class="input-group-addon" title="<?= lang('new') . ' ' . lang('client') ?>"
                         data-toggle="tooltip" data-placement="top">
                        <a data-toggle="modal" data-target="#myModal" href="<?= base_url() ?>admin/client/new_client"><i
                                    class="fa fa-plus"></i></a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('type') ?> <span class="text-danger">*</span>
        </label>
        <div class="col-lg-5">
            <div class=" ">
                <select name="ticket_sub_type" class="form-control select_box" required>
                    <option value=""><?= lang('select') . ' ' . lang('complaint_type') ?></option>
                    <?php
                    $complaints_types = $this->db->get('tbl_complaints_types')->result();
                    if (!empty($complaints_types)) {
                        foreach ($complaints_types as $valu) :
                            ?>
                            <option value="<?= $valu->id ?>" <?php
                            if (!empty($tickets_info) && $tickets_info->ticket_sub_type == $valu->id) {
                                echo 'selected';
                            }
                            ?>><?= ($valu->name) ?></option>
                        <?php
                        endforeach;
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>
    
    
    <div class="form-group">
        <label for="lodged_date" class="col-lg-3 control-label"><?= lang('complaint') . ' ' . lang('date') ?> <span
                    class="text-danger">*</span></label>
        <div class="col-lg-5">
            <div class="input-group">
                <input required type="text" name="lodged_date" id="lodged_date" class="form-control datepicker"
                       value="<?= set_value('lodged_date', $lodged_date) ?>"
                       data-date-format="<?= config_item('date_picker_format'); ?>">
                <div class="input-group-addon">
                    <a href="#"><i class="fa fa-calendar"></i></a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label for="due_date" class="col-lg-3 control-label"><?= lang('due_date') ?> <span class="text-danger">*</span></label>
        <div class="col-lg-5">
            <div class="input-group">
                <input required type="text" name="due_date" id="due_date" class="form-control datepicker"
                       value="<?= set_value('due_date', $due_date) ?>"
                       data-date-format="<?= config_item('date_picker_format'); ?>">
                <div class="input-group-addon">
                    <a href="#"><i class="fa fa-calendar"></i></a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="form-group mb-0">
        <label for="field-1" class="col-sm-3 control-label"><?= lang('attachment') ?></label>
        
        <div class="col-sm-5">
            <div id="comments_file-dropzone" class="dropzone mb15">
            
            </div>
            <div id="comments_file-dropzone-scrollbar">
                <div id="comments_file-previews">
                    <div id="file-upload-row" class="mt pull-left">
                        
                        <div class="preview box-content pr-lg wd-100">
                            <span data-dz-remove class="pull-right pointer">
                                <i class="fa fa-times"></i>
                            </span>
                            <img data-dz-thumbnail class="upload-thumbnail-sm"/>
                            <input class="file-count-field" type="hidden" name="files[]" value=""/>
                            <div class="mb progress progress-striped upload-progress-sm active mt-sm" role="progressbar"
                                 aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                <div class="progress-bar progress-bar-success w-0"
                                     data-dz-uploadprogress></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            if (!empty($tickets_info->upload_file)) {
                $uploaded_file = json_decode($tickets_info->upload_file);
            }
            if (!empty($uploaded_file)) {
                foreach ($uploaded_file as $v_files_image) { ?>
                    <div class="pull-left mt pr-lg mb wd-100">
                        <span data-dz-remove class="pull-right existing_image pointer"><i
                                    class="fa fa-times"></i></span>
                        <?php if ($v_files_image->is_image == 1) { ?>
                            <img data-dz-thumbnail src="<?php echo base_url() . $v_files_image->path ?>"
                                 class="upload-thumbnail-sm"/>
                        <?php } else { ?>
                            <span data-toggle="tooltip" data-placement="top" title="<?= $v_files_image->fileName ?>"
                                  class="mailbox-attachment-icon"><i class="fa fa-file-text-o"></i></span>
                        <?php } ?>
        
                        <input type="hidden" name="path[]" value="<?php echo $v_files_image->path ?>">
                        <input type="hidden" name="fileName[]" value="<?php echo $v_files_image->fileName ?>">
                        <input type="hidden" name="fullPath[]" value="<?php echo $v_files_image->fullPath ?>">
                        <input type="hidden" name="size[]" value="<?php echo $v_files_image->size ?>">
                        <input type="hidden" name="is_image[]" value="<?php echo $v_files_image->is_image ?>">
                    </div>
                <?php }; ?>
            <?php }; ?>
            <script type="text/javascript">
                (function ($) {
                    "use strict";
                    $(".existing_image").on("click", function () {
                        $(this).parent().remove();
                    });

                    fileSerial = 0;
                    // Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
                    var previewNode = document.querySelector("#file-upload-row");
                    previewNode.id = "";
                    var previewTemplate = previewNode.parentNode.innerHTML;
                    previewNode.parentNode.removeChild(previewNode);
                    Dropzone.autoDiscover = false;
                    var projectFilesDropzone = new Dropzone("#comments_file-dropzone", {
                        url: "<?= base_url() ?>admin/common/upload_file",
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
                            //validate the file
                            $.ajax({
                                url: "<?= base_url() ?>admin/common/validate_project_file",
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
                            //add custom fallback;
                            $("body").addClass("dropzone-disabled");
                            $('.modal-dialog').find('[type="submit"]').removeAttr('disabled');

                            $("#comments_file-dropzone").hide();

                            $("#file-modal-footer").prepend("<button id='add-more-file-button' type='button' class='btn  btn-default pull-left'><i class='fa fa-plus-circle'></i> " + "<?php echo lang("add_more"); ?>" + "</button>");

                            $("#file-modal-footer").on("click", "#add-more-file-button", function () {
                                var newFileRow = "<div class='file-row pb pt10 b-b mb10'>" +
                                    "<div class='pb clearfix '><button type='button' class='btn btn-xs btn-danger pull-left mr remove-file'><i class='fa fa-times'></i></button> <input class='pull-left' type='file' name='manualFiles[]' /></div>" +
                                    "<div class='mb5 pb5'><input class='form-control description-field cursor-auto'  name='comment[]'  type='text'  placeholder='<?php echo lang("comment") ?>' /></div>" +
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
                })(jQuery);
            </script>
        </div>
    </div>
    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('procedure_to_solve') ?> </label>
        <div class="col-lg-7">
            <textarea name="body" class="form-control textarea_" placeholder="<?= lang('message') ?>"><?php
                if (!empty($tickets_info)) {
                    echo $tickets_info->body;
                } else {
                    echo set_value('body');
                }
                ?></textarea>
        
        </div>
    </div>
    
    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('status') ?> <span class="text-danger">*</span></label>
        <div class="col-lg-5">
            <select name="status" class="form-control select_box" required="">
                <option <?php
                if (!empty($tickets_info->status)) {
                    echo $tickets_info->status == 'open' ? 'selected' : null;
                } ?> value="open"><?= lang('open') ?></option>
                <option <?php
                if (!empty($tickets_info->status)) {
                    echo $tickets_info->status == 'waiting_for_someone' ? 'selected' : null;
                } ?> value="started"><?= lang('waiting_for_someone') ?></option>
                
                <option <?php
                if (!empty($tickets_info->status)) {
                    echo $tickets_info->status == 'in_progress' ? 'selected' : null;
                } ?> value="in_progress"><?= lang('in_progress') ?></option>
                <option <?php
                if (!empty($tickets_info->status)) {
                    echo $tickets_info->status == 'resolved' ? 'selected' : null;
                } ?> value="resolved"><?= lang('resolved') ?></option>
                <option <?php
                if (!empty($tickets_info->status)) {
                    echo $tickets_info->status == 'closed' ? 'selected' : null;
                } ?> value="closed"><?= lang('closed') ?></option>
            </select>
        </div>
    </div>
    <div class="btn-bottom-toolbar text-right">
        <?php
        if (!empty($tickets_info)) { ?>
            <button type="submit" id="file-save-button" class="btn btn-sm btn-primary"><?= lang('updates') ?></button>
            <button type="button" onclick="goBack()" class="btn btn-sm btn-danger"><?= lang('cancel') ?></button>
        <?php } else {
            ?>
            <button type="submit" id="file-save-button"
                    class="btn btn-sm btn-primary"><?= lang('save') . ' ' . lang('Complain') ?></button>
        <?php }
        ?>
    </div>
</form>