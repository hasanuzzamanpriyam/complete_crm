<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/plugins/dropzone/dropzone.min.css">
<script type="text/javascript" src="<?= base_url() ?>assets/plugins/dropzone/dropzone.min.js"></script>
<script type="text/javascript" src="<?= base_url() ?>assets/plugins/dropzone/dropzone.custom.js"></script>
<style>
    .pointer {
        cursor: pointer !important;
    }

    .cursor-auto {
        cursor: auto !important;
    }

    .wd-100 {
        width: 100px !important;
    }
</style>
<div class="panel panel-custom">
    <div class="panel-heading">
        <h3 class="panel-title"><?= lang('comments') ?></h3>
    </div>
    <div class="panel-body chat" id="chat-box">
        
        <form action="<?= base_url('admin/' . $module . '/save_comments'); ?>" id="leads-comment-form"
              class="form-horizontal general-form" enctype="multipart/form-data" role="form" method="post"
              accept-charset="utf-8" novalidate="novalidate">
            
            <input type="hidden" name="module" value="<?= $module ?>" class="form-control">
            <input type="hidden" name="module_field_id" value="<?= $module_field_id ?>" class="form-control">
            
            <div class="form-group">
                <div class="col-sm-12">
                    <textarea name="comment" cols="40" rows="4" id="comment_description"
                              class="form-control comment_description" placeholder="<?= $subject ?>-Comments"
                              data-rule-required="1" data-msg-required="field_required" aria-required="true"></textarea>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-12">
                    <div id="comments_file-dropzone" class="dropzone mb15 dz-clickable">
                        
                        <div class="dz-default dz-message"><span><strong> Drag files </strong> to upload <span
                                        class="block"> (or click)</span></span></div>
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
                                    <div class="mb progress progress-striped upload-progress-sm active mt-sm"
                                         role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                        <div class="progress-bar progress-bar-success w-0"
                                             data-dz-uploadprogress></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            
            </div>
            
            <div class="form-group">
                <div class="col-sm-12">
                    <div class="pull-right">
                        <button type="submit" id="file-save-button"
                                class="btn btn-primary"><?= lang('post_comment') ?></button>
                    </div>
                </div>
            </div>
            <hr/>
            <?php echo form_close();
            $comment_reply_type = 'complain-reply';
            ?>
            <?php $this->load->view('admin/leads/comments_list', array('comment_details' => $comment_details)) ?>
            <script type="text/javascript">
                // add use strict
                (function ($) {
                    "use strict";
                    $(".reply__").hide();
                    $("button.reply").on("click", function () {
                        var id = $(this).attr("id");
                        var sectionId = id.replace("reply__", "reply_");
                        $(".reply__").hide();
                        $("div#" + sectionId).fadeIn("fast");
                        $("div#" + sectionId).css("margin-top", "10" + "px");
                    });
                })(jQuery);
            </script>
            <script type="text/javascript">
                (function ($) {
                    "use strict";
                    $('#file-save-button').on('click', function (e) {
                        var ubtn = $(this);
                        ubtn.html('Please wait...');
                        ubtn.addClass('disabled');
                    });
                    $("#leads-comment-form").appForm({
                        isModal: false,
                        onSuccess: function (result) {
                            $(".comment_description").val("");
                            $(".dz-complete").remove();
                            $('#file-save-button').removeClass("disabled").html('Post Comment');
                            $(result.data).insertAfter("#leads-comment-form");
                            toastr[result.status](result.message);
                        }
                    });
                    var fileSerial = 0;
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
                        dictDefaultMessage: '<strong> Drag files </strong> to upload <span class="block"> (or click)</span>',
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

                            $("#file-modal-footer").prepend("<button id='add-more-file-button' type='button' class='btn  btn-default pull-left'><i class='fa fa-plus-circle'></i> " + "Add More" + "</button>");

                            $("#file-modal-footer").on("click", "#add-more-file-button", function () {
                                var newFileRow = "<div class='file-row pb pt10 b-b mb10'>" +
                                    "<div class='pb clearfix '><button type='button' class='btn btn-xs btn-danger pull-left mr remove-file'><i class='fa fa-times'></i></button> <input class='pull-left' type='file' name='manualFiles[]' /></div>" +
                                    "<div class='mb5 pb5'><input class='form-control description-field cursor-auto'  name='comment[]'  type='text'  placeholder='Comment' /></div>" +
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