# Mailbox Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Reskin all 7 mailbox screens (Inbox, Sent, Drafts, Favourites, Trash, Read, Compose) for admin + client to the approved Design A using a new `mailbox_modern.css` and in-place view edits — zero controller/model/URL changes.

**Architecture:** New `assets/css/mailbox_modern.css` is loaded via a URI-conditional `<link>` in the shared head component (`admin/components/htmlheader.php`, used by BOTH roles). View files are restructured from Bootstrap-3 tables/panels to the demo's card/row markup; all PHP logic, form actions, `lang()` calls, and JS hooks (`#parent_present`, `.child_present`, dropzone, select2) stay verbatim. The client view set is regenerated from the admin set with an `admin/mailbox → client/mailbox` prefix swap (they are identical modulo that prefix).

**Tech Stack:** CodeIgniter 3 PHP views, Bootstrap 3 (reskinned), Font Awesome 4, global `tokens.css`, PHP 8.3, curl-based e2e test + Playwright visual pass.

**Spec:** `docs/superpowers/specs/2026-10-03-mailbox-redesign-design.md`
**Demo reference:** `.superpowers/brainstorm/1374-1790861074/content/mailbox-full.html`

---

## File structure

| Action | File | Responsibility |
|---|---|---|
| Create | `plan/_tools/mailbox-redesign-test.php` | e2e gate (`composer test:mailbox`) |
| Create | `assets/css/mailbox_modern.css` | all mailbox component styles |
| Modify | `application/views/admin/components/htmlheader.php` | URI-conditional CSS link (shared by admin + client) |
| Modify | `application/views/admin/mailbox/mailbox.php` | folder panel wrapper + `$view` slot |
| Modify | `application/views/admin/mailbox/inbox.php` | inbox list card |
| Modify | `application/views/admin/mailbox/sent.php` | sent list card |
| Modify | `application/views/admin/mailbox/draft.php` | drafts list card |
| Modify | `application/views/admin/mailbox/favourites.php` | favourites list card |
| Modify | `application/views/admin/mailbox/trash.php` | trash tabs + sublist loader |
| Modify | `application/views/admin/mailbox/trash/inbox.php` | trash sublist (inbox) |
| Modify | `application/views/admin/mailbox/trash/sent.php` | trash sublist (sent) |
| Modify | `application/views/admin/mailbox/trash/draft.php` | trash sublist (draft) |
| Modify | `application/views/admin/mailbox/read_mail.php` | full-page read card |
| Modify | `application/views/admin/mailbox/compose_mail.php` | container-class reskin (JS/dropzone untouched) |
| Regenerate | `application/views/client/mailbox/**` (10 files) | prefix-swap mirror of admin set |
| Modify | `composer.json` | add `test:mailbox` script |
| Create | `docs/superpowers/2026-10-03-mailbox-redesign-verification.md` | Task 9 record |

**Canonical class names** (used identically by CSS, views, and test markers):
`.mbflex`, `.folders`, `.f-item`, `.fc`, `.list-card`, `.list-toolbar`, `.tbtn`, `.tb-check`, `.tb-compose`, `.rows`, `.mrow`, `.udot`, `.mb-av`, `.m-main`, `.m-top`, `.m-from`, `.m-time`, `.m-subj`, `.m-prev`, `.f-tag`, `.mb-star`, `.list-status`, `.mb-empty`, `.read-card`, `.read-head`, `.rh-top`, `.mb-back`, `.rh-tools`, `.r-subj`, `.r-who`, `.read-body`, `.att-strip`, `.att-file`, `.mb-compose`, `.compose-card`, `.cc-body`, `.cc-foot`, `.trash-tabs`, `.ttab`.

## Conventions used by every task

- PHP binary: `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe` (call as `php` if on PATH; otherwise substitute).
- **Client mirror step** (run after every admin view edit — replaces ONLY the mailbox role prefix; `admin/common/...` URLs inside compose must stay `admin/`):

```powershell
$src = 'application\views\admin\mailbox\inbox.php'   # ← file for this task
$dst = 'application\views\client\mailbox\inbox.php'  # ← same path under client
$c = [System.IO.File]::ReadAllText((Resolve-Path $src))
$c = $c.Replace('admin/mailbox', 'client/mailbox')
[System.IO.File]::WriteAllText((Resolve-Path $dst), $c)
if (Select-String -Path $dst -Pattern 'admin/mailbox' -Quiet) { throw 'role swap failed' }
& C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l $dst
```

- Test command: `composer test:mailbox` (exits 0 green, 1 red).
- One commit per task; commit messages as specified.

---

### Task 1: e2e test harness (`test:mailbox`)

**Files:**
- Create: `plan/_tools/mailbox-redesign-test.php`
- Modify: `composer.json` (scripts)

- [ ] **Step 1: Write the failing test**

Create `plan/_tools/mailbox-redesign-test.php`:

```php
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
```

- [ ] **Step 2: Register the composer script**

In `composer.json`, inside `"scripts"`, add after `"test:woo"`:

```json
"test:mailbox": "php plan/_tools/mailbox-redesign-test.php"
```

- [ ] **Step 3: Run the test to verify it fails**

Run: `composer test:mailbox`
Expected: FAIL (exit 1) — every folder/read/compose assertion reports `missing marker: mailbox_modern.css`.

- [ ] **Step 4: Commit**

```bash
git add plan/_tools/mailbox-redesign-test.php composer.json
git commit -m "test: add mailbox redesign e2e test"
```

---

### Task 2: `mailbox_modern.css` + conditional stylesheet link

**Files:**
- Create: `assets/css/mailbox_modern.css`
- Modify: `application/views/admin/components/htmlheader.php` (after the `app.css` link, ~line 47)

- [ ] **Step 1: Create the stylesheet**

Create `assets/css/mailbox_modern.css`:

```css
/* mailbox_modern.css — Design A (list + full-page read)
   Tokens come from assets/css/tokens.css (loaded globally before this file). */

/* ---------- layout ---------- */
.mbflex { display: flex; gap: 14px; align-items: flex-start; }
.mbflex .list-col { flex: 1; min-width: 0; }

/* ---------- folders panel ---------- */
.folders {
  width: 212px; flex: 0 0 212px;
  background: var(--c-surface); border: 1px solid var(--c-border-2);
  border-radius: 16px; box-shadow: 0 1px 2px rgba(15, 23, 42, .06);
  padding: 10px;
}
.folders .sec {
  font-size: 10px; font-weight: 800; letter-spacing: .07em;
  text-transform: uppercase; color: var(--c-slate); padding: 8px 10px 6px;
}
.f-item {
  display: flex; align-items: center; gap: 9px; height: 38px;
  border-radius: 9px; padding: 0 10px; font-size: 13.5px; font-weight: 600;
  color: var(--c-text-2); cursor: pointer; user-select: none;
  position: relative; text-decoration: none;
}
.f-item:hover { background: #f1f3f5; color: var(--c-text-2); }
.f-item.on { background: #ecfdf5; color: var(--c-accent); }
.f-item.on::before {
  content: ""; position: absolute; left: -10px; top: 7px; bottom: 7px;
  width: 3px; border-radius: 99px; background: var(--c-accent);
}
.f-item .fi { width: 18px; text-align: center; font-size: 13px; flex: 0 0 18px; }
.f-item .fc {
  margin-left: auto; font-size: 10.5px; font-weight: 800;
  background: #f1f3f5; color: var(--c-text-3); border-radius: 99px;
  padding: 2px 8px; min-width: 24px; text-align: center;
}
.f-item .fc.hot { background: #fef2f2; color: #dc2626; }
.f-item.on .fc, .f-item.on .fc.hot { background: var(--c-accent); color: #fff; }

/* ---------- list card ---------- */
.list-card {
  background: var(--c-surface); border: 1px solid var(--c-border-2);
  border-radius: 16px; box-shadow: 0 1px 2px rgba(15, 23, 42, .06);
  display: flex; flex-direction: column; min-width: 0; overflow: hidden;
}
.list-toolbar {
  display: flex; align-items: center; gap: 8px;
  padding: 12px 14px; border-bottom: 1px solid #f1f3f5;
}
.tb-check { width: 15px; height: 15px; accent-color: var(--c-accent); }
.tbtn {
  width: 30px; height: 30px; border-radius: 8px; background: #f1f3f5;
  display: inline-grid; place-items: center; color: var(--c-text-2);
  font-size: 12.5px; border: 0; cursor: pointer; text-decoration: none;
}
.tbtn:hover { background: #e9ecef; color: var(--c-text); }
.tbtn.danger:hover { background: #fef2f2; color: #dc2626; }
.tb-compose {
  margin-left: auto; height: 32px; border-radius: 8px;
  background: var(--c-accent); color: #fff; font-size: 12.5px; font-weight: 700;
  padding: 0 14px; display: inline-flex; align-items: center; gap: 7px;
  text-decoration: none; box-shadow: 0 1px 2px rgba(13, 148, 136, .35);
}
.tb-compose:hover { background: #0b7c72; color: #fff; }

/* ---------- rows ---------- */
.rows { flex: 1; min-height: 0; }
.mrow {
  display: flex; gap: 11px; padding: 12px 14px;
  border-bottom: 1px solid #f1f3f5; cursor: pointer;
  position: relative; align-items: flex-start;
}
.mrow:last-child { border-bottom: 0; }
.mrow:hover { background: #fafcfb; }
.mrow.unread .m-from, .mrow.unread .m-subj { color: var(--c-text); font-weight: 800; }
.udot { width: 7px; height: 7px; border-radius: 99px; background: var(--c-accent); display: none; flex: 0 0 7px; margin-top: 11px; }
.mrow.unread .udot { display: block; }
.mrow .tb-check { margin-top: 9px; flex: 0 0 auto; }
.mb-av {
  width: 34px; height: 34px; border-radius: 99px; flex: 0 0 34px;
  display: grid; place-items: center; color: #fff; font-size: 12px; font-weight: 800;
}
.mrow .m-main { flex: 1; min-width: 0; }
.mrow .m-top { display: flex; align-items: baseline; gap: 8px; }
.mrow .m-from { font-size: 13px; font-weight: 700; color: var(--c-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.mrow .m-time { margin-left: auto; font-size: 11px; color: var(--c-slate); white-space: nowrap; flex: 0 0 auto; }
.mrow .m-time .tbtn { width: 24px; height: 24px; font-size: 11px; display: inline-grid; vertical-align: middle; margin-left: 4px; }
.mrow .m-subj { font-size: 13px; font-weight: 600; color: var(--c-text-2); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.mrow .m-subj a { color: inherit; text-decoration: none; }
.mrow .m-subj a:hover { color: var(--c-accent); }
.mrow .m-prev { font-size: 12px; color: var(--c-slate); margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.mrow .m-prev .fa { font-size: 11px; }
.mb-star { margin-left: auto; color: #cbd5e1; font-size: 14px; flex: 0 0 auto; text-decoration: none; align-self: center; }
.mb-star:hover { color: var(--c-amber); }
.mb-star.on { color: var(--c-amber); }

/* ---------- status + empty ---------- */
.list-status {
  border-top: 1px solid #f1f3f5; padding: 9px 14px; font-size: 11.5px;
  color: var(--c-slate); display: flex; justify-content: space-between; align-items: center;
}
.mb-empty { padding: 50px 16px; text-align: center; color: var(--c-slate); }
.mb-empty .big { font-size: 30px; margin-bottom: 8px; color: #cbd5e1; }
.mb-empty b { display: block; color: var(--c-text-2); font-size: 14.5px; margin-bottom: 4px; }

/* ---------- trash tabs ---------- */
.trash-tabs { display: flex; gap: 8px; margin-bottom: 12px; }
.ttab {
  height: 34px; border-radius: 999px; border: 1px solid var(--c-border-2);
  background: #fff; color: var(--c-text-2); font-size: 12.5px; font-weight: 700;
  padding: 0 15px; display: inline-flex; align-items: center; gap: 7px;
  text-decoration: none;
}
.ttab:hover { background: #f1f3f5; }
.ttab.on { background: var(--c-accent); border-color: var(--c-accent); color: #fff; box-shadow: 0 1px 2px rgba(13, 148, 136, .35); }

/* ---------- read page ---------- */
.read-card {
  background: var(--c-surface); border: 1px solid var(--c-border-2);
  border-radius: 16px; box-shadow: 0 1px 2px rgba(15, 23, 42, .06);
  display: flex; flex-direction: column; min-width: 0; overflow: hidden;
}
.read-head { padding: 14px 18px; border-bottom: 1px solid #f1f3f5; }
.read-head .rh-top { display: flex; align-items: center; gap: 8px; margin-bottom: 9px; }
.mb-back {
  display: inline-flex; align-items: center; gap: 6px; height: 30px;
  border-radius: 8px; background: #f1f3f5; border: 0; color: var(--c-text-2);
  font-size: 12.5px; font-weight: 700; padding: 0 12px; cursor: pointer;
}
.mb-back:hover { background: #e9ecef; color: var(--c-text); }
.read-head .rh-tools { margin-left: auto; display: flex; gap: 6px; }
.r-subj { font-size: 18px; font-weight: 800; letter-spacing: -.015em; margin: 0 0 10px; color: var(--c-text); }
.r-who { display: flex; align-items: center; gap: 10px; }
.r-who .mb-av { width: 38px; height: 38px; flex: 0 0 38px; font-size: 13px; }
.r-who .who { font-size: 13.5px; font-weight: 700; color: var(--c-text); }
.r-who .em { font-size: 12px; color: var(--c-slate); }
.r-who .when { margin-left: auto; font-size: 12px; color: var(--c-slate); }
.read-body {
  padding: 18px 20px; font-size: 14px; line-height: 1.7;
  color: var(--c-text-2); text-align: justify;
}
.read-body p { margin: 0 0 13px; }
.att-strip { list-style: none; margin: 0; padding: 14px 20px 18px; display: flex; gap: 10px; flex-wrap: wrap; border-top: 1px solid #f1f3f5; }
.att-file {
  display: flex; align-items: center; gap: 9px; border: 1px solid var(--c-border-2);
  border-radius: 10px; padding: 8px 12px; background: #fafbfc;
  font-size: 12.5px; color: var(--c-text-2); font-weight: 600;
}
.att-file img.att-thumb { width: 34px; height: 34px; border-radius: 7px; object-fit: cover; }
.att-file .ic { width: 26px; height: 26px; border-radius: 7px; background: #fef2f2; color: #dc2626; display: grid; place-items: center; font-size: 12px; }
.att-file .att-name { color: var(--c-text-2); text-decoration: none; word-break: break-all; max-width: 220px; }
.att-file .att-name:hover { color: var(--c-accent); }
.att-file .sz { color: var(--c-slate); font-weight: 500; font-size: 11.5px; }
.att-file .att-dl { width: 24px; height: 24px; border-radius: 7px; background: #f1f3f5; color: var(--c-text-2); display: grid; place-items: center; text-decoration: none; }
.att-file .att-dl:hover { background: #e9ecef; color: var(--c-text); }

/* ---------- compose ---------- */
.mb-compose { display: block; }
.compose-card {
  background: var(--c-surface); border: 1px solid var(--c-border-2);
  border-radius: 16px; box-shadow: 0 1px 2px rgba(15, 23, 42, .06);
  overflow: hidden;
}
.mb-compose .cc-body { padding: 16px; }
.mb-compose .form-group.col-md-12 { margin-left: 0; margin-right: 0; }
.mb-compose .form-group.col-md-12 + .form-group.col-md-12 { margin-top: 12px; }
.mb-compose .form-control {
  border: 1px solid var(--c-border-2); border-radius: 10px;
  font-size: 13.5px; box-shadow: none;
}
.mb-compose .form-control:focus { border-color: var(--c-accent); box-shadow: 0 0 0 3px rgba(13, 148, 136, .12); }
.mb-compose .select2-container { font-size: 13.5px; }
.mb-compose .select2-container--bootstrap .select2-selection {
  height: 40px; border-radius: 10px; border: 1px solid var(--c-border-2);
  box-shadow: none; font-size: 13.5px;
}
.mb-compose .select2-container--bootstrap .select2-selection__rendered { line-height: 26px; color: var(--c-text); padding-left: 12px; }
.mb-compose textarea.textarea_ { min-height: 320px; border-radius: 10px; }
.mb-compose .cc-foot {
  border-top: 1px solid #f1f3f5; background: #fafbfc;
  padding: 13px 16px; display: flex; gap: 8px; align-items: center;
}
.mb-compose .cc-foot .pull-right { float: none !important; display: flex; gap: 8px; margin-left: auto; }
.mb-compose .cc-foot .btn-primary {
  background: var(--c-accent); border-color: var(--c-accent); color: #fff;
  border-radius: 9px; font-weight: 700; box-shadow: 0 1px 2px rgba(13, 148, 136, .35);
}
.mb-compose .cc-foot .btn-primary:hover { background: #0b7c72; border-color: #0b7c72; }
.mb-compose .cc-foot .btn-default { border-radius: 9px; font-weight: 700; background: #f1f3f5; border-color: transparent; color: var(--c-text-2); }
.mb-compose .cc-foot .btn-default:hover { background: #e9ecef; }
.mb-compose .dropzone { border: 1.5px dashed var(--c-border-2); border-radius: 12px; background: #fafbfc; min-height: 110px; }
.mb-compose .dropzone:hover { border-color: var(--c-accent); background: #f0fdfa; }
```

- [ ] **Step 2: Add the conditional link to the shared head**

In `application/views/admin/components/htmlheader.php`, immediately AFTER the line:

```php
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/app.css?v=23" id="maincss">
```

insert:

```php
<?php if (strpos(uri_string(), 'mailbox') !== false) { ?>
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/css/mailbox_modern.css?v=1">
<?php } ?>
```

(This one component renders the `<head>` for both admin and client — `client/_layout_main.php` line 1 loads `admin/components/htmlheader`.)

- [ ] **Step 3: Lint + run test (partial pass expected)**

Run: `php -l application/views/admin/components/htmlheader.php`
Expected: `No syntax errors detected`

Run: `composer test:mailbox`
Expected: still FAIL overall, but the `missing marker: mailbox_modern.css` errors are gone; remaining failures are the Design-A markup markers (`list-card`, `f-item`, `read-card`, `mb-compose`) — implemented in Tasks 3–8.

- [ ] **Step 4: Commit**

```bash
git add assets/css/mailbox_modern.css application/views/admin/components/htmlheader.php
git commit -m "feat: add mailbox modern stylesheet + conditional link"
```

---

### Task 3: Wrapper folder panel (`mailbox.php`)

**Files:**
- Modify: `application/views/admin/mailbox/mailbox.php`
- Regenerate: `application/views/client/mailbox/mailbox.php`

- [ ] **Step 1: Replace the admin wrapper**

Full content of `application/views/admin/mailbox/mailbox.php` (PHP logic — unread var, trash inline count, `message_box()` flashes, `$view` load — preserved verbatim):

```php
<div class="mbflex">
    <div class="folders">
        <div class="sec"><?= lang('all_messages') ?></div>
        <a class="<?php echo ($menu_active == 'inbox') ? 'on f-item' : 'f-item'; ?>"
           href="<?= base_url() ?>admin/mailbox/index/inbox">
            <span class="fi"><i class="fa fa-inbox"></i></span><?= lang('inbox') ?>
            <span class="fc<?php echo (!empty($unread_mail) && $unread_mail > 0) ? ' hot' : ''; ?>"><?php
                echo !empty($unread_mail) ? $unread_mail : 0;
            ?></span>
        </a>
        <a class="<?php echo ($menu_active == 'sent') ? 'on f-item' : 'f-item'; ?>"
           href="<?= base_url() ?>admin/mailbox/index/sent">
            <span class="fi"><i class="fa fa-paper-plane"></i></span><?= lang('sent') ?>
        </a>
        <a class="<?php echo ($menu_active == 'draft') ? 'on f-item' : 'f-item'; ?>"
           href="<?= base_url() ?>admin/mailbox/index/draft">
            <span class="fi"><i class="fa fa-pencil"></i></span>Drafts
        </a>
        <a class="<?php echo ($menu_active == 'favourites') ? 'on f-item' : 'f-item'; ?>"
           href="<?= base_url() ?>admin/mailbox/index/favourites">
            <span class="fi"><i class="fa fa-star"></i></span><?= lang('favourites') ?>
        </a>
        <a class="<?php echo ($menu_active == 'trash') ? 'on f-item' : 'f-item'; ?>"
           href="<?= base_url() ?>admin/mailbox/index/trash">
            <span class="fi"><i class="fa fa-trash-o"></i></span><?= lang('trash') ?>
            <span class="fc"><?php
                $inbox_query = $this->db->where(array('to' => $this->session->userdata('email'), 'deleted' => 'Yes'))->get('tbl_inbox');
                $totat_inbox = $inbox_query->num_rows();
                $sent_query = $this->db->where(array('user_id' => $this->session->userdata('user_id'), 'deleted' => 'Yes'))->get('tbl_sent');
                $totat_sent = $sent_query->num_rows();
                $draft_query = $this->db->where(array('user_id' => $this->session->userdata('user_id'), 'deleted' => 'Yes'))->get('tbl_draft');
                $tatal_draft = $draft_query->num_rows();
                echo $totat_inbox + $totat_sent + $tatal_draft;
            ?></span>
        </a>
    </div>
    <div class="list-col">
        <?php echo message_box('success'); ?>
        <?php echo message_box('error'); ?>
        <?php $this->load->view('admin/mailbox/' . $view) ?>
    </div>
</div>
```

- [ ] **Step 2: Regenerate the client wrapper**

```powershell
$src = 'application\views\admin\mailbox\mailbox.php'
$dst = 'application\views\client\mailbox\mailbox.php'
$c = [System.IO.File]::ReadAllText((Resolve-Path $src))
$c = $c.Replace('admin/mailbox', 'client/mailbox')
[System.IO.File]::WriteAllText((Resolve-Path $dst), $c)
if (Select-String -Path $dst -Pattern 'admin/mailbox' -Quiet) { throw 'role swap failed' }
& C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l $dst
```

Expected: `No syntax errors detected`

- [ ] **Step 3: Lint admin + run test**

Run: `php -l application/views/admin/mailbox/mailbox.php` → no errors.
Run: `composer test:mailbox` → `f-item` marker now present on all folder routes; `list-card` etc. still FAIL (Tasks 4–6).

- [ ] **Step 4: Commit**

```bash
git add application/views/admin/mailbox/mailbox.php application/views/client/mailbox/mailbox.php
git commit -m "feat: restyle mailbox folder panel wrapper"
```

---

### Task 4: Inbox list card

**Files:**
- Modify: `application/views/admin/mailbox/inbox.php`
- Regenerate: `application/views/client/mailbox/inbox.php`

- [ ] **Step 1: Replace the admin inbox view**

Full content of `application/views/admin/mailbox/inbox.php`:

```php
<div class="row">
    <div class="col-md-12">
        <form method="post" action="<?php echo base_url() ?>admin/mailbox/delete_mail/inbox">
            <div class="list-card">
                <div class="list-toolbar">
                    <input type="checkbox" id="parent_present" class="tb-check" title="Select all">
                    <button class="tbtn danger" title="Delete selected"><i class="fa fa-trash-o"></i></button>
                    <a href="#" onClick="history.go(0)" class="tbtn" title="Refresh"><i class="fa fa-refresh"></i></a>
                    <a href="<?php echo base_url() ?>admin/mailbox/index/compose" class="tb-compose"><i class="fa fa-pencil"></i> Compose</a>
                </div>
                <div class="rows">
                    <?php if (!empty($get_inbox_message)): foreach ($get_inbox_message as $v_inbox_msg): ?>
                        <?php
                        $string = (strlen($v_inbox_msg->to) > 50) ? strip_html_tags(mb_substr($v_inbox_msg->to, 0, 50)) . '...' : $v_inbox_msg->to;
                        $subject = (strlen($v_inbox_msg->subject) > 50) ? strip_html_tags(mb_substr($v_inbox_msg->subject, 0, 50)) . '...' : $v_inbox_msg->subject;
                        $preview = (strlen($v_inbox_msg->message_body) > 100) ? strip_html_tags(mb_substr($v_inbox_msg->message_body, 0, 100)) . '...' : $v_inbox_msg->message_body;
                        $uploaded_file = json_decode($v_inbox_msg->attach_file);
                        $parts = preg_split('/[\s@.]+/', trim($string), -1, PREG_SPLIT_NO_EMPTY);
                        $ini = mb_strtoupper(implode('', array_slice($parts, 0, 2)));
                        $av = ['#0d9488', '#2563eb', '#8b5cf6', '#f59e0b', '#dc2626', '#0ea5e9', '#10b981', '#e11d48'][abs(crc32((string) $v_inbox_msg->inbox_id)) % 8];
                        ?>
                        <div class="mrow<?php echo $v_inbox_msg->view_status == 1 ? '' : ' unread'; ?>">
                            <span class="udot"></span>
                            <input class="child_present tb-check" type="checkbox" name="selected_id[]" value="<?php echo $v_inbox_msg->inbox_id; ?>"/>
                            <span class="mb-av" style="background:<?php echo $av; ?>"><?php echo $ini; ?></span>
                            <div class="m-main">
                                <div class="m-top">
                                    <span class="m-from"><?php echo $string; ?></span>
                                    <span class="m-time"><?php echo time_ago($v_inbox_msg->message_time); ?></span>
                                </div>
                                <div class="m-subj"><a href="<?php echo base_url() ?>admin/mailbox/index/read_inbox_mail/<?php echo $v_inbox_msg->inbox_id ?>"><?php echo $subject; ?></a></div>
                                <div class="m-prev"><?php echo $preview; ?><?php if (!empty($uploaded_file)): ?> <i class="fa fa-paperclip"></i><?php endif; ?></div>
                            </div>
                            <a class="mb-star<?php echo $v_inbox_msg->favourites == 1 ? ' on' : ''; ?>"
                               href="<?php echo base_url() ?>admin/mailbox/index/added_favourites/<?php echo $v_inbox_msg->inbox_id ?>/<?php echo $v_inbox_msg->favourites == 1 ? 0 : 1; ?>"
                               title="Favourite"><i class="fa fa-star"></i></a>
                        </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <div class="mb-empty">
                            <div class="big"><i class="fa fa-inbox"></i></div>
                            <b>There is no email to display</b>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="list-status">
                    <span><?php echo !empty($get_inbox_message) ? count($get_inbox_message) : 0; ?> messages &middot; Inbox</span>
                </div>
            </div>
        </form>
    </div>
</div>
```

- [ ] **Step 2: Regenerate the client inbox view**

```powershell
$src = 'application\views\admin\mailbox\inbox.php'
$dst = 'application\views\client\mailbox\inbox.php'
$c = [System.IO.File]::ReadAllText((Resolve-Path $src))
$c = $c.Replace('admin/mailbox', 'client/mailbox')
[System.IO.File]::WriteAllText((Resolve-Path $dst), $c)
if (Select-String -Path $dst -Pattern 'admin/mailbox' -Quiet) { throw 'role swap failed' }
& C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l $dst
```

- [ ] **Step 3: Lint + run test**

Run: `php -l application/views/admin/mailbox/inbox.php` → no errors.
Run: `composer test:mailbox` → both inbox assertions (admin + client) now PASS; sent/draft/favourites/trash/read/compose still FAIL.

- [ ] **Step 4: Commit**

```bash
git add application/views/admin/mailbox/inbox.php application/views/client/mailbox/inbox.php
git commit -m "feat: restyle mailbox inbox list"
```

---

### Task 5: Sent + Drafts list cards

**Files:**
- Modify: `application/views/admin/mailbox/sent.php`
- Modify: `application/views/admin/mailbox/draft.php`
- Regenerate: `application/views/client/mailbox/sent.php`, `application/views/client/mailbox/draft.php`

- [ ] **Step 1: Replace the admin sent view**

Full content of `application/views/admin/mailbox/sent.php`:

```php
<div class="row">
    <div class="col-md-12">
        <form method="post" action="<?php echo base_url() ?>admin/mailbox/delete_mail/sent">
            <div class="list-card">
                <div class="list-toolbar">
                    <input type="checkbox" id="parent_present" class="tb-check" title="Select all">
                    <button class="tbtn danger" title="Delete selected"><i class="fa fa-trash-o"></i></button>
                    <a href="#" onClick="history.go(0)" class="tbtn" title="Refresh"><i class="fa fa-refresh"></i></a>
                    <a href="<?php echo base_url() ?>admin/mailbox/index/compose" class="tb-compose"><i class="fa fa-pencil"></i> Compose</a>
                </div>
                <div class="rows">
                    <?php if (!empty($get_sent_message)): foreach ($get_sent_message as $v_sent_msg): ?>
                        <?php
                        $string = (strlen($v_sent_msg->to) > 50) ? strip_html_tags(mb_substr($v_sent_msg->to, 0, 50)) . '...' : $v_sent_msg->to;
                        $subject = (strlen($v_sent_msg->subject) > 50) ? strip_html_tags(mb_substr($v_sent_msg->subject, 0, 50)) . '...' : $v_sent_msg->subject;
                        $preview = (strlen($v_sent_msg->message_body) > 100) ? strip_html_tags(mb_substr($v_sent_msg->message_body, 0, 100)) . '...' : $v_sent_msg->message_body;
                        $uploaded_file = json_decode($v_sent_msg->attach_file);
                        $parts = preg_split('/[\s@.]+/', trim($string), -1, PREG_SPLIT_NO_EMPTY);
                        $ini = mb_strtoupper(implode('', array_slice($parts, 0, 2)));
                        $av = ['#0d9488', '#2563eb', '#8b5cf6', '#f59e0b', '#dc2626', '#0ea5e9', '#10b981', '#e11d48'][abs(crc32((string) $v_sent_msg->sent_id)) % 8];
                        ?>
                        <div class="mrow">
                            <span class="udot"></span>
                            <input class="child_present tb-check" type="checkbox" name="selected_id[]" value="<?php echo $v_sent_msg->sent_id; ?>"/>
                            <span class="mb-av" style="background:<?php echo $av; ?>"><?php echo $ini; ?></span>
                            <div class="m-main">
                                <div class="m-top">
                                    <span class="m-from">To: <?php echo $string; ?></span>
                                    <span class="m-time"><?php echo time_ago($v_sent_msg->message_time); ?></span>
                                </div>
                                <div class="m-subj"><a href="<?php echo base_url() ?>admin/mailbox/index/read_send_mail/<?php echo $v_sent_msg->sent_id ?>"><?php echo $subject; ?></a></div>
                                <div class="m-prev"><?php echo $preview; ?><?php if (!empty($uploaded_file)): ?> <i class="fa fa-paperclip"></i><?php endif; ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <div class="mb-empty">
                            <div class="big"><i class="fa fa-paper-plane"></i></div>
                            <b>There is no email to display</b>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="list-status">
                    <span><?php echo !empty($get_sent_message) ? count($get_sent_message) : 0; ?> messages &middot; Sent</span>
                </div>
            </div>
        </form>
    </div>
</div>
```

- [ ] **Step 2: Replace the admin draft view**

Full content of `application/views/admin/mailbox/draft.php`:

```php
<div class="row">
    <div class="col-md-12">
        <form method="post" action="<?php echo base_url() ?>admin/mailbox/delete_mail/draft">
            <div class="list-card">
                <div class="list-toolbar">
                    <input type="checkbox" id="parent_present" class="tb-check" title="Select all">
                    <button class="tbtn danger" title="Delete selected"><i class="fa fa-trash-o"></i></button>
                    <a href="#" onClick="history.go(0)" class="tbtn" title="Refresh"><i class="fa fa-refresh"></i></a>
                    <a href="<?php echo base_url() ?>admin/mailbox/index/compose" class="tb-compose"><i class="fa fa-pencil"></i> Compose</a>
                </div>
                <div class="rows">
                    <?php if (!empty($draft_message)): foreach ($draft_message as $v_draft_msg): ?>
                        <?php
                        $email_address = unserialize($v_draft_msg->to);
                        $total_email = count(array($email_address));
                        if ($total_email > 1) {
                            $deduct = "$total_email" - 1;
                            $string = $email_address[0] . ' , (' . $deduct . ')';
                        } else {
                            $string = $email_address[0];
                        }
                        $subject = (strlen($v_draft_msg->subject) > 50) ? strip_html_tags(mb_substr($v_draft_msg->subject, 0, 50)) . '...' : $v_draft_msg->subject;
                        $preview = (strlen($v_draft_msg->message_body) > 100) ? strip_html_tags(mb_substr($v_draft_msg->message_body, 0, 100)) . '...' : $v_draft_msg->message_body;
                        $uploaded_file = json_decode($v_draft_msg->attach_file);
                        $parts = preg_split('/[\s@.]+/', trim((string) $string), -1, PREG_SPLIT_NO_EMPTY);
                        $ini = mb_strtoupper(implode('', array_slice($parts, 0, 2)));
                        $av = ['#0d9488', '#2563eb', '#8b5cf6', '#f59e0b', '#dc2626', '#0ea5e9', '#10b981', '#e11d48'][abs(crc32((string) $v_draft_msg->draft_id)) % 8];
                        ?>
                        <div class="mrow">
                            <span class="udot"></span>
                            <input class="child_present tb-check" type="checkbox" name="selected_id[]" value="<?php echo $v_draft_msg->draft_id; ?>"/>
                            <span class="mb-av" style="background:<?php echo $av; ?>"><?php echo $ini; ?></span>
                            <div class="m-main">
                                <div class="m-top">
                                    <span class="m-from"><?php echo $string; ?></span>
                                    <span class="m-time"><?php echo time_ago($v_draft_msg->message_time); ?></span>
                                </div>
                                <div class="m-subj"><a href="<?php echo base_url() ?>admin/mailbox/index/compose/<?php echo $v_draft_msg->draft_id ?>"><?php echo $subject; ?></a></div>
                                <div class="m-prev"><?php echo $preview; ?><?php if (!empty($uploaded_file)): ?> <i class="fa fa-paperclip"></i><?php endif; ?> <span class="f-tag" style="background:#fffbeb;color:#b45309">Draft</span></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <div class="mb-empty">
                            <div class="big"><i class="fa fa-pencil"></i></div>
                            <b>There is no email to display</b>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="list-status">
                    <span><?php echo !empty($draft_message) ? count($draft_message) : 0; ?> messages &middot; Drafts</span>
                </div>
            </div>
        </form>
    </div>
</div>
```

- [ ] **Step 3: Regenerate both client views**

```powershell
foreach ($f in @('sent.php', 'draft.php')) {
  $src = "application\views\admin\mailbox\$f"
  $dst = "application\views\client\mailbox\$f"
  $c = [System.IO.File]::ReadAllText((Resolve-Path $src))
  $c = $c.Replace('admin/mailbox', 'client/mailbox')
  [System.IO.File]::WriteAllText((Resolve-Path $dst), $c)
  if (Select-String -Path $dst -Pattern 'admin/mailbox' -Quiet) { throw "role swap failed: $f" }
  & C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l $dst
}
```

Expected: two `No syntax errors detected` lines.

- [ ] **Step 4: Lint + run test**

Run: `php -l application/views/admin/mailbox/sent.php` and `php -l application/views/admin/mailbox/draft.php` → no errors.
Run: `composer test:mailbox` → sent + draft assertions PASS (admin + client); favourites/trash/read/compose still FAIL.

- [ ] **Step 5: Commit**

```bash
git add application/views/admin/mailbox/sent.php application/views/admin/mailbox/draft.php application/views/client/mailbox/sent.php application/views/client/mailbox/draft.php
git commit -m "feat: restyle mailbox sent + drafts lists"
```

---

### Task 6: Favourites + Trash (tabs + 3 sublists)

**Files:**
- Modify: `application/views/admin/mailbox/favourites.php`
- Modify: `application/views/admin/mailbox/trash.php`
- Modify: `application/views/admin/mailbox/trash/inbox.php`
- Modify: `application/views/admin/mailbox/trash/sent.php`
- Modify: `application/views/admin/mailbox/trash/draft.php`
- Regenerate: same 5 paths under `application/views/client/mailbox/`

- [ ] **Step 1: Replace the admin favourites view**

Full content of `application/views/admin/mailbox/favourites.php` (same row recipe as Task 4; form action `delete_inbox_mail` kept verbatim; the old preview line's undefined `$v_sent_msg` reference is corrected to `$v_inbox_msg` — see verification doc):

```php
<div class="row">
    <div class="col-md-12">
        <form method="post" action="<?php echo base_url() ?>admin/mailbox/delete_inbox_mail">
            <div class="list-card">
                <div class="list-toolbar">
                    <input type="checkbox" id="parent_present" class="tb-check" title="Select all">
                    <button class="tbtn danger" title="Delete selected"><i class="fa fa-trash-o"></i></button>
                    <a href="#" onClick="history.go(0)" class="tbtn" title="Refresh"><i class="fa fa-refresh"></i></a>
                    <a href="<?php echo base_url() ?>admin/mailbox/index/compose" class="tb-compose"><i class="fa fa-pencil"></i> Compose</a>
                </div>
                <div class="rows">
                    <?php if (!empty($get_inbox_message)): foreach ($get_inbox_message as $v_inbox_msg): ?>
                        <?php
                        $string = (strlen($v_inbox_msg->to) > 50) ? strip_html_tags(mb_substr($v_inbox_msg->to, 0, 50)) . '...' : $v_inbox_msg->to;
                        $subject = (strlen($v_inbox_msg->subject) > 50) ? strip_html_tags(mb_substr($v_inbox_msg->subject, 0, 50)) . '...' : $v_inbox_msg->subject;
                        $preview = (strlen($v_inbox_msg->message_body) > 100) ? strip_html_tags(mb_substr($v_inbox_msg->message_body, 0, 100)) . '...' : $v_inbox_msg->message_body;
                        $uploaded_file = json_decode($v_inbox_msg->attach_file);
                        $parts = preg_split('/[\s@.]+/', trim($string), -1, PREG_SPLIT_NO_EMPTY);
                        $ini = mb_strtoupper(implode('', array_slice($parts, 0, 2)));
                        $av = ['#0d9488', '#2563eb', '#8b5cf6', '#f59e0b', '#dc2626', '#0ea5e9', '#10b981', '#e11d48'][abs(crc32((string) $v_inbox_msg->inbox_id)) % 8];
                        ?>
                        <div class="mrow<?php echo $v_inbox_msg->view_status == 1 ? '' : ' unread'; ?>">
                            <span class="udot"></span>
                            <input class="child_present tb-check" type="checkbox" name="selected_id[]" value="<?php echo $v_inbox_msg->inbox_id; ?>"/>
                            <span class="mb-av" style="background:<?php echo $av; ?>"><?php echo $ini; ?></span>
                            <div class="m-main">
                                <div class="m-top">
                                    <span class="m-from"><?php echo $string; ?></span>
                                    <span class="m-time"><?php echo time_ago($v_inbox_msg->message_time); ?></span>
                                </div>
                                <div class="m-subj"><a href="<?php echo base_url() ?>admin/mailbox/index/read_inbox_mail/<?php echo $v_inbox_msg->inbox_id ?>"><?php echo $subject; ?></a></div>
                                <div class="m-prev"><?php echo $preview; ?><?php if (!empty($uploaded_file)): ?> <i class="fa fa-paperclip"></i><?php endif; ?></div>
                            </div>
                            <a class="mb-star<?php echo $v_inbox_msg->favourites == 1 ? ' on' : ''; ?>"
                               href="<?php echo base_url() ?>admin/mailbox/index/added_favourites/<?php echo $v_inbox_msg->inbox_id ?>/<?php echo $v_inbox_msg->favourites == 1 ? 0 : 1; ?>"
                               title="Favourite"><i class="fa fa-star"></i></a>
                        </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <div class="mb-empty">
                            <div class="big"><i class="fa fa-star"></i></div>
                            <b>There is no email to display</b>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="list-status">
                    <span><?php echo !empty($get_inbox_message) ? count($get_inbox_message) : 0; ?> messages &middot; Favourites</span>
                </div>
            </div>
        </form>
    </div>
</div>
```

- [ ] **Step 2: Replace the admin trash tabs view**

Full content of `application/views/admin/mailbox/trash.php`:

```php
<div class="trash-tabs">
    <a class="ttab<?php echo ($trash_view == 'inbox') ? ' on' : ''; ?>" href="<?= base_url() ?>admin/mailbox/index/trash/inbox"><i class="fa fa-inbox"></i> <?= lang('inbox') ?></a>
    <a class="ttab<?php echo ($trash_view == 'sent') ? ' on' : ''; ?>" href="<?= base_url() ?>admin/mailbox/index/trash/sent"><i class="fa fa-envelope-o"></i> <?= lang('sent') ?></a>
    <a class="ttab<?php echo ($trash_view == 'draft') ? ' on' : ''; ?>" href="<?= base_url() ?>admin/mailbox/index/trash/draft"><i class="fa fa-file-text-o"></i> <?= lang('draft') ?></a>
</div>
<div class="trash-body">
    <?php $this->load->view('admin/mailbox/trash/' . $trash_view) ?>
</div>
```

- [ ] **Step 3: Replace the admin trash/inbox sublist**

Full content of `application/views/admin/mailbox/trash/inbox.php` (restore + permanent-delete controls kept in the time slot; preview uses the correct `$v_inbox_msg` field):

```php
<div class="row">
    <div class="col-md-12">
        <form method="post" action="<?php echo base_url() ?>admin/mailbox/delete_mail/inbox/trash">
            <div class="list-card">
                <div class="list-toolbar">
                    <input type="checkbox" id="parent_present" class="tb-check" title="Select all">
                    <button class="tbtn danger" title="Delete selected"><i class="fa fa-trash-o"></i></button>
                    <a href="#" onClick="history.go(0)" class="tbtn" title="Refresh"><i class="fa fa-refresh"></i></a>
                </div>
                <div class="rows">
                    <?php if (!empty($get_inbox_message)): foreach ($get_inbox_message as $v_inbox_msg): ?>
                        <?php
                        $string = (strlen($v_inbox_msg->to) > 50) ? strip_html_tags(mb_substr($v_inbox_msg->to, 0, 50)) . '...' : $v_inbox_msg->to;
                        $subject = (strlen($v_inbox_msg->subject) > 50) ? strip_html_tags(mb_substr($v_inbox_msg->subject, 0, 50)) . '...' : $v_inbox_msg->subject;
                        $preview = (strlen($v_inbox_msg->message_body) > 100) ? strip_html_tags(mb_substr($v_inbox_msg->message_body, 0, 100)) . '...' : $v_inbox_msg->message_body;
                        $uploaded_file = json_decode($v_inbox_msg->attach_file);
                        $parts = preg_split('/[\s@.]+/', trim($string), -1, PREG_SPLIT_NO_EMPTY);
                        $ini = mb_strtoupper(implode('', array_slice($parts, 0, 2)));
                        $av = ['#0d9488', '#2563eb', '#8b5cf6', '#f59e0b', '#dc2626', '#0ea5e9', '#10b981', '#e11d48'][abs(crc32((string) $v_inbox_msg->inbox_id)) % 8];
                        ?>
                        <div class="mrow<?php echo $v_inbox_msg->view_status == 1 ? '' : ' unread'; ?>">
                            <span class="udot"></span>
                            <input class="child_present tb-check" type="checkbox" name="selected_id[]" value="<?php echo $v_inbox_msg->inbox_id; ?>"/>
                            <span class="mb-av" style="background:<?php echo $av; ?>"><?php echo $ini; ?></span>
                            <div class="m-main">
                                <div class="m-top">
                                    <span class="m-from"><?php echo $string; ?></span>
                                    <span class="m-time"><?php echo time_ago($v_inbox_msg->message_time); ?>
                                        <a class="tbtn" href="<?php echo base_url() ?>admin/mailbox/restore/inbox/<?php echo $v_inbox_msg->inbox_id ?>" title="Restore"><i class="fa fa-retweet"></i></a>
                                        <a class="tbtn danger" href="<?php echo base_url() ?>admin/mailbox/delete_mail/inbox/deleted/<?php echo $v_inbox_msg->inbox_id ?>"
                                           onclick="return confirm('You are about to delete a record. This cannot be undone. Are you sure?');" title="Permanent Delete"><i class="fa fa-trash-o"></i></a>
                                    </span>
                                </div>
                                <div class="m-subj"><a href="<?php echo base_url() ?>admin/mailbox/index/read_inbox_mail/<?php echo $v_inbox_msg->inbox_id ?>"><?php echo $subject; ?></a></div>
                                <div class="m-prev"><?php echo $preview; ?><?php if (!empty($uploaded_file)): ?> <i class="fa fa-paperclip"></i><?php endif; ?></div>
                            </div>
                            <a class="mb-star<?php echo $v_inbox_msg->favourites == 1 ? ' on' : ''; ?>"
                               href="<?php echo base_url() ?>admin/mailbox/index/added_favourites/<?php echo $v_inbox_msg->inbox_id ?>/<?php echo $v_inbox_msg->favourites == 1 ? 0 : 1; ?>"
                               title="Favourite"><i class="fa fa-star"></i></a>
                        </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <div class="mb-empty">
                            <div class="big"><i class="fa fa-trash-o"></i></div>
                            <b>There is no email to display</b>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="list-status">
                    <span><?php echo !empty($get_inbox_message) ? count($get_inbox_message) : 0; ?> deleted messages &middot; Inbox</span>
                </div>
            </div>
        </form>
    </div>
</div>
```

- [ ] **Step 4: Replace the admin trash/sent sublist**

Full content of `application/views/admin/mailbox/trash/sent.php`:

```php
<div class="row">
    <div class="col-md-12">
        <form method="post" action="<?php echo base_url() ?>admin/mailbox/delete_mail/sent/trash">
            <div class="list-card">
                <div class="list-toolbar">
                    <input type="checkbox" id="parent_present" class="tb-check" title="Select all">
                    <button class="tbtn danger" title="Delete selected"><i class="fa fa-trash-o"></i></button>
                    <a href="#" onClick="history.go(0)" class="tbtn" title="Refresh"><i class="fa fa-refresh"></i></a>
                </div>
                <div class="rows">
                    <?php if (!empty($get_sent_message)): foreach ($get_sent_message as $v_sent_msg): ?>
                        <?php
                        $string = (strlen($v_sent_msg->to) > 50) ? strip_html_tags(mb_substr($v_sent_msg->to, 0, 50)) . '...' : $v_sent_msg->to;
                        $subject = (strlen($v_sent_msg->subject) > 50) ? strip_html_tags(mb_substr($v_sent_msg->subject, 0, 50)) . '...' : $v_sent_msg->subject;
                        $preview = (strlen($v_sent_msg->message_body) > 100) ? strip_html_tags(mb_substr($v_sent_msg->message_body, 0, 100)) . '...' : $v_sent_msg->message_body;
                        $uploaded_file = json_decode($v_sent_msg->attach_file);
                        $parts = preg_split('/[\s@.]+/', trim($string), -1, PREG_SPLIT_NO_EMPTY);
                        $ini = mb_strtoupper(implode('', array_slice($parts, 0, 2)));
                        $av = ['#0d9488', '#2563eb', '#8b5cf6', '#f59e0b', '#dc2626', '#0ea5e9', '#10b981', '#e11d48'][abs(crc32((string) $v_sent_msg->sent_id)) % 8];
                        ?>
                        <div class="mrow">
                            <span class="udot"></span>
                            <input class="child_present tb-check" type="checkbox" name="selected_id[]" value="<?php echo $v_sent_msg->sent_id; ?>"/>
                            <span class="mb-av" style="background:<?php echo $av; ?>"><?php echo $ini; ?></span>
                            <div class="m-main">
                                <div class="m-top">
                                    <span class="m-from">To: <?php echo $string; ?></span>
                                    <span class="m-time"><?php echo time_ago($v_sent_msg->message_time); ?>
                                        <a class="tbtn" href="<?php echo base_url() ?>admin/mailbox/restore/sent/<?php echo $v_sent_msg->sent_id ?>" title="Restore"><i class="fa fa-retweet"></i></a>
                                        <a class="tbtn danger" href="<?php echo base_url() ?>admin/mailbox/delete_mail/sent/deleted/<?php echo $v_sent_msg->sent_id ?>"
                                           onclick="return confirm('You are about to delete a record. This cannot be undone. Are you sure?');" title="Permanent Delete"><i class="fa fa-trash-o"></i></a>
                                    </span>
                                </div>
                                <div class="m-subj"><a href="<?php echo base_url() ?>admin/mailbox/index/read_send_mail/<?php echo $v_sent_msg->sent_id ?>"><?php echo $subject; ?></a></div>
                                <div class="m-prev"><?php echo $preview; ?><?php if (!empty($uploaded_file)): ?> <i class="fa fa-paperclip"></i><?php endif; ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <div class="mb-empty">
                            <div class="big"><i class="fa fa-trash-o"></i></div>
                            <b>There is no email to display</b>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="list-status">
                    <span><?php echo !empty($get_sent_message) ? count($get_sent_message) : 0; ?> deleted messages &middot; Sent</span>
                </div>
            </div>
        </form>
    </div>
</div>
```

- [ ] **Step 5: Replace the admin trash/draft sublist**

Full content of `application/views/admin/mailbox/trash/draft.php`:

```php
<div class="row">
    <div class="col-md-12">
        <form method="post" action="<?php echo base_url() ?>admin/mailbox/delete_mail/draft/trash">
            <div class="list-card">
                <div class="list-toolbar">
                    <input type="checkbox" id="parent_present" class="tb-check" title="Select all">
                    <button class="tbtn danger" title="Delete selected"><i class="fa fa-trash-o"></i></button>
                    <a href="#" onClick="history.go(0)" class="tbtn" title="Refresh"><i class="fa fa-refresh"></i></a>
                </div>
                <div class="rows">
                    <?php if (!empty($draft_message)): foreach ($draft_message as $v_draft_msg): ?>
                        <?php
                        $email_address = unserialize($v_draft_msg->to);
                        $total_email = count(array($email_address));
                        if ($total_email > 1) {
                            $deduct = "$total_email" - 1;
                            $string = $email_address[0] . ' , (' . $deduct . ')';
                        } else {
                            $string = $email_address[0];
                        }
                        $subject = (strlen($v_draft_msg->subject) > 50) ? strip_html_tags(mb_substr($v_draft_msg->subject, 0, 50)) . '...' : $v_draft_msg->subject;
                        $preview = (strlen($v_draft_msg->message_body) > 100) ? strip_html_tags(mb_substr($v_draft_msg->message_body, 0, 100)) . '...' : $v_draft_msg->message_body;
                        $uploaded_file = json_decode($v_draft_msg->attach_file);
                        $parts = preg_split('/[\s@.]+/', trim((string) $string), -1, PREG_SPLIT_NO_EMPTY);
                        $ini = mb_strtoupper(implode('', array_slice($parts, 0, 2)));
                        $av = ['#0d9488', '#2563eb', '#8b5cf6', '#f59e0b', '#dc2626', '#0ea5e9', '#10b981', '#e11d48'][abs(crc32((string) $v_draft_msg->draft_id)) % 8];
                        ?>
                        <div class="mrow">
                            <span class="udot"></span>
                            <input class="child_present tb-check" type="checkbox" name="selected_id[]" value="<?php echo $v_draft_msg->draft_id; ?>"/>
                            <span class="mb-av" style="background:<?php echo $av; ?>"><?php echo $ini; ?></span>
                            <div class="m-main">
                                <div class="m-top">
                                    <span class="m-from"><?php echo $string; ?></span>
                                    <span class="m-time"><?php echo time_ago($v_draft_msg->message_time); ?>
                                        <a class="tbtn" href="<?php echo base_url() ?>admin/mailbox/restore/draft/<?php echo $v_draft_msg->draft_id ?>" title="Restore"><i class="fa fa-retweet"></i></a>
                                        <a class="tbtn danger" href="<?php echo base_url() ?>admin/mailbox/delete_mail/draft/deleted/<?php echo $v_draft_msg->draft_id ?>"
                                           onclick="return confirm('You are about to delete a record. This cannot be undone. Are you sure?');" title="Permanent Delete"><i class="fa fa-trash-o"></i></a>
                                    </span>
                                </div>
                                <div class="m-subj"><a href="<?php echo base_url() ?>admin/mailbox/index/compose/<?php echo $v_draft_msg->draft_id ?>"><?php echo $subject; ?></a></div>
                                <div class="m-prev"><?php echo $preview; ?><?php if (!empty($uploaded_file)): ?> <i class="fa fa-paperclip"></i><?php endif; ?> <span class="f-tag" style="background:#fffbeb;color:#b45309">Draft</span></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <div class="mb-empty">
                            <div class="big"><i class="fa fa-trash-o"></i></div>
                            <b>There is no email to display</b>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="list-status">
                    <span><?php echo !empty($draft_message) ? count($draft_message) : 0; ?> deleted messages &middot; Drafts</span>
                </div>
            </div>
        </form>
    </div>
</div>
```

- [ ] **Step 6: Regenerate all 5 client views**

```powershell
$files = @(
  @{ s = 'application\views\admin\mailbox\favourites.php';     d = 'application\views\client\mailbox\favourites.php' },
  @{ s = 'application\views\admin\mailbox\trash.php';          d = 'application\views\client\mailbox\trash.php' },
  @{ s = 'application\views\admin\mailbox\trash\inbox.php';    d = 'application\views\client\mailbox\trash\inbox.php' },
  @{ s = 'application\views\admin\mailbox\trash\sent.php';     d = 'application\views\client\mailbox\trash\sent.php' },
  @{ s = 'application\views\admin\mailbox\trash\draft.php';    d = 'application\views\client\mailbox\trash\draft.php' }
)
foreach ($f in $files) {
  $c = [System.IO.File]::ReadAllText((Resolve-Path $f.s))
  $c = $c.Replace('admin/mailbox', 'client/mailbox')
  [System.IO.File]::WriteAllText((Resolve-Path $f.d), $c)
  if (Select-String -Path $f.d -Pattern 'admin/mailbox' -Quiet) { throw "role swap failed: $($f.s)" }
  & C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l $f.d
}
```

Expected: five `No syntax errors detected` lines.

- [ ] **Step 7: Lint + run test**

Run: `php -l` on the 5 admin files → no errors.
Run: `composer test:mailbox` → favourites + trash assertions PASS; read + compose remain FAIL.

- [ ] **Step 8: Commit**

```bash
git add application/views/admin/mailbox/favourites.php application/views/admin/mailbox/trash.php application/views/admin/mailbox/trash/ application/views/client/mailbox/favourites.php application/views/client/mailbox/trash.php application/views/client/mailbox/trash/
git commit -m "feat: restyle mailbox favourites + trash tabs/sublists"
```

---

### Task 7: Read page (`read_mail.php`)

**Files:**
- Modify: `application/views/admin/mailbox/read_mail.php`
- Regenerate: `application/views/client/mailbox/read_mail.php`

- [ ] **Step 1: Replace the admin read view**

Full content of `application/views/admin/mailbox/read_mail.php` (the old inline `<style>` block is deleted — replaced by `mailbox_modern.css`; reply/delete actions, `$reply` branch, date format, and attachment logic all preserved):

```php
<?php
$read_from = !empty($reply) ? $read_mail->from : $read_mail->to;
$read_label = !empty($reply) ? 'From' : 'To';
$parts = preg_split('/[\s@.]+/', trim((string) $read_from), -1, PREG_SPLIT_NO_EMPTY);
$ini = mb_strtoupper(implode('', array_slice($parts, 0, 2)));
$av = ['#0d9488', '#2563eb', '#8b5cf6', '#f59e0b', '#dc2626', '#0ea5e9', '#10b981', '#e11d48'][abs(crc32((string) $read_mail->inbox_id)) % 8];
?>
<div class="row">
    <div class="col-md-12">
        <div class="read-card">
            <div class="read-head">
                <div class="rh-top">
                    <button class="mb-back" onclick="history.go(-1)"><i class="fa fa-arrow-left"></i> Back</button>
                    <div class="rh-tools">
                        <?php if (!empty($reply)) { ?>
                            <a href="<?= base_url() ?>admin/mailbox/index/compose/<?= $read_mail->inbox_id ?>/reply"
                               class="tbtn" title="Reply"><i class="fa fa-reply"></i></a>
                            <a href="<?= base_url() ?>admin/mailbox/delete_inbox_mail/<?= $read_mail->inbox_id ?>"
                               class="tbtn danger" title="Delete"><i class="fa fa-trash-o"></i></a>
                        <?php } ?>
                    </div>
                </div>
                <h2 class="r-subj"><?php echo $read_mail->subject; ?></h2>
                <div class="r-who">
                    <span class="mb-av" style="background:<?php echo $av; ?>"><?php echo $ini; ?></span>
                    <div>
                        <div class="who"><?php echo $read_label ?>: <?php echo $read_from; ?></div>
                        <div class="em"><?php echo date('d M , Y h:i:A', strtotime($read_mail->message_time)) ?></div>
                    </div>
                    <div class="when"><?php echo date('d M , Y', strtotime($read_mail->message_time)) ?></div>
                </div>
            </div>
            <div class="read-body">
                <p><?php echo $read_mail->message_body; ?></p>
            </div>
            <?php
            $uploaded_file = json_decode($read_mail->attach_file);
            if (!empty($uploaded_file)):
                ?>
                <ul class="att-strip">
                    <?php foreach ($uploaded_file as $v_files): ?>
                        <?php if (!empty($v_files)): ?>
                            <li class="att-file">
                                <?php if (!empty($v_files->is_image) && $v_files->is_image == 1): ?>
                                    <img class="att-thumb" src="<?= base_url() . $v_files->path ?>" alt="Attachment">
                                <?php else: ?>
                                    <span class="ic"><i class="fa fa-file-pdf-o"></i></span>
                                <?php endif; ?>
                                <a target="_blank" href="<?php echo base_url() . $read_mail->attach_file; ?>"
                                   class="att-name"><?= $v_files->fileName ?></a>
                                <span class="sz"><?= $v_files->size ?> <?= lang('kb') ?></span>
                                <a href="<?= base_url() ?>admin/mailbox/download_file/<?= $v_files->fileName ?>"
                                   class="att-dl" title="Download"><i class="fa fa-cloud-download"></i></a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
```

- [ ] **Step 2: Regenerate the client read view**

```powershell
$src = 'application\views\admin\mailbox\read_mail.php'
$dst = 'application\views\client\mailbox\read_mail.php'
$c = [System.IO.File]::ReadAllText((Resolve-Path $src))
$c = $c.Replace('admin/mailbox', 'client/mailbox')
[System.IO.File]::WriteAllText((Resolve-Path $dst), $c)
if (Select-String -Path $dst -Pattern 'admin/mailbox' -Quiet) { throw 'role swap failed' }
& C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l $dst
```

- [ ] **Step 3: Lint + run test**

Run: `php -l application/views/admin/mailbox/read_mail.php` → no errors.
Run: `composer test:mailbox` → read assertions PASS; only compose remains FAIL.

- [ ] **Step 4: Commit**

```bash
git add application/views/admin/mailbox/read_mail.php application/views/client/mailbox/read_mail.php
git commit -m "feat: restyle mailbox read page"
```

---

### Task 8: Compose page (`compose_mail.php`)

**Files:**
- Modify: `application/views/admin/mailbox/compose_mail.php` (4 container-class edits only — all select2/dropzone/JS blocks untouched)
- Regenerate: `application/views/client/mailbox/compose_mail.php`

- [ ] **Step 1: Add the `mb-compose` class to the form tag**

In `application/views/admin/mailbox/compose_mail.php` line 5, change:

```php
?>" method="post" class="form-horizontal form-groups-bordered">
```

to:

```php
?>" method="post" class="form-horizontal form-groups-bordered mb-compose">
```

- [ ] **Step 2: Swap the box container classes**

Same file, change:

```html
        <div class="box box-primary">
            <div class="box-body">
```

to:

```html
        <div class="compose-card">
            <div class="cc-body">
```

And change:

```html
            <div class="box-footer">
```

to:

```html
            <div class="cc-foot">
```

(Leave the comment lines `<!-- /.box-body -->` and `<!-- /. box -->` as-is — comments are inert.)

- [ ] **Step 3: Lint + verify no other edits**

Run: `php -l application/views/admin/mailbox/compose_mail.php` → no errors.
Run: `git diff application/views/admin/mailbox/compose_mail.php` → exactly 4 changed lines (form class, box→compose-card, box-body→cc-body, box-footer→cc-foot). No JS/dropzone/select2 lines changed.

- [ ] **Step 4: Regenerate the client compose view**

```powershell
$src = 'application\views\admin\mailbox\compose_mail.php'
$dst = 'application\views\client\mailbox\compose_mail.php'
$c = [System.IO.File]::ReadAllText((Resolve-Path $src))
$c = $c.Replace('admin/mailbox', 'client/mailbox')
[System.IO.File]::WriteAllText((Resolve-Path $dst), $c)
if (Select-String -Path $dst -Pattern 'admin/mailbox' -Quiet) { throw 'role swap failed' }
if (-not (Select-String -Path $dst -Pattern 'admin/common/upload_file' -Quiet)) { throw 'admin/common URL was wrongly swapped' }
& C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l $dst
```

Expected: `No syntax errors detected` (the second guard confirms `admin/common/...` survived — it must stay `admin/`).

- [ ] **Step 5: Run the full test — expect ALL GREEN**

Run: `composer test:mailbox`
Expected: exit 0 — every assertion PASS (SKIPs allowed only for "no inbox message to open").

- [ ] **Step 6: Commit**

```bash
git add application/views/admin/mailbox/compose_mail.php application/views/client/mailbox/compose_mail.php
git commit -m "feat: restyle mailbox compose page"
```

---

### Task 9: Full verification + record

**Files:**
- Create: `docs/superpowers/2026-10-03-mailbox-redesign-verification.md`

- [ ] **Step 1: Lint every edited PHP file**

Run:

```powershell
& C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l application/views/admin/components/htmlheader.php
Get-ChildItem application\views\admin\mailbox, application\views\client\mailbox -Recurse -Filter *.php | ForEach-Object { & C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l $_.FullName }
```

Expected: every file reports `No syntax errors detected`.

- [ ] **Step 2: Run the full gate suite**

Run:

```powershell
composer smoke; "smoke: $LASTEXITCODE"
composer test:login; "login: $LASTEXITCODE"
composer test:menu; "menu: $LASTEXITCODE"
composer test:mailbox; "mailbox: $LASTEXITCODE"
composer test:theme; "theme: $LASTEXITCODE"
```

Expected: smoke PASS (22/22), login PASS, menu PASS, mailbox PASS (0 failed), theme FAIL with the **pre-existing** baseline (`expected 25 !important, got 26`, `--c-accent` undefined — documented in `docs/superpowers/2026-10-01-woocommerce-redesign-verification.md`; must be byte-identical to that baseline, not worse).

- [ ] **Step 3: Playwright visual pass**

For each row below open the URL, confirm the listed expectations, and check **0 console errors**:

| URL | Expect |
|---|---|
| `http://localhost/complete_crm/admin/mailbox/index/inbox` | folders card (Inbox active, unread badge), list-card rows with avatars/unread dots/stars, Compose button |
| `.../index/sent` | rows show `To:` prefix, no star column |
| `.../index/draft` | Draft tag on rows; subject opens compose |
| `.../index/favourites` | starred rows with amber stars |
| `.../index/trash` | pill tabs (Inbox active), sublist rows with restore/delete buttons |
| `.../index/trash/sent` then `.../trash/draft` | tabs switch, sublist restyles |
| click any subject on inbox | read page: Back button, subject, sender block, body, reply/delete tools |
| `.../index/compose` | compose card: To select2, subject, textarea, dropzone area, Draft/Send/Discard footer |
| `http://localhost/complete_crm/client/mailbox/index/inbox` | same design under client sidebar |
| `http://localhost/complete_crm/admin/settings` | **no** mailbox CSS/markup (leak check) |

Interactions to verify: check-all checkbox toggles row checkboxes; starring reloads into Favourites; folder counts update after opening unread mail.

- [ ] **Step 4: Write the verification record**

Create `docs/superpowers/2026-10-03-mailbox-redesign-verification.md` containing: gates table (commands + exact outputs), Playwright pass checklist with results, the two incidental fixes made during reskin (undefined `$v_sent_msg` preview refs in `favourites.php` and `trash/inbox.php` corrected to `$v_inbox_msg`), and deviations from the demo/spec (compose full-page not modal; storage footer omitted — no data source; read reply footer reduced to existing Reply/Delete actions; trash sublists keep controls but drop the original Compose button which pointed at an odd context — record whatever was actually shipped).

- [ ] **Step 5: Commit**

```bash
git add docs/superpowers/2026-10-03-mailbox-redesign-verification.md
git commit -m "docs: mailbox redesign verification notes"
```

---

## Self-review (plan → spec)

1. **Spec coverage:** §2 all 7 screens × both roles → Tasks 3–8; conditional CSS delivery → Task 2 (single shared `htmlheader` instead of two layouts — same coverage, fewer touch points); §5 design system → Task 2 CSS uses canonical class list; §7 gates → Tasks 1 + 9; §8 deviations (compose full page, real counts, no switcher) → Tasks 8 + 9 record; §3 non-goals respected (no AJAX, no new endpoints, controls kept: `#parent_present`, `.child_present`, all form actions/hrefs).
2. **Placeholder scan:** no TBD/TODO; every code step carries complete file content or exact edit snippets; every run step has command + expected output.
3. **Type consistency:** class names identical across CSS (Task 2), views (Tasks 3–8), and test markers (Task 1): `list-card`, `f-item`, `mrow`, `mb-empty`, `read-card`, `r-subj`, `mb-compose`, `mailbox_modern.css`. Role-swap rule uniformly `admin/mailbox → client/mailbox` (never `admin/` blanket — protects `admin/common/upload_file`).
