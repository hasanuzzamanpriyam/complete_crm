<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php spreadsheet_add_head_component(); ?>
<div class="row">
    <div class="col-md-12">
        <div class="nav-tabs-custom">
            <!-- Tabs within a box -->
            <ul class="nav nav-tabs">
                <li role="presentation" class="tab_cart <?php if ($tab == 'my_folder') {
                    echo 'active';
                } ?>">
                    <a href="<?php echo admin_url('/spreadsheet/manage/my_folder'); ?>" aria-controls="tab_config"
                       role="tab" aria-controls="tab_config">
                        <?php echo lang('my_folder'); ?>
                    
                    </a>
                </li>
                
                <li role="presentation" class="tab_cart <?php if ($tab == 'share_folder') {
                    echo 'active';
                } ?>">
                    <a href="<?php echo admin_url('spreadsheet/manage/share_folder'); ?>" aria-controls="tab_config"
                       role="tab" aria-controls="tab_config">
                        <?php echo lang('share_folder'); ?>
                    </a>
                </li>
            </ul>
            <div class="p">
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
</div>
<?php
spreadsheet_load_js();
?>
<?php require 'modules/' . SPREADSHEET_MODULE . '/assets/js/manage_js.php'; ?>

</body>

</html>