<link rel="stylesheet" href="<?php echo base_url(); ?>plugins/colorpicker/css/bootstrap-colorpicker.min.css">
<script src="<?php echo base_url(); ?>plugins/colorpicker/js/bootstrap-colorpicker.min.js"></script>

<form role="form" id="from_items" action="<?php echo base_url(); ?>admin/calendar/save_settings" method="post" class="form-horizontal form-groups-bordered">
    <div class="cal-settings">
        <div class="m-head">
            <div>
                <h3><?php echo lang('calendar_settings'); ?></h3>
                <div class="sub">Google sync &amp; what appears on the calendar</div>
            </div>
            <button type="button" class="m-x" data-dismiss="modal" aria-hidden="true"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="m-body">
            <div class="m-sec">Google Calendar</div>
            <div class="gcal-note"><span>&#8505;</span> Paste your Google API key and Calendar ID to sync events both ways. Leave empty to use CRM events only.</div>
            <div class="f-2" style="margin-top:13px">
                <div class="f-row">
                    <label><?php echo lang('google_api'); ?></label>
                    <input type="text" class="inp" value="<?php echo config_item('gcal_api_key'); ?>" name="gcal_api_key">
                </div>
                <div class="f-row">
                    <label><?php echo lang('calendar_id'); ?></label>
                    <input type="text" class="inp" value="<?php echo config_item('gcal_id'); ?>" name="gcal_id">
                </div>
            </div>

            <div class="m-sec">Show on calendar</div>
            <?php
            $cal_items = array(
                array('project_on_calendar', 'project_color', 'project', '#0d9488'),
                array('milestone_on_calendar', 'milestone_color', 'milestone', '#a86495'),
                array('tasks_on_calendar', 'tasks_color', 'tasks', '#0239c7'),
                array('bugs_on_calendar', 'bugs_color', 'bugs', '#ff0000'),
                array('invoice_on_calendar', 'invoice_color', 'invoice', '#53b567'),
                array('payments_on_calendar', 'payments_color', 'payments', '#7f21c9'),
                array('estimate_on_calendar', 'estimate_color', 'estimate', '#a01dab'),
                array('opportunities_on_calendar', 'opportunities_color', 'opportunities', '#349685'),
                array('goal_tracking_on_calendar', 'goal_tracking_color', 'goal_tracking', '#537015'),
                array('holiday_on_calendar', 'holiday_color', 'holiday', '#f0ad4e'),
                array('absent_on_calendar', 'absent_color', 'absent', '#f71724'),
                array('on_leave_on_calendar', 'on_leave_color', 'on_leave', '#bd1a10'),
                array('leads_on_calendar', 'leads_color', 'leads', '#783131'),
                array('expense_schedule_on_calendar', 'expense_schedule_color', 'expense_schedule', '#fb6b5b'),
                array('domain_on_calendar', 'domain_color', 'domain', '#3b5998'),
                array('hosting_on_calendar', 'hosting_color', 'hosting', '#00897b'),
            );
            $label_fallbacks = array(
                'expense_schedule' => 'Expense Schedules',
                'domain' => 'Domains',
                'hosting' => 'Hosting',
            );
            ?>
            <div class="so-grid">
                <?php foreach ($cal_items as $ci):
                    list($vis_key, $color_key, $lang_key, $default_color) = $ci;
                    $label = isset($lang[$lang_key]) ? lang($lang_key) : (isset($label_fallbacks[$lang_key]) ? $label_fallbacks[$lang_key] : ucwords(str_replace('_', ' ', $lang_key)));
                    $color_value = config_item($color_key) ? config_item($color_key) : $default_color;
                    $cp_id = 'cp_' . str_replace('_color', '', $color_key);
                    ?>
                    <div class="so">
                        <span class="prio" style="background:<?php echo $color_value; ?>"></span>
                        <span class="so-l"><?php echo $label; ?></span>
                        <div class="checkbox c-checkbox so-cb">
                            <label class="needsclick">
                                <input type="checkbox" <?php if (config_item($vis_key) == 'on') { echo 'checked="checked"'; } ?> name="<?php echo $vis_key; ?>">
                                <span class="fa fa-check"></span>
                            </label>
                        </div>
                        <div id="<?php echo $cp_id; ?>" class="input-group colorpicker-component">
                            <input type="hidden" value="<?php echo $color_value; ?>" name="<?php echo $color_key; ?>" class="form-control"/>
                            <span class="input-group-addon"><i></i></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="f-row" style="margin-top:14px">
                <label><?php echo isset($lang['upcoming_expiry_days']) ? lang('upcoming_expiry_days') : 'Upcoming Expiry Days'; ?></label>
                <input type="number" class="inp" style="max-width:160px" value="<?php echo config_item('upcoming_expiry_days') ? config_item('upcoming_expiry_days') : '7'; ?>" name="upcoming_expiry_days" min="1" max="90">
            </div>

            <script>
                $(function () {
                    $('.colorpicker-component').each(function () {
                        var $this = $(this);
                        var color = $this.find('input').val();
                        $this.colorpicker({color: color});
                    });
                });
            </script>
        </div>
        <div class="m-foot">
            <button type="button" class="btn-ghost" data-dismiss="modal"><?php echo lang('close'); ?></button>
            <span class="spacer-m"></span>
            <button type="submit" class="btn-accent"><?php echo lang('save'); ?></button>
        </div>
    </div>
</form>
