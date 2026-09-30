<link href="<?php echo base_url() ?>assets/plugins/fullcalendar/fullcalendar.min.css" rel="stylesheet" type="text/css">
<link href="<?php echo base_url() ?>assets/css/calendar_modern.css?v=2" rel="stylesheet" type="text/css">
<?php
$curency = $this->admin_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');
$gcal_api_key = config_item('gcal_api_key');
$gcal_id = config_item('gcal_id');

?>
<!--Calendar-->
<?php /*Comment in my JavaScript*/ ?>
<script type="text/javascript">
    $(document).ready(function() {
        if ($('#my_calendar').length) {
            var date = new Date();
            var d = date.getDate();
            var m = date.getMonth();
            var y = date.getFullYear();
            var calendar = $('#my_calendar').fullCalendar({
                googleCalendarApiKey: '<?= $gcal_api_key ?>',
                eventAfterRender: function(event, element, view) {
                    if (event.type == 'fo' || ((event.type == 'domain' || event.type == 'hosting') && event.url && event.url.indexOf('tasks/details') === -1)) {
                        $(element).attr('data-toggle', 'ajaxModal').addClass('ajaxModal');
                    }
                },
                header: {
                    left: '',
                    center: '',
                    right: ''
                },
                viewRender: function (view) {
                    $('#calTitle').text(view.title);
                },
                eventAfterAllRender: function () {
                    updateCalendarKpis();
                },
                selectable: true,
                selectHelper: true,
                eventLimit: true,
                select: function (start, end, allDay) {
                    if (typeof window.resetEventModal === 'function') {
                        window.resetEventModal();
                    }
                    var d = moment(start).format('YYYY-MM-DD');
                    $('#apptStartTime').val(d);
                    $('#apptEndTime').val(d);
                    $('#apptAllDay').prop('checked', !!allDay);
                    $('#when').text(d);
                    $('#event_modal').modal('show');
                    calendar.fullCalendar('unselect');
                },
                eventClick: function (event, element) {
                    if (event.type === 'manual') {
                        $('#calendar_events_id').val(event.cal_id);
                        $('#ev_title').val(event.title);
                        $('#ev_category').val(event.ev_category || 'project');
                        $('#ev_related').val(event.ev_related_id || '');
                        if ($('#ev_related').val() === null) { $('#ev_related').val(''); }
                        $('#apptStartTime').val(moment(event.start).format('YYYY-MM-DD'));
                        $('#apptEndTime').val(event.end ? moment(event.end).format('YYYY-MM-DD') : moment(event.start).format('YYYY-MM-DD'));
                        $('#ev_start_time').val(event.ev_start_time || '');
                        $('#ev_end_time').val(event.ev_end_time || '');
                        $('#apptAllDay').prop('checked', !!event.ev_allday);
                        $('#ev_notify').prop('checked', !!event.ev_notify);
                        $('#ev_notes').val(event.ev_notes || '');
                        $('#evModalTitle').text('Edit event');
                        $('#evSave').text('Save changes');
                        $('#evDelete').show();
                        $('#when').text(moment(event.start).format('YYYY-MM-DD'));
                        $('#event_modal').modal('show');
                        return false;
                    }
                },
                eventSources: [{
                        events: [
                            <?php
                            if ($role == 1) {
                                if (config_item('payments_on_calendar') == 'on') {
                                    if (!empty($searchType) && $searchType == 'payments' || !empty($searchType) && $searchType == 'all') {

                                        $payments_info = $this->invoice_model->all_payments_client_wise();
                                        //print_r($payments_info); exit;

                                        //$payments_info = $this->db->get('tbl_payments')->result();

                                    }
                                    if (!empty($payments_info)) {
                                        foreach ($payments_info as $v_payments) :
                                            //$invoice = $this->db->where(array('invoices_id' => $v_payments->invoices_id))->get('tbl_invoices')->row();
                                            //$client_info = $this->db->where(array('client_id' => $invoice->client_id))->get('tbl_client')->row();
                            ?> {
                                                title: "<?= clear_textarea_breaks($v_payments->name . " (" . client_currency($v_payments->client_id) . $v_payments->amount . ")") ?>",
                                                start: '<?= date('Y-m-d', strtotime($v_payments->payment_date)) ?>',
                                                end: '<?= date('Y-m-d', strtotime($v_payments->payment_date)) ?>',
                                                color: '<?= config_item('payments_color') ?>',
                                                url: '<?= base_url() ?>admin/invoice/manage_invoice/payments_details/<?= $v_payments->payments_id ?>'
                                            },
                            <?php
                                        endforeach;
                                    }
                                }
                            }
                            ?>
                        ],
                    },
                    {
                        events: [<?php
                                    if (config_item('invoice_on_calendar') == 'on') {
                                        if (!empty($searchType) && $searchType == 'invoices' || !empty($searchType) && $searchType == 'all') {
                                            $invoice_info = $this->admin_model->get_permission('tbl_invoices');
                                        }
                                        if (!empty($invoice_info)) {
                                            foreach ($invoice_info as $v_invoice) :
                                    ?> {
                                            title: "<?php echo clear_textarea_breaks($v_invoice->reference_no) ?>",
                                            start: '<?= date('Y-m-d', strtotime($v_invoice->due_date)) ?>',
                                            end: '<?= date('Y-m-d', strtotime($v_invoice->due_date)) ?>',
                                            color: '<?= config_item('invoice_color') ?>',
                                            url: '<?= base_url() ?>admin/invoice/manage_invoice/invoice_details/<?= $v_invoice->invoices_id ?>'
                                        },
                            <?php endforeach;
                                        }
                                    } ?>
                        ],
                    },

                    {
                        events: [<?php
                                    if (config_item('estimate_on_calendar') == 'on') {
                                        if (!empty($searchType) && $searchType == 'estimates' || !empty($searchType) && $searchType == 'all') {
                                            $estimates_info = $this->admin_model->get_permission('tbl_estimates');
                                        }
                                        if (!empty($estimates_info)) {
                                            foreach ($estimates_info as $v_estimates) :
                                    ?> {
                                            title: "<?php echo clear_textarea_breaks($v_estimates->reference_no) ?>",
                                            start: '<?= date('Y-m-d', strtotime($v_estimates->due_date)) ?>',
                                            end: '<?= date('Y-m-d', strtotime($v_estimates->due_date)) ?>',
                                            color: '<?= config_item('estimate_color') ?>',
                                            url: '<?= base_url() ?>admin/estimates/create/estimates_details/<?= $v_estimates->estimates_id ?>'
                                        },
                            <?php endforeach;
                                        }
                                    } ?>
                        ],
                    },

                    {
                        events: [<?php
                                    if (config_item('project_on_calendar') == 'on') {
                                        if (!empty($searchType) && $searchType == 'projects' || !empty($searchType) && $searchType == 'milestones' || !empty($searchType) && $searchType == 'all') {
                                            $project_info = $this->admin_model->get_permission('tbl_project');
                                        }
                                        if (!empty($project_info)) {
                                            foreach ($project_info as $v_project) :
                                                if (!empty($searchType) && $searchType == 'projects' || !empty($searchType) && $searchType == 'all') {
                                                    if (!empty($v_project)) {
                                    ?> {
                                                    title: "<?php echo clear_textarea_breaks($v_project->project_name) ?>",
                                                    start: '<?= date('Y-m-d', strtotime($v_project->end_date)) ?>',
                                                    end: '<?= date('Y-m-d', strtotime($v_project->end_date)) ?>',
                                                    color: '<?= config_item('project_color') ?>',
                                                    url: '<?= base_url() ?>admin/projects/project_details/<?= $v_project->project_id ?>'
                                                },
                                        <?php }
                                                } ?>
                                    ],
                    },

                    {
                        events: [<?php if (config_item('milestone_on_calendar') == 'on') {
                                                    if (!empty($searchType) && $searchType == 'milestones' || !empty($searchType) && $searchType == 'all') {
                                                        $milestone_info = $this->db->where(array('project_id' => $v_project->project_id))->get('tbl_milestones')->result();
                                                    }
                                                    if (!empty($milestone_info)) {
                                                        foreach ($milestone_info as $v_milestone) :
                                    ?> {
                                            title: '<?php echo clear_textarea_breaks($v_milestone->milestone_name) ?>',
                                            start: '<?= date('Y-m-d', strtotime($v_milestone->end_date)) ?>',
                                            end: '<?= date('Y-m-d', strtotime($v_milestone->end_date)) ?>',
                                            color: '<?= config_item('milestone_color') ?>',
                                            url: '<?= base_url() ?>admin/projects/project_details/<?= $v_project->project_id ?>/5'
                                        },

                        <?php endforeach;
                                                    }
                                                }
                                            endforeach;
                                        }
                                    } ?>
                        ],
                    },

                    {
                        events: [<?php
                                    if (config_item('tasks_on_calendar') == 'on') {
                                        if (!empty($searchType) && ($searchType == 'tasks' || $searchType == 'all')) {
                                            $task_where = "(tbl_task.task_status != 'completed')";
                                            $task_info = $this->admin_model->get_permission('tbl_task', $task_where);
                                            // Status colors mapping
                                            $status_colors = [
                                                'not_started'         => '#23b7e5', // Info
                                                'in_progress'         => '#337ab7', // Primary/Blue
                                                'deferred'            => '#f05050', // Danger
                                                'waiting_for_someone' => '#ff902b', // Warning
                                                'completed'           => '#27c24c', // Success
                                            ];
                                        }
                                        if (!empty($task_info)) {
                                            foreach ($task_info as $v_task) :
                                                if (!empty($v_task->payment_type) && $v_task->payment_type != 'none') {
                                                    // Recurring logic similar to expense_schedule
                                                    $year_end = strtotime('+5 years');
                                                    $base_date = new DateTime($v_task->due_date);
                                                    $original_day = (int) $base_date->format('d');
                                                    $current = clone $base_date;

                                                    // Performance limit for daily recurring tasks
                                                    $task_end_limit = $year_end;
                                                    if ($v_task->payment_type === 'daily') {
                                                        $task_end_limit = strtotime('+60 days');
                                                    }

                                                    while ($current->getTimestamp() <= $task_end_limit) {
                                                        $event_date_str = $current->format('Y-m-d');
                                    ?> {
                                                    title: "<?php echo clear_textarea_breaks($v_task->task_name) ?>",
                                                    start: '<?= $event_date_str ?>',
                                                    end: '<?= $event_date_str ?>',
                                                    color: '<?= $status_colors[$v_task->task_status] ?? config_item('tasks_color') ?>',
                                                    url: '<?= base_url() ?>admin/tasks/details/<?= $v_task->task_id ?>'
                                                },
                                            <?php
                                                        if ($v_task->payment_type === 'daily') {
                                                            $current->modify('+1 day');
                                                        } elseif ($v_task->payment_type === 'yearly') {
                                                            $current->modify('+1 year');
                                                        } elseif ($v_task->payment_type === 'monthly') {
                                                            $current->modify('first day of next month');
                                                            $yr = $current->format('Y');
                                                            $mo = $current->format('m');
                                                            $days_in_next_month = (int) $current->format('t');
                                                            $target_day = min($original_day, $days_in_next_month);
                                                            $current = new DateTime(sprintf('%s-%s-%02d', $yr, $mo, $target_day));
                                                        } elseif ($v_task->payment_type === 'bi-monthly') {
                                                            $current->modify('first day of next month');
                                                            $current->modify('+1 month');
                                                            $yr = $current->format('Y');
                                                            $mo = $current->format('m');
                                                            $days_in_next_month = (int) $current->format('t');
                                                            $target_day = min($original_day, $days_in_next_month);
                                                            $current = new DateTime(sprintf('%s-%s-%02d', $yr, $mo, $target_day));
                                                        } elseif ($v_task->payment_type === 'quarterly') {
                                                            $current->modify('first day of next month');
                                                            $current->modify('+2 months');
                                                            $yr = $current->format('Y');
                                                            $mo = $current->format('m');
                                                            $days_in_next_month = (int) $current->format('t');
                                                            $target_day = min($original_day, $days_in_next_month);
                                                            $current = new DateTime(sprintf('%s-%s-%02d', $yr, $mo, $target_day));
                                                        } else {
                                                            break;
                                                        }
                                                    }
                                                } else {
                                                    // Standard non-recurring task
                                            ?> {
                                                title: "<?php echo clear_textarea_breaks($v_task->task_name) ?>",
                                                start: '<?= date('Y-m-d', strtotime($v_task->due_date)) ?>',
                                                end: '<?= date('Y-m-d', strtotime($v_task->due_date)) ?>',
                                                color: '<?= $status_colors[$v_task->task_status] ?? config_item('tasks_color') ?>',
                                                url: '<?= base_url() ?>admin/tasks/details/<?= $v_task->task_id ?>'
                                            },
                            <?php }
                                            endforeach;
                                        }
                                    } ?>
                        ],
                    },

                    {
                        events: [<?php if (config_item('bugs_on_calendar') == 'on') {
                                        if (!empty($searchType) && $searchType == 'bugs' || !empty($searchType) && $searchType == 'all') {
                                            $bug_info = $this->admin_model->get_permission('tbl_bug');
                                        }
                                        if (!empty($bug_info)) {
                                            foreach ($bug_info as $v_bug) : ?> {
                                            title: "<?php echo clear_textarea_breaks($v_bug->bug_title) ?>",
                                            start: '<?= date('Y-m-d', strtotime($v_bug->created_time)) ?>',
                                            end: '<?= date('Y-m-d', strtotime($v_bug->created_time)) ?>',
                                            color: '<?= config_item('bugs_color') ?>',
                                            url: '<?= base_url() ?>admin/bugs/view_bug_details/<?= $v_bug->bug_id ?>'
                                        },
                            <?php endforeach;
                                        }
                                    } ?>
                        ],
                    },
                    {
                        events: [<?php
                                    if (config_item('opportunities_on_calendar') == 'on') {
                                        if (!empty($searchType) && $searchType == 'opportunities' || !empty($searchType) && $searchType == 'all') {
                                            $opportunity_info = $this->admin_model->get_permission('tbl_opportunities');
                                        }
                                        if (!empty($opportunity_info)) {
                                            foreach ($opportunity_info as $v_opportunity) :
                                                if (!empty($v_opportunity)) {
                                    ?> {
                                                title: "<?php echo clear_textarea_breaks($v_opportunity->opportunity_name) ?>",
                                                start: '<?= date('Y-m-d', strtotime($v_opportunity->close_date)) ?>',
                                                end: '<?= date('Y-m-d', strtotime($v_opportunity->close_date)) ?>',
                                                color: '<?= config_item('opportunities_color') ?>',
                                                url: '<?= base_url() ?>admin/opportunities/opportunity_details/<?= $v_opportunity->opportunities_id ?>'
                                            },
                                            {
                                                title: "<?php echo clear_textarea_breaks($v_opportunity->next_action) ?>",
                                                start: '<?= date('Y-m-d', strtotime($v_opportunity->next_action_date)) ?>',
                                                end: '<?= date('Y-m-d', strtotime($v_opportunity->next_action_date)) ?>',
                                                color: '<?= config_item('opportunities_color') ?>',
                                                url: '<?= base_url() ?>admin/opportunities/opportunity_details/<?= $v_opportunity->opportunities_id ?>'
                                            },
                                        <?php }
                                        ?>
                                    ]
                    },
                    {
                        events: [<?php $opportunity_meetings = $this->db->where('opportunities_id', $v_opportunity->opportunities_id)->get('tbl_mettings')->result();
                                                $opportunity_calls = $this->db->where('opportunities_id', $v_opportunity->opportunities_id)->get('tbl_calls')->result();

                                                foreach ($opportunity_calls as $v_o_calls) :
                                    ?> {
                                    title: '<?php echo clear_textarea_breaks($v_o_calls->call_summary) ?>',
                                    start: '<?= date('Y-m-d', strtotime($v_o_calls->date)) ?>',
                                    end: '<?= date('Y-m-d', strtotime($v_o_calls->date)) ?>',
                                    color: '<?= config_item('opportunities_color') ?>',
                                    url: '<?= base_url() ?>admin/opportunities/opportunity_details/<?= $v_opportunity->opportunities_id ?>/2'
                                },
                            <?php endforeach;
                                                foreach ($opportunity_meetings as $v_o_meetings) :
                            ?> {
                                    title: '<?php echo clear_textarea_breaks($v_o_meetings->meeting_subject) ?>',
                                    start: '<?= date('Y-m-d H:i:s', ($v_o_meetings->start_date)) ?>',
                                    end: '<?= date('Y-m-d H:i:s', ($v_o_meetings->end_date)) ?>',
                                    color: '<?= config_item('opportunities_color') ?>',
                                    url: '<?= base_url() ?>admin/opportunities/opportunity_details/<?= $v_opportunity->opportunities_id ?>/3'
                                },
                            <?php endforeach; ?>

                        <?php endforeach;
                                        }
                                    } ?>
                        ]
                    }, {
                        events: [<?php
                                    if (config_item('leads_on_calendar') == 'on') {
                                        if (!empty($searchType) && $searchType == 'leads' || !empty($searchType) && $searchType == 'all') {
                                            $leads_info = $this->admin_model->get_permission('tbl_leads');
                                        }
                                        if (!empty($leads_info)) {
                                            foreach ($leads_info as $v_leads) :
                                                if (!empty($v_leads)) {
                                    ?> {
                                                title: "<?php echo clear_textarea_breaks($v_leads->lead_name) ?>",
                                                start: '<?= date('Y-m-d', strtotime($v_leads->created_time)) ?>',
                                                end: '<?= date('Y-m-d', strtotime($v_leads->created_time)) ?>',
                                                color: '<?= config_item('leads_color') ?>',
                                                url: '<?= base_url() ?>admin/leads/leads_details/<?= $v_leads->leads_id ?>'
                                            }
                                        <?php }
                                        ?>
                                    ]
                    },
                    {
                        events: [<?php $opportunity_meetings = $this->db->where('leads_id', $v_leads->leads_id)->get('tbl_mettings')->result();
                                                $opportunity_calls = $this->db->where('leads_id', $v_leads->leads_id)->get('tbl_calls')->result();

                                                foreach ($opportunity_calls as $v_l_calls) :
                                    ?> {
                                    title: '<?php echo clear_textarea_breaks($v_l_calls->call_summary) ?>',
                                    start: '<?= date('Y-m-d', strtotime($v_l_calls->date)) ?>',
                                    end: '<?= date('Y-m-d', strtotime($v_l_calls->date)) ?>',
                                    color: '<?= config_item('opportunities_color') ?>',
                                    url: '<?= base_url() ?>admin/opportunities/opportunity_details/<?= $v_leads->leads_id ?>/2'
                                },
                            <?php endforeach;
                                                foreach ($opportunity_meetings as $v_l_meetings) :
                            ?> {
                                    title: '<?php echo clear_textarea_breaks($v_l_meetings->meeting_subject) ?>',
                                    start: '<?= date('Y-m-d H:i:s', ($v_l_meetings->start_date)) ?>',
                                    end: '<?= date('Y-m-d H:i:s', ($v_l_meetings->end_date)) ?>',
                                    color: '<?= config_item('opportunities_color') ?>',
                                    url: '<?= base_url() ?>admin/opportunities/opportunity_details/<?= $v_leads->leads_id ?>/3'
                                },
                            <?php endforeach; ?>

                        <?php endforeach;
                                        }
                                    } ?>
                        ]
                    },
                    {
                        events: [<?php
                                    if (config_item('holiday_on_calendar') == 'on') {
                                        if (!empty($searchType) && $searchType == 'holiday' || !empty($searchType) && $searchType == 'all') {
                                            $holiday_info = $this->db->get('tbl_holiday')->result();
                                        }
                                        if (!empty($holiday_info)) {
                                            foreach ($holiday_info as $v_holiday) :
                                    ?> {
                                            title: "<?php echo clear_textarea_breaks($v_holiday->event_name) ?>",
                                            start: '<?= date('Y-m-d', strtotime($v_holiday->start_date)) ?>',
                                            end: '<?= date('Y-m-d', strtotime($v_holiday->end_date)) ?>',
                                            color: '<?= $v_holiday->color ?>',
                                            url: '<?= base_url() ?>admin/holiday/index/<?= $v_holiday->holiday_id ?>'
                                        },
                            <?php endforeach;
                                        }
                                    } ?>
                        ],
                    },
                    {
                        events: [<?php if (config_item('goal_tracking_on_calendar') == 'on') {
                                        if (!empty($searchType) && $searchType == 'goal' || !empty($searchType) && $searchType == 'all') {
                                            $all_goal_tracking = $this->admin_model->get_permission('tbl_goal_tracking');
                                        }
                                        if (!empty($all_goal_tracking)) {
                                            foreach ($all_goal_tracking as $v_goal_tracking) : ?> {
                                            title: "<?php echo clear_textarea_breaks($v_goal_tracking->subject) ?>",
                                            start: '<?= date('Y-m-d', strtotime($v_goal_tracking->end_date)) ?>',
                                            end: '<?= date('Y-m-d', strtotime($v_goal_tracking->end_date)) ?>',
                                            color: '<?= config_item('goal_tracking_color') ?>',
                                            url: '<?= base_url() ?>admin/goal_tracking/goal_details/<?= $v_goal_tracking->goal_tracking_id ?>'
                                        },
                            <?php endforeach;
                                        }
                                    } ?>
                        ],
                    },
                    {
                        events: [<?php if (config_item('absent_on_calendar') == 'on') {
                                        if (!empty($searchType) && $searchType == 'absent' || !empty($searchType) && $searchType == 'all') {
                                            //$absent_info = $this->db->where('attendance_status', '0')->get('tbl_attendance')->result();
                                            $absent_info = $this->db->query("SELECT tbl_account_details.fullname,tbl_account_details.user_id, tbl_attendance.date_in 
                                         FROM tbl_attendance
                                         LEFT  JOIN tbl_account_details ON tbl_attendance.user_id = tbl_account_details.user_id
                                         WHERE tbl_attendance.attendance_status = 0")->result();
                                        }
                                        if (!empty($absent_info)) {
                                            foreach ($absent_info as $v_absent) {
                                    ?> {
                                            title: "<?php echo clear_textarea_breaks($v_absent->fullname) ?>",
                                            start: '<?= date('Y-m-d', strtotime($v_absent->date_in)) ?>',
                                            end: '<?= date('Y-m-d', strtotime($v_absent->date_in)) ?>',
                                            color: '<?= config_item('absent_color') ?>',
                                            url: '<?= base_url() ?>admin/user/user_details/<?= $v_absent->user_id ?>'
                                        },
                            <?php
                                            }
                                        }
                                    } ?>
                        ],
                    }, {
                        events: [<?php if (config_item('on_leave_on_calendar') == 'on') {
                                        if (!empty($searchType) && $searchType == 'on_leave' || !empty($searchType) && $searchType == 'all') {
                                            $leave_info = $this->db->query("SELECT tbl_account_details.fullname, tbl_account_details.user_id, tbl_attendance.date_in 
                                         FROM tbl_attendance
                                         LEFT  JOIN tbl_account_details ON tbl_attendance.user_id = tbl_account_details.user_id
                                         WHERE tbl_attendance.attendance_status = 3")->result();
                                        }
                                        if (!empty($leave_info)) {
                                            foreach ($leave_info as $v_leave) :

                                                $l_start_day = date('d', strtotime($v_leave->date_in));
                                                $l_smonth = date('n', strtotime($v_leave->date_in));
                                                $l_start_month = $l_smonth - 1;
                                                $l_start_year = date('Y', strtotime($v_leave->date_in));
                                                $l_end_year = date('Y', strtotime($v_leave->date_in));
                                                $l_end_day = date('d', strtotime($v_leave->date_in));
                                                $l_emonth = date('n', strtotime($v_leave->date_in));
                                                $l_end_month = $l_emonth - 1; ?> {
                                            title: "<?php echo clear_textarea_breaks($v_leave->fullname) ?>",
                                            start: '<?= date('Y-m-d', strtotime($v_leave->date_in)) ?>',
                                            end: '<?= date('Y-m-d', strtotime($v_leave->date_in)) ?>',
                                            color: '<?= config_item('on_leave_color') ?>',
                                            url: '<?= base_url() ?>admin/user/user_details/<?= $v_leave->user_id ?>'
                                        },
                            <?php
                                            endforeach;
                                        }
                                    } ?>
                        ],
                    },
                    {
                        events: [<?php
                                    if (config_item('expense_schedule_on_calendar') == 'on') {
                                        $expense_info = $this->db->get('expenses')->result();
                                        if (!empty($expense_info)) {
                                            if (empty($searchType) || $searchType == 'all' || $searchType == 'expenses') {
                                                $year_end = strtotime('+5 years');
                                                foreach ($expense_info as $v_expense) {
                                                    $base_date = new DateTime($v_expense->last_payment_date);
                                                    $original_day = (int) $base_date->format('d');
                                                    $current = clone $base_date;

                                                    // Performance limit: Only project daily expenses 60 days into the future
                                                    $expense_end_limit = $year_end;
                                                    if ($v_expense->payment_type === 'daily') {
                                                        $expense_end_limit = strtotime('+60 days');
                                                    }

                                                    while ($current->getTimestamp() <= $expense_end_limit) {
                                                        $event_date_str = $current->format('Y-m-d');
                                    ?> {
                                                    title: "<?= clear_textarea_breaks($v_expense->task_name . ' ($' . number_format($v_expense->amount, 2) . ')') ?>",
                                                    start: '<?= $event_date_str ?>',
                                                    end: '<?= $event_date_str ?>',
                                                    color: '<?= config_item('expense_schedule_color') ?: '#fb6b5b' ?>',
                                                    url: '<?= base_url('admin/expenses') ?>'
                                                },
                            <?php
                                                        // Increment for the next plotted visual loop natively
                                                        if ($v_expense->payment_type === 'daily') {
                                                            $current->modify('+1 day');
                                                        } elseif ($v_expense->payment_type === 'yearly') {
                                                            $current->modify('+1 year');
                                                        } elseif ($v_expense->payment_type === 'monthly') {
                                                            $current->modify('first day of next month');
                                                            $yr = $current->format('Y');
                                                            $mo = $current->format('m');
                                                            $days_in_next_month = (int) $current->format('t');
                                                            $target_day = min($original_day, $days_in_next_month);
                                                            $current = new DateTime(sprintf('%s-%s-%02d', $yr, $mo, $target_day));
                                                        } elseif ($v_expense->payment_type === 'bi-monthly') {
                                                            // Advance by 2 months and clamp day-of-month when necessary.
                                                            $current->modify('first day of next month');
                                                            $current->modify('+1 month');
                                                            $yr = $current->format('Y');
                                                            $mo = $current->format('m');
                                                            $days_in_next_month = (int) $current->format('t');
                                                            $target_day = min($original_day, $days_in_next_month);
                                                            $current = new DateTime(sprintf('%s-%s-%02d', $yr, $mo, $target_day));
                                                        } elseif ($v_expense->payment_type === 'quarterly') {
                                                            // Advance by 3 months and clamp day-of-month when necessary.
                                                            $current->modify('first day of next month');
                                                            $current->modify('+2 months');
                                                            $yr = $current->format('Y');
                                                            $mo = $current->format('m');
                                                            $days_in_next_month = (int) $current->format('t');
                                                            $target_day = min($original_day, $days_in_next_month);
                                                            $current = new DateTime(sprintf('%s-%s-%02d', $yr, $mo, $target_day));
                                                        } else {
                                                            break; // Safety break
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                            ?>
                        ],
                    },
                    {
                        events: [
                        <?php if (config_item('domain_on_calendar') == 'on') {
                            $domain_events = $this->domain_model->get_calendar_events();
                            if (!empty($domain_events)) {
                                foreach ($domain_events as $domain_event) {
                                    $status_label = $domain_event['status'] === 'expired' ? ' (EXPIRED)' : ' (Expiring: ' . $domain_event['days_left'] . ' days)';
                        ?> {
                                        title: "<?= clear_textarea_breaks($domain_event['title']) . $status_label ?>",
                                        start: "<?= $domain_event['start'] ?>",
                                        end: "<?= $domain_event['end'] ?>",
                                        color: "<?= $domain_event['color'] ?>",
                                        url: "<?= $domain_event['url'] ?>",
                                        type: "domain"
                                    },
                                    <?php }
                            }
                        } ?>
                        <?php if (config_item('hosting_on_calendar') == 'on') {
                                $hosting_events = $this->hosting_model->get_calendar_events();
                                if (!empty($hosting_events)) {
                                    foreach ($hosting_events as $hosting_event) {
                                        $status_label = $hosting_event['status'] === 'expired' ? ' (EXPIRED)' : ' (Expiring: ' . $hosting_event['days_left'] . ' days)';
                            ?> {
                                        title: "<?= clear_textarea_breaks($hosting_event['title']) . $status_label ?>",
                                        start: "<?= $hosting_event['start'] ?>",
                                        end: "<?= $hosting_event['end'] ?>",
                                        color: "<?= $hosting_event['color'] ?>",
                                        url: "<?= $hosting_event['url'] ?>",
                                        type: "hosting"
                                    },
                        <?php }
                                }
                            } ?>
                        <?php if (config_item('billing_on_calendar') == 'on') {
                                $this->load->model('billing_model');
                                $billing_events = $this->billing_model->get_calendar_events();
                                if (!empty($billing_events)) {
                                    foreach ($billing_events as $billing_event) {
                                        $status_label = $billing_event['status'] === 'expired' ? ' (EXPIRED)' : ' (Expiring: ' . $billing_event['days_left'] . ' days)';
                            ?> {
                                        title: "<?= clear_textarea_breaks($billing_event['title']) . $status_label ?>",
                                        start: "<?= $billing_event['start'] ?>",
                                        end: "<?= $billing_event['end'] ?>",
                                        color: "<?= $billing_event['color'] ?>",
                                        url: "<?= $billing_event['url'] ?>",
                                        type: "billing"
                                    },
                        <?php }
                                }
                            } ?>
                        <?php
                        // Manual events (tbl_calendar_events)
                        $manual_events = $this->db->order_by('start_date', 'ASC')->get('tbl_calendar_events')->result();
                        $manual_colors = array(
                            'project' => config_item('project_color'),
                            'milestone' => config_item('milestone_color'),
                            'tasks' => config_item('tasks_color'),
                            'bugs' => config_item('bugs_color'),
                            'invoice' => config_item('invoice_color'),
                            'payments' => config_item('payments_color'),
                            'holiday' => config_item('holiday_color'),
                            'on_leave' => config_item('on_leave_color'),
                        );
                        $manual_filter_map = array(
                            'projects' => 'project',
                            'milestones' => 'milestone',
                            'tasks' => 'tasks',
                            'bugs' => 'bugs',
                            'invoices' => 'invoice',
                            'payments' => 'payments',
                            'holiday' => 'holiday',
                            'on_leave' => 'on_leave',
                        );
                        $manual_filter_cat = null;
                        if (!empty($searchType) && $searchType != 'all') {
                            $manual_filter_cat = !empty($manual_filter_map[$searchType]) ? $manual_filter_map[$searchType] : '__none__';
                        }
                        if (!empty($manual_events)) {
                            foreach ($manual_events as $me) :
                                if ($manual_filter_cat !== null && $me->category !== $manual_filter_cat) {
                                    continue;
                                }
                                $me_color = !empty($manual_colors[$me->category]) ? $manual_colors[$me->category] : '#0d9488';
                                $me_end = !empty($me->end_date) ? $me->end_date : $me->start_date;
                                $me_start_dt = (!$me->all_day && trim((string) $me->start_time) !== '') ? strtotime($me->start_date . ' ' . $me->start_time) : false;
                                $me_start = ($me_start_dt !== false) ? date('Y-m-d H:i:s', $me_start_dt) : $me->start_date;
                                $me_emit_end = true;
                                $me_end_value = $me_end;
                                if (!$me->all_day && trim((string) $me->end_time) !== '' && !empty($me->end_date)) {
                                    $me_end_dt = strtotime($me->end_date . ' ' . $me->end_time);
                                    if ($me_end_dt !== false) {
                                        $me_end_value = date('Y-m-d H:i:s', $me_end_dt);
                                    }
                                } elseif ($me_start_dt !== false && trim((string) $me->end_time) === '') {
                                    $me_emit_end = false;
                                }
                                ?> {
                                    title: <?= json_encode($me->title, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?: '""' ?>,
                                    start: "<?= $me_start ?>",
                                    <?php if ($me_emit_end) { ?>end: "<?= $me_end_value ?>",
                                    <?php } ?>color: "<?= $me_color ?>",
                                    allDay: <?= $me->all_day ? 'true' : 'false' ?>,
                                    type: "manual",
                                    cal_id: <?= (int) $me->calendar_events_id ?>,
                                    ev_category: "<?= $me->category ?>",
                                    ev_related_id: <?= $me->related_id ? (int) $me->related_id : 'null' ?>,
                                    ev_start_time: <?= json_encode((string) $me->start_time, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?: '""' ?>,
                                    ev_end_time: <?= json_encode((string) $me->end_time, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?: '""' ?>,
                                    ev_allday: <?= $me->all_day ? 'true' : 'false' ?>,
                                    ev_notify: <?= $me->notify ? 'true' : 'false' ?>,
                                    ev_notes: <?= json_encode((string) $me->notes, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?: '""' ?>
                                },
                                <?php
                            endforeach;
                        }
                        ?>
                        ]
                    }
                ]
            });
            function updateCalendarKpis() {
                if (typeof moment === 'undefined' || typeof calendar === 'undefined' || !calendar) { return; }
                var evs = calendar.fullCalendar('clientEvents');
                var now = moment();
                var mStart = now.clone().startOf('month');
                var mEnd = now.clone().endOf('month');
                var wStart = now.clone().startOf('day');
                var wEnd = now.clone().add(7, 'days');
                var inMonth = 0, inWeek = 0;
                for (var i = 0; i < evs.length; i++) {
                    var s = moment(evs[i].start);
                    if (!s.isBefore(mStart) && !s.isAfter(mEnd)) { inMonth++; }
                    if (!s.isBefore(wStart) && !s.isAfter(wEnd)) { inWeek++; }
                }
                $('#kpiEvents').text(inMonth);
                $('#kpiUpcoming').text(inWeek);
            }

            $('#calPrev').on('click', function () { calendar.fullCalendar('prev'); });
            $('#calToday').on('click', function () { calendar.fullCalendar('today'); });
            $('#calNext').on('click', function () { calendar.fullCalendar('next'); });
            $('#calSeg button').on('click', function () {
                $(this).addClass('on').siblings().removeClass('on');
                calendar.fullCalendar('changeView', $(this).data('view'));
            });
            updateCalendarKpis();
        }
    });
</script>
<?php /*Comment in my JavaScript*/ ?>

<script src='<?= base_url() ?>assets/plugins/fullcalendar/moment.min.js'></script>
<script src='<?= base_url() ?>assets/plugins/fullcalendar/fullcalendar.min.js'></script>
<script src="<?= base_url() ?>assets/plugins/fullcalendar/gcal.min.js"></script>