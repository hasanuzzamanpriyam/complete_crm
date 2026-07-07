<?php
echo message_box('success');
echo message_box('error');
?>
<div class="panel panel-custom">
    <header class="panel-heading">
        <div class="panel-title">
            <strong><?= lang('jitsi_settings') ?></strong>
            <a href="<?= base_url('admin/jitsi') ?>" class="btn btn-xs btn-danger pull-right"><?= lang('back') ?></a>
        </div>
    </header>
    <div class="panel-body">
        <form role="form" action="<?= base_url('admin/jitsi/settings') ?>" method="post" class="form-horizontal">
            <div class="form-group">
                <label class="col-lg-3 control-label"><?= lang('jitsi_domain') ?> <span class="text-danger">*</span></label>
                <div class="col-lg-7">
                    <input type="url" class="form-control" name="jitsi_domain" value="<?= config_item('jitsi_domain') ?: 'https://meet.jit.si' ?>" placeholder="https://meet.jit.si" required>
                    <p class="help-block"><?= lang('jitsi_domain_help') ?></p>
                </div>
            </div>

            <div class="form-group">
                <label class="col-lg-3 control-label"><?= lang('jitsi_app_id') ?> <span class="text-danger">*</span></label>
                <div class="col-lg-7">
                    <input type="text" class="form-control" name="jitsi_app_id" value="<?= config_item('jitsi_app_id') ?>" placeholder="your-app-id" required>
                    <p class="help-block"><?= lang('jitsi_app_id_help') ?></p>
                </div>
            </div>

            <div class="form-group">
                <label class="col-lg-3 control-label"><?= lang('jitsi_private_key') ?> <span class="text-danger">*</span></label>
                <div class="col-lg-7">
                    <textarea class="form-control" name="jitsi_private_key" rows="8" placeholder="-----BEGIN PRIVATE KEY-----" required><?= config_item('jitsi_private_key') ? '*** Key is configured ***' : '' ?></textarea>
                    <p class="help-block"><?= lang('jitsi_private_key_help') ?></p>
                </div>
            </div>

            <div class="form-group">
                <label class="col-lg-3 control-label"><?= lang('jitsi_public_key') ?></label>
                <div class="col-lg-7">
                    <textarea class="form-control" name="jitsi_public_key" rows="5" placeholder="-----BEGIN PUBLIC KEY-----"><?= config_item('jitsi_public_key') ?></textarea>
                    <p class="help-block"><?= lang('jitsi_public_key_help') ?></p>
                </div>
            </div>

            <div class="form-group">
                <div class="col-lg-offset-3 col-lg-7">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> <?= lang('save') ?></button>
                </div>
            </div>
        </form>
    </div>
</div>
