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
$mode = $argv[1] ?? 'all';

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
$EXPECTED_BACKFILL = [
    'crm'      => [1, 2, 4, 5, 227, 228, 139, 141],
    'projects' => [6, 54, 55, 56, 57, 58, 233, 234, 269],
    'hr'       => [70, 72, 85, 100, 101, 102, 105, 211, 212],
    'finance'  => [12, 29, 75, 89, 150, 210, 213],
    'tools'    => [24, 25, 26, 42, 73, 110, 209, 235, 236, 248, 263],
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
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 8,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_COOKIEJAR      => $jar,
        CURLOPT_COOKIEFILE     => $jar,
    ]);
    $body = curl_exec($ch);
    curl_close($ch);
    return $body === false ? '' : $body;
}

function login(string $base, string $jar): bool
{
    $ch = curl_init($base . '/login');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'user_name' => 'forid',
            'password'  => 'Admin@123',
            'lat'       => '0.000000',
            'long'      => '0.000000',
        ]),
        CURLOPT_COOKIEJAR      => $jar,
        CURLOPT_COOKIEFILE     => $jar,
        CURLOPT_TIMEOUT        => 30,
    ]);
    curl_exec($ch);
    curl_close($ch);
    return is_file($jar) && str_contains((string) @file_get_contents($jar), 'uniquecoder');
}

function testRender(string $base): void
{
    global $EXPECTED_ORDER, $EXPECTED_BACKFILL, $EXPECTED_LABEL;

    heading('render — sidebar group headers');

    $jar = (getenv('TEMP') ?: sys_get_temp_dir()) . '/tic_crm_menu_' . getmypid() . '.jar';
    if (!login($base, $jar)) {
        bad('login as forid', 'no session cookie');
        return;
    }
    ok('login as forid');

    $html = httpGet($base . '/admin/dashboard', $jar);
    if (!str_contains($html, 'class="nav s-menu')) {
        bad('sidebar nav rendered', 'no s-menu nav in the response');
        return;
    }
    ok('sidebar nav rendered');

    preg_match_all('/<li class="sidebar-group-header">([^<]+)<\/li>/', $html, $m);
    // buildMenu() htmlspecialchars()es the label, so "CRM & Clients" arrives as
    // "CRM &amp; Clients". Decode before comparing.
    $found = array_map('trim', array_map('html_entity_decode', $m[1] ?? [], ENT_QUOTES, 'UTF-8'));
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

    // The client menu must NOT be grouped.
    $client = httpGet($base . '/client/dashboard', $jar);
    if (str_contains($client, 'class="nav s-menu')) {
        check(
            !str_contains($client, 'sidebar-group-header'),
            'client menu is not grouped'
        );
    } else {
        ok('client menu is not grouped (no client nav rendered for this role)');
    }
}

// ------------------------------------------------------------------ admin ----

function testAdmin(string $base): void
{
    global $EXPECTED_LABEL;

    heading('admin — navigation manager UI');

    $jar = (getenv('TEMP') ?: sys_get_temp_dir()) . '/tic_crm_menu_' . getmypid() . '.jar';
    if (!login($base, $jar)) {
        bad('login as forid', 'no session cookie');
        return;
    }

    $form = httpGet($base . '/admin/navigation/add_navigation', $jar);
    check(str_contains($form, 'name="menu_group"'), 'add_navigation has a menu_group select');
    foreach ($EXPECTED_LABEL as $key => $label) {
        check(
            str_contains($form, 'value="' . $key . '"'),
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
        CURLOPT_COOKIEJAR      => $jar,
        CURLOPT_COOKIEFILE     => $jar,
        CURLOPT_TIMEOUT        => 30,
    ]);
    curl_exec($ch);
    curl_close($ch);

    $saved = $pdo->query('SELECT * FROM tbl_menu WHERE menu_id = 1')->fetch(PDO::FETCH_ASSOC);
    check(($saved['menu_group'] ?? null) === $target, "save_navigation persists menu_group='{$target}'", 'got ' . var_export($saved['menu_group'] ?? null, true));

    // The save must not have damaged any other column.
    $drift = array_diff_assoc($saved, array_merge($before, ['menu_group' => $target]));
    check(count($drift) === 0, 'no collateral column changes', json_encode($drift));

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
