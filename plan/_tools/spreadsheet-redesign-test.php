<?php
/**
 * Spreadsheet redesign e2e test (TDD companion - written BEFORE implementation).
 *
 * Asserts: reachability fixes R1+R2 (client session reaches both tabs on bare,
 * admin-prefixed, AND menu-target URLs — the last proving the /spreadsheet
 * redirect chain — in client layout markers), the conditional stylesheet +
 * Design-A markup on all 4 screens, fixture content for both role trees,
 * context menus (7 / 2 actions), no stylesheet leak onto dashboards, and no
 * PHP errors on any spreadsheet screen.
 *
 *   php plan/_tools/spreadsheet-redesign-test.php [--base=URL]
 *   composer test:spreadsheet
 *
 * Exits 0 when all assertions pass, 1 otherwise.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$opts = getopt('', ['base::', 'help']);
if (isset($opts['help'])) {
    echo "Usage: php plan/_tools/spreadsheet-redesign-test.php [--base=URL]\n";
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

$jarAdmin = (getenv('TEMP') ?: sys_get_temp_dir()) . '/tic_crm_ss_admin_' . getmypid() . '.jar';
$jarClient = (getenv('TEMP') ?: sys_get_temp_dir()) . '/tic_crm_ss_client_' . getmypid() . '.jar';
$cookieJar = $jarAdmin;

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
        CURLOPT_USERAGENT      => 'TIC-CRM-spreadsheet-test/1.0',
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
    unset($ch);
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

function db(): mysqli
{
    static $db = null;
    if ($db === null) {
        $db = @new mysqli('localhost', 'root', '', 'tic_crm');
        if ($db->connect_error) {
            fatal('db connect: ' . $db->connect_error);
        }
    }
    return $db;
}

function check_page(string $name, string $url, string $jar, array $mustContain, array $mustBeAbsent = [])
{
    global $checks, $errorPattern;
    $page = request('GET', $url, [], [], $jar);
    $reason = null;
    if ($page['status'] !== 200) {
        $reason = 'HTTP ' . $page['status'];
    } elseif (preg_match($errorPattern, $page['body'], $m)) {
        $reason = 'PHP error: ' . $m[0];
    } else {
        $reason = markers_in($page['body'], $mustContain);
        if ($reason === null) {
            foreach ($mustBeAbsent as $absent) {
                if (strpos($page['body'], $absent) !== false) {
                    $reason = 'unexpected marker: ' . $absent;
                    break;
                }
            }
        }
        if ($reason === null && preg_match('/lang\([\'"][a-z_]+[\'"]\)/', $page['body'])) {
            $reason = 'raw lang( artifact in output';
        }
    }
    $checks[$name] = $reason;
}

/* ---------- sessions ---------- */
$login = request('POST', $baseUrl . '/login', [
    'user_name' => $user,
    'password'  => $pass,
    'lat'       => '0.000000',
    'long'      => '0.000000',
]);
if (strpos($login['body'], 'name="user_name"') !== false) {
    fatal('admin login failed — login page returned.');
}
if (empty($login['cookie']['uniquecoder']) || strlen($login['cookie']['uniquecoder']) < 10) {
    fatal('admin login produced no session cookie.');
}

$clientLogin = request('POST', $baseUrl . '/login', [
    'user_name' => $clientUser,
    'password'  => $clientPass,
    'lat'       => '0.000000',
    'long'      => '0.000000',
], [], $jarClient);
if (strpos($clientLogin['body'], 'name="user_name"') !== false) {
    fatal('client login failed — check mailbox_client credentials / fixtures.');
}
if (empty($clientLogin['cookie']['uniquecoder']) || strlen($clientLogin['cookie']['uniquecoder']) < 10) {
    fatal('client login produced no session cookie.');
}

$checks = [];

$css = 'spreadsheet_modern.css';
$shell = ['id="ss-panel"', 'id="ss-crumb"', 'list-card', 'mrow'];

/* ---------- 1. admin: my_folder ---------- */
check_page('admin my_folder has shell + fixtures',
    "$baseUrl/admin/spreadsheet/manage/my_folder", $jarAdmin,
    array_merge([$css], $shell, ['SS Redesign Fixture Folder', 'SS Redesign Fixture File', 'data-action="create_folder"', 'right-menu-position']),
    ['caption-a', 'nav-tabs-custom']);

/* ---------- 2. admin: share_folder ---------- */
check_page('admin share_folder has shell + shared fixture',
    "$baseUrl/admin/spreadsheet/manage/share_folder", $jarAdmin,
    array_merge([$css], $shell, ['SS Redesign Shared Folder', 'SS Redesign Shared File', 'data-action="view"']),
    ['caption-a', 'nav-tabs-custom', 'data-action="create_folder"']);

/* ---------- 3. admin: editor (fixture file) ---------- */
check_page('admin editor has chrome + luckysheet',
    "$baseUrl/admin/spreadsheet/new_file_view/910001/910002", $jarAdmin,
    [$css, 'ed-head', 'id="luckysheet"', 'SS Redesign Fixture File']);

/* ---------- 4. admin: share-by-link view (fixture hash) ---------- */
check_page('admin share-by-link view has card chrome',
    "$baseUrl/admin/spreadsheet/file_view_share/ssfixturehash910002", $jarAdmin,
    [$css, 'ss-share-view', 'id="luckysheet"']);

/* ---------- 5. client: bare URLs prove R1+R2 (client layout via company fixture) ---------- */
$clientShell = [$css, 'id="ss-panel"', 'list-card', 'mrow', 'client/dashboard'];
check_page('client bare my_folder reachable + redesigned',
    "$baseUrl/spreadsheet/manage/my_folder", $jarClient,
    array_merge($clientShell, ['SS Redesign Client Folder', 'data-action="create_folder"']),
    ['set_clocking', 'caption-a', 'nav-tabs-custom']);

check_page('client bare share_folder reachable + redesigned',
    "$baseUrl/spreadsheet/manage/share_folder", $jarClient,
    array_merge($clientShell, ['SS Redesign Shared Folder', 'SS Redesign Shared File']),
    ['set_clocking', 'caption-a', 'nav-tabs-custom']);

/* ---------- 6. client: admin-prefixed URL still serves the client ---------- */
check_page('client admin-prefixed my_folder serves client layout',
    "$baseUrl/admin/spreadsheet/manage/my_folder", $jarClient,
    array_merge($clientShell, ['SS Redesign Client Folder']),
    ['set_clocking']);

/* ---------- 7. client: menu link target (R1 chain: /spreadsheet -> manage, 200) ---------- */
check_page('client menu target /spreadsheet lands on redesigned manage',
    "$baseUrl/spreadsheet", $jarClient,
    array_merge($clientShell, ['SS Redesign Client Folder']),
    ['set_clocking', 'caption-a', 'nav-tabs-custom']);

/* ---------- 8. context menu counts (spec 7: 7 on my folder, 2 on share folder) ---------- */
$name = 'admin my_folder context menu has 7 actions';
$page = request('GET', "$baseUrl/admin/spreadsheet/manage/my_folder", [], [], $jarAdmin);
$n = substr_count($page['body'], 'class="context-menu__link"');
$checks[$name] = ($page['status'] === 200 && $n === 7) ? null : "HTTP {$page['status']}, {$n} links (expected 7)";

$name = 'admin share_folder context menu has 2 actions';
$page = request('GET', "$baseUrl/admin/spreadsheet/manage/share_folder", [], [], $jarAdmin);
$n = substr_count($page['body'], 'class="context-menu__link"');
$checks[$name] = ($page['status'] === 200 && $n === 2) ? null : "HTTP {$page['status']}, {$n} links (expected 2)";

/* ---------- 9. DB: R1 data + grant + fixtures + client company ---------- */
$name = 'DB menu_id 24 link is bare spreadsheet';
$row = db()->query("SELECT link FROM tbl_client_menu WHERE menu_id = 24 AND label = 'spreadsheet'")->fetch_row();
$checks[$name] = ($row && $row[0] === 'spreadsheet') ? null : 'link = ' . ($row[0] ?? 'missing row');

$name = 'DB client menu grant seeded (1790, 24)';
$r = db()->query("SELECT COUNT(*) FROM tbl_client_role WHERE user_id = 1790 AND menu_id = 24")->fetch_row();
$checks[$name] = ((int) $r[0] >= 1) ? null : 'grant missing';

$name = 'DB spreadsheet fixtures present (910001-910007 + 3 hashes + company)';
$r = db()->query("SELECT (SELECT COUNT(*) FROM tbl_spreadsheet_my_folder WHERE id BETWEEN 910001 AND 910007), (SELECT COUNT(*) FROM tbl_spreadsheet_hash_share WHERE id IN (910001, 910002, 910003)), (SELECT company FROM tbl_account_details WHERE user_id = 1790)")->fetch_row();
$checks[$name] = ((int) $r[0] === 7 && (int) $r[1] === 3 && (string) $r[2] === '1') ? null : "rows={$r[0]} hashes={$r[1]} company={$r[2]}";

/* ---------- 10. leak checks: dashboards must NOT load the stylesheet ---------- */
$leakPages = ['/admin/dashboard' => $jarAdmin, '/client/dashboard' => $jarClient];
foreach ($leakPages as $path => $jar) {
    $page = request('GET', $baseUrl . $path, [], [], $jar);
    $checks["no leak on $path"] =
        ($page['status'] === 200 && strpos($page['body'], $css) === false)
            ? null : "$css leaked (or HTTP {$page['status']})";
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
