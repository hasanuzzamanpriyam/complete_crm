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
                        <label class="col-lg-3 control-label">Screenshot Retention (days)</label>
                        <div class="col-lg-5">
                            <input type="number" name="screenshot_retention_days" class="form-control"
                                   value="<?= $screenshot_retention_days ?>" min="0" max="9999">
                            <p class="help-block">
                                Auto-delete screenshots older than this many days. Set to 0 to disable.
                            </p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-3 control-label">Screenshot Capture Interval</label>
                        <div class="col-lg-5">
                            <select name="screenshot_interval_minutes" class="form-control" style="width:200px;">
                                <?php
                                $options = [1 => '1 minute', 2 => '2 minutes', 3 => '3 minutes', 5 => '5 minutes', 10 => '10 minutes', 15 => '15 minutes', 20 => '20 minutes', 30 => '30 minutes', 60 => '60 minutes'];
                                foreach ($options as $val => $label):
                                    $selected = ((int)($screenshot_interval_minutes ?? 5) === $val) ? 'selected' : '';
                                ?>
                                    <option value="<?= $val ?>" <?= $selected ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="help-block">
                                How often screenshots are captured while the timer is running.
                                First screenshot is taken after the interval elapses (not on timer start).
                            </p>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-lg-offset-3 col-lg-5">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </div>
                    </div>
                <?php echo form_close(); ?>

                <hr>

                <?php echo form_open('admin/timesync/settings', ['class' => 'form-horizontal']); ?>
                    <h4 style="margin-bottom:16px;">Hourly Requirements</h4>
                    <p class="text-muted" style="margin-bottom:14px;">
                        Set a global default below, then override per-user values as needed.
                        Users without a custom value will use the default.
                    </p>

                    <div class="row" style="margin-bottom:16px;">
                        <div class="col-sm-4">
                            <label class="control-label">Default Daily Hours</label>
                            <input type="text" name="default_daily_hours" id="defaultDailyHours" class="form-control input-sm"
                                   value="<?= number_format((float)$default_daily_hours, 2) ?>"
                                   style="width:120px;">
                        </div>
                        <div class="col-sm-4">
                            <label class="control-label">Default Monthly Hours</label>
                            <input type="text" name="default_monthly_hours" id="defaultMonthlyHours" class="form-control input-sm"
                                   value="<?= number_format((float)$default_monthly_hours, 2) ?>"
                                   style="width:120px;">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-condensed">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th style="width:130px;">Daily Hours</th>
                                    <th style="width:130px;">Monthly Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($all_users)): ?>
                                    <?php foreach ($all_users as $u):
                                        $uses_default = empty($u->required_daily_hours) && empty($u->required_monthly_hours);
                                        $daily_val = $uses_default ? $default_daily_hours : $u->required_daily_hours;
                                        $monthly_val = $uses_default ? $default_monthly_hours : $u->required_monthly_hours;
                                    ?>
                                        <tr<?= $uses_default ? ' class="text-muted"' : '' ?>>
                                            <td>
                                                <?= htmlspecialchars($u->fullname ?? 'User #' . $u->user_id) ?>
                                                <?php if ($uses_default): ?>
                                                    <span class="label label-default" style="font-size:10px;">default</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <input type="text" name="required_daily[<?= (int)$u->user_id ?>]"
                                                       value="<?= number_format((float)$daily_val, 2) ?>"
                                                       class="form-control input-sm user-daily-hours" style="width:100px;">
                                            </td>
                                            <td>
                                                <input type="text" name="required_monthly[<?= (int)$u->user_id ?>]"
                                                       value="<?= number_format((float)$monthly_val, 2) ?>"
                                                       class="form-control input-sm user-monthly-hours" style="width:100px;">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center">No active users found</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="form-group">
                        <div class="col-lg-12" style="padding-top:8px;">
                            <button type="submit" class="btn btn-primary">Save Requirements</button>
                        </div>
                    </div>
                <?php echo form_close(); ?>

                <hr>

                <h4 style="margin-bottom:16px;">Public Holidays</h4>
                <p class="text-muted" style="margin-bottom:14px;">
                    Holidays are managed via <a href="<?= base_url('admin/holiday') ?>">Admin &rarr; Holiday</a>.
                    They are excluded from working day calculations. Friday is always excluded.
                </p>
            </div>

            <script>
            $(function() {
                $('#defaultDailyHours').on('input', function() {
                    $('.user-daily-hours').val($(this).val());
                });
                $('#defaultMonthlyHours').on('input', function() {
                    $('.user-monthly-hours').val($(this).val());
                });
            });
            </script>
        </section>
    </div>
</div>
