<script type="text/javascript">
    (function () {
        "use strict";

        $('#luckysheet').parents('.container').css({
            'width': 'unset',
            'padding': '0'
        });
        if ((<?php echo $tree_save ?>).length > 0) {
            var tree = $('input[name="folder"]').comboTree({
                source: <?php echo $tree_save ?>
            });
        }

        $('input[name="folder"]').on('change', function () {
            var id = tree.getSelectedItemsId();
            $("input[name='parent_id']").val(id.replace(/^\D+/g, ''));
        })
        if ((<?php echo isset($data_form) ? "true" : "false" ?>)) {
            var data = <?php echo isset($data_form) ? ($data_form != "" ? $data_form : '""') : '""' ?>;
            var dataSheet = data;
            var title = "<?php echo isset($file_excel) ? $file_excel->name : "" ?>";
        } else {
            var dataSheet = [{
                name: "Sheet1",
                status: "1",
                order: "0",
                data: [],
                config: {},
                index: 0
            }, {
                name: "Sheet2",
                status: "0",
                order: "1",
                data: [],
                config: {},
                index: 1
            }, {
                name: "Sheet3",
                status: "0",
                order: "2",
                data: [],
                config: {},
                index: 2
            }];

            var title = "Spreadsheet Online New";
        }
        var options = {
            container: 'luckysheet',
            lang: 'en',
            allowEdit: true,
            forceCalculation: true,
            plugins: ['chart'],
            data: dataSheet,
            title: title

        }
        luckysheet.create(options);

        var type_screen = $("input[name='type']").val();
        var role = $("input[name='role']").val();

        if (type_screen == 3) {
            $('.luckysheet_info_detail_save_as').remove();
        }
        if (role == 1) {
            $('.luckysheet_info_detail_save_as').remove();
            $('.luckysheet_info_detail_save').remove();
        }

    })(jQuery);

// ---- Spreadsheet editor command bar actions ----
function openShareModal() {
    if (!$('#ShareModal').length) { return; }
    $('.add_share_button').first().trigger('click');
}

function openRelatedModal() {
    if (!$('#RelatedModal').length) { return; }
    $('.add_related_button').first().trigger('click');
}

function triggerFilePicker() {
    var fi = document.getElementById('Luckyexcel-demo-file');
    if (fi) { fi.click(); }
}

document.addEventListener('DOMContentLoaded', function () {
    var splitBtn = document.querySelector('.split-btn');
    var splitMenu = document.getElementById('split-menu');
    if (splitBtn && splitMenu) {
        splitBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            splitMenu.classList.toggle('show');
        });
        document.addEventListener('click', function (e) {
            if (!splitBtn.contains(e.target) && !splitMenu.contains(e.target)) {
                splitMenu.classList.remove('show');
            }
        });
        splitMenu.addEventListener('click', function (e) {
            e.stopPropagation();
        });
        Array.prototype.forEach.call(splitMenu.querySelectorAll('.split-item'), function (item) {
            item.addEventListener('click', function () {
                splitMenu.classList.remove('show');
            });
        });
    }

    var saveBtn = document.getElementById('save-btn');
    var saveForm = document.getElementById('spreadsheet-test-form');
    if (saveBtn && saveForm && typeof luckysheet !== 'undefined') {
        saveBtn.addEventListener('click', function () {
            var rawData = luckysheet.getLuckysheetfile();
            var image_flag = 'false';
            for (var i = 0; i < rawData.length; i++) {
                var rawCelldata = luckysheet.transToCellData(rawData[i].data);
                rawData[i].celldata = rawCelldata;
                if (rawData[i].images != undefined) {
                    if (image_flag == 'false') { image_flag = 'true'; }
                    if (Object.keys(rawData[i].images).length > 0) {
                        $.each(rawData[i].images, function (key, value) {
                            rawData[i].images[key].src = 'imga$imga' + rawData[i].images[key].src + 'imga$imga';
                        });
                    }
                }
            }
            var finalData = JSON.stringify(rawData);
            var formData = new FormData(saveForm);
            var nameInput = document.getElementById('luckysheet_info_detail_input');
            var name = nameInput ? nameInput.value : (document.getElementById('file-name') ? document.getElementById('file-name').textContent.trim() : '');
            formData.append('data_form', finalData);
            formData.append('name', name);
            formData.append('id', $("input[name='id']").val());
            formData.append('image_flag', image_flag);
            $.ajax({
                url: $(saveForm).attr('action'),
                type: 'POST',
                data: formData,
                contentType: false,
                success: function (response, status, xhr) {
                    try { response = JSON.parse(response); } catch (err) {
                        toastr['error']('Save failed: unexpected server response.');
                        return;
                    }
                    if (response.success == true) {
                        toastr['success'](response.message);
                        var badge = document.getElementById('saved-badge');
                        if (badge) { badge.style.display = 'inline-block'; }
                        if (response.id && saveForm.querySelector('[name="id"]')) {
                            saveForm.querySelector('[name="id"]').value = response.id;
                        }
                        if (response.name_excel) {
                            var fname = document.getElementById('file-name');
                            if (fname) { fname.textContent = response.name_excel; }
                        }
                    } else {
                        toastr['warning'](response.message);
                    }
                },
                error: function () {
                    toastr['error']('Save failed. Please try again.');
                },
                cache: false,
                processData: false
            });
        });
    }
});
</script>