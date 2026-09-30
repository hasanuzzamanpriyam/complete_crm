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

$errorPattern = '/Fatal error|Parse error|Uncaught\s|<b>Warning\b|<b>Notice\b|'
              . 'Allowed memory size|Cannot redeclare|Call to undefined|'
              . 'mysqli_sql_exception|SQLSTATE\[/i';

$cookieJar = (getenv('TEMP') ?: sys_get_temp_dir()) . '/tic_crm_cal_' . getmypid() . '.jar';

if (!function_exists('curl_init')) {
    fwrite(STDERR, "FATAL: the PHP curl extension is required.\n");
    exit(1);
}

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
        unset($ch);
        return ['error' => $err, 'status' => 0, 'body' => ''];
    }
    $headerLen = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $body = substr($raw, $headerLen);
    curl_close($ch);
    unset($ch);
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
    if (is_file($cookieJar)) { @unlink($cookieJar); }
    exit(1);
}

$checks = [];
$titleOld = 'Plan e2e event ' . date('His');
$titleNew = 'Plan e2e edited ' . date('His');
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
if ($createdId === 0 && preg_match('/"id"\s*:\s*(\d+)/', $res['body'], $m)) {
    $createdId = (int) $m[1];
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
    preg_match($errorPattern, $page['body'])
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
if (is_file($cookieJar)) { @unlink($cookieJar); }
exit($fail === 0 ? 0 : 1);
