
<style type="text/css">
.datepicker {
z-index: 1151 !important;
}

.mt-sm {
font-size: 14px;
}

/* FullCalendar Popover Overflow Fix */
.fc-popover {
    max-height: 350px;
    overflow-y: auto;
    overflow-x: hidden;
    border-radius: 4px;
    box-shadow: 0 6px 12px rgba(0,0,0,0.175);
}

.fc-popover .fc-header {
    background: #f8f9fa;
    border-bottom: 1px solid #ddd;
    padding: 8px 12px;
    border-radius: 4px 4px 0 0;
}

.fc-popover .fc-day-grid-container {
    max-height: 280px;
    overflow-y: auto;
}

.fc-event-container {
    max-height: 180px;
    overflow-y: auto;
}

.fc-event-container .fc-day-grid-event {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.fc-popover .fc-event {
    margin-bottom: 4px !important;
    padding: 4px 6px !important;
    border-radius: 4px !important;
}

.fc-popover .fc-event-container {
    padding: 4px 8px !important;
}
/* Calendar filter dropdown - Ultimate z-index fix */
.calendar-filter {
    position: static !important;
}

.calendar-filter > .dropdown-menu {
    position: absolute !important;
    top: 100% !important;
    left: auto !important;
    right: 0 !important;
    z-index: 99999 !important;
    margin-top: 2px !important;
    max-height: 400px;
    overflow-y: auto;
}

/* Fix parent stacking context */
.panel-heading {
    position: relative !important;
    z-index: 10;
}

.panel-title {
    position: relative !important;
    z-index: 10;
}

/* Ensure calendar doesn't create new stacking issues */
.full-calender {
    position: static !important;
}

.content-body {
    position: relative;
    z-index: 1;
}

/* Fix for dropdown menu being cut off in panel-heading */
.panel-custom, 
.panel-heading, 
.panel-title, 
.pull-right, 
.pull-left {
    overflow: visible !important;
}
</style>
<?php
echo message_box('success');
echo message_box('error');

// KPI strip (real data): leave + holidays overlapping the current month
$kpi_leave = $this->db->select('COUNT(DISTINCT user_id) AS c', false)
        ->where('attendance_status', '3')
        ->where('date_in >=', date('Y-m-01'))
        ->where('date_in <=', date('Y-m-t'))
        ->get('tbl_attendance')->row()->c;
$kpi_holidays = $this->db->where('start_date <=', date('Y-m-t'))
        ->where('end_date >=', date('Y-m-01'))
        ->count_all_results('tbl_holiday');
?>
<div class="dashboard row">

    <div class="full-calender">
        <div class="clearfix visible-sm-block "></div>

        <div class="col-sm-12 mt-lg">

            <!-- KPI strip (layout C) -->
            <div class="kpi-strip">
                <div class="kpi-grid">
                    <div class="kpi-card"><div class="k-i ok">&#9636;</div><div><div class="v"><span id="kpiEvents">0</span></div><div class="l">Events this month</div></div></div>
                    <div class="kpi-card"><div class="k-i cyan">&#9200;</div><div><div class="v"><span id="kpiUpcoming">0</span></div><div class="l">Upcoming this week</div></div></div>
                    <div class="kpi-card"><div class="k-i amb">&#9873;</div><div><div class="v"><?php echo (int) $kpi_leave; ?></div><div class="l">On leave this month</div></div></div>
                    <div class="kpi-card"><div class="k-i no">&#10006;</div><div><div class="v"><?php echo (int) $kpi_holidays; ?></div><div class="l">Holidays this month</div></div></div>
                </div>
            </div>

            <!-- calendar card -->
            <section class="card cal-card">
                <div class="cal-toolbar">
                    <div class="nav-btns">
                        <button type="button" class="mini-btn" id="calPrev">&#8249;</button>
                        <button type="button" class="mini-btn" id="calToday">Today</button>
                        <button type="button" class="mini-btn" id="calNext">&#8250;</button>
                    </div>
                    <span class="cal-title" id="calTitle"><?php echo date('F Y'); ?></span>
                    <div class="seg" id="calSeg">
                        <button type="button" class="on" data-view="month">Month</button>
                        <button type="button" data-view="agendaWeek">Week</button>
                        <button type="button" data-view="agendaDay">Day</button>
                    </div>
                    <div class="tb-right">
                        <button type="button" class="btn-new" id="calNewEvent">+ New event</button>
                        <?php if (admin_head()) { ?>
                        <a class="icon-btn" title="Calendar settings" data-toggle="modal" data-target="#myModal" href="<?php echo base_url(); ?>admin/calendar/calendar_settings"><i class="fa fa-cogs"></i></a>
                        <?php } ?>
                        <div class="btn-group calendar-filter">
                            <button type="button" class="filter-pill dropdown-toggle" data-toggle="dropdown">
                                <span class="c-dot"></span><span class="filter-label"><?php echo htmlspecialchars(!empty($searchType) ? lang($searchType) : lang('all')); ?></span> <span class="caret-i">&#9662;</span>
                            </button>
                            <ul class="dropdown-menu pull-right animated zoomIn calendar-dropdown">
                                <li><a href="javascript:void(0);" class="calendar-filter-link" data-filter="all"><?php echo lang('all'); ?></a></li>
                                <?php
                                if (!empty($calendar_categories)) {
                                    $lang_keys = array(
                                        'project' => 'project',
                                        'milestone' => 'milestone',
                                        'tasks' => 'tasks',
                                        'bugs' => 'bugs',
                                        'invoice' => 'invoice',
                                        'payments' => 'payments',
                                        'estimate' => 'estimate',
                                        'opportunities' => 'opportunities',
                                        'leads' => 'leads',
                                        'goal' => 'goal_tracking',
                                        'holiday' => 'holiday',
                                        'absent' => 'absent',
                                        'on_leave' => 'on_leave',
                                        'expenses' => 'expense_schedules',
                                        'domain' => 'domains',
                                        'hosting' => 'hosting'
                                    );
                                    foreach ($calendar_categories as $cat) {
                                        $lang_key = isset($lang_keys[$cat]) ? $lang_keys[$cat] : $cat;
                                        ?>
                                        <li><a href="javascript:void(0);" class="calendar-filter-link" data-filter="<?php echo $cat; ?>"><?php echo lang($lang_key); ?></a></li>
                                        <?php
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="content-body">
                    <div class="calendar-content-body" id="my_calendar"></div>
                </div>
            </section>

        </div>
    </div>

</div>
<?php
$this->load->view("admin/calendar/my_calendar");
?>

<!-- New / edit event modal -->
<div class="modal fade" id="event_modal" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="evModalTitle">
    <div class="modal-dialog" role="document">
        <div class="modal-content cal-modal">
            <div class="m-head">
                <div><h3 id="evModalTitle">New event</h3><div class="sub" id="when">&nbsp;</div></div>
                <button type="button" class="m-x" data-dismiss="modal">&#10005;</button>
            </div>
            <form id="eventForm" method="post" action="<?php echo base_url(); ?>admin/calendar/save_event">
                <div class="m-body">
                    <input type="hidden" name="calendar_events_id" id="calendar_events_id" value="0">
                    <div class="f-row"><label for="ev_title">Event title <span class="req">*</span></label>
                        <input type="text" class="inp" name="title" id="ev_title" placeholder="e.g. Client sync - Trigon Labs" required></div>
                    <div class="f-2">
                        <div class="f-row"><label for="ev_category">Category</label>
                            <select class="inp" name="category" id="ev_category">
                                <option value="project">Project</option>
                                <option value="milestone">Milestone</option>
                                <option value="tasks" selected>Tasks</option>
                                <option value="bugs">Bugs</option>
                                <option value="invoice">Invoice</option>
                                <option value="payments">Payments</option>
                                <option value="holiday">Holiday</option>
                                <option value="on_leave">On leave</option>
                            </select></div>
                        <div class="f-row"><label for="ev_related">Related to</label>
                            <select class="inp" name="related_id" id="ev_related">
                                <option value="">None</option>
                                <?php
                                $related_projects = $this->db->order_by('project_id', 'DESC')->limit(30)->get('tbl_project')->result();
                                if (!empty($related_projects)) {
                                    foreach ($related_projects as $rp) {
                                        echo '<option value="' . (int) $rp->project_id . '">' . htmlspecialchars($rp->project_name) . '</option>';
                                    }
                                }
                                ?>
                            </select></div>
                    </div>
                    <div class="f-2">
                        <div class="f-row"><label for="apptStartTime">Start date <span class="req">*</span></label>
                            <input type="text" class="inp datepicker" name="start_date" id="apptStartTime" required></div>
                        <div class="f-row"><label for="apptEndTime">End date</label>
                            <input type="text" class="inp datepicker" name="end_date" id="apptEndTime"></div>
                    </div>
                    <div class="f-2">
                        <div class="f-row"><label for="ev_start_time">Start time</label>
                            <input type="text" class="inp" name="start_time" id="ev_start_time" placeholder="10:00 AM"></div>
                        <div class="f-row"><label for="ev_end_time">End time</label>
                            <input type="text" class="inp" name="end_time" id="ev_end_time" placeholder="11:00 AM"></div>
                    </div>
                    <div class="f-row f-checks">
                        <label class="chk"><input type="checkbox" id="apptAllDay" name="all_day" value="1"> All-day event</label>
                        <label class="chk"><input type="checkbox" id="ev_notify" name="notify" value="1" checked> Notify attendees</label>
                    </div>
                    <div class="f-row"><label for="ev_notes">Notes</label>
                        <textarea class="inp" name="notes" id="ev_notes" placeholder="Agenda, links, preparation..."></textarea></div>
                </div>
                <div class="m-foot">
                    <button type="button" class="btn-danger-ghost" id="evDelete" style="display:none">Delete</button>
                    <span class="spacer-m"></span>
                    <button type="button" class="btn-ghost" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-accent" id="evSave">Add event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        // ---- category filter (reloads with the selected source) ----
        $('.calendar-filter-link').on('click', function (e) {
            e.preventDefault();
            var filter = $(this).data('filter');
            $('.filter-label').text($(this).text());
            var searchMap = {
                'project': 'projects',
                'milestone': 'milestones',
                'tasks': 'tasks',
                'bugs': 'bugs',
                'invoice': 'invoices',
                'payments': 'payments',
                'estimate': 'estimates',
                'opportunities': 'opportunities',
                'leads': 'leads',
                'goal': 'goal',
                'holiday': 'holiday',
                'absent': 'absent',
                'on_leave': 'on_leave',
                'expenses': 'expenses',
                'domain': 'domain',
                'hosting': 'hosting'
            };
            if (filter === 'all') {
                location.href = '<?php echo base_url(); ?>admin/calendar/index/search/all';
            } else {
                var searchValue = searchMap[filter] || filter;
                location.href = '<?php echo base_url(); ?>admin/calendar/index/search/' + searchValue;
            }
        });

        // ---- event modal helpers ----
        function resetEventModal() {
            document.getElementById('eventForm').reset();
            $('#calendar_events_id').val('0');
            $('#evModalTitle').text('New event');
            $('#evSave').text('Add event');
            $('#evDelete').hide();
            $('#when').text(moment().format('YYYY-MM-DD'));
        }
        window.resetEventModal = resetEventModal;

        function pad(n) { return n < 10 ? '0' + n : '' + n; }
        function isoOf(d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); }

        $('#calNewEvent').on('click', function () {
            resetEventModal();
            var t = new Date();
            $('#apptStartTime').val(isoOf(t));
            $('#apptEndTime').val(isoOf(t));
            $('#event_modal').modal('show');
        });

        $('#eventForm').on('submit', function (e) {
            e.preventDefault();
            var $btn = $('#evSave');
            if ($btn.prop('disabled')) { return; }
            $btn.prop('disabled', true);
            var form = this;
            $.post(form.action, $(form).serialize(), function (r) {
                if (r && r.status === 'success') {
                    $('#event_modal').modal('hide');
                    location.reload();
                } else {
                    $btn.prop('disabled', false);
                    alert((r && r.message) || 'Save failed');
                }
            }, 'json').fail(function () {
                $btn.prop('disabled', false);
                alert('Save failed');
            });
        });

        $('#evDelete').on('click', function () {
            if (!confirm('Delete this event?')) { return; }
            $.post('<?php echo base_url(); ?>admin/calendar/delete_event',
                { calendar_events_id: $('#calendar_events_id').val() },
                function (r) {
                    if (r && r.status === 'success') {
                        $('#event_modal').modal('hide');
                        location.reload();
                    } else {
                        alert((r && r.message) || 'Delete failed');
                    }
                }, 'json').fail(function () { alert('Delete failed'); });
        });

        if ($.fn.datepicker) {
            $('#eventForm .datepicker').datepicker({ autoclose: true, format: 'yyyy-mm-dd' });
        }

        // settings modal: (re)initialize colorpickers each time it opens
        $('#myModal').on('loaded.bs.modal', function () {
            $('#myModal .colorpicker-component').each(function () {
                var $this = $(this);
                if ($this.data('colorpicker')) { return; }
                $this.colorpicker({ color: $this.find('input').val() });
            });
        });

        // settings modal: destroy colorpickers on close so their body-level
        // popup elements are removed instead of accumulating per reopen
        $('#myModal').on('hidden.bs.modal', function () {
            $('#myModal .colorpicker-component').each(function () {
                var $c = $(this);
                if ($c.data('colorpicker')) { $c.colorpicker('destroy'); }
            });
        });
    });
</script>


