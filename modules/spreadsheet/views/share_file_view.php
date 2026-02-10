<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php spreadsheet_add_head_component(); ?>
<style>
    .luckysheet_info_detail {
        width: 82% !important;
    }

    .luckysheet-wa-calculate {
        margin-left: 10px;
    }
</style>

<div class="col-sm-12">
    <?php
    echo form_hidden('type', 2); ?>
    <?php echo form_hidden('role', $role); ?>
    <?php echo form_open_multipart(admin_url('spreadsheet/file_view_share'), array('id' => 'spreadsheet-test-form')); ?>
    <div id="luckysheet"></div>
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