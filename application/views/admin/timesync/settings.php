<div class="row">
    <div class="col-lg-12">
        <section class="panel panel-custom">
            <header class="panel-heading">
                <h3 class="panel-title"><?= $title ?? 'TimeSync Settings' ?></h3>
            </header>
            <div class="panel-body">
                <?php echo message_box('success'); ?>

                <?php echo form_open('admin/timesync/settings', ['class' => 'form-horizontal']); ?>
                    <div class="form-group">
                        <label class="col-lg-3 control-label">Demo Mode</label>
                        <div class="col-lg-5">
                            <div class="radio">
                                <label>
                                    <input type="radio" name="demo_mode" value="1" <?= $demo_mode == '1' ? 'checked' : '' ?>>
                                    Enabled — Desktop app can use demo mode
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="demo_mode" value="0" <?= $demo_mode == '0' ? 'checked' : '' ?>>
                                    Disabled — Desktop app must use ERP connection
                                </label>
                            </div>
                            <p class="help-block">
                                When disabled, TimeSync desktop users must authenticate via ERP to use the app.
                                Only super admin can change this setting.
                            </p>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-lg-offset-3 col-lg-5">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </div>
                    </div>
                <?php echo form_close(); ?>
            </div>
        </section>
    </div>
</div>
