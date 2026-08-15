<?php echo message_box('success') ?>

<div class="row">
    <!-- Start Form -->
    <div class="col-lg-12">
        <form role="form" id="form" action="<?php echo base_url(); ?>admin/settings/save_settings" method="post"
              class="form-horizontal  ">
            <section class="panel panel-custom">
                <?php
                $can_do = can_do(271);
                if (!empty($can_do)) { ?>
                    <header class="panel-heading  "><?= lang('telegram_integration') ?></header>
                    <div class="panel-body">
                        <div class="form-group">
                            <label class="col-lg-3 control-label"><?= lang('telegram_bot_token') ?></label>
                            <div class="col-lg-7">
                                <input type="text" class="form-control"
                                       value="<?= $this->config->item('telegram_bot_token') ?>"
                                       name="telegram_bot_token"
                                       placeholder="123456789:ABCdefGHIJKlmnoPQRstuvWXYZ">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-3 control-label"><?= lang('telegram_group_id') ?></label>
                            <div class="col-lg-7">
                                <input type="text" class="form-control"
                                       value="<?= $this->config->item('telegram_group_id') ?>"
                                       name="telegram_group_id"
                                       placeholder="-1001234567890">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-3 control-label"><?= lang('telegram_super_admin_notify') ?></label>
                            <div class="col-lg-7">
                                <input type="hidden" name="telegram_super_admin_notify" value="0">
                                <input type="checkbox" name="telegram_super_admin_notify" value="1"
                                    <?= $this->config->item('telegram_super_admin_notify') == '1' ? 'checked' : '' ?>>
                                <span class="help-block"><?= lang('telegram_super_admin_notify_help') ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3"></label>
                        <div class="col-lg-7">
                            <button type="submit" class="btn btn-sm btn-primary"><?= lang('save_changes') ?></button>
                        </div>
                    </div>
                    <?php
                } else {
                    // messages for user
                    echo lang('nothing_to_display');
                }
                ?>
            </section>
        </form>
    </div>
    <!-- End Form -->
</div>
