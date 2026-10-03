<?php
/**
 * Mailbox redesign e2e test (TDD companion - written BEFORE implementation).
 *
 * Asserts: conditional stylesheet + Design-A markup on all folder routes
 * (admin + client), the read page, and compose; no stylesheet leak onto
 * non-mailbox pages; no PHP errors on any mailbox screen.
 *
 *   php plan/_tools/mailbox-redesign-test.php [--base=URL]
 *   composer test:mailbox
 *
 * Exits 0 when all assertions pass (SKIPs allowed), 1 otherwise.
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

$errorPattern = '/Fatal error|Parse error|Uncaught\s|<b>Warning\b|<b>Notice\b|'
    . '<b>Deprecated\b|Allowed memory size|Cannot redeclare|Call to undefined|'
    . 'mysqli_sql_exception|SQLSTATE\[|A Database Error Occurred|Error Number:|'
    . 'Undefined (variable|array|property|offset)/i';

$cookieJar = (getenv('TEMP') ?: sys_get_temp_dir()) . '/tic_crm_mailbox_' . getmypid() . '.jar';

if (!function_exists('curl_init')) {
    fwrite(STDERR, "FATAL: the PHP curl extension is required.\n");
    exit(1);
}

function request(string $method, string $url, array $post = [], array $headers = []): array
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
        CURLOPT_USERAGENT      => 'TIC-CRM-mailbox-test/1.0',
        CURLOPT_COOKIEJAR      => $cookieJar,
        CURLOPT_COOKIEFILE     => $cookieJar,
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

function markers_in(string $body, array $needles): ?string
{
    foreach ($needles as $n) {
        if (strpos($body, $n) === false) {
            return 'missing marker: ' . $n;
        }
    }
    return null;
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

/* ---------- 1. folder routes: stylesheet + Design-A markup (admin + client) ---------- */
$folders = ['inbox', 'sent', 'draft', 'favourites', 'trash'];
foreach (['admin', 'client'] as $role) {
    foreach ($folders as $f) {
        $name = "$role mailbox/$f has CSS + card markup";
        $page = request('GET', "$baseUrl/$role/mailbox/index/$f");
        $reason = null;
        if ($page['status'] !== 200) {
            $reason = 'HTTP ' . $page['status'];
        } elseif (preg_match($errorPattern, $page['body'], $m)) {
            $reason = 'PHP error: ' . $m[0];
        } else {
            $reason = markers_in($page['body'], ['mailbox_modern.css', 'list-card', 'f-item']);
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

/* ---------- 2. read page (extract a real inbox id from the list; SKIP if none) ---------- */
foreach (['admin', 'client'] as $role) {
    $name = "$role read page has read-card markup";
    $inbox = request('GET', "$baseUrl/$role/mailbox/index/inbox");
    $id = null;
    if ($inbox['status'] === 200
        && preg_match('#' . $role . '/mailbox/index/read_inbox_mail/(\d+)#', $inbox['body'], $m)) {
        $id = $m[1];
    }
    if ($id === null) {
        $checks[$name] = 'SKIP: no inbox message to open';
        continue;
    }
    $page = request('GET', "$baseUrl/$role/mailbox/index/read_inbox_mail/$id");
    $reason = null;
    if ($page['status'] !== 200) {
        $reason = 'HTTP ' . $page['status'];
    } elseif (preg_match($errorPattern, $page['body'], $m)) {
        $reason = 'PHP error: ' . $m[0];
    } else {
        $reason = markers_in($page['body'], ['mailbox_modern.css', 'read-card', 'r-subj']);
    }
    $checks[$name] = $reason;
}

/* ---------- 3. compose (admin + client) ---------- */
foreach (['admin', 'client'] as $role) {
    $name = "$role compose has mb-compose markup";
    $page = request('GET', "$baseUrl/$role/mailbox/index/compose");
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
$leakPages = ['/admin/settings', '/client/dashboard'];
foreach ($leakPages as $path) {
    $page = request('GET', $baseUrl . $path);
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
    } elseif (strpos($reason, 'SKIP') === 0) {
        printf("SKIP  %s\n      -> %s\n", $name, substr($reason, 5));
        $skip++;
    } else {
        printf("FAIL  %s\n      -> %s\n", $name, $reason);
        $fail++;
    }
}
printf("==============================================================================\n");
printf("%d assertions — %d passed, %d failed, %d skipped\n",
    count($checks), $pass, $fail, $skip);
if (is_file($cookieJar)) { @unlink($cookieJar); }
exit($fail > 0 ? 1 : 0);
