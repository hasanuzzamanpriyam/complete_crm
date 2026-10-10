<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php spreadsheet_add_head_component(); ?>
<?php
$ss_active = ($tab == 'share_folder') ? 'share' : 'my';
$ss_root_label = ($ss_active == 'share') ? lang('share_folder') : lang('my_folder');
?>
<div class="ss-layout">
    <aside class="ss-panel" id="ss-panel" data-active="<?php echo $ss_active ?>">
        <div class="p-head"><b><?php echo lang('spreadsheet') ?></b><span class="badge" data-ss-total>0</span></div>
        <a class="f-item <?php echo ($ss_active == 'my') ? 'on' : ''; ?>"
           href="<?php echo admin_url('spreadsheet/manage/my_folder') ?>" data-root="my">
            <span class="ico fa fa-file-excel-o"></span><?php echo lang('my_folder'); ?><span class="cnt" data-ss-count="my"></span>
        </a>
        <div class="ss-subtree" data-subtree="my"></div>
        <a class="f-item <?php echo ($ss_active == 'share') ? 'on' : ''; ?>"
           href="<?php echo admin_url('spreadsheet/manage/share_folder') ?>" data-root="share">
            <span class="ico fa fa-share-square-o"></span><?php echo lang('share_folder'); ?><span class="cnt" data-ss-count="share"></span>
        </a>
        <div class="ss-subtree" data-subtree="share"></div>
        <div class="p-sep"></div>
        <div class="p-foot">
            <div class="row"><span><?php echo lang('files') ?></span><b data-ss-files>0</b></div>
            <div class="row"><span><?php echo lang('folders') ?></span><b data-ss-folders>0</b></div>
            <div class="row"><span><?php echo lang('shared') ?></span><b data-ss-shared>0</b></div>
        </div>
    </aside>
    <div class="ss-main">
        <div class="crumb" id="ss-crumb" data-root-label="<?php echo $ss_root_label ?>"></div>
        <div class="p ss-content">
            <?php $this->load->view('spreadsheet/' . $tab); ?>
        </div>
    </div>
</div>
    
    <div class="modal fade" id="setting-sent-notifications" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><?php echo lang('setting_notification') ?></h4>
                </div>
                <div class="modal-body">
                    <?php echo form_open_multipart(admin_url('spreadsheet/spreadsheet_setting'), array('id' => 'spreadsheet-online-setting-form')) ?>
                    <h4><?php echo lang('staff'); ?></h4>
                    <div class="wrapper">
                        <input id="spreadsheet_staff_notification" type="checkbox" name="spreadsheet_staff_notification"
                        <label for="spreadsheet_staff_notification"><?php echo lang('notifications') ?></label>
                        <input id="spreadsheet_email_templates_staff" type="checkbox"
                               name="spreadsheet_email_templates_staff"
                        <label for="spreadsheet_email_templates_staff"><?php echo lang('email_templates') ?></label>
                        
                        <div data-tooltip="<?php echo lang('ss_guide_email_template') ?>" data-tooltip-location="up"><i
                                    class="fa fa-question-circle"></i></div>
                    
                    </div>
                    
                    <h4><?php echo lang('client'); ?></h4>
                    <div class="wrapper">
                        <input id="spreadsheet_client_notification" type="checkbox"
                               name="spreadsheet_client_notification"/>
                        <label for="spreadsheet_client_notification"><?php echo lang('notifications') ?></label>
                        <input id="spreadsheet_email_templates_client" type="checkbox"
                               name="spreadsheet_email_templates_client"/>
                        <label for="spreadsheet_email_templates_client"><?php echo lang('email_templates') ?></label>
                        <div data-tooltip="<?php echo lang('ss_guide_email_template') ?>" data-tooltip-location="up"><i
                                    class="fa fa-question-circle"></i></div>
                    
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default test_class"
                            data-dismiss="modal"><?php echo lang('close'); ?></button>
                    <button type="submit" class="btn btn-info"><?php echo lang('submit'); ?></button>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
<?php
spreadsheet_load_js();
?>
<?php require 'modules/' . SPREADSHEET_MODULE . '/assets/js/manage_js.php'; ?>

</body>

</html>