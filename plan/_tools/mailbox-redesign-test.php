<?php
/**
 * Mailbox redesign e2e test (TDD companion - written BEFORE implementation).
 *
 * Asserts: conditional stylesheet + Design-A markup on all folder routes
 * (admin + client), the fixture-backed inbox list row, the read page, and
 * compose; no stylesheet leak onto non-mailbox pages; no PHP errors on any
 * mailbox screen.
 *
 * Runs two sessions (admin jar A, client jar B) so client-role routes are
 * fetched with a client session (no role-redirect false failures), and pins
 * the read pages to the fixed fixture IDs seeded by mailbox-fixtures.sql
 * (admin inbox_id 900001, client inbox_id 900002). Every assertion executes:
 * zero SKIPs.
 *
 *   php plan/_tools/mailbox-redesign-test.php [--base=URL]
 *   composer test:mailbox
 *
 * Exits 0 when all 17 assertions pass, 1 otherwise.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$opts = getopt('', ['base::', 'help']);
if (isset($opts['help'])) {
    echo "Usage: php plan/_tools/mailbox-redesign-test.php [--base=URL]\n";
    exit(0);
}
$baseUrl = rtrim($opts['base'] ?? 'http://localhost/complete_crm', '/');
$user = 'forid';
$pass = 'Admin@123';
$clientUser = 'mailbox_client';
$clientPass = 'ClientMail@123';

$errorPattern = '/Fatal error|Parse error|Uncaught\s|<b>Warning\b|<b>Notice\b|'
    . '<b>Deprecated\b|Allowed memory size|Cannot redeclare|Call to undefined|'
    . 'mysqli_sql_exception|SQLSTATE\[|A Database Error Occurred|Error Number:|'
    . 'Undefined (variable|array|property|offset)/i';

$jarAdmin = (getenv('TEMP') ?: sys_get_temp_dir()) . '/tic_crm_mailbox_admin_' . getmypid() . '.jar';
$jarClient = (getenv('TEMP') ?: sys_get_temp_dir()) . '/tic_crm_mailbox_client_' . getmypid() . '.jar';
$cookieJar = $jarAdmin; // default jar (admin session) used when request() gets no explicit jar

if (!function_exists('curl_init')) {
    fwrite(STDERR, "FATAL: the PHP curl extension is required.\n");
    exit(1);
}

function request(string $method, string $url, array $post = [], array $headers = [], ?string $jar = null): array
{
    global $cookieJar;
    $jar = $jar ?: $cookieJar;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 8,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_ENCODING       => '',
        CURLOPT_USERAGENT      => 'TIC-CRM-mailbox-test/1.0',
        CURLOPT_COOKIEJAR      => $jar,
        CURLOPT_COOKIEFILE     => $jar,
    ]);
    if ($headers) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
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
    unset($ch); // PHP 8: cookie jar is flushed when the handle object is freed
    $set = [];
    if (is_file($jar)) {
        foreach (file($jar, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $p = explode("\t", $line);
            if (count($p) >= 7) {
                $set[$p[5]] = $p[6];
            }
        }
    }
    return ['status' => $status, 'body' => $body, 'cookie' => $set];
}

function markers_in(string $body, array $needles): ?string
{
    foreach ($needles as $n) {
        if (strpos($body, $n) === false) {
            return 'missing marker: ' . $n;
        }
    }
    return null;
}

function fatal(string $msg): void
{
    global $jarAdmin, $jarClient;
    fwrite(STDERR, 'FATAL: ' . $msg . "\n");
    foreach ([$jarAdmin, $jarClient] as $jar) {
        if (is_file($jar)) { @unlink($jar); }
    }
    exit(1);
}

function jar_for(string $role): string
{
    global $jarAdmin, $jarClient;
    return $role === 'admin' ? $jarAdmin : $jarClient;
}

/* ---------- session A: admin ---------- */
$login = request('POST', $baseUrl . '/login', [
    'user_name' => $user,
    'password'  => $pass,
    'lat'       => '0.000000',
    'long'      => '0.000000',
]);
if (strpos($login['body'], 'name="user_name"') !== false) {
    fatal('admin login failed — login page returned (check credentials / server).');
}
if (empty($login['cookie']['uniquecoder']) || strlen($login['cookie']['uniquecoder']) < 10) {
    fatal('admin login produced no session cookie.');
}

/* ---------- session B: client ---------- */
$clientLogin = request('POST', $baseUrl . '/login', [
    'user_name' => $clientUser,
    'password'  => $clientPass,
    'lat'       => '0.000000',
    'long'      => '0.000000',
], [], $jarClient);
if (strpos($clientLogin['body'], 'name="user_name"') !== false) {
    fatal('client login failed — login page returned (check mailbox_client credentials / fixtures).');
}
$clientSession = $clientLogin['cookie']['uniquecoder'] ?? null;
if ($clientSession === null) {
    foreach ($clientLogin['cookie'] as $name => $value) {
        if (strlen($value) >= 10) {
            $clientSession = $value;
            break;
        }
    }
}
if ($clientSession === null || strlen($clientSession) < 10) {
    fatal('client login produced no session cookie.');
}

$checks = [];

/* ---------- 1. folder routes: stylesheet + Design-A markup (admin + client) ---------- */
$folders = ['inbox', 'sent', 'draft', 'favourites', 'trash'];
foreach (['admin', 'client'] as $role) {
    foreach ($folders as $f) {
        $name = "$role mailbox/$f has CSS + card markup";
        $page = request('GET', "$baseUrl/$role/mailbox/index/$f", [], [], jar_for($role));
        $reason = null;
        if ($page['status'] !== 200) {
            $reason = 'HTTP ' . $page['status'];
        } elseif (preg_match($errorPattern, $page['body'], $m)) {
            $reason = 'PHP error: ' . $m[0];
        } else {
            $reason = markers_in($page['body'], ['mailbox_modern.css', 'f-item', 'list-card']);
        }
        if ($reason === null) {
            if (strpos($page['body'], 'mrow') === false
                && strpos($page['body'], 'mb-empty') === false) {
                $reason = 'no mrow row and no mb-empty state';
            }
        }
        $checks[$name] = $reason;
    }
}

/* ---------- 1b. admin inbox list shows the seeded fixture row ---------- */
$name = 'admin inbox shows fixture row';
$page = request('GET', "$baseUrl/admin/mailbox/index/inbox", [], [], $jarAdmin);
$reason = null;
if ($page['status'] !== 200) {
    $reason = 'HTTP ' . $page['status'];
} elseif (preg_match($errorPattern, $page['body'], $m)) {
    $reason = 'PHP error: ' . $m[0];
} else {
    $reason = markers_in($page['body'], ['Mailbox redesign fixture', 'read_inbox_mail/900001']);
}
$checks[$name] = $reason;

/* ---------- 2. read page (fixed fixture IDs - always executes) ---------- */
$readIds = ['admin' => 900001, 'client' => 900002];
foreach ($readIds as $role => $id) {
    $name = "$role read page has read-card markup";
    $page = request('GET', "$baseUrl/$role/mailbox/index/read_inbox_mail/$id", [], [], jar_for($role));
    $reason = null;
    if ($page['status'] !== 200) {
        $reason = 'HTTP ' . $page['status'];
    } elseif (preg_match($errorPattern, $page['body'], $m)) {
        $reason = 'PHP error: ' . $m[0];
    } else {
        $reason = markers_in($page['body'], ['mailbox_modern.css', 'Mailbox redesign fixture', 'read-card', 'r-subj']);
    }
    $checks[$name] = $reason;
}

/* ---------- 3. compose (admin + client) ---------- */
foreach (['admin', 'client'] as $role) {
    $name = "$role compose has mb-compose markup";
    $page = request('GET', "$baseUrl/$role/mailbox/index/compose", [], [], jar_for($role));
    $reason = null;
    if ($page['status'] !== 200) {
        $reason = 'HTTP ' . $page['status'];
    } elseif (preg_match($errorPattern, $page['body'], $m)) {
        $reason = 'PHP error: ' . $m[0];
    } else {
        $reason = markers_in($page['body'], ['mailbox_modern.css', 'mb-compose', 'send_mail']);
    }
    $checks[$name] = $reason;
}

/* ---------- 4. no stylesheet leak on non-mailbox pages ---------- */
$leakPages = ['/admin/settings' => $jarAdmin, '/client/dashboard' => $jarClient];
foreach ($leakPages as $path => $jar) {
    $page = request('GET', $baseUrl . $path, [], [], $jar);
    $checks["no leak on $path"] =
        ($page['status'] === 200 && strpos($page['body'], 'mailbox_modern.css') === false)
            ? null : 'mailbox_modern.css leaked (or HTTP ' . $page['status'] . ')';
}

/* ---------- report ---------- */
$pass = 0; $fail = 0; $skip = 0;
foreach ($checks as $name => $reason) {
    if ($reason === null) {
        printf("PASS  %s\n", $name);
        $pass++;
    } else {
        printf("FAIL  %s\n      -> %s\n", $name, $reason);
        $fail++;
    }
}
printf("==============================================================================\n");
printf("%d assertions — %d passed, %d failed, %d skipped\n",
    count($checks), $pass, $fail, $skip);
foreach ([$jarAdmin, $jarClient] as $jar) {
    if (is_file($jar)) { @unlink($jar); }
}
exit($fail > 0 ? 1 : 0);
