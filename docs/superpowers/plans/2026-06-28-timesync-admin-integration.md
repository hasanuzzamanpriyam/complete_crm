# TimeSync Admin Integration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Upgrade `/admin/timesync/` from basic KPIs to full dashboard with Chart.js charts, user drill-down, calendar, time entries datatable, enhanced screenshots/usage gallery, matching ERP UI conventions.

**Architecture:** PHP controller methods return data via `$data['json_var']` passed to views; views use Chart.js AJAX + inline data for charts; all endpoints are in `controllers/admin/Timesync.php`; all views in `views/admin/timesync/`.

**Tech Stack:** CI3 + Chart.js (already in `assets/plugins/chart.js/Chart.js`) + jQuery + Bootstrap 3 panels + PHPUnit 12 for backend tests. **NO DARK THEME** — match existing ERP light UI.

**Spec:** `docs/superpowers/specs/2026-06-28-timesync-admin-integration-design.md`

---
### Task 1: Include Chart.js + add menu active state for timesync sub-pages

**Files:**
- Modify: `application/views/admin/components/htmlheader.php`
- Test: `application/tests/controllers/admin/TimesyncTest.php`

- [ ] **Step 1: Add Chart.js to admin htmlheader**

Add Chart.js script include after the morris include in htmlheader.php (~line 87).

After `assets/plugins/morris/morris.min.css` link, add:
```php
<script src="<?php echo base_url() ?>assets/plugins/chart.js/Chart.js"></script>
```

- [ ] **Step 2: Write failing test — TimesyncTest.php scaffold**

Create `application/tests/controllers/admin/TimesyncTest.php`:
```php
<?php
use PHPUnit\Framework\TestCase;

class TimesyncTest extends TestCase
{
    private static $pdo;
    private $cookieFile;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = new PDO('mysql:host=localhost;dbname=tic_crm', 'root', '');
    }

    protected function setUp(): void
    {
        $this->cookieFile = tempnam(sys_get_temp_dir(), 'TS_');
        $this->loginAsAdmin();
    }

    protected function tearDown(): void
    {
        if (file_exists($this->cookieFile)) {
            unlink($this->cookieFile);
        }
    }

    private function loginAsAdmin(): void
    {
        $ch = curl_init('http://localhost/tic_crm/index.php/login');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 5,
        ]);
        curl_exec($ch);
        curl_close($ch);

        // Submit login form
        $ch = curl_init('http://localhost/tic_crm/index.php/login');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_POSTFIELDS => http_build_query([
                'user_name' => 'admin',
                'password' => 'admin',
            ]),
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    private function get($url)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_TIMEOUT => 5,
        ]);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['body' => $resp, 'code' => $httpCode];
    }

    public function testDashboardReturns200()
    {
        $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync');
        $this->assertEquals(200, $res['code']);
    }
}
```

- [ ] **Step 3: Run test to confirm it fails (or if env not ready, skip verify)**

Run: `cd C:\laragon\www\tic_crm && vendor\bin\phpunit.bat --testsuite admin --filter TimesyncTest::testDashboardReturns200`

Expected: PASS if admin:admin login works, or test might need actual admin credentials adjusted.

- [ ] **Step 4: Update the test credentials if needed**

If the test fails on login, adjust `loginAsAdmin()` to use actual working admin creds.

---
### Task 2: Rewrite Timesync::index() — dashboard data methods

**Files:**
- Modify: `application/controllers/admin/Timesync.php`
- Test: `application/tests/controllers/admin/TimesyncTest.php`

- [ ] **Step 1: Add private helper methods to Timesync.php**

Add to `Timesync.php` (before `settings()` method or grouped at bottom):

```php
private function _dashboard_kpis($start_date, $end_date)
{
    $today = date('Y-m-d');
    $week_start = date('Y-m-d', strtotime('monday this week'));
    $month_start = date('Y-m-01');

    return [
        'today_hours' => $this->_total_hours_since($today),
        'week_hours' => $this->_total_hours_since($week_start),
        'month_hours' => $this->_total_hours_since($month_start),
        'active_users' => (int) $this->db->select('COUNT(DISTINCT user_id) as count')
            ->where('started_at >=', $today)
            ->where('is_running', 1)
            ->get('tbl_desktop_time_entries')
            ->row()->count ?? 0,
        'total_entries' => $this->db->count_all('tbl_desktop_time_entries'),
        'total_screenshots' => $this->db->count_all('tbl_screenshots'),
        'period_hours' => $this->_total_hours_since($start_date),
    ];
}
```

```php
private function _daily_hours_chart($start_date, $end_date)
{
    $result = $this->db
        ->select('DATE(started_at) as day, COALESCE(SUM(total_seconds), 0) as total_sec')
        ->where('started_at >=', $start_date . ' 00:00:00')
        ->where('started_at <=', $end_date . ' 23:59:59')
        ->group_by('DATE(started_at)')
        ->order_by('day', 'ASC')
        ->get('tbl_desktop_time_entries')
        ->result();

    $labels = [];
    $values = [];
    foreach ($result as $row) {
        $labels[] = $row->day;
        $values[] = round($row->total_sec / 3600, 1);
    }

    return ['labels' => $labels, 'values' => $values];
}
```

```php
private function _user_distribution($start_date, $end_date)
{
    return $this->db
        ->select('tbl_desktop_time_entries.user_id, tbl_account_details.fullname, COALESCE(SUM(tbl_desktop_time_entries.total_seconds), 0) as total_sec, COUNT(DISTINCT tbl_desktop_time_entries.id) as entry_count')
        ->from('tbl_desktop_time_entries')
        ->join('tbl_account_details', 'tbl_account_details.user_id = tbl_desktop_time_entries.user_id', 'left')
        ->where('tbl_desktop_time_entries.started_at >=', $start_date . ' 00:00:00')
        ->where('tbl_desktop_time_entries.started_at <=', $end_date . ' 23:59:59')
        ->group_by('tbl_desktop_time_entries.user_id')
        ->order_by('total_sec', 'DESC')
        ->limit(10)
        ->get()
        ->result();
}
```

```php
private function _user_grid($start_date, $end_date)
{
    $users = $this->db
        ->select('tbl_users.user_id, tbl_account_details.fullname, tbl_account_details.avatar')
        ->from('tbl_users')
        ->join('tbl_account_details', 'tbl_account_details.user_id = tbl_users.user_id', 'left')
        ->where('tbl_users.activated', 1)
        ->order_by('tbl_account_details.fullname', 'ASC')
        ->get()
        ->result();

    foreach ($users as &$u) {
        $stats = $this->db
            ->select('COALESCE(SUM(total_seconds), 0) as total_sec, COUNT(DISTINCT id) as entry_count, MAX(started_at) as last_active')
            ->where('user_id', $u->user_id)
            ->where('started_at >=', $start_date . ' 00:00:00')
            ->where('started_at <=', $end_date . ' 23:59:59')
            ->get('tbl_desktop_time_entries')
            ->row();
        $u->total_sec = (int) $stats->total_sec;
        $u->entry_count = (int) $stats->entry_count;
        $u->last_active = $stats->last_active;
        $u->screenshot_count = (int) $this->db
            ->where('user_id', $u->user_id)
            ->where('captured_at >=', $start_date . ' 00:00:00')
            ->where('captured_at <=', $end_date . ' 23:59:59')
            ->count_all_results('tbl_screenshots');
    }

    // Sort by total_sec desc
    usort($users, function ($a, $b) {
        return $b->total_sec <=> $a->total_sec;
    });

    return $users;
}
```

- [ ] **Step 2: Write failing test for dashboard data structure**

Add to TimesyncTest.php:

```php
public function testDashboardHasExpectedDataKeys()
{
    $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync');
    $this->assertEquals(200, $res['code']);
    $this->assertStringContainsString('today_hours', $res['body']);
    $this->assertStringContainsString('week_hours', $res['body']);
    $this->assertStringContainsString('month_hours', $res['body']);
    $this->assertStringContainsString('active_users', $res['body']);
    $this->assertStringContainsString('daily_chart_labels', $res['body']);
    $this->assertStringContainsString('daily_chart_values', $res['body']);
    $this->assertStringContainsString('user_distribution', $res['body']);
    $this->assertStringContainsString('user_grid', $res['body']);
}
```

- [ ] **Step 3: Run test to verify it fails**

Run: `vendor\bin\phpunit.bat --testsuite admin --filter TimesyncTest::testDashboardHasExpectedDataKeys`

Expected: FAIL because index() doesn't set these keys yet.

- [ ] **Step 4: Rewrite Timesync::index() to pass all dashboard data**

Replace the `index()` method:

```php
public function index()
{
    if (!is_super_admin()) {
        $can_view = can_action('timesync', 'view');
        if (!$can_view) {
            redirect('404');
        }
    }

    $data['title'] = 'TimeSync Dashboard';

    $from = $this->input->get('from');
    $to = $this->input->get('to');
    if (empty($from)) $from = date('Y-m-d', strtotime('-7 days'));
    if (empty($to)) $to = date('Y-m-d');

    $data['from'] = $from;
    $data['to'] = $to;

    // KPI data
    $kpis = $this->_dashboard_kpis($from, $to);
    foreach ($kpis as $key => $val) {
        $data[$key] = $val;
    }

    // Chart data
    $chart = $this->_daily_hours_chart($from, $to);
    $data['daily_chart_labels'] = json_encode($chart['labels']);
    $data['daily_chart_values'] = json_encode($chart['values']);

    $dist = $this->_user_distribution($from, $to);
    $data['user_distribution'] = json_encode($dist);
    $data['user_distribution_raw'] = $dist;

    $data['user_grid'] = $this->_user_grid($from, $to);
    $data['top_users'] = $this->_user_distribution($from, $to);

    $data['subview'] = $this->load->view('admin/timesync/dashboard', $data, true);
    $this->load->view('admin/_layout_main', $data);
}
```

- [ ] **Step 5: Run test to verify it passes**

Expected: PASS — testDashboardHasExpectedDataKeys should now find the data keys.

- [ ] **Step 6: Commit**

```bash
git add application/controllers/admin/Timesync.php application/tests/controllers/admin/TimesyncTest.php
git commit -m "feat(timesync): add dashboard data methods with KPIs charts user grid"
```

---
### Task 3: Backend — calendar(), day_details(), entries_datatable()

**Files:**
- Modify: `application/controllers/admin/Timesync.php`
- Test: `application/tests/controllers/admin/TimesyncTest.php`

- [ ] **Step 1: Write failing tests for new endpoints**

Add to TimesyncTest.php:

```php
public function testCalendarReturns200()
{
    $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync/calendar');
    $this->assertEquals(200, $res['code']);
    $this->assertStringContainsString('calendar_grid', $res['body']);
}

public function testDayDetailsReturnsJson()
{
    $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync/day_details/' . date('Y-m-d'));
    $this->assertEquals(200, $res['code']);
    $data = json_decode($res['body'], true);
    $this->assertIsArray($data);
    $this->assertArrayHasKey('entries', $data);
    $this->assertArrayHasKey('screenshots', $data);
}

public function testEntriesDatatableReturnsJson()
{
    $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync/entries_datatable');
    $this->assertEquals(200, $res['code']);
    $data = json_decode($res['body'], true);
    $this->assertIsArray($data);
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `vendor\bin\phpunit.bat --testsuite admin --filter TimesyncTest::testCalendarReturns200`

Expected: FAIL (404 or method not found).

- [ ] **Step 3: Add calendar() method to Timesync.php**

```php
public function calendar()
{
    if (!is_super_admin()) {
        $can_view = can_action('timesync', 'view');
        if (!$can_view) redirect('404');
    }

    $data['title'] = 'TimeSync Calendar';

    $month = $this->input->get('month') ?: date('Y-m');
    $data['current_month'] = $month;
    list($year, $mon) = explode('-', $month);

    $first_day = mktime(0, 0, 0, $mon, 1, $year);
    $days_in_month = date('t', $first_day);
    $start_offset = date('w', $first_day); // 0=Sun

    // Get entries data for this month
    $entries_by_day = [];
    $start_date = $year . '-' . str_pad($mon, 2, '0', STR_PAD_LEFT) . '-01';
    $end_date = $year . '-' . str_pad($mon, 2, '0', STR_PAD_LEFT) . '-' . $days_in_month;

    $rows = $this->db
        ->select('DATE(started_at) as day, COUNT(DISTINCT id) as entry_count, COALESCE(SUM(total_seconds), 0) as total_sec')
        ->where('started_at >=', $start_date . ' 00:00:00')
        ->where('started_at <=', $end_date . ' 23:59:59')
        ->group_by('DATE(started_at)')
        ->get('tbl_desktop_time_entries')
        ->result();

    foreach ($rows as $r) {
        $entries_by_day[$r->day] = [
            'entry_count' => $r->entry_count,
            'total_sec' => (int)$r->total_sec,
        ];
    }

    // Get screenshot count by day
    $screenshots = $this->db
        ->select('DATE(captured_at) as day, COUNT(*) as count')
        ->where('captured_at >=', $start_date . ' 00:00:00')
        ->where('captured_at <=', $end_date . ' 23:59:59')
        ->group_by('DATE(captured_at)')
        ->get('tbl_screenshots')
        ->result();

    $screenshots_by_day = [];
    foreach ($screenshots as $s) {
        $screenshots_by_day[$s->day] = $s->count;
    }

    $data['calendar_grid'] = [];
    $data['start_offset'] = $start_offset;
    $data['days_in_month'] = $days_in_month;
    $data['month_name'] = date('F Y', $first_day);

    for ($d = 1; $d <= $days_in_month; $d++) {
        $date_str = $start_date . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
        $date_key = $year . '-' . str_pad($mon, 2, '0', STR_PAD_LEFT) . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
        $data['calendar_grid'][] = [
            'day' => $d,
            'date' => $date_key,
            'entries' => $entries_by_day[$date_key] ?? null,
            'screenshots_count' => $screenshots_by_day[$date_key] ?? 0,
        ];
    }

    // Previous/next month nav
    $prev = strtotime($month . '-01 -1 month');
    $next = strtotime($month . '-01 +1 month');
    $data['prev_month'] = date('Y-m', $prev);
    $data['next_month'] = date('Y-m', $next);

    $data['subview'] = $this->load->view('admin/timesync/calendar', $data, true);
    $this->load->view('admin/_layout_main', $data);
}
```

- [ ] **Step 4: Add day_details() method**

```php
public function day_details($date = null)
{
    if (empty($date)) {
        $date = date('Y-m-d');
    }

    $entries = $this->db
        ->select('tbl_desktop_time_entries.*, tbl_account_details.fullname')
        ->join('tbl_account_details', 'tbl_account_details.user_id = tbl_desktop_time_entries.user_id', 'left')
        ->where('DATE(tbl_desktop_time_entries.started_at)', $date)
        ->order_by('tbl_desktop_time_entries.started_at', 'DESC')
        ->get('tbl_desktop_time_entries')
        ->result();

    $screenshots = $this->db
        ->select('tbl_screenshots.*, tbl_account_details.fullname')
        ->join('tbl_account_details', 'tbl_account_details.user_id = tbl_screenshots.user_id', 'left')
        ->where('DATE(tbl_screenshots.captured_at)', $date)
        ->order_by('tbl_screenshots.captured_at', 'DESC')
        ->get('tbl_screenshots')
        ->result();

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'entries' => $entries,
            'screenshots' => $screenshots,
        ]));
}
```

- [ ] **Step 5: Add entries_datatable() method**

```php
public function entries_datatable()
{
    if (!$this->input->is_ajax_request()) {
        redirect('admin/timesync');
    }

    $from = $this->input->get('from');
    $to = $this->input->get('to');
    $user_id = $this->input->get('user_id');
    $limit = (int)($this->input->get('length') ?: 10);
    $offset = (int)($this->input->get('start') ?: 0);
    $search = $this->input->get('search')['value'] ?? '';

    $this->db->select('tbl_desktop_time_entries.*, tbl_account_details.fullname, tbl_task.task_name');
    $this->db->from('tbl_desktop_time_entries');
    $this->db->join('tbl_account_details', 'tbl_account_details.user_id = tbl_desktop_time_entries.user_id', 'left');
    $this->db->join('tbl_task', 'tbl_task.task_id = tbl_desktop_time_entries.task_id', 'left');

    if (!empty($from)) $this->db->where('tbl_desktop_time_entries.started_at >=', $from . ' 00:00:00');
    if (!empty($to)) $this->db->where('tbl_desktop_time_entries.started_at <=', $to . ' 23:59:59');
    if (!empty($user_id)) $this->db->where('tbl_desktop_time_entries.user_id', (int)$user_id);
    if (!empty($search)) {
        $this->db->group_start();
        $this->db->like('tbl_account_details.fullname', $search);
        $this->db->or_like('tbl_task.task_name', $search);
        $this->db->or_like('tbl_desktop_time_entries.type', $search);
        $this->db->group_end();
    }

    // Count total filtered
    $count_query = clone $this->db;
    $total_filtered = $count_query->count_all_results();

    $this->db->order_by('tbl_desktop_time_entries.started_at', 'DESC');
    $this->db->limit($limit, $offset);
    $data = $this->db->get()->result();

    $total_all = $this->db->count_all('tbl_desktop_time_entries');

    $rows = [];
    foreach ($data as $r) {
        $rows[] = [
            'id' => $r->id,
            'date' => date('Y-m-d', strtotime($r->started_at)),
            'user' => htmlspecialchars($r->fullname ?? 'User #' . $r->user_id),
            'type' => htmlspecialchars($r->type),
            'started' => $r->started_at ? date('H:i:s', strtotime($r->started_at)) : '-',
            'stopped' => $r->stopped_at ? date('H:i:s', strtotime($r->stopped_at)) : '-',
            'duration' => gmdate('H:i:s', $r->total_seconds),
            'task' => $r->task_name ? htmlspecialchars($r->task_name) : '-',
            'task_id' => $r->task_id,
            'user_id' => $r->user_id,
        ];
    }

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'draw' => (int)($this->input->get('draw') ?: 0),
            'recordsTotal' => $total_all,
            'recordsFiltered' => $total_filtered,
            'data' => $rows,
        ]));
}
```

- [ ] **Step 6: Run tests to verify they pass**

Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add application/controllers/admin/Timesync.php application/tests/controllers/admin/TimesyncTest.php
git commit -m "feat(timesync): add calendar day_details entries_datatable methods"
```

---
### Task 4: Backend — rewrite user() with 3-tab drill-down data

**Files:**
- Modify: `application/controllers/admin/Timesync.php`
- Test: `application/tests/controllers/admin/TimesyncTest.php`

- [ ] **Step 1: Write failing test for user report**

Add to TimesyncTest.php:

```php
public function testUserReportHasExpectedData()
{
    $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync/user/1');
    $this->assertEquals(200, $res['code']);
    $this->assertStringContainsString('total_seconds', $res['body']);
    $this->assertStringContainsString('entry_count', $res['body']);
    $this->assertStringContainsString('app_usage_labels', $res['body']);
    $this->assertStringContainsString('app_usage_values', $res['body']);
    $this->assertStringContainsString('user_tasks', $res['body']);
}
```

- [ ] **Step 2: Run test to verify it fails**

Expected: FAIL (current user() output doesn't have these keys).

- [ ] **Step 3: Add private helper methods for user drill-down**

```php
private function _user_entries($user_id, $from, $to)
{
    return $this->db
        ->where('user_id', $user_id)
        ->where('started_at >=', $from . ' 00:00:00')
        ->where('started_at <=', $to . ' 23:59:59')
        ->order_by('started_at', 'DESC')
        ->get('tbl_desktop_time_entries')
        ->result();
}

private function _user_app_usage($user_id, $from, $to)
{
    $records = $this->db
        ->select('app_name, SUM(total_seconds) as total_sec')
        ->where('user_id', $user_id)
        ->where('recorded_at >=', $from)
        ->where('recorded_at <=', $to)
        ->group_by('app_name')
        ->order_by('total_sec', 'DESC')
        ->limit(10)
        ->get('tbl_desktop_app_usage')
        ->result();

    $labels = [];
    $values = [];
    foreach ($records as $r) {
        $labels[] = $r->app_name;
        $values[] = (int)$r->total_sec;
    }

    return ['data' => $records, 'labels' => json_encode($labels), 'values' => json_encode($values)];
}

private function _user_tasks($user_id, $from, $to)
{
    return $this->db
        ->select('tbl_task.*, tbl_users.username, tbl_account_details.fullname as assignee_name')
        ->from('tbl_desktop_time_entries')
        ->join('tbl_task', 'tbl_task.task_id = tbl_desktop_time_entries.task_id', 'left')
        ->join('tbl_users', 'tbl_users.user_id = tbl_task.assigned_to', 'left')
        ->join('tbl_account_details', 'tbl_account_details.user_id = tbl_task.assigned_to', 'left')
        ->where('tbl_desktop_time_entries.user_id', $user_id)
        ->where('tbl_desktop_time_entries.started_at >=', $from . ' 00:00:00')
        ->where('tbl_desktop_time_entries.started_at <=', $to . ' 23:59:59')
        ->group_by('tbl_task.task_id')
        ->get()
        ->result();
}
```

- [ ] **Step 4: Rewrite user() method**

```php
public function user($user_id = null)
{
    if (!is_super_admin()) {
        redirect('404');
    }

    if (empty($user_id)) {
        redirect('admin/timesync');
    }

    $data['title'] = 'User Report';
    $data['user'] = $this->db
        ->select('tbl_users.*, tbl_account_details.fullname, tbl_account_details.avatar')
        ->join('tbl_account_details', 'tbl_account_details.user_id = tbl_users.user_id', 'left')
        ->where('tbl_users.user_id', $user_id)
        ->get('tbl_users')
        ->row();

    if (empty($data['user'])) {
        redirect('admin/timesync');
    }

    $from = $this->input->get('from');
    $to = $this->input->get('to');
    if (empty($from)) $from = date('Y-m-01');
    if (empty($to)) $to = date('Y-m-d');

    $data['from'] = $from;
    $data['to'] = $to;

    // Entries
    $entries = $this->_user_entries($user_id, $from, $to);
    $data['entries'] = $entries;
    $data['total_seconds'] = 0;
    $data['entry_count'] = count($entries);
    foreach ($entries as $e) {
        $data['total_seconds'] += $e->total_seconds;
    }

    // Screenshots
    $data['screenshots'] = $this->db
        ->where('user_id', $user_id)
        ->where('captured_at >=', $from . ' 00:00:00')
        ->where('captured_at <=', $to . ' 23:59:59')
        ->order_by('captured_at', 'DESC')
        ->get('tbl_screenshots')
        ->result();
    $data['screenshot_count'] = count($data['screenshots']);

    // App usage
    $usage = $this->_user_app_usage($user_id, $from, $to);
    $data['app_usage'] = $usage['data'];
    $data['app_usage_labels'] = $usage['labels'];
    $data['app_usage_values'] = $usage['values'];

    // Tasks
    $data['user_tasks'] = $this->_user_tasks($user_id, $from, $to);

    // Active tab
    $tab = $this->input->get('tab');
    $data['active_tab'] = in_array($tab, ['timeline', 'apps', 'tasks']) ? $tab : 'timeline';

    $data['subview'] = $this->load->view('admin/timesync/user_report', $data, true);
    $this->load->view('admin/_layout_main', $data);
}
```

- [ ] **Step 5: Run tests to verify they pass**

Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add application/controllers/admin/Timesync.php application/tests/controllers/admin/TimesyncTest.php
git commit -m "feat(timesync): rewrite user() with 3-tab drill-down data"
```

---
### Task 5: Views — dashboard.php rewrite with Chart.js

**Files:**
- Rewrite: `application/views/admin/timesync/dashboard.php`

- [ ] **Step 1: Write dashboard view**

```php
<div class="row">
    <div class="col-lg-12">
        <section class="panel panel-custom">
            <header class="panel-heading">
                <h3 class="panel-title"><?= $title ?? 'TimeSync Dashboard' ?></h3>
            </header>
            <div class="panel-body">
                <!-- Date Range Filter -->
                <form method="get" class="form-inline mb-lg">
                    <div class="btn-group mb-sm">
                        <a href="?from=<?= date('Y-m-d') ?>&to=<?= date('Y-m-d') ?>" class="btn btn-<?= $from == date('Y-m-d') ? 'primary' : 'default' ?> btn-sm">Today</a>
                        <a href="?from=<?= date('Y-m-d', strtotime('monday this week')) ?>&to=<?= date('Y-m-d') ?>" class="btn btn-<?= $from == date('Y-m-d', strtotime('monday this week')) ? 'primary' : 'default' ?> btn-sm">This Week</a>
                        <a href="?from=<?= date('Y-m-01') ?>&to=<?= date('Y-m-d') ?>" class="btn btn-<?= $from == date('Y-m-01') ? 'primary' : 'default' ?> btn-sm">This Month</a>
                        <a href="?from=<?= date('Y-m-d', strtotime('-7 days')) ?>&to=<?= date('Y-m-d') ?>" class="btn btn-<?= ($from == date('Y-m-d', strtotime('-7 days')) && $to == date('Y-m-d')) ? 'primary' : 'default' ?> btn-sm">Last 7 Days</a>
                    </div>
                    <div class="form-group ml-sm">
                        <label>From: </label>
                        <input type="date" name="from" class="form-control input-sm" value="<?= $from ?>">
                    </div>
                    <div class="form-group ml-sm">
                        <label>To: </label>
                        <input type="date" name="to" class="form-control input-sm" value="<?= $to ?>">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm ml-sm">Filter</button>
                </form>

                <!-- KPI Row -->
                <div class="row">
                    <div class="col-md-2 col-sm-4 col-xs-6">
                        <div class="panel panel-info">
                            <div class="panel-body text-center">
                                <h2><?= $today_hours ?>h</h2>
                                <p class="text-muted">Today</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-xs-6">
                        <div class="panel panel-success">
                            <div class="panel-body text-center">
                                <h2><?= $week_hours ?>h</h2>
                                <p class="text-muted">This Week</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-xs-6">
                        <div class="panel panel-warning">
                            <div class="panel-body text-center">
                                <h2><?= $month_hours ?>h</h2>
                                <p class="text-muted">This Month</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-xs-6">
                        <div class="panel panel-primary">
                            <div class="panel-body text-center">
                                <h2><?= $active_users ?></h2>
                                <p class="text-muted">Active Users</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-xs-6">
                        <div class="panel panel-purple">
                            <div class="panel-body text-center">
                                <h2><?= $total_entries ?></h2>
                                <p class="text-muted">Entries</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-xs-6">
                        <div class="panel panel-danger">
                            <div class="panel-body text-center">
                                <h2><?= $total_screenshots ?></h2>
                                <p class="text-muted">Screenshots</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart Row -->
                <div class="row mt-lg">
                    <div class="col-md-8">
                        <div class="panel panel-custom">
                            <div class="panel-heading">
                                <h4 class="panel-title">Daily Hours</h4>
                            </div>
                            <div class="panel-body">
                                <canvas id="dailyHoursChart" height="120"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="panel panel-custom">
                            <div class="panel-heading">
                                <h4 class="panel-title">User Distribution</h4>
                            </div>
                            <div class="panel-body">
                                <canvas id="userDistChart" height="160"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Grid -->
                <div class="row mt-lg">
                    <div class="col-md-12">
                        <div class="panel panel-custom">
                            <div class="panel-heading">
                                <h4 class="panel-title">Users (<?= count($user_grid) ?>)</h4>
                                <span class="pull-right text-muted small">Sorted by total hours</span>
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <?php if (!empty($user_grid)): ?>
                                        <?php foreach ($user_grid as $u): ?>
                                            <div class="col-md-4 col-sm-6 mb-sm">
                                                <div class="panel panel-default" style="margin-bottom: 0;">
                                                    <div class="panel-body">
                                                        <div class="media">
                                                            <div class="media-left">
                                                                <?php
                                                                $avatar = (!empty($u->avatar) && file_exists(FCPATH . $u->avatar))
                                                                    ? base_url($u->avatar)
                                                                    : base_url('assets/img/user/default_avatar.jpg');
                                                                ?>
                                                                <img src="<?= $avatar ?>" class="img-circle" style="width: 48px; height: 48px; object-fit: cover;">
                                                            </div>
                                                            <div class="media-body">
                                                                <h5 class="media-heading">
                                                                    <a href="<?= base_url('admin/timesync/user/' . $u->user_id . '?from=' . $from . '&to=' . $to) ?>">
                                                                        <?= htmlspecialchars($u->fullname ?? 'User #' . $u->user_id) ?>
                                                                    </a>
                                                                </h5>
                                                                <div class="row text-center small" style="margin-top: 8px;">
                                                                    <div class="col-xs-4">
                                                                        <strong><?= round($u->total_sec / 3600, 1) ?>h</strong><br>
                                                                        <span class="text-muted">Hours</span>
                                                                    </div>
                                                                    <div class="col-xs-4">
                                                                        <strong><?= $u->entry_count ?></strong><br>
                                                                        <span class="text-muted">Entries</span>
                                                                    </div>
                                                                    <div class="col-xs-4">
                                                                        <strong><?= $u->screenshot_count ?></strong><br>
                                                                        <span class="text-muted">Screenshots</span>
                                                                    </div>
                                                                </div>
                                                                <?php if (!empty($u->last_active)): ?>
                                                                    <p class="text-muted small text-center" style="margin: 4px 0 0;">
                                                                        Last: <?= date('M d, H:i', strtotime($u->last_active)) ?>
                                                                    </p>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="col-md-12"><p class="text-center text-muted">No user data for this period</p></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
$(function () {
    // Daily Hours Bar Chart
    var dailyLabels = <?= $daily_chart_labels ?>;
    var dailyValues = <?= $daily_chart_values ?>;
    if (dailyLabels.length > 0) {
        new Chart(document.getElementById('dailyHoursChart'), {
            type: 'bar',
            data: {
                labels: dailyLabels,
                datasets: [{
                    label: 'Hours',
                    data: dailyValues,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, title: { display: true, text: 'Hours' } } },
                plugins: { legend: { display: false } }
            }
        });
    }

    // User Distribution Doughnut Chart
    var distData = <?= $user_distribution ?>;
    if (distData.length > 0) {
        var colors = ['#36a2eb','#ff6384','#ffce56','#4bc0c0','#9966ff','#ff9f40','#c9cbcf','#7bc043','#f37735','#ee4037'];
        new Chart(document.getElementById('userDistChart'), {
            type: 'doughnut',
            data: {
                labels: distData.map(function(d) { return d.fullname || 'User #' + d.user_id; }),
                datasets: [{
                    data: distData.map(function(d) { return Math.round(d.total_sec / 3600 * 10) / 10; }),
                    backgroundColor: colors.slice(0, distData.length),
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 10 } }
                    }
                }
            }
        });
    }
});
</script>
```

- [ ] **Step 2: Commit**

```bash
git add application/views/admin/timesync/dashboard.php
git commit -m "feat(timesync): rewrite dashboard view with Chart.js and user grid"
```

---
### Task 6: Views — entries.php + calendar.php

**Files:**
- Create: `application/views/admin/timesync/entries.php`
- Create: `application/views/admin/timesync/calendar.php`

- [ ] **Step 1: Create entries.php datatable view**

```php
<div class="row">
    <div class="col-lg-12">
        <section class="panel panel-custom">
            <header class="panel-heading">
                <h3 class="panel-title">Time Entries</h3>
            </header>
            <div class="panel-body">
                <form method="get" class="form-inline mb-lg" id="entries-filter-form">
                    <div class="form-group">
                        <label>From: </label>
                        <input type="date" name="from" class="form-control input-sm" value="<?= $this->input->get('from') ?? date('Y-m-d', strtotime('-30 days')) ?>">
                    </div>
                    <div class="form-group ml-sm">
                        <label>To: </label>
                        <input type="date" name="to" class="form-control input-sm" value="<?= $this->input->get('to') ?? date('Y-m-d') ?>">
                    </div>
                    <div class="form-group ml-sm">
                        <label>User: </label>
                        <select name="user_id" class="form-control input-sm">
                            <option value="">All Users</option>
                            <?php
                            $users = $this->db->select('tbl_users.user_id, tbl_account_details.fullname')
                                ->join('tbl_account_details', 'tbl_account_details.user_id = tbl_users.user_id', 'left')
                                ->where('tbl_users.activated', 1)
                                ->get('tbl_users')
                                ->result();
                            $selected_uid = $this->input->get('user_id');
                            foreach ($users as $u): ?>
                                <option value="<?= $u->user_id ?>" <?= $selected_uid == $u->user_id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u->fullname ?? 'User #' . $u->user_id) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm ml-sm">Filter</button>
                </form>

                <table class="table table-striped table-hover DataTimesync">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>User</th>
                            <th>Type</th>
                            <th>Started</th>
                            <th>Stopped</th>
                            <th>Duration</th>
                            <th>Task</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<script>
$(function () {
    var table = $('.DataTimesync').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('admin/timesync/entries_datatable') ?>',
            data: function (d) {
                d.from = $('input[name="from"]').val();
                d.to = $('input[name="to"]').val();
                d.user_id = $('select[name="user_id"]').val();
            }
        },
        columns: [
            { data: 'date' },
            { data: 'user' },
            { data: 'type' },
            { data: 'started' },
            { data: 'stopped' },
            { data: 'duration' },
            {
                data: null,
                render: function (data) {
                    return data.task_id
                        ? '<a href="<?= base_url('admin/tasks/view/') ?>' + data.task_id + '">' + data.task + '</a>'
                        : data.task;
                }
            }
        ],
        order: [[0, 'desc']],
        pageLength: 25
    });

    $('#entries-filter-form').on('submit', function (e) {
        e.preventDefault();
        table.ajax.reload();
    });
});
</script>
```

Need a method to serve this view page. Add to Timesync.php:

```php
public function entries()
{
    if (!is_super_admin()) {
        $can_view = can_action('timesync', 'view');
        if (!$can_view) redirect('404');
    }

    $data['title'] = 'Time Entries';
    $data['subview'] = $this->load->view('admin/timesync/entries', $data, true);
    $this->load->view('admin/_layout_main', $data);
}
```

- [ ] **Step 2: Create calendar.php view**

```php
<div class="row">
    <div class="col-lg-12">
        <section class="panel panel-custom">
            <header class="panel-heading">
                <h3 class="panel-title"><?= $title ?? 'Calendar' ?></h3>
                <div class="pull-right">
                    <a href="?month=<?= $prev_month ?>" class="btn btn-default btn-xs"><i class="fa fa-chevron-left"></i></a>
                    <strong class="mx-sm"><?= $month_name ?></strong>
                    <a href="?month=<?= $next_month ?>" class="btn btn-default btn-xs"><i class="fa fa-chevron-right"></i></a>
                </div>
            </header>
            <div class="panel-body">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $day_count = 0;
                        echo '<tr>';
                        for ($i = 0; $i < $start_offset; $i++) {
                            echo '<td style="background: #f9f9f9;"></td>';
                            $day_count++;
                        }
                        foreach ($calendar_grid as $cell):
                            if ($day_count > 0 && $day_count % 7 == 0) echo '</tr><tr>';
                            $has_data = !empty($cell['entries']);
                            $entry_count = $has_data ? $cell['entries']['entry_count'] : 0;
                            $total_h = $has_data ? round($cell['entries']['total_sec'] / 3600, 1) : 0;
                            $ss_count = $cell['screenshots_count'];
                        ?>
                            <td class="<?= $has_data ? 'info' : '' ?>" style="cursor: pointer; vertical-align: top; padding: 8px; height: 80px; width: 14.28%;"
                                onclick="showDayDetails('<?= $cell['date'] ?>')">
                                <strong><?= $cell['day'] ?></strong>
                                <?php if ($has_data): ?>
                                    <br><small class="text-primary"><?= $entry_count ?> entries</small>
                                    <br><small class="text-success"><?= $total_h ?>h</small>
                                <?php endif; ?>
                                <?php if ($ss_count > 0): ?>
                                    <br><small class="text-warning"><?= $ss_count ?> ss</small>
                                <?php endif; ?>
                            </td>
                        <?php
                            $day_count++;
                        endforeach;
                        while ($day_count % 7 != 0) {
                            echo '<td style="background: #f9f9f9;"></td>';
                            $day_count++;
                        }
                        echo '</tr>';
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<!-- Day Details Modal -->
<div class="modal fade" id="dayDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">Day Details</h4>
            </div>
            <div class="modal-body" id="day-detail-body">
                <p class="text-center">Loading...</p>
            </div>
        </div>
    </div>
</div>

<script>
function showDayDetails(date) {
    $('#dayDetailModal').modal('show');
    $('#day-detail-body').html('<p class="text-center">Loading...</p>');
    $.get('<?= base_url('admin/timesync/day_details/') ?>' + date, function (data) {
        var html = '<h5>' + date + '</h5>';
        html += '<h5>Entries (' + data.entries.length + ')</h5>';
        if (data.entries.length > 0) {
            html += '<table class="table table-striped"><thead><tr><th>User</th><th>Start</th><th>Stop</th><th>Duration</th><th>Type</th></tr></thead><tbody>';
            $.each(data.entries, function (i, e) {
                var dur = '0:00';
                if (e.total_seconds) {
                    var h = Math.floor(e.total_seconds / 3600);
                    var m = Math.floor((e.total_seconds % 3600) / 60);
                    dur = h + ':' + (m < 10 ? '0' : '') + m;
                }
                html += '<tr><td>' + (e.fullname || 'User #' + e.user_id) + '</td><td>' + (e.started_at ? e.started_at.substr(11,5) : '-') + '</td><td>' + (e.stopped_at ? e.stopped_at.substr(11,5) : '-') + '</td><td>' + dur + '</td><td>' + (e.type || '-') + '</td></tr>';
            });
            html += '</tbody></table>';
        } else {
            html += '<p class="text-muted">No entries</p>';
        }
        html += '<h5>Screenshots (' + data.screenshots.length + ')</h5>';
        if (data.screenshots.length > 0) {
            html += '<div class="row">';
            $.each(data.screenshots, function (i, s) {
                html += '<div class="col-md-3 col-sm-4 col-xs-6 mb-sm"><a href="<?= base_url('admin/timesync/view_image/') ?>' + s.id + '" target="_blank"><img src="<?= base_url('admin/timesync/view_image/') ?>' + s.id + '" class="img-responsive img-thumbnail" style="height:100px;object-fit:cover;"></a><p class="small text-center">' + (s.fullname || 'User') + '<br>' + s.captured_at.substr(11,5) + '</p></div>';
            });
            html += '</div>';
        } else {
            html += '<p class="text-muted">No screenshots</p>';
        }
        $('#day-detail-body').html(html);
    });
}
</script>
```

- [ ] **Step 3: Add the `entries()` route to Timesync.php**

Add the entries() method (shown in Step 1 above).

- [ ] **Step 4: Commit**

```bash
git add application/controllers/admin/Timesync.php application/views/admin/timesync/entries.php application/views/admin/timesync/calendar.php
git commit -m "feat(timesync): add entries datatable and calendar views"
```

---
### Task 7: Views — user_report.php rewrite with 3 tabs

**Files:**
- Rewrite: `application/views/admin/timesync/user_report.php`

- [ ] **Step 1: Write user_report.php**

```php
<div class="row">
    <div class="col-lg-12">
        <section class="panel panel-custom">
            <header class="panel-heading">
                <h3 class="panel-title">
                    <?= $title ?> — <?= htmlspecialchars($user->fullname ?? $user->username) ?>
                </h3>
            </header>
            <div class="panel-body">
                <!-- User header -->
                <div class="row mb-lg">
                    <div class="col-md-2 text-center">
                        <?php
                        $avatar = (!empty($user->avatar) && file_exists(FCPATH . $user->avatar))
                            ? base_url($user->avatar)
                            : base_url('assets/img/user/default_avatar.jpg');
                        ?>
                        <img src="<?= $avatar ?>" class="img-circle" style="width: 80px; height: 80px; object-fit: cover;">
                    </div>
                    <div class="col-md-3">
                        <h4><?= htmlspecialchars($user->fullname ?? $user->username) ?></h4>
                        <p class="text-muted">Role: <?= $user->role_id == 1 ? 'Admin' : ($user->role_id == 3 ? 'Manager' : 'Employee') ?></p>
                    </div>
                    <div class="col-md-2">
                        <div class="panel panel-info" style="margin:0">
                            <div class="panel-body text-center">
                                <h3><?= round($total_seconds / 3600, 1) ?>h</h3>
                                <p class="text-muted small">Total Hours</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="panel panel-success" style="margin:0">
                            <div class="panel-body text-center">
                                <h3><?= $entry_count ?></h3>
                                <p class="text-muted small">Entries</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="panel panel-warning" style="margin:0">
                            <div class="panel-body text-center">
                                <h3><?= $screenshot_count ?></h3>
                                <p class="text-muted small">Screenshots</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Date Filter -->
                <form method="get" class="form-inline mb-lg">
                    <input type="hidden" name="tab" value="<?= $active_tab ?>">
                    <div class="form-group">
                        <label>From: </label>
                        <input type="date" name="from" class="form-control input-sm" value="<?= $from ?>">
                    </div>
                    <div class="form-group ml-sm">
                        <label>To: </label>
                        <input type="date" name="to" class="form-control input-sm" value="<?= $to ?>">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm ml-sm">Filter</button>
                </form>

                <!-- Tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="<?= $active_tab == 'timeline' ? 'active' : '' ?>">
                        <a href="?<?= http_build_query(array_merge($_GET, ['tab' => 'timeline'])) ?>">Timeline</a>
                    </li>
                    <li role="presentation" class="<?= $active_tab == 'apps' ? 'active' : '' ?>">
                        <a href="?<?= http_build_query(array_merge($_GET, ['tab' => 'apps'])) ?>">App Usage</a>
                    </li>
                    <li role="presentation" class="<?= $active_tab == 'tasks' ? 'active' : '' ?>">
                        <a href="?<?= http_build_query(array_merge($_GET, ['tab' => 'tasks'])) ?>">Tasks</a>
                    </li>
                </ul>

                <div class="tab-content mt-lg">
                    <!-- Timeline Tab -->
                    <div role="tabpanel" class="tab-pane <?= $active_tab == 'timeline' ? 'active' : '' ?>">
                        <table class="table table-striped DataTables">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Started</th>
                                    <th>Stopped</th>
                                    <th>Duration</th>
                                    <th>Task</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($entries)): ?>
                                    <?php foreach ($entries as $e): ?>
                                        <tr>
                                            <td><?= date('Y-m-d', strtotime($e->started_at)) ?></td>
                                            <td><?= htmlspecialchars($e->type) ?></td>
                                            <td><?= $e->started_at ? date('H:i:s', strtotime($e->started_at)) : '-' ?></td>
                                            <td><?= $e->stopped_at ? date('H:i:s', strtotime($e->stopped_at)) : '-' ?></td>
                                            <td><?= gmdate('H:i:s', $e->total_seconds) ?></td>
                                            <td>
                                                <?php if (!empty($e->task_id)): ?>
                                                    <a href="<?= base_url('admin/tasks/view/' . $e->task_id) ?>">#<?= $e->task_id ?></a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center">No entries found</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- App Usage Tab -->
                    <div role="tabpanel" class="tab-pane <?= $active_tab == 'apps' ? 'active' : '' ?>">
                        <div class="row">
                            <div class="col-md-5">
                                <canvas id="appUsageChart" height="200"></canvas>
                            </div>
                            <div class="col-md-7">
                                <table class="table table-striped">
                                    <thead>
                                        <tr><th>Application</th><th>Hours</th><th>%</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($app_usage)): ?>
                                            <?php $total_all = array_sum(array_map(function($a) { return $a->total_sec; }, $app_usage)); ?>
                                            <?php foreach ($app_usage as $a): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($a->app_name) ?></td>
                                                    <td><?= round($a->total_sec / 3600, 1) ?>h</td>
                                                    <td><?= $total_all > 0 ? round(($a->total_sec / $total_all) * 100, 1) : 0 ?>%</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="3" class="text-center">No app usage data</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Tasks Tab -->
                    <div role="tabpanel" class="tab-pane <?= $active_tab == 'tasks' ? 'active' : '' ?>">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Status</th>
                                    <th>Assignee</th>
                                    <th>Due Date</th>
                                    <th>Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($user_tasks)): ?>
                                    <?php foreach ($user_tasks as $t): ?>
                                        <tr>
                                            <td>
                                                <a href="<?= base_url('admin/tasks/view/' . $t->task_id) ?>">
                                                    <?= htmlspecialchars($t->task_name) ?>
                                                </a>
                                            </td>
                                            <td><span class="label label-<?= $t->task_status == 'completed' ? 'success' : ($t->task_status == 'in_progress' ? 'info' : 'default') ?>"><?= htmlspecialchars($t->task_status) ?></span></td>
                                            <td><?= htmlspecialchars($t->assignee_name ?? $t->username ?? '-') ?></td>
                                            <td><?= !empty($t->due_date) && $t->due_date != '0000-00-00' ? date('Y-m-d', strtotime($t->due_date)) : '-' ?></td>
                                            <td>
                                                <div class="progress" style="height: 6px; margin: 0; min-width: 80px;">
                                                    <div class="progress-bar progress-bar-success" style="width: <?= (int)$t->task_progress ?>%"></div>
                                                </div>
                                                <small><?= (int)$t->task_progress ?>%</small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center">No tasks for this period</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
$(function () {
    var appLabels = <?= $app_usage_labels ?: '[]' ?>;
    var appValues = <?= $app_usage_values ?: '[]' ?>;
    if (appLabels.length > 0 && document.getElementById('appUsageChart')) {
        var colors = ['#36a2eb','#ff6384','#ffce56','#4bc0c0','#9966ff','#ff9f40','#c9cbcf','#7bc043','#f37735','#ee4037'];
        new Chart(document.getElementById('appUsageChart'), {
            type: 'pie',
            data: {
                labels: appLabels,
                datasets: [{
                    data: appValues.map(function(v) { return Math.round(v / 3600 * 10) / 10; }),
                    backgroundColor: colors.slice(0, appLabels.length),
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } }
                }
            }
        });
    }
});
</script>
```

- [ ] **Step 2: Commit**

```bash
git add application/views/admin/timesync/user_report.php
git commit -m "feat(timesync): rewrite user report with 3-tab layout and Chart.js"
```

---
### Task 8: Views — enhance screenshots.php + usage_report.php

**Files:**
- Modify: `application/views/admin/timesync/screenshots.php`
- Modify: `application/views/admin/timesync/usage_report.php`

- [ ] **Step 1: Enhance screenshots.php with lightbox and better layout**

```php
<div class="row">
    <div class="col-lg-12">
        <section class="panel panel-custom">
            <header class="panel-heading">
                <h3 class="panel-title"><?= $title ?? 'Screenshots' ?></h3>
            </header>
            <div class="panel-body">
                <form method="get" class="form-inline mb-lg">
                    <div class="form-group">
                        <label>User: </label>
                        <select name="user_id" class="form-control input-sm">
                            <option value="">All Users</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u->user_id ?>" <?= $this->input->get('user_id') == $u->user_id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u->fullname ?? $u->user_id) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group ml-sm">
                        <label>Task ID: </label>
                        <input type="number" name="task_id" class="form-control input-sm" value="<?= $this->input->get('task_id') ?>" placeholder="Task #">
                    </div>
                    <div class="form-group ml-sm">
                        <label>From: </label>
                        <input type="date" name="from" class="form-control input-sm" value="<?= $this->input->get('from') ?>">
                    </div>
                    <div class="form-group ml-sm">
                        <label>To: </label>
                        <input type="date" name="to" class="form-control input-sm" value="<?= $this->input->get('to') ?>">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm ml-sm">Filter</button>
                </form>

                <div class="row" id="screenshot-gallery">
                    <?php if (!empty($screenshots)): ?>
                        <?php foreach ($screenshots as $s): ?>
                            <div class="col-md-3 col-sm-4 col-xs-6 mb-sm">
                                <div class="panel panel-default" style="margin-bottom: 0;">
                                    <a href="#" onclick="showScreenshot('<?= base_url('admin/timesync/view_image/' . $s->id) ?>'); return false;">
                                        <img src="<?= base_url('admin/timesync/view_image/' . $s->id) ?>" class="img-responsive" style="width: 100%; height: 150px; object-fit: cover;">
                                    </a>
                                    <div class="panel-body" style="padding: 6px 8px;">
                                        <p class="small" style="margin: 0;">
                                            <strong><?= htmlspecialchars($s->fullname ?? 'User') ?></strong><br>
                                            <?= date('M d, Y H:i', strtotime($s->captured_at)) ?>
                                        </p>
                                        <?php if (!empty($s->task_id)): ?>
                                            <a href="<?= base_url('admin/tasks/view/' . $s->task_id) ?>" class="small"><?= htmlspecialchars($s->task_name ?? 'Task #' . $s->task_id) ?></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-md-12"><p class="text-center">No screenshots found</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Lightbox Modal -->
<div class="modal fade" id="screenshotModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body text-center">
                <img id="screenshot-full" src="" class="img-responsive" style="margin: 0 auto; max-height: 80vh;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function showScreenshot(src) {
    $('#screenshot-full').attr('src', src);
    $('#screenshotModal').modal('show');
}
</script>
```

- [ ] **Step 2: Enhance usage_report.php with Chart.js pie chart**

Add a Chart.js doughnut chart at the top of the usage_report view, after the filter form and before the user_scores row. Insert this after line ~35 (after filter form):

```php
<?php if (!empty($user_scores) && count($user_scores) > 0): ?>
<div class="row mb-lg">
    <div class="col-md-12">
        <div class="panel panel-custom">
            <div class="panel-heading"><h4 class="panel-title">Hours Distribution</h4></div>
            <div class="panel-body">
                <canvas id="usageDistChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>
<script>
$(function () {
    var usageData = <?= json_encode(array_map(function($uid, $score) {
        return ['label' => 'User #' . $uid, 'hours' => round($score['total_seconds'] / 3600, 1)];
    }, array_keys($user_scores), $user_scores)) ?>;
    if (usageData.length > 0) {
        var colors = ['#36a2eb','#ff6384','#ffce56','#4bc0c0','#9966ff','#ff9f40','#c9cbcf','#7bc043','#f37735','#ee4037'];
        new Chart(document.getElementById('usageDistChart'), {
            type: 'bar',
            data: {
                labels: usageData.map(function(d) { return d.label; }),
                datasets: [{
                    label: 'Hours',
                    data: usageData.map(function(d) { return d.hours; }),
                    backgroundColor: colors.slice(0, usageData.length),
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } },
                plugins: { legend: { display: false } }
            }
        });
    }
});
</script>
<?php endif; ?>
```

- [ ] **Step 3: Commit**

```bash
git add application/views/admin/timesync/screenshots.php application/views/admin/timesync/usage_report.php
git commit -m "feat(timesync): enhance screenshots gallery and usage report with Chart.js"
```

---
### Task 9: Full test suite & final verification

**Files:**
- Modify: `application/tests/controllers/admin/TimesyncTest.php`

- [ ] **Step 1: Write comprehensive test methods**

Add more tests to TimesyncTest.php:

```php
public function testDashboardWithDateFilterWorks()
{
    $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync?from=2026-01-01&to=2026-12-31');
    $this->assertEquals(200, $res['code']);
    $this->assertStringContainsString('daily_chart_labels', $res['body']);
}

public function testUserReportWithInvalidUserRedirects()
{
    $ch = curl_init('http://localhost/tic_crm/index.php/admin/timesync/user/99999');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEFILE => $this->cookieFile,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 5,
    ]);
    $resp = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    $this->assertStringContainsString('timesync', $info['url']); // redirected to timesync list
}

public function testEntriesDatatableHasCorrectStructure()
{
    $res = $this->get('http://localhost/tic_crm/index.php/admin/timesync/entries_datatable?draw=1');
    $data = json_decode($res['body'], true);
    $this->assertArrayHasKey('draw', $data);
    $this->assertArrayHasKey('recordsTotal', $data);
    $this->assertArrayHasKey('recordsFiltered', $data);
    $this->assertArrayHasKey('data', $data);
    $this->assertIsArray($data['data']);
}
```

- [ ] **Step 2: Run full admin test suite**

Run: `vendor\bin\phpunit.bat --testsuite admin`

Expected: ALL tests pass (all TimesyncTest methods + all other test files).

- [ ] **Step 3: Verify Chart.js is properly included**

Check `http://localhost/tic_crm/index.php/admin/timesync` renders Chart.js charts without JS console errors.

- [ ] **Step 4: Final commit**

```bash
git add application/tests/controllers/admin/TimesyncTest.php
git commit -m "test(timesync): add comprehensive test coverage for all endpoints"
```

---
## Spec Coverage Map

| Spec Requirement | Task | Status |
|---|---|---|
| Dashboard — KPI cards | Task 2 (step 4) + Task 5 (step 1) | ✅ |
| Dashboard — daily hours bar chart | Task 2 (step 1 helper) + Task 5 (step 1) | ✅ |
| Dashboard — user distribution doughnut | Task 2 (step 1 helper) + Task 5 (step 1) | ✅ |
| Dashboard — user grid cards | Task 2 (step 1 helper) + Task 5 (step 1) | ✅ |
| Dashboard — date range filter | Task 2 (step 4) + Task 5 (step 1) | ✅ |
| User drill-down — Timeline tab | Task 4 (step 3+4) + Task 7 (step 1) | ✅ |
| User drill-down — App Usage tab | Task 4 (step 3+4) + Task 7 (step 1) | ✅ |
| User drill-down — Tasks tab | Task 4 (step 3+4) + Task 7 (step 1) | ✅ |
| Calendar — month grid | Task 3 (step 3) + Task 6 (step 2) | ✅ |
| Calendar — day details modal | Task 3 (step 4) + Task 6 (step 2) | ✅ |
| Time entries datatable | Task 3 (step 5) + Task 6 (step 1) | ✅ |
| Screenshots — enhanced gallery + lightbox | Task 8 (step 1) | ✅ |
| Usage report — Chart.js integration | Task 8 (step 2) | ✅ |
| Chart.js library include | Task 1 (step 1) | ✅ |
| Backend test suite | Task 9 (step 1+2) | ✅ |
