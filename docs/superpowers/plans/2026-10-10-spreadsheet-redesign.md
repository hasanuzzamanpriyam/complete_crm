# Spreadsheet Redesign — Implementation Plan

**Spec:** `docs/superpowers/specs/2026-10-10-spreadsheet-redesign-design.md` (approved `43be783e`, amended `cae326ec`)
**Demo:** `.superpowers/brainstorm/1827-1791618467/content/spreadsheet-full.html` (Design A — folder panel + card rows)
**Branch:** `sakib` · **Gate:** `composer test:spreadsheet` (new) + existing gates (smoke / test:login / test:menu green; test:theme pre-existing FAIL baseline)
**Principle:** restyle-only for the redesign (zero controller/model/query/endpoint changes), two pre-existing bug fixes (R1/R2) for client reachability.

## File structure map

| Area | Files |
|---|---|
| Reachability | `application/helpers/admin_helper.php` (R2), `application/migrations/645_add_spreadsheet_client_menu_link.php` (new, R1), `modules/spreadsheet/install.php` (R1b) |
| CSS | `modules/spreadsheet/assets/css/spreadsheet_modern.css` (new), `modules/spreadsheet/helpers/spreadsheet_helper.php` (delivery link, end of `spreadsheet_add_head_component()`) |
| JS | `modules/spreadsheet/assets/js/spreadsheet_shell.js` (new), helper `spreadsheet_load_js()` (script tag + `ss_lang` globals), `assets/js/manage_js.php` (1-line filter refresh) |
| Views | `manage.php`, `my_folder.php`, `share_folder.php`, `new_file_view.php`, `share_file_view.php` |
| Lang | `modules/spreadsheet/language/english/spreadsheet_lang.php` (6 additive keys) |
| Test | `plan/_tools/spreadsheet-fixtures.sql` (new — incl. client rows + `tbl_account_details.company`), `plan/_tools/spreadsheet-redesign-test.php` (new), `composer.json` (`test:spreadsheet`) |
| Untouched | `Spreadsheet.php` controller, `Spreadsheet_model.php`, `manage.js`, `context_menu.js`, `shareModal.php`, `RelatedModal.php`, `manage_style.css`, htmlheader |

---

## Task 1 — R2: remove `created_by` conditions from `apply_user_scope()` (client 500 → 200)

**Why:** `tbl_users.created_by` exists in no migration; every non-super-admin query through this helper fatals with `Unknown column 'tbl_users.created_by'`. Super admins (forid) skip via early return, hiding the bug. Spec §6.5.

### 1a. Failing probe (before edit)

```powershell
$jar="$env:TEMP\ss_r2.txt"
curl.exe -s -c $jar -b $jar -d "user_name=mailbox_client&password=ClientMail@123&lat=0.000000&long=0.000000" -e "http://localhost/complete_crm/" http://localhost/complete_crm/login -o NUL
curl.exe -s -c $jar -b $jar -o "$env:TEMP\ss_r2_body.html" -w "bare_manage=%{http_code}`n" http://localhost/complete_crm/spreadsheet/manage
Select-String -Path "$env:TEMP\ss_r2_body.html" -Pattern "created_by" -SimpleMatch | Select-Object -First 1
```

Expected **before:** `bare_manage=500`, body contains `created_by`.

### 1b. Edit `application/helpers/admin_helper.php` lines 14-19

Replace:

```php
        // Scope to users created by this admin (or the admin themselves)
        if ($exclude_self) {
            $CI->db->where('tbl_users.created_by', $admin_id);
        } else {
            $CI->db->where('(tbl_users.created_by = ' . $admin_id . ' OR tbl_users.user_id = ' . $admin_id . ')');
        }
```

with:

```php
        // The historical created_by clause filtered on a column that exists in
        // no migration (SHOW COLUMNS tbl_users verified 2026-10-10), so it made
        // every non-super-admin query fatal. Removed per spec 2026-10-10 S6.5;
        // $exclude_self is kept for signature compatibility and no longer
        // narrows the query. The filters above (is_super_admin = 0,
        // role_id != 1 OR user_id = self) and the super-admin early return
        // stay as written.
```

Nothing else in the function changes (lines 2-13 verbatim).

### 1c. Verify

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l application\helpers\admin_helper.php
# re-run 1a probe → expect bare_manage=200, no created_by match
```

**Commit:** `fix: drop nonexistent tbl_users.created_by filters in apply_user_scope (R2)`

---

## Task 2 — R1: client menu link `client/spreadsheet` → `spreadsheet`

**Why:** menu_id 24 links to a route that does not exist (404). The module resolves from the bare `spreadsheet/...` segment (verified: `MX/Router.php:121-151`, `index()` client branch, module `admin_url()` client branch). Spec §6.5.

### 2a. New migration `application/migrations/645_add_spreadsheet_client_menu_link.php`

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_spreadsheet_client_menu_link extends CI_Migration {

    public function up()
    {
        // tbl_client_menu.menu_id=24 pointed at client/spreadsheet (404 — no
        // such controller). The module resolves from the bare spreadsheet/...
        // segment, which is also what Spreadsheet::index() and the module's
        // admin_url() emit for client sessions. Idempotent data migration.
        $this->db->where('menu_id', 24);
        $this->db->where('label', 'spreadsheet');
        $this->db->where('link', 'client/spreadsheet');
        $this->db->update('tbl_client_menu', array('link' => 'spreadsheet'));
    }

    public function down()
    {
        $this->db->where('menu_id', 24);
        $this->db->where('label', 'spreadsheet');
        $this->db->where('link', 'spreadsheet');
        $this->db->update('tbl_client_menu', array('link' => 'client/spreadsheet'));
    }
}
```

(Pattern matches `644_add_ai_pending_action.php`: no constructor, `$this->db` provided by `CI_Migration`.)

### 2b. Apply to BOTH dev DBs directly (do NOT run the migrate CLI)

```powershell
mysql -u root tic_crm -e "UPDATE tbl_client_menu SET link='spreadsheet' WHERE menu_id=24 AND label='spreadsheet' AND link='client/spreadsheet'; SELECT menu_id, label, link FROM tbl_client_menu WHERE menu_id=24;"
mysql -u root tic_crm_demo -e "UPDATE tbl_client_menu SET link='spreadsheet' WHERE menu_id=24 AND label='spreadsheet' AND link='client/spreadsheet'; SELECT menu_id, label, link FROM tbl_client_menu WHERE menu_id=24;"
```

Expected from both: `link = spreadsheet`. (`tic_crm_demo` is the demo site's DB — `demo/index.php` defines `DEMO_MODE` → `database.php` serves `tic_crm_demo`; precedent: `2026-09-27-header-sidebar-redesign.md` applied its SQL to both.)

Why hand-apply, not migrate CLI: `migration_version` is pinned at 643 (`application/config/migration.php:70`) so 645 wouldn't run anyway, and `migration_auto_latest=TRUE` (`:59`) makes loading the migrator auto-run `latest()` → re-running 644's `up()` against the already-existing `pending_action` column (duplicate-column failure). Project convention is hand-applied SQL for data fixes.

### 2c. R1b — same bad link in the module installer (fresh installs)

`modules/spreadsheet/install.php:10`: `'client/spreadsheet'` → `'spreadsheet'`. Also `demo/modules/spreadsheet/install.php:10` (tracked mirror copy — same one-line change, else repo-wide greps stay dirty and a fresh DB built from `demo/` re-breaks).

### 2d. Verify menu URL serves the client session (self-contained — do not rely on Task 1's `$jar`)

```powershell
$jar="$env:TEMP\ss_r1.txt"
curl.exe -s -c $jar -b $jar -d "user_name=mailbox_client&password=ClientMail@123&lat=0.000000&long=0.000000" -e "http://localhost/complete_crm/" http://localhost/complete_crm/login -o NUL
curl.exe -s -c $jar -b $jar -o NUL -w "menu_target=%{http_code}`n" http://localhost/complete_crm/spreadsheet
```

Expected: `200` (redirect chain to `admin/spreadsheet/manage` at this point — company fixture not yet applied; R2 already fixed). After Task 3b the same URL lands on `spreadsheet/manage` (client branch).

**Commit:** `fix: client spreadsheet menu link 404 via migration 645 + installer (R1)`

---

## Task 3 — Fixtures + RED e2e gate

### 3a. `plan/_tools/spreadsheet-fixtures.sql` (idempotent)

```sql
-- Spreadsheet redesign e2e fixtures (idempotent) — local dev DB tic_crm
-- Run:  Get-Content plan\_tools\spreadsheet-fixtures.sql -Raw | mysql -u root tic_crm
--
-- Deterministic data for composer test:spreadsheet:
--   My Folder (admin, staffid=1 forid):
--                    910001 root folder / 910002 file (editor route) /
--                    910003 nested folder (drill-down)
--   My Folder (client, staffid=1790 mailbox_client):
--                    910006 root folder / 910007 file (drill on client tab)
--   Share Folder (both roles): 910004 shared root folder
--                    (staffs_share='1' AND clients_share='1') /
--                    910005 its file child (share-by-link route)
--   hash rows      : 910001 (staff/1, id_share=910004 — admin share root) /
--                    910002 (staff/1, id_share=910005 — file_view_share route) /
--                    910003 (client/1, id_share=910004 — client share root)
--   client grant   : (mailbox_client user_id 1790, menu 24)
--   menu link      : menu_id 24 -> spreadsheet (R1 data)
--   company        : tbl_account_details.company = 1 for user 1790
--
-- WHY company: session client_id comes from tbl_account_details.company
-- (Login_model.php:124). mailbox_client has company=NULL, so client_id() is
-- empty and Spreadsheet::index()/manage() took the STAFF branch (verified
-- live: GET /spreadsheet -> 307 -> admin/spreadsheet/manage, i.e. the ADMIN
-- layout). Spec 7 requires the client response to use client/_layout_main
-- content markers, so the fixture links mailbox_client to existing client_id 1
-- ("Moktadir Rahman", tbl_client row verified). Side effects checked: mailbox
-- gate hits client/Mailbox.php which loads client/_layout_main unconditionally
-- (no client_id branch); only mailbox-redesign-test.php uses mailbox_client;
-- select_client_roll has no ORDER BY so the login redirect (client_menu[0])
-- stays client/dashboard (grant 24 appends last); menu/smoke gates never touch
-- tbl_client_role/user 1790.
--
-- My-folder rows: staffid = my_id() (Spreadsheet_model.php:110-115), so the
-- admin sees staffid=1 rows and the client sees staffid=1790 rows — two trees
-- are required. Files copy data_form from an existing file so
-- file_view_share/new_file_view never pass NULL to strpos().

INSERT INTO tbl_spreadsheet_my_folder (id, parent_id, name, type, size, staffid, category, data_form, rel_type, rel_id)
SELECT 910001, '0', 'SS Redesign Fixture Folder', 'folder', '', 1, 'my_folder', NULL, '', ''
WHERE NOT EXISTS (SELECT 1 FROM (SELECT id FROM tbl_spreadsheet_my_folder WHERE id = 910001) AS a);

INSERT INTO tbl_spreadsheet_my_folder (id, parent_id, name, type, size, staffid, category, data_form, rel_type, rel_id)
SELECT 910002, '910001', 'SS Redesign Fixture File', 'file', '12 KB', 1, 'my_folder',
       (SELECT s.data_form FROM (SELECT data_form AS data_form FROM tbl_spreadsheet_my_folder WHERE type = 'file' AND data_form IS NOT NULL AND data_form <> '' LIMIT 1) AS s),
       '', ''
WHERE NOT EXISTS (SELECT 1 FROM (SELECT id FROM tbl_spreadsheet_my_folder WHERE id = 910002) AS b);

INSERT INTO tbl_spreadsheet_my_folder (id, parent_id, name, type, size, staffid, category, data_form, rel_type, rel_id)
SELECT 910003, '910001', 'SS Redesign Nested Folder', 'folder', '', 1, 'my_folder', NULL, '', ''
WHERE NOT EXISTS (SELECT 1 FROM (SELECT id FROM tbl_spreadsheet_my_folder WHERE id = 910003) AS c);

INSERT INTO tbl_spreadsheet_my_folder (id, parent_id, name, type, size, staffid, category, data_form, rel_type, rel_id, staffs_share, clients_share)
SELECT 910004, '0', 'SS Redesign Shared Folder', 'folder', '', 1, 'my_folder', NULL, '', '', '1', '1'
WHERE NOT EXISTS (SELECT 1 FROM (SELECT id FROM tbl_spreadsheet_my_folder WHERE id = 910004) AS d);

INSERT INTO tbl_spreadsheet_my_folder (id, parent_id, name, type, size, staffid, category, data_form, rel_type, rel_id)
SELECT 910005, '910004', 'SS Redesign Shared File', 'file', '12 KB', 1, 'my_folder',
       (SELECT s.data_form FROM (SELECT data_form AS data_form FROM tbl_spreadsheet_my_folder WHERE type = 'file' AND data_form IS NOT NULL AND data_form <> '' LIMIT 1) AS s),
       '', ''
WHERE NOT EXISTS (SELECT 1 FROM (SELECT id FROM tbl_spreadsheet_my_folder WHERE id = 910005) AS e);

INSERT INTO tbl_spreadsheet_my_folder (id, parent_id, name, type, size, staffid, category, data_form, rel_type, rel_id)
SELECT 910006, '0', 'SS Redesign Client Folder', 'folder', '', 1790, 'my_folder', NULL, '', ''
WHERE NOT EXISTS (SELECT 1 FROM (SELECT id FROM tbl_spreadsheet_my_folder WHERE id = 910006) AS i);

INSERT INTO tbl_spreadsheet_my_folder (id, parent_id, name, type, size, staffid, category, data_form, rel_type, rel_id)
SELECT 910007, '910006', 'SS Redesign Client File', 'file', '12 KB', 1790, 'my_folder',
       (SELECT s.data_form FROM (SELECT data_form AS data_form FROM tbl_spreadsheet_my_folder WHERE type = 'file' AND data_form IS NOT NULL AND data_form <> '' LIMIT 1) AS s),
       '', ''
WHERE NOT EXISTS (SELECT 1 FROM (SELECT id FROM tbl_spreadsheet_my_folder WHERE id = 910007) AS j);

INSERT INTO tbl_spreadsheet_hash_share (id, rel_type, rel_id, id_share, hash, role)
SELECT 910001, 'staff', 1, 910004, 'ssfolderhash910001', 2
WHERE NOT EXISTS (SELECT 1 FROM (SELECT id FROM tbl_spreadsheet_hash_share WHERE id = 910001) AS f);

INSERT INTO tbl_spreadsheet_hash_share (id, rel_type, rel_id, id_share, hash, role)
SELECT 910002, 'staff', 1, 910005, 'ssfixturehash910002', 2
WHERE NOT EXISTS (SELECT 1 FROM (SELECT id FROM tbl_spreadsheet_hash_share WHERE id = 910002) AS g);

INSERT INTO tbl_spreadsheet_hash_share (id, rel_type, rel_id, id_share, hash, role)
SELECT 910003, 'client', 1, 910004, 'ssfolderhash910003', 2
WHERE NOT EXISTS (SELECT 1 FROM (SELECT id FROM tbl_spreadsheet_hash_share WHERE id = 910003) AS k);

INSERT INTO tbl_client_role (user_id, menu_id)
SELECT 1790, 24
WHERE NOT EXISTS (SELECT 1 FROM (SELECT client_role_id FROM tbl_client_role WHERE user_id = 1790 AND menu_id = 24) AS h);

UPDATE tbl_client_menu SET link = 'spreadsheet'
WHERE menu_id = 24 AND label = 'spreadsheet' AND link = 'client/spreadsheet';

UPDATE tbl_account_details SET company = 1
WHERE user_id = 1790 AND (company IS NULL OR company = '');

SELECT (SELECT COUNT(*) FROM tbl_spreadsheet_my_folder WHERE id BETWEEN 910001 AND 910007) AS folder_rows,
       (SELECT COUNT(*) FROM tbl_spreadsheet_hash_share WHERE id IN (910001, 910002, 910003)) AS hash_rows,
       (SELECT COUNT(*) FROM tbl_client_role WHERE user_id = 1790 AND menu_id = 24) AS grant_rows,
       (SELECT link FROM tbl_client_menu WHERE menu_id = 24) AS menu_link,
       (SELECT company FROM tbl_account_details WHERE user_id = 1790) AS company;
```

Visibility reasoning (verified against model + live probe):
- **Admin my tab:** `staffid=my_id()=1 AND category='my_folder'` (`Spreadsheet_model.php:110-115`) → 910001-910005.
- **Client my tab:** same query with `my_id()=1790` → 910006/910007.
- **Admin share tab:** staff branch (`client_id()` empty before fixture / rel filters): `find_in_set(1, staffs_share)` + join `id_share = my_folder.id` where `rel_id=1 AND rel_type='staff'` (`Spreadsheet_model.php:600-608`) → 910004 via hash 910001; children join `hash.id_share = child.parent_id` (`:136-146`) → 910005 via hash 910001.
- **Client share tab:** with company set, client branch: `find_in_set(client_id()=1, clients_share)` + `rel_id=1 AND rel_type='client'` → 910004 via hash 910003; child 910005 via hash 910003.
- **`file_view_share('ssfixturehash910002')`:** id_share 910005 (file with data_form), `rel_id=1 == my_id()` for forid ✓ (`Spreadsheet.php:304`).
- **Layout:** company=1 → `client_id()` non-empty → `manage()` picks `client/_layout_main` (`Spreadsheet.php:44-48`) and `index()` redirects `/spreadsheet` → `spreadsheet/manage` (client branch, `:18-19`).

### 3b. Apply fixtures + sanity probe

```powershell
Get-Content plan\_tools\spreadsheet-fixtures.sql -Raw | mysql -u root tic_crm
# expected: the 5-column SELECT prints folder_rows=7, hash_rows=3,
#           grant_rows=1, menu_link=spreadsheet, company=1

# post-apply sanity: the company update must not break the client dashboard
# (leak checks in both e2e gates assert /client/dashboard = 200 + no errors):
$jar="$env:TEMP\ss_fix.txt"
curl.exe -s -c $jar -b $jar -d "user_name=mailbox_client&password=ClientMail@123&lat=0.000000&long=0.000000" -e "http://localhost/complete_crm/" http://localhost/complete_crm/login -o NUL
curl.exe -s -c $jar -b $jar -o "$env:TEMP\ss_fix_dash.html" -w "client_dashboard=%{http_code}`n" http://localhost/complete_crm/client/dashboard
Select-String -Path "$env:TEMP\ss_fix_dash.html" -Pattern "Fatal error|A Database Error Occurred|Unknown column|Attempt to assign" | Select-Object -First 1
curl.exe -s -L -c $jar -b $jar -o "$env:TEMP\ss_fix_index.html" -w "menu_target=%{http_code} -> %{url_effective}`n" http://localhost/complete_crm/spreadsheet
```
Expected: `client_dashboard=200`, no error matches, `menu_target=200 -> .../spreadsheet/manage` (R1 chain: `/spreadsheet` → `index()` client branch → bare `spreadsheet/manage`, default tab `my_folder`). **Note:** `company=1` exposed a pre-existing PHP 8 fatal in the client dashboard's recently-paid widget (`client/main_content.php:190` assigned a property on an undefined `$payment_method` — PHP 7 auto-vivified, PHP 8 throws). Fixed by commit `77458846` (`$payment_method = (object) array('method_name' => ...)` in the else branch); without it `/client/dashboard` 500s and BOTH e2e gates (spreadsheet + mailbox leak checks) fail. **Contingency (only if dashboard errors for a different reason):** try the next existing `tbl_client.client_id` (`SELECT client_id FROM tbl_client LIMIT 5` → e.g. 6) in the `company` UPDATE, re-run, and carry that value through the fixture comment + hash `rel_id` rows (910003) — `client_id()` must equal the `rel_id` in hash row 910003. (Do NOT use the company=6 contingency to dodge the dashboard bug: `find_in_set(6, clients_share)` fails the client share tab and the test's DB check asserts `company='1'`.)

### 3c. `composer.json` scripts — add after `test:mailbox`:

```json
"test:mailbox": "php plan/_tools/mailbox-redesign-test.php",
"test:spreadsheet": "php plan/_tools/spreadsheet-redesign-test.php"
```

### 3d. `plan/_tools/spreadsheet-redesign-test.php` (complete)

```php
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
```

(15 assertions: 8 page checks incl. the R1 redirect chain, 2 context-menu counts, 3 DB, 2 leak — exact total is `count($checks)` at runtime.)

### 3e. RED run

```powershell
composer test:spreadsheet
```

Expected **FAIL** (design markers absent): `id="ss-panel"`, `id="ss-crumb"`, `list-card`, `mrow`, `spreadsheet_modern.css`, `ed-head`, `ss-share-view`, plus the still-present `caption-a`/`nav-tabs-custom`. Expected **PASS** already at RED: all HTTP 200s (Task 1 + fixtures), client layout markers (`client/dashboard`, no `set_clocking` — company fixture), fixture names in server rows, context-menu counts 7/2, all 3 DB checks (link/grant/fixtures+company), leak checks. Commit message notes the RED baseline.

**Commit:** `test: spreadsheet redesign gate (RED) + fixtures`

---

## Task 4 — CSS delivery + `spreadsheet_modern.css`

### 4a. Helper delivery link — end of `spreadsheet_add_head_component()`

In `modules/spreadsheet/helpers/spreadsheet_helper.php`, immediately before the closing `}` of `spreadsheet_add_head_component()` (after the editor-branch `if`, line 64's `}`), add:

```php
    if (strpos($viewuri, '/spreadsheet') !== false) {
        echo '<link href="' . module_dir_url(SPREADSHEET_MODULE, 'assets/css/spreadsheet_modern.css') . '?v=2" rel="stylesheet" type="text/css" />';
    }
```

`$viewuri` is defined at line 31. Position = after every existing `<link>` (including `manage_style.css` at line 36) → wins cascade ties per spec §6.1. Cache key: initially `?v=1`; bumped to `?v=2` by the Task 4 review fix (`b8b95dce`) when the specificity fix changed the file — per spec §6.6 the version lives in the query string only, `VERSION_SREADSHEET` stays untouched.

### 4b. Extract demo CSS

```powershell
$demo = '.superpowers\brainstorm\1827-1791618467\content\spreadsheet-full.html'
$lines = Get-Content $demo
$css = $lines[7..189]   # <style> body, file lines 8-190
$header = "/* Spreadsheet redesign (Design A) - ported from approved demo spreadsheet-full.html,`n   adapted for in-app delivery (tokens.css variables, live markup). Spec 2026-10-10. */`n"
Set-Content -Path 'modules\spreadsheet\assets\css\spreadsheet_modern.css' -Value ($header + ($css -join "`n")) -Encoding UTF8
```

### 4c. Transform — ordered exact operations on the new file

**DELETE blocks (exact leading text → delete through closing brace):**

1. `:root{` … `}` (tokens.css provides the same vars; demo-only names mapped below)
2. `*{box-sizing` / `html,body{` / `body{` / `a{color:var(--c-accent)` / `button{font-family` (app-wide rules must not leak)
3. `.shell{` …
4. Comment `/* ============ app sidebar` + `.sidebar{` … `.nav a.on .cnt{` (all sidebar rules; comment line L24 + rules L25-L34)
5. `main{` …
6. `.screenbar{` … `.screen.on{` (demo navigation)
7. `.bar{` … `.bar i{` (quota bar — §8)
8. `.hintbar{` … `.hintbar b{` (§8)
9. `.fname{` … `.fname span{` (no `.fname` node in live rows)
10. `.ftile{` … `.ftile.ods{` (tile is CSS-generated on `.tr-pointer::before`, see additions)
11. `.mrow{` / `.mrow:last-child` / `.mrow:hover` / `.mrow.dragover` (replaced by additions below — 3-col grid, kebab absolute)
12. `.lhead{` (replaced by additions — 3-col head)
13. `.rel{` … `.rel.none{` (live class is `.related-to-hanlde`, see additions)
14. `.ed-back{` / `.ed-title*` / `.saved{` / `.sheet-frame{` / `.sheet-tools*` / `.grid*` (demo editor interior — Luckysheet untouched, §3/§8)
15. `.sv-acts{` / `.sv-prev{` / `.sv-prev .mini{` / `.sv-note{` (no such controls exist, §8)
16. `.overlay{` … `.tabs2 button.on{` (entire custom-modal block, §8 — bootstrap restyle in additions)
17. `.ctx{` … `.ctx .sep{` (live menu is `#context-menu`, restyle in additions)
18. `.notes{` … `.notes li b{` + `@media (max-width:960px)` line (§8 + desktop-only)

**RENAMES (exact string → exact string, in order):**

| # | From | To |
|---|---|---|
| 1 | `.panel{` | `.ss-panel{` |
| 2 | `.panel .p-head{` | `.ss-panel .p-head{` |
| 3 | `.panel .p-head b{` | `.ss-panel .p-head b{` |
| 4 | `.panel .p-head .badge{` | `.ss-panel .p-head .badge{` |
| 5 | `.crumb{` | `#ss-crumb{` |
| 6 | `.crumb b{` | `#ss-crumb b{` |
| 7 | `.crumb .sep{` | `#ss-crumb .sep{` |
| 8 | `.btn{` | `.ss-btn{` |
| 9 | `.btn.accent{` | `.ss-btn.accent{` |
| 10 | `.btn.accent:hover{` | `.ss-btn.accent:hover{` |
| 11 | `.btn.ghost{` | `.ss-btn.ghost{` |
| 12 | `.btn.ghost:hover{` | `.ss-btn.ghost:hover{` |
| 13 | `.btn.danger{` | `.ss-btn.danger{` |
| 14 | `.toolbar .spacer{` | `.toolbar .ss-spacer{` |
| 15 | `var(--c-mint)` | `var(--ac-mint)` |
| 16 | `var(--c-cyan)` | `var(--ac-cyan)` |
| 17 | `var(--c-purple)` | `var(--ac-purple)` |

(Kept as-is: `.f-item*`, `.p-sep`, `.p-foot*`, `.toolbar`, `.chipstat`, `.list-card`, `.pill*`, `.kebab*`, `.empty*`, `.ed-head`, `.sv-card`, `.sv-top*`, `.p-head` items. `.crumb`→`#ss-crumb` avoids colliding with the admin header's `.crumb` (`admin/components/header.php:70`); `.panel`→`.ss-panel` and `.btn`→`.ss-btn` are the spec §5 "collision-safe where names overlap Bootstrap" treatments.)

### 4d. ADDITIONS — append this block verbatim

```css

/* ===================== live-markup adaptation (2026-10-10) ===================== */

/* ---- layout shell (manage.php) ---- */
.ss-layout { display: flex; align-items: stretch; gap: 0; }
.ss-main { flex: 1; min-width: 0; padding: 6px 4px 8px; }
.ss-content { min-width: 0; }

/* ---- folder panel ---- */
.ss-layout .ss-panel { min-height: 460px; }
.ss-layout .ss-panel .f-item { text-decoration: none; }
.ss-subtree { display: flex; flex-direction: column; }
.ss-subtree:empty { display: none; }
.ss-subtree ul { list-style: none; margin: 0; padding: 0; }
.ss-subtree .ss-tf { display: flex; align-items: center; gap: 6px; height: 32px; padding: 0 8px; border-radius: var(--r-md); color: var(--c-text-2); font-size: 13px; font-weight: 400; cursor: pointer; position: relative; user-select: none; }
.ss-subtree .ss-tf:hover { background: #f1f5f4; }
.ss-subtree .ss-tf.on { background: #e6f7f5; color: var(--c-accent); font-weight: 700; }
.ss-subtree ul ul .ss-tf { margin-left: 16px; }
.ss-subtree .ss-caret { width: 16px; flex: 0 0 16px; text-align: center; font-size: 11px; color: var(--c-text-3); }
.ss-subtree .ss-tf .cnt { margin-left: auto; font-size: 10.5px; font-weight: 700; background: #eef2f5; color: var(--c-text-3); border-radius: var(--r-full); padding: 2px 7px; }
.ss-subtree .ss-tf.on .cnt { background: var(--c-accent); color: #fff; }
.ss-kids.ss-collapsed { display: none; }

/* ---- breadcrumb (id-scoped: admin header also uses .crumb) ---- */
#ss-crumb .ss-cur { color: var(--c-text-2); font-weight: 600; }
#ss-crumb a { color: var(--c-text-3); cursor: pointer; }
#ss-crumb a:hover { color: var(--c-accent); }

/* ---- toolbar ---- */
.toolbar { display: flex; align-items: center; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }
.ss-btn { display: inline-flex; align-items: center; gap: 7px; height: 36px; padding: 0 14px; border-radius: var(--r-md); border: 1px solid transparent; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; box-sizing: border-box; }
.toolbar .ss-btn { line-height: 1; }
.toolbar .ss-btn.btn-info,
.toolbar .ss-btn.accent { background: var(--c-accent); border-color: var(--c-accent); color: #fff; }
.toolbar .ss-btn.accent:hover { background: #0b7d74; border-color: #0b7d74; color: #fff; }
.toolbar .ss-btn.ghost { background: var(--c-surface); border-color: var(--c-border); color: var(--c-text-2); }
.toolbar .ss-btn.ghost:hover { border-color: var(--c-accent); color: var(--c-accent); }

/* ---- list card: table -> card rows ---- */
.list-card { background: var(--c-surface); border: 1px solid var(--c-border-2); border-radius: var(--r-xl); box-shadow: var(--sh-1); overflow: hidden; }
table#spreadsheet-advanced { display: block; width: 100%; border: 0; margin: 0; background: transparent; } /* review fix b8b95dce: `table` prefix outranks manage_style.css:1's `table#spreadsheet-advanced` (1,0,1) so border:0 + background:transparent actually win; without it the legacy #f1f8ee fill + #839E99 border survive */
#spreadsheet-advanced thead { display: block; }
#spreadsheet-advanced tbody { display: block; }
#spreadsheet-advanced thead tr,
#spreadsheet-advanced tbody tr.mrow { display: grid; grid-template-columns: minmax(0, 1.6fr) 96px minmax(0, 1.1fr); gap: 10px; align-items: center; padding: 12px 52px 12px 16px; border-bottom: 1px solid #eef1f4; position: relative; }
#spreadsheet-advanced tbody tr.mrow:last-child { border-bottom: 0; }
#spreadsheet-advanced thead tr { padding: 11px 52px 11px 16px; background: #fbfcfd; border-bottom: 1px solid var(--c-border-2); }
#spreadsheet-advanced thead th { border: 0; padding: 0; font-size: 10.5px; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; color: var(--c-text-3); background: transparent; text-align: left; }
#spreadsheet-advanced tbody tr.mrow:hover { background: #f8fbfb; }
#spreadsheet-advanced tbody tr.mrow.selected { background: #e6f7f5; }
#spreadsheet-advanced .accept { outline: 2px dashed var(--c-accent); outline-offset: -3px; background: #e6f7f5; }
#spreadsheet-advanced td { padding: 0; min-width: 0; }
#spreadsheet-advanced .indenter,
#spreadsheet-advanced .expander { display: none !important; }
#spreadsheet-advanced tr.ss-hidden { display: none !important; }

/* name cell: tile + name + secondary line */
#spreadsheet-advanced .tr-pointer { display: block; position: relative; padding-left: 48px; font-size: 13.5px; font-weight: 600; color: var(--c-text); line-height: 1.3; cursor: pointer; }
#spreadsheet-advanced .tr-pointer::before { content: "\f1c3"; font-family: 'FontAwesome'; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 38px; height: 38px; border-radius: var(--r-md); display: grid; place-items: center; color: #fff; font-size: 15px; font-weight: 400; background: linear-gradient(135deg, #10b981, #059669); }
#spreadsheet-advanced .tr-pointer.folder::before { content: "\f07b"; background: linear-gradient(135deg, #f59e0b, #d97706); }
#spreadsheet-advanced .tr-pointer .fa-group { color: var(--c-accent); font-size: 11px; }
#spreadsheet-advanced .ss-sub { display: block; font-size: 11.5px; font-weight: 400; color: var(--c-text-3); padding-left: 0; }

/* kind pill (td.qcont) */
#spreadsheet-advanced td.qcont { justify-self: start; align-self: center; background: #eef2f5; color: var(--c-text-2); font-size: 10.5px; font-weight: 700; border-radius: var(--r-full); padding: 3px 10px; text-transform: capitalize; letter-spacing: .02em; white-space: nowrap; text-align: center; }
#spreadsheet-advanced tr:has(td:first-child .folder) td.qcont { background: #fef3e2; color: #b45309; }
#spreadsheet-advanced tr:has(td:first-child .file) td.qcont { background: #e7f8f1; color: #059669; }

/* related chips / size cell */
#spreadsheet-advanced tr.mrow td:last-child { font-size: 12px; color: var(--c-text-2); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
#spreadsheet-advanced .related-to-hanlde { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: var(--c-text-2); background: #f6f8fa; border: 1px solid var(--c-border-2); border-radius: var(--r-full); padding: 3px 10px; max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; cursor: pointer; }
#spreadsheet-advanced .related-to-hanlde:hover { border-color: var(--c-accent); color: var(--c-accent); }

/* kebab */
.ss-kebab, .kebab { width: 30px; height: 30px; border-radius: 8px; border: 0; background: transparent; color: var(--c-text-3); font-size: 15px; display: grid; place-items: center; padding: 0; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); }
.ss-kebab:hover, .kebab:hover { background: #e9eef2; color: var(--c-text); }

/* empty state (JS-injected) */
.ss-empty { border: 1.5px dashed var(--c-border); border-radius: var(--r-xl); padding: 46px 20px; text-align: center; color: var(--c-text-3); background: var(--c-surface); }
.ss-empty .big { font-size: 34px; margin-bottom: 8px; }
.ss-empty b { display: block; color: var(--c-text-2); font-size: 15px; margin-bottom: 4px; }

/* ---- context menu (overrides manage_style's .context-menu block) ---- */
.context-menu { position: absolute; min-width: 200px; padding: 6px; border-radius: var(--r-md); background: var(--c-surface); border: 1px solid var(--c-border-2); box-shadow: var(--sh-2); z-index: 9999; }
.context-menu--active { display: block; }
.context-menu__items { list-style: none; margin: 0; padding: 0; }
.context-menu__item { display: block; margin-bottom: 2px; }
.context-menu__link { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 7px; color: var(--c-text-2); font-size: 13px; text-decoration: none; background: transparent; }
.context-menu__link:hover { color: var(--c-text); background: #f1f5f4; text-decoration: none; }
.context-menu__link[data-action="delete"] { color: var(--c-danger); }
.context-menu__link[data-action="delete"]:hover { background: #fdf0f0; color: var(--c-danger); }
.context-menu__link i { width: 16px; text-align: center; color: var(--c-text-3); }
.context-menu__link:hover i { color: inherit; }

/* ---- mobile action sheet: token-safe card look (position/size untouched) ---- */
.popup-overlay { border-radius: var(--r-xl); border: 1px solid var(--c-border-2); z-index: 9999; }

/* ---- bootstrap modals restyled as Design A cards (page-scoped delivery) ---- */
.modal-dialog { margin-top: 7vh; }
.modal-content { background: var(--c-surface); border: 0; border-radius: var(--r-xl); box-shadow: var(--sh-2); color: var(--c-text); }
.modal-header { display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--c-border-2); padding: 15px 20px; }
.modal-title { font-size: 15px; font-weight: 700; color: var(--c-text); letter-spacing: -.01em; }
.modal-header .close { float: none; margin: 0; opacity: 1; background: #eef2f5; border: 0; width: 28px; height: 28px; border-radius: 8px; color: var(--c-text-2); font-size: 15px; font-weight: 400; display: grid; place-items: center; }
.modal-header .close:hover { background: #e2e7eb; }
.modal-body { padding: 18px 20px; max-height: 60vh; overflow: auto; color: var(--c-text-2); font-size: 13.5px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid var(--c-border-2); background: #fbfcfd; padding: 13px 20px; margin: 0; }
.modal-footer .btn { height: 34px; border-radius: var(--r-md); font-size: 13px; font-weight: 600; padding: 0 14px; border: 1px solid transparent; display: inline-flex; align-items: center; }
.modal-footer .btn-default { background: var(--c-surface); border-color: var(--c-border); color: var(--c-text-2); }
.modal-footer .btn-default:hover { background: #f4f6f8; color: var(--c-text); }
.modal-footer .btn-info { background: var(--c-accent); border-color: var(--c-accent); color: #fff; }
.modal-footer .btn-info:hover { background: #0b7d74; border-color: #0b7d74; color: #fff; }
.modal .control-label { font-size: 11.5px; font-weight: 700; color: var(--c-text-2); letter-spacing: .02em; }
.modal .form-control { height: 38px; border: 1px solid var(--c-border); border-radius: var(--r-md); font-size: 13.5px; color: var(--c-text); box-shadow: none; }
.modal .form-control:focus { border-color: var(--c-accent); box-shadow: 0 0 0 3px rgba(13, 148, 136, .14); }
.modal ul.tabs { display: flex; gap: 6px; list-style: none; margin: 0 0 14px; padding: 0; }
.modal ul.tabs .tab-link { flex: 1; text-align: center; height: 34px; line-height: 32px; border: 1px solid var(--c-border); border-radius: var(--r-md); background: var(--c-surface); font-size: 12.5px; font-weight: 600; color: var(--c-text-2); cursor: pointer; }
.modal ul.tabs .tab-link.current { background: #e6f7f5; border-color: var(--c-accent); color: var(--c-accent); }
.modal .tab-content { display: none; }
.modal .tab-content.current { display: block; }
.modal .content-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.modal .content-table th { font-size: 10.5px; text-transform: uppercase; letter-spacing: .07em; color: var(--c-text-3); text-align: left; padding: 8px 10px; border-bottom: 1px solid var(--c-border-2); }
.modal .content-table td { padding: 8px 10px; border-bottom: 1px solid #eef1f4; color: var(--c-text-2); }

/* ---- editor page chrome (existing controls only, §8) ---- */
.upload_file_class.ed-head { display: flex; position: static; /* review fix 2b654635: neutralize manage.css:2422's legacy position:absolute so the chrome card sits in flow */ align-items: center; gap: 10px; flex-wrap: wrap; background: var(--c-surface); border: 1px solid var(--c-border-2); border-radius: var(--r-xl); padding: 12px 14px; box-shadow: var(--sh-1); margin-bottom: 14px; }
.upload_file_class.ed-head .btn { display: inline-flex; align-items: center; gap: 7px; height: 34px; padding: 0 14px; border-radius: var(--r-md); font-size: 13px; font-weight: 600; border: 1px solid transparent; }
.upload_file_class.ed-head .btn-info { background: var(--c-accent); border-color: var(--c-accent); color: #fff; }
.upload_file_class.ed-head .btn-info:hover { background: #0b7d74; border-color: #0b7d74; color: #fff; }
.upload_file_class.ed-head #file-link { margin: 0; }
.upload_file_class.ed-head .new_file_margin { margin-left: 0; }
#luckysheet { border: 1px solid var(--c-border-2); border-radius: var(--r-xl); overflow: hidden; box-shadow: var(--sh-1); background: var(--c-surface); }

/* ---- share-by-link view card ---- */
.ss-share-view { max-width: 920px; margin: 0 auto; }
.ss-share-view .sv-card { background: var(--c-surface); border: 1px solid var(--c-border-2); border-radius: var(--r-xl); box-shadow: var(--sh-1); overflow: hidden; }
.ss-share-view .sv-top { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 16px 20px; border-bottom: 1px solid var(--c-border-2); }
.ss-share-view .sv-top b { font-size: 15.5px; letter-spacing: -.01em; display: block; }
.ss-share-view .sv-body { padding: 16px; }
.ss-share-view .sv-role-chip { flex: 0 0 auto; }
```

### 4e. Verify + commit (self-contained probe)

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l modules\spreadsheet\helpers\spreadsheet_helper.php
Select-String -Path modules\spreadsheet\assets\css\spreadsheet_modern.css -Pattern 'screenbar|hintbar|\.shell\{|\.sidebar\{|\.notes\{'   # expect none

# delivery + cascade order (admin session, fresh jar):
$jar="$env:TEMP\ss_css.txt"
curl.exe -s -c $jar -b $jar -d "user_name=forid&password=Admin@123&lat=0.000000&long=0.000000" -e "http://localhost/complete_crm/" http://localhost/complete_crm/login -o NUL
curl.exe -s -c $jar -b $jar -o "$env:TEMP\ss_css_body.html" -w "manage=%{http_code}`n" http://localhost/complete_crm/admin/spreadsheet/manage/my_folder
$body = Get-Content "$env:TEMP\ss_css_body.html" -Raw
if ($body -notmatch 'spreadsheet_modern\.css\?v=') { throw 'spreadsheet_modern.css link missing' }
if ($body.IndexOf('spreadsheet_modern.css') -lt $body.IndexOf('manage_style.css')) { throw 'modern CSS emitted BEFORE manage_style.css (cascade order wrong)' }
'css delivery order OK'
```
Expected: lint OK, no deleted-block matches, `manage=200`, `css delivery order OK`. Full delivery/marker coverage is asserted by the gate in Task 9.

**Commit:** `feat: spreadsheet Design-A stylesheet + guarded delivery (spreadsheet_modern.css)`

---

## Task 5 — `manage.php` shell (panel + crumb + content slot) + lang keys

### 5a. Lang additions — `modules/spreadsheet/language/english/spreadsheet_lang.php` (append before EOF)

```php
$lang['folders'] = 'Folders';
$lang['shared'] = 'Shared';
$lang['ss_items'] = 'items';
$lang['ss_empty_title'] = 'This folder is empty';
$lang['ss_empty_hint'] = 'New files and folders you create will show up here.';
$lang['new_file'] = 'New file';
```

(`new_file` was already defined at lang line 28 as `'New File'` — plan's earlier "never existed" note was wrong: the custom `lang()` returns the key string on miss, so titles rendered `new_file`... correction: they rendered `'New File'`. The append re-defines it as `'New file'` (appended-last wins) — cosmetic casing only; consumers are titles at `Spreadsheet.php:314/575` + Task 7b's fallback. Recorded in Task 12; line-28 cleanup deferred.)

### 5b. `spreadsheet_load_js()` — manage branch (after line 77's `echo '</script>';`, before line 78's jquery-ui script echo) add:

```php
        echo '<script>';
        echo 'var ss_lang = ' . json_encode(array(
            'folder' => lang('folder'),
            'items' => lang('ss_items'),
            'edit' => lang('edit'),
            'view' => lang('view'),
            'empty_title' => lang('ss_empty_title'),
            'empty_hint' => lang('ss_empty_hint'),
        )) . ';';
        echo '</script>';
```

And after the `context_menu.js` script echo (line 81) add:

```php
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/js/spreadsheet_shell.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
```

### 5c. `manage.php` — two exact edits (order-independent; anchor by content, not by post-edit line numbers)

**Edit A:** replace the opening wrapper — from `<div class="row">` (line 3) through the `col-md-12` close plus its trailing whitespace (line 34) — with the shell below. The region contains the whole `nav-tabs-custom` tab strip.

**Edit B:** delete the orphaned standalone `</div>` at column 0 that sits between the settings modal's indented close (`    </div>`, line 79) and the `<?php / spreadsheet_load_js();` block (line 81) — it closes the old `<div class="row">` from line 3 (verified: lines 1-35 = 4 opens/3 closes, lines 36-80 = 10 opens/11 closes), whose opener is inside Edit A's range. The settings modal block itself (lines 36-79) is balanced and stays **verbatim**, as does everything from `spreadsheet_load_js()` on.

```php
<?php
$ss_active = ($tab == 'share_folder') ? 'share' : 'my';
$ss_root_label = ($ss_active == 'share') ? lang('share_folder') : lang('my_folder');
?>
<div class="ss-layout">
    <aside class="ss-panel" id="ss-panel" data-active="<?php echo $ss_active ?>">
        <div class="p-head"><b><?php echo lang('spreadsheet') ?></b><span class="badge" data-ss-total>0</span></div>
        <a class="f-item <?php echo ($ss_active == 'my') ? 'on' : ''; ?>"
           href="<?php echo admin_url('spreadsheet/manage/my_folder') ?>" data-root="my">
            <span class="ico fa fa-file-excel-o"></span><?php echo lang('my_folder'); ?><span class="cnt" data-ss-count="my"></span>
        </a>
        <div class="ss-subtree" data-subtree="my"></div>
        <a class="f-item <?php echo ($ss_active == 'share') ? 'on' : ''; ?>"
           href="<?php echo admin_url('spreadsheet/manage/share_folder') ?>" data-root="share">
            <span class="ico fa fa-share-square-o"></span><?php echo lang('share_folder'); ?><span class="cnt" data-ss-count="share"></span>
        </a>
        <div class="ss-subtree" data-subtree="share"></div>
        <div class="p-sep"></div>
        <div class="p-foot">
            <div class="row"><span><?php echo lang('files') ?></span><b data-ss-files>0</b></div>
            <div class="row"><span><?php echo lang('folders') ?></span><b data-ss-folders>0</b></div>
            <div class="row"><span><?php echo lang('shared') ?></span><b data-ss-shared>0</b></div>
        </div>
    </aside>
    <div class="ss-main">
        <div class="crumb" id="ss-crumb" data-root-label="<?php echo $ss_root_label ?>"></div>
        <div class="p ss-content">
            <?php $this->load->view('spreadsheet/' . $tab); ?>
        </div>
    </div>
</div>
```

Preserved verbatim: `spreadsheet_add_head_component()` line 2; settings modal block (36-79); `spreadsheet_load_js()`; `require manage_js.php`; `</body></html>`.
Removed: `nav-tabs-custom` tab strip (§8), `row/col-md-12` wrappers, the orphaned `row` close at old line 80.
Root `<a class="f-item">` = full-page navigation (same as today's tabs); both subtrees live in the DOM; JS renders only into `[data-subtree=active]`.

### 5d. Verify + commit

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l modules\spreadsheet\views\manage.php
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l modules\spreadsheet\helpers\spreadsheet_helper.php
```

**Commit:** `feat: spreadsheet shell - folder panel roots, crumb, panel footer (manage.php)`

---

## Task 6 — `my_folder.php` + `share_folder.php` markup

### 6a. `my_folder.php` — replace the 4 toolbar anchors (lines 110-121) with:

```php
<div class="toolbar">
    <a href="#" data-toggle="modal" class="ss-btn accent add_file_button btn-info">
        <i class="fa fa-plus-circle"></i> <?php echo lang('add_file'); ?>
    </a>
    <a href="#AddFolderModal" data-toggle="modal" class="ss-btn ghost add_folder_button btn-info">
        <i class="fa fa-plus-square-o"></i> <?php echo lang('add_folder'); ?>
    </a>
    <a href="#ShareModal" data-toggle="modal" class="ss-btn ghost add_share_button btn-info">
        <i class="fa fa-share-square-o"></i> <?php echo lang('share'); ?>
    </a>
    <a href="#RelatedModal" data-toggle="modal" class="ss-btn ghost add_related_button btn-info">
        <i class="fa fa-paw"></i> <?php echo lang('related'); ?>
    </a>
    <span class="ss-spacer"></span>
    <span class="chipstat" data-ss-chip>0 <?php echo lang('ss_items'); ?></span>
</div>
```

(All 4 `add_*_button` trigger classes + `data-toggle` targets kept — `manage.js:13,22,206,420` and `context_menu.js:439,443` depend on them.)

### 6b. `my_folder.php` — replace the table wrapper (old lines 124-157: `<div class="row"><div class="col-sm-12">…caption…table…</div></div>`) with:

```php
<div class="list-card">
    <table id="spreadsheet-advanced">
        <thead>
        <tr class="lhead">
            <th><?php echo lang('name') ?></th>
            <th><?php echo lang('kind') ?></th>
            <th><?php echo lang('related_to') ?></th>
        </tr>
        </thead>
        <?php
        echo str_replace(
            '<tr class="right-menu-position"',
            '<tr class="mrow right-menu-position"',
            html_entity_decode($folder_my_tree)
        );
        ?>
    </table>
</div>
```

(Removed: `<caption>` expand/collapse block (§8), `.row/.col-sm-12` wrappers. `mrow` class added at view level so the model's row HTML stays authoritative while spec §4/§7 list-card rows + the `mrow` marker exist in server output. Class order `mrow right-menu-position` keeps the context-menu class intact. `class="lhead"` on the header row satisfies the spec §5 class list — its styling comes from the `#spreadsheet-advanced thead tr` rule, replacing the demo's standalone `.lhead{` block deleted in Task 4c.)

### 6c. `share_folder.php` — replace lines 1-22 (row/col/caption/table wrapper) with the same shape:

```php
<div class="list-card">
    <table id="spreadsheet-advanced">
        <thead>
        <tr class="lhead">
            <th><?php echo lang('name') ?></th>
            <th><?php echo lang('kind') ?></th>
            <th><?php echo lang('size') ?></th>
        </tr>
        </thead>
        <?php
        echo str_replace(
            '<tr class="right-menu-position"',
            '<tr class="mrow right-menu-position"',
            html_entity_decode($folder_my_share_tree)
        );
        ?>
    </table>
</div>
```

(No toolbar — `share_folder.php` has none today, §8. `AddFolderModal` + hidden `ul[data-share=true]` + `#context-menu` (edit/view) untouched.)

### 6d. Verify + commit

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l modules\spreadsheet\views\my_folder.php
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l modules\spreadsheet\views\share_folder.php
```

**Commit:** `feat: spreadsheet list-card rows + Design-A toolbar (my_folder, share_folder)`

---

## Task 7 — Editor + share-by-link chrome

### 7a. `new_file_view.php` line 14

```php
    <div class="upload_file_class ed-head">
```

(Nothing else in the file changes — Upload link, file input, Share/Related, `#luckysheet`, `SaveAsModal` verbatim, §8.)

### 7b. `share_file_view.php` — replace lines 13-22 (the bare `<div class="col-sm-12">` wrapper through its close) with the block below — the block is complete: `ss-share-view > (sv-card > (sv-top, sv-body > form))`, all three levels opened and closed; the existing `SaveAsModal` block (lines 25-42) and everything after stay verbatim:

```php
<div class="col-sm-12 ss-share-view">
    <div class="sv-card">
        <div class="sv-top">
            <div>
                <b><?php echo (isset($file_excel) && !empty($file_excel->name)) ? $file_excel->name : lang('new_file') ?></b>
                <span><?php echo lang('share_detail') ?></span>
            </div>
            <span class="chipstat sv-role-chip"><?php echo ((int) $role === 2) ? lang('edit') : lang('view') ?></span>
        </div>
        <div class="sv-body">
        <?php
        echo form_hidden('type', 2); ?>
        <?php echo form_hidden('role', $role); ?>
        <?php echo form_open_multipart(admin_url('spreadsheet/file_view_share'), array('id' => 'spreadsheet-test-form')); ?>
        <div id="luckysheet"></div>
        <?php echo form_hidden('parent_id', $parent_id); ?>
        <?php echo form_hidden('id', isset($id) ? $id : ""); ?>
        <?php echo form_close(); ?>
        </div>
    </div>
</div>
```

Notes: `$role` / `$file_excel` are set by `file_view_share()` (`Spreadsheet.php:293,336`); the `<b>` title falls back to `lang('new_file')` when `$file_excel` is unset (empty-hash path, `Spreadsheet.php:308-310` — key added in Task 5a). The original form PHP (form_hidden/form_open/`#luckysheet`/form_close) is carried over inside the block unchanged.

### 7c. Verify + commit

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l modules\spreadsheet\views\new_file_view.php
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l modules\spreadsheet\views\share_file_view.php
```

**Commit:** `feat: spreadsheet editor + share-link page chrome (Design A)`

---

## Task 8 — `spreadsheet_shell.js` (drill filter, panel, crumb, counts, kebab, empty)

New file `modules/spreadsheet/assets/js/spreadsheet_shell.js` (loaded only on `/spreadsheet/manage` from Task 5b, guarded on `#ss-panel` + `#spreadsheet-advanced` — `new_file_view`/`file_view_share` never match the guard):

```js
/**
 * Spreadsheet Design-A shell (presentation-side only).
 * All state derives from the server-rendered data-tt-* rows: zero network
 * behavior. Kebab dispatches a synthetic contextmenu so context_menu.js keeps
 * owning the menu (7/2 actions, position, dispatch) untouched.
 */
(function ($) {
    "use strict";

    var $panel = $('#ss-panel');
    var $table = $('#spreadsheet-advanced');
    if (!$panel.length || !$table.length || typeof window.ss_lang === 'undefined') {
        return;
    }

    var $crumb = $('#ss-crumb');
    var rootLabel = $crumb.attr('data-root-label') || 'Folder';
    var activeRoot = $panel.attr('data-active') || 'my';
    var currentId = '0';
    var nodes = {};
    var childIndex = {};
    var drillTimer = null;

    function esc(s) {
        return $('<div>').text(s == null ? '' : String(s)).html();
    }

    function collect() {
        nodes = {};
        childIndex = {};
        $table.find('tbody tr.right-menu-position').each(function () {
            var $tr = $(this);
            var id = String($tr.data('tt-id'));
            var parent = $tr.attr('data-tt-parent-id');
            parent = (parent === undefined || parent === null || parent === '') ? '0' : String(parent);
            nodes[id] = {
                id: id,
                parent: parent,
                type: String($tr.data('tt-type') || ''),
                name: $tr.attr('data-tt-name') || '',
                role: $tr.attr('data-tt-role') || '',
                $row: $tr
            };
            (childIndex[parent] = childIndex[parent] || []).push(id);
        });
    }

    function descendantCount(id) {
        var kids = childIndex[id] || [];
        var total = kids.length;
        for (var i = 0; i < kids.length; i++) {
            total += descendantCount(kids[i]);
        }
        return total;
    }

    function pathOf(id) {
        var path = [];
        var cur = id;
        while (cur && cur !== '0' && nodes[cur]) {
            path.unshift(cur);
            cur = nodes[cur].parent;
        }
        return path;
    }

    function applyFilter() {
        $table.find('tbody tr.right-menu-position').each(function () {
            var $tr = $(this);
            var parent = $tr.attr('data-tt-parent-id');
            parent = (parent === undefined || parent === null || parent === '') ? '0' : String(parent);
            if (parent === currentId) {
                $tr.removeClass('ss-hidden').css('display', '');
            } else {
                $tr.addClass('ss-hidden').css('display', 'none');
            }
        });
        window.ssCurrentFolder = currentId;
        renderCrumb();
        renderCounts();
        renderEmpty();
        var $sub = $panel.find('.ss-subtree[data-subtree="' + activeRoot + '"]');
        $sub.find('.ss-tf').removeClass('on');
        if (currentId !== '0') {
            $sub.find('[data-drill="' + currentId + '"]').addClass('on');
        }
    }
    window.ssApplyFilter = applyFilter;

    function drill(id) {
        if (id !== '0' && !nodes[id]) {
            return;
        }
        currentId = id;
        applyFilter();
    }

    function renderCrumb() {
        var html = '<b>' + esc(rootLabel) + '</b>';
        if (currentId !== '0' && nodes[currentId]) {
            var path = pathOf(currentId);
            for (var i = 0; i < path.length; i++) {
                var n = nodes[path[i]];
                var last = (i === path.length - 1);
                html += ' <span class="sep">/</span> ';
                html += last
                    ? '<span class="ss-cur">' + esc(n.name) + '</span>'
                    : '<a data-drill="' + n.id + '">' + esc(n.name) + '</a>';
            }
        }
        $crumb.html(html);
    }

    function renderCounts() {
        var total = 0, files = 0, folders = 0, shared = 0;
        for (var id in nodes) {
            if (!Object.prototype.hasOwnProperty.call(nodes, id)) { continue; }
            var n = nodes[id];
            total++;
            if (n.type === 'folder') { folders++; } else { files++; }
            if (n.role !== '' || n.$row.find('.share-status').length) { shared++; }
        }
        $panel.find('[data-ss-total]').text(total);
        $panel.find('.f-item.on [data-ss-count]').text(total);
        $panel.find('[data-ss-files]').text(files);
        $panel.find('[data-ss-folders]').text(folders);
        $panel.find('[data-ss-shared]').text(shared);
        var $chip = $('[data-ss-chip]');
        if ($chip.length) {
            $chip.text(total + ' ' + ss_lang.items);
        }
    }

    function renderEmpty() {
        var visible = 0;
        $table.find('tbody tr.right-menu-position').each(function () {
            if (!$(this).hasClass('ss-hidden')) { visible++; }
        });
        var $state = $table.next('.ss-empty');
        if (visible === 0) {
            if (!$state.length) {
                $state = $('<div class="empty ss-empty">' +
                    '<span class="big fa fa-folder-open-o"></span>' +
                    '<b>' + esc(ss_lang.empty_title) + '</b>' +
                    esc(ss_lang.empty_hint) + '</div>');
                $table.after($state);
            }
            $table.addClass('ss-hidden-table').hide();
            $state.show();
        } else {
            $state.hide();
            $table.show();
        }
    }

    function subtreeHtml(ids) {
        var html = '<ul>';
        for (var i = 0; i < ids.length; i++) {
            var n = nodes[ids[i]];
            var kids = (childIndex[n.id] || []).filter(function (k) {
                return nodes[k] && nodes[k].type === 'folder';
            });
            var count = descendantCount(n.id);
            html += '<li>' +
                '<div class="ss-tf" data-drill="' + n.id + '">' +
                (kids.length
                    ? '<span class="ss-caret fa fa-caret-right" data-caret="' + n.id + '"></span>'
                    : '<span class="ss-caret"></span>') +
                '<span>' + esc(n.name) + '</span>' +
                (count ? '<span class="cnt">' + count + '</span>' : '') +
                '</div>' +
                (kids.length
                    ? '<div class="ss-kids ss-collapsed" data-kids="' + n.id + '">' + subtreeHtml(kids) + '</div>'
                    : '') +
                '</li>';
        }
        return html + '</ul>';
    }

    function renderSubtree() {
        var $sub = $panel.find('.ss-subtree[data-subtree="' + activeRoot + '"]');
        $sub.empty();
        var roots = childIndex['0'] || [];
        var folders = [];
        for (var i = 0; i < roots.length; i++) {
            if (nodes[roots[i]] && nodes[roots[i]].type === 'folder') {
                folders.push(roots[i]);
            }
        }
        if (folders.length) {
            $sub.html(subtreeHtml(folders));
        }
    }

    function appendSublines() {
        $table.find('tbody tr.right-menu-position').each(function () {
            var $tr = $(this);
            var n = nodes[String($tr.data('tt-id'))];
            if (!n) { return; }
            var text = '';
            if (n.type === 'folder') {
                text = ss_lang.folder + ' · ' + descendantCount(n.id) + ' ' + ss_lang.items;
            } else if (n.role !== '') {
                text = (String(n.role) === '2') ? ss_lang.edit : ss_lang.view;
            }
            if (text) {
                var $span = $tr.find('.tr-pointer').first();
                if ($span.length && !$span.find('.ss-sub').length) {
                    $span.append('<span class="ss-sub">' + esc(text) + '</span>');
                }
            }
        });
    }

    function appendKebab() {
        $table.find('tbody tr.right-menu-position').each(function () {
            var $td = $(this).children('td').first();
            if ($td.length && !$td.find('.ss-kebab').length) {
                $td.append('<button type="button" class="kebab ss-kebab" title="Menu"><i class="fa fa-ellipsis-v"></i></button>');
            }
        });
    }

    /* kebab -> synthetic contextmenu (context_menu.js owns the menu; its
       document click listener runs first — registered earlier — closing any
       stale menu before this opens a fresh one at the pointer). */
    $(document).on('click', '.ss-kebab', function (e) {
        e.preventDefault();
        this.dispatchEvent(new MouseEvent('contextmenu', {
            bubbles: true, cancelable: true, view: window,
            clientX: e.clientX, clientY: e.clientY, button: 2
        }));
    });

    /* folder name click = drill, debounced so double-click (rename) wins */
    $(document).on('click', '#spreadsheet-advanced .tr-pointer.folder', function () {
        var id = String($(this).closest('tr').data('tt-id'));
        window.clearTimeout(drillTimer);
        drillTimer = window.setTimeout(function () { drill(id); }, 260);
    });
    $(document).on('dblclick', '#spreadsheet-advanced .tr-pointer', function () {
        window.clearTimeout(drillTimer);
    });

    /* crumb segments + panel subtree navigate client-side */
    $(document).on('click', '[data-drill]', function (e) {
        if ($(e.target).closest('.ss-caret').length) { return; }
        e.preventDefault();
        window.clearTimeout(drillTimer);
        drill(String($(this).attr('data-drill')));
    });

    /* panel carets expand/collapse the subtree (replaces caption buttons) */
    $(document).on('click', '.ss-caret[data-caret]', function (e) {
        e.stopPropagation();
        e.preventDefault();
        var id = $(this).attr('data-caret');
        $('[data-kids="' + id + '"]').toggleClass('ss-collapsed');
        $(this).toggleClass('fa-caret-right fa-caret-down');
    });

    $(function () {
        collect();
        renderSubtree();
        appendSublines();
        appendKebab();
        applyFilter();
    });
})(jQuery);
```

Notes:
- `manage.js:132` click handler still sets modal ids on every `.tr-pointer` click (unchanged); the 260 ms drill delay makes double-click → rename deterministic (timeout cleared by `dblclick`).
- Root `<a class="f-item">` clicks = normal full-page navigation (server), unchanged.
- Right-click path untouched; `$(document).mouseup` (`manage.js:212`) runs before the kebab `click`, so the menu opened by the synthetic event stays open.

### Verify + commit

```powershell
node --check modules\spreadsheet\assets\js\spreadsheet_shell.js
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l modules\spreadsheet\views\manage.php
```

**Commit:** `feat: spreadsheet shell js - drill filter, panel carets, crumb, counts, kebab menu`

---

## Task 9 — e2e GREEN

```powershell
composer test:spreadsheet
```

Fix-forward until all assertions pass (candidates: marker wording in views, `mrow` str_replace matching exactly, `ss-share-view` wrapper, client-layout markers). Re-run after every fix. Then re-run the full set:

```powershell
composer smoke
composer test:login
composer test:menu
composer test:mailbox
composer test:woo
composer test:theme   # expected: pre-existing FAIL baseline (documented in WooCommerce record) — diff vs baseline unchanged
```

**Commit:** `test: spreadsheet redesign gate green`

---

## Task 10 — Lint + regression sweep

```powershell
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"
& $php -l application\helpers\admin_helper.php
& $php -l application\migrations\645_add_spreadsheet_client_menu_link.php
& $php -l modules\spreadsheet\install.php
& $php -l modules\spreadsheet\helpers\spreadsheet_helper.php
& $php -l modules\spreadsheet\views\manage.php
& $php -l modules\spreadsheet\views\my_folder.php
& $php -l modules\spreadsheet\views\share_folder.php
& $php -l modules\spreadsheet\views\new_file_view.php
& $php -l modules\spreadsheet\views\share_file_view.php
& $php -l modules\spreadsheet\language\english\spreadsheet_lang.php
& $php -l plan\_tools\spreadsheet-redesign-test.php
```

All must print `No syntax errors detected`. **Commit** (if any fixups were needed): `chore: spreadsheet redesign lint pass`.

---

## Task 11 — Playwright pass (visual + interaction, spec §7)

Use the browser tools on `http://localhost/complete_crm`:

1. **Admin login** (`forid` / `Admin@123`) → `/admin/spreadsheet/manage/my_folder`:
   - screenshot → compare against demo `#s-main` (panel left, teal active root, crumb, toolbar 4 buttons + chip, card rows w/ tile·name·subline, pill, related chip, kebab, no tab strip, no caption buttons);
   - drill: single-click `SS Redesign Fixture Folder` → rows filter to children, crumb shows `My Folder / SS Redesign Fixture Folder`, counts update;
   - crumb segment click → back to root; panel caret expand/collapse; empty state on `SS Redesign Nested Folder` (no children);
   - right-click a row → menu (7 items) positioned; kebab click → same menu; menu action click → `create_folder` modal opens (close it); double-click folder → rename modal (close); double-click file → editor page;
   - drag `SS Redesign Fixture File` onto `SS Redesign Nested Folder` → row moves (POST fires), filter stays consistent;
   - open all 4 toolbar modals + settings modal → Design-A card styling, fields/labels intact.
2. **Share folder tab** → root switch via panel, shared rows w/ role subline, 2-item menu (edit·view).
3. **Editor** `/admin/spreadsheet/new_file_view/910001/910002` → `ed-head` chrome + untouched Luckysheet; **share link** `/admin/spreadsheet/file_view_share/ssfixturehash910002` → card + role chip.
4. **Client** login (`mailbox_client` / `ClientMail@123`) → client sidebar menu link `spreadsheet` (R1: bare URL → lands on `spreadsheet/manage`, **client layout** — `client/dashboard` in sidebar, no admin `set_clocking` bar) → My Folder shows `SS Redesign Client Folder` (drill → `SS Redesign Client File`), Share Folder shows the shared tree, kebab + modals work, panel roots/crumb/counters behave as admin. If a share-row "view" action lands on an error page, note it — the child rows join the parent's hash (existing app behavior), the gate only asserts the explicit admin hash URL.
5. **Console:** `playwright_browser_console_messages level=error` → must be empty on every screen (zero new errors).
6. **Computed-style spot checks** (marker gates can't see these — Task 4 review proved a specificity loss slipped past both):
   - `getComputedStyle(document.querySelector('table#spreadsheet-advanced')).backgroundColor` must NOT be `rgb(241, 248, 238)` (legacy `#f1f8ee` fill) and `borderWidth` must be `0px` (the `table#...` + `background:transparent` fix, `b8b95dce`);
   - `getComputedStyle(document.querySelector('#context-menu')).display` === `'none'` when inactive;
   - `getComputedStyle(document.querySelector('.ss-btn')).borderRadius` !== `'0px'`.
7. Iterate CSS in `spreadsheet_modern.css` (cache key is `?v=2`; bump to `?v=3` etc. if cache bites) until visual match; re-run `composer test:spreadsheet` after any edit.

**Commit:** `fix: spreadsheet redesign visual pass adjustments` (only if files changed).

---

## Task 12 — Verification record + close

Create `docs/superpowers/2026-10-10-spreadsheet-redesign-verification.md` (mailbox record structure):

1. Summary + gate table (`smoke / test:login / test:menu / test:mailbox / test:woo` green, `test:theme` = pre-existing baseline FAIL, `test:spreadsheet` = N/N).
2. Screenshots table (4 screens × admin/client).
3. **Reachability proof:** before → after (`menu link client/spreadsheet 404 → spreadsheet 200` — both `tic_crm` and `tic_crm_demo`; `client bare manage 500 Unknown column created_by → 200`; `admin-prefixed client URL 500 → 200`; `GET /spreadsheet 307 → admin/spreadsheet/manage (admin layout, company NULL) → 307 → spreadsheet/manage (client layout) after the company fixture`).
4. Deviations/hardening: `shareModal.php`/`RelatedModal.php` needed no markup change (restyled wholesale by the `.modal-content` block — spec §6.2 candidate list achieved without markup churn); R1b `install.php` link fix (both `modules/` and the `demo/modules/` mirror); `mrow` added at view level (model untouched); `.panel`/`.btn`/`.crumb` collision-safe scoping; **fixture links `mailbox_client` to `tbl_client.client_id=1` via `tbl_account_details.company`** — the account shipped with `company=NULL`, so `client_id()` was empty and `manage()` served the ADMIN layout (verified live via `index()` redirect); spec §7's client-layout requirement needs a non-empty `client_id`; side effects audited (mailbox gate layout is unconditional; only the mailbox test touches mailbox_client; login redirect order unchanged); demo `.av`/`.pill` rules have no live markup counterparts (rows use `td.qcont` pill styling); **third bug fix forced by the fixture: `client/main_content.php:190` PHP 8 fatal guard** (`77458846`) — `company=1` exposed the recently-paid widget's undefined-`$payment_method` assignment, which 500'd `/client/dashboard` and failed BOTH e2e gates' leak checks; repo has no `demo/modules` runtime conflict (MODULES_PATH is root `modules/`); **Task 4 review cascade fix** (`b8b95dce`): `#spreadsheet-advanced` → `table#spreadsheet-advanced` + `background:transparent` (outranks `manage_style.css:1` (1,0,1) — without it the legacy `#f1f8ee` fill/`#839E99` border survived the redesign and no marker gate could see it) and cache key `?v=1 → ?v=2`; **review minors backlog** (non-blocking): test `db()` should `mysqli_report(MYSQLI_REPORT_OFF)` so MySQL-down hits `fatal()` instead of an uncaught exception (plan-authored test hygiene), `client/main_content.php:185` null-`$invoices_info` guard (pre-existing, not triggered by current data), CSS `.ss-sub` dead rule + unscoped `.kebab` absolute positioning (leave — additions block is plan-canonical, no live node matches), `strpos($viewuri,'/spreadsheet')` query-string false positive (accepted: tightening to `/spreadsheet/` would risk bare-URL misses); **mid-execution review fixes applied:** `.p-foot .row` flex + `.f-item .cnt:empty{display:none}` (`fa9ad73c`, Task 5 review — Bootstrap `.row` negative margins/clearfix mangled the footer; inactive root showed an empty count pill), `.upload_file_class.ed-head` + `position:static` (`2b654635`, Task 7 review — legacy `manage.css:2422` absolute positioning otherwise overlays the sheet); further non-blocking notes: gate `mrow` marker could tighten to `<tr class="mrow right-menu-position"` (Task 9 candidate), Task 11 must assert chip ≠ `0 items` + ed-head in-flow + footer spacing, `Spreadsheet_model.php:630` appends `"<tbody>"` instead of `</tbody>` (frozen pre-existing), `manage.css` `.new_file_margin !important` −7px legacy (cosmetic on editor head), share-view name echo is a new echo site but same-privilege/app-pattern (info).
5. Follow-ups (non-blocking): panel-root drag-and-drop targets, per-row "edited …" secondary line (needs `updated_at` in row attrs → data layer change, out of scope), i18n for new keys beyond English.

**Commit:** `docs: spreadsheet redesign verification record`

---

## Guardrails (every task)

- Stage only intended files — never `git add docs` or `git add .` (stray untracked files: `_design/`, `docs/superpowers/specs/2026-09-27-dashboard-redesign-design.md`, root PNGs, `plan/_tools/_probe_modal.php` stay untracked).
- PowerShell 5.1: no `&&`, no `<`, no single-quoted PHP `-r`; use `curl.exe` (not `curl`); chain with `cmd1; if ($?) { cmd2 }`.
- Zero changes to: `Spreadsheet.php`, `Spreadsheet_model.php`, `manage.js`, `context_menu.js`, `manage_style.css`, `htmlheader.php`, other modules' embeds (URI guards keep them on old CSS).
- Every UI string via existing/new `lang()` keys — no hardcoded English in views except `title=` attributes on kebab.
- If a gate fails unexpectedly: `git diff` the last task, fix-forward, re-run; do not skip or weaken assertions.
