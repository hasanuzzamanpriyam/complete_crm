<link rel="stylesheet" href="<?php echo base_url(); ?>plugins/colorpicker/css/bootstrap-colorpicker.min.css">
<script src="<?php echo base_url(); ?>plugins/colorpicker/js/bootstrap-colorpicker.min.js"></script>

<div class="panel panel-custom">
    <div class="panel-heading">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" style="margin-top: 13px;" id="myModalLabel"><?php echo lang('calendar_settings'); ?></h4>
    </div>
    <form role="form" id="from_items" action="<?php echo base_url(); ?>admin/calendar/save_settings" method="post" class="form-horizontal form-groups-bordered">
        <div class="modal-body">
            <div class="form-group">
                <label class="col-lg-4 control-label"><?php echo lang('google_api'); ?></label>
                <div class="col-lg-8">
                    <input type="text" class="form-control" value="<?php echo config_item('gcal_api_key'); ?>" name="gcal_api_key">
                </div>
            </div>
            
            <div class="form-group">
                <label class="col-lg-4 control-label"><?php echo lang('calendar_id'); ?></label>
                <div class="col-lg-8">
                    <input type="text" class="form-control" value="<?php echo config_item('gcal_id'); ?>" name="gcal_id">
                </div>
            </div>
            
            <h4 class="mb0"><?php echo lang('show_on_calendar'); ?></h4>
            <hr class="mt-sm"/>
            
            <!-- Row 1: Project & Milestone -->
            <div class="form-group">
                <label class="col-lg-4 control-label"><?php echo lang('project'); ?></label>
                <div class="col-lg-1">
                    <div class="checkbox c-checkbox">
                        <label class="needsclick">
                            <input type="checkbox" <?php if (config_item('project_on_calendar') == 'on') { echo 'checked="checked"'; } ?> name="project_on_calendar">
                            <span class="fa fa-check"></span>
                        </label>
                    </div>
                </div>
                <div class="col-lg-1">
                    <div id="cp_project" class="input-group colorpicker-component">
                        <input type="hidden" value="<?php echo config_item('project_color'); ?>" name="project_color" class="form-control"/>
                        <span class="input-group-addon"><i></i></span>
                    </div>
                </div>
                <label class="col-lg-2 control-label"><?php echo lang('milestone'); ?></label>
                <div class="col-lg-1">
                    <div class="checkbox c-checkbox">
                        <label class="needsclick">
                            <input type="checkbox" <?php if (config_item('milestone_on_calendar') == 'on') { echo 'checked="checked"'; } ?> name="milestone_on_calendar">
                            <span class="fa fa-check"></span>
                        </label>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div id="cp_milestone" class="input-group colorpicker-component">
                        <input type="hidden" value="<?php echo config_item('milestone_color'); ?>" name="milestone_color" class="form-control"/>
                        <span class="input-group-addon"><i></i></span>
                    </div>
                </div>
            </div>
            
            <!-- Row 2: Tasks & Bugs -->
            <div class="form-group">
                <label class="col-lg-4 control-label"><?php echo lang('tasks'); ?></label>
                <div class="col-lg-1">
                    <div class="checkbox c-checkbox">
                        <label class="needsclick">
                            <input type="checkbox" <?php if (config_item('tasks_on_calendar') == 'on') { echo 'checked="checked"'; } ?> name="tasks_on_calendar">
                            <span class="fa fa-check"></span>
                        </label>
                    </div>
                </div>
                <div class="col-lg-1">
                    <div id="cp_tasks" class="input-group colorpicker-component">
                        <input type="hidden" value="<?php echo config_item('tasks_color'); ?>" name="tasks_color" class="form-control"/>
                        <span class="input-group-addon"><i></i></span>
                    </div>
                </div>
                <label class="col-lg-2 control-label"><?php echo lang('bugs'); ?></label>
                <div class="col-lg-1">
                    <div class="checkbox c-checkbox">
                        <label class="needsclick">
                            <input type="checkbox" <?php if (config_item('bugs_on_calendar') == 'on') { echo 'checked="checked"'; } ?> name="bugs_on_calendar">
                            <span class="fa fa-check"></span>
                        </label>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div id="cp_bugs" class="input-group colorpicker-component">
                        <input type="hidden" value="<?php echo config_item('bugs_color'); ?>" name="bugs_color" class="form-control"/>
                        <span class="input-group-addon"><i></i></span>
                    </div>
                </div>
            </div>
            
            <!-- Row 3: Invoice & Payments -->
            <div class="form-group">
                <label class="col-lg-4 control-label"><?php echo lang('invoice'); ?></label>
                <div class="col-lg-1">
                    <div class="checkbox c-checkbox">
                        <label class="needsclick">
                            <input type="checkbox" <?php if (config_item('invoice_on_calendar') == 'on') { echo 'checked="checked"'; } ?> name="invoice_on_calendar">
                            <span class="fa fa-check"></span>
                        </label>
                    </div>
                </div>
                <div class="col-lg-1">
                    <div id="cp_invoice" class="input-group colorpicker-component">
                        <input type="hidden" value="<?php echo config_item('invoice_color'); ?>" name="invoice_color" class="form-control"/>
                        <span class="input-group-addon"><i></i></span>
                    </div>
                </div>
                <label class="col-lg-2 control-label"><?php echo lang('payments'); ?></label>
                <div class="col-lg-1">
                    <div class="checkbox c-checkbox">
                        <label class="needsclick">
                            <input type="checkbox" <?php if (config_item('payments_on_calendar') == 'on') { echo 'checked="checked"'; } ?> name="payments_on_calendar">
                            <span class="fa fa-check"></span>
                        </label>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div id="cp_payments" class="input-group colorpicker-component">
                        <input type="hidden" value="<?php echo config_item('payments_color'); ?>" name="payments_color" class="form-control"/>
                        <span class="input-group-addon"><i></i></span>
                    </div>
                </div>
            </div>
            
            <!-- Row 4: Estimate & Opportunities -->
            <div class="form-group">
                <label class="col-lg-4 control-label"><?php echo lang('estimate'); ?></label>
                <div class="col-lg-1">
                    <div class="checkbox c-checkbox">
                        <label class="needsclick">
                            <input type="checkbox" <?php if (config_item('estimate_on_calendar') == 'on') { echo 'checked="checked"'; } ?> name="estimate_on_calendar">
                            <span class="fa fa-check"></span>
                        </label>
                    </div>
                </div>
                <div class="col-lg-1">
                    <div id="cp_estimate" class="input-group colorpicker-component">
                        <input type="hidden" value="<?php echo config_item('estimate_color'); ?>" name="estimate_color" class="form-control"/>
                        <span class="input-group-addon"><i></i></span>
                    </div>
                </div>
                <label class="col-lg-2 control-label"><?php echo lang('opportunities'); ?></label>
                <div class="col-lg-1">
                    <div class="checkbox c-checkbox">
                        <label class="needsclick">
                            <input type="checkbox" <?php if (config_item('opportunities_on_calendar') == 'on') { echo 'checked="checked"'; } ?> name="opportunities_on_calendar">
                            <span class="fa fa-check"></span>
                        </label>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div id="cp_opportunities" class="input-group colorpicker-component">
                        <input type="hidden" value="<?php echo config_item('opportunities_color'); ?>" name="opportunities_color" class="form-control"/>
                        <span class="input-group-addon"><i></i></span>
                    </div>
                </div>
            </div>
            
            <!-- Row 5: Goal Tracking & Holiday -->
            <div class="form-group">
                <label class="col-lg-4 control-label"><?php echo lang('goal_tracking'); ?></label>
                <div class="col-lg-1">
                    <div class="checkbox c-checkbox">
                        <label class="needsclick">
                            <input type="checkbox" <?php if (config_item('goal_tracking_on_calendar') == 'on') { echo 'checked="checked"'; } ?> name="goal_tracking_on_calendar">
                            <span class="fa fa-check"></span>
                        </label>
                    </div>
                </div>
                <div class="col-lg-1">
                    <div id="cp_goal_tracking" class="input-group colorpicker-component">
                        <input type="hidden" value="<?php echo config_item('goal_tracking_color'); ?>" name="goal_tracking_color" class="form-control"/>
                        <span class="input-group-addon"><i></i></span>
                    </div>
                </div>
                <label class="col-lg-2 control-label"><?php echo lang('holiday'); ?></label>
                <div class="col-lg-1">
                    <div class="checkbox c-checkbox">
                        <label class="needsclick">
                            <input type="checkbox" <?php if (config_item('holiday_on_calendar') == 'on') { echo 'checked="checked"'; } ?> name="holiday_on_calendar">
                            <span class="fa fa-check"></span>
                        </label>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div id="cp_holiday" class="input-group colorpicker-component">
                        <input type="hidden" value="<?php echo config_item('holiday_color') ? config_item('holiday_color') : '#f0ad4e'; ?>" name="holiday_color" class="form-control"/>
                        <span class="input-group-addon"><i></i></span>
                    </div>
                </div>
            </div>
            
            <!-- Row 6: Absent & On Leave -->
            <div class="form-group">
                <label class="col-lg-4 control-label"><?php echo lang('absent'); ?></label>
                <div class="col-lg-1">
                    <div class="checkbox c-checkbox">
                        <label class="needsclick">
                            <input type="checkbox" <?php if (config_item('absent_on_calendar') == 'on') { echo 'checked="checked"'; } ?> name="absent_on_calendar">
                            <span class="fa fa-check"></span>
                        </label>
                    </div>
                </div>
                <div class="col-lg-1">
                    <div id="cp_absent" class="input-group colorpicker-component">
                        <input type="hidden" value="<?php echo config_item('absent_color'); ?>" name="absent_color" class="form-control"/>
                        <span class="input-group-addon"><i></i></span>
                    </div>
                </div>
                <label class="col-lg-2 control-label"><?php echo lang('on_leave'); ?></label>
                <div class="col-lg-1">
                    <div class="checkbox c-checkbox">
                        <label class="needsclick">
                            <input type="checkbox" <?php if (config_item('on_leave_on_calendar') == 'on') { echo 'checked="checked"'; } ?> name="on_leave_on_calendar">
                            <span class="fa fa-check"></span>
                        </label>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div id="cp_on_leave" class="input-group colorpicker-component">
                        <input type="hidden" value="<?php echo config_item('on_leave_color'); ?>" name="on_leave_color" class="form-control"/>
                        <span class="input-group-addon"><i></i></span>
                    </div>
                </div>
            </div>
            
            <!-- Row 7: Leads & Expense Schedules -->
            <div class="form-group">
                <label class="col-lg-4 control-label"><?php echo lang('leads'); ?></label>
                <div class="col-lg-1">
                    <div class="checkbox c-checkbox">
                        <label class="needsclick">
                            <input type="checkbox" <?php if (config_item('leads_on_calendar') == 'on') { echo 'checked="checked"'; } ?> name="leads_on_calendar">
                            <span class="fa fa-check"></span>
                        </label>
                    </div>
                </div>
                <div class="col-lg-1">
                    <div id="cp_leads" class="input-group colorpicker-component">
                        <input type="hidden" value="<?php echo config_item('leads_color'); ?>" name="leads_color" class="form-control"/>
                        <span class="input-group-addon"><i></i></span>
                    </div>
                </div>
                <label class="col-lg-2 control-label"><?php echo isset($lang['expense_schedules']) ? lang('expense_schedules') : 'Expense Schedules'; ?></label>
                <div class="col-lg-1">
                    <div class="checkbox c-checkbox">
                        <label class="needsclick">
                            <input type="checkbox" <?php if (config_item('expense_schedule_on_calendar') == 'on') { echo 'checked="checked"'; } ?> name="expense_schedule_on_calendar">
                            <span class="fa fa-check"></span>
                        </label>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div id="cp_expense_schedule" class="input-group colorpicker-component">
                        <input type="hidden" value="<?php echo config_item('expense_schedule_color') ? config_item('expense_schedule_color') : '#fb6b5b'; ?>" name="expense_schedule_color" class="form-control"/>
                        <span class="input-group-addon"><i></i></span>
                    </div>
                </div>
            </div>
            
            <!-- Row 8: Domains & Hosting -->
            <div class="form-group">
                <label class="col-lg-4 control-label"><?php echo isset($lang['domains']) ? lang('domains') : 'Domains'; ?></label>
                <div class="col-lg-1">
                    <div class="checkbox c-checkbox">
                        <label class="needsclick">
                            <input type="checkbox" <?php if (config_item('domain_on_calendar') == 'on') { echo 'checked="checked"'; } ?> name="domain_on_calendar">
                            <span class="fa fa-check"></span>
                        </label>
                    </div>
                </div>
                <div class="col-lg-1">
                    <div id="cp_domain" class="input-group colorpicker-component">
                        <input type="hidden" value="<?php echo config_item('domain_color') ? config_item('domain_color') : '#3b5998'; ?>" name="domain_color" class="form-control"/>
                        <span class="input-group-addon"><i></i></span>
                    </div>
                </div>
                <label class="col-lg-2 control-label"><?php echo isset($lang['hosting']) ? lang('hosting') : 'Hosting'; ?></label>
                <div class="col-lg-1">
                    <div class="checkbox c-checkbox">
                        <label class="needsclick">
                            <input type="checkbox" <?php if (config_item('hosting_on_calendar') == 'on') { echo 'checked="checked"'; } ?> name="hosting_on_calendar">
                            <span class="fa fa-check"></span>
                        </label>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div id="cp_hosting" class="input-group colorpicker-component">
                        <input type="hidden" value="<?php echo config_item('hosting_color') ? config_item('hosting_color') : '#00897b'; ?>" name="hosting_color" class="form-control"/>
                        <span class="input-group-addon"><i></i></span>
                    </div>
                </div>
            </div>
            
            <!-- Upcoming Expiry Days -->
            <div class="form-group">
                <label class="col-lg-4 control-label"><?php echo isset($lang['upcoming_expiry_days']) ? lang('upcoming_expiry_days') : 'Upcoming Expiry Days'; ?></label>
                <div class="col-lg-3">
                    <input type="number" class="form-control" value="<?php echo config_item('upcoming_expiry_days') ? config_item('upcoming_expiry_days') : '7'; ?>" name="upcoming_expiry_days" min="1" max="90">
                </div>
            </div>
            
            <script>
                $(function () {
                    $('.colorpicker-component').each(function() {
                        var $this = $(this);
                        var color = $this.find('input').val();
                        $this.colorpicker({color: color});
                    });
                });
            </script>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo lang('save'); ?></button>
            </div>
        </div>
    </form>
</div>