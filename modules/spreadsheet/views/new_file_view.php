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
    
    
    <div class="upload_file_class">
        <a href="#" id="file-link" class="btn btn-info"><i class="fa fa-upload" aria-hidden="true"></i> Upload</a>
        <input type="file" id="Luckyexcel-demo-file" name="Luckyexcel-demo-file" change="demoHandler">
        
        <?php if (isset($id)) { ?>
            <a href="#ShareModal" data-toggle="modal" class="btn add_share_button btn-info new_file_margin">
                <i class="fa fa-share-square-o"></i> <?php echo lang('share'); ?>
            </a>
            
            <a href="#RelatedModal" data-toggle="modal" class="btn add_related_button btn-info new_file_margin">
                <i class="fa fa-paw"></i> <?php echo lang('related'); ?>
            </a>
        <?php } ?>
    </div>
    
    <div class="col-sm-12">
        <?php echo form_open_multipart(base_url() . 'admin/spreadsheet/new_file_view/' . $parent_id, array('id' => 'spreadsheet-test-form')); ?>
        
        <div id="luckysheet"></div>
        
        <?php echo form_hidden('name'); ?>
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
                    <label for="folder" class="control-label"><?php echo lang('folder') ?></label>
                    <input type="text" id="folder" name="folder" class="form-control"
                           placeholder="<?php echo lang('enter_your') . ' ' . lang('folder'); ?>" autocomplete="off">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default"
                            data-dismiss="modal"><?php echo lang('close'); ?></button>
                    <button type="submit" class="btn btn-info"><?php echo lang('submit'); ?></button>
                </div>
            </div>
        </div>
    </div>


<?php
$this->load->view('RelatedModal');
$rata['staffs'] = $staffs;
$rata['clients'] = $clients;
$this->load->view('shareModal', $rata);
spreadsheet_load_js();
?>
<?php require 'modules/' . SPREADSHEET_MODULE . '/assets/js/new_file_js.php'; ?>