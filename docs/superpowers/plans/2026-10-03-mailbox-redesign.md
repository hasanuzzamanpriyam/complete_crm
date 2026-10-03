# Mailbox Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Reskin all 7 mailbox screens (Inbox, Sent, Drafts, Favourites, Trash, Read, Compose) for admin + client to the approved Design A using a new `mailbox_modern.css` and in-place view edits — **no changes to existing controller/model/URL logic, plus one additive row-fetch block in the admin module controller** (amended spec §2, user decision 2026-10-03).

**Architecture (amended 2026-10-03 after live-routing discovery):** `application/third_party/MX/Router.php:110-115` reroutes every `admin/<module>` URL into the module — **admin mailbox screens live in `modules/mailbox/`** (controller + views), while `application/controllers/admin/Mailbox.php` and `application/views/admin/mailbox/*` are unreachable dead code (never edited). Client screens live in `application/controllers/client/Mailbox.php` + `application/views/client/mailbox/*` (no reroute). New `assets/css/mailbox_modern.css` is loaded via a URI-conditional `<link>` in the shared head component (`admin/components/htmlheader.php`, loaded by BOTH `_layout_main` layouts). Admin folder lists are **server-rendered** from a controller-passed `$local_messages` array (single `local_mails.php` for all 5 folders; AJAX `inboxList` retained for remote/Gmail folders); client folder views keep their per-folder server-rendered foreach structure. All PHP logic, form actions, `lang()` calls, and JS hooks (`#parent_present`, `.child_present`, dropzone, select2) stay verbatim.

**Tech Stack:** CodeIgniter 3 PHP views, Bootstrap 3 (reskinned), Font Awesome 4, global `tokens.css`, PHP 8.3, curl-based e2e test + Playwright visual pass.

**Spec:** `docs/superpowers/specs/2026-10-03-mailbox-redesign-design.md`
**Demo reference:** `.superpowers/brainstorm/1374-1790861074/content/mailbox-full.html`

---

## File structure

| Action | File | Responsibility |
|---|---|---|
| Create (done) | `plan/_tools/mailbox-redesign-test.php` | e2e gate (`composer test:mailbox`) — amended in Task 1b |
| Modify (done) | `composer.json` | `test:mailbox` script |
| Create (done) | `assets/css/mailbox_modern.css` | all mailbox component styles |
| Modify (done) | `application/views/admin/components/htmlheader.php` | URI-conditional CSS link (shared by admin + client) |
| Create | `plan/_tools/mailbox-fixtures.sql` | idempotent e2e fixtures (client user/rows + admin inbox row 900001) |
| Modify | `modules/mailbox/controllers/Mailbox.php` | **additive** `$local_messages` row-fetch block in `index()` only |
| Modify | `modules/mailbox/views/mailbox.php` | admin folder-panel wrapper + `$view` slot (col-2/col-10) |
| Modify | `modules/mailbox/views/local_mails.php` | admin server-rendered list card (all 5 folders; DataTables removed) |
| Modify | `modules/mailbox/views/read_mail.php` | admin full-page read card |
| Modify | `modules/mailbox/views/compose_mail.php` | admin compose container-class reskin (JS/dropzone untouched) |
| Modify | `application/views/client/mailbox/mailbox.php` | client folder-panel wrapper |
| Modify | `application/views/client/mailbox/inbox.php` | client inbox list card |
| Modify | `application/views/client/mailbox/sent.php`, `draft.php` | client sent + drafts list cards |
| Modify | `application/views/client/mailbox/favourites.php`, `trash.php`, `trash/{inbox,sent,draft}.php` | client favourites + trash tabs/sublists |
| Modify | `application/views/client/mailbox/read_mail.php` | client full-page read card |
| Modify | `application/views/client/mailbox/compose_mail.php` | client compose container-class reskin |
| Create | `docs/superpowers/2026-10-03-mailbox-redesign-verification.md` | Task 9 record |

**Dead code (never edited):** `application/controllers/admin/Mailbox.php`, `application/views/admin/mailbox/**` (11 files) — loaded only by the unreachable application admin controller. **Untouched module files:** `modules/mailbox/views/inbox.php` (unreachable branch), `mail_box_menu.php` (remote-folder AJAX menu — out of scope), `settings.php`, `send_email.php`, `local_mails` AJAX endpoints (`inboxList`/`mailboxList`) kept for remote folders.

**Canonical class names** (used identically by CSS, views, and test markers):
`.mbflex`, `.folders`, `.f-item`, `.fc`, `.list-card`, `.list-toolbar`, `.tbtn`, `.tb-check`, `.tb-compose`, `.rows`, `.mrow`, `.udot`, `.mb-av`, `.m-main`, `.m-top`, `.m-from`, `.m-time`, `.m-subj`, `.m-prev`, `.f-tag`, `.mb-star`, `.list-status`, `.mb-empty`, `.read-card`, `.read-head`, `.rh-top`, `.mb-back`, `.rh-tools`, `.r-subj`, `.r-who`, `.read-body`, `.att-strip`, `.att-file`, `.mb-compose`, `.compose-card`, `.cc-body`, `.cc-foot`, `.trash-tabs`, `.ttab`.

## Conventions used by every task

- PHP binary: `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe` (call as `php` if on PATH; otherwise substitute).
- **No mirror/regenerate steps (amended):** module admin files and client files are independent lineages — client files are **written directly** from the content blocks in this plan (apply `admin/mailbox` → `client/mailbox` when writing to a client path; `admin/common/...` and `admin/global_controller/...` URLs must stay `admin/`). Guard after every client write:

```powershell
if (Select-String -Path <client-file> -Pattern 'admin/mailbox' -Quiet) { throw 'role swap failed' }
if (-not (Select-String -Path <client-file> -Pattern 'admin/(common|global_controller)/' -Quiet) -and <file-is-compose>) { throw 'shared admin URL was wrongly swapped' }
& C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l <client-file>
```

- Test command: `composer test:mailbox` (exits 0 green, 1 red).
- One commit per task; commit messages as specified.

---

### Task 1: e2e test harness (`test:mailbox`)

> **STATUS: COMPLETE** — commit `6bbcfd05`. Spec review passed (byte-identical); code-quality review approved with one Important follow-up (read-page SKIP) — closed by Task 1b below.

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

### Task 1b: Amend e2e test for live verification (fixtures + two sessions + fixed read IDs)

> Added 2026-10-03 (user-approved "Full live verification"): the original test could not see client pages (role redirect) or admin read pages (AJAX list rows), leaving 2 permanent SKIPs. This task seeds deterministic fixtures, logs in twice, and pins read URLs to fixed fixture IDs so **every assertion executes — zero SKIPs**.

**Files:**
- Modify: `plan/_tools/mailbox-redesign-test.php`
- Create: `plan/_tools/mailbox-fixtures.sql` (content already authored — commit the file as-is)

- [ ] **Step 1: Commit the fixture SQL + apply it**

The full SQL is authored in this repo at `plan/_tools/mailbox-fixtures.sql` (write it exactly as specified there — client user `mailbox_client`, profile, menu grants copied from user 13, admin fixture `inbox_id=900001`, client fixture `inbox_id=900002`, verification SELECT). Apply:

```powershell
Get-Content plan\_tools\mailbox-fixtures.sql -Raw | & C:\laragon\bin\mysql\mariadb-12.2.2-winx64\bin\mysql.exe -u root tic_crm
```

Expected: verification row shows `client_uid=1790, client_menus=12, admin_fixture=1, client_fixture=1` (client_uid may differ on a fresh DB; menu count 12; both fixtures 1).

- [ ] **Step 2: Amend `plan/_tools/mailbox-redesign-test.php`**

Behavioral requirements (edit the current file; keep its structure, helpers, error pattern, reporting):

1. **Two sessions.** Parameterize `request()` with a cookie-jar path (or run two jars). After the existing admin login (`forid`/`Admin@123`, jar A), add a second login POST to `/login` with `user_name=mailbox_client`, `password=ClientMail@123` (jar B). Fatal-exit if jar B gets no `uniquecoder` cookie. Admin requests (folders, read, compose, `/admin/settings` leak) use jar A; **all client requests use jar B**.
2. **Folder loop (section 1).** Markers reordered to `['mailbox_modern.css', 'f-item', 'list-card', 'mrow'|'mb-empty']` — the first-missing-marker is then a per-task delta: after Task 2 it reports `f-item`, after Task 3 it reports `list-card`, after the task that adds rows it PASSES. (The Task 1 order `css, list-card, f-item` hid Task 3's `f-item` behind `list-card`.) Client iterations must hit the server with the client session (fixes the role-redirect false failures).
3. **New check — admin fixture row visible:** `admin inbox shows fixture row` → GET `/admin/mailbox/index/inbox` (admin session), assert markers `['Mailbox redesign fixture', 'read_inbox_mail/900001']`. FAILS until Task 4 (server-rendered list), then PASSES.
4. **Read (section 2) — no SKIP path.** Replace dynamic-ID extraction with fixed fixture IDs: admin → `900001`, client → `900002`. For each: GET `/{role}/mailbox/index/read_inbox_mail/{id}` with the role's session; assert HTTP 200, no PHP error, and markers `['mailbox_modern.css', 'Mailbox redesign fixture', 'read-card', 'r-subj']` (subject marker passes immediately as a routing/fixture canary; `read-card`/`r-subj` FAIL until Task 7). Delete the SKIP branch entirely; update the file-header comment (no SKIPs).
5. **Compose (section 3).** Unchanged (`mailbox_modern.css`, `mb-compose`, `send_mail`) with the correct session per role.
6. **Leak (section 4).** `/admin/settings` via admin session; `/client/dashboard` via **client** session.
7. Keep the summary line format; total assertions becomes **17** (10 folders + 1 fixture-row + 2 read + 2 compose + 2 leak).

- [ ] **Step 3: Apply fixtures + run — expect RED with zero SKIPs**

Run: `composer test:mailbox` → exit 1. Expected shape (regression baseline for Tasks 3–8):

```
FAIL  admin mailbox/inbox has CSS + card markup      -> missing marker: f-item
... (all 10 folder checks FAIL on `missing marker: f-item`)
FAIL  admin inbox shows fixture row                 -> missing marker: Mailbox redesign fixture
FAIL  admin read page has read-card markup          -> missing marker: read-card
FAIL  client read page has read-card markup         -> missing marker: read-card
FAIL  admin compose has mb-compose markup            -> missing marker: mb-compose
FAIL  client compose has mb-compose markup           -> missing marker: mb-compose
PASS  no leak on /admin/settings
PASS  no leak on /client/dashboard
17 assertions — 2 passed, 15 failed, 0 skipped
```

If ANY check reports `HTTP 302`/login page or a PHP error — fix the fixture/session wiring first (do not proceed). If client inbox does not contain `mrow`/fixture data, re-check `plan/_tools/mailbox-fixtures.sql` was applied.

- [ ] **Step 4: Commit**

```bash
git add plan/_tools/mailbox-redesign-test.php plan/_tools/mailbox-fixtures.sql
git commit -m "test: mailbox e2e - two sessions, fixtures, fixed read IDs"
```

---

### Task 2: `mailbox_modern.css` + conditional stylesheet link

> **STATUS: COMPLETE** — commit `b4d5c886`; spec review ✅. Code-quality review found a Critical **plan defect**: the stylesheet uses `--c-border-2`/`--c-slate`/`--c-amber`, which `tokens.css` does not define (undefined `var()` nullifies the declaration — every card border computed to `none`, browser-verified) → fixed in `765b2282` by adding the three tokens to `assets/css/tokens.css` `:root` (file set +1: `tokens.css`) and bumping `tokens.css?v=3` → `?v=4` in htmlheader; `mailbox_modern.css` itself stays byte-identical to the plan. Approved deviation: the `<link>` was inserted **after** the RTL/LTR branch closes (before `$custom_color`), so RTL also loads it. Task-3 quality review subsequently added `.folders .sec` flex + `.fr`/`.sub` rules to `mailbox_modern.css` (commit `5c3c7912`, stylesheet bumped to `?v=2`).

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

### Task 3: Wrapper folder panel (admin module + client)

> **STATUS: COMPLETE** — commit `b6da678e` (both wrappers byte-identical to this plan's content; spec review ✅). Code-quality review: *Yes with fixes* → applied in `5c3c7912`: C1/M1 `.folders .sec` made flex + `.fr`/`.sec.sub` rules added to `mailbox_modern.css`; C2 `#mail_box_menu` restored `nav nav-pills nav-stacked fsub` (the remote-folder AJAX payload depends on Bootstrap pill/`active` styles; `fsub` kept as the Design A hook); htmlheader `mailbox_modern.css?v=1` → `?v=2` cache-bust.

**Files:**
- Modify: `modules/mailbox/views/mailbox.php` (live admin wrapper — col-2 folder panel + col-10 `$view` slot)
- Modify: `application/views/client/mailbox/mailbox.php` (live client wrapper)

- [ ] **Step 1: Replace the admin wrapper (`modules/mailbox/views/mailbox.php`)**

Full content (preserves verbatim: `$smtp_syn_time` sync button, settings link, `$system_mail_count` counts, `#mail_box_menu` AJAX container, `message_box()` flashes, `$rata`/`$view` load, and the whole sync-`<script>` block including `mail_box_menu/<?= $folder ?>` + `fetch_remote_mails` — functional logic byte-identical; only the inline `//` JS comments are dropped):

```php
<div class="mbflex">
    <div class="folders">
        <div class="sec">
            <?= lang('all_messages') ?>
            <div class="fr">
                <button id="syn_bar" title="<?php echo $smtp_syn_time; ?>" class="tbtn" data-toggle="tooltip"><i class="fa fa-refresh"></i></button>
                <a href="<?= base_url() ?>admin/mailbox/settings" class="tbtn" title="Settings"><i class="fa fa-cogs"></i></a>
            </div>
        </div>
        <div class="sec sub"><?= lang('system_mails') ?></div>
        <?php
        $mb_folders = array(
            'inbox'      => array('label' => lang('inbox'), 'icon' => 'fa-inbox'),
            'sent'       => array('label' => lang('sent'), 'icon' => 'fa-envelope-o'),
            'draft'      => array('label' => 'Drafts', 'icon' => 'fa-file-text-o'),
            'favourites' => array('label' => lang('favourites'), 'icon' => 'fa-star'),
            'trash'      => array('label' => lang('trash'), 'icon' => 'fa-trash-o'),
        );
        foreach ($mb_folders as $mb_key => $mb_def): ?>
            <a class="<?php echo ($menu_active == $mb_key) ? 'on f-item' : 'f-item'; ?>"
               href="<?= base_url() ?>admin/mailbox/index/<?php echo $mb_key; ?>">
                <span class="fi"><i class="fa <?php echo $mb_def['icon']; ?>"></i></span><?= $mb_def['label'] ?>
                <span class="fc<?php echo (in_array($mb_key, array('inbox', 'sent', 'draft'), true) && !empty($system_mail_count[$mb_key]) && $system_mail_count[$mb_key] > 0) ? ' hot' : ''; ?>"><?php echo isset($system_mail_count[$mb_key]) ? $system_mail_count[$mb_key] : 0; ?></span>
            </a>
        <?php endforeach; ?>
        <ul id="mail_box_menu" class="nav nav-pills nav-stacked fsub"></ul>
    </div>
    <div class="list-col">
        <?php echo message_box('success'); ?>
        <?php echo message_box('error'); ?>
        <?php $rata['folder'] = $folder;
        $rata['action'] = $action;
        $rata['menu_active'] = $action;
        $this->load->view($view, $rata); ?>
    </div>
</div>
<script>
    <?php
    $mailboxes_last_sync_time = time();
    $this->session->set_userdata('mailboxes_last_sync_time', $mailboxes_last_sync_time);
    if (empty($this->session->userdata('mailboxes_sync_time')) || $mailboxes_last_sync_time > $this->session->userdata('mailboxes_sync_time')) {
    $five_mins_after = time() + (5 * 60);
    $this->session->set_userdata('mailboxes_sync_time', $five_mins_after);
    $mailboxes_sync_time = $this->session->userdata('mailboxes_sync_time');
    ?>
    <?php } ?>
    $(function () {
        'use strict';
        var syn_bar = $("#syn_bar");
        syn_bar.on("click", ajax_req_send);
        ins_data(base_url + "admin/mailbox/mail_box_menu/<?= $folder; ?>");
        function ins_data(url, datastring = '') {
            $.ajax({
                async: false,
                url: url,
                type: 'post',
                data: datastring,
                dataType: "json",
                success: function (data) {
                    $.each(data, function (index, value) {
                        $('#' + index).empty().html(value);
                    });
                }
            });
        }

        function ajax_req_send() {
            syn_bar.css({color: "red"});
            $('#syn_bar i').addClass('fa-spin');
            var send_url = base_url + 'admin/mailbox/fetch_remote_mails';
            $.ajax({
                async: false,
                type: 'POST',
                url: send_url,
                dataType: 'json',
                encode: true,
                success: function (res) {
                    if (res.success == true) {
                        $('#syn_bar i').removeClass('fa-spin');
                        syn_bar.css({color: ""});
                        syn_bar.attr('title') == '';
                        ins_data(base_url + 'admin/mailbox/mail_box_menu/<?= $folder; ?>');
                    } else {
                        alert('There was a problem with AJAX');
                    }
                }
            })
        }
    });
</script>
```

- [ ] **Step 2: Replace the client wrapper**

Write the **original plan wrapper content below** to `application/views/client/mailbox/mailbox.php` with every `admin/mailbox` replaced by `client/mailbox` (client URLs only; the content's inline trash-count queries, `$unread_mail` badge, and `client/mailbox/' . $view` load are preserved verbatim from the current file):

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

(The final load line must read `'client/mailbox/' . $view` after the swap.)

- [ ] **Step 3: Lint both + run test**

Run: `php -l modules/mailbox/views/mailbox.php` and `php -l application/views/client/mailbox/mailbox.php` → no errors.
Run: `composer test:mailbox` → folder checks now fail on `missing marker: list-card` (f-item present on all 10); everything else unchanged.

- [ ] **Step 4: Commit**

```bash
git add modules/mailbox/views/mailbox.php application/views/client/mailbox/mailbox.php
git commit -m "feat: restyle mailbox folder panel wrapper"
```

---

### Task 4: Admin server-rendered list (`local_mails.php` + controller block) + client inbox

> **STATUS: COMPLETE** — commits `c77d09f4` (controller block + both views) + `1b09f42a` (review fixes). Spec review ✅ (controller insert pure +18/0, legacy branch byte-identical to the old file, client swap clean, test `9 passed, 8 failed`). Code-quality review: *Yes with fixes* → M1 sent rows now `unserialize` (data reality: `tbl_inbox.to` is serialized for sent AND draft — raw concat rendered blobs), M2 `.f-tag` rule added + `mailbox_modern.css?v=3`, M3 guarded `json_decode` ×2, M4 `?? ''`, M5 `allowed_classes => false`; the same guards were back-ported into the Task 5/6 plan blocks. M6 (no LIMIT — full-list by design) and M7 (`lang('alert_delete')` undefined → empty confirm — pre-existing) recorded, no action.

**Files:**
- Modify: `modules/mailbox/controllers/Mailbox.php` (**additive block only** — inserted immediately before `$data['subview'] = $this->load->view('mailbox', $data, TRUE);`, currently line 141)
- Modify: `modules/mailbox/views/local_mails.php`
- Modify: `application/views/client/mailbox/inbox.php`

- [ ] **Step 1: Add the row-fetch block to the module controller**

Insert exactly this block immediately **before line 141** (`$data['subview'] = $this->load->view('mailbox', $data, TRUE);`), i.e. after the view-selection if/elseif chain — `$action` (line 78) and `$data` are in scope there, and `$data['local_messages']` reaches the inner view because CI3/MX Loader merges every view's vars into `_ci_cached_vars` and extracts from the full cache (`system/core/Loader.php:867-868`, `application/third_party/MX/Loader.php:324-326`). No existing line is modified or moved:

```php
        // Mailbox redesign (additive): server-render system-folder rows for local_mails.php
        // Filters/order mirror inboxList() verbatim.
        $local_messages = array();
        if (in_array($action, array('inbox', 'sent', 'draft', 'favourites', 'trash'), true)) {
            $mb_where = array('user_id' => $this->session->userdata('user_id'));
            if ($action == 'trash') {
                $mb_where += array('deleted' => 'Yes');
            } elseif ($action == 'favourites') {
                $mb_where += array('favourites' => 1, 'deleted !=' => 'Yes');
            } else {
                $mb_where += array('mail_folder' => $action, 'deleted !=' => 'Yes', 'smtp_host_name' => 'system');
            }
            $this->db->where($mb_where);
            $this->db->order_by('view_status', 'DESC');
            $this->db->order_by('message_time', 'DESC');
            $local_messages = $this->db->get('tbl_inbox')->result();
        }
        $data['local_messages'] = $local_messages;
```

- [ ] **Step 2: Replace `modules/mailbox/views/local_mails.php`**

Full content (preserves verbatim: form `method/post` + action `admin/mailbox/delete_mail/<?= $action ?>`, `#parent_present`, `.child_present` + `name="selected_id[]"` values, star URL `admin/mailbox/index/added_favourites/<id>/<flag>`, read link `admin/mailbox/index/read_inbox_mail/<id>`, draft link `admin/mailbox/index/compose/<id>`, trash restore `admin/mailbox/restore/remote_host/<id>` + permanent delete `admin/mailbox/delete_mail/remote_host/deleted/<id>`, `time_ago()`, attachment `json_decode(attach_file)` flag. **For the five system folders** the `list` global + DataTables table are removed — the guarded bootstrap at `assets/plugins/dataTables/js/dataTables.php:188` (fires only when global `list` is set) then skips; **non-system actions (remote mailbox folders / unknown keys) keep the legacy DataTable block byte-identically**, so `inboxList` keeps serving remote folders — `mailboxList`/`inboxList` remain untouched):

```php
<?php
$mb_label = array(
    'inbox'      => lang('inbox'),
    'sent'       => lang('sent'),
    'draft'      => 'Drafts',
    'favourites' => lang('favourites'),
    'trash'      => lang('trash'),
);
$mb_title = isset($mb_label[$action]) ? $mb_label[$action] : $action;
$mb_rows = isset($local_messages) ? $local_messages : array();
$mb_is_draft = ($action == 'draft');
$mb_is_trash = ($action == 'trash');
$mb_is_sent = ($action == 'sent');
$mb_system = in_array($action, array('inbox', 'sent', 'draft', 'favourites', 'trash'), true);
if (!$mb_system):
    // Non-system action (remote mailbox folder / unknown key): legacy DataTable path,
    // byte-identical old file — `list` global boots inboxList via the dataTables.php:188 guard.
?>
<div class="row">
    <div class="col-md-12">
        <form method="post" action="<?php echo base_url() ?>admin/mailbox/delete_mail/<?= $action; ?>">
            <!-- Main content -->
            <div class="panel panel-custom">
                <div class="panel-heading">
                    <div class="mailbox-controls">

                        <!-- Check all button -->
                        <div class="mail_checkbox mr-sm">
                            <input type="checkbox" id="parent_present">
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-default btn-xs mr-sm"><i class="fa fa-trash-o"></i></button>
                        </div><!-- /.btn-group -->
                        <a href="<?php echo base_url() ?>admin/mailbox/index/compose"
                           class="btn btn-danger btn-xs mr-sm">Compose +</a>
                    </div>
                </div>
                <div class="table-responsive mailbox-messages">
                    <table class="table table-striped DataTables mb-mails" id="DataTables" width="100%">
                        <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th><?= lang('host') ?></th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                        <script type="text/javascript">
                            list = base_url + "admin/mailbox/inboxList/<?= $action; ?>";
                        </script>
                    </table>
                </div>
            </div><!-- /.box-body -->
    </div><!-- /. box -->
    </form>
</div><!-- /.content-wrapper -->
<?php else: ?>
<div class="row">
    <div class="col-md-12">
        <form method="post" action="<?php echo base_url() ?>admin/mailbox/delete_mail/<?= $action ?>">
            <div class="list-card">
                <div class="list-toolbar">
                    <input type="checkbox" id="parent_present" class="tb-check" title="Select all">
                    <button class="tbtn danger" title="Delete selected"><i class="fa fa-trash-o"></i></button>
                    <a href="#" onClick="history.go(0)" class="tbtn" title="Refresh"><i class="fa fa-refresh"></i></a>
                    <a href="<?php echo base_url() ?>admin/mailbox/index/compose" class="tb-compose"><i class="fa fa-pencil"></i> Compose</a>
                </div>
                <div class="rows">
                    <?php if (!empty($mb_rows)): foreach ($mb_rows as $v_inbox_msg): ?>
                        <?php
                        if ($mb_is_draft || $mb_is_sent) {
                            $mb_to = @unserialize($v_inbox_msg->to, array('allowed_classes' => false));
                            if (!is_array($mb_to)) {
                                $mb_to = array((string) $v_inbox_msg->to);
                            }
                            $string = $mb_is_sent ? 'To: ' . ($mb_to[0] ?? '') : ($mb_to[0] ?? '');
                            if (count($mb_to) > 1) {
                                $string .= ' , (' . (count($mb_to) - 1) . ')';
                            }
                        } else {
                            $string = $v_inbox_msg->from;
                        }
                        if (strlen($string) > 50) {
                            $string = strip_html_tags(mb_substr($string, 0, 50)) . '...';
                        }
                        $subject = (strlen($v_inbox_msg->subject) > 50) ? strip_html_tags(mb_substr($v_inbox_msg->subject, 0, 50)) . '...' : $v_inbox_msg->subject;
                        $preview = (strlen($v_inbox_msg->message_body) > 100) ? strip_html_tags(mb_substr($v_inbox_msg->message_body, 0, 100)) . '...' : $v_inbox_msg->message_body;
                        $uploaded_file = $v_inbox_msg->attach_file ? json_decode($v_inbox_msg->attach_file) : null;
                        $parts = preg_split('/[\s@.]+/', trim($string), -1, PREG_SPLIT_NO_EMPTY);
                        $ini = mb_strtoupper(implode('', array_slice($parts, 0, 2)));
                        $av = ['#0d9488', '#2563eb', '#8b5cf6', '#f59e0b', '#dc2626', '#0ea5e9', '#10b981', '#e11d48'][abs(crc32((string) $v_inbox_msg->inbox_id)) % 8];
                        $mb_href = $mb_is_draft
                            ? base_url('admin/mailbox/index/compose/' . $v_inbox_msg->inbox_id)
                            : base_url('admin/mailbox/index/read_inbox_mail/' . $v_inbox_msg->inbox_id);
                        ?>
                        <div class="mrow<?php echo $v_inbox_msg->view_status == 1 ? '' : ' unread'; ?>">
                            <span class="udot"></span>
                            <input class="child_present tb-check" type="checkbox" name="selected_id[]" value="<?php echo $v_inbox_msg->inbox_id; ?>"/>
                            <span class="mb-av" style="background:<?php echo $av; ?>"><?php echo html_escape($ini); ?></span>
                            <div class="m-main">
                                <div class="m-top">
                                    <span class="m-from"><?php echo $string; ?></span>
                                    <span class="m-time"><?php echo time_ago($v_inbox_msg->message_time); ?><?php if ($mb_is_trash) { ?>
                                        <a class="tbtn" href="<?php echo base_url() ?>admin/mailbox/restore/remote_host/<?php echo $v_inbox_msg->inbox_id ?>" title="Restore"><i class="fa fa-retweet"></i></a>
                                        <a class="tbtn danger" href="<?php echo base_url() ?>admin/mailbox/delete_mail/remote_host/deleted/<?php echo $v_inbox_msg->inbox_id ?>"
                                           onclick="return confirm('<?php echo lang('alert_delete') ?>');" title="Permanent Delete"><i class="fa fa-trash-o"></i></a><?php } ?>
                                    </span>
                                </div>
                                <div class="m-subj"><a href="<?php echo $mb_href ?>"><?php echo $subject; ?></a></div>
                                <div class="m-prev"><?php echo $preview; ?><?php if (!empty($uploaded_file)): ?> <i class="fa fa-paperclip"></i><?php endif; ?><?php if ($mb_is_draft): ?> <span class="f-tag" style="background:#fffbeb;color:#b45309">Draft</span><?php endif; ?></div>
                            </div>
                            <a class="mb-star<?php echo $v_inbox_msg->favourites == 1 ? ' on' : ''; ?>"
                               href="<?php echo base_url() ?>admin/mailbox/index/added_favourites/<?php echo $v_inbox_msg->inbox_id ?>/<?php echo $v_inbox_msg->favourites == 1 ? 0 : 1; ?>"
                               title="Favourite"><i class="fa fa-star"></i></a>
                        </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <div class="mb-empty">
                            <div class="big"><i class="fa <?php echo $mb_is_trash ? 'fa-trash-o' : ($mb_is_sent ? 'fa-envelope-o' : ($mb_is_draft ? 'fa-pencil' : ($action == 'favourites' ? 'fa-star' : 'fa-inbox'))); ?>"></i></div>
                            <b>There is no email to display</b>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="list-status">
                    <span><?php echo count($mb_rows); ?> messages &middot; <?php echo $mb_title; ?></span>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
```

Presentation notes (recorded, behavior-neutral): sent rows show `To: ` + the **unserialized** first recipient (data reality: `tbl_inbox.to` is `serialize()`'d for BOTH draft and sent — `modules/mailbox/controllers/Mailbox.php` saves `serialize($all_email)` before the sent/draft branches; legacy showed raw `from`); draft/sent both use `unserialize(…, ['allowed_classes' => false])` with a plain-string fallback + `?? ''` guard; star and unread-dot rules follow the legacy `inboxList()` (all rows keep both controls). No LIMIT by design (full-list render; Design A has no pagination — record as known trade-off).

- [ ] **Step 3: Replace the client inbox view**

Write the content block below to `application/views/client/mailbox/inbox.php` with `admin/mailbox` → `client/mailbox` (client's `$get_inbox_message` loop, star/read URLs, and `delete_mail/inbox` form action preserved — this is the original plan content, retargeted):

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
                        $uploaded_file = $v_inbox_msg->attach_file ? json_decode($v_inbox_msg->attach_file) : null;
                        $parts = preg_split('/[\s@.]+/', trim($string), -1, PREG_SPLIT_NO_EMPTY);
                        $ini = mb_strtoupper(implode('', array_slice($parts, 0, 2)));
                        $av = ['#0d9488', '#2563eb', '#8b5cf6', '#f59e0b', '#dc2626', '#0ea5e9', '#10b981', '#e11d48'][abs(crc32((string) $v_inbox_msg->inbox_id)) % 8];
                        ?>
                        <div class="mrow<?php echo $v_inbox_msg->view_status == 1 ? '' : ' unread'; ?>">
                            <span class="udot"></span>
                            <input class="child_present tb-check" type="checkbox" name="selected_id[]" value="<?php echo $v_inbox_msg->inbox_id; ?>"/>
                            <span class="mb-av" style="background:<?php echo $av; ?>"><?php echo html_escape($ini); ?></span>
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

- [ ] **Step 4: Lint + run test**

Run: `php -l` on the three files → no errors.
Run: `composer test:mailbox` → **admin folder checks ×5 now PASS** (inbox shows fixture row; sent has real rows; draft/favourites/trash show `mb-empty`), `admin inbox shows fixture row` PASSES, and **client inbox passes too** (Step 3's `list-card` + client fixture row); client sent/draft/favourites/trash still fail `list-card` (Tasks 5–6); read ×2 + compose ×2 still fail. Summary line must be `17 assertions — 9 passed, 8 failed, 0 skipped`, exit 1 (9 = 5 admin folders + client inbox + fixture + 2 leaks; 8 = 4 remaining client folders + read ×2 + compose ×2).

- [ ] **Step 5: Commit**

```bash
git add modules/mailbox/controllers/Mailbox.php modules/mailbox/views/local_mails.php application/views/client/mailbox/inbox.php
git commit -m "feat: server-render admin mailbox lists + restyle client inbox"
```

---

### Task 5: Client sent + drafts list cards

> **STATUS: COMPLETE** — commit `621bc070`. Spec review ✅ (both blocks byte-faithful to this plan after URL swap; functional elements preserved vs old views; test `11 passed, 6 failed`). Code-quality review: **Yes** (no Critical/Major). Minors noted, no action: row-level code paths untested by fixtures (only `mb-empty` renders — candidate for Task 9 fixture enrichment), draft `unserialize` non-array offset = pre-existing parity (hardened with `allowed_classes`), raw echoes pre-existing posture, uncommitted plan doc, `.mrow` cursor cosmetic.

> Admin sent/draft lists are already covered by Task 4 — `modules/mailbox/views/local_mails.php` serves all five admin folders. This task is client-only.

**Files:**
- Modify: `application/views/client/mailbox/sent.php`
- Modify: `application/views/client/mailbox/draft.php`

- [ ] **Step 1: Replace the client sent view**

Write the content block below to `application/views/client/mailbox/sent.php` with `admin/mailbox` → `client/mailbox` (client's `$get_sent_message` loop, `sent_id` checkboxes and `read_send_mail` links, and `delete_mail/sent` form action preserved):

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
                        $uploaded_file = $v_sent_msg->attach_file ? json_decode($v_sent_msg->attach_file) : null;
                        $parts = preg_split('/[\s@.]+/', trim($string), -1, PREG_SPLIT_NO_EMPTY);
                        $ini = mb_strtoupper(implode('', array_slice($parts, 0, 2)));
                        $av = ['#0d9488', '#2563eb', '#8b5cf6', '#f59e0b', '#dc2626', '#0ea5e9', '#10b981', '#e11d48'][abs(crc32((string) $v_sent_msg->sent_id)) % 8];
                        ?>
                        <div class="mrow">
                            <span class="udot"></span>
                            <input class="child_present tb-check" type="checkbox" name="selected_id[]" value="<?php echo $v_sent_msg->sent_id; ?>"/>
                            <span class="mb-av" style="background:<?php echo $av; ?>"><?php echo html_escape($ini); ?></span>
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

- [ ] **Step 2: Replace the client draft view**

Write the content block below to `application/views/client/mailbox/draft.php` with `admin/mailbox` → `client/mailbox` (client's `$draft_message` loop, `unserialize(to)` display, `draft_id` checkboxes and `compose/<id>` links, and `delete_mail/draft` form action preserved):

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
                        $email_address = unserialize($v_draft_msg->to, array('allowed_classes' => false));
                        $total_email = count(array($email_address));
                        if ($total_email > 1) {
                            $deduct = "$total_email" - 1;
                            $string = $email_address[0] . ' , (' . $deduct . ')';
                        } else {
                            $string = $email_address[0];
                        }
                        $subject = (strlen($v_draft_msg->subject) > 50) ? strip_html_tags(mb_substr($v_draft_msg->subject, 0, 50)) . '...' : $v_draft_msg->subject;
                        $preview = (strlen($v_draft_msg->message_body) > 100) ? strip_html_tags(mb_substr($v_draft_msg->message_body, 0, 100)) . '...' : $v_draft_msg->message_body;
                        $uploaded_file = $v_draft_msg->attach_file ? json_decode($v_draft_msg->attach_file) : null;
                        $parts = preg_split('/[\s@.]+/', trim((string) $string), -1, PREG_SPLIT_NO_EMPTY);
                        $ini = mb_strtoupper(implode('', array_slice($parts, 0, 2)));
                        $av = ['#0d9488', '#2563eb', '#8b5cf6', '#f59e0b', '#dc2626', '#0ea5e9', '#10b981', '#e11d48'][abs(crc32((string) $v_draft_msg->draft_id)) % 8];
                        ?>
                        <div class="mrow">
                            <span class="udot"></span>
                            <input class="child_present tb-check" type="checkbox" name="selected_id[]" value="<?php echo $v_draft_msg->draft_id; ?>"/>
                            <span class="mb-av" style="background:<?php echo $av; ?>"><?php echo html_escape($ini); ?></span>
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

- [ ] **Step 3: Lint + run test**

Run: `php -l application/views/client/mailbox/sent.php` and `php -l application/views/client/mailbox/draft.php` → no errors.
Run: `composer test:mailbox` → client sent + client draft folder checks now PASS; client favourites/trash still FAIL (`missing marker: list-card`); admin 5 folders + fixture-row unchanged (PASS); read/compose still FAIL.

- [ ] **Step 4: Commit**

```bash
git add application/views/client/mailbox/sent.php application/views/client/mailbox/draft.php
git commit -m "feat: restyle client mailbox sent + drafts lists"
```

---

### Task 6: Client favourites + trash (tabs + 3 sublists)

> **STATUS: COMPLETE** — commit `abb29fab`. Spec review ✅ (all 5 blocks byte-match this plan post-swap; preservation + swap verified; test `13 passed, 4 failed`). Code-quality review: **Yes** (no Critical/Major). Minors recorded: trash/draft `unserialize` offset warning = pre-existing parity (harden with the `is_array` fallback if fixtures ever seed a raw `to` AND the gate fetches `/trash/draft`); favourites bulk-delete no-op = pre-existing controller dead-end (`delete_inbox_mail` reads URL `$id` only — backlog, same in old file); trash/sent gains the Design-A `To:` prefix (main `sent.php` already had it); dead `count(array())` branch = verbatim parity. Gate caveat: fixtures seed inbox rows only → favourites/trash render `mb-empty`, trash sublists never fetched by the gate (Task 9 fixture-enrichment candidate).

> **Design note:** `.trash-body` is a **markup-only wrapper — intentionally unstyled** (spacing comes from `.trash-tabs { margin-bottom }`; the old view used an inline-margin div; the class inventory + Task 2 CSS block omit it by design). `.trash-tabs`/`.ttab` DO have rules (mailbox_modern.css:114-122).

> Admin favourites/trash lists are already covered by Task 4 (`modules/mailbox/views/local_mails.php` serves all five admin folders — including the single-list admin trash, per spec §8 deviation). This task is client-only.

**Files:**
- Modify: `application/views/client/mailbox/favourites.php`
- Modify: `application/views/client/mailbox/trash.php`
- Modify: `application/views/client/mailbox/trash/inbox.php`
- Modify: `application/views/client/mailbox/trash/sent.php`
- Modify: `application/views/client/mailbox/trash/draft.php`

- [ ] **Step 1: Replace the client favourites view**

Write the content block below to `application/views/client/mailbox/favourites.php` with `admin/mailbox` → `client/mailbox`. Loop variable is **`$favourites_mail`** (the client controller passes `favourites_mail`, not `get_inbox_message` — fixes the pre-existing empty-favourites bug). Form action `delete_inbox_mail` kept verbatim:

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
                    <?php if (!empty($favourites_mail)): foreach ($favourites_mail as $v_inbox_msg): ?>
                        <?php
                        $string = (strlen($v_inbox_msg->to) > 50) ? strip_html_tags(mb_substr($v_inbox_msg->to, 0, 50)) . '...' : $v_inbox_msg->to;
                        $subject = (strlen($v_inbox_msg->subject) > 50) ? strip_html_tags(mb_substr($v_inbox_msg->subject, 0, 50)) . '...' : $v_inbox_msg->subject;
                        $preview = (strlen($v_inbox_msg->message_body) > 100) ? strip_html_tags(mb_substr($v_inbox_msg->message_body, 0, 100)) . '...' : $v_inbox_msg->message_body;
                        $uploaded_file = $v_inbox_msg->attach_file ? json_decode($v_inbox_msg->attach_file) : null;
                        $parts = preg_split('/[\s@.]+/', trim($string), -1, PREG_SPLIT_NO_EMPTY);
                        $ini = mb_strtoupper(implode('', array_slice($parts, 0, 2)));
                        $av = ['#0d9488', '#2563eb', '#8b5cf6', '#f59e0b', '#dc2626', '#0ea5e9', '#10b981', '#e11d48'][abs(crc32((string) $v_inbox_msg->inbox_id)) % 8];
                        ?>
                        <div class="mrow<?php echo $v_inbox_msg->view_status == 1 ? '' : ' unread'; ?>">
                            <span class="udot"></span>
                            <input class="child_present tb-check" type="checkbox" name="selected_id[]" value="<?php echo $v_inbox_msg->inbox_id; ?>"/>
                            <span class="mb-av" style="background:<?php echo $av; ?>"><?php echo html_escape($ini); ?></span>
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
                    <span><?php echo !empty($favourites_mail) ? count($favourites_mail) : 0; ?> messages &middot; Favourites</span>
                </div>
            </div>
        </form>
    </div>
</div>
```

- [ ] **Step 2: Replace the client trash tabs view**

Write the content block below to `application/views/client/mailbox/trash.php` with `admin/mailbox` → `client/mailbox` (the `$trash_view` variable and the three `trash/{inbox,sent,draft}` sub-view loads preserved; the swapped load path becomes `client/mailbox/trash/`):

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

- [ ] **Step 3: Replace the client trash/inbox sublist**

Write the content block below to `application/views/client/mailbox/trash/inbox.php` with `admin/mailbox` → `client/mailbox` (client's `$get_inbox_message` loop, `restore/inbox/<id>` + `delete_mail/inbox/deleted/<id>` controls, and `delete_mail/inbox/trash` form action preserved):

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
                        $uploaded_file = $v_inbox_msg->attach_file ? json_decode($v_inbox_msg->attach_file) : null;
                        $parts = preg_split('/[\s@.]+/', trim($string), -1, PREG_SPLIT_NO_EMPTY);
                        $ini = mb_strtoupper(implode('', array_slice($parts, 0, 2)));
                        $av = ['#0d9488', '#2563eb', '#8b5cf6', '#f59e0b', '#dc2626', '#0ea5e9', '#10b981', '#e11d48'][abs(crc32((string) $v_inbox_msg->inbox_id)) % 8];
                        ?>
                        <div class="mrow<?php echo $v_inbox_msg->view_status == 1 ? '' : ' unread'; ?>">
                            <span class="udot"></span>
                            <input class="child_present tb-check" type="checkbox" name="selected_id[]" value="<?php echo $v_inbox_msg->inbox_id; ?>"/>
                            <span class="mb-av" style="background:<?php echo $av; ?>"><?php echo html_escape($ini); ?></span>
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

- [ ] **Step 4: Replace the client trash/sent sublist**

Write the content block below to `application/views/client/mailbox/trash/sent.php` with `admin/mailbox` → `client/mailbox` (client's `$get_sent_message` loop, `sent_id` controls, and `delete_mail/sent/trash` form action preserved):

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
                        $uploaded_file = $v_sent_msg->attach_file ? json_decode($v_sent_msg->attach_file) : null;
                        $parts = preg_split('/[\s@.]+/', trim($string), -1, PREG_SPLIT_NO_EMPTY);
                        $ini = mb_strtoupper(implode('', array_slice($parts, 0, 2)));
                        $av = ['#0d9488', '#2563eb', '#8b5cf6', '#f59e0b', '#dc2626', '#0ea5e9', '#10b981', '#e11d48'][abs(crc32((string) $v_sent_msg->sent_id)) % 8];
                        ?>
                        <div class="mrow">
                            <span class="udot"></span>
                            <input class="child_present tb-check" type="checkbox" name="selected_id[]" value="<?php echo $v_sent_msg->sent_id; ?>"/>
                            <span class="mb-av" style="background:<?php echo $av; ?>"><?php echo html_escape($ini); ?></span>
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

- [ ] **Step 5: Replace the client trash/draft sublist**

Write the content block below to `application/views/client/mailbox/trash/draft.php` with `admin/mailbox` → `client/mailbox` (client's `$draft_message` loop, `unserialize(to)` display, `draft_id` controls, and `delete_mail/draft/trash` form action preserved):

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
                        $email_address = unserialize($v_draft_msg->to, array('allowed_classes' => false));
                        $total_email = count(array($email_address));
                        if ($total_email > 1) {
                            $deduct = "$total_email" - 1;
                            $string = $email_address[0] . ' , (' . $deduct . ')';
                        } else {
                            $string = $email_address[0];
                        }
                        $subject = (strlen($v_draft_msg->subject) > 50) ? strip_html_tags(mb_substr($v_draft_msg->subject, 0, 50)) . '...' : $v_draft_msg->subject;
                        $preview = (strlen($v_draft_msg->message_body) > 100) ? strip_html_tags(mb_substr($v_draft_msg->message_body, 0, 100)) . '...' : $v_draft_msg->message_body;
                        $uploaded_file = $v_draft_msg->attach_file ? json_decode($v_draft_msg->attach_file) : null;
                        $parts = preg_split('/[\s@.]+/', trim((string) $string), -1, PREG_SPLIT_NO_EMPTY);
                        $ini = mb_strtoupper(implode('', array_slice($parts, 0, 2)));
                        $av = ['#0d9488', '#2563eb', '#8b5cf6', '#f59e0b', '#dc2626', '#0ea5e9', '#10b981', '#e11d48'][abs(crc32((string) $v_draft_msg->draft_id)) % 8];
                        ?>
                        <div class="mrow">
                            <span class="udot"></span>
                            <input class="child_present tb-check" type="checkbox" name="selected_id[]" value="<?php echo $v_draft_msg->draft_id; ?>"/>
                            <span class="mb-av" style="background:<?php echo $av; ?>"><?php echo html_escape($ini); ?></span>
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

- [ ] **Step 6: Lint + run test**

Run: `php -l` on the 5 client files → no errors.
Run: `composer test:mailbox` → **all 10 folder checks PASS** (admin 5 from Task 4; client inbox/sent/draft from Tasks 4–5; client favourites/trash from this task); fixture-row + leak PASS; read + compose still FAIL.

- [ ] **Step 7: Commit**

```bash
git add application/views/client/mailbox/favourites.php application/views/client/mailbox/trash.php application/views/client/mailbox/trash/
git commit -m "feat: restyle client mailbox favourites + trash tabs/sublists"
```

---

### Task 7: Read page (`read_mail.php`)

**Files:**
- Modify: `modules/mailbox/views/read_mail.php`
- Modify: `application/views/client/mailbox/read_mail.php`

> Spec rule "keep every existing control" governs both blocks. Client specifics (verified against `application/controllers/client/Mailbox.php` + the old view at `79e889dc^`): the mark-read/unread toggle is omitted — the client controller has **no `change_view` action** (a link to it would fall through to the inbox); reply/replyall/forward AND delete all render only inside `if (!empty($reply))` (inbox reads — the old view's exact guard; `$data['reply'] = 1` is set only by `read_inbox_mail` at controller line 84) because client `compose/<id>/<status>` looks the message up in `tbl_inbox` by `inbox_id`. Delete uses the old view's **soft** path `client/mailbox/delete_inbox_mail/<inbox_id>` (sets `deleted='Yes'` → appears in trash, controller lines 123-130) — the single-id branch of `delete_mail` (controller line 163) is **permanent**, so `delete_mail/<action>/NULL/<id>` must NOT be used from the client read page; sent/draft reads show no delete link (the old view had none — its delete sat inside the same `$reply` guard). replyall/forward are Design-A additions to the old single reply button (identical links to the admin read page); because client `compose_mail.php` has no `$method` handling yet, their compose-mode branch (to/subject prefill per reply|replyall|forward) is added in **Task 8 Step 2b** so the controls behave as labeled. The read page shows a **single identity line** (`From:` in reply mode, `To:` otherwise) per Design A — the old admin view showed both From and To in reply mode (recorded in Task 9). The favourite star renders whenever `inbox_id` is present, computed as `$favs = favourites == 1 ? 0 : 1` with an `isset($favs)` guard (post-review fix — the old `!empty($favs)` logic hid the toggle in every real state; sent/draft reads show no star because `added_favourites` acts on `tbl_inbox` only). All recorded in Task 9.

- [ ] **Step 1: Replace the admin module read view**

Full content of `modules/mailbox/views/read_mail.php` (Design A `read-card`; favourite/view toggles, id detection → `$delete` paths, reply/replyall/forward/delete, cc line, unserialize-To display, `d M , Y h:i:A` date, and the `modules/mailbox/uploads` + `mailbox/download_file` attachment paths all preserved from the current view):

```php
<?php
$read_label = !empty($reply) ? 'From' : 'To';
$read_from = !empty($reply) ? ($read_mail->from ?? '') : '';
if (empty($reply)) {
    $read_to = @unserialize($read_mail->to, array('allowed_classes' => false));
    $read_from = is_array($read_to) ? implode(', ', $read_to) : $read_mail->to;
}
if (!empty($read_mail->inbox_id)) {
    $favs = (!empty($read_mail->favourites) && $read_mail->favourites == 1) ? 0 : 1;
    $fav_icon = $favs == 0 ? 'fa-star' : 'fa-star-o';
    $fav_title = $favs == 0 ? lang('remove_from_favourites') : lang('added_into_favourites');
}
if (!empty($read_mail->view_status)) {
    if ($read_mail->view_status == 1) {
        $views = 2;
        $view_icon = 'fa-envelope';
        $view_title = lang('mark_as_unread');
    } else {
        $view_icon = 'fa-envelope-o';
        $views = 1;
        $view_title = lang('mark_as_read');
    }
}
if (!empty($read_mail->inbox_id)) {
    $id = $read_mail->inbox_id;
    $delete = 'delete_mail/inbox/NULL';
} else if (!empty($read_mail->sent_id)) {
    $id = $read_mail->sent_id;
    $delete = 'delete_mail/sent/NULL';
} else if (!empty($read_mail->draft_id)) {
    $id = $read_mail->draft_id;
    $delete = 'delete_mail/draft/NULL';
}
$parts = preg_split('/[\s@.]+/', trim((string) $read_from), -1, PREG_SPLIT_NO_EMPTY);
$ini = mb_strtoupper(implode('', array_slice($parts, 0, 2)));
$row_key = $read_mail->inbox_id ?? $read_mail->sent_id ?? $read_mail->draft_id ?? '';
$av = ['#0d9488', '#2563eb', '#8b5cf6', '#f59e0b', '#dc2626', '#0ea5e9', '#10b981', '#e11d48'][abs(crc32((string) $row_key)) % 8];
?>
<div class="row">
    <div class="col-md-12">
        <div class="read-card">
            <div class="read-head">
                <div class="rh-top">
                    <button class="mb-back" onclick="history.go(-1)"><i class="fa fa-arrow-left"></i> Back</button>
                    <div class="rh-tools">
                        <?php if (isset($favs)) { ?>
                            <a href="<?= base_url() ?>admin/mailbox/index/added_favourites/<?= $read_mail->inbox_id . '/' . $favs ?>"
                               class="tbtn" title="<?= $fav_title ?>"><i class="fa <?= $fav_icon ?>"></i></a>
                        <?php } ?>
                        <?php if (!empty($views)) { ?>
                            <a href="<?= base_url() ?>admin/mailbox/index/change_view/<?= $read_mail->inbox_id . '/' . $views ?>"
                               class="tbtn" title="<?= $view_title ?>"><i class="fa <?= $view_icon ?>"></i></a>
                        <?php } ?>
                        <a href="<?= base_url() ?>admin/mailbox/index/compose/<?= $id ?>/reply" class="tbtn"
                           title="<?= lang('reply') ?>"><i class="fa fa-reply"></i></a>
                        <a href="<?= base_url() ?>admin/mailbox/index/compose/<?= $id ?>/replyall" class="tbtn"
                           title="<?= lang('reply_all') ?>"><i class="fa fa-reply-all"></i></a>
                        <a href="<?= base_url() ?>admin/mailbox/index/compose/<?= $id ?>/forward" class="tbtn"
                           title="<?= lang('forward') ?>"><i class="fa fa-mail-forward"></i></a>
                        <a href="<?= base_url() ?>admin/mailbox/<?= $delete . '/' . $id ?>" class="tbtn danger"
                           title="<?= lang('delete') ?>"><i class="fa fa-trash-o"></i></a>
                    </div>
                </div>
                <h2 class="r-subj"><?php echo html_escape($read_mail->subject); ?></h2>
                <div class="r-who">
                    <span class="mb-av" style="background:<?php echo $av; ?>"><?php echo html_escape($ini); ?></span>
                    <div>
                        <div class="who"><?php echo $read_label ?>: <?php echo html_escape($read_from); ?></div>
                        <?php if (!empty($read_mail->cc)) { ?>
                            <div class="who">Cc: <?php echo html_escape($read_mail->cc); ?></div>
                        <?php } ?>
                        <div class="em"><?php echo date('d M , Y h:i:A', strtotime($read_mail->message_time)) ?></div>
                    </div>
                    <div class="when"><?php echo date('d M , Y', strtotime($read_mail->message_time)) ?></div>
                </div>
            </div>
            <div class="read-body">
                <p><?php echo $read_mail->message_body; ?></p>
            </div>
            <?php
            $uploaded_file = $read_mail->attach_file ? json_decode($read_mail->attach_file) : null;
            if (!empty($uploaded_file)):
                ?>
                <ul class="att-strip">
                    <?php foreach ($uploaded_file as $v_files): ?>
                        <?php if (!empty($v_files)): ?>
                            <li class="att-file">
                                <?php if (!empty($v_files->is_image) && $v_files->is_image == 1): ?>
                                    <img class="att-thumb"
                                         src="<?= base_url('modules/mailbox/uploads/' . html_escape($v_files->fileName)) ?>"
                                         alt="Attachment">
                                <?php else: ?>
                                    <span class="ic"><i class="fa fa-file-pdf-o"></i></span>
                                <?php endif; ?>
                                <a target="_blank"
                                   href="<?= base_url() ?>mailbox/download_file/<?= html_escape($v_files->fileName) ?>"
                                   class="att-name"><i class="fa fa-paperclip"></i> <?= html_escape($v_files->fileName) ?></a>
                                <span class="sz"><?= $v_files->size ?> <?= lang('kb') ?></span>
                                <a href="<?= base_url() ?>mailbox/download_file/<?= html_escape($v_files->fileName) ?>"
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

- [ ] **Step 2: Replace the client read view**

Full content of `application/views/client/mailbox/read_mail.php` (the old inline `<style>` block and outer `<section class="content">` are dropped — the wrapper provides layout and `mailbox_modern.css` provides styling; client attachment paths `base_url().$v_files->path` + `client/mailbox/download_file/<fileName>` preserved; the attachment name link is pointed at `download_file` like the admin one — the old `base_url().attach_file` href opened the raw JSON and 404'd):

```php
<?php
$read_label = !empty($reply) ? 'From' : 'To';
$read_from = !empty($reply) ? ($read_mail->from ?? '') : '';
if (empty($reply)) {
    $read_to = @unserialize($read_mail->to, array('allowed_classes' => false));
    $read_from = is_array($read_to) ? implode(', ', $read_to) : $read_mail->to;
}
if (!empty($read_mail->inbox_id)) {
    $favs = (!empty($read_mail->favourites) && $read_mail->favourites == 1) ? 0 : 1;
    $fav_icon = $favs == 0 ? 'fa-star' : 'fa-star-o';
    $fav_title = $favs == 0 ? lang('remove_from_favourites') : lang('added_into_favourites');
}
if (!empty($read_mail->inbox_id)) {
    $id = $read_mail->inbox_id;
} else if (!empty($read_mail->sent_id)) {
    $id = $read_mail->sent_id;
} else if (!empty($read_mail->draft_id)) {
    $id = $read_mail->draft_id;
}
$parts = preg_split('/[\s@.]+/', trim((string) $read_from), -1, PREG_SPLIT_NO_EMPTY);
$ini = mb_strtoupper(implode('', array_slice($parts, 0, 2)));
$row_key = $read_mail->inbox_id ?? $read_mail->sent_id ?? $read_mail->draft_id ?? '';
$av = ['#0d9488', '#2563eb', '#8b5cf6', '#f59e0b', '#dc2626', '#0ea5e9', '#10b981', '#e11d48'][abs(crc32((string) $row_key)) % 8];
?>
<div class="row">
    <div class="col-md-12">
        <div class="read-card">
            <div class="read-head">
                <div class="rh-top">
                    <button class="mb-back" onclick="history.go(-1)"><i class="fa fa-arrow-left"></i> Back</button>
                    <div class="rh-tools">
                        <?php if (isset($favs)) { ?>
                            <a href="<?= base_url() ?>client/mailbox/index/added_favourites/<?= $read_mail->inbox_id . '/' . $favs ?>"
                               class="tbtn" title="<?= $fav_title ?>"><i class="fa <?= $fav_icon ?>"></i></a>
                        <?php } ?>
                        <?php if (!empty($reply)) { ?>
                            <a href="<?= base_url() ?>client/mailbox/index/compose/<?= $id ?>/reply" class="tbtn"
                               title="<?= lang('reply') ?>"><i class="fa fa-reply"></i></a>
                            <a href="<?= base_url() ?>client/mailbox/index/compose/<?= $id ?>/replyall" class="tbtn"
                               title="<?= lang('reply_all') ?>"><i class="fa fa-reply-all"></i></a>
                            <a href="<?= base_url() ?>client/mailbox/index/compose/<?= $id ?>/forward" class="tbtn"
                               title="<?= lang('forward') ?>"><i class="fa fa-mail-forward"></i></a>
                            <a href="<?= base_url() ?>client/mailbox/delete_inbox_mail/<?= $read_mail->inbox_id ?>"
                               class="tbtn danger" title="<?= lang('delete') ?>"><i class="fa fa-trash-o"></i></a>
                        <?php } ?>
                    </div>
                </div>
                <h2 class="r-subj"><?php echo html_escape($read_mail->subject); ?></h2>
                <div class="r-who">
                    <span class="mb-av" style="background:<?php echo $av; ?>"><?php echo html_escape($ini); ?></span>
                    <div>
                        <div class="who"><?php echo $read_label ?>: <?php echo html_escape($read_from); ?></div>
                        <?php if (!empty($read_mail->cc)) { ?>
                            <div class="who">Cc: <?php echo html_escape($read_mail->cc); ?></div>
                        <?php } ?>
                        <div class="em"><?php echo date('d M , Y h:i:A', strtotime($read_mail->message_time)) ?></div>
                    </div>
                    <div class="when"><?php echo date('d M , Y', strtotime($read_mail->message_time)) ?></div>
                </div>
            </div>
            <div class="read-body">
                <p><?php echo $read_mail->message_body; ?></p>
            </div>
            <?php
            $uploaded_file = $read_mail->attach_file ? json_decode($read_mail->attach_file) : null;
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
                                <a target="_blank"
                                   href="<?= base_url() ?>client/mailbox/download_file/<?= html_escape($v_files->fileName) ?>"
                                   class="att-name"><i class="fa fa-paperclip"></i> <?= html_escape($v_files->fileName) ?></a>
                                <span class="sz"><?= $v_files->size ?> <?= lang('kb') ?></span>
                                <a href="<?= base_url() ?>client/mailbox/download_file/<?= html_escape($v_files->fileName) ?>"
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

- [ ] **Step 3: Lint + run test**

Run: `php -l` on both files → no errors.
Run: `composer test:mailbox` → both inbox read assertions PASS **and the 2 new sent-read assertions PASS** (section 2b, added with this task: admin `read_send_mail/900001`, client `read_send_mail/900003` against the new `tbl_sent` fixture — exercises the non-reply path incl. the guarded `from` line); only compose remains FAIL. Summary line must be `19 assertions — 17 passed, 2 failed, 0 skipped`, exit 1.

- [ ] **Step 4: Commit**

```bash
git add modules/mailbox/views/read_mail.php application/views/client/mailbox/read_mail.php
git commit -m "feat: restyle mailbox read page (admin module + client)"
```

---

### Task 8: Compose page (`compose_mail.php`)

**Files:**
- Modify: `modules/mailbox/views/compose_mail.php` (Step 1: 4 container-class edits only — all select2/dropzone/JS blocks untouched)
- Modify: `application/views/client/mailbox/compose_mail.php` (Step 2: same 4 class edits; Step 2b: compose-mode branch for the read page's reply/replyall/forward links — PHP/HTML only, no JS/select2/dropzone changes)

- [ ] **Step 1: Admin module compose — 4 class edits**

In `modules/mailbox/views/compose_mail.php`:

1. Line 7 — form tag: `class="form-horizontal form-groups-bordered"` → `class="form-horizontal form-groups-bordered mb-compose"` (the trailing `data-parsley-validate novalidate` attributes stay outside the class).
2. Line 46 — `<div class="box box-primary">` → `<div class="compose-card">`
3. Line 47 — `<div class="box-body">` → `<div class="cc-body">`
4. Line 229 — `<div class="box-footer">` → `<div class="cc-foot">`

(Leave the comment lines `<!-- /.box-body -->` and `<!-- /. box -->` as-is — comments are inert.)

- [ ] **Step 2: Client compose — same 4 class edits**

In `application/views/client/mailbox/compose_mail.php`:

1. Line 5 — form tag: `class="form-horizontal form-groups-bordered"` → `class="form-horizontal form-groups-bordered mb-compose"` (this file has no parsley attributes on the form).
2. Line 8 — `<div class="box box-primary">` → `<div class="compose-card">`
3. Line 9 — `<div class="box-body">` → `<div class="cc-body">`
4. Line 215 — `<div class="box-footer">` → `<div class="cc-foot">`

Do NOT touch the `admin/common/upload_file` and `admin/common/validate_project_file` URLs (client lines 141/157) — they must stay `admin/`.

- [ ] **Step 2b: Client compose — reply/replyall/forward mode branch**

Task 7's client read page links `compose/<id>/reply|replyall|forward`; the controller loads `$inbox_info` whenever `$status` is non-empty (client `Mailbox.php:105-106`), but the view has no `$method` handling — today all three links behave like plain reply. Admin module compose reads `$method = $this->uri->segment(6)` at its line 13; the segment position is identical for client URLs (`client/mailbox/index/compose/<id>/<method>` → segment 6 = method). Add the branch (mirror of the module's lines 9-43, adapted: no `$cc` field exists client-side; `!empty($inbox_info)` replaces `$action_type == 'inbox'`):

1. Insert between the `<!-- Content Header (Page header) -->` comment and `<div class="col-md-12">`:

```php
    <?php
    $method = $this->uri->segment(6);
    $subject = '';
    $body = '';
    $all_to = array();
    if (!empty($inbox_info)) {
        if ($method == 'reply') {
            $subject = 'RE: ' . $inbox_info->subject;
            $all_to[] = $inbox_info->from;
        } else if ($method == 'replyall') {
            $subject = 'RE: ' . $inbox_info->subject;
            $all_to[] = $inbox_info->from;
            $to_raw = @unserialize($inbox_info->to, array('allowed_classes' => false));
            if (is_array($to_raw)) {
                foreach ($to_raw as $v_to) {
                    $all_to[] = $v_to;
                }
            } else if (!empty($inbox_info->to)) {
                $all_to[] = $inbox_info->to;
            }
        } else {
            $subject = 'FW: ' . $inbox_info->subject;
            $body = $inbox_info->message_body;
        }
    }
    ?>
```

2. Replace the single preselected-from option (current lines 44-47):

```php
                        if (!empty($inbox_info)) {
                            ?>
                            <option value="<?= $inbox_info->from ?>" selected=""><?= $inbox_info->from ?></option>
                        <?php } ?>
```

with a loop over `$all_to` (immediately after the existing draft-options loop, still inside the `<select>`):

```php
                        if (!empty($all_to)) {
                            foreach ($all_to as $v_email) {
                                ?>
                                <option value="<?= html_escape($v_email) ?>" selected=""><?= html_escape($v_email) ?></option>
                            <?php }
                        } ?>
```

3. Draft-attach decode (current lines 93-96) gains an inbox fallback so forward carries attachments:

```php
                    <?php
                    if (!empty($get_draft_info->attach_file)) {
                        $uploaded_file = json_decode($get_draft_info->attach_file);
                    } else if (!empty($inbox_info->attach_file)) {
                        $uploaded_file = json_decode($inbox_info->attach_file);
                    }
                    if (!empty($uploaded_file)) {
```

The existing hidden `path[]/fileName[]/fullPath[]/size[]/is_image[]` inputs (view lines 114-123) are consumed by client `send_mail` (controller lines 242-258), and the `.existing_image` remove handler (view line 129) already covers them — no JS changes.

4. Subject input gains an else branch (draft prefill stays first):

```php
                    <input class="form-control" value="<?php
                    if (!empty($get_draft_info->subject)) {
                        echo $get_draft_info->subject;
                    } else {
                        echo html_escape($subject);
                    }
                    ?>" type="text" required="" name="subject" placeholder="Subject:"/>
```

5. Body textarea gains an else branch (draft prefill stays first):

```php
                              style="height: 350px"><?php
                        if (!empty($get_draft_info->message_body)) {
                            echo $get_draft_info->message_body;
                        } else {
                            echo $body;
                        }
                        ?></textarea>
```

Reply keeps the old behavior (preselect original sender); replyall adds the original `to` recipient(s); forward preselects nobody, prefixes `FW: `, carries body + attachments. No cc field exists client-side (module-only) — recorded in Task 9.

- [ ] **Step 3: Lint + verify no other edits**

Run: `php -l` on both files → no errors.
Run: `git diff modules/mailbox/views/compose_mail.php application/views/client/mailbox/compose_mail.php` → admin file: exactly 4 changed lines; client file: the 4 class edits + Step 2b's inserted branch/options/subject/body/attach lines. No JS/dropzone/select2 lines changed in either file.

- [ ] **Step 4: Run the full test — expect ALL GREEN**

Run: `composer test:mailbox`
Expected: exit 0 — all 19 assertions PASS (0 failed, 0 skipped).

- [ ] **Step 5: Commit**

```bash
git add modules/mailbox/views/compose_mail.php application/views/client/mailbox/compose_mail.php
git commit -m "feat: restyle mailbox compose page (admin module + client)"
```

---

### Task 9: Full verification + record

**Files:**
- Create: `docs/superpowers/2026-10-03-mailbox-redesign-verification.md`

- [ ] **Step 1: Lint every edited PHP file**

Run:

```powershell
& C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l application\views\admin\components\htmlheader.php
& C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l modules\mailbox\controllers\Mailbox.php
Get-ChildItem modules\mailbox\views, application\views\client\mailbox -Recurse -Filter *.php | ForEach-Object { & C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l $_.FullName }
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

Expected: smoke PASS (22/22), login PASS, menu PASS, mailbox PASS (19/19 — 0 failed, 0 skipped), theme FAIL with the **pre-existing** baseline (`expected 25 !important, got 26`, `--c-accent` undefined — documented in `docs/superpowers/2026-10-01-woocommerce-redesign-verification.md`; must be byte-identical to that baseline, not worse).

- [ ] **Step 3: Playwright visual pass**

For each row below open the URL, confirm the listed expectations, and check **0 console errors**:

| URL | Expect |
|---|---|
| `http://localhost/complete_crm/admin/mailbox/index/inbox` | Design A folder panel (Inbox active) + list-card rows with avatars/unread dots/stars + Compose; **fixture row `Mailbox redesign fixture` visible** |
| `.../index/sent` | rows show `To:` prefix, star shown for all rows (or `mb-empty` if none) |
| `.../index/draft` | Draft tag on rows; subject opens compose |
| `.../index/favourites` | starred rows with amber stars (or `mb-empty`) |
| `.../index/trash` | **single list** (admin trash has no tabs — spec §8 deviation) with restore + permanent-delete buttons per row |
| click any subject on inbox | read page: Back, subject, sender/avatar block, cc (if any), body, attachment strip, favourite/view/reply/replyall/forward/delete tools |
| `.../index/compose` | compose card: To select2, subject, editor, dropzone area, Draft/Send/Discard footer (`mb-compose`, `compose-card`) |
| `http://localhost/complete_crm/client/mailbox/index/inbox` | same design under the client sidebar |
| `http://localhost/complete_crm/client/mailbox/index/trash` | pill tabs (Inbox active) + sublist rows with restore/delete buttons; tabs switch to sent/draft |
| click any client subject | read page with Back/subject/body/attachments; favourite + reply/replyall/forward + delete tools (inbox reads) |
| `http://localhost/complete_crm/admin/settings` | **no** mailbox CSS/markup (leak check) |

Interactions to verify: check-all checkbox toggles row checkboxes; starring reloads into Favourites; admin trash restore + permanent-delete work; client trash tabs switch sublists; **once on inbox, check the computed `border-style` of `.list-card` is `solid`** (canary for the Task 2 token defect — the marker test cannot see computed styles).

- [ ] **Step 4: Write the verification record**

Create `docs/superpowers/2026-10-03-mailbox-redesign-verification.md` containing:

- gates table (commands + exact outputs) and the Playwright checklist with results;
- **fixtures note:** `plan/_tools/mailbox-fixtures.sql` (client user `mailbox_client` uid 1790 + 12 menu grants + admin fixture row 900001 + client fixture row 900002 + client **sent** fixture row 900003) and how to reapply it;
- **architecture finding:** the `MX/Router.php` reroute means every `admin/mailbox/*` URL executes `modules/mailbox/controllers/Mailbox.php` + `modules/mailbox/views/*`; `application/controllers/admin/Mailbox.php` + `application/views/admin/mailbox/*` are dead code and were deliberately left untouched;
- **server-render deviation:** additive `$local_messages` block in the module controller + rows rendered in `local_mails.php`; DataTables AJAX dropped for the five system folders (endpoints `inboxList`/`mailboxList` kept for remote folders);
- **admin trash = single list** (client trash keeps the tabs + 3 sublists);
- **incidental fixes:** client favourites view now loops `$favourites_mail` (was `$get_inbox_message` — pre-existing empty-list bug); client read attachment name link now points at `download_file` (old `base_url().attach_file` href opened the raw JSON and 404'd); client read drops its inline `<style>` + outer `<section>` (covered by the wrapper + `mailbox_modern.css`); admin sent rows now show a `To:` prefix (demo parity — legacy showed raw `from`);
- **spec deviations actually shipped:** client read omits the mark-read/unread toggle (no `change_view` action exists in the client controller); client reply/replyall/forward tools render only on inbox reads (`$reply`) because client `compose/<id>/<status>` resolves messages from `tbl_inbox` only; client delete renders only on inbox reads and uses the **soft** `delete_inbox_mail` path (the single-id `delete_mail` branch is permanent — old view parity); the read page shows a single identity line (`From:` in reply mode / `To:` otherwise) where the old admin view showed From and To in reply mode; sent/draft reads show no delete/favourite tools (old view had none that worked); replyall/forward got a client compose-mode branch in Task 8 Step 2b (subject/to/body/attachment prefill; no cc field exists client-side). Record whatever else the visual pass turns up.
- **post-review hardening shipped with Task 7:** avatar initials escaped via `html_escape($ini)` in all 10 redesign views (stored-XSS fix — `from` is attacker-controlled); client read `from` access guarded (`!empty($reply) ? ($read_mail->from ?? '') : ''` — `tbl_sent`/`tbl_draft` have no `from` column); favourite-star toggle fixed to render (old `!empty($favs)` logic hid it in every real state) and inbox-guarded; gate grew to 19 assertions with the 2 sent-read fetches (client `tbl_sent` fixture 900003) so the `Undefined property` error-scan covers the sent/draft read path.

- [ ] **Step 5: Commit**

```bash
git add docs/superpowers/2026-10-03-mailbox-redesign-verification.md
git commit -m "docs: mailbox redesign verification notes"
```

---

## Self-review (plan → spec)

1. **Spec coverage:** §2 all 7 screens × both roles → Tasks 3–8 (admin = `modules/mailbox/views/*`, client = `application/views/client/mailbox/*`); §6.4 server-render → Task 4's additive controller block + `local_mails.php`; conditional CSS delivery → Task 2 (single shared `htmlheader` instead of two layouts — same coverage, fewer touch points); §5 design system → Task 2 CSS uses canonical class list; §7 gates → Tasks 1 + 1b + 9; §8 deviations (compose full page, real counts, no switcher, module routing, server-render, admin trash single-list) → Tasks 4 + 9 record; §3 non-goals respected (no AJAX/modal compose, no new endpoints, DataTables endpoints kept, controls kept: `#parent_present`, `.child_present`, all form actions/hrefs).
2. **Placeholder scan:** no TBD/TODO; every code step carries complete file content or exact edit snippets; every run step has command + expected output.
3. **Type consistency:** class names identical across CSS (Task 2), views (Tasks 3–8), and test markers (Task 1/1b): `list-card`, `f-item`, `mrow`, `mb-empty`, `read-card`, `r-subj`, `mb-compose`, `mailbox_modern.css`. Role-swap rule uniformly `admin/mailbox → client/mailbox` (never `admin/` blanket — protects `admin/common/upload_file`).
4. **Routing correctness:** no `application/views/admin/mailbox/*` file is ever edited (dead code — MX Router reroutes to the module); no task "regenerates" a client file from an admin file — client content is written directly from its own authored block; the `admin/mailbox → client/mailbox` swap appears only as an instruction inside a block whose links are `admin/mailbox` by construction.
5. **Delta chain:** Task 1b's folder marker order (`css → f-item → list-card → rows`) makes each task's contribution observable: RED baseline as measured at Task 1b was 17 = 2 pass/15 fail → Task 3 flips failures to `list-card` → Tasks 4–6 flip folder checks green (13/4 at Task 6) → Task 7 read green **and adds section 2b (2 sent-read assertions + `tbl_sent` fixture 900003) for a 19-assertion gate: 17 passed/2 failed** → Task 8 flips the last 2 (compose) → all 19 green.
6. **Control preservation (§6.3):** admin keeps every control of the current module views (list toolbar, star, restore/permanent-delete, favourite/view/reply/replyall/forward/delete, form actions, `selected_id[]`); client keeps its existing ones (reply, delete, favourite, attachments). The single omission is the client mark-read/unread toggle (no `change_view` action in the client controller) — recorded in Task 9, not silently dropped.
