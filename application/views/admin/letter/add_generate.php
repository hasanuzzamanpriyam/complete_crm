<link href="<?= base_url() ?>assets/plugins/summernote/summernote.min.css" rel="stylesheet">
<style>
    .letter-preview {
        border: 1px solid #ddd;
        padding: 20px;
        min-height: 500px;
        background: #fff;
        overflow: auto;
    }
    .margin-input {
        width: 70px;
        display: inline-block;
    }
    .margin-group label {
        display: block;
        font-size: 11px;
        color: #888;
        text-align: center;
    }
    .preview-header {
        border-bottom: 1px solid #e5e5e5;
        padding-bottom: 10px;
        margin-bottom: 15px;
    }
</style>

<?= message_box('success'); ?>
<?= message_box('error'); ?>

<div class="panel panel-custom">
    <header class="panel-heading ">
        <div class="panel-title">
            <strong>Generate Letter</strong>
        </div>
    </header>

    <div class="panel-body">
        <div class="row">
            <!-- LEFT COLUMN: FORM -->
            <div class="col-md-7">
                <?= form_open(base_url('admin/letter/save_generated/' . (!empty($letter_info) ? $letter_info->id : '')), array('id' => 'generate_letter_form', 'class' => 'form-horizontal', 'role' => 'form')); ?>

                <div class="form-group">
                    <label class="col-lg-3 control-label">Letter Type <span class="required">*</span></label>
                    <div class="col-lg-8">
                        <select name="template_id" id="template_id" class="form-control select_box" required>
                            <option value="">Select Template</option>
                            <?php if (!empty($templates)): ?>
                                <?php foreach ($templates as $tpl): ?>
                                    <option value="<?= $tpl->id ?>" <?= (!empty($letter_info) && $letter_info->template_id == $tpl->id) ? 'selected' : '' ?>>
                                        <?= $tpl->title ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-lg-3 control-label">Employee <span class="required">*</span></label>
                    <div class="col-lg-8">
                        <select name="employee_id" id="employee_id" class="form-control select_box" required>
                            <option value="">Select Employee</option>
                            <?php if (!empty($employees)): ?>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?= $emp->user_id ?>" <?= (!empty($letter_info) && $letter_info->employee_id == $emp->user_id) ? 'selected' : '' ?>>
                                        <?= $emp->fullname ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-lg-3 control-label">Employee Name</label>
                    <div class="col-lg-8">
                        <input type="text" id="employee_name_display" class="form-control" readonly
                               value="<?= !empty($letter_info) ? fullname($letter_info->employee_id) : '' ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-lg-3 control-label">Margin (px)</label>
                    <div class="col-lg-8">
                        <div class="row">
                            <div class="col-xs-3 margin-group">
                                <label>Top</label>
                                <input type="number" name="margin_top" class="form-control margin-input" value="<?= !empty($letter_info) ? $letter_info->margin_top : 20 ?>" min="0">
                            </div>
                            <div class="col-xs-3 margin-group">
                                <label>Bottom</label>
                                <input type="number" name="margin_bottom" class="form-control margin-input" value="<?= !empty($letter_info) ? $letter_info->margin_bottom : 20 ?>" min="0">
                            </div>
                            <div class="col-xs-3 margin-group">
                                <label>Left</label>
                                <input type="number" name="margin_left" class="form-control margin-input" value="<?= !empty($letter_info) ? $letter_info->margin_left : 20 ?>" min="0">
                            </div>
                            <div class="col-xs-3 margin-group">
                                <label>Right</label>
                                <input type="number" name="margin_right" class="form-control margin-input" value="<?= !empty($letter_info) ? $letter_info->margin_right : 20 ?>" min="0">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-lg-3 control-label">Description</label>
                    <div class="col-lg-8">
                        <textarea name="content" class="form-control textarea_lg" id="letter_content" rows="15"><?= !empty($letter_info) ? html_escape($letter_info->content) : '' ?></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-lg-3 control-label">Available Variables</label>
                    <div class="col-lg-8">
                        <div class="variables-list">
                            <?php
                            $variables = array(
                                '##CURRENT_DATE##', '##CURRENT_YEAR##',
                                '##EMPLOYEE_NAME##', '##EMPLOYEE_ID##', '##EMPLOYEE_ADDRESS##',
                                '##EMPLOYEE_PHONE##', '##JOINING_DATE##', '##DATE_OF_BIRTH##',
                                '##FATHER_NAME##', '##MOTHER_NAME##', '##GENDER##',
                                '##DESIGNATION##', '##DEPARTMENT##',
                                '##COMPANY_NAME##', '##COMPANY_ADDRESS##', '##COMPANY_PHONE##', '##COMPANY_EMAIL##',
                                '##CLIENT_NAME##', '##CLIENT_ADDRESS##',
                                '##PROJECT_NAME##', '##PROJECT_ID##',
                                '##TASK_NAME##', '##TASK_ID##',
                            );
                            foreach ($variables as $var) {
                                echo '<a href="#" class="insert-variable label label-primary" style="display:inline-block;margin:2px;cursor:pointer;font-size:11px;">' . $var . '</a> ';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-lg-offset-3 col-lg-8">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check"></i> Save Letter
                        </button>
                        <a href="<?= base_url('admin/letter/generate') ?>" class="btn btn-default">Cancel</a>
                    </div>
                </div>

                <?= form_close(); ?>
            </div>

            <!-- RIGHT COLUMN: PREVIEW -->
            <div class="col-md-5">
                <div class="panel panel-default">
                    <div class="panel-heading preview-header">
                        <div class="pull-right">
                            <a href="#" id="print_letter_btn" class="btn btn-xs btn-default" title="Print"><i class="fa fa-print"></i> Print</a>
                            <a href="#" id="preview_download_pdf" class="btn btn-xs btn-info" title="Download PDF"><i class="fa fa-download"></i> PDF</a>
                        </div>
                        <strong>Preview</strong>
                    </div>
                    <div class="panel-body" style="padding:0;">
                        <div class="letter-preview" id="letter_preview">
                            <p class="text-muted" style="padding:20px;">Select a template and employee to preview the letter...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var rawTemplate = '';
        var employeeData = null;
        var summernoteEditor = $('.textarea_lg');
        var companyLogoUrl = '<?= base_url() . config_item('company_logo') ?>';
        var currentLetterId = '<?= !empty($letter_info) ? $letter_info->id : '' ?>';

        // Initialize select2 on dropdowns
        if ($.fn.select2) {
            $('#template_id, #employee_id').select2();
        }

        // ---- Helper: Prepend company logo HTML (preview only) ----
        function prependLogo(html) {
            if (!html || !companyLogoUrl) return html;
            return '<div style="text-align:center;margin-bottom:20px;"><img src="' + companyLogoUrl + '" style="max-height:80px;width:auto;"></div>' + html;
        }

        // ---- Helper: Merge template placeholders with employee data ----
        function mergeAndPreview() {
            var merged = rawTemplate;

            if (!merged && summernoteEditor.length && summernoteEditor.summernote('code')) {
                merged = summernoteEditor.summernote('code');
            }

            if (!merged) {
                $('#letter_preview').html('<p class="text-muted" style="padding:20px;">Select a template and employee to preview the letter...</p>');
                return;
            }

            // If we have employee data, do string replacement
            if (employeeData && employeeData.variables) {
                $.each(employeeData.variables, function (key, value) {
                    var val = value || '';
                    var regex = new RegExp(key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g');
                    merged = merged.replace(regex, val);
                });
            }

            // Update summernote editor and sync to textarea
            $('.note-editable').html(merged);
            $('textarea[name="content"]').val(merged);

            // Update preview panel (with logo)
            $('#letter_preview').html(prependLogo(merged));
        }

        // ---- EVENT 1: Template dropdown change ----
        $('#template_id').on('change', function () {
            var templateId = $(this).val();
            if (!templateId) {
                rawTemplate = '';
                $('#letter_preview').html('<p class="text-muted" style="padding:20px;">Select a template and employee to preview the letter...</p>');
                if (summernoteEditor.length && summernoteEditor.summernote) {
                    summernoteEditor.summernote('code', '');
                }
                return;
            }

            $.ajax({
                type: 'POST',
                url: base_url + 'admin/letter/get_template_data',
                data: { template_id: templateId },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        rawTemplate = response.description;
                        mergeAndPreview();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function () {
                    toastr.error('Failed to load template.');
                }
            });
        });

        // ---- EVENT 2: Employee dropdown change ----
        $('#employee_id').on('change', function () {
            var employeeId = $(this).val();
            if (!employeeId) {
                employeeData = null;
                $('#employee_name_display').val('');
                $('#letter_preview').html('<p class="text-muted" style="padding:20px;">Select a template and employee to preview the letter...</p>');
                return;
            }

            $.ajax({
                type: 'POST',
                url: base_url + 'admin/letter/get_employee_data',
                data: { employee_id: employeeId },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        employeeData = response;
                        $('#employee_name_display').val(response.employee_name);
                        mergeAndPreview();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function () {
                    toastr.error('Failed to load employee data.');
                }
            });
        });

        // ---- EVENT 3: Insert variable into summernote ----
        $(document).on('click', '.insert-variable', function (e) {
            e.preventDefault();
            var variable = $(this).text();

            if (summernoteEditor.length && summernoteEditor.summernote) {
                var node = document.createElement('span');
                node.textContent = variable;
                summernoteEditor.summernote('insertNode', node);
            }
        });

        // ---- EVENT 4: Live preview sync from summernote ----
        if (summernoteEditor.length && summernoteEditor.summernote) {
            summernoteEditor.on('summernote.change', function (we, contents, $editable) {
                // When user types manually, merge with employee data
                var displayHtml = contents;
                if (employeeData && employeeData.variables) {
                    $.each(employeeData.variables, function (key, value) {
                        var val = value || '';
                        var regex = new RegExp(key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g');
                        displayHtml = displayHtml.replace(regex, val);
                    });
                }
                $('#letter_preview').html(prependLogo(displayHtml));
                $('textarea[name="content"]').val(contents);
            });
        }

        // ---- EVENT 5: Form submit via AJAX ----
        $('#generate_letter_form').on('submit', function (e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');

            // Sync summernote content into the textarea before serializing
            var $editable = $('.note-editable').first();
            if ($editable.length) {
                $('textarea[name="content"]').val($editable.html() || '');
            }

            $.ajax({
                type: 'POST',
                url: url,
                data: form.serialize(),
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        toastr.success(response.message);
                        if (typeof table !== 'undefined') {
                            table.ajax.reload(null, false);
                        } else if (typeof $('.DataTables').DataTable !== 'undefined') {
                            $('.DataTables').DataTable().ajax.reload(null, false);
                        }
                        window.location.href = base_url + 'admin/letter/generate';
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function () {
                    toastr.error('An error occurred while saving.');
                }
            });
        });

        // ---- EVENT 6: Print preview ----
        $('#print_letter_btn').on('click', function (e) {
            e.preventDefault();
            var printContent = $('#letter_preview').html();
            var printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>Print Letter</title>');
            printWindow.document.write('<link rel="stylesheet" href="<?= base_url() ?>assets/css/bootstrap.min.css">');
            printWindow.document.write('<style>body { padding: 20px; }</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(printContent);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.print();
        });

        // ---- EVENT 7: PDF download ----
        $('#preview_download_pdf').on('click', function (e) {
            e.preventDefault();
            if (currentLetterId) {
                window.location.href = base_url + 'admin/letter/download_pdf/' + currentLetterId;
            } else {
                toastr.error('Save the letter first before downloading PDF.');
            }
        });

        // ---- INITIALIZATION: Edit mode pre-load ----
        if (currentLetterId) {
            rawTemplate = $('#letter_content').val();
            var employeeId = $('#employee_id').val();
            if (employeeId) {
                $.ajax({
                    type: 'POST',
                    url: base_url + 'admin/letter/get_employee_data',
                    data: { employee_id: employeeId },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            employeeData = response;
                            $('#employee_name_display').val(response.employee_name);
                            mergeAndPreview();
                        }
                    }
                });
            } else {
                mergeAndPreview();
            }
        }
    });
</script>
