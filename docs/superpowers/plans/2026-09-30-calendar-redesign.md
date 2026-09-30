# Calendar Page Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Redesign the admin calendar page (layout C: KPI strip + full-width calendar card) and both modals (Calendar settings, New event) to the approved dashboard design, including new real CRUD for manual calendar events.

**Architecture:** Restyle FullCalendar 3.8 via a rewritten `calendar_modern.css` (dashboard tokens) + custom toolbar markup in the view; add `tbl_calendar_events` table with `save_event`/`delete_event` JSON endpoints; inject a real `#event_modal` markup the already-existing (currently dead) `select` handler targets; redesign `calendar_settings.php` inside the existing remote-into-`#myModal` flow.

**Tech Stack:** CodeIgniter 3 (PHP 8.3), FullCalendar 3.8 (jQuery), Bootstrap 3 modals, MariaDB, plain CSS with dashboard tokens.

**Design source of truth:** `.superpowers/brainstorm/108-1790752228/content/calendar-full.html` (approved mockup) and `docs/superpowers/specs/2026-09-30-calendar-redesign-design.md`.

**Environment:** PHP `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe`, MySQL `C:\laragon\bin\mysql\mariadb-12.2.2-winx64\bin\mysql.exe -u root tic_crm`, base `http://localhost/complete_crm`, login `forid`/`Admin@123`, branch `sakib`. CSRF is OFF (`config.php`), session cookie `uniquecoder`.

**Verified facts (do not re-investigate):**
- Settings modal flow: gear `<a data-toggle="modal" data-target="#myModal" href=".../calendar_settings">` → Bootstrap remote-loads the response into the page-level `#myModal .modal-content` (`_layout_main.php:326` renders empty `#myModal`; `Calendar::calendar_settings()` echoes the settings view via `load->view(..., FALSE)` side effect, then `_layout_modal` boilerplate follows). Redesign = edit `calendar_settings.php` output only; keep form `id="from_items"`, action, all `name` attributes, `.colorpicker-component` markup, and the colorpicker `<link>/<script>` at its top.
- FC 3.8 month DOM inside `#my_calendar`: `.fc-toolbar.fc-header-toolbar` (built-in — must be hidden), `th.fc-day-header > span`, `td.fc-day-top > span.fc-day-number`, `td.fc-day` (event cells), `.fc-today`, `.fc-other-month`, `a.fc-day-grid-event.fc-event > .fc-content > .fc-title` with **inline** background/border colors.
- `select` handler (`my_calendar.php:33-42`) targets `#event_modal` ids that do not exist — day-click is dead today.
- Topbar "New" is a shared dropdown (`header.php:113`), not a button — the "+ New event" button goes in the calendar toolbar instead (page-scoped).
- `isset()` does not exist in JS — the category filter click handler (`calendar.php:240`) throws for every non-"all" filter today. Fixed in Task 4.
- `$leave_info` in `calendar.php:102` is overwritten by `my_calendar.php:434` before use — keep line 102 untouched (harmless), add KPI queries after it.
- Tables: `tbl_holiday(holiday_id, event_name, start_date, end_date, ...)`, `tbl_attendance(attendance_status, date_in, ...)`, `tbl_project(project_id, project_name)` (NOT tbl_projects).

---

### Task 1: Create `tbl_calendar_events`

**Files:**
- Run: SQL via mysql CLI (recorded here; no repo file needed)

- [ ] **Step 1: Create the table**

```bash
C:\laragon\bin\mysql\mariadb-12.2.2-winx64\bin\mysql.exe -u root tic_crm -e "CREATE TABLE IF NOT EXISTS tbl_calendar_events ( calendar_events_id INT(11) NOT NULL AUTO_INCREMENT, title VARCHAR(255) NOT NULL, category VARCHAR(50) NOT NULL DEFAULT 'project', start_date DATE NOT NULL, end_date DATE NULL, start_time VARCHAR(10) NULL, end_time VARCHAR(10) NULL, all_day TINYINT(1) NOT NULL DEFAULT 0, notify TINYINT(1) NOT NULL DEFAULT 0, related_type VARCHAR(30) NULL, related_id INT(11) NULL, notes TEXT NULL, created_by INT(11) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (calendar_events_id) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
```

- [ ] **Step 2: Verify**

```bash
C:\laragon\bin\mysql\mariadb-12.2.2-winx64\bin\mysql.exe -u root tic_crm -e "DESCRIBE tbl_calendar_events;"
```
Expected: 15 rows (one per column), `calendar_events_id` PRI auto_increment.

---

### Task 2: Failing e2e test for event CRUD (write FIRST)

**Files:**
- Create: `plan/_tools/calendar-event-test.php`

- [ ] **Step 1: Write the test**

```php
<?php
/**
 * Calendar manual-event CRUD e2e test (TDD companion for the calendar redesign).
 *
 * Logs in, then exercises admin/calendar/save_event + delete_event over HTTP and
 * asserts the event appears/disappears on /admin/calendar (the manual source
 * renders its title into the page). Also asserts validation and no PHP errors.
 *
 *   php plan/_tools/calendar-event-test.php [--base=http://localhost/complete_crm]
 *
 * Exits 0 when all assertions pass, 1 otherwise.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$opts = getopt('', ['base::', 'help']);
if (isset($opts['help'])) {
    echo "Usage: php plan/_tools/calendar-event-test.php [--base=URL]\n";
    exit(0);
}
$baseUrl = rtrim($opts['base'] ?? 'http://localhost/complete_crm', '/');
$user = 'forid';
$pass = 'Admin@123';

$cookieJar = (getenv('TEMP') ?: sys_get_temp_dir()) . '/tic_crm_cal_' . getmypid() . '.jar';

function request(string $method, string $url, array $post = []): array
{
    global $cookieJar;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 8,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_ENCODING       => '',
        CURLOPT_USERAGENT      => 'TIC-CRM-calendar-test/1.0',
        CURLOPT_COOKIEJAR      => $cookieJar,
        CURLOPT_COOKIEFILE     => $cookieJar,
    ]);
    if (strtoupper($method) === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $raw = curl_exec($ch);
    if ($raw === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return ['error' => $err, 'status' => 0, 'body' => ''];
    }
    $headerLen = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $body = substr($raw, $headerLen);
    curl_close($ch);
    $set = [];
    if (is_file($cookieJar)) {
        foreach (file($cookieJar, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $p = explode("\t", $line);
            if (count($p) >= 7) {
                $set[$p[5]] = $p[6];
            }
        }
    }
    return ['status' => $status, 'body' => $body, 'cookie' => $set];
}

$login = request('POST', $baseUrl . '/login', [
    'user_name' => $user,
    'password'  => $pass,
    'lat'       => '0.000000',
    'long'      => '0.000000',
]);
if (empty($login['cookie']['uniquecoder']) || strlen($login['cookie']['uniquecoder']) < 10) {
    fwrite(STDERR, "FATAL: login produced no session cookie.\n");
    exit(1);
}

$checks = [];
$titleOld = 'Plan e2e event ' . date('His');
$titleNew = $titleOld . ' edited';
$today = date('Y-m-d');
$createdId = 0;

// 1. create
$res = request('POST', $baseUrl . '/admin/calendar/save_event', [
    'title'       => $titleOld,
    'category'    => 'tasks',
    'start_date'  => $today,
    'end_date'    => $today,
    'start_time'  => '10:00 AM',
    'end_time'    => '11:00 AM',
    'all_day'     => '0',
    'notify'      => '1',
    'notes'       => 'e2e plan test',
]);
$json = json_decode($res['body'], true);
$checks['save_event creates an event'] =
    (is_array($json) && ($json['status'] ?? '') === 'success' && isset($json['id']) && (int)$json['id'] > 0)
        ? null : 'response: ' . substr($res['body'], 0, 200);
if (is_array($json) && isset($json['id'])) {
    $createdId = (int) $json['id'];
}

// 2. appears on the calendar page
$page = request('GET', $baseUrl . '/admin/calendar');
$checks['created event renders on the calendar page'] =
    strpos($page['body'], $titleOld) !== false ? null : 'title not found in page HTML';

// 3. update
if ($createdId > 0) {
    $res = request('POST', $baseUrl . '/admin/calendar/save_event', [
        'calendar_events_id' => $createdId,
        'title'              => $titleNew,
        'category'           => 'tasks',
        'start_date'         => $today,
        'end_date'           => $today,
    ]);
    $json = json_decode($res['body'], true);
    $checks['save_event updates an existing event'] =
        (is_array($json) && ($json['status'] ?? '') === 'success') ? null : 'response: ' . substr($res['body'], 0, 200);

    $page = request('GET', $baseUrl . '/admin/calendar');
    $checks['updated title replaces the old one'] =
        (strpos($page['body'], $titleNew) !== false && strpos($page['body'], $titleOld) === false)
            ? null : 'old/new title mismatch on page';
}

// 4. validation: missing title is rejected
$res = request('POST', $baseUrl . '/admin/calendar/save_event', [
    'start_date' => $today,
]);
$json = json_decode($res['body'], true);
$checks['save_event rejects a missing title'] =
    (is_array($json) && ($json['status'] ?? '') === 'error') ? null : 'expected status=error, got: ' . substr($res['body'], 0, 200);

// 5. delete
if ($createdId > 0) {
    $res = request('POST', $baseUrl . '/admin/calendar/delete_event', [
        'calendar_events_id' => $createdId,
    ]);
    $json = json_decode($res['body'], true);
    $checks['delete_event removes the event'] =
        (is_array($json) && ($json['status'] ?? '') === 'success') ? null : 'response: ' . substr($res['body'], 0, 200);

    $page = request('GET', $baseUrl . '/admin/calendar');
    $checks['deleted event no longer renders'] =
        strpos($page['body'], $titleNew) === false ? null : 'title still present after delete';
}

// 6. page health
$checks['calendar page has no PHP errors'] =
    preg_match('/Fatal error|Parse error|Uncaught\s|<b>Warning\b|<b>Notice\b/i', $page['body'])
        ? 'PHP error text present' : null;

// teardown (best effort, in case an assertion failed mid-run)
if ($createdId > 0) {
    request('POST', $baseUrl . '/admin/calendar/delete_event', ['calendar_events_id' => $createdId]);
}

$fail = 0;
echo str_repeat('=', 76) . "\n";
printf("calendar-event-test  %s\n", $baseUrl);
echo str_repeat('=', 76) . "\n";
foreach ($checks as $name => $reason) {
    if ($reason === null) {
        printf("PASS  %s\n", $name);
    } else {
        printf("FAIL  %s\n      -> %s\n", $name, $reason);
        $fail++;
    }
}
echo str_repeat('=', 76) . "\n";
printf("%d assertions — %d passed, %d failed\n", count($checks), count($checks) - $fail, $fail);
exit($fail === 0 ? 0 : 1);
```

- [ ] **Step 2: Run it — expect FAIL (endpoints don't exist yet)**

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe plan\_tools\calendar-event-test.php
```
Expected: FAIL on `save_event creates an event` (route missing → HTML 404, `json_decode` null) and the dependent assertions. This is the red state.

---

### Task 3: `save_event` / `delete_event` controller endpoints

**Files:**
- Modify: `application/controllers/admin/Calendar.php` (insert before the final closing `}` at line 145)

- [ ] **Step 1: Add the two endpoints**

Insert this code immediately before the last `}` of the class:

```php
    public function save_event()
    {
        $title = trim((string) $this->input->post('title'));
        $start_date = trim((string) $this->input->post('start_date'));
        $id = (int) $this->input->post('calendar_events_id');

        if ($title === '' || $start_date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Title and a valid start date are required')));
            return;
        }

        $row = array(
            'title'        => $title,
            'category'     => in_array($this->input->post('category'), array('project', 'milestone', 'tasks', 'bugs', 'invoice', 'payments', 'holiday', 'on_leave'), true) ? $this->input->post('category') : 'project',
            'start_date'   => $start_date,
            'end_date'     => trim((string) $this->input->post('end_date')) !== '' ? $this->input->post('end_date') : $start_date,
            'start_time'   => (string) $this->input->post('start_time'),
            'end_time'     => (string) $this->input->post('end_time'),
            'all_day'      => $this->input->post('all_day') ? 1 : 0,
            'notify'       => $this->input->post('notify') ? 1 : 0,
            'related_type' => $this->input->post('related_id') ? 'project' : null,
            'related_id'   => $this->input->post('related_id') ? (int) $this->input->post('related_id') : null,
            'notes'        => (string) $this->input->post('notes'),
        );

        if ($id > 0) {
            $this->db->where('calendar_events_id', $id)->update('tbl_calendar_events', $row);
            $message = 'Event updated';
        } else {
            $row['created_by'] = (int) $this->session->userdata('user_id');
            $row['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('tbl_calendar_events', $row);
            $id = (int) $this->db->insert_id();
            $message = 'Event created';
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('status' => 'success', 'id' => $id, 'message' => $message)));
    }

    public function delete_event()
    {
        $id = (int) $this->input->post('calendar_events_id');
        if ($id <= 0) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Missing event id')));
            return;
        }
        $this->db->where('calendar_events_id', $id)->delete('tbl_calendar_events');
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('status' => 'success', 'message' => 'Event deleted')));
    }
```

- [ ] **Step 2: Lint**

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l application\controllers\admin\Calendar.php
```
Expected: `No syntax errors detected`.

- [ ] **Step 3: Run the test — expect PASS (green)**

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe plan\_tools\calendar-event-test.php
```
Expected: `9 assertions — 9 passed, 0 failed`.

- [ ] **Step 4: Commit**

```bash
git add application/controllers/admin/Calendar.php plan/_tools/calendar-event-test.php
git commit -m "feat: calendar manual event CRUD (save_event/delete_event) + e2e test"
```

---

### Task 4: Restructure the page view (KPI strip + card toolbar + event modal)

**Files:**
- Modify: `application/views/admin/calendar.php` (lines 98-249: PHP top, markup, script block; keep lines 1-97 inline `<style>` untouched)

- [ ] **Step 1: Replace lines 98-193 (everything from `<?php` after the style block through the `$this->load->view("admin/calendar/my_calendar"); ?>` + closing divs) with this**

```php
<?php
echo message_box('success');
echo message_box('error');
$curency = $this->admin_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');
$leave_info = $this->db->where('attendance_status', '3')->get('tbl_attendance')->result();

// KPI strip (real data): leave + holidays overlapping the current month
$kpi_leave = $this->db->where('attendance_status', '3')
        ->where('date_in >=', date('Y-m-01'))
        ->where('date_in <=', date('Y-m-t'))
        ->count_all_results('tbl_attendance');
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
                                <span class="c-dot"></span><span class="filter-label"><?php echo !empty($searchType) ? lang($searchType) : lang('all'); ?></span> <span class="caret-i">&#9662;</span>
                            </button>
                            <ul class="dropdown-menu pull-right animated zoomIn calendar-dropdown">
                                <li><a href="javascript:void(0);" class="calendar-filter-link" data-filter="all"><?php echo lang('all'); ?></a></li>
                                <?php
                                if (!empty($calendar_categories)) {
                                    foreach ($calendar_categories as $cat) {
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
<div class="modal fade" id="event_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content cal-modal">
            <div class="m-head">
                <div><h3 id="evModalTitle">New event</h3><div class="sub" id="when">&nbsp;</div></div>
                <button type="button" class="m-x" data-dismiss="modal">&#10005;</button>
            </div>
            <form id="eventForm" method="post" action="<?php echo base_url(); ?>admin/calendar/save_event">
                <div class="m-body">
                    <input type="hidden" name="calendar_events_id" id="calendar_events_id" value="0">
                    <div class="f-row"><label>Event title <span class="req">*</span></label>
                        <input type="text" class="inp" name="title" id="ev_title" placeholder="e.g. Client sync - Trigon Labs" required></div>
                    <div class="f-2">
                        <div class="f-row"><label>Category</label>
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
                        <div class="f-row"><label>Related to</label>
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
                        <div class="f-row"><label>Start date <span class="req">*</span></label>
                            <input type="text" class="inp datepicker" name="start_date" id="apptStartTime" required></div>
                        <div class="f-row"><label>End date</label>
                            <input type="text" class="inp datepicker" name="end_date" id="apptEndTime"></div>
                    </div>
                    <div class="f-2">
                        <div class="f-row"><label>Start time</label>
                            <input type="text" class="inp" name="start_time" id="ev_start_time" placeholder="10:00 AM"></div>
                        <div class="f-row"><label>End time</label>
                            <input type="text" class="inp" name="end_time" id="ev_end_time" placeholder="11:00 AM"></div>
                    </div>
                    <div class="f-row f-checks">
                        <label class="chk"><input type="checkbox" id="apptAllDay" name="all_day" value="1"> All-day event</label>
                        <label class="chk"><input type="checkbox" id="ev_notify" name="notify" value="1" checked> Notify attendees</label>
                    </div>
                    <div class="f-row"><label>Notes</label>
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
```

- [ ] **Step 2: Replace the old filter script block (current lines 196-249, `<script>` … `</script>`) with this**

```html
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
            var form = this;
            $.post(form.action, $(form).serialize(), function (r) {
                if (r && r.status === 'success') {
                    $('#event_modal').modal('hide');
                    location.reload();
                } else {
                    alert((r && r.message) || 'Save failed');
                }
            }, 'json').fail(function () { alert('Save failed'); });
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
    });
</script>
```

- [ ] **Step 3: Lint**

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l application\views\admin\calendar.php
```
Expected: `No syntax errors detected`.

(Styling arrives in Task 6; JS wiring in Task 5. The page looks broken between tasks — that is expected.)

---

### Task 5: FullCalendar config + toolbar wiring + manual source (`my_calendar.php`)

**Files:**
- Modify: `application/views/admin/calendar/my_calendar.php`

- [ ] **Step 1: Cache-bust the stylesheet — change line 2**

```html
<link href="<?php echo base_url() ?>assets/css/calendar_modern.css?v=2" rel="stylesheet" type="text/css">
```

- [ ] **Step 2: Hide the built-in toolbar — replace lines 25-29 (the `header:` option) with**

```js
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
```

- [ ] **Step 3: Replace the `select` handler (lines 33-42) with**

```js
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
```

- [ ] **Step 4: Append the manual-events source — insert immediately AFTER the billing block's closing `?>` (after the `<?php } } ?>` that closes the billing `foreach`/`if`, around line 570, and BEFORE the final `]` that closes `events: [`)**

```php
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
                        if (!empty($manual_events)) {
                            foreach ($manual_events as $me) :
                                $me_color = !empty($manual_colors[$me->category]) ? $manual_colors[$me->category] : '#0d9488';
                                $me_end = !empty($me->end_date) ? $me->end_date : $me->start_date;
                                ?> {
                                    title: <?= json_encode($me->title) ?>,
                                    start: "<?= $me->start_date ?>",
                                    end: "<?= $me_end ?>",
                                    color: "<?= $me_color ?>",
                                    allDay: <?= $me->all_day ? 'true' : 'false' ?>,
                                    type: "manual",
                                    cal_id: <?= (int) $me->calendar_events_id ?>,
                                    ev_category: "<?= $me->category ?>",
                                    ev_related_id: <?= $me->related_id ? (int) $me->related_id : 'null' ?>,
                                    ev_start_time: <?= json_encode((string) $me->start_time) ?>,
                                    ev_end_time: <?= json_encode((string) $me->end_time) ?>,
                                    ev_allday: <?= $me->all_day ? 'true' : 'false' ?>,
                                    ev_notify: <?= $me->notify ? 'true' : 'false' ?>,
                                    ev_notes: <?= json_encode((string) $me->notes) ?>
                                },
                                <?php
                            endforeach;
                        }
                        ?>
```

- [ ] **Step 5: Add toolbar wiring + KPI counter — insert immediately AFTER the `});` that closes `$('#my_calendar').fullCalendar({...})` (line ~585), still inside the `if ($('#my_calendar').length) {` block**

```js
        function updateCalendarKpis() {
            if (typeof moment === 'undefined') { return; }
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
```

- [ ] **Step 6: Lint**

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l application\views\admin\calendar\my_calendar.php
```
Expected: `No syntax errors detected`.

- [ ] **Step 7: Smoke the page renders**

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe plan\_tools\calendar-event-test.php
```
Expected: PASS (page loads, CRUD intact — the source block edit didn't break PHP).

- [ ] **Step 8: Commit**

```bash
git add application/views/admin/calendar.php application/views/admin/calendar/my_calendar.php
git commit -m "feat: calendar page restructure (KPI strip, custom toolbar, event modal) + manual event source"
```

---

### Task 6: Rewrite `calendar_modern.css` (dashboard theme)

**Files:**
- Rewrite: `assets/css/calendar_modern.css` (full file replacement — currently the 318-line "Red & Gray" theme)

- [ ] **Step 1: Replace the whole file with**

```css
/* ===================================================
   Calendar page - TIC CRM dashboard theme (A2-16)
   FullCalendar v3.8 overrides + page chrome + modals.
   Tokens mirror assets/css/tokens.css / _design/dashboard-modern.html.
   =================================================== */
:root {
  --c-bg: #f4f6f8; --c-surface: #fff; --c-border-2: #e3e6ea; --c-border: #d5dae0;
  --c-text: #1a1d21; --c-text-2: #475569; --c-text-3: #7a828c; --c-slate: #94a3b8;
  --c-accent: #0d9488; --c-mint: #10b981; --c-cyan: #06b6d4; --c-amber: #f59e0b;
  --c-purple: #8b5cf6; --c-danger: #dc2626;
  --r-xl: 16px; --r-lg: 14px; --r-md: 10px; --r-full: 999px;
  --sh-1: 0 1px 2px rgba(15, 23, 42, .06);
}
.full-calender { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }

/* ---------------- KPI strip ---------------- */
.kpi-strip { margin-bottom: 16px; }
.kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
.kpi-card { display: flex; align-items: center; gap: 12px; padding: 14px 16px; background: var(--c-surface); border: 1px solid var(--c-border-2); border-radius: var(--r-xl); box-shadow: var(--sh-1); min-width: 0; }
.kpi-card .k-i { width: 32px; height: 32px; flex: 0 0 32px; border-radius: var(--r-md); display: grid; place-items: center; font-size: 15px; font-weight: 700; }
.k-i.ok { background: #ecfdf5; color: #10b981; }
.k-i.no { background: #fef2f2; color: #dc2626; }
.k-i.cyan { background: #ecfeff; color: #0e7490; }
.k-i.amb { background: #fffbeb; color: #b45309; }
.kpi-card .v { font-size: 24px; font-weight: 700; line-height: 1.2; font-variant-numeric: tabular-nums; white-space: nowrap; }
.kpi-card .l { font-size: 13px; color: var(--c-text-3); margin-top: 2px; }

/* ---------------- calendar card + toolbar ---------------- */
.cal-card { background: var(--c-surface); border: 1px solid var(--c-border-2); border-radius: var(--r-xl); box-shadow: var(--sh-1); overflow: visible; margin-top: 0; }
.cal-toolbar { display: flex; align-items: center; gap: 8px; padding: 14px 16px; border-bottom: 1px solid #f1f3f5; flex-wrap: wrap; }
.cal-toolbar .nav-btns { display: flex; gap: 5px; }
.mini-btn { font-size: 11.5px; font-weight: 700; border-radius: 8px; padding: 6px 11px; background: #f1f3f5; color: var(--c-text-2); border: 0; }
.mini-btn:hover { background: #e9ecef; }
.cal-title { font-size: 16.5px; font-weight: 700; letter-spacing: -.01em; margin: 0 4px 0 6px; color: var(--c-text); }
.seg { display: flex; background: #f1f3f5; border-radius: 999px; padding: 3px; gap: 2px; }
.seg button { border: 0; background: transparent; font-size: 12px; font-weight: 700; color: var(--c-text-3); border-radius: 999px; padding: 5px 14px; }
.seg button.on { background: var(--c-accent); color: #fff; box-shadow: 0 1px 2px rgba(13, 148, 136, .35); }
.tb-right { margin-left: auto; display: flex; gap: 8px; align-items: center; }
.filter-pill { display: flex; align-items: center; gap: 7px; height: 32px; border-radius: 999px; background: #f1f3f5; color: var(--c-text-2); font-size: 12px; font-weight: 600; padding: 0 13px; border: 0; }
.filter-pill .c-dot { width: 7px; height: 7px; border-radius: 99px; background: var(--c-accent); }
.filter-pill .caret-i { font-size: 9px; color: var(--c-slate); }
.filter-pill:hover { background: #e9ecef; }
.icon-btn { width: 32px; height: 32px; border-radius: 8px; background: #f1f3f5; display: grid; place-items: center; color: var(--c-text-2); font-size: 13px; border: 0; text-decoration: none; }
.icon-btn:hover { background: #e9ecef; color: var(--c-text); }
.btn-new { height: 32px; border-radius: 8px; background: var(--c-accent); color: #fff; font-size: 12px; font-weight: 600; padding: 0 14px; display: inline-flex; align-items: center; gap: 6px; border: 0; box-shadow: 0 1px 2px rgba(13, 148, 136, .35); }
.btn-new:hover { background: #0b7c72; color: #fff; }

/* keep the legacy dropdown z-index fixes working (was defined for panel-heading) */
.calendar-filter { position: static !important; }
.calendar-filter > .dropdown-menu { position: absolute !important; top: 100% !important; left: auto !important; right: 0 !important; z-index: 99999 !important; margin-top: 4px !important; max-height: 400px; overflow-y: auto; }
.cal-toolbar { position: relative !important; z-index: 20; }

/* ---------------- FullCalendar 3.8 theming ---------------- */
#my_calendar { padding: 0; }
#my_calendar .fc-toolbar { display: none !important; }          /* custom toolbar replaces it */
#my_calendar .fc { border: 0; }
#my_calendar .fc table { border: 0; }
#my_calendar .fc th.fc-day-header { background: #fafbfc; border-bottom: 1px solid var(--c-border-2) !important; border-top: 0 !important; padding: 9px 8px; font-size: 10.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--c-slate); text-align: left; }
#my_calendar .fc th.fc-day-header span { display: inline-block; }
#my_calendar .fc td.fc-day-top { background: #fff; border-color: #f1f3f5 !important; padding: 5px 6px 2px; vertical-align: top; }
#my_calendar .fc td.fc-day { border-color: #f1f3f5 !important; background: #fff; }
#my_calendar .fc .fc-day-number { font-size: 12.5px; font-weight: 600; color: var(--c-text-2); padding: 2px 4px; float: none; display: inline-block; min-width: 24px; text-align: center; border-radius: 999px; }
#my_calendar .fc td.fc-day-top.fc-today .fc-day-number { background: var(--c-accent); color: #fff; font-weight: 800; }
#my_calendar .fc td.fc-day-top.fc-other-month, #my_calendar .fc td.fc-day.fc-other-month { background: #fafbfc; }
#my_calendar .fc td.fc-day-top.fc-other-month .fc-day-number { color: #c3c9d0; }
#my_calendar .fc td.fc-today { background: #f0fdfa !important; }
#my_calendar .fc .fc-row { min-height: 96px; border-color: #f1f3f5; }

/* events: solid rounded pills (colors come from inline styles / config) */
#my_calendar .fc .fc-day-grid-event { margin: 3px 5px 0; padding: 3px 7px !important; border-radius: 6px !important; border: 0 !important; max-width: 100%; box-shadow: var(--sh-1); }
#my_calendar .fc .fc-day-grid-event .fc-content { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.45; }
#my_calendar .fc .fc-day-grid-event .fc-title { font-size: 11px; font-weight: 600; color: #fff; }
#my_calendar .fc .fc-day-grid-event:hover { filter: brightness(1.08); }
#my_calendar .fc .fc-time-grid-event { border-radius: 6px; }
#my_calendar .fc .fc-highlight { background: #ccfbf1; }

/* week/day view basics */
#my_calendar .fc .fc-time-grid .fc-slot td, #my_calendar .fc .fc-slot td { border-color: #f1f3f5 !important; }
#my_calendar .fc .fc-axis { color: var(--c-slate); font-size: 11px; }
#my_calendar .fc .fc-day-header, #my_calendar .fc .fc-widget-header { border-color: var(--c-border-2) !important; }

/* popover ("+N more") */
#my_calendar .fc-popover { border: 0; border-radius: var(--r-lg); box-shadow: 0 12px 32px rgba(15, 23, 42, .18); overflow: hidden; z-index: 40; }
#my_calendar .fc-popover .fc-header { background: #fafbfc; border-bottom: 1px solid var(--c-border-2); }
#my_calendar .fc-popover .fc-title { font-weight: 700; font-size: 13px; color: var(--c-text); }
#my_calendar .fc-popover .fc-close { color: var(--c-slate); }
#my_calendar .fc-popover .fc-day-grid-event { border-radius: 6px; }

/* ---------------- shared modal design (both modals) ---------------- */
.cal-modal, #myModal .modal-content.cal-settings { border: 0; border-radius: var(--r-xl); box-shadow: 0 24px 60px rgba(15, 23, 42, .28); overflow: hidden; }
#myModal .modal-dialog { border: 0; }
#myModal .modal-dialog:has(> .modal-content > .cal-settings) { max-width: 640px; }
#event_modal .modal-dialog { max-width: 560px; }
.m-head { display: flex; align-items: flex-start; gap: 10px; padding: 18px 20px 0; }
.m-head h3 { margin: 0; font-size: 16.5px; font-weight: 700; letter-spacing: -.01em; color: var(--c-text); }
.m-head .sub { font-size: 12px; color: var(--c-slate); margin-top: 2px; }
.m-x { margin-left: auto; width: 30px; height: 30px; border-radius: 999px; background: #f1f3f5; border: 0; color: var(--c-text-3); font-size: 13px; display: grid; place-items: center; }
.m-x:hover { background: #e9ecef; color: var(--c-text); }
.m-body { padding: 14px 20px 4px; }
.m-sec { font-size: 11px; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; color: var(--c-slate); margin: 14px 0 9px; }
.m-sec:first-child { margin-top: 0; }
.m-foot { display: flex; align-items: center; gap: 9px; padding: 14px 20px 18px; border-top: 1px solid #f1f3f5; margin-top: 16px; }
.m-foot .spacer-m { flex: 1; }
.f-row { margin-bottom: 13px; }
.f-row > label { display: block; font-size: 12.5px; font-weight: 700; color: var(--c-text-2); margin-bottom: 6px; }
.f-row .req { color: var(--c-danger); }
.inp { width: 100%; height: 38px; border-radius: 9px; border: 1px solid var(--c-border-2); background: #fff; padding: 0 12px; font-size: 13.5px; color: var(--c-text); outline: none; font-family: inherit; }
.inp:focus { border-color: var(--c-accent); box-shadow: 0 0 0 3px rgba(13, 148, 136, .12); }
textarea.inp { height: 74px; padding: 9px 12px; resize: vertical; }
select.inp { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%2394a3b8' stroke-width='1.6' fill='none' stroke-linecap='round'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; }
.f-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.f-checks { display: flex; gap: 22px; align-items: center; padding: 2px 0 4px; }
.chk { display: inline-flex; align-items: center; gap: 8px; font-size: 13.5px; color: var(--c-text-2); font-weight: 600; cursor: pointer; margin: 0; }
.chk input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--c-accent); margin: 0; }
.btn-ghost { height: 36px; border-radius: 9px; background: #f1f3f5; border: 0; color: var(--c-text-2); font-size: 13px; font-weight: 700; padding: 0 16px; }
.btn-ghost:hover { background: #e9ecef; color: var(--c-text-2); }
.btn-accent { height: 36px; border-radius: 9px; background: var(--c-accent); border: 0; color: #fff; font-size: 13px; font-weight: 700; padding: 0 18px; box-shadow: 0 1px 2px rgba(13, 148, 136, .35); }
.btn-accent:hover { background: #0b7c72; color: #fff; }
.btn-danger-ghost { height: 36px; border-radius: 9px; background: #fef2f2; border: 0; color: #b91c1c; font-size: 13px; font-weight: 700; padding: 0 16px; }
.btn-danger-ghost:hover { background: #fee2e2; color: #b91c1c; }

/* settings modal specifics */
.gcal-note { display: flex; gap: 9px; align-items: flex-start; background: #f0fdfa; border: 1px solid #ccfbf1; border-radius: 10px; padding: 10px 12px; font-size: 12.5px; color: #0f766e; line-height: 1.5; }
.so-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 14px; }
.so { display: flex; align-items: center; gap: 9px; padding: 9px 11px; border: 1px solid var(--c-border-2); border-radius: 10px; font-size: 13px; color: var(--c-text-2); font-weight: 600; background: #fbfcfd; }
.so .prio { width: 8px; height: 8px; border-radius: 99px; flex: 0 0 8px; }
.so .so-l { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.so .so-cb { margin: 0 0 0 auto; flex: 0 0 auto; }
.so .so-cb label { margin: 0; padding: 0; }
.so .colorpicker-component { flex: 0 0 auto; }
.so .colorpicker-component .input-group-addon { border: 0; background: transparent; padding: 0; }
.so .colorpicker-component .input-group-addon > i { display: block; width: 16px; height: 16px; border-radius: 5px; box-shadow: inset 0 0 0 1px rgba(15, 23, 42, .1); cursor: pointer; }
/* keep bootstrap checkbox visuals quiet inside .so */
.so .c-checkbox > label > span.fa-check { background: transparent !important; border: 1.5px solid #cbd5e1; color: transparent !important; border-radius: 5px; width: 16px; height: 16px; }
.so .c-checkbox > label > input:checked + span.fa-check { background: var(--c-accent) !important; border-color: var(--c-accent) !important; color: #fff !important; }

/* ---------------- responsive ---------------- */
@media (max-width: 1100px) {
  .kpi-grid { grid-template-columns: repeat(2, 1fr); }
  .so-grid { grid-template-columns: 1fr; }
}
@media (max-width: 760px) {
  .kpi-grid { grid-template-columns: 1fr; }
  .cal-toolbar { gap: 6px; }
  .f-2 { grid-template-columns: 1fr; }
}
```

- [ ] **Step 2: Sanity check the file parses as CSS (no tool — eyeball braces)**

Run: `Select-String -Path assets\css\calendar_modern.css -Pattern '\{' | Measure-Object` and same for `'}'` — counts must match.

- [ ] **Step 3: Commit with Task 7 (single "redesign" commit) or alone**

```bash
git add assets/css/calendar_modern.css
git commit -m "feat: calendar dashboard-theme stylesheet (grid, toolbar, KPI, modals)"
```

---

### Task 7: Redesign the settings modal view

**Files:**
- Rewrite: `application/views/admin/settings/calendar_settings.php`

- [ ] **Step 1: Replace the whole file with**

```php
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
```

- [ ] **Step 2: Lint**

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l application\views\admin\settings\calendar_settings.php
```
Expected: `No syntax errors detected`.

- [ ] **Step 3: Commit**

```bash
git add application/views/admin/settings/calendar_settings.php
git commit -m "feat: calendar settings modal - dashboard design (google section, so-grid toggles)"
```

---

### Task 8: Browser verification against the approved mockup

**Files:** none edited — verification only (Playwright MCP browser or manual Chrome).

- [ ] **Step 1: Fresh load + screenshot**

Navigate to `http://localhost/complete_crm/admin/calendar?cb=cal1` and screenshot immediately (DPR 0.9: browser inner width ≈1422 when window=1280).

Expected vs mockup (`.superpowers/brainstorm/108-1790752228/content/calendar-full.html`): bg `#f4f6f8`, card `radius 16`, toolbar with `‹ Today ›` + title + teal Month pill + `+ New event` + gear + `All ▾` filter, KPI strip of 4 cards, white month grid, teal today circle (day 30), colored event pills, no built-in FC toolbar.

- [ ] **Step 2: DOM probes (run in console)**

```js
JSON.stringify({
  toolbarHidden: getComputedStyle(document.querySelector('#my_calendar .fc-toolbar')).display, // "none"
  title: document.querySelector('#calTitle').textContent,                                      // "September 2026"
  kpiEvents: document.querySelector('#kpiEvents').textContent,                                 // ">0", digits
  kpiUpcoming: document.querySelector('#kpiUpcoming').textContent,                             // digits
  kpiLeave: document.querySelectorAll('.kpi-card .v')[2].textContent,                          // digits
  kpiHoliday: document.querySelectorAll('.kpi-card .v')[3].textContent,                        // digits
  todayCell: !!document.querySelector('#my_calendar .fc-today'),
  segOn: document.querySelector('#calSeg button.on').textContent                               // "Month"
})
```
Expected: `toolbarHidden:"none"`, all KPIs numeric, `todayCell:true`.

- [ ] **Step 3: Functional pass**

1. Click **‹ / Today / ›** → title changes, returns to "September 2026" on Today.
2. Click **Week** → agenda week renders, seg pill moves; back to **Month**.
3. Click **All ▾ → Tasks** (or any category) → page reloads filtered, no JS console error (old `isset` bug is gone), label shows the category.
4. Click **+ New event** → modal opens (design: rounded 16, teal focus inputs, Cancel/Add event). Fill title `Browser check event`, dates default today → **Add event** → page reloads → event chip visible → **click chip** → Edit modal prefill → change title → Save → chip updates → **Delete** → gone.
5. Click **gear** → settings modal opens in new design: Google note box, 16 toggle rows with swatches, expiry input, Close/Save. Toggle a checkbox + save → modal closes, redirect back, `tbl_config` value flipped (re-open and confirm state).
6. Day-click on empty cell → New event modal prefilled with that date.

- [ ] **Step 4: Widths / overflow**

At inner width ≈1422 and ≈711 (window resize 1280 / 640): screenshot both; assert `document.documentElement.scrollWidth <= innerWidth + 1`.

- [ ] **Step 5: KPI honesty check**

Compare `#kpiEvents` to visible September chips (or `calendar.fullCalendar('clientEvents').filter(e=>moment(e.start).isSame(moment(),'month')).length` — must equal the printed number).

---

### Task 9: Gates, lint, commit, push

- [ ] **Step 1: Lint all touched PHP**

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l application\views\admin\calendar.php
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l application\views\admin\calendar\my_calendar.php
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l application\views\admin\settings\calendar_settings.php
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l application\controllers\admin\Calendar.php
```
Expected: 4× `No syntax errors detected`.

- [ ] **Step 2: Full gates**

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe plan\_tools\menu-group-test.php
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe plan\_tools\smoke.php
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe plan\_tools\login-test.php
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe plan\_tools\calendar-event-test.php
```
Expected: 59/59, 22/22, 7/7, 9/9 — 0 failures.

- [ ] **Step 3: Commit + push**

```bash
git add application/views/admin/calendar.php application/views/admin/calendar/my_calendar.php application/views/admin/settings/calendar_settings.php application/controllers/admin/Calendar.php assets/css/calendar_modern.css plan/_tools/calendar-event-test.php
git status --short
git commit -m "feat: calendar page redesign (layout C) + settings & new-event modals"
git push origin sakib
```
Only stage the six files above — `_design/*.png`, `.superpowers/`, `docs/` artifacts stay untracked/uncommitted unless already tracked.

---

## Self-Review (plan vs spec)

1. **Spec coverage:** page restructure → Task 4; FC theming + cache buster → Tasks 5/6; KPI (client counts + leave/holiday SQL) → Tasks 4/5; settings modal → Task 7 (fields/actions/colorpickers preserved); event modal + table + endpoints + source + edit/delete → Tasks 1/2/3/4/5; error handling (JSON validation errors, confirm delete) → Task 3/4; testing (gates + e2e + browser vs mockup) → Tasks 8/9. Gap found & fixed: spec's `yyyy/MM/dd` legacy format cell updated to ISO `yyyy-mm-dd` (datepicker/DB format).
2. **Placeholder scan:** no TBD/TODO; every code step carries complete code.
3. **Type consistency:** `calendar_events_id` (PK/POST field), `save_event`/`delete_event` routes, `#kpiEvents/#kpiUpcoming` ids, `updateCalendarKpis()` name and `cal_id`/`ev_*` event props are identical across Tasks 3-5; `resetEventModal` is defined global in Task 4 and consumed in Task 5.
