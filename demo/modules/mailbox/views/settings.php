<div class="panel panel-custom">
    <div class="panel-heading"><?= lang('email') . ' ' . lang('settings') ?></div>
    <div class="panel-body">
        <form role="form" data-parsley-validate="" novalidate=""
              action="<?php echo base_url(); ?>admin/mailbox/settings" method="post"
              class="form-horizontal form-groups-bordered">
            <?php
            $form_error = $this->session->userdata('form_error');
            $profile_info_2 = MyDetails();
            $smtp_type = $this->session->userdata('smtp_type');
            if (!empty($form_error)) {
                ?>
                <div role="alert" class="alert alert-<?= $this->session->userdata('imap_type') ?> alert-dismissible">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    <?= $this->session->userdata('imap_msg') ?>
                </div>
                <div role="alert" class="alert alert-<?= $smtp_type ?> alert-dismissible">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    <?= $this->session->userdata('smtp_msg') ?>
                </div>
            <?php }
            $this->session->unset_userdata('form_error');
            ?>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?= lang('email') ?></label>
                <div class="col-lg-6">
                    <input type="text" name="smtp_email" required
                           value="<?php echo set_value('', $profile_info_2->active_email); ?>" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?= lang('encryption') ?></label>
                <div class="col-lg-9">
                    <label class="checkbox-inline c-checkbox">
                        <input class="select_one" required type="checkbox" value="tls" name="smtp_encryption" <?php
                        if ($profile_info_2->smtp_encription == 'tls') {
                            echo "checked=\"checked\"";
                        }
                        ?>>
                        <span class="fa fa-check"></span><?= lang('tls') ?>
                    </label>
                    
                    <label class="checkbox-inline c-checkbox">
                        <input class="select_one " type="checkbox" value="ssl" required name="smtp_encryption" <?php
                        if ($profile_info_2->smtp_encription == 'ssl') {
                            echo "checked=\"checked\"";
                        }
                        ?>>
                        <span class="fa fa-check"></span><?= lang('ssl') ?>
                    </label>
                    <label class="checkbox-inline c-checkbox">
                        <input class="select_one " type="checkbox" value="no" required name="smtp_encryption" <?php
                        if ($profile_info_2->smtp_encription == null) {
                            echo "checked=\"checked\"";
                        }
                        ?>>
                        <span class="fa fa-check"></span><?= lang('no') . ' ' . lang('encryption') ?>
                    </label>
                
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?= lang('IMAP') . ' ' . lang('HOST'); ?></label>
                <div class="col-lg-6">
                    <input type="text" name="smtp_host_name" required
                           value="<?php echo set_value('', $profile_info_2->smtp_host_name); ?>" class="form-control"
                           placeholder="for example: imap.gmail.com">
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?= lang('smtp_host') ?></label>
                <div class="col-lg-6">
                    <input type="text" name="mail_host" value="<?php echo set_value('', $profile_info_2->mail_host); ?>"
                           class="form-control" placeholder="for example: smtp.gmail.com">
                </div>
            </div>
            <?php if (!empty($smtp_type) && $smtp_type == 'danger') { ?>
                <div class="form-group">
                    <label class="col-lg-3 control-label"><?= lang('email_protocol') ?> <span
                                class="text-danger">*</span></label>
                    <div class="col-lg-6">
                        <select name="protocol" required="" class="form-control">
                            <?php $prot = $profile_info_2->smtp_email_type; ?>
                            <option value="smtp" <?= ($prot == "smtp" ? ' selected="selected"' : '') ?>><?= lang('smtp') ?></option>
                            <option value="sendmail" <?= ($prot == "sendmail" ? ' selected="selected"' : '') ?>><?= lang('sendmail') ?></option>
                            <option value="mail" <?= ($prot == "mail" ? ' selected="selected"' : '') ?>><?= lang('php_mail') ?></option>
                        </select>
                    </div>
                </div>
            <?php } ?>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?= lang('password') ?></label>
                <div class="col-lg-6">
                    <?php $password = strlen(decrypt($profile_info_2->smtp_password)); ?>
                    <input type="password" name="smtp_password" placeholder="<?php
                    if (!empty($password)) {
                        for ($p = 1; $p <= $password; $p++) {
                            echo '*';
                        }
                    } ?>" class="form-control">
                    <strong id="show_password" class="required"></strong>
                </div>
                <div class="col-lg-3">
                    <a data-toggle="modal" data-target="#myModal"
                       href="<?= base_url('admin/client/see_password/timap_') ?>"
                       id="see_password"><?= lang('see_password') ?></a>
                    <strong id="hosting_password_" class="required"></strong>
                </div>
            </div>
            <?php
            $unread_email = $profile_info_2->smtp_unread_email;
            ?>
            <div class="form-group">
                <label class="col-lg-3 control-label"></label>
                <div class="col-lg-3">
                    <label class="checkbox-inline c-checkbox">
                        <input type="checkbox" value="1" name="smtp_unread_email" <?php
                        if ($unread_email == '1') {
                            echo "checked=\"checked\"";
                        }
                        ?>>
                        <span class="fa fa-check"></span><?= lang('unread_email') ?>
                    </label>
                </div>
                <div class="col-sm-6">
                    <label class="checkbox-inline c-checkbox">
                        <input type="checkbox" value="1" name="smtp_delete_mail_after_import" <?php
                        if ($profile_info_2->smtp_delete_mail_after_import == '1') {
                            echo "checked=\"checked\"";
                        }
                        ?>>
                        <span class="fa fa-check"></span><?= lang('delete_mail_after_import') ?>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label"></label>
                <div class="col-lg-6">
                    <div class="pull-left">
                        <button type="submit" name="user_email_integration"
                                class="btn btn-sm btn-primary"><?= lang('save_changes'); ?></button>
                    </div>
                    
                    <div class="pull-right">
                        <button type="submit" name="test_settings"
                                class="btn btn-warning pull-right ml"><?= lang('Test') . '  ' . lang('settings'); ?></button>
                    </div>
                
                </div>
                <div class="col-lg-3 control-label"></div>
            </div>
        </form>
    </div>
</div>