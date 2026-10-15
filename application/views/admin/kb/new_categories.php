<?php
$this->load->view('kb_shared/_kb_assets');
$created = can_action('142', 'created');
$edited = can_action('142', 'edited');
if (!empty($created) || !empty($edited)) {
    // Only an explicit status "2" reads as inactive — everything else (new
    // category, status 1/0/empty) starts on the Active side of the toggle,
    // matching the old Active/Inactive checkbox pair's save outcomes.
    $status_active = empty($category_info) || (string)$category_info->status !== '2';
    ?>
    <style>
        /* The Bootstrap shell that receives this modal is made transparent so the
           kb-modal card below is the only surface. Scoped to this modal only
           (:has ensures it applies while a kb-modal is loaded) — no !important. */
        #myModal .modal-content:has(.kb-modal) {
            background: transparent;
            border: 0;
            box-shadow: none;
            border-radius: 0;
        }

        .kb-modal {
            margin: 0 auto;
        }

        .kb-modal-head .kb-x {
            background: transparent;
            border: 0;
            padding: 0;
        }

        /* .kb-mrow label styles every label in the row — keep the switch label inline. */
        .kb-mrow label.kb-switch {
            display: inline-flex;
            font-weight: 400;
            margin-bottom: 0;
            cursor: pointer;
        }

        .kb-modal .kb-fin-sort {
            max-width: 140px;
        }

        .kb-modal-body .kb-mrow:last-child {
            margin-bottom: 0;
        }
    </style>
    <div class="kb-modal">
        <div class="kb-modal-head">
            <h2 id="myModalLabel"><?= lang('kb_new_category') ?></h2>
            <button type="button" class="kb-x" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                        class="sr-only">Close</span></button>
        </div>
        <form id="form_validation" data-parsley-validate="" novalidate="" enctype="multipart/form-data"
              action="<?php echo base_url() ?>admin/knowledgebase/saved_categories/<?php
              if (!empty($category_info->kb_category_id)) {
                  echo $category_info->kb_category_id;
              } elseif (!empty($inline)) {
                  echo $inline;
              }
              ?>" method="post">
            <div class="kb-modal-body">
                <div class="kb-mrow">
                    <label><?= lang('categories') ?> <span class="kb-req">*</span></label>
                    <input type="text" name="category" required class="kb-fin" value="<?php
                    if (!empty($category_info->category)) {
                        echo $category_info->category;
                    }
                    ?>"/>
                </div>
                <input type="hidden" name="type" required class="form-control" value="kb"/>
                <div class="kb-mrow">
                    <label><?= lang('description') ?></label>
                    <textarea class="kb-fin" name="description"><?php if (!empty($category_info->description)) {
                            echo $category_info->description;
                        } ?></textarea>
                </div>
                <div class="kb-mrow">
                    <label><?= lang('order') ?> <span class="kb-req">*</span></label>
                    <input type="text" name="sort" data-parsley-type="number" class="kb-fin kb-fin-sort" value="<?php
                    if (!empty($category_info->sort)) {
                        echo $category_info->sort;
                    }
                    ?>" required/>
                </div>
                <div class="kb-mrow">
                    <label><?= lang('status') ?></label>
                    <input type="hidden" name="status" value="2">
                    <label class="kb-switch">
                        <input <?= $status_active ? 'checked' : ''; ?> type="checkbox" name="status" value="1" hidden>
                        <span class="kb-toggle <?= $status_active ? 'on' : ''; ?>"></span>
                        <span><?= lang('active') ?></span>
                    </label>
                </div>
            </div>
            <div class="kb-modal-foot">
                <button type="button" class="kb-btn kb-btn-ghost" data-dismiss="modal"><?= lang('cancel') ?></button>
                <button type="submit" id="sbtn" class="kb-btn kb-btn-primary"><?= lang('kb_save_category') ?></button>
            </div>
        </form>
    </div>
    <script type="text/javascript">
        // Keep the switch knob in sync with the hidden status checkbox.
        $('.kb-modal .kb-switch input[name="status"]').on('change', function () {
            $(this).siblings('.kb-toggle').toggleClass('on', this.checked);
        });
    </script>
<?php }
if (!empty($inline)) { ?>
    <script type="text/javascript">
        $(document).on("submit", "form", function (event) {
            var form = $(event.target);
            if (form.attr('action') == '<?= base_url('admin/knowledgebase/saved_categories/' . $inline)?>') {
                event.preventDefault();

                $.ajax({
                    type: form.attr('method'),
                    url: form.attr('action'),
                    data: form.serialize()
                }).done(function (response) {
                    response = JSON.parse(response);
                    if (response.status == 'success') {
                        if (typeof (response.id) != 'undefined') {
                            var groups = $('select[name="kb_category_id"]');
                            groups.prepend('<option selected value="' + response.id + '">' + response.category + '</option>');
                            var select2Instance = groups.data('select2');
                            var resetOptions = select2Instance.options.options;
                            groups.select2('destroy').select2(resetOptions)
                        }
                        toastr[response.status](response.message);
                    }
                    $('#myModal').modal('hide');
                }).fail(function () {
                    alert('There was a problem with AJAX');
                });
            }
        });
    </script>
<?php }
?>
