<?php
/**
 * Part A · A0-4 — smoke check
 *
 * Requests the 20 baseline routes and fails on anything broken. This is the
 * regression gate every Part A task must pass before committing.
 *
 *   php plan/_tools/smoke.php [--base=http://localhost/complete_crm] [--db=live|demo]
 *
 * Exits 0 when all routes pass, 1 otherwise.
 *
 * NOTE: for Part A (CSS only) this cannot detect a wrong-coloured button — that
 * is what the A0-2 screenshots are for. It catches structural breakage: PHP
 * fatals, empty pages, 404s, and a missing stylesheet.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

// ---------------------------------------------------------------- config ----

$opts = getopt('', ['base::', 'db::', 'help']);
if (isset($opts['help'])) {
    echo "Usage: php plan/_tools/smoke.php [--base=URL] [--db=live|demo]\n";
    exit(0);
}

$baseUrl = rtrim($opts['base'] ?? 'http://localhost/complete_crm', '/');
$db      = $opts['db'] ?? 'live';

$user = 'forid';
$pass = 'Admin@123';

// The 20 baseline routes from A0-2. Every one verified live 2026-09-26.
// A 307 on the unauthenticated check means "route exists, auth redirected".
$routes = [
    ['GET',  '/',                          0],
    ['GET',  '/login',                     0],
    ['GET',  '/login/register',            0],
    ['GET',  '/login/forgot_password',     0],
    ['GET',  '/career',                    0],
    ['GET',  '/book-free-consultation',    0],
    ['GET',  '/admin/dashboard',           1],
    ['GET',  '/admin/superadmin',          1],
    ['GET',  '/admin/client/manage_client',1],
    ['GET',  '/admin/invoice/manage_invoice', 1],
    ['GET',  '/admin/quotations',          1],
    ['GET',  '/admin/proposals',           1],
    ['GET',  '/admin/projects',            1],
    ['GET',  '/admin/tasks/all_task',      1],
    ['GET',  '/admin/user/user_list',      1],
    ['GET',  '/admin/accounting/chart_of_accounts', 1],
    ['GET',  '/admin/transactions/transactions_report', 1],
    ['GET',  '/admin/attendance/daily_attendance', 1],
    ['GET',  '/admin/payroll/payroll_summary', 1],
    ['GET',  '/admin/report/sales_report', 1],
    ['GET',  '/admin/settings',            1],
    ['GET',  '/client/dashboard',          1],
];

// Failure patterns. These must never appear in a rendered page.
$errorPattern = '/Fatal error|Parse error|Uncaught\s|<b>Warning\b|<b>Notice\b|'
              . 'Allowed memory size|Cannot redeclare|Call to undefined|'
              . 'mysqli_sql_exception|SQLSTATE\[/i';

// ------------------------------------------------------------ http helper ----

/**
 * A shared cookie jar file. curl does NOT forward Set-Cookie across a redirect
 * when only CURLOPT_COOKIE is set — the cookie engine must be enabled, which
 * requires a jar file. Without this, login redirects to /admin/dashboard with no
 * session, which bounces straight back to the login page and every authenticated
 * route silently tests the wrong page.
 */
$cookieJar = (getenv('TEMP') ?: sys_get_temp_dir()) . '/tic_crm_smoke_' . getmypid() . '.jar';

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
        CURLOPT_USERAGENT      => 'TIC-CRM-smoke/1.0',
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
        return ['error' => $err, 'status' => 0, 'body' => '', 'cookie' => []];
    }

    $headerLen = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $status    = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headers   = substr($raw, 0, $headerLen);
    $body      = substr($raw, $headerLen);
    $lastUrl   = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    // Count redirects seen in the final header block, and collect the jar's
    // contents by re-reading the file (more reliable than parsing Set-Cookie).
    $set = [];
    foreach (explode("\r\n\r\n", $headers) as $block) {
        foreach (explode("\r\n", $block) as $line) {
            if (stripos($line, 'Set-Cookie:') === 0) {
                $kv = explode('=', trim(substr($line, 11)), 2);
                if (count($kv) === 2) {
                    $nameVal = explode(';', $kv[1], 2);
                    $set[trim($kv[0])] = $nameVal[0];
                }
            }
        }
    }
    // The jar is only created once curl has flushed cookies to it, so it may not
    // exist on the first call — guard to avoid a warning before it exists.
    if (is_file($cookieJar)) {
        foreach (file($cookieJar, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $jarLine) {
            $p = explode("\t", $jarLine);
            if (count($p) >= 7) { $set[$p[5]] = $p[6]; }
        }
    }

    return ['status' => $status, 'body' => $body, 'cookie' => $set, 'url' => $lastUrl];
}

// ------------------------------------------------------------------ login ----

if (!function_exists('curl_init')) {
    fwrite(STDERR, "FATAL: the PHP curl extension is required.\n");
    exit(1);
}

echo "Smoke check — {$baseUrl}\n";
echo str_repeat('-', 78) . "\n";

$login = request('POST', $baseUrl . '/login', [
    'user_name' => $user,
    'password'  => $pass,
    // Login::index() declares these 'trim|required'. They are normally injected by
    // navigator.geolocation in login_form.php, so a non-browser client must supply
    // them or validation silently fails and returns the form (HTTP 200, no session).
    'lat'       => '0.000000',
    'long'      => '0.000000',
]);

if (empty($login['cookie'])) {
    fwrite(STDERR, "FATAL: login returned no session cookie.\n");
    fwrite(STDERR, "       status={$login['status']}  url=" . ($login['url'] ?? '?') . "\n");
    fwrite(STDERR, "       body snippet: " . substr(strip_tags($login['body']), 0, 300) . "\n");
    exit(1);
}

$cookie = $login['cookie'];

// Session cookie name. config.php sets sess_cookie_name = 'uniquecoder';
// cookie_prefix = '_crm_' is applied by CI only to get_cookie(), not to Set-Cookie.
$sessionCookie = $opts['sessname'] ?? 'uniquecoder';

$loggedIn = isset($cookie[$sessionCookie]) && strlen($cookie[$sessionCookie]) > 10;

echo sprintf("Authenticated as %-14s %s\n", $user, $loggedIn ? "(session cookie ok)" : "(NO session cookie!)");
if (!$loggedIn) {
    fwrite(STDERR, "FATAL: expected cookie [$sessionCookie] after login.\n");
    fwrite(STDERR, "       cookies received: " . implode(', ', array_keys($cookie)) . "\n");
    fwrite(STDERR, "       status={$login['status']}  url=" . ($login['url'] ?? '?') . "\n");
    fwrite(STDERR, "       body snippet: " . substr(strip_tags($login['body']), 0, 300) . "\n");
    exit(1);
}
echo str_repeat('-', 78) . "\n";

// ------------------------------------------------------------------ probe ----

$fail = 0;
$pass = 0;
$report = [];

foreach ($routes as [$method, $path, $needsAuth]) {
    $label = $path === '/' ? '/ (root)' : $path;

    if (!$needsAuth) {
        // Probe unauthenticated first so we can tell a redirect from a 404.
        $probe = request('GET', $baseUrl . $path);
        if ($probe['status'] === 404) {
            $report[] = ['FAIL', $label, '404 not found (route is wrong)'];
            $fail++;
            continue;
        }
    }

    $res = request($method, $baseUrl . $path);
    $status  = $res['status'];
    $body    = $res['body'];
    $len     = strlen($body);
    $problems = [];

    if ($status === 404) {
        $problems[] = '404 not found';
    } elseif ($status !== 200) {
        $problems[] = "HTTP $status";
    }

    if ($len < 2000 && $status === 200) {
        $problems[] = "suspiciously small ($len bytes)";
    }

    if (preg_match($errorPattern, $body, $m)) {
        $problems[] = 'php error: ' . substr($m[0], 0, 40);
    }

    // The theme stylesheet is the whole point of Part A. The shell links either
    // app.css or app.min.css depending on environment, so accept either — but the
    // file must actually be referenced, because A1-2 inserts tokens.css next to it
    // and a broken <link> is silent.
    if ($status === 200
        && !preg_match('#app(\.min)?\.css#', $body)
        && !in_array($path, ['/career', '/book-free-consultation'], true)) {
        $problems[] = 'app.css not referenced';
    }

    // If an authenticated route rendered a password field, the session bounced
    // back to the login page: the route "passed" HTTP-wise but tested nothing.
    if ($needsAuth && $status === 200 && preg_match('#name="password"#i', $body)) {
        $problems[] = 'bounced to login (session not honored)';
    }

    if ($problems) {
        $report[] = ['FAIL', $label, implode('; ', $problems)];
        $fail++;
    } else {
        $report[] = ['PASS', $label, "200  {$len} bytes"];
        $pass++;
    }
}

// ---------------------------------------------------------------- report ----

printf("%-4s %-44s %s\n", '', 'ROUTE', 'RESULT');
echo str_repeat('-', 78) . "\n";

foreach ($report as [$state, $label, $detail]) {
    printf("%-4s %-44s %s\n", $state, $label, $detail);
}

echo str_repeat('=', 78) . "\n";
printf("%d routes — %d passed, %d failed\n", count($routes), $pass, $fail);

// Leave no session credentials on disk.
if (is_file($cookieJar)) { @unlink($cookieJar); }

if ($fail > 0) {
    echo "\nFAILURES ARE EITHER:\n";
    echo "  1. A pre-existing bug   -> record in plan/_baseline/part-a/NOTES.md\n";
    echo "  2. Caused by your change -> REVERT the task before proceeding\n";
}

exit($fail > 0 ? 1 : 0);
