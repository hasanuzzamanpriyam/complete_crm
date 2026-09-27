# Header and Sidebar Redesign — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Group and Soft-UI-restyle the admin left sidebar, and visually restyle the top bar, with no change to top-bar behaviour and no break across the 14 existing themes.

**Architecture:** One new config file, `application/config/menu_groups.php`, is the single source of truth for the ordered group map. A migration adds one additive `menu_group` column to `tbl_menu`; `Menu::buildMenu()` gains an opt-in `$group` flag that only `dynamicMenu()` passes, so the client menu and the horizontal layout render byte-identical to today. All visual work is CSS in `assets/css/app.css` reading only Soft UI tokens from `assets/css/tokens.css`. A new gate, `plan/_tools/menu-group-test.php`, asserts the data and rendered-HTML invariants.

**Tech Stack:** CodeIgniter 3 (MVC, `config_item()`, CI migrations), PHP 8.3, MySQL/MariaDB via PDO, Bootstrap 3 shell, Font Awesome, CSS custom properties.

**Spec:** `docs/superpowers/specs/2026-09-27-header-sidebar-redesign-design.md`

---

## Environment

```powershell
$php   = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"
$mysql = "C:\laragon\bin\mysql\mariadb-12.2.2-winx64\bin\mysql.exe"
```

- **Each shell invocation is a fresh PowerShell session.** `$php` and `$mysql` do
  not survive between blocks — re-declare them, or paste the full path, in every
  block that uses them. Blocks that depend on them declare them inline below.
- App under test: `http://localhost/complete_crm`
- DB: `tic_crm` (live) and `tic_crm_demo`, PDO `mysql:host=127.0.0.1`, user `root`, blank password
- Branch: `sakib`. Push to `origin sakib`. **Never touch `main`.**
- Browser checks use Playwright MCP. There is no local `node_modules/playwright`.
- Screenshots cannot be inspected — assert on computed geometry only.

## File map

| File | Responsibility after this plan |
| --- | --- |
| `application/config/menu_groups.php` | **New.** Canonical ordered group map. Read by Menu, the admin form, the controller, and the test. |
| `application/migrations/1003_version_1003.php` | **New.** Records the additive `menu_group` column. |
| `application/libraries/Menu.php` | Renders group headers when `$group` is on. `clientMenu()` untouched. |
| `application/controllers/admin/Navigation.php` | Whitelists and validates `menu_group` on save. |
| `application/views/admin/menu/add_navigation.php` | Group `<select>`. |
| `application/views/admin/menu/manage_navigation.php` | Group column. |
| `application/views/admin/components/header.php` | Cosmetic only: muted title, `New` label. No ID, href or handler changes. |
| `assets/css/app.css` | Sidebar group headers, row treatment, collapsed-rail tooltip, top bar restyle. |
| `plan/_tools/menu-group-test.php` | **New.** Verification gate for data + rendered HTML + admin UI. |

---

### Task 1: Commit the responsive/overflow fix

This work is already done and verified in the working tree. It commits first so
the redesign builds on a clean base.

- [ ] **Step 1: Confirm the working tree holds exactly the three expected files**

```powershell
git status --short
```

Expected: ` M application/views/admin/projects/all_projects_state_report.php`,
` M application/views/admin/tasks/tasks_state_report.php`, ` M assets/css/app.css`.
Nothing else. If anything else is listed, stop and report it.

- [ ] **Step 2: Lint the two changed PHP views**

```powershell
& $php -l "application\views\admin\tasks\tasks_state_report.php"
& $php -l "application\views\admin\projects\all_projects_state_report.php"
```

Expected: `No syntax errors detected` twice.

- [ ] **Step 3: Confirm the progress-percentage fix is present**

```powershell
Select-String -Path "application\views\admin\tasks\tasks_state_report.php","application\views\admin\projects\all_projects_state_report.php" -SimpleMatch -Pattern "count(array(`$all_"
```

Expected: **no matches**. A match means the `count(array($all_x))` bug — always
`1` as a denominator — has been reintroduced.

- [ ] **Step 4: Run the existing gates**

```powershell
cmd /c composer smoke
cmd /c composer test:login
cmd /c composer test:theme
```

Expected: smoke 22/22, login 7/7, theme passes in both modes.

- [ ] **Step 5: Regenerate the CSS inventory**

```powershell
cmd /c "$php plan\_tools\css-inventory.php > plan\_data\part-a-css-inventory.md"
```

- [ ] **Step 6: Commit**

```powershell
git add assets/css/app.css application/views/admin/tasks/tasks_state_report.php application/views/admin/projects/all_projects_state_report.php plan/_data/part-a-css-inventory.md
git commit -m "fix: horizontal overflow - table scroll containers, canvas containment, offsidebar parking, state-report percentages"
```

- [ ] **Step 7: Push**

```powershell
git push origin sakib
```

---

### Task 2: Canonical group map in config

- [ ] **Step 1: Write the failing test**

Create `plan/_tools/menu-group-test.php` with the `config` subcommand only. Full
file — later tasks append subcommands to it.

```php
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
    heading('config — canonical group map');

    $file = $ROOT . '/application/config/menu_groups.php';
    if (!is_file($file)) {
        bad('application/config/menu_groups.php exists');
        return;
    }
    ok('application/config/menu_groups.php exists');

    $config = [];
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

    // Round-trip: save a group, confirm the DB, restore the original value.
    $pdo = dbPdo('tic_crm');
    $before = $pdo->query('SELECT menu_group FROM tbl_menu WHERE menu_id = 1')->fetchColumn();
    $target = ($before === 'finance') ? 'tools' : 'finance';

    $ch = curl_init($base . '/admin/navigation/save_navigation/1');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'label'      => 'dashboard',
            'icon'       => 'fa fa-dashboard',
            'sort'       => '0',
            'parent'     => '0',
            'menu_group' => $target,
        ]),
        CURLOPT_COOKIEJAR      => $jar,
        CURLOPT_COOKIEFILE     => $jar,
        CURLOPT_TIMEOUT        => 30,
    ]);
    curl_exec($ch);
    curl_close($ch);

    $after = $pdo->query('SELECT menu_group FROM tbl_menu WHERE menu_id = 1')->fetchColumn();
    check($after === $target, "save_navigation persists menu_group='{$target}'", 'got ' . var_export($after, true));

    $stmt = $pdo->prepare('UPDATE tbl_menu SET menu_group = ? WHERE menu_id = 1');
    $stmt->execute([$before]);
    $restored = $pdo->query('SELECT menu_group FROM tbl_menu WHERE menu_id = 1')->fetchColumn();
    check($restored === $before, 'original menu_group restored', 'got ' . var_export($restored, true));
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
```

- [ ] **Step 2: Run it to confirm it fails**

```powershell
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"
& $php "plan\_tools\menu-group-test.php" config
```

Expected: `FAIL  application/config/menu_groups.php exists` and a non-zero exit.

- [ ] **Step 3: Create the config**

`application/config/menu_groups.php`:

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sidebar group map — the single source of truth for A2-7.
 *
 * Array order IS the sidebar display order. Read it with
 * config_item('menu_groups'); there is no menu_group ordering column because
 * admins do not need to reorder groups, only to assign a menu to one.
 *
 * 'tools' is the documented fallback: an unknown or empty menu_group renders
 * here rather than producing an unlabelled section.
 */
$config['menu_groups'] = [
    'crm'      => 'CRM & Clients',
    'projects' => 'Projects & Tasks',
    'hr'       => 'HR & Payroll',
    'finance'  => 'Finance & Billing',
    'tools'    => 'Tools & Admin',
    'admin'    => 'Super Admin',
];
```

- [ ] **Step 4: Run it to confirm it passes**

```powershell
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"
& $php "plan\_tools\menu-group-test.php" config
```

Expected: 10 passes — `exists`, `is defined`, `order matches the design`, the 6
per-label checks, and `'tools' fallback group exists` — 0 failures, exit 0.

- [ ] **Step 5: Add the composer script**

In `composer.json`, inside the existing `scripts` object, add:

```json
"test:menu": "php plan/_tools/menu-group-test.php all"
```

- [ ] **Step 6: Commit**

```powershell
git add application/config/menu_groups.php plan/_tools/menu-group-test.php composer.json
git commit -m "feat: canonical sidebar group map and menu-group verification gate"
```

---

### Task 3: Migration and backfill

- [ ] **Step 1: Write the migration file**

`application/migrations/1003_version_1003.php`:

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * A2-7 — sidebar grouping.
 *
 * Adds one additive column. Safe to leave in place after a code rollback: the
 * renderer skips grouping when the column is absent, and the DEFAULT keeps
 * every existing row valid.
 */
class Migration_Version_1003 extends CI_Migration
{
    public function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        $this->db->query(
            "ALTER TABLE `tbl_menu`
             ADD COLUMN `menu_group` VARCHAR(32) NOT NULL DEFAULT 'tools' AFTER `parent`"
        );

        // Top-level menus only; children inherit their parent's group when the
        // sidebar renders. 45 ids, matching plan/_tools/menu-group-test.php.
        $map = [
            'crm'      => [1, 2, 4, 5, 227, 228, 139, 141],
            'projects' => [6, 54, 55, 56, 57, 58, 233, 234, 269],
            'hr'       => [70, 72, 85, 100, 101, 102, 105, 211, 212],
            'finance'  => [12, 29, 75, 89, 150, 210, 213],
            'tools'    => [24, 25, 26, 42, 73, 110, 209, 235, 236, 248, 263],
            'admin'    => [243],
        ];

        foreach ($map as $group => $ids) {
            $in = implode(',', array_map('intval', $ids));
            $this->db->query("UPDATE `tbl_menu` SET `menu_group` = '{$group}' WHERE `menu_id` IN ({$in})");
        }
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `tbl_menu` DROP COLUMN `menu_group`");
    }
}
```

- [ ] **Step 2: Lint it**

```powershell
& $php -l "application\migrations\1003_version_1003.php"
```

- [ ] **Step 3: Apply to `tic_crm`**

`application/config/migration.php` has `migration_version = 643` and there is no
`migrations` tracking table, so the CI migrator will not run this. The project
convention is to apply the SQL by hand. Run:

```powershell
$mysql = "C:\laragon\bin\mysql\mariadb-12.2.2-winx64\bin\mysql.exe"
& $mysql -u root tic_crm -e "ALTER TABLE tbl_menu ADD COLUMN menu_group VARCHAR(32) NOT NULL DEFAULT 'tools' AFTER parent;"

$map = [ordered]@{
  crm      = 1,2,4,5,227,228,139,141
  projects = 6,54,55,56,57,58,233,234,269
  hr       = 70,72,85,100,101,102,105,211,212
  finance  = 12,29,75,89,150,210,213
  tools    = 24,25,26,42,73,110,209,235,236,248,263
  admin    = 243
}
foreach ($g in $map.Keys) {
  & $mysql -u root tic_crm -e "UPDATE tbl_menu SET menu_group='$g' WHERE menu_id IN ($($map[$g] -join ','));"
}
if ($?) { Write-Output "backfill applied to tic_crm" }
```

- [ ] **Step 4: Apply to `tic_crm_demo`**

Repeat Step 3 with `tic_crm_demo` in both places.

- [ ] **Step 5: Verify both databases**

```powershell
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"
& $php "plan\_tools\menu-group-test.php" db
& $php "plan\_tools\menu-group-test.php" db --db-name=tic_crm_demo
```

Expected for each: column exists, no unconfigured groups, live count 45, 45/45
grouped, six per-group assertions pass, 45 unique ids. 0 failures.

- [ ] **Step 6: Record the SQL in the plan doc**

Append this block to the **A2-7** entry in
`plan/part-a-css-reskin/02-app-css.md`, so the change is reproducible on a fresh
database:

```markdown
#### Database

Additive, applied by hand to `tic_crm` and `tic_crm_demo` (this project has no
runnable migrator — `application/config/migration.php` pins
`migration_version = 643` and there is no `migrations` tracking table). See
`application/migrations/1003_version_1003.php` for the canonical form.

```sql
ALTER TABLE `tbl_menu`
  ADD COLUMN `menu_group` VARCHAR(32) NOT NULL DEFAULT 'tools' AFTER `parent`;

UPDATE `tbl_menu` SET `menu_group` = 'crm'      WHERE `menu_id` IN (1,2,4,5,227,228,139,141);
UPDATE `tbl_menu` SET `menu_group` = 'projects' WHERE `menu_id` IN (6,54,55,56,57,58,233,234,269);
UPDATE `tbl_menu` SET `menu_group` = 'hr'       WHERE `menu_id` IN (70,72,85,100,101,102,105,211,212);
UPDATE `tbl_menu` SET `menu_group` = 'finance'  WHERE `menu_id` IN (12,29,75,89,150,210,213);
UPDATE `tbl_menu` SET `menu_group` = 'tools'    WHERE `menu_id` IN (24,25,26,42,73,110,209,235,236,248,263);
UPDATE `tbl_menu` SET `menu_group` = 'admin'    WHERE `menu_id` IN (243);
```

45 top-level rows; children inherit their parent's group at render time.
Group labels and order live in `application/config/menu_groups.php`.
```

- [ ] **Step 7: Commit**

```powershell
git add application/migrations/1003_version_1003.php plan/part-a-css-reskin/02-app-css.md
git commit -m "feat: A2-7 menu_group column, migration 1003 and 45-row backfill"
```

---

### Task 4: Render group headers in the sidebar

- [ ] **Step 1: Run the render test to confirm it fails**

```powershell
& $php "plan\_tools\menu-group-test.php" render
```

Expected: `FAIL  exactly 6 group headers render` and
`FAIL  header 'CRM & Clients' present`, and so on for each label. The data is in
place from Task 3; only the rendering is missing.

- [ ] **Step 2: Add the group-ordering helper to `Menu`**

In `application/libraries/Menu.php`, add this method after `dynamicMenu()`:

```php
    /**
     * The canonical, ordered group map from application/config/menu_groups.php.
     * An empty array means grouping is unavailable and the caller must render
     * the flat menu.
     */
    public function groupMap()
    {
        $map = config_item('menu_groups');
        return is_array($map) ? $map : array();
    }

    /**
     * True when tbl_menu actually has the menu_group column. Checked once per
     * request by dynamicMenu() so an unmigrated database degrades to the flat
     * menu instead of fataling.
     */
    public function hasMenuGroupColumn()
    {
        static $has = null;
        if ($has === null) {
            $CI = &get_instance();
            $has = (bool) $CI->db->field_exists('menu_group', 'tbl_menu');
        }
        return $has;
    }
```

- [ ] **Step 3: Give `buildMenu()` an opt-in `$group` flag**

Change the signature and the two opening lines of `buildMenu()` in the same file.
Current:

```php
    public function buildMenu($parent, $menu, $sub = NULL)
    {
        $html = "";
        $ul_class = "";
```

New:

```php
    public function buildMenu($parent, $menu, $sub = NULL, $group = false)
    {
        $html = "";
        $ul_class = "";
```

`$group` is deliberately not passed down in the recursive call below, so only
top level emits group headers and submenus stay flat.

- [ ] **Step 4: Emit the header before each group's items**

In `buildMenu()`, change the loop head from:

```php
            foreach ($menu['parents'][$parent] as $itemId) {
```

to:

```php
            $lastGroup = null;
            $groupMap  = $group ? $this->groupMap() : array();

            foreach ($menu['parents'][$parent] as $itemId) {
                // Only top level groups. Falls back to 'tools' for an unknown or
                // empty value so a section is never unlabelled.
                if ($group && $parent === 0) {
                    $g = isset($menu['items'][$itemId]->menu_group) ? (string) $menu['items'][$itemId]->menu_group : '';
                    $g = ($g !== '' && isset($groupMap[$g])) ? $g : 'tools';
                    if ($g !== $lastGroup) {
                        if (isset($groupMap[$g])) {
                            $html .= '<li class="sidebar-group-header">'
                                . htmlspecialchars($groupMap[$g], ENT_QUOTES, 'UTF-8')
                                . "</li>\n";
                        }
                        $lastGroup = $g;
                    }
                }
```

Note: this only emits a header when the current group is a **new** run. If a
group's items are not contiguous, the header appears again — which is
correct, because the alternative is a header followed by items from a different
section.

- [ ] **Step 5: Turn grouping on for the admin sidebar only**

At the end of `dynamicMenu()`, change:

```php
        // Builds the array lists with data from the menu table
        return $output = $this->buildMenu(0, $menu);
```

to:

```php
        // Builds the array lists with data from the menu table.
        // Grouping is admin-sidebar only: clientMenu() and the horizontal layout
        // call buildMenu() without the flag and are unchanged.
        $group = $this->hasMenuGroupColumn();
        return $output = $this->buildMenu(0, $menu, null, $group);
```

- [ ] **Step 6: Lint**

```powershell
& $php -l "application\libraries\Menu.php"
```

- [ ] **Step 7: Run the render test to confirm it passes**

```powershell
& $php "plan\_tools\menu-group-test.php" render
```

Expected: exactly 6 group headers, all six labels present, sidebar still
renders links, client menu not grouped. 0 failures.

- [ ] **Step 8: Confirm the client menu is byte-identical**

```powershell
git diff application/libraries/Menu.php | Select-String -SimpleMatch -Pattern "clientMenu"
```

Expected: no changes inside `clientMenu()`.

- [ ] **Step 9: Run the full gate set**

```powershell
& $php "plan\_tools\menu-group-test.php" all
cmd /c composer smoke
cmd /c composer test:login
```

Expected: all menu checks pass, smoke 22/22, login 7/7.

- [ ] **Step 10: Commit**

```powershell
git add application/libraries/Menu.php
git commit -m "feat: A2-7 render sidebar group headers, admin sidebar only"
```

---

### Task 5: Admin UI for assigning groups

- [ ] **Step 1: Run the admin test to confirm it fails**

```powershell
& $php "plan\_tools\menu-group-test.php" admin
```

Expected: `FAIL  add_navigation has a menu_group select` and
`FAIL  manage_navigation has a Group column`.

- [ ] **Step 2: Add the Group select**

In `application/views/admin/menu/add_navigation.php`, insert this block
immediately after the `Parent` form-group closing `</div>` at line 52, before the
`Sort` group:

```php
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label">Group</label>
                <div class="col-sm-5">
                    <?php $menu_group_map = config_item('menu_groups'); ?>
                    <?php if (!is_array($menu_group_map)) { $menu_group_map = array(); } ?>
                    <select name="menu_group" class="form-control">
                        <?php foreach ($menu_group_map as $mg_key => $mg_label) { ?>
                            <option value="<?php echo $mg_key ?>"
                                <?php echo (isset($menu_info->menu_group) && $menu_info->menu_group === $mg_key) ? 'selected' : '' ?>>
                                <?php echo $mg_label ?></option>
                        <?php } ?>
                    </select>
                    <span class="help-block">Applies to top-level menus. Sub-menus inherit their parent's group.</span>
                </div>
            </div>
```

- [ ] **Step 3: Whitelist and validate in the controller**

In `application/controllers/admin/Navigation.php`, replace `save_navigation()`'s
first statement:

```php
        $data = $this->navigation_model->array_from_post(array('label', 'link', 'icon', 'sort'));
        $data['parent'] = $this->input->post('parent',true);
```

with:

```php
        $data = $this->navigation_model->array_from_post(array('label', 'link', 'icon', 'sort', 'menu_group'));
        $data['parent'] = $this->input->post('parent',true);

        // Reject anything outside the configured map so a hand-rolled POST
        // cannot invent a group the sidebar has no label for.
        $groups = config_item('menu_groups');
        if (!is_array($groups)) { $groups = array(); }
        $group = (string) $this->input->post('menu_group', true);
        $data['menu_group'] = isset($groups[$group]) ? $group : 'tools';
```

- [ ] **Step 4: Add the Group column**

In `application/views/admin/menu/manage_navigation.php`, add the header after
the `Parent` `<th>`:

```php
                        <th>Group</th>
```

and the cell after the `Parent` `<td>`:

```php
                                <td><?php echo $v_nav->menu_group ?></td>
```

- [ ] **Step 5: Run the admin test to confirm it passes**

```powershell
& $php "plan\_tools\menu-group-test.php" admin
```

Expected: select present, all six options present, Group column present, the save
round-trip persists `finance`, and the original value is restored. 0 failures.

- [ ] **Step 6: Confirm the round-trip left no residue**

```powershell
& $mysql -u root --skip-column-names tic_crm -e "SELECT menu_id, menu_group FROM tbl_menu WHERE menu_id=1;"
```

Expected: `1	crm` — the value from the design table, not `finance`.

- [ ] **Step 7: Lint and run the gates**

```powershell
& $php -l "application\controllers\admin\Navigation.php"
& $php -l "application\views\admin\menu\add_navigation.php"
& $php -l "application\views\admin\menu\manage_navigation.php"
cmd /c composer smoke
```

- [ ] **Step 8: Commit**

```powershell
git add application/controllers/admin/Navigation.php application/views/admin/menu/add_navigation.php application/views/admin/menu/manage_navigation.php
git commit -m "feat: A2-7 group selector and column in the navigation manager"
```

---

### Task 6: Sidebar Soft UI styling

- [ ] **Step 1: Establish the current geometry as the baseline**

In the browser, load `http://localhost/complete_crm/admin/dashboard` and evaluate:

```js
() => {
  const aside = document.querySelector('.wrapper > .aside');
  const rows  = document.querySelectorAll('.wrapper > .aside .nav > li').length;
  return { asideWidth: Math.round(aside.getBoundingClientRect().width), topLevelRows: rows };
}
```

Expected: `asideWidth: 220`, `topLevelRows` = 45 + 6 group headers + pinned items.
Record the number — Step 5 asserts it is unchanged.

- [ ] **Step 2: Append the group-header rules**

Add to `assets/css/app.css`, inside the existing sidebar section, after the
`.aside-collapsed .sidebar > .nav .nav` rules near line 1993.

**Do not touch `display`, `flex` or the `[data-toggle="collapse"]:after` caret on
any existing sidebar anchor.** The template already lays these out; this change
only normalises the box metrics and colour. Changing `display` breaks the
submenu caret and the flyout below.

```css
/* -------------------------------------------- A2-7 · sidebar group headers */
.sidebar-group-header {
  padding: var(--sp-4) var(--sp-4) var(--sp-2);
  margin: 0;
  font-size: var(--fs-xs);
  font-weight: 600;
  line-height: 1.4;
  letter-spacing: .04em;
  text-transform: uppercase;
  color: var(--c-sidebar-text);
  opacity: .65;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  border-bottom: 1px solid var(--c-sidebar-border);
}

.sidebar .nav > li > a {
  height: var(--row-h);
  line-height: var(--row-h);
  padding: 0 var(--sp-3);
  margin: 0 var(--sp-2) 2px;
  border-radius: var(--r-md);
  color: var(--c-sidebar-text);
  transition: background-color var(--dur-fast) var(--ease),
              color var(--dur-fast) var(--ease);
}

.sidebar .nav > li > a > em {
  width: 20px;
  flex: 0 0 20px;
  text-align: center;
  font-size: 16px;
}

.sidebar .nav > li > a:hover {
  background-color: rgba(26, 29, 33, .06);
  color: var(--c-sidebar-text-hi);
}

.sidebar .nav > li.active > a {
  background-color: rgba(13, 148, 136, .16);
  color: var(--c-sidebar-text-hi);
  box-shadow: inset 3px 0 0 var(--c-sidebar-active);
}

.sidebar .nav > li.active > a > em {
  color: var(--c-sidebar-active);
}

/* ------------------------------ collapsed rail: icons only, hover flyout ---- */
.aside-collapsed .sidebar-group-header {
  display: none;
}

.aside-collapsed .sidebar .nav > li {
  position: relative;
}

.aside-collapsed .sidebar .nav > li > a {
  width: 44px;
  margin: 0 auto;
  padding: 0;
  text-align: center;
}

.aside-collapsed .sidebar .nav > li > a > span {
  display: none;
}

/* Label tooltip. The label is already in the title attribute that buildMenu()
   emits, so this needs no JS. */
.aside-collapsed .sidebar .nav > li > a[title]:hover::after {
  content: attr(title);
  position: absolute;
  left: calc(100% + 10px);
  top: 50%;
  transform: translateY(-50%);
  z-index: 1200;
  padding: var(--sp-1) var(--sp-3);
  border-radius: var(--r-md);
  background: var(--c-text);
  color: var(--c-text-inverse);
  font-size: var(--fs-sm);
  line-height: 1.6;
  white-space: nowrap;
  box-shadow: var(--sh-3);
  pointer-events: none;
}

/* Submenus fly out to the right on hover instead of stacking in the 70px rail. */
.aside-collapsed .sidebar .nav > li.sub-menu > .sidebar-subnav {
  display: none;
  position: absolute;
  left: 100%;
  top: 0;
  z-index: 1200;
  min-width: 200px;
  margin: 0;
  padding: var(--sp-2);
  background: var(--c-surface);
  border: 1px solid var(--c-border);
  border-radius: var(--r-md);
  box-shadow: var(--sh-3);
}

.aside-collapsed .sidebar .nav > li.sub-menu:hover > .sidebar-subnav {
  display: block;
}

.aside-collapsed .sidebar-subnav li > a {
  display: block;
  height: auto;
  line-height: 1.6;
  width: auto;
  margin: 0;
  padding: var(--sp-1) var(--sp-3);
  border-radius: var(--r-sm);
  color: var(--c-text);
  text-align: left;
  white-space: nowrap;
}

.aside-collapsed .sidebar-subnav li > a:hover {
  background-color: rgba(26, 29, 33, .06);
  color: var(--c-text);
}

.aside-collapsed .sidebar-subnav .sidebar-subnav-header {
  color: var(--c-text-3);
  font-size: var(--fs-xs);
  text-transform: uppercase;
  letter-spacing: .04em;
}

/* Menu search */
.sidebar .menu-border-transparent {
  height: var(--h-control-sm);
  border-radius: var(--r-md);
  background-color: var(--c-sidebar-bg-2);
  color: var(--c-sidebar-text);
  border: 0;
}

.sidebar .left-addon .fa {
  color: var(--c-sidebar-text);
  font-size: 13px;
  padding: 9px;
}
```

`buildMenu()` emits submenus as
`<ul id="<label>" class='nav s-menu sidebar-subnav collapse'>`, so the selectors
above match the real markup. The existing `collapse` class means the flyout needs
the `display: none` / `display: block` pair rather than a class toggle — no JS
change.

- [ ] **Step 3: Clear the browser cache**

The stylesheet is versioned by query string in `htmlheader.php`. Hard-reload, or
clear the cache through the Playwright MCP browser before measuring — stale CSS
makes every check below meaningless.

- [ ] **Step 4: Verify the collapsed rail round-trips**

Click `[data-toggle-state="aside-collapsed"]`, then evaluate:

```js
() => {
  const aside = document.querySelector('.wrapper > .aside');
  return {
    bodyClass: document.body.className,
    asideWidth: Math.round(aside.getBoundingClientRect().width),
    headersHidden: getComputedStyle(document.querySelector('.sidebar-group-header')).display,
    spansHidden: getComputedStyle(document.querySelector('.aside .nav > li > a > span')).display
  };
}
```

Expected: `bodyClass` contains `aside-collapsed`, `asideWidth: 70`,
`headersHidden: "none"`, `spansHidden: "none"`. Click again and expect
`asideWidth: 220` and `headersHidden` back to `block`.

- [ ] **Step 5: Verify no item was lost**

Re-run the Step 1 evaluation. Expected: `asideWidth: 220` and the same
`topLevelRows` count as before — the styling must not add or remove rows.

- [ ] **Step 6: Verify the active pill and hover tint**

```js
() => {
  const active = document.querySelector('.sidebar .nav > li.active > a');
  const cs = getComputedStyle(active);
  return {
    bg: cs.backgroundColor,
    inset: cs.boxShadow,
    iconColor: getComputedStyle(active.querySelector('em')).color
  };
}
```

Expected: `bg` an `rgba(13, 148, 136, 0.16)`-family value, `inset` a
`3px 0px 0px rgb(13, 148, 136)`-family value, `iconColor` `rgb(13, 148, 136)`.
If the active item is not present on the dashboard, navigate to a page inside a
group first, e.g. `/admin/tasks/all_task`.

- [ ] **Step 7: Verify no horizontal overflow was introduced**

At widths 1920, 1366, 1024, 768, 600, 375 assert
`document.documentElement.scrollWidth === document.documentElement.clientWidth`,
excluding scroll containers, `aside.offsidebar` and `#loader-wrapper`.

- [ ] **Step 8: Verify all 14 themes stay readable**

For each of the 14 sidebar themes, set it via the theme settings page and assert
the sidebar background, the default label colour and the active pill colour are
all distinct values — i.e. the rules are reading tokens and are not hardcoded to
one theme. Specifically confirm `--c-sidebar-bg` and `--c-sidebar-active` differ
per theme.

- [ ] **Step 9: Run the full gate set**

```powershell
& $php "plan\_tools\menu-group-test.php" all
cmd /c composer smoke
cmd /c composer test:login
cmd /c composer test:theme
cmd /c "$php plan\_tools\css-inventory.php > plan\_data\part-a-css-inventory.md"
```

- [ ] **Step 10: Commit**

```powershell
git add assets/css/app.css plan/_data/part-a-css-inventory.md
git commit -m "feat: A2-7 Soft UI sidebar - group headers, 40px rows, active pill, collapsed-rail tooltip"
```

---

### Task 7: Top bar restyle

Visual only. No ID, href, class contract or handler changes.

- [ ] **Step 1: Record the behaviour that must not change**

In the browser on `/admin/dashboard`, evaluate. The trigger selector matches
**both** the old `fa-plus-circle` markup and the new `.topnav-new`, so the exact
same expression can be re-run in Step 6.

```js
() => {
  const trigger = document.querySelector('.topnavbar .fa-plus-circle, .topnavbar .topnav-new');
  const dd = trigger ? trigger.closest('li.dropdown').querySelector('.dropdown-menu') : null;
  return {
    newDropdownLinks: dd ? dd.querySelectorAll('a').length : -1,
    newClientHref: dd ? (dd.querySelector('a[href*="create_client"]') || {}).href || null : null,
    newProjectHref: dd ? (dd.querySelector('a[href*="projects/create"]') || {}).href || null : null,
    searchTypes: document.getElementById('searchType')
      ? document.getElementById('searchType').options.length : -1,
    hasAllSearch: !!document.getElementById('all_search'),
    searchOpenInitially: document.querySelector('.navbar-form').classList.contains('open'),
    collapseToggle: !!document.querySelector('[data-toggle-state="aside-collapsed"]'),
    offsidebarToggle: !!document.querySelector('[data-toggle-state="offsidebar-open"]')
  };
}
```

Record the values. Expected: `newDropdownLinks: 18`, `searchTypes: 12`, and
every boolean `true`. Step 6 asserts they are all identical.

- [ ] **Step 2: Append the top bar rules**

Add to `assets/css/app.css` after the topnavbar section (after the existing
`.aside-collapsed .topnavbar .navbar-form` rule near line 1742):

```css
/* ------------------------------------------------ A2-8 · top bar surfaces */
.topnavbar-wrapper .topnavbar {
  height: 56px;
  min-height: 56px;
  background: var(--c-surface);
  border-bottom: 1px solid var(--c-border);
  box-shadow: var(--sh-2);
}

.topnavbar .nav-wrapper {
  display: flex;
  align-items: center;
  height: 56px;
}

.topnavbar .nav > li > a {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: var(--r-md);
  color: var(--c-text-2);
  transition: background-color var(--dur-fast) var(--ease),
              color var(--dur-fast) var(--ease);
}

/* The user link holds an avatar plus a name, so it must not inherit the
   36px square. */
.topnavbar .nav > li.user-menu > a {
  width: auto;
  padding: 0 var(--sp-2);
  gap: var(--sp-2);
}

.topnavbar .nav > li > a:hover {
  background-color: rgba(26, 29, 33, .06);
  color: var(--c-text);
}

/* Lock-screen title, previously a 20px link. */
.topnavbar .nav > li.hidden-xs > a[href=""] {
  font-size: var(--fs-base) !important;
  color: var(--c-text-3) !important;
  width: auto;
  padding: 0 var(--sp-2);
}

/* Primary action */
.topnavbar .nav > li > a.topnav-new {
  width: auto;
  height: var(--h-control);
  padding: 0 var(--sp-4);
  gap: var(--sp-2);
  background: var(--c-accent);
  color: var(--c-on-accent);
  font-weight: 600;
  border-radius: var(--r-md);
}

.topnavbar .nav > li > a.topnav-new:hover {
  background: var(--c-accent-hover);
  color: var(--c-on-accent);
}

/* Dropdown rows: same 18 links, restyled. */
.topnavbar .dropdown-menu {
  padding: var(--sp-2);
  border: 1px solid var(--c-border);
  border-radius: var(--r-lg);
  box-shadow: var(--sh-3);
  max-height: 70vh;
  overflow-y: auto;
}

.topnavbar .dropdown-menu > li > a {
  display: flex;
  align-items: center;
  min-height: var(--row-h);
  padding: 0 var(--sp-3);
  border-radius: var(--r-sm);
  color: var(--c-text);
}

.topnavbar .dropdown-menu > li > a:hover {
  background-color: rgba(26, 29, 33, .06);
}

/* Notification badge */
.topnavbar .dropdown-list .badge,
.topnavbar .notifications .badge {
  background: var(--c-accent);
  color: var(--c-on-accent);
  border-radius: var(--r-full);
  font-weight: 600;
}

/* User avatar */
.topnavbar .user-menu .user-image {
  width: 28px;
  height: 28px;
}

.topnavbar .user-menu > a > span {
  font-size: var(--fs-base);
  color: var(--c-text);
}

@media only screen and (max-width: 1199px) {
  .topnavbar .user-menu > a > span { display: none; }
}

/* Search overlay: same reveal behaviour, new surface. */
.topnavbar .navbar-form {
  height: 55px;
  background: var(--c-surface);
  border-bottom: 1px solid var(--c-border);
  box-shadow: var(--sh-3);
}

.topnavbar .navbar-form .input-group .form-control {
  height: var(--h-control);
  max-width: 340px;
  margin: 0 auto;
  border: 0;
  border-radius: var(--r-md);
  background: var(--c-surface-2);
  color: var(--c-text);
  padding-left: var(--sp-4);
}

.topnavbar .navbar-form .input-group-btn .form-control {
  width: auto;
  max-width: 96px;
  border-radius: var(--r-md);
  color: var(--c-text-2);
}
```

- [ ] **Step 3: Mark the primary action and label it**

In `application/views/admin/components/header.php`, change the `+` dropdown
trigger at lines 68-70 from:

```php
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-plus-circle"></i>
                    </a>
```

to:

```php
                <li class="dropdown">
                    <a href="#" class="topnav-new dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-plus"></i>
                        <span>New</span>
                    </a>
```

Nothing inside the `<ul>` changes — same 18 `<li>` entries, same hrefs, same
`data-toggle` modals.

- [ ] **Step 4: Verify the dropdown still works**

Click the `New` button. Expected: the menu opens, shows 18 links, and the
`New Client`, `New Project` and `Make Payment` hrefs match their pre-change
values. Close it with Escape.

- [ ] **Step 5: Verify the search overlay still reveals**

Click `[data-search-open]`. Expected: `.navbar-form` gains the `open` class, the
input is focused, the 12 options in `#searchType` are intact, and
`#all_search` autocompletes. Press Escape. Expected: the `open` class is removed
and the input is emptied.

- [ ] **Step 6: Assert the Step 1 baseline is unchanged**

Re-run the Step 1 evaluation. Expected: identical results, except
`newDropdownLinks` still counts the same 18 and every boolean is unchanged.
The `New` label span does not change any count.

- [ ] **Step 7: Verify the collapse and offsidebar toggles still work**

Click `[data-toggle-state="aside-collapsed"]` and expect `aside-collapsed` on
`<body>` and the sidebar at 70px; click again to restore. Click
`[data-toggle-state="offsidebar-open"]` and expect `offsidebar-open` on `<body>`
and the offsidebar visible at `translate3d(0,0,0)` with a fully in-viewport
right edge; click again to restore.

- [ ] **Step 8: Verify no horizontal overflow was introduced**

Repeat the Task 6 Step 7 sweep at 6 widths across the dashboard, projects, tasks
and invoice pages.

- [ ] **Step 9: Run the full gate set**

```powershell
& $php -l "application\views\admin\components\header.php"
& $php "plan\_tools\menu-group-test.php" all
cmd /c composer smoke
cmd /c composer test:login
cmd /c composer test:theme
cmd /c "$php plan\_tools\css-inventory.php > plan\_data\part-a-css-inventory.md"
```

- [ ] **Step 10: Commit**

```powershell
git add assets/css/app.css application/views/admin/components/header.php plan/_data/part-a-css-inventory.md
git commit -m "feat: A2-8 minimalist top bar - 56px white surface, soft New button, restyled menus"
```

---

### Task 8: Close out the tasks and push

- [ ] **Step 1: Update the plan doc**

In `plan/part-a-css-reskin/02-app-css.md`, mark **A2-7** and **A2-8** complete
with the commit hashes from Tasks 4-7.

- [ ] **Step 2: Update the status doc**

In `plan/STATUS.md`, move A2-7 and A2-8 to done and increment the Part A and
total counters by 2.

- [ ] **Step 3: Run every gate one final time**

```powershell
& $php "plan\_tools\menu-group-test.php" all
cmd /c composer smoke
cmd /c composer test:login
cmd /c composer test:theme
```

Expected: all menu checks pass, smoke 22/22, login 7/7, theme both modes.

- [ ] **Step 4: Confirm the branch state**

```powershell
git status --short
git log --oneline -8
```

Expected: a clean working tree, with the responsive fix, the group map, the
migration, the renderer, the admin UI, the sidebar CSS and the top bar CSS
each as its own commit on top of the two spec commits.

- [ ] **Step 5: Push**

```powershell
git push origin sakib
```

- [ ] **Step 6: Report**

State what changed, the gate results, and the one known open item that is **not**
part of this plan: a pre-existing 404 in the console on some list pages, observed
during the responsive sweep and never attributed.

---

## Self-review notes

Defects found and fixed during self-review, so the implementer does not hit them:

1. **The render test would have failed on `&`.** `buildMenu()` runs the label
   through `htmlspecialchars()`, so the DOM contains `CRM &amp; Clients`. The
   test now `html_entity_decode`s each header before comparing.
2. **Pass count was wrong.** Task 2 Step 4 claimed 8 passes; the test emits 10.
3. **An empty CSS rule was left in Task 6** — a selector pair holding only a
   comment. The group header's hairline is now a real
   `border-bottom: 1px solid var(--c-sidebar-border)`.
4. **The submenu flyout was specified but never implemented.** The spec promises
   "submenus fly out to the right on hover"; the plan now contains the rules,
   scoped to `.aside-collapsed`, matching the `sidebar-subnav collapse` markup
   `buildMenu()` actually emits.
5. **Task 7's baseline selector was too broad.** `document.querySelectorAll('.topnavbar .dropdown-menu a')`
   would have counted links across the language, user *and* create menus. It now
   scopes to the create dropdown via a selector that matches both the old and new
   trigger markup, so the same expression verifies before and after.
6. **The 36px square would have clipped the user menu.** `.topnavbar .nav > li > a`
   now has an explicit `.user-menu > a` override.
7. **`$php` / `$mysql` do not survive between shell invocations.** The
   Environment section says so, and every block that uses them re-declares them.

Coverage and consistency:

- **Spec coverage.** Every spec section maps to a task: sidebar geometry and
  grouping → 4 and 6; item rows and headers → 6; collapsed rail and flyout → 6;
  top bar surface, left, search, right, primary action → 7; migration and
  backfill → 3; `menu_groups.php` → 2; `Menu.php` → 4; admin UI → 5; failure
  handling → 4 (`hasMenuGroupColumn()`) and 5 (controller rejection);
  verification → every task's gate steps.
- **No CSS is hand-waved.** Every colour and size in Tasks 6 and 7 names a token
  from `assets/css/tokens.css`. Where a value is a literal `rgba()` it sits on
  the rule that uses it, so an older engine degrades instead of dropping it.
- **Type consistency.** `config_item('menu_groups')` returns the same ordered map
  everywhere. `Menu::groupMap()` and `Menu::hasMenuGroupColumn()` are the only new
  methods, both defined in Task 4 and used only there. `buildMenu()` keeps `$sub`
  in position 3, so the existing recursive call stays valid, and `$group` is
  position 4.
- **The test tool is written once, in full, in Task 2.** Later tasks only run it.
