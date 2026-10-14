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
            <b class="file-name" id="file-name"><?php echo isset($file_excel) ? htmlspecialchars($file_excel->name, ENT_QUOTES) : ''; ?></b>
            <div class="file-path" id="file-path">
                <?php 
                $path = isset($file_path) ? $file_path : '';
                echo htmlspecialchars($path, ENT_QUOTES);
                ?>
            </div>
        </div>
        <div class="spacer"></div>
        <span class="cmd-btn saved" id="saved-badge">&#x2713; Saved</span>
        <?php if (isset($id)) { ?>
            <div class="cmd-btn share-btn" onclick="openShareModal();">
                &#x1F517; Share
            </div>
            <div class="cmd-btn related-btn" onclick="openRelatedModal();">
                &#x1F4C4; Related
            </div>
        <?php } ?>
        <div class="cmd-btn split-btn">
            <span class="split-text">Download</span>
            <span class="split-arrow">&#x25BE;</span>
            <div class="split-menu" id="split-menu">
                <div class="split-item" onclick="triggerFilePicker();">
                    Replace file
                </div>
            </div>
        </div>
        <div class="cmd-btn save-btn" id="save-btn">
            &#x1F4BE; Save
        </div>
        <input type="file" id="Luckyexcel-demo-file" name="Luckyexcel-demo-file" style="display:none">
    </div>
    
    <div class="col-sm-12">
        <?php echo form_open_multipart(base_url() . 'admin/spreadsheet/new_file_view/' . $parent_id, array('id' => 'spreadsheet-test-form')); ?>
        
        <div class="ss-frame">
            <div class="ss-toolbar">
                <div class="btn btn-undo" onclick="luckysheet.undo();">&#x21A9;</div>
                <div class="btn btn-redo" onclick="luckysheet.redo();">&#x21AA;</div>
                <div class="sep"></div>
                <div class="btn btn-bold">B</div>
                <div class="btn btn-italic">I</div>
                <div class="btn btn-underline">U</div>
                <div class="sep"></div>
                <div class="dropdown">
                    Automatic
                    <div class="dropdown-menu">
                        <div class="dropdown-item">Automatic</div>
                        <div class="dropdown-item">Manual</div>
                    </div>
                </div>
                <div class="dropdown">
                    Times N...
                    <div class="dropdown-menu">
                        <div class="dropdown-item">0</div>
                        <div class="dropdown-item">100</div>
                        <div class="dropdown-item">1000</div>
                    </div>
                </div>
                <div class="sep"></div>
                <div class="fx-container">
                    <input type="text" class="fx-input" id="fx-input" readonly>
                </div>
            </div>

            <div class="ss-formula">
                <div class="cell-ref" id="cell-ref">A1</div>
                <span></span>
                <div class="formula-preview" id="formula-preview"></div>
                <span></span>
                <div class="action-group" id="action-group"></div>
                <div class="flex-spacer"></div>
                <div class="cell-value" id="cell-value"></div>
            </div>

            <div id="luckysheet"></div>

            <div class="ss-footer">
                <div class="sheet-names" id="sheet-names">Sheet1</div>
                <div class="zoom" id="zoom">100%</div>
            </div>
        </div>
        
        <?php echo form_hidden('name'); ?>
        <?php echo form_hidden('parent_id', $parent_id); ?>
        <?php echo form_hidden('id', isset($id) ? $id : ""); ?>
        <?php echo form_close(); ?>
    </div>
    
    
    <!-- Hidden helpers: reuse manage.js pre-population handlers + Bootstrap modal trigger -->
    <a href="#ShareModal" data-toggle="modal" class="add_share_button" style="display:none"></a>
    <a href="#RelatedModal" data-toggle="modal" class="add_related_button" style="display:none"></a>

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