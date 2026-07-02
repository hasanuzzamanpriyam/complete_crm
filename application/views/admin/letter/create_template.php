<div class="panel panel-custom">
    <header class="panel-heading ">
        <div class="panel-title">
            <strong><?= !empty($template_id) ? 'Edit Template' : 'New Template' ?></strong>
        </div>
    </header>

    <div class="panel-body">
        <div class="row">
            <div class="col-md-8">
                <?= form_open(base_url('admin/letter/save/' . $template_id), array('id' => 'letter_template_form', 'class' => 'form-horizontal', 'role' => 'form')); ?>

                <div class="form-group">
                    <label class="col-lg-3 control-label">Title <span class="required">*</span></label>
                    <div class="col-lg-8">
                        <input type="text" name="title" class="form-control" required
                               value="<?= !empty($template_info) ? $template_info->title : '' ?>"
                               placeholder="Enter template title">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-lg-3 control-label">Description</label>
                    <div class="col-lg-8">
                        <textarea name="description" class="form-control textarea" rows="10"><?= !empty($template_info) ? $template_info->description : '' ?></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-lg-offset-3 col-lg-8">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check"></i> Save
                        </button>
                        <a href="<?= base_url('admin/letter/templates') ?>" class="btn btn-default">Cancel</a>
                    </div>
                </div>

                <?= form_close(); ?>
            </div>

            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <strong>Available Variables</strong>
                        <div class="pull-right">
                            <a href="<?= base_url('admin/letter/variables') ?>" class="btn btn-xs btn-info" style="color:#fff;">
                                <i class="fa fa-cog"></i> Manage
                            </a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <p class="text-muted">Click a variable to insert it into the editor at cursor position:</p>
                        <div class="variables-list">
                            <?php if (!empty($variables)): ?>
                                <?php foreach ($variables as $var): ?>
                                    <a href="#" class="insert-variable label label-<?= $var->type === 'user' ? 'info' : 'primary' ?>" style="display:inline-block;margin:3px;cursor:pointer;" title="<?= htmlspecialchars($var->label) ?>">##<?= htmlspecialchars(strtoupper($var->name)) ?>##</a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No variables defined.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var $form = $('#letter_template_form');
        var $panel = $form.closest('.panel');

        $panel.find('.insert-variable').on('click', function (e) {
            e.preventDefault();
            var variable = $(this).text();
            var $editor = $form.find('textarea[name="description"]');
            if ($editor.length && $editor.hasClass('note-editor') === false) {
                try {
                    var node = document.createElement('span');
                    node.textContent = variable;
                    $editor.summernote('insertNode', node);
                } catch(err) {
                    // fallback: append to textarea value
                    $editor.val($editor.val() + variable);
                }
            }
        });

        $form.on('submit', function (e) {
            e.preventDefault();
            var url = $form.attr('action');

            // Sync summernote content into the hidden textarea before serializing
            var $editable = $form.find('.note-editable');
            if ($editable.length) {
                $form.find('textarea[name="description"]').val($editable.html() || '');
            }

            $.ajax({
                type: 'POST',
                url: url,
                data: $form.serialize(),
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        toastr.success(response.message);
                        // Close any open modal
                        $('#myModal').modal('hide');
                        // Reload DataTable if available, otherwise reload page
                        if (typeof reload_table === 'function') {
                            reload_table();
                        } else {
                            window.location.href = base_url + 'admin/letter/templates';
                        }
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function () {
                    toastr.error('An error occurred while saving the template.');
                }
            });
        });
    });
</script>
