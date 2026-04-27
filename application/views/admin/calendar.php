
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
$curency = $this->admin_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');
$leave_info = $this->db->where('attendance_status', '3')->get('tbl_attendance')->result();

?>
<div class="dashboard row">

<div class="full-calender">
<div class="clearfix visible-sm-block "></div>

<div class="col-sm-12 mt-lg">
<div class="panel panel-custom ">
<div class="panel-heading mb0 pt-sm pb-sm">
<div class="panel-title ">
<h4><?= lang('calendar') ?>
<div class="pull-right ">
<?php if (admin_head()) { ?>
<div class="pull-right ml">
<a data-toggle="modal" data-target="#myModal"
   href="<?= base_url() ?>admin/calendar/calendar_settings"
   class="text-default ml"><i class="fa fa-cogs"></i></a>
</div>
<?php } ?>
<div class="pull-left" style="position: relative; z-index: 9999;">
<div class="btn-group calendar-filter">
<button class="btn btn-xs btn-success dropdown-toggle calendar-filter-btn" data-toggle="dropdown">
    <?php
    if (!empty($searchType)) {
        echo lang($searchType);
    } else {
        echo lang('all');
    }
    ?>
    <span class="caret"></span></button>
<ul class="dropdown-menu pull-right animated zoomIn calendar-dropdown">
    <li>
        <a href="javascript:void(0);" class="calendar-filter-link" data-filter="all"><?php echo lang('all'); ?></a>
    </li>
    <?php 
    if (!empty($calendar_categories)) {
        foreach ($calendar_categories as $cat) {
            // Map category keys to language strings
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
            $lang_key = isset($lang_keys[$cat]) ? $lang_keys[$cat] : $cat;
            ?>
            <li>
                <a href="javascript:void(0);" class="calendar-filter-link" data-filter="<?php echo $cat; ?>"><?php echo lang($lang_key); ?></a>
            </li>
            <?php
        }
    }
    ?>
</ul>
</div>
</div>
</div>
</div>
</h4>

</div>
</div>



<div class="content-body">
<div class="mt-lg calendar-content-body" id="my_calendar"></div>


</div>
</div>
</div>

</div>
<?php
$this->load->view("admin/calendar/my_calendar");

?>
<!--Calendar-->

<script type="text/javascript">
$(document).ready(function() {
    // Calendar Filter Functionality
    $('.calendar-filter-link').on('click', function(e) {
        e.preventDefault();
        
        var filter = $(this).data('filter');
        
        // Update dropdown button text
        var filterText = $(this).text();
        $('.calendar-filter-btn').html(filterText + ' <span class="caret"></span>');
        
        // Filter calendar events via FullCalendar
        if (typeof calendar !== 'undefined') {
            // Get all event sources
            var allSources = calendar.fullCalendar('getEventSources');
            
            // Hide all events first
            calendar.fullCalendar('removeEventSource', allSources);
            
            if (filter === 'all') {
                // Reload all sources - we need to reload the page or store original sources
                // For now, we'll use the event filtering approach
                location.href = '<?php echo base_url(); ?>admin/calendar/index/search/all';
            } else {
                // Filter events by type - reload with specific search
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
                var searchValue = isset(searchMap[filter]) ? searchMap[filter] : filter;
                location.href = '<?php echo base_url(); ?>admin/calendar/index/search/' + searchValue;
            }
        }
    });
    
    // Alternative client-side filtering using event source filtering
    // This works if calendar is initialized with named sources
});
</script>


