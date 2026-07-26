<style>
.ts-top-cards { margin-bottom: 20px; }
.ts-card {
    background: #fff;
    border: 1px solid #e4eaec;
    border-radius: 6px;
    padding: 20px;
    min-height: 220px;
}
.ts-card:hover { border-color: #cfdbe2; }
.ts-card-head {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
    padding-bottom: 14px;
    border-bottom: 1px solid #f0f2f4;
}
.ts-card-icon {
    width: 38px; height: 38px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 15px; flex-shrink: 0;
}
.ts-card-icon.blue { background: #3b82f6; }
.ts-card-icon.green { background: #10b981; }
.ts-card-label {
    font-size: 14px; font-weight: 600; color: #333;
}
.ts-card-sub {
    font-size: 11px; color: #999; margin-top: 1px;
}
.ts-card .form-group { margin-bottom: 12px; }
.ts-card label.field-label {
    font-size: 12px; font-weight: 600; color: #555;
    margin-bottom: 4px; display: block; text-transform: uppercase;
    letter-spacing: 0.3px;
}
.ts-card .form-control {
    border: 1px solid #dde6e9; border-radius: 4px;
    font-size: 13px; height: 34px; padding: 5px 10px;
    transition: border-color 0.15s;
}
.ts-card .form-control:focus {
    border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,0.12);
}
.ts-card .help-block {
    color: #999; font-size: 11px; margin-top: 3px; line-height: 1.4;
}
.ts-card .btn-save {
    background: #3b82f6; color: #fff; border: none;
    border-radius: 4px; padding: 7px 18px; font-size: 12px;
    font-weight: 600; margin-top: 12px; cursor: pointer;
    transition: background 0.15s;
}
.ts-card .btn-save:hover { background: #2563eb; }
.ts-card select.form-control {
    appearance: auto; -webkit-appearance: auto;
}
.ts-holiday-box {
    background: #f9fbfc; border: 1px dashed #d1d9dc;
    border-radius: 6px; padding: 20px; text-align: center;
    margin-top: 8px;
}
.ts-holiday-box i { font-size: 28px; color: #10b981; margin-bottom: 10px; display: block; }
.ts-holiday-box p { color: #555; font-size: 13px; margin: 6px 0; line-height: 1.5; }
.ts-holiday-link {
    display: inline-block; margin-top: 10px;
    background: #10b981; color: #fff; padding: 7px 18px;
    border-radius: 4px; font-size: 12px; font-weight: 600;
    text-decoration: none; transition: background 0.15s;
}
.ts-holiday-link:hover { background: #059669; color: #fff; }

/* Hourly Requirements - full-width section */
.ts-hr-section {
    background: #fff;
    border: 1px solid #e4eaec;
    border-radius: 6px;
    padding: 0;
}
.ts-hr-head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    border-bottom: 1px solid #f0f2f4;
}
.ts-hr-head .ts-card-icon { width: 34px; height: 34px; font-size: 14px; }
.ts-hr-body { padding: 20px; }
.ts-hr-defaults {
    display: flex; gap: 16px; align-items: flex-end;
    margin-bottom: 16px; padding-bottom: 16px;
    border-bottom: 1px solid #f0f2f4;
}
.ts-hr-defaults > div { flex: 0 0 auto; }
.ts-hr-defaults label.field-label {
    font-size: 12px; font-weight: 600; color: #555;
    margin-bottom: 4px; display: block; text-transform: uppercase;
    letter-spacing: 0.3px;
}
.ts-hr-defaults .form-control {
    border: 1px solid #dde6e9; border-radius: 4px;
    font-size: 13px; height: 34px; padding: 5px 10px;
    width: 110px; transition: border-color 0.15s;
}
.ts-hr-defaults .form-control:focus {
    border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,0.12);
}
.ts-hr-defaults .help-text {
    font-size: 11px; color: #999; margin-top: 4px;
}
.ts-hr-table-wrap {
    max-height: 280px; overflow-y: auto;
    border: 1px solid #f0f2f4; border-radius: 4px;
    margin-bottom: 16px;
}
.ts-hr-table { width: 100%; margin-bottom: 0; }
.ts-hr-table > thead > tr > th {
    background: #fafbfc; border-bottom: 2px solid #e4eaec;
    font-size: 11px; font-weight: 700; color: #777;
    text-transform: uppercase; letter-spacing: 0.4px;
    padding: 10px 12px; position: sticky; top: 0; z-index: 1;
}
.ts-hr-table > tbody > tr > td {
    padding: 8px 12px; vertical-align: middle; font-size: 13px;
    border-top: 1px solid #f0f2f4;
}
.ts-hr-table > tbody > tr.text-muted { opacity: 0.55; }
.ts-hr-table .input-sm {
    width: 90px; height: 30px; font-size: 12px; padding: 4px 8px;
    border: 1px solid #dde6e9; border-radius: 4px;
    transition: border-color 0.15s;
}
.ts-hr-table .input-sm:focus {
    border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,0.12);
}
.ts-hr-table .label-default {
    font-size: 9px; background: #f0f2f4; color: #999;
    font-weight: 600; padding: 2px 6px; border-radius: 3px;
    text-transform: uppercase; letter-spacing: 0.3px;
    vertical-align: middle; margin-left: 4px;
}
.ts-hr-footer {
    display: flex; align-items: center; justify-content: space-between;
}
.ts-hr-footer .help-block { margin: 0; }
.ts-hr-footer .btn-save {
    background: #f59e0b; color: #fff; border: none;
    border-radius: 4px; padding: 7px 18px; font-size: 12px;
    font-weight: 600; cursor: pointer; transition: background 0.15s;
}
.ts-hr-footer .btn-save:hover { background: #d97706; }
</style>

<?php echo message_box('success'); ?>

<!-- Row 1: Screenshot + Holidays -->
<div class="ts-top-cards">
    <div class="row">
        <div class="col-lg-6">
            <div class="ts-card">
                <div class="ts-card-head">
                    <div class="ts-card-icon blue"><i class="fa fa-camera"></i></div>
                    <div>
                        <div class="ts-card-label">Screenshot Settings</div>
                        <div class="ts-card-sub">Capture &amp; retention config</div>
                    </div>
                </div>

                <?php echo form_open('admin/timesync/settings'); ?>
                    <div class="form-group">
                        <label class="field-label">Retention Period (days)</label>
                        <input type="number" name="screenshot_retention_days" class="form-control"
                               value="<?= $screenshot_retention_days ?>" min="0" max="9999">
                        <p class="help-block">Auto-delete screenshots older than this. 0 = never delete.</p>
                    </div>

                    <div class="form-group">
                        <label class="field-label">Capture Interval</label>
                        <select name="screenshot_interval_minutes" class="form-control">
                            <?php
                            $options = [1=>'1 min',2=>'2 min',3=>'3 min',5=>'5 min',10=>'10 min',15=>'15 min',20=>'20 min',30=>'30 min',60=>'60 min'];
                            foreach ($options as $val => $label):
                                $selected = ((int)($screenshot_interval_minutes ?? 5) === $val) ? 'selected' : '';
                            ?>
                                <option value="<?= $val ?>" <?= $selected ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="help-block">Frequency while timer is running. First capture after interval elapses.</p>
                    </div>

                    <button type="submit" class="btn-save"><i class="fa fa-check" style="margin-right:4px;"></i> Save Settings</button>
                <?php echo form_close(); ?>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="ts-card">
                <div class="ts-card-head">
                    <div class="ts-card-icon green"><i class="fa fa-calendar"></i></div>
                    <div>
                        <div class="ts-card-label">Holidays &amp; Info</div>
                        <div class="ts-card-sub">Working day config</div>
                    </div>
                </div>

                <div class="ts-holiday-box">
                    <i class="fa fa-calendar-check-o"></i>
                    <p>Public holidays are managed through the<br><strong>Admin &rarr; Holiday</strong> page.</p>
                    <p style="color:#999;font-size:11px;">Holidays are excluded from working day calculations.<br>Friday is always excluded.</p>
                    <a href="<?= base_url('admin/holiday') ?>" class="ts-holiday-link">
                        <i class="fa fa-external-link" style="margin-right:4px;"></i> Manage Holidays
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 2: Hourly Requirements (full-width) -->
<div class="ts-hr-section">
    <div class="ts-hr-head">
        <div class="ts-card-icon amber" style="background:#f59e0b;"><i class="fa fa-clock-o"></i></div>
        <div>
            <div class="ts-card-label">Hourly Requirements</div>
            <div class="ts-card-sub">Set global defaults, then override per-user</div>
        </div>
    </div>
    <div class="ts-hr-body">
        <?php echo form_open('admin/timesync/settings'); ?>
            <div class="ts-hr-defaults">
                <div>
                    <label class="field-label">Default Daily Hours</label>
                    <input type="text" name="default_daily_hours" id="defaultDailyHours"
                           class="form-control"
                           value="<?= number_format((float)$default_daily_hours, 2) ?>">
                </div>
                <div>
                    <label class="field-label">Default Monthly Hours</label>
                    <input type="text" name="default_monthly_hours" id="defaultMonthlyHours"
                           class="form-control"
                           value="<?= number_format((float)$default_monthly_hours, 2) ?>">
                </div>
                <div>
                    <p class="help-text" style="margin-bottom:0;">Changing defaults auto-fills all user fields below.</p>
                </div>
            </div>

            <div class="ts-hr-table-wrap">
                <table class="ts-hr-table table table-striped">
                    <thead>
                        <tr>
                            <th style="width:40%;">User</th>
                            <th style="width:30%;">Daily Hours</th>
                            <th style="width:30%;">Monthly Hours</th>
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
                                            <span class="label label-default">default</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <input type="text" name="required_daily[<?= (int)$u->user_id ?>]"
                                               value="<?= number_format((float)$daily_val, 2) ?>"
                                               class="form-control input-sm user-daily-hours">
                                    </td>
                                    <td>
                                        <input type="text" name="required_monthly[<?= (int)$u->user_id ?>]"
                                               value="<?= number_format((float)$monthly_val, 2) ?>"
                                               class="form-control input-sm user-monthly-hours">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center text-muted">No active users</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="ts-hr-footer">
                <p class="help-block">Users with <span class="label label-default" style="font-size:9px;">default</span> use the global values above.</p>
                <button type="submit" class="btn-save"><i class="fa fa-check" style="margin-right:4px;"></i> Save Requirements</button>
            </div>
        <?php echo form_close(); ?>
    </div>
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
