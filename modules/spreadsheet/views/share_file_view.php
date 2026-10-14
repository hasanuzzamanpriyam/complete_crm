<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php spreadsheet_add_head_component(); ?>
<style>
    .luckysheet-wa-calculate {
        margin-left: 10px;
    }
</style>

<div class="ed-head-dark">
    <div class="cmd-btn back" onclick="window.history.back();">
        &#x2190;
    </div>
    <div class="file-info">
        <b class="file-name" id="file-name"><?php echo (isset($file_excel) && !empty($file_excel->name)) ? htmlspecialchars($file_excel->name, ENT_QUOTES) : lang('new_file'); ?></b>
        <div class="file-path" id="file-path">
            <?php
            $path = isset($file_path) ? $file_path : '';
            echo htmlspecialchars($path, ENT_QUOTES);
            ?>
        </div>
    </div>
    <div class="spacer"></div>
    <span class="cmd-btn saved" id="saved-badge">&#x2713; Saved</span>
    <div class="cmd-btn share-btn" onclick="openShareModal();">
        &#x1F517; Share
    </div>
    <div class="cmd-btn related-btn" onclick="openRelatedModal();">
        &#x1F4C4; Related
    </div>
    <div class="cmd-btn split-btn">
        <span class="split-text">View</span>
        <span class="split-arrow">&#x25BE;</span>
        <div class="split-menu" id="split-menu">
            <div class="split-item">Export</div>
        </div>
    </div>
    <!-- Save button hidden in view-only mode -->
    <!-- <div class="cmd-btn save-btn" id="save-btn" disabled>&#x1F4BE; Save</div> -->
</div>

<div class="col-sm-12">
    <?php echo form_hidden('type', 2); ?>
    <?php echo form_hidden('role', $role); ?>
    <?php echo form_open_multipart(admin_url('spreadsheet/file_view_share'), array('id' => 'spreadsheet-test-form')); ?>
    <div class="ss-frame">
        <div id="luckysheet"></div>
    </div>
    <?php echo form_hidden('parent_id', $parent_id); ?>
    <?php echo form_hidden('id', isset($id) ? $id : ""); ?>
    <?php echo form_close(); ?>
</div>


<div class="modal fade" id="SaveAsModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title add-new"><?php echo lang('save_as') ?></h4>
            </div>

            <div class="modal-body">
                <?php echo render_select('folder', $folder, array('id', 'name'), '', '', array()); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo lang('submit'); ?></button>
            </div>
        </div>
    </div>
</div>

<?php
spreadsheet_load_js();
require 'modules/' . SPREADSHEET_MODULE . '/assets/js/new_file_js.php';
?>
</body>

</html>
