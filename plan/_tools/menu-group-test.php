<?php
/**
 * A2-7 / A2-8 — sidebar grouping gate.
 *
 *   php plan/_tools/menu-group-test.php config   canonical group map
 *   php plan/_tools/menu-group-test.php db      column + backfill invariants
 *   php plan/_tools/menu-group-test.php render  rendered sidebar HTML
 *   php plan/_tools/menu-group-test.php admin   admin UI + save round-trip
 *   php plan/_tools/menu-group-test.php all     all of the above
 *
 * Exits 0 when every check passes, 1 otherwise.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$ROOT = dirname(__DIR__, 2);
$opts = getopt('', ['db-name::', 'base::', 'help']);
// getopt() stops parsing at the first non-option argument, so flags must come
// BEFORE the subcommand (--db-name=tic_crm_demo db). That leaves $argv[1] holding
// the flag rather than the subcommand, so take the first argument that is not
// an option instead of assuming position 1.
$mode = 'all';
foreach (array_slice($argv, 1) as $arg) {
    if ($arg !== '' && $arg[0] !== '-') {
        $mode = $arg;
        break;
    }
}

if (isset($opts['help'])) {
    echo "Usage: php plan/_tools/menu-group-test.php [config|db|render|admin|all]\n";
    exit(0);
}

$EXPECTED_ORDER = ['crm', 'projects', 'hr', 'finance', 'tools', 'admin'];
$EXPECTED_LABEL = [
    'crm'      => 'CRM & Clients',
    'projects' => 'Projects & Tasks',
    'hr'       => 'HR & Payroll',
    'finance'  => 'Finance & Billing',
    'tools'    => 'Tools & Admin',
    'admin'    => 'Super Admin',
];

// The 45 live top-level menu_ids, with the group each must carry.
// 73 (utilities) is the HR module container — it parents 71 holiday and
// 69 goal_tracking — and 209 warning / 210 promotion are employee records whose
// siblings 211 termination / 212 resignation are already in hr. All three were
// moved to hr after review of the live DB and the app's own language files.
$EXPECTED_BACKFILL = [
    'crm'      => [1, 2, 4, 5, 227, 228, 139, 141],
    'projects' => [6, 54, 55, 56, 57, 58, 233, 234, 269],
    'hr'       => [70, 72, 73, 85, 100, 101, 102, 105, 209, 210, 211, 212],
    'finance'  => [12, 29, 75, 89, 150, 213],
    'tools'    => [24, 25, 26, 42, 110, 235, 236, 248, 263],
    'admin'    => [243],
];

$fail = 0;
$pass = 0;

function ok(string $label): void
{
    global $pass;
    $pass++;
    echo "  PASS  {$label}\n";
}

function bad(string $label, string $detail = ''): void
{
    global $fail;
    $fail++;
    echo "  FAIL  {$label}" . ($detail !== '' ? "  ({$detail})" : '') . "\n";
}

function check(bool $cond, string $label, string $detail = ''): void
{
    $cond ? ok($label) : bad($label, $detail);
}

function heading(string $text): void
{
    echo "\n{$text}\n" . str_repeat('-', 78) . "\n";
}

// ------------------------------------------------------------------ config ----

function testConfig(): void
{
    global $ROOT, $EXPECTED_ORDER, $EXPECTED_LABEL;

    heading('config — canonical group map');

    $file = $ROOT . '/application/config/menu_groups.php';
    if (!is_file($file)) {
        bad('application/config/menu_groups.php exists');
        return;
    }
    ok('application/config/menu_groups.php exists');

    $config = [];
    // Every config file in this project opens with
    // `defined('BASEPATH') OR exit(...)`. Under CLI that guard would exit(0)
    // and the gate would report success without testing anything, so satisfy it
    // before the require. The value is never read.
    if (!defined('BASEPATH')) { define('BASEPATH', $ROOT . '/system/'); }
    require $file;

    $map = $config['menu_groups'] ?? null;
    if (!is_array($map)) {
        bad('$config[menu_groups] is defined', 'missing or not an array');
        return;
    }
    ok('$config[menu_groups] is defined');

    check(
        array_keys($map) === $EXPECTED_ORDER,
        'group order matches the design',
        'got ' . implode(',', array_keys($map))
    );

    foreach ($EXPECTED_LABEL as $key => $label) {
        check(($map[$key] ?? null) === $label, "label for '{$key}' is '{$label}'", 'got ' . var_export($map[$key] ?? null, true));
    }

    // 'tools' is the documented fallback for unknown values, so it must exist.
    check(isset($map['tools']), "'tools' fallback group exists");
}

// --------------------------------------------------------------------- db ----

function dbPdo(string $dbName): PDO
{
    $pdo = new PDO("mysql:host=127.0.0.1;dbname={$dbName}", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

function testDb(string $dbName): void
{
    global $EXPECTED_ORDER, $EXPECTED_BACKFILL;

    heading("db — {$dbName}");

    try {
        $pdo = dbPdo($dbName);
    } catch (PDOException $e) {
        bad("connect to {$dbName}", $e->getMessage());
        return;
    }

    $cols = $pdo->query("SHOW COLUMNS FROM `tbl_menu` LIKE 'menu_group'")->fetchAll();
    if (count($cols) === 0) {
        bad('tbl_menu.menu_group exists', 'column missing — run the migration');
        return;
    }
    ok('tbl_menu.menu_group exists');

    $allowed = "'" . implode("','", $EXPECTED_ORDER) . "'";
    $bad = $pdo->query("SELECT menu_group, COUNT(*) c FROM tbl_menu WHERE parent=0 AND status=1 AND menu_group NOT IN ($allowed) GROUP BY menu_group")
        ->fetchAll(PDO::FETCH_ASSOC);
    check(count($bad) === 0, 'every active top-level menu has a configured group', json_encode($bad));

    $total = (int) $pdo->query('SELECT COUNT(*) FROM tbl_menu WHERE parent=0 AND status=1')->fetchColumn();
    $grouped = (int) $pdo->query("SELECT COUNT(*) FROM tbl_menu WHERE parent=0 AND status=1 AND menu_group IN ($allowed)")->fetchColumn();
    check($total === 45, 'live top-level menu count is 45', "got {$total}");
    check($grouped === $total, 'all 45 top-level menus are grouped', "{$grouped}/{$total}");

    foreach ($EXPECTED_BACKFILL as $group => $ids) {
        $in  = implode(',', array_map('intval', $ids));
        $badRows = $pdo->query("SELECT menu_id, menu_group FROM tbl_menu WHERE menu_id IN ({$in}) AND menu_group <> '{$group}'")
            ->fetchAll(PDO::FETCH_ASSOC);
        check(count($badRows) === 0, "group '{$group}' holds its " . count($ids) . ' assigned ids', json_encode($badRows));
    }

    // No id may appear in two groups.
    $all = array_merge(...array_values($EXPECTED_BACKFILL));
    check(count($all) === count(array_unique($all)), 'no menu_id is assigned to two groups', count($all) . ' ids, ' . count(array_unique($all)) . ' unique');
    check(count($all) === 45, 'the design table lists 45 ids', 'got ' . count($all));
}

// ------------------------------------------------------------------ render ----

function httpGet(string $url, string $jar): string
{
    $ch = curl_init($url);
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 8,
        CURLOPT_TIMEOUT        => 30,
    ];
    if (is_string($jar) && $jar !== '') {
        $opts[CURLOPT_HTTPHEADER] = ['Cookie: ' . $jar];
    }
    curl_setopt_array($ch, $opts);
    $body = curl_exec($ch);
    curl_close($ch);
    return $body === false ? '' : $body;
}

/**
 * Authenticate and return the session cookie as a string, or '' on failure.
 *
 * This PHP's curl reports no cookie-jar support: CURLOPT_COOKIEJAR and
 * CURLOPT_COOKIEFILE are silent no-ops and no file is ever written, even though
 * Set-Cookie arrives normally. So capture the cookie from the response header
 * and replay it on every later request via the Cookie header.
 *
 * $jar is therefore a cookie STRING here, not a path. The parameter name is
 * kept so existing call sites do not change.
 */
function login(string $base, &$jar): bool
{
    $ch = curl_init($base . '/login');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'user_name' => 'forid',
            'password'  => 'Admin@123',
            'lat'       => '0.000000',
            'long'      => '0.000000',
        ]),
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HEADER         => true,
    ]);
    $raw    = (string) curl_exec($ch);
    $hsize  = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $cookie = '';
    foreach (preg_split('/\r?\n/', substr($raw, 0, $hsize)) as $line) {
        if (stripos($line, 'Set-Cookie:') === 0) {
            $cookie = trim(substr($line, strlen('Set-Cookie:')));
            $semi = strpos($cookie, ';');
            if ($semi !== false) {
                $cookie = substr($cookie, 0, $semi);
            }
            break;
        }
    }

    if (!str_contains($cookie, 'uniquecoder')) {
        return false;
    }

    // Prove the cookie really authenticates: the dashboard must come back
    // without the sign-in form.
    $page = httpGet($base . '/admin/dashboard', $cookie);
    if ($page === '' || str_contains($page, 'name="user_name"')) {
        return false;
    }

    $jar = $cookie;
    return true;
}

function testRender(string $base): void
{
    global $EXPECTED_ORDER, $EXPECTED_BACKFILL, $EXPECTED_LABEL;

    heading('render — sidebar group headers');

    $jar = '';   // becomes the session cookie string after login()
    if (!login($base, $jar)) {
        bad('login as forid', 'no session cookie');
        return;
    }
    ok('login as forid');

    $html = httpGet($base . '/admin/dashboard', $jar);
    // buildMenu() emits single-quoted attributes: class='nav s-menu ...'
    if (!str_contains($html, "class='nav s-menu")) {
        bad('sidebar nav rendered', 'no s-menu nav in the response');
        return;
    }
    ok('sidebar nav rendered');

    preg_match_all('/<li class="sidebar-group-header">([^<]+)<\/li>/', $html, $m);
    // buildMenu() htmlspecialchars()es the label, so "CRM & Clients" arrives as
    // "CRM &amp; Clients". Decode before comparing.
    $found = array_map(
        fn($s) => trim(html_entity_decode($s, ENT_QUOTES, 'UTF-8')),
        $m[1] ?? []
    );
    $nonEmpty = 0;
    foreach ($EXPECTED_ORDER as $g) {
        if (count($EXPECTED_BACKFILL[$g]) > 0) { $nonEmpty++; }
    }
    check(count($found) === $nonEmpty, "exactly {$nonEmpty} group headers render", 'got ' . count($found) . ': ' . implode(' | ', $found));

    foreach ($EXPECTED_LABEL as $key => $label) {
        if (count($EXPECTED_BACKFILL[$key]) === 0) { continue; }
        check(in_array($label, $found, true), "header '{$label}' present");
    }

    // Every top-level item must still be linked, grouped or not.
    $links = preg_match_all('/<li[^>]*>\s*<a[^>]*href=/', $html);
    check($links > 0, 'sidebar still renders links', "{$links} anchors");

    testClientMenuUngrouped($base, $jar);
}

/**
 * The client menu must never be grouped.
 *
 * This cannot be checked over HTTP: /client/dashboard 307-redirects an admin
 * session straight back to /admin/dashboard, so the only response available is
 * the grouped admin page and the assertion can never pass. Client passwords in
 * tbl_client are hashed and there is no client auto_login(), so no client
 * session is obtainable here.
 *
 * So the invariant is checked where it is actually decided — at the call sites
 * inside Menu.php. $group is opt-in and defaults to false, so the client menu
 * is ungrouped if and only if clientMenu() calls buildMenu() without the flag,
 * and buildMenu() does not forward the flag into its own recursion (which is
 * what keeps submenus flat).
 */
function testClientMenuUngrouped(string $base, string $jar): void
{
    global $ROOT;

    // This gate is a standalone CLI script, so the CI constant FCPATH does not
    // exist here; $ROOT is derived from __DIR__ instead.
    $path = $ROOT . '/application/libraries/Menu.php';
    $src  = (string) @file_get_contents($path);
    if ($src === '') {
        bad('client menu is not grouped', 'cannot read ' . $path);
        return;
    }

    // Parse rather than regex, so a nested comma (e.g. a multi-line argument or
    // a call inside an array) cannot be miscounted as a separator.
    $tokens = token_get_all($src);
    $fn     = '(file scope)';
    $calls  = [];
    $depth  = 0;
    for ($i = 0, $n = count($tokens); $i < $n; $i++) {
        $t = $tokens[$i];
        if (is_array($t)) {
            if ($t[0] === T_FUNCTION) {
                for ($j = $i + 1; $j < $n; $j++) {
                    if (is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) {
                        $fn = $tokens[$j][1];
                        break;
                    }
                    if ($tokens[$j] === '(') { break; }
                }
            }
            if ($t[0] === T_STRING && strtolower($t[1]) === 'buildmenu' && $tokens[$i + 1] === '(') {
                // Skip the declaration itself: `function buildMenu($parent,
                // $menu, $sub = NULL, $group = false)` has the same shape as a
                // call and would otherwise be counted as a 4-argument call.
                $isDecl = false;
                for ($k = $i - 1; $k >= 0; $k--) {
                    $p = $tokens[$k];
                    if (is_array($p) && in_array($p[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) { continue; }
                    $isDecl = (is_array($p) && $p[0] === T_FUNCTION);
                    break;
                }
                if ($isDecl) { continue; }

                $args  = 0;
                $inner = 0;
                $seen  = false;
                // Start past the '(' itself and count separators at depth 0. An
                // empty call buildMenu() has no content token, so $seen stays
                // false and reports 0 args rather than 1.
                for ($j = $i + 2; $j < $n; $j++) {
                    $u = $tokens[$j];
                    if ($u === '(' || $u === '[') { $inner++; continue; }
                    if ($u === ')' || $u === ']') {
                        if ($inner === 0) { break; }
                        $inner--;
                        continue;
                    }
                    if ($u === ',') {
                        if ($inner === 0) { $args++; }
                        continue;
                    }
                    if (is_array($u)) {
                        if (in_array($u[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) { continue; }
                        $seen = true;
                        continue;
                    }
                    $seen = true;
                }
                $calls[] = ['fn' => $fn, 'args' => ($seen ? $args + 1 : 0)];
            }
            continue;
        }
        $depth += ($t === '{' || $t === '}') ? 1 : 0;
    }

    $byFn = [];
    foreach ($calls as $c) {
        $byFn[$c['fn']][] = $c['args'];
    }

    check(
        isset($byFn['clientMenu']) && count($byFn['clientMenu']) === 1,
        'clientMenu() calls buildMenu() exactly once',
        'found ' . count($byFn['clientMenu'] ?? []) . ' call(s)'
    );
    check(
        isset($byFn['clientMenu']) && $byFn['clientMenu'][0] <= 3,
        'clientMenu() does not pass the $group flag',
        'buildMenu() called with ' . ($byFn['clientMenu'][0] ?? '?') . ' args'
    );

    // The recursion inside buildMenu() must not forward the flag either, which
    // is what keeps every submenu flat.
    check(
        !isset($byFn['buildMenu']) || max($byFn['buildMenu']) <= 3,
        'buildMenu() recursion does not forward $group (submenus stay flat)',
        'max ' . (isset($byFn['buildMenu']) ? max($byFn['buildMenu']) : '?') . ' args'
    );

    // And the admin sidebar must be the one caller that opts in.
    check(
        isset($byFn['dynamicMenu']) && $byFn['dynamicMenu'][0] === 4,
        'dynamicMenu() opts in with the 4th argument',
        'buildMenu() called with ' . ($byFn['dynamicMenu'][0] ?? '?') . ' args'
    );

    // $group must default to false, so any other caller stays ungrouped.
    check(
        (bool) preg_match('/function\s+buildMenu\s*\([^)]*\$group\s*=\s*false/i', $src),
        'buildMenu() $group parameter defaults to false'
    );

    // Grouping is the VERTICAL admin sidebar only. The horizontal layout also
    // renders dynamicMenu() (views/admin/components/horizontal_sidebar.php), so
    // opting in unconditionally would drop group headers into a horizontal menu
    // where the section labels mean nothing. layout-h is not set anywhere today,
    // so this is currently dormant — assert the guard exists rather than rely on
    // that accident.
    check(
        (bool) preg_match('/\$group\s*=\s*\$this->hasMenuGroupColumn\(\)\s*&&\s*!\s*config_item\(\s*[\'"]layout-h[\'"]\s*\)/', $src),
        'dynamicMenu() opts out of grouping for the horizontal layout',
        'no `!config_item(\'layout-h\')` guard on the $group assignment'
    );

    // If a genuine client page is ever reachable, assert on it too.
    $ch = curl_init($base . '/client/dashboard');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => ['Cookie: ' . $jar],
    ]);
    $client = (string) curl_exec($ch);
    $code   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redir  = (string) curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);

    if (in_array($code, [301, 302, 303, 307, 308], true) && str_contains($redir, '/admin/')) {
        ok('client page not asserted over HTTP (admin session is redirected to the admin dashboard)');
    } else {
        check(
            !str_contains($client, 'sidebar-group-header'),
            'client menu is not grouped (live page)',
            'found ' . substr_count($client, 'sidebar-group-header') . ' group headers at http ' . $code
        );
    }
}

// ------------------------------------------------------------------ admin ----

function testAdmin(string $base): void
{
    global $EXPECTED_LABEL;

    heading('admin — navigation manager UI');

    $jar = '';   // becomes the session cookie string after login()
    if (!login($base, $jar)) {
        bad('login as forid', 'no session cookie');
        return;
    }

    $form = httpGet($base . '/admin/navigation/add_navigation', $jar);
    check(str_contains($form, 'name="menu_group"'), 'add_navigation has a menu_group select');

    // Scope the option check to the menu_group <select> itself. A whole-page
    // substring search for value="projects" passed even before the select
    // existed, because that string occurs elsewhere in the page — a false pass
    // that hid four real failures.
    $selectHtml = '';
    if (preg_match('/<select\b[^>]*\bname="menu_group".*?<\/select>/is', $form, $sm)) {
        $selectHtml = $sm[0];
    }
    check($selectHtml !== '', 'menu_group select element is locatable');
    foreach ($EXPECTED_LABEL as $key => $label) {
        check(
            $selectHtml !== '' && str_contains($selectHtml, 'value="' . $key . '"'),
            "select offers '{$key}'",
            "missing option for {$key}"
        );
    }

    $list = httpGet($base . '/admin/navigation', $jar);
    check(str_contains($list, '<th>Group</th>'), 'manage_navigation has a Group column');

    // Round-trip: save a group, confirm the DB, restore the ORIGINAL value.
    //
    // save_navigation() rewrites every column the form posts, and the form does
    // post `link`. Posting only menu_group would blank menu_id=1's link and
    // leave the dashboard menu pointing nowhere, so post the real current values
    // and verify nothing but menu_group actually changed.
    $pdo = dbPdo('tic_crm');
    $before = $pdo->query('SELECT * FROM tbl_menu WHERE menu_id = 1')->fetch(PDO::FETCH_ASSOC);
    $target = ($before['menu_group'] === 'finance') ? 'tools' : 'finance';

    $ch = curl_init($base . '/admin/navigation/save_navigation/1');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'label'      => $before['label'],
            'link'       => $before['link'],
            'icon'       => $before['icon'],
            'sort'       => $before['sort'],
            'parent'     => $before['parent'],
            'menu_group' => $target,
        ]),
        CURLOPT_HTTPHEADER     => ['Cookie: ' . $jar],
        CURLOPT_TIMEOUT        => 30,
    ]);
    curl_exec($ch);
    curl_close($ch);

    $saved = $pdo->query('SELECT * FROM tbl_menu WHERE menu_id = 1')->fetch(PDO::FETCH_ASSOC);
    check(($saved['menu_group'] ?? null) === $target, "save_navigation persists menu_group='{$target}'", 'got ' . var_export($saved['menu_group'] ?? null, true));

    // The save must not have damaged any other column.
    //
    // tbl_menu.time is `timestamp ... on update current_timestamp()`, so MySQL
    // refreshes it on any write that actually changes a value. Persisting
    // menu_group IS such a write, so comparing `time` against the pre-save
    // snapshot made this assertion unsatisfiable: it could only pass when the
    // save silently did nothing. It is a database-managed column, not a
    // form-owned one, so it is excluded from the drift check and asserted
    // separately below.
    $driftSaved = $saved;
    $driftWant  = array_merge($before, ['menu_group' => $target]);
    $timeBefore = $driftSaved['time'] ?? null;
    unset($driftSaved['time'], $driftWant['time']);
    $drift = array_diff_assoc($driftSaved, $driftWant);
    check(count($drift) === 0, 'no collateral column changes', json_encode($drift));

    // The auto-managed timestamp must still behave sanely: it may advance, but
    // it must never go backwards or go missing.
    check(
        !empty($timeBefore) && !empty($saved['time']) && strtotime((string) $saved['time']) >= strtotime((string) $timeBefore),
        'auto-managed `time` did not go backwards',
        "before '{$timeBefore}' after '" . ($saved['time'] ?? 'NULL') . "'"
    );

    $stmt = $pdo->prepare('UPDATE tbl_menu SET menu_group = ? WHERE menu_id = 1');
    $stmt->execute([$before['menu_group']]);
    $restored = $pdo->query('SELECT menu_group FROM tbl_menu WHERE menu_id = 1')->fetchColumn();
    check($restored === $before['menu_group'], 'original menu_group restored', 'got ' . var_export($restored, true));
}

// ------------------------------------------------------------------- main ----

$dbName  = $opts['db-name'] ?? 'tic_crm';
$baseUrl = rtrim($opts['base'] ?? 'http://localhost/complete_crm', '/');

echo "Sidebar grouping gate — {$baseUrl}\n";
echo str_repeat('=', 78) . "\n";

$runs = match ($mode) {
    'config' => ['config'],
    'db'     => ['config', 'db'],
    'render' => ['config', 'render'],
    'admin'  => ['config', 'admin'],
    default  => ['config', 'db', 'render', 'admin'],
};

if (in_array('config', $runs, true)) { testConfig(); }
if (in_array('db', $runs, true))     { testDb($dbName); }
if (in_array('render', $runs, true)) { testRender($baseUrl); }
if (in_array('admin', $runs, true))  { testAdmin($baseUrl); }

echo "\n" . str_repeat('=', 78) . "\n";
echo "{$pass} passed, {$fail} failed\n";
exit($fail === 0 ? 0 : 1);
