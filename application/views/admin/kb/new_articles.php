<?php
$this->load->view('kb_shared/_kb_assets');
$created = can_action('143', 'created');
$edited = can_action('143', 'edited');
$deleted = can_action('143', 'deleted');
if (!empty($created) || !empty($edited)) {
    // Dropzone's stock .dropzone rules load AFTER kb_modern.css (footer), so the kb
    // chrome is re-asserted below at higher specificity where the two conflict.
    $dz_message = '<div class="kb-dz-ic">📤</div><b>' . lang('kb_drag_drop_files') . '</b><p>' . lang('kb_browse_files') . '</p>';
    $status_active = (!empty($articles_info->status) && $articles_info->status == '1' || empty($articles_info));
    $status_inactive = (!empty($articles_info->status) && $articles_info->status == '2');
    $for_all_on = (!empty($articles_info->for_all) && $articles_info->for_all == 'Yes');
    ?>
    <style>
        /* ---------- Scoped chrome: outrank stock plugin CSS that loads later ---------- */
        .kb-dropzone.dropzone {
            border: 2px dashed var(--c-border-strong);
            background: var(--c-surface-2);
            padding: var(--sp-7);
        }

        .kb-dropzone.dropzone:hover {
            border-color: var(--c-accent);
            background: var(--c-accent-subtle);
        }

        .kb-dropzone.dropzone .dz-message { margin: 0; }

        /* Summernote renders itself inside .kb-editor — let the kb card frame it. */
        .kb-editor .note-editor {
            border: none;
            margin: 0;
            background: var(--c-surface);
        }

        .kb-editor .note-editable { background: var(--c-surface); }

        .kb-form .kb-head-acts {
            display: flex;
            gap: var(--sp-2);
            flex-wrap: wrap;
        }

        .kb-form .kb-fc-head .kb-btn {
            padding: 7px 12px;
            font-size: var(--fs-sm);
            box-shadow: none;
        }

        .kb-form .kb-fc-head .kb-del-link {
            color: var(--c-danger);
            border-color: var(--c-danger);
        }

        .kb-form .kb-fc-head .kb-del-link:hover {
            color: var(--c-danger);
            background: var(--c-surface-2);
        }

        .kb-file-chip .kb-file-name {
            max-width: 240px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .kb-file-chip .upload-thumbnail-sm {
            width: 26px;
            height: 26px;
            object-fit: cover;
            border-radius: var(--r-sm);
            background: none;
        }
    </style>
    <div class="kb-root">
        <div class="kb-crumb">
            <a href="<?= base_url('admin/knowledgebase') ?>"><?= lang('knowledgebase') ?></a>
            <span class="kb-sep">/</span>
            <a href="<?= base_url('admin/knowledgebase/articles') ?>"><?= lang('articles') ?></a>
            <span class="kb-sep">/</span>
            <b><?= lang('kb_new_article') ?></b>
        </div>
        <div class="kb-form">
            <div class="kb-fc-head">
                <h2><?= lang('kb_new_article') ?></h2>
                <?php
                if (!empty($articles_info->kb_id)) { ?>
                    <div class="kb-head-acts">
                        <a target="_blank"
                           href="<?= base_url() ?>admin/knowledgebase/details/articles/<?= $articles_info->kb_id ?>"
                           class="kb-btn kb-btn-ghost">
                            <span>👁</span> <?= lang('view_details') ?>
                        </a>
                        <a href="<?= base_url() ?>admin/knowledgebase/new_articles" class="kb-btn kb-btn-ghost">
                            <?= lang('new') ?> <?= lang('articles') ?>
                        </a>
                        <?php if (!empty($deleted)) { ?>
                            <a href="<?= base_url() ?>admin/knowledgebase/delete_articles/<?= $articles_info->kb_id ?>/1"
                               class="kb-btn kb-btn-ghost kb-del-link">
                                <?= lang('delete') ?> <?= lang('articles') ?>
                            </a>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
            <form id="form_validation" data-parsley-validate="" novalidate="" enctype="multipart/form-data"
                  action="<?php echo base_url() ?>admin/knowledgebase/saved_articles/<?php
                  if (!empty($articles_info->kb_id)) {
                      echo $articles_info->kb_id;
                  }
                  ?>" method="post">
                <div class="kb-form-body">
                    <div class="kb-frow">
                        <div class="kb-flabel"><?= lang('title') ?> <span
                                class="kb-req">*</span></div>
                        <div class="kb-fcell">
                            <input type="text" name="title" required id="title" class="kb-fin gen_slug" value="<?php
                            if (!empty($articles_info->title)) {
                                echo $articles_info->title;
                            }
                            ?>"/>
                        </div>
                    </div>
                    <div class="kb-frow">
                        <div class="kb-flabel"><?= lang('slug') ?> <span
                                class="kb-req">*</span></div>
                        <div class="kb-fcell">
                            <div class="kb-slug-wrap">
                                <span class="kb-prefix">/kb/</span>
                                <input type="text" name="slug" required id="slug" class="kb-fin" value="<?php
                                if (!empty($articles_info->slug)) {
                                    echo $articles_info->slug;
                                }
                                ?>"/>
                            </div>
                        </div>
                    </div>

                    <div class="kb-frow">
                        <div class="kb-flabel"><?= lang('categories') ?> <span
                                class="kb-req">*</span>
                        </div>
                        <div class="kb-fcell">
                            <div class="kb-cat-select-row">
                                <div class="kb-select-wrap">
                                    <select class="kb-fin" name="kb_category_id" required>
                                        <option value=""><?= lang('select') . ' ' . lang('categories') ?></option>
                                        <?php
                                        $kb_category_info = get_result('tbl_kb_category', array('status' => 1));
                                        if (!empty($kb_category_info)) {
                                            $kb_category_info = array_reverse($kb_category_info);
                                            foreach ($kb_category_info as $v_kb_category) {
                                                ?>
                                                <option value="<?= $v_kb_category->kb_category_id ?>"
                                                    <?php
                                                    if (!empty($articles_info)) {
                                                        echo $articles_info->kb_category_id == $v_kb_category->kb_category_id ? 'selected' : '';
                                                    }
                                                    ?>
                                                ><?= $v_kb_category->category ?></option>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <a class="kb-add-inline" title="<?= lang('kb_new_category') ?>"
                                   data-toggle="modal" data-target="#myModal"
                                   href="<?= base_url() ?>admin/knowledgebase/new_categories/inline"><i
                                        class="fa fa-plus"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="kb-frow">
                        <div class="kb-flabel"><?= lang('status') ?></div>
                        <div class="kb-fcell">
                            <div class="kb-radio-pills">
                                <label class="<?= $status_active ? 'on' : '' ?>">
                                    <input type="radio" name="status" value="1"
                                        <?= $status_active ? 'checked' : '' ?>> <?= lang('active') ?>
                                </label>
                                <label class="<?= $status_inactive ? 'on' : '' ?>">
                                    <input type="radio" name="status" value="2"
                                        <?= $status_inactive ? 'checked' : '' ?>> <?= lang('inactive') ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="kb-frow">
                        <div class="kb-flabel"><?= lang('kb_visibility') ?></div>
                        <div class="kb-fcell">
                            <label class="kb-switch">
                                <input <?= $for_all_on ? 'checked' : ''; ?> type="checkbox" name="for_all"
                                     value="Yes" hidden>
                                <span class="kb-toggle <?= $for_all_on ? 'on' : ''; ?>"></span>
                                <span><?= lang('kb_show_clients') ?></span>
                            </label>
                        </div>
                    </div>

                    <div class="kb-frow">
                        <div class="kb-flabel"><?= lang('description') ?></div>
                        <div class="kb-fcell">
                            <div class="kb-editor">
                                <textarea class="form-control textarea_lg"
                                          name="description"><?php if (!empty($articles_info->description)) {
                                    echo $articles_info->description;
                                } ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="kb-frow">
                        <div class="kb-flabel"><?= lang('kb_attachments') ?></div>

                        <div class="kb-fcell">
                            <div id="comments_file-dropzone" class="kb-dropzone dropzone">

                            </div>
                            <div id="comments_file-dropzone-scrollbar">
                                <div id="comments_file-previews">
                                    <div id="file-upload-row" class="mt pull-left">

                                        <div class="preview box-content pr-lg" style="width:100px;">
                                                        <span data-dz-remove class="pull-right" style="cursor: pointer">
                                            <i class="fa fa-times"></i>
                                        </span>
                                            <img data-dz-thumbnail class="upload-thumbnail-sm"/>
                                            <input class="file-count-field" type="hidden" name="files[]"
                                                   value=""/>
                                            <div
                                                class="mb progress progress-striped upload-progress-sm active mt-sm"
                                                role="progressbar" aria-valuemin="0" aria-valuemax="100"
                                                aria-valuenow="0">
                                                <div class="progress-bar progress-bar-success"
                                                     style="width:0%;"
                                                     data-dz-uploadprogress></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                            if (!empty($articles_info->attachments)) {
                                $uploaded_file = json_decode($articles_info->attachments);
                            }
                            if (!empty($uploaded_file)) {
                                foreach ($uploaded_file as $v_files_image) { ?>
                                    <div class="kb-file-chip">
                                        <?php if ($v_files_image->is_image == 1) { ?>
                                            <img data-dz-thumbnail
                                                 src="<?php echo base_url() . $v_files_image->path ?>"
                                                 class="upload-thumbnail-sm"/>
                                        <?php } else { ?>
                                            <span data-toggle="tooltip" data-placement="top"
                                                  title="<?= $v_files_image->fileName ?>"
                                                  class="kb-fi mailbox-attachment-icon"><i
                                                    class="fa fa-file-text-o"></i></span>
                                        <?php } ?>
                                        <span class="kb-file-name"><?= $v_files_image->fileName ?></span>
                                        <span class="kb-x existing_image"><i class="fa fa-times"></i></span>

                                        <input type="hidden" name="path[]"
                                               value="<?php echo $v_files_image->path ?>">
                                        <input type="hidden" name="fileName[]"
                                               value="<?php echo $v_files_image->fileName ?>">
                                        <input type="hidden" name="fullPath[]"
                                               value="<?php echo $v_files_image->fullPath ?>">
                                        <input type="hidden" name="size[]"
                                               value="<?php echo $v_files_image->size ?>">
                                        <input type="hidden" name="is_image[]"
                                               value="<?php echo $v_files_image->is_image ?>">
                                    </div>
                                <?php }; ?>
                            <?php }; ?>
                            <script type="text/javascript">
                                $(document).ready(function () {
                                    function getSlug(title) {
                                        var slug_url = '<?= base_url('admin/knowledgebase/getSlug')?>';
                                        $.get(slug_url, {title: title}, function (slug) {
                                            $('#slug').val(slug).change();
                                        });
                                    }

                                    $('.gen_slug').change(function (e) {
                                        getSlug($(this).val());
                                    });

                                    $('input[name="status"]').on("change", function () {
                                        $(this).closest("label").addClass("on").siblings("label").removeClass("on");
                                    });

                                    $('.kb-switch input[name="for_all"]').on("change", function () {
                                        $(this).siblings(".kb-toggle").toggleClass("on", this.checked);
                                    });

                                    $(".existing_image").on("click", function(){
                                        $(this).parent().remove();
                                    });

                                    var fileSerial = 0;
                                    // Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
                                    var previewNode = document.querySelector("#file-upload-row");
                                    previewNode.id = "";
                                    var previewTemplate = previewNode.parentNode.innerHTML;
                                    previewNode.parentNode.removeChild(previewNode);
                                    Dropzone.autoDiscover = false;
                                    var projectFilesDropzone = new Dropzone("#comments_file-dropzone", {
                                        url: "<?= base_url()?>admin/common/upload_file",
                                        thumbnailWidth: 80,
                                        thumbnailHeight: 80,
                                        parallelUploads: 20,
                                        previewTemplate: previewTemplate,
                                        dictDefaultMessage: <?= json_encode($dz_message) ?>,
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
                                                url: "<?= base_url()?>admin/common/validate_project_file",
                                                data: {file_name: file.name, file_size: file.size},
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

                                            $("#file-modal-footer").prepend("<button id='add-more-file-button' type='button' class='btn  btn-default pull-left mr remove-file'><i class='fa fa-plus-circle'></i> " + "<?php echo lang("add_more"); ?>" + "</button>");

                                            $("#file-modal-footer").on("click", "#add-more-file-button", function () {
                                                var newFileRow = "<div class='file-row pb pt10 b-b mb10'>"
                                                    + "<div class='pb clearfix '><button type='button' class='btn btn-xs btn-danger pull-left mr remove-file'><i class='fa fa-times'></i></button> <input class='pull-left' type='file' name='manualFiles[]' /></div>"
                                                    + "<div class='mb5 pb5'><input class='form-control description-field'  name='comment[]'  type='text' style='cursor: auto;' placeholder='<?php echo lang("comment") ?>' /></div>"
                                                    + "</div>";
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
                    </div>
                </div>
                <div class="kb-form-foot">
                    <a href="<?= base_url('admin/knowledgebase/articles') ?>"
                       class="kb-btn kb-btn-ghost"><?= lang('cancel') ?></a>
                    <button type="submit" id="file-save-button" class="kb-btn kb-btn-primary"><span>💾</span>
                        <?= lang('kb_save_article') ?></button>
                </div>
            </form>
        </div>
    </div>
<?php } ?>
