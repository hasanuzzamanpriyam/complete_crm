# WooCommerce Redesign — Implementation Plan

**Spec:** `docs/superpowers/specs/2026-10-01-woocommerce-redesign-implementation-design.md` (approved)
**Demo:** `_design/woocommerce-full.html` (committed `86019a0d`, served at `http://localhost:8788/woocommerce-full.html`)
**Branch:** `sakib` · **Date:** 2026-10-01 · **Method:** TDD (red test first), one commit per task.

## Goal

Reskin the WooCommerce section (4 list pages + order detail + 4 modals) to the approved
demo design while keeping the existing server-side DataTables pipeline (Approach A):
no new endpoints, no client-side data source, KPIs server-rendered, `fire_datatable()`
extended with small backward-compatible options.

## Environment & commands

| Item | Value |
|---|---|
| PHP | `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe` |
| MySQL | `C:\laragon\bin\mysql\mariadb-12.2.2-winx64\bin\mysql.exe -u root tic_crm` |
| Base URL | `http://localhost/complete_crm` (login `forid` / `Admin@123`) |
| Gates | `composer smoke` · `composer test:menu` · `composer test:login` · `composer test:theme` |
| New gate | `composer test:woo` (Task 1) |
| Shell | PowerShell 5.1 — no `&&`, no `php -r`; use `;` and script files |

## Verified facts this plan is built on

- `tokens.css` is loaded globally (`htmlheader.php:37`, `?v=3`). Demo `:root` vars that
  tokens lacks: `--c-border-2 --c-slate --c-mint --c-cyan --c-amber --c-purple`; differs:
  `--r-md` (8→10), `--r-lg` (12→14), `--fs-sm` (12→13), `--fs-xs` (11→12), `--sh-1`.
  ⇒ re-declare **scoped** (`.woo-modern, #myModal_lg`), never touch `:root` (spec L25).
- `dataTables.buttons.min.js` auto-initializes Buttons on `init.dt` whenever the
  `buttons` option is set — **regardless of `dom`** — and the default button row is left
  detached (never appended). ⇒ spec L112 (`dom` without `B`, buttons still functional)
  is safe; woo pages use `dom:'lrtip'` (drops `B` **and** `f`); no CSS hiding needed.
  Button array in `fire_datatable`: `[0 print, 1 print-selected, 2 excel, 3 csv, 4 pdf,
  5 custom-bulk-button]`.
- `make_datatables($where = null, $where_in = null)` / `render_table($data, $where = null, ...)`
  (`admin_helper.php:2859/2878`) — `$where` is AND-ed after the search group ⇒ pill filters
  work with zero endpoint changes beyond reading `$_POST['woo_status']`/`woo_store`.
- `Datatables` model joins are LEFT, via parallel `join_table[]`/`join_where[]`;
  `column_search` builds `LIKE` groups reading `$_POST['search']['value']` directly;
  `get_all_data` counts **without** joins (recordsTotal), `get_filtered_data` counts with
  them (recordsFiltered).
- **Live-probed defects (red until fixed):**
  - D8 fan-out: orderslist customer join without store condition ⇒ `recordsFiltered 1913`
    vs `recordsTotal 1496` (duplicated rows). Fix join_where with
    `AND tbl_woocommerce_customers.store_id=tbl_woocommerce_orders.store_id` (SQL-verified
    → exactly 1496; 0 orders have NULL store_id).
  - D9 orders global search ⇒ `Error 1052: Column 'phone' in WHERE is ambiguous` (page
    dead). Fix: qualify every `column_search`/`column_order` entry.
  - D10 stores search ⇒ phantom `assignees` column in `column_search` (SQL error).
  - D1 customers search ⇒ `phone_number` (real column is `phone`).
  - D2 orders thead 8 vs 7 cells · D3 customers thead 7 vs 6 + dead `can_action` ·
    D4 list URL prefixes (`woocommerce/productlist` missing `admin/`) ·
    D5 `lang('pending')`/`lang('draft')` missing (blank modal options), `lang('failed')='failed'` ·
    D6 hardcoded toasts in stores JS · D7 `delete_stores` passes raw `'no_record_found'`,
    `edit_products` action has unquoted stray token + `lang('client')` label.
- CSRF is off (`csrf_protection = FALSE`) ⇒ tests need no token. DataTables POSTs must
  carry `X-Requested-With: XMLHttpRequest` (without it the endpoint redirects to
  `/admin/dashboard` — probed).
- Schema: orders owns `address`, `phone`, `order_number`, `store_id`; customers has
  `phone` (NOT `phone_number`), NO `address`; `phone` exists in **both** orders and
  customers ⇒ qualify orders search columns. products: `picture/category/sku/permalink/sales`.
  Stores PK = `store_id`. `tbl_woocommerce_summary` cols `customers|orders|products` hold
  JSON arrays `[{slug,name,total}]`.
- KPI values (DB-verified): orders 1496 / `$21,100.65` (SUM completed) / 11 / 591 ·
  products 741 / 728 / 7 / 826 · customers 2133 / paying 15 / stores 3 / orders 1496 ·
  stores 3 / 3039 / 776 / 2961. Spec L147 asserts `1,496` · `2,133` · `3,039`.
- `display_money($v, $currency=false, $decimal=2)` returns bare number when `$currency`
  is false — revenue KPI renders `'$' . number_format($rev, 2)` (demo parity; SUM mixes
  currencies — documented limitation).
- Global lang has `print`, `showing`, `preview` (main_lang); `of` does **not** — add to
  module lang.
- View ready-callbacks register **before** the footer's ready (document order), so views
  may set `list` + `woo_opts` inside `$(function(){...})` and the footer will see them.
- `order_number` values have no `#` prefix (probed `15983`) ⇒ cell adds it.

## Decisions locked (supersede earlier working notes)

1. **CSS loading = both:** each list view + order view emits its own
   `<link ... assets/css/woocommerce_modern.css?v=1>` (spec L113) **and**
   `_layout_modal_lg.php` conditionally links it when `uri_string()` contains
   `woocommerce` (so remotely-loaded modal pages get the styles too). Modal views emit no
   link.
2. **Toolbar (6 icons + New store):** `woo-xls` `fa-file-excel-o`, `woo-csv` `fa-download`,
   `woo-pdf` `fa-file-pdf-o`, `woo-print` `fa-print`, `woo-sel` `fa-files-o` (print-selected),
   `woo-del` `fa-trash-o` (bulk delete) + `.btn-new` New store (stores page; superset of
   demo's 3 icons per user decision #3). All wire to hidden native Buttons via
   `api.button(n).node().click()` — **native click**, because app.js's bulk handler reads
   the global `event`/`window.event`.
3. **`fire_datatable` opts (Task 2):** `fire_datatable(req_url, ttable = null, opts = {})`
   — `opts.ajaxData(d)` inside ajax `data`, `$.extend(dtConfig, opts.config || {})`,
   `opts.onInit(table.api())` after init. Footer: guard with
   `typeof list !== 'undefined' && list` and pass
   `(typeof woo_opts !== 'undefined' && woo_opts) ? woo_opts : {}` — non-woo path stays
   bit-identical and the existing `ReferenceError` on list-less pages goes away.
   `retrieve:true` makes any double-init harmless.
4. **Cell helpers (Task 3):** `woo_pill`, `woo_status_label` (uses `lang()` keys
   `pending/processing/on_hold/completed/cancelled/refunded/failed/publish/draft/private`,
   fallback `ucfirst`; on-hold→'On Hold'), `woo_store_tag`, `woo_initials`,
   `woo_product_sub` (sku + `json_decode(category)` names, `·`, ≤3 parts),
   `woo_summary_rows/blob/paying/totals`, `woo_store_synced` (static row cache).
5. **Scope, not globals:** all component CSS is prefixed `.woo-modern `; modal rules are
   under `#myModal_lg`; demo-only vars re-declared in one scoped block; pill styles
   duplicated once under `#myModal_lg` for the edit-order preview. Exclude demo shell,
   sidebar, topbar, toasts, `.m-*` modal mocks, global resets.
6. **Order detail** keeps `#sales_details` + `include_once 'assets/js/sales.php'`
   (verified harmless — `calculate_total()` no-ops without invoice rows), guards every
   API field (`is_object`/`!empty`) because PHP warnings trip the e2e error regex, fixes
   shipping-row label (`lang('discount')` → `lang('shipping')`), computes subtotal =
   Σ line qty×price, drops the inline `<style>` block.
7. **E2E scope:** order detail is asserted when the live WooCommerce API returns the page;
   if `get_order()` redirects (API unreachable) the check reports `SKIP`, not failure —
   API availability is environment, not code.
8. **Bulk delete on woo pages** = existing behavior: confirm dialog then no-op
   (`bulk_url = null`, no checkboxes). Toolbar icon clicks it (parity) — record as
   limitation, no new endpoint.

## Task overview

| # | Task | Commit |
|---|---|---|
| 1 | Red e2e test + composer `test:woo` | `test: add woocommerce redesign e2e test` |
| 2 | `fire_datatable` opts + footer guard | `feat: add opts hooks to fire_datatable` |
| 3 | CSS, JS, lang keys, PHP helpers | `feat: add woocommerce modern assets, helpers, lang keys` |
| 4 | Orders page (pilot) + D8/D9 | `feat: restyle woocommerce orders list` |
| 5 | Products page | `feat: restyle woocommerce products list` |
| 6 | Customers page + D1/D3 | `feat: restyle woocommerce customers list` |
| 7 | Stores page + D6/D10 | `feat: restyle woocommerce stores list` |
| 8 | Order detail | `feat: restyle woocommerce order detail` |
| 9 | Modals + conditional modal link (D7 + edit_products fixes) | `feat: restyle woocommerce modal forms` |
| 10 | Full verification (gates + Playwright vs demo) | `docs: woocommerce redesign verification notes` |

Gates run after Task 2 (shared file touched), after each page task (`test:woo`
progression), and fully in Task 10.

---

## Task 1 — Red e2e test (TDD)

**Files:** `plan/_tools/woocommerce-redesign-test.php` (new), `composer.json` (add script).

### 1a. composer.json

```json
"scripts": {
    "smoke": "php plan/_tools/smoke.php",
    "smoke:demo": "php plan/_tools/smoke.php --db=demo",
    "test:login": "php plan/_tools/login-test.php",
    "test:theme": "php plan/_tools/bg-custom-test.php normal && php plan/_tools/bg-custom-test.php theme",
    "test:menu": "php plan/_tools/menu-group-test.php all",
    "test:woo": "php plan/_tools/woocommerce-redesign-test.php"
}
```

### 1b. `plan/_tools/woocommerce-redesign-test.php` (complete file)

Mirror `calendar-event-test.php` (curl + cookie jar + `$checks[name] = null|reason`
convention), plus a `SKIP:` convention (not a failure) and a `dt_post()` helper with the
`X-Requested-With` header.

```php
<?php
/**
 * WooCommerce redesign e2e test (TDD companion — written BEFORE implementation).
 *
 * Asserts: modern markup + stylesheet on the 4 list pages, server-rendered KPI
 * values, DataTables JSON incl. pill/search filters, modal forms with the
 * conditional stylesheet, and the order detail page (soft-SKIP when the live
 * WooCommerce API is unreachable).
 *
 *   php plan/_tools/woocommerce-redesign-test.php [--base=URL]
 *   composer test:woo
 *
 * Exits 0 when all assertions pass (SKIPs allowed), 1 otherwise.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$opts = getopt('', ['base::', 'help']);
if (isset($opts['help'])) {
    echo "Usage: php plan/_tools/woocommerce-redesign-test.php [--base=URL]\n";
    exit(0);
}
$baseUrl = rtrim($opts['base'] ?? 'http://localhost/complete_crm', '/');
$user = 'forid';
$pass = 'Admin@123';

$errorPattern = '/Fatal error|Parse error|Uncaught\s|<b>Warning\b|<b>Notice\b|'
    . '<b>Deprecated\b|Allowed memory size|Cannot redeclare|Call to undefined|'
    . 'mysqli_sql_exception|SQLSTATE\[|A Database Error Occurred|Error Number:|'
    . 'Undefined (variable|array|property|offset)/i';

$cookieJar = (getenv('TEMP') ?: sys_get_temp_dir()) . '/tic_crm_woo_' . getmypid() . '.jar';

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
        CURLOPT_USERAGENT      => 'TIC-CRM-woo-test/1.0',
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
    $set = [];
    if (is_file($cookieJar)) {
        foreach (file($cookieJar, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $p = explode("\t", $line);
            if (count($p) >= 7) {
                $set[$p[5]] = $p[6];
            }
        }
    }
    return ['status' => $status, 'body' => $body, 'cookie' => $set, 'url' => curl_getinfo($ch) ?: ''];
}

/** POST to a DataTables endpoint with the payload the pipeline requires. */
function dt_post(string $baseUrl, string $path, array $extra = []): array
{
    $base = [
        'draw'             => '1',
        'start'            => '0',
        'length'           => '10',
        'order[0][column]' => '0',
        'order[0][dir]'    => 'desc',
        'search[value]'    => '',
        'search[regex]'    => 'false',
    ];
    $res = request('POST', $baseUrl . $path, array_merge($base, $extra),
        ['X-Requested-With: XMLHttpRequest']);
    $json = json_decode($res['body'], true);
    return [
        'status' => $res['status'],
        'body'   => $res['body'],
        'json'   => is_array($json) ? $json : null,
    ];
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

/* ---------- 1. list pages: modern markup + KPI values ---------- */
$pages = [
    'orders page has woo-modern markup + CSS + JS' => [
        '/admin/woocommerce/orders',
        ['woocommerce_modern.css', 'woocommerce-modern.js', 'woo-modern', 'kpi-strip',
         'list-toolbar', 'kpi-card', 'Total orders', '1,496'],
    ],
    'products page has woo-modern markup + KPI' => [
        '/admin/woocommerce/products',
        ['woocommerce_modern.css', 'woo-modern', 'kpi-card', 'Total products', '741'],
    ],
    'customers page has woo-modern markup + KPI' => [
        '/admin/woocommerce/customers',
        ['woocommerce_modern.css', 'woo-modern', 'kpi-card', 'Total customers', '2,133'],
    ],
    'stores page has woo-modern markup + KPI' => [
        '/admin/woocommerce/stores',
        ['woocommerce_modern.css', 'woo-modern', 'kpi-card', 'Total stores', '3,039'],
    ],
];
foreach ($pages as $name => [$path, $needles]) {
    $page = request('GET', $baseUrl . $path);
    $reason = null;
    if ($page['status'] !== 200) {
        $reason = 'HTTP ' . $page['status'];
    } elseif (preg_match($errorPattern, $page['body'], $m)) {
        $reason = 'PHP error: ' . $m[0];
    } else {
        $reason = markers_in($page['body'], $needles);
    }
    $checks[$name] = $reason;
}

/* ---------- 2. orderslist: JSON health + fan-out fix (D8) ---------- */
$dt = dt_post($baseUrl, '/admin/woocommerce/orderslist');
$reason = null;
if ($dt['json'] === null) {
    $reason = 'not JSON: ' . substr($dt['body'], 0, 180);
} elseif ((int) $dt['json']['recordsFiltered'] < 1) {
    $reason = 'no rows';
} elseif ((int) $dt['json']['recordsFiltered'] !== (int) $dt['json']['recordsTotal']) {
    $reason = sprintf('fan-out: total=%d filtered=%d (D8 join fix missing)',
        $dt['json']['recordsTotal'], $dt['json']['recordsFiltered']);
} else {
    $row = implode('', $dt['json']['data'][0] ?? []);
    $reason = markers_in($row, ['#', 'st st-', 'tag', 'acts'])
        ?: (preg_match($errorPattern, $dt['body'], $m) ? 'PHP error: ' . $m[0] : null);
}
$checks['orderslist returns healthy modern JSON'] = $reason;

/* orders search must work (D9 ambiguous phone — currently a Database Error page) */
$dt = dt_post($baseUrl, '/admin/woocommerce/orderslist', ['search[value]' => 'Johnson']);
$checks['orderslist global search works (D9)'] =
    ($dt['json'] !== null && (int) $dt['json']['recordsFiltered'] > 0)
        ? null : 'search failed: ' . substr($dt['body'], 0, 180);

/* status pill → every row carries the completed pill */
$dt = dt_post($baseUrl, '/admin/woocommerce/orderslist', ['woo_status' => 'completed']);
$reason = null;
if ($dt['json'] === null) {
    $reason = 'not JSON';
} else {
    foreach ($dt['json']['data'] as $row) {
        if (strpos(implode('', (array) $row), 'st st-completed') === false) {
            $reason = 'row without st st-completed pill';
            break;
        }
    }
}
$checks['orderslist woo_status=completed filters rows'] = $reason;

/* store pill that matches nothing → zero rows */
$dt = dt_post($baseUrl, '/admin/woocommerce/orderslist', ['woo_store' => '999']);
$checks['orderslist woo_store=999 returns 0 rows'] =
    ($dt['json'] !== null && (int) $dt['json']['recordsFiltered'] === 0)
        ? null : 'expected 0, got: ' . substr($dt['body'], 0, 160);

/* ---------- 3. productlist ---------- */
$dt = dt_post($baseUrl, '/admin/woocommerce/productlist');
$reason = null;
if ($dt['json'] === null) {
    $reason = 'not JSON: ' . substr($dt['body'], 0, 180);
} elseif ((int) $dt['json']['recordsFiltered'] < 1) {
    $reason = 'no rows';
} else {
    $row = implode('', $dt['json']['data'][0] ?? []);
    $reason = markers_in($row, ['thumb', 'st st-', 'tag', 'acts']);
}
$checks['productlist returns healthy modern JSON'] = $reason;

$dt = dt_post($baseUrl, '/admin/woocommerce/productlist', ['woo_status' => 'draft']);
$reason = null;
if ($dt['json'] === null || (int) $dt['json']['recordsFiltered'] < 1) {
    $reason = 'draft filter returned nothing: ' . substr($dt['body'], 0, 160);
} else {
    foreach ($dt['json']['data'] as $row) {
        if (strpos(implode('', (array) $row), 'st st-draft') === false) {
            $reason = 'row without st st-draft pill';
            break;
        }
    }
}
$checks['productlist woo_status=draft filters rows'] = $reason;

$dt = dt_post($baseUrl, '/admin/woocommerce/productlist', ['search[value]' => 'zzzznotfoundzzz']);
$checks['productlist no-match search returns 0 rows'] =
    ($dt['json'] !== null && (int) $dt['json']['recordsFiltered'] === 0)
        ? null : 'expected 0, got: ' . substr($dt['body'], 0, 160);

/* ---------- 4. customerlist ---------- */
$dt = dt_post($baseUrl, '/admin/woocommerce/customerlist');
$reason = null;
if ($dt['json'] === null) {
    $reason = 'not JSON: ' . substr($dt['body'], 0, 180);
} elseif ((int) $dt['json']['recordsFiltered'] < 1) {
    $reason = 'no rows';
} else {
    $row = implode('', $dt['json']['data'][0] ?? []);
    $reason = markers_in($row, ['av', 'nm', 'acts']);
}
$checks['customerlist returns healthy modern JSON'] = $reason;

$dt = dt_post($baseUrl, '/admin/woocommerce/customerlist', ['search[value]' => 'zzzznotfoundzzz']);
$checks['customerlist search works + no-match returns 0 (D1)'] =
    ($dt['json'] !== null && (int) $dt['json']['recordsFiltered'] === 0)
        ? null : 'expected 0, got: ' . substr($dt['body'], 0, 160);

$dt = dt_post($baseUrl, '/admin/woocommerce/customerlist', ['woo_store' => '999']);
$checks['customerlist woo_store=999 returns 0 rows'] =
    ($dt['json'] !== null && (int) $dt['json']['recordsFiltered'] === 0)
        ? null : 'expected 0, got: ' . substr($dt['body'], 0, 160);

/* ---------- 5. storeslist ---------- */
$dt = dt_post($baseUrl, '/admin/woocommerce/storeslist');
$reason = null;
if ($dt['json'] === null) {
    $reason = 'not JSON: ' . substr($dt['body'], 0, 180);
} elseif ((int) $dt['json']['recordsFiltered'] < 1) {
    $reason = 'no rows';
} else {
    $row = implode('', $dt['json']['data'][0] ?? []);
    $reason = markers_in($row, ['synced', 'acts']);
}
$checks['storeslist returns healthy modern JSON'] = $reason;

$dt = dt_post($baseUrl, '/admin/woocommerce/storeslist', ['search[value]' => 'zzzznotfoundzzz']);
$checks['storeslist search works + no-match returns 0 (D10)'] =
    ($dt['json'] !== null && (int) $dt['json']['recordsFiltered'] === 0)
        ? null : 'expected 0, got: ' . substr($dt['body'], 0, 160);

/* ---------- 6. modal pages: conditional stylesheet + forms ---------- */
$modal = request('GET', $baseUrl . '/admin/woocommerce/new_stores');
$checks['new_stores modal loads with conditional CSS + form'] =
    $modal['status'] !== 200
        ? 'HTTP ' . $modal['status']
        : (markers_in($modal['body'], ['woocommerce_modern.css', '<form', 'store_name'])
            ?: (preg_match($errorPattern, $modal['body'], $m) ? 'PHP error: ' . $m[0] : null));

$modal = request('GET', $baseUrl . '/admin/woocommerce/edit_products/1');
$checks['edit_products modal has fixed action URL + conditional CSS'] =
    $modal['status'] !== 200
        ? 'HTTP ' . $modal['status']
        : (markers_in($modal['body'], ['woocommerce_modern.css', 'update/product/'])
            ?: (preg_match($errorPattern, $modal['body'], $m) ? 'PHP error: ' . $m[0] : null));

/* ---------- 7. order detail (soft-skip when API unreachable) ---------- */
$detailUrl = null;
$dt = dt_post($baseUrl, '/admin/woocommerce/orderslist');
if (!empty($dt['json']['data'][0])) {
    $row = implode('', $dt['json']['data'][0]);
    if (preg_match('#href="([^"]*/order/\d+)"#', $row, $m)) {
        $detailUrl = $m[1];
    }
}
if ($detailUrl === null) {
    $checks['order detail page renders modern detail'] = 'SKIP: could not extract an order URL';
} else {
    $page = request('GET', $detailUrl);
    if (strpos($page['body'], 'dt-bar') !== false) {
        $reason = markers_in($page['body'], ['dt-bar', 'dt-grid', 'tot-row', 'note-box', 'dt-title'])
            ?: (preg_match($errorPattern, $page['body'], $m) ? 'PHP error: ' . $m[0] : null);
        $checks['order detail page renders modern detail'] = $reason;
    } elseif (preg_match($errorPattern, $page['body'], $m)) {
        $checks['order detail page renders modern detail'] = 'PHP error: ' . $m[0];
    } else {
        $checks['order detail page renders modern detail'] =
            'SKIP: WooCommerce API unreachable (page redirected)';
    }
}

/* ---------- 8. no stylesheet leak on non-woo pages ---------- */
$home = request('GET', $baseUrl . '/admin/dashboard');
$checks['non-woo page does not load woocommerce_modern.css'] =
    strpos($home['body'], 'woocommerce_modern.css') === false
        ? null : 'stylesheet leaked outside the woo module';

/* ---------- report ---------- */
$fail = 0;
$skip = 0;
echo str_repeat('=', 76) . "\n";
printf("woocommerce-redesign-test  %s\n", $baseUrl);
echo str_repeat('=', 76) . "\n";
foreach ($checks as $name => $reason) {
    if ($reason === null) {
        printf("PASS  %s\n", $name);
    } elseif (strpos($reason, 'SKIP:') === 0) {
        printf("SKIP  %s\n      -> %s\n", $name, substr($reason, 5));
        $skip++;
    } else {
        printf("FAIL  %s\n      -> %s\n", $name, $reason);
        $fail++;
    }
}
echo str_repeat('=', 76) . "\n";
printf("%d assertions — %d passed, %d failed, %d skipped\n",
    count($checks), count($checks) - $fail - $skip, $fail, $skip);
if (is_file($cookieJar)) { @unlink($cookieJar); }
exit($fail === 0 ? 0 : 1);
```

### 1c. Verification (red baseline)

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe plan\_tools\woocommerce-redesign-test.php
```

Expected: exit 1 with failures on all `woo-modern`/KPI markers (pages not yet restyled),
`fan-out` (D8), orders search (D9), stores search (D10). Baseline failures are captured
in the run output — every one must flip to PASS by Task 9. `composer smoke` must stay
green (nothing touched yet).

**Commit:** `test: add woocommerce redesign e2e test`

---

## Task 2 — `fire_datatable` opts + footer guard

**File:** `assets/plugins/dataTables/js/dataTables.php`

### 2a. Function signature + opts wiring

Replace `function fire_datatable(req_url, ttable = null) {` with:

```js
function fire_datatable(req_url, ttable = null, opts = {}) {
    opts = opts || {};
```

### 2b. Build the config as a variable, merge opts, call onInit

Replace the statement

```js
            table = $(dtable).dataTable({
```

with

```js
            var dtConfig = {
```

and after the closing `});` of the config object (the one ending with `oLanguage`), i.e.
replacing

```js
            });
        }
    }
```

with

```js
            };
            if (opts.config) {
                $.extend(dtConfig, opts.config);
            }
            table = $(dtable).dataTable(dtConfig);
            if (typeof opts.onInit === 'function') {
                opts.onInit(table.api());
            }
        }
    }
```

### 2c. ajaxData hook

Replace

```js
                    data: function (d) {
                        d.csrf_token = getCookie('csrf_cookie');
                    },
```

with

```js
                    data: function (d) {
                        d.csrf_token = getCookie('csrf_cookie');
                        if (typeof opts.ajaxData === 'function') {
                            opts.ajaxData(d);
                        }
                    },
```

### 2d. Footer auto-invoke guard

Replace

```js
    $(function () {
        fire_datatable(list);
    });
```

with

```js
    $(function () {
        if (typeof list !== 'undefined' && list) {
            fire_datatable(list, null,
                (typeof woo_opts !== 'undefined' && woo_opts) ? woo_opts : {});
        }
    });
```

Nothing else in this file changes (leave the `#datatable_action` block alone).

### Verification

```
composer smoke; composer test:menu; composer test:login; composer test:theme
php plan\_tools\woocommerce-redesign-test.php
```

Gates green; `test:woo` still red at the same UI markers (pages not restyled yet — the
JSON/fan-out/search checks stay red until Tasks 4/6/7). Spot-check: open
`admin/invoice/manage_invoice` in a browser — table still initializes (unchanged path).

**Commit:** `feat: add opts hooks to fire_datatable`

---

## Task 3 — Stylesheet, JS, language keys, PHP helpers

**Files (all new):**
- `assets/css/woocommerce_modern.css`
- `assets/js/woocommerce-modern.js`
- `application/modules/woocommerce/language/english/woocommerce_lang.php` (edit)
- `application/modules/woocommerce/helpers/woocommerce_helper.php` (append)

### 3a. `assets/css/woocommerce_modern.css` (complete file)

Ported from `_design/woocommerce-full.html` lines 7–221 with `.woo-modern ` scoping.
Excluded: global resets (18–22), shell/sidebar/topbar (24–61), demo pager (141–145 —
DT supplies pagination), demo modal mocks + toasts (177–213), `@keyframes pop/tin`.

```css
/* =============================================================
   WooCommerce modern list/detail/modal styles
   Spec: docs/superpowers/specs/2026-10-01-woocommerce-redesign-implementation-design.md (L113)
   Ported from _design/woocommerce-full.html (style block lines 7-221),
   scoped with .woo-modern / #myModal_lg so tokens.css and theme CSS stay global.
   ============================================================= */

/* ---------- demo-only token overrides (scoped — zero leak) ---------- */
.woo-modern,
#myModal_lg {
  --c-border-2: #e3e6ea;
  --c-slate: #94a3b8;
  --c-mint: var(--ac-mint);
  --c-cyan: var(--ac-cyan);
  --c-amber: var(--ac-amber);
  --c-purple: var(--ac-purple);
  --r-md: 10px;
  --r-lg: 14px;
  --sh-1: 0 1px 2px rgba(15, 23, 42, .06);
  --fs-sm: 13px;
  --fs-xs: 12px;
}

/* ---------- page title ---------- */
.woo-modern h1.page-t { font-size: 18px; font-weight: 700; margin: 0 0 14px; letter-spacing: -.01em; }

/* ---------- cards + KPI ---------- */
.woo-modern .card { background: var(--c-surface); border: 1px solid var(--c-border-2); border-radius: var(--r-xl); box-shadow: var(--sh-1); position: relative; min-width: 0; margin-bottom: 16px; }
.woo-modern .card-h { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 16px 0; }
.woo-modern .card-h h3 { margin: 0; font-size: 15px; font-weight: 700; letter-spacing: -.01em; }
.woo-modern .card-b { padding: 14px 16px 16px; }
.woo-modern .kpi-strip { margin-bottom: 16px; }
.woo-modern .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
.woo-modern .kpi-card { display: flex; align-items: center; gap: 12px; padding: 14px 16px; background: var(--c-surface); border: 1px solid var(--c-border-2); border-radius: var(--r-xl); box-shadow: var(--sh-1); min-width: 0; }
.woo-modern .kpi-card .k-i { width: 32px; height: 32px; flex: 0 0 32px; border-radius: var(--r-md); display: grid; place-items: center; font-size: 15px; font-weight: 700; }
.woo-modern .k-i.ok { background: #ecfdf5; color: #10b981; }
.woo-modern .k-i.no { background: #fef2f2; color: #dc2626; }
.woo-modern .k-i.cyan { background: #ecfeff; color: #0e7490; }
.woo-modern .k-i.amb { background: #fffbeb; color: #b45309; }
.woo-modern .k-i.pur { background: #f5f3ff; color: #6d28d9; }
.woo-modern .kpi-card .v { font-size: 20px; font-weight: 700; line-height: 1.2; font-variant-numeric: tabular-nums; white-space: nowrap; }
.woo-modern .kpi-card .l { font-size: var(--fs-sm); color: var(--c-text-3); margin-top: 2px; }

/* ---------- store tag ---------- */
.woo-modern .tag { font-size: 10px; font-weight: 700; border-radius: var(--r-full); padding: 3px 8px; white-space: nowrap; background: #ecfeff; color: #0e7490; }

/* ---------- toolbar ---------- */
.woo-modern .list-toolbar { display: flex; align-items: center; gap: 8px; padding: 14px 16px; border-bottom: 1px solid #f1f3f5; flex-wrap: wrap; }
.woo-modern .tsearch { display: flex; align-items: center; gap: 7px; height: 32px; border-radius: 999px; background: #f1f3f5; padding: 0 13px; min-width: 230px; }
.woo-modern .tsearch input { border: 0; background: transparent; outline: none; font-size: 12.5px; color: var(--c-text); width: 100%; font-family: inherit; }
.woo-modern .tsearch .ico { color: var(--c-slate); font-size: 13px; }
.woo-modern .filter-pill { display: flex; align-items: center; gap: 7px; height: 32px; border-radius: 999px; background: #f1f3f5; color: var(--c-text-2); font-size: 12px; font-weight: 600; padding: 0 13px; border: 0; }
.woo-modern .filter-pill:hover { background: #e9ecef; }
.woo-modern .filter-pill .c-dot { width: 7px; height: 7px; border-radius: 99px; background: var(--c-accent); }
.woo-modern .icon-btn { width: 32px; height: 32px; border-radius: 8px; background: #f1f3f5; display: grid; place-items: center; color: var(--c-text-2); font-size: 13px; border: 0; }
.woo-modern .icon-btn:hover { background: #e9ecef; }
.woo-modern .tb-right { margin-left: auto; display: flex; gap: 8px; align-items: center; }
.woo-modern .dd-wrap { position: relative; }
.woo-modern .dd { position: absolute; top: 38px; left: 0; min-width: 190px; background: #fff; border: 1px solid var(--c-border-2); border-radius: 12px; box-shadow: 0 12px 32px rgba(15, 23, 42, .14); padding: 6px; z-index: 45; display: none; }
.woo-modern .dd.open { display: block; }
.woo-modern .dd-item { display: flex; align-items: center; gap: 8px; padding: 7px 10px; border-radius: 8px; font-size: 12.5px; color: var(--c-text-2); cursor: pointer; font-weight: 600; white-space: nowrap; }
.woo-modern .dd-item:hover { background: #f1f3f5; }
.woo-modern .dd-item.on { color: var(--c-accent); }
.woo-modern .dd-item .ck { width: 13px; font-size: 11px; flex: 0 0 13px; }
.woo-modern .btn-new { height: 34px; border-radius: 8px; background: var(--c-accent); color: #fff; font-size: 12px; font-weight: 600; padding: 0 16px; display: flex; align-items: center; gap: 6px; box-shadow: 0 1px 2px rgba(13, 148, 136, .35); border: 0; text-decoration: none; }
.woo-modern .btn-new:hover { background: #0b7c72; color: #fff; }

/* ---------- table ---------- */
.woo-modern .tbl-wrap { overflow-x: auto; }
.woo-modern .tbl { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 840px; }
.woo-modern .tbl th { font-size: 10px; text-transform: uppercase; letter-spacing: .05em; color: var(--c-slate); font-weight: 700; text-align: left; padding: 10px 16px; background: #fafbfc; border-bottom: 1px solid var(--c-border-2); }
.woo-modern .tbl td { padding: 11px 16px; border-bottom: 1px solid #f1f3f5; color: var(--c-text); vertical-align: middle; }
.woo-modern .tbl tbody tr:last-child td { border-bottom: 0; }
.woo-modern .tbl tbody tr:hover { background: #fafcfb; }
.woo-modern .tbl .num, .woo-modern .tbl .t-r { text-align: right; font-variant-numeric: tabular-nums; }
.woo-modern .tbl .num { font-weight: 600; }
.woo-modern .cell-main { display: flex; gap: 10px; align-items: center; min-width: 0; }
.woo-modern .thumb { width: 34px; height: 34px; border-radius: 8px; object-fit: cover; background: #f1f3f5; flex: 0 0 34px; }
.woo-modern .thumb-fb { width: 34px; height: 34px; border-radius: 8px; display: none; place-items: center; background: #ecfdf5; color: #0d9488; font-size: 12px; font-weight: 800; flex: 0 0 34px; }
.woo-modern .av { width: 32px; height: 32px; border-radius: 99px; object-fit: cover; background: linear-gradient(135deg, #0d9488, #06b6d4); flex: 0 0 32px; }
.woo-modern .av-fb { width: 32px; height: 32px; border-radius: 99px; display: none; place-items: center; background: #ecfdf5; color: #0d9488; font-size: 11px; font-weight: 800; flex: 0 0 32px; }
.woo-modern .av-ini { display: grid; place-items: center; background: #ecfdf5; color: #0d9488; font-size: 11px; font-weight: 800; }
.woo-modern .nm { font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 340px; }
.woo-modern .sub { font-size: 11.5px; color: var(--c-slate); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 340px; }
.woo-modern .mono { font-variant-numeric: tabular-nums; }
.woo-modern .col-soft { color: var(--c-text-2); }

/* ---------- status pills ---------- */
.woo-modern .st { display: inline-flex; align-items: center; gap: 6px; font-size: 10.5px; font-weight: 700; border-radius: 999px; padding: 4px 10px; text-transform: capitalize; white-space: nowrap; }
.woo-modern .st::before { content: ""; width: 6px; height: 6px; border-radius: 99px; background: currentColor; opacity: .75; }
.woo-modern .st-completed, .woo-modern .st-publish { background: #ecfdf5; color: #0d9488; }
.woo-modern .st-pending { background: #fffbeb; color: #b45309; }
.woo-modern .st-processing { background: #eff6ff; color: #2563eb; }
.woo-modern .st-on-hold, .woo-modern .st-draft { background: #f1f3f5; color: #475569; }
.woo-modern .st-cancelled, .woo-modern .st-failed { background: #fef2f2; color: #dc2626; }
.woo-modern .st-refunded, .woo-modern .st-private { background: #f5f3ff; color: #6d28d9; }

/* ---------- row actions ---------- */
.woo-modern .acts { display: flex; gap: 6px; justify-content: flex-end; }
.woo-modern .acts .btn { width: 28px; height: 28px; padding: 0; margin: 0; border-radius: 8px; border: 1px solid var(--c-border-2); background: #fff; color: var(--c-text-3); display: grid; place-items: center; font-size: 12px; line-height: 1; text-decoration: none; }
.woo-modern .acts .btn:hover { background: #f1f3f5; color: var(--c-text); }
.woo-modern .acts .btn-danger:hover { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
.woo-modern .synced { display: flex; gap: 8px; font-size: 11.5px; color: var(--c-text-2); font-variant-numeric: tabular-nums; flex-wrap: wrap; }
.woo-modern .synced b { font-weight: 700; color: var(--c-text); }
.woo-modern .assignees { display: flex; flex-wrap: wrap; gap: 4px; }
.woo-modern .asg { font-size: 10.5px; font-weight: 700; background: #f1f3f5; color: var(--c-text-2); border-radius: 99px; padding: 3px 8px; white-space: nowrap; }

/* ---------- DataTables skin ---------- */
.woo-modern .dataTables_wrapper { padding: 0; }
.woo-modern .dataTables_wrapper .row { margin: 0; }
.woo-modern .dataTables_wrapper .dataTables_length { padding: 10px 16px 0; font-size: 12px; color: var(--c-slate); }
.woo-modern .dataTables_wrapper .dataTables_length select { height: 28px; border-radius: 8px; border: 1px solid var(--c-border-2); font-size: 12px; color: var(--c-text-2); padding: 0 6px; }
.woo-modern .dataTables_wrapper .dataTables_info { padding: 12px 16px; font-size: 12px; color: var(--c-slate); }
.woo-modern .dataTables_wrapper .dataTables_paginate { padding: 10px 16px; }
.woo-modern .dataTables_wrapper .dataTables_paginate .pagination { margin: 0; display: flex; gap: 6px; }
.woo-modern .dataTables_wrapper .dataTables_paginate .pagination > li > a,
.woo-modern .dataTables_wrapper .dataTables_paginate .pagination > li > span { height: 28px; min-width: 28px; padding: 0 9px; border-radius: 8px; border: 1px solid var(--c-border-2); background: #fff; font-size: 12px; font-weight: 700; color: var(--c-text-2); display: grid; place-items: center; line-height: 1; }
.woo-modern .dataTables_wrapper .dataTables_paginate .pagination > li > a:hover,
.woo-modern .dataTables_wrapper .dataTables_paginate .pagination > li > a:focus { background: #f1f3f5; color: var(--c-text-2); border-color: var(--c-border-2); }
.woo-modern .dataTables_wrapper .dataTables_paginate .pagination > .active > a,
.woo-modern .dataTables_wrapper .dataTables_paginate .pagination > .active > span { background: var(--c-accent); border-color: var(--c-accent); color: #fff; }
.woo-modern .dataTables_wrapper .dataTables_paginate .pagination > .disabled > a { opacity: .4; cursor: default; }
.woo-modern table.dataTable thead th.sorting:after,
.woo-modern table.dataTable thead th.sorting_asc:after,
.woo-modern table.dataTable thead th.sorting_desc:after { right: 6px; opacity: .5; }
.woo-modern table.dataTable tbody tr.selected,
.woo-modern table.dataTable tbody tr.selected:hover { background: #ecfdf9 !important; }
.woo-modern td.dataTables_empty { padding: 44px 16px; text-align: center; color: var(--c-slate); font-size: 13px; }
.woo-modern .dataTables_processing { z-index: 10; }

/* ---------- empty state (detail page) ---------- */
.woo-modern .empty { padding: 44px 16px; text-align: center; color: var(--c-slate); }
.woo-modern .empty .big { font-size: 26px; margin-bottom: 6px; }
.woo-modern .empty b { display: block; color: var(--c-text-2); font-size: 14px; margin-bottom: 3px; }

/* ---------- order detail ---------- */
.woo-modern .dt-bar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }
.woo-modern .dt-title { font-size: 18px; font-weight: 700; letter-spacing: -.01em; }
.woo-modern .dt-when { font-size: 12px; color: var(--c-slate); }
.woo-modern .dt-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 16px; }
.woo-modern .kv { display: flex; justify-content: space-between; gap: 10px; padding: 7px 0; border-bottom: 1px solid #f1f3f5; font-size: 13px; }
.woo-modern .kv:last-child { border-bottom: 0; }
.woo-modern .kv .k { color: var(--c-slate); }
.woo-modern .kv .val { font-weight: 600; text-align: right; word-break: break-word; }
.woo-modern .dt-2 { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 16px; }
.woo-modern .tot-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 13px; color: var(--c-text-2); border-bottom: 1px solid #f1f3f5; }
.woo-modern .tot-row:last-child { border-bottom: 0; }
.woo-modern .tot-row .tv { font-weight: 600; color: var(--c-text); font-variant-numeric: tabular-nums; }
.woo-modern .tot-row.grand { border-top: 2px solid var(--c-border-2); border-bottom: 0; margin-top: 4px; padding-top: 12px; font-size: 15px; font-weight: 800; color: var(--c-text); }
.woo-modern .note-box { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; border-radius: 12px; padding: 12px 14px; font-size: 13px; line-height: 1.55; margin-top: 14px; }
.woo-modern .note-box b { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 4px; }

/* ---------- buttons ---------- */
.woo-modern .mini-btn { font-size: 11.5px; font-weight: 700; border-radius: 8px; padding: 6px 11px; background: #f1f3f5; color: var(--c-text-2); border: 0; text-decoration: none; display: inline-block; }
.woo-modern .mini-btn:hover { background: #e9ecef; color: var(--c-text-2); }
.woo-modern .btn-ghost { height: 36px; border-radius: 9px; background: #f1f3f5; border: 0; color: var(--c-text-2); font-size: 13px; font-weight: 700; padding: 0 16px; display: inline-flex; align-items: center; gap: 6px; }
.woo-modern .btn-ghost:hover { background: #e9ecef; color: var(--c-text-2); }
.woo-modern .btn-accent { height: 36px; border-radius: 9px; background: var(--c-accent); border: 0; color: #fff; font-size: 13px; font-weight: 700; padding: 0 18px; box-shadow: 0 1px 2px rgba(13, 148, 136, .35); display: inline-flex; align-items: center; gap: 6px; }
.woo-modern .btn-accent:hover { background: #0b7c72; color: #fff; }

/* ---------- responsive ---------- */
@media (max-width: 1100px) {
  .woo-modern .kpi-grid { grid-template-columns: repeat(2, 1fr); }
  .woo-modern .dt-grid, .woo-modern .dt-2 { grid-template-columns: 1fr; }
}
@media (max-width: 760px) {
  .woo-modern .kpi-grid { grid-template-columns: 1fr; }
}

/* =============================================================
   Modal (#myModal_lg) restyle — only loaded for /admin/woocommerce/* URIs
   (conditional link in application/views/admin/_layout_modal_lg.php).
   ============================================================= */
#myModal_lg .modal-content { border: 0; border-radius: var(--r-xl); box-shadow: 0 24px 60px rgba(15, 23, 42, .28); overflow: hidden; background: var(--c-surface); }
#myModal_lg .panel { border: 0; box-shadow: none; margin-bottom: 0; background: var(--c-surface); border-radius: 0; }
#myModal_lg .panel .panel-heading { display: flex; align-items: center; gap: 10px; padding: 18px 20px 0; background: none; border: 0; color: var(--c-text); }
#myModal_lg .panel .panel-heading h4, #myModal_lg .panel .panel-heading .modal-title { font-size: 16.5px; font-weight: 700; letter-spacing: -.01em; margin: 0; }
#myModal_lg .panel .panel-heading .close { position: static; float: none; margin-left: auto; width: 30px; height: 30px; border-radius: 999px; background: #f1f3f5; border: 0; color: var(--c-text-3); font-size: 14px; display: grid; place-items: center; opacity: 1; text-shadow: none; line-height: 1; }
#myModal_lg .panel .panel-heading .close:hover { background: #e9ecef; color: var(--c-text); }
#myModal_lg .modal-body { padding: 16px 20px 4px; }
#myModal_lg .form-group { margin-bottom: 13px; }
#myModal_lg .control-label { font-size: 12.5px; font-weight: 700; color: var(--c-text-2); padding-top: 8px; }
#myModal_lg .required { color: var(--c-danger); }
#myModal_lg .form-control {
  height: 38px; border-radius: 9px; border: 1px solid var(--c-border-2);
  background: #fff; padding: 0 12px; font-size: 13.5px; color: var(--c-text);
  box-shadow: none; transition: border-color var(--dur-fast) var(--ease), box-shadow var(--dur-fast) var(--ease);
}
#myModal_lg .form-control:focus { border-color: var(--c-accent); box-shadow: 0 0 0 3px rgba(13, 148, 136, .12); }
#myModal_lg textarea.form-control { height: auto; min-height: 74px; padding: 9px 12px; }
#myModal_lg .btn-primary { background: var(--c-accent); border-color: var(--c-accent); border-radius: 9px; height: 36px; font-weight: 700; font-size: 13px; padding: 0 18px; box-shadow: 0 1px 2px rgba(13, 148, 136, .35); }
#myModal_lg .btn-primary:hover, #myModal_lg .btn-primary:focus { background: #0b7c72; border-color: #0b7c72; }
#myModal_lg .select2-container--bootstrap .select2-selection { height: 38px; border-radius: 9px; border: 1px solid var(--c-border-2); box-shadow: none; font-size: 13.5px; }
#myModal_lg .select2-container--bootstrap .select2-selection__rendered { line-height: 36px; color: var(--c-text); padding-left: 12px; }
#myModal_lg .select2-container--bootstrap .select2-selection__arrow { height: 36px; }
#myModal_lg .select2-container--bootstrap.select2-container--focus .select2-selection,
#myModal_lg .select2-container--bootstrap.select2-container--open .select2-selection { border-color: var(--c-accent); box-shadow: 0 0 0 3px rgba(13, 148, 136, .12); }
#myModal_lg .bootstrap-select > .btn { height: 38px; border-radius: 9px; border: 1px solid var(--c-border-2); background: #fff; color: var(--c-text); font-weight: 500; font-size: 13.5px; box-shadow: none; }
#myModal_lg .bootstrap-select > .btn:focus { border-color: var(--c-accent); box-shadow: 0 0 0 3px rgba(13, 148, 136, .12); }

/* edit-order live pill preview */
#myModal_lg .live-pill { display: flex; align-items: center; gap: 10px; background: #f8f9fa; border: 1px solid var(--c-border-2); border-radius: 12px; padding: 11px 13px; margin-bottom: 6px; }
#myModal_lg .live-pill .lp-l { font-size: 11.5px; color: var(--c-slate); font-weight: 600; }
/* pill styles duplicated under #myModal_lg (list copy is .woo-modern-scoped) */
#myModal_lg .st { display: inline-flex; align-items: center; gap: 6px; font-size: 10.5px; font-weight: 700; border-radius: 999px; padding: 4px 10px; text-transform: capitalize; white-space: nowrap; }
#myModal_lg .st::before { content: ""; width: 6px; height: 6px; border-radius: 99px; background: currentColor; opacity: .75; }
#myModal_lg .st-completed, #myModal_lg .st-publish { background: #ecfdf5; color: #0d9488; }
#myModal_lg .st-pending { background: #fffbeb; color: #b45309; }
#myModal_lg .st-processing { background: #eff6ff; color: #2563eb; }
#myModal_lg .st-on-hold, #myModal_lg .st-draft { background: #f1f3f5; color: #475569; }
#myModal_lg .st-cancelled, #myModal_lg .st-failed { background: #fef2f2; color: #dc2626; }
#myModal_lg .st-refunded, #myModal_lg .st-private { background: #f5f3ff; color: #6d28d9; }
```

### 3b. `assets/js/woocommerce-modern.js` (complete file)

```js
/* WooCommerce modern list toolbar — companion to woocommerce_modern.css.
   Wires the .list-toolbar controls to the DataTables API created by
   fire_datatable() (buttons array order: 0 print, 1 print-selected, 2 excel,
   3 csv, 4 pdf, 5 custom-bulk-button). */
var WOO = { status: '', store: '', table: null };

WOO.bind = function (api) {
    WOO.table = api;

    var btnMap = {
        'woo-print': 0,
        'woo-sel': 1,
        'woo-xls': 2,
        'woo-csv': 3,
        'woo-pdf': 4,
        'woo-del': 5
    };
    Object.keys(btnMap).forEach(function (cls) {
        $('.' + cls).off('click.woo').on('click.woo', function (e) {
            e.preventDefault();
            var node = api.button(btnMap[cls]).node();
            if (node) {
                node.click(); // native click: app.js bulk handler reads window.event
            }
        });
    });

    var timer;
    $('.woo-q').off('input.woo').on('input.woo', function () {
        var value = this.value;
        clearTimeout(timer);
        timer = setTimeout(function () { api.search(value).draw(); }, 300);
    }).off('keydown.woo').on('keydown.woo', function (e) {
        if (e.key === 'Escape') {
            this.value = '';
            api.search('').draw();
        }
    });

    $('.woo-dd').each(function () {
        var wrap = $(this);
        var key = wrap.data('key'); // status | store
        wrap.find('.filter-pill').off('click.woo').on('click.woo', function (e) {
            e.stopPropagation();
            var dd = wrap.find('.dd');
            $('.dd.open').not(dd).removeClass('open');
            dd.toggleClass('open');
        });
        wrap.find('.dd-item').off('click.woo').on('click.woo', function () {
            var item = $(this);
            wrap.find('.dd-item').removeClass('on').find('.ck').html('');
            item.addClass('on').find('.ck').html('&#10003;');
            wrap.find('.pill-label').text(item.data('label'));
            WOO[key] = item.data('v') || '';
            wrap.find('.dd').removeClass('open');
            api.ajax.reload();
        });
    });

    $(document).off('click.wooClose').on('click.wooClose', function () {
        $('.dd.open').removeClass('open');
    });
};
```

### 3c. Language keys (`woocommerce_lang.php`)

Change line 51: `$lang['failed'] = 'failed';` → `$lang['failed'] = 'Failed';`

Append:

```php
// --- WooCommerce redesign: KPI labels ---
$lang['kpi_total_orders'] = 'Total orders';
$lang['kpi_revenue_completed'] = 'Revenue (completed)';
$lang['kpi_pending_processing'] = 'Pending / processing';
$lang['kpi_completed'] = 'Completed';
$lang['kpi_total_products'] = 'Total products';
$lang['kpi_published'] = 'Published';
$lang['kpi_draft'] = 'Draft';
$lang['kpi_total_sales'] = 'Total sales';
$lang['kpi_total_customers'] = 'Total customers';
$lang['kpi_paying'] = 'Paying';
$lang['kpi_active_stores'] = 'Active stores';
$lang['kpi_orders'] = 'Orders';
$lang['kpi_total_stores'] = 'Total stores';
$lang['kpi_synced_orders'] = 'Synced orders';
$lang['kpi_synced_products'] = 'Synced products';
$lang['kpi_synced_customers'] = 'Synced customers';
// --- toolbar / filters ---
$lang['all_statuses'] = 'All statuses';
$lang['all_stores'] = 'All stores';
$lang['search_products'] = 'Search name, SKU, category...';
$lang['search_customers'] = 'Search name, email, phone...';
$lang['search_stores'] = 'Search stores...';
$lang['export_csv'] = 'Export CSV';
$lang['export_excel'] = 'Export Excel';
$lang['export_pdf'] = 'Export PDF';
$lang['print_selected'] = 'Print selected';
// --- statuses used by pills + modal selects (D5) ---
$lang['pending'] = 'Pending';
$lang['draft'] = 'Draft';
// --- misc UI ---
$lang['of'] = 'of';
$lang['synced'] = 'Synced';
$lang['guest_checkout'] = 'Guest checkout';
$lang['back_to_orders'] = 'Back to orders';
$lang['edit_status'] = 'Edit status';
$lang['shipping'] = 'Shipping';
$lang['product_id'] = 'Product ID';
$lang['line_items'] = 'Line items';
$lang['order_totals'] = 'Order totals';
$lang['customer_note'] = 'Customer note';
$lang['billing_address'] = 'Billing address';
$lang['shipping_address'] = 'Shipping address';
$lang['payment'] = 'Payment';
$lang['currency'] = 'Currency';
$lang['phone'] = 'Phone';
$lang['woo_details_unavailable'] = 'Order details are unavailable right now (WooCommerce API unreachable).';
```

Note: `lang('search_all_column')`, `lang('showing')`, `lang('print')`, `lang('preview')`,
`lang('selected')`, `lang('bulk_delete')` already exist globally — reuse, don't duplicate.

### 3d. Helpers (append to `modules/woocommerce/helpers/woocommerce_helper.php`)

```php
if (!function_exists('woo_status_label')) {
    function woo_status_label($status)
    {
        $status = trim((string) $status);
        if ($status === '') {
            return '-';
        }
        $key = str_replace('-', '_', $status);
        $label = lang($key);
        if (is_string($label) && $label !== '' && $label !== $key) {
            return $label;
        }
        if ($status === 'on-hold') {
            return 'On hold';
        }
        return ucfirst($status);
    }
}

if (!function_exists('woo_pill')) {
    function woo_pill($status)
    {
        $status = trim((string) $status);
        if ($status === '') {
            return '-';
        }
        return '<span class="st st-' . htmlspecialchars($status, ENT_QUOTES, 'UTF-8') . '">'
            . woo_status_label($status) . '</span>';
    }
}

if (!function_exists('woo_store_tag')) {
    function woo_store_tag($name)
    {
        $name = trim((string) $name);
        if ($name === '') {
            return '-';
        }
        return '<span class="tag">' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</span>';
    }
}

if (!function_exists('woo_initials')) {
    function woo_initials($name)
    {
        $parts = preg_split('/\s+/', trim((string) $name));
        $initials = mb_strtoupper(mb_substr($parts[0] ?? '?', 0, 1));
        if (!empty($parts[1])) {
            $initials .= mb_strtoupper(mb_substr($parts[1], 0, 1));
        }
        return $initials !== '' ? $initials : '?';
    }
}

if (!function_exists('woo_product_sub')) {
    function woo_product_sub($sku, $category)
    {
        $bits = array();
        $sku = trim((string) $sku);
        if ($sku !== '') {
            $bits[] = $sku;
        }
        $cats = json_decode((string) $category, true);
        if (is_array($cats)) {
            foreach ($cats as $cat) {
                if (!empty($cat['name'])) {
                    $bits[] = $cat['name'];
                }
                if (count($bits) >= 3) {
                    break;
                }
            }
        }
        return implode(' · ', array_slice($bits, 0, 3));
    }
}

if (!function_exists('woo_summary_rows')) {
    function woo_summary_rows()
    {
        static $rows = null;
        if ($rows === null) {
            $CI = &get_instance();
            $rows = $CI->db->get('tbl_woocommerce_summary')->result();
        }
        return $rows;
    }
}

if (!function_exists('woo_summary_blob')) {
    function woo_summary_blob($row, $col)
    {
        if (!in_array($col, array('customers', 'orders', 'products'), true)) {
            return array();
        }
        $val = isset($row->{$col}) ? $row->{$col} : null;
        $decoded = is_string($val) ? json_decode($val, true) : $val;
        return is_array($decoded) ? $decoded : array();
    }
}

if (!function_exists('woo_summary_paying')) {
    function woo_summary_paying()
    {
        $total = 0;
        foreach (woo_summary_rows() as $row) {
            foreach (woo_summary_blob($row, 'customers') as $item) {
                if (isset($item['slug']) && $item['slug'] === 'paying') {
                    $total += (int) (isset($item['total']) ? $item['total'] : 0);
                }
            }
        }
        return $total;
    }
}

if (!function_exists('woo_summary_totals')) {
    function woo_summary_totals($col)
    {
        $total = 0;
        foreach (woo_summary_rows() as $row) {
            foreach (woo_summary_blob($row, $col) as $item) {
                $total += (int) (isset($item['total']) ? $item['total'] : 0);
            }
        }
        return $total;
    }
}

if (!function_exists('woo_store_synced')) {
    function woo_store_synced($store_id, $col)
    {
        foreach (woo_summary_rows() as $row) {
            if ((int) $row->store_id === (int) $store_id) {
                $total = 0;
                foreach (woo_summary_blob($row, $col) as $item) {
                    $total += (int) (isset($item['total']) ? $item['total'] : 0);
                }
                return $total;
            }
        }
        return 0;
    }
}
```

(`$col` values are whitelisted in `woo_summary_blob` — SQL-safe by construction.)

### Verification

```
php -l <path>\modules\woocommerce\helpers\woocommerce_helper.php
php -l <path>\modules\woocommerce\language\english\woocommerce_lang.php
composer smoke
php plan\_tools\woocommerce-redesign-test.php   (still red on page markers — views not yet converted)
```

**Commit:** `feat: add woocommerce modern assets, helpers, lang keys`

---

## Task 4 — Orders page (pilot) + D8/D9

**Files:**
- `modules/woocommerce/controllers/Woocommerce.php` — `orders()` + `orderslist()`
- `modules/woocommerce/views/orders.php` — full rewrite

### 4a. Controller `orders()`

```php
public function orders()
{
    $data['title'] = lang('orders');
    $data['kpi'] = array(
        array('cls' => 'cyan', 'icon' => '&#128722;', 'val' => number_format($this->db->count_all('tbl_woocommerce_orders')), 'label' => lang('kpi_total_orders')),
        array('cls' => 'ok', 'icon' => '&#36;', 'val' => '$' . number_format((float) $this->db->query("SELECT COALESCE(SUM(total + 0), 0) AS s FROM tbl_woocommerce_orders WHERE status = 'completed'")->row()->s, 2), 'label' => lang('kpi_revenue_completed')),
        array('cls' => 'amb', 'icon' => '&#8987;', 'val' => number_format($this->db->where_in('status', array('pending', 'processing', 'on-hold'))->count_all_results('tbl_woocommerce_orders')), 'label' => lang('kpi_pending_processing')),
        array('cls' => 'ok', 'icon' => '&#10003;', 'val' => number_format($this->db->where('status', 'completed')->count_all_results('tbl_woocommerce_orders')), 'label' => lang('kpi_completed')),
    );
    $data['woo_stores'] = $this->db->order_by('store_name', 'ASC')->get('tbl_woocommerce_stores')->result();
    $data['subview'] = $this->load->view('woocommerce/orders', $data, TRUE);
    $this->load->view('admin/_layout_main', $data);
}
```

### 4b. Controller `orderslist()` (full replacement of the method body)

```php
public function orderslist()
{
    if ($this->input->is_ajax_request()) {
        $this->load->model('datatables');
        $this->datatables->table = 'tbl_woocommerce_orders';
        $this->datatables->join_table = array('tbl_woocommerce_customers', 'tbl_woocommerce_stores');
        $this->datatables->join_where = array(
            'tbl_woocommerce_orders.customer_id=tbl_woocommerce_customers.woo_customer_id AND tbl_woocommerce_customers.store_id=tbl_woocommerce_orders.store_id', // D8: scope customer join per store (kills 1913-vs-1496 fan-out)
            'tbl_woocommerce_orders.store_id=tbl_woocommerce_stores.store_id',
        );
        $this->datatables->select = 'tbl_woocommerce_orders.*,tbl_woocommerce_orders.id as orderID,tbl_woocommerce_customers.*,tbl_woocommerce_customers.phone as customer_phone,tbl_woocommerce_stores.store_name as store_name';
        // D9: every column qualified (unqualified `phone` is ambiguous between orders + customers)
        $this->datatables->column_order = array(
            'tbl_woocommerce_orders.order_number',
            'tbl_woocommerce_customers.last_name',
            'tbl_woocommerce_stores.store_name',
            'tbl_woocommerce_orders.total',
            'tbl_woocommerce_orders.status',
            'tbl_woocommerce_orders.date_created',
            'tbl_woocommerce_orders.id',
        );
        $this->datatables->column_search = array(
            'tbl_woocommerce_orders.order_number',
            'tbl_woocommerce_customers.first_name',
            'tbl_woocommerce_customers.last_name',
            'tbl_woocommerce_customers.email',
            'tbl_woocommerce_customers.username',
            'tbl_woocommerce_orders.address',
            'tbl_woocommerce_orders.phone',
            'tbl_woocommerce_orders.status',
            'tbl_woocommerce_orders.total',
            'tbl_woocommerce_orders.date_created',
            'tbl_woocommerce_orders.currency',
            'tbl_woocommerce_stores.store_name',
            'tbl_woocommerce_orders.id',
        );
        $where = array();
        if (!empty($_POST['woo_status'])) {
            $where['tbl_woocommerce_orders.status'] = $_POST['woo_status'];
        }
        if (!empty($_POST['woo_store'])) {
            $where['tbl_woocommerce_orders.store_id'] = $_POST['woo_store'];
        }
        $fetch_data = make_datatables($where);
        $data = array();
        foreach ($fetch_data as $_key => $v_announcements) {
            $e = function ($x) { return htmlspecialchars((string) $x, ENT_QUOTES, 'UTF-8'); };
            $name = trim($v_announcements->first_name . ' ' . $v_announcements->last_name);
            $sub = $v_announcements->email ?: $v_announcements->customer_phone ?: explode(',', $v_announcements->address)[0];
            $sub = trim((string) $sub) !== '' ? $sub : '—';
            $sub_array = array();
            $sub_array[] = '<b class="mono">#' . $e($v_announcements->order_number) . '</b>';
            $sub_array[] = '<div class="cell-main"><div><div class="nm">'
                . $e($name !== '' ? $name : ($v_announcements->email ?: lang('guest_checkout')))
                . '</div><div class="sub">' . $e($sub) . '</div></div></div>';
            $sub_array[] = woo_store_tag($v_announcements->store_name);
            $sub_array[] = '<span class="num">' . $e($v_announcements->currency . ' ' . $v_announcements->total) . '</span>';
            $sub_array[] = woo_pill($v_announcements->status);
            $sub_array[] = '<span class="mono col-soft">' . display_date($v_announcements->date_created) . '</span>';
            $action = '<div class="acts">';
            $action .= '<a href="' . base_url() . 'admin/woocommerce/order/' . $v_announcements->order_id . '"'
                . ' class="btn btn-primary btn-xs" title="' . lang('view') . '"><span class="fa fa-list-alt"></span></a> ';
            $action .= '<a href="' . base_url() . 'admin/woocommerce/edit_orders/' . $v_announcements->orderID . '"'
                . ' class="btn btn-primary btn-xs" title="' . lang('edit') . '" data-toggle="modal"'
                . ' data-target="#myModal_lg"><span class="fa fa-pencil-square-o"></span></a> ';
            $action .= '<a href="' . base_url() . 'admin/woocommerce/delete/orders/' . $v_announcements->order_id . "/" . $v_announcements->id . '"'
                . ' class="btn btn-danger btn-xs" title="' . lang('delete') . '"><span class="fa fa-trash-o"></span></a>';
            $action .= '</div>';
            $sub_array[] = $action;
            $data[] = $sub_array;
        }
        render_table($data, $where);
    } else {
        redirect('admin/dashboard');
    }
}
```

### 4c. `views/orders.php` (complete file)

```php
<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/css/woocommerce_modern.css?v=1">
<div class="woo-modern">
    <h1 class="page-t"><?= lang('orders') ?></h1>

    <div class="kpi-strip">
        <div class="kpi-grid">
            <?php foreach ($kpi as $k) { ?>
                <div class="kpi-card">
                    <div class="k-i <?= $k['cls'] ?>"><?= $k['icon'] ?></div>
                    <div>
                        <div class="v"><?= $k['val'] ?></div>
                        <div class="l"><?= $k['label'] ?></div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <section class="card">
        <div class="list-toolbar">
            <div class="tsearch">
                <span class="ico">&#9906;</span>
                <input type="text" class="woo-q" placeholder="<?= lang('search_all_column') ?>">
            </div>
            <div class="dd-wrap woo-dd" data-key="status">
                <button type="button" class="filter-pill"><span class="c-dot"></span><span
                        class="pill-label"><?= lang('all_statuses') ?></span>&#9662;</button>
                <div class="dd">
                    <div class="dd-item on" data-v="" data-label="<?= lang('all_statuses') ?>"><span class="ck">&#10003;</span><?= lang('all_statuses') ?></div>
                    <?php
                    $statuses = array('pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed');
                    foreach ($statuses as $s) { ?>
                        <div class="dd-item" data-v="<?= $s ?>" data-label="<?= woo_status_label($s) ?>"><span class="ck"></span><?= woo_status_label($s) ?></div>
                    <?php } ?>
                </div>
            </div>
            <div class="dd-wrap woo-dd" data-key="store">
                <button type="button" class="filter-pill"><span class="c-dot"></span><span
                        class="pill-label"><?= lang('all_stores') ?></span>&#9662;</button>
                <div class="dd">
                    <div class="dd-item on" data-v="" data-label="<?= lang('all_stores') ?>"><span class="ck">&#10003;</span><?= lang('all_stores') ?></div>
                    <?php foreach ($woo_stores as $st) { ?>
                        <div class="dd-item" data-v="<?= (int) $st->store_id ?>" data-label="<?= htmlspecialchars($st->store_name, ENT_QUOTES, 'UTF-8') ?>"><span class="ck"></span><?= htmlspecialchars($st->store_name, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php } ?>
                </div>
            </div>
            <div class="tb-right">
                <button type="button" class="icon-btn woo-xls" title="<?= lang('export_excel') ?>"><i class="fa fa-file-excel-o"></i></button>
                <button type="button" class="icon-btn woo-csv" title="<?= lang('export_csv') ?>"><i class="fa fa-download"></i></button>
                <button type="button" class="icon-btn woo-pdf" title="<?= lang('export_pdf') ?>"><i class="fa fa-file-pdf-o"></i></button>
                <button type="button" class="icon-btn woo-print" title="<?= lang('print') ?>"><i class="fa fa-print"></i></button>
                <button type="button" class="icon-btn woo-sel" title="<?= lang('print_selected') ?>"><i class="fa fa-files-o"></i></button>
                <button type="button" class="icon-btn woo-del" title="<?= lang('bulk_delete') ?>"><i class="fa fa-trash-o"></i></button>
            </div>
        </div>
        <div class="tbl-wrap">
            <table class="table table-striped DataTables tbl" id="DataTables" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th><?= lang('order') ?> #</th>
                    <th><?= lang('customer') ?></th>
                    <th><?= lang('stores') ?></th>
                    <th class="t-r"><?= lang('total_spent') ?></th>
                    <th><?= lang('status') ?></th>
                    <th><?= lang('order_date') ?></th>
                    <th class="t-r"><?= lang('action') ?></th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>
</div>
<script type="text/javascript" src="<?= base_url() ?>assets/js/woocommerce-modern.js"></script>
<script type="text/javascript">
    $(function () {
        list = base_url + "admin/woocommerce/orderslist";
        woo_opts = {
            ajaxData: function (d) {
                d.woo_status = WOO.status;
                d.woo_store = WOO.store;
            },
            config: {
                dom: 'lrtip',
                columnDefs: [
                    {targets: [6], orderable: false},
                    {targets: [3, 6], className: 't-r'}
                ],
                oLanguage: {
                    sLengthMenu: "_MENU_",
                    zeroRecords: "<?= lang('nothing_found_sorry') ?>",
                    infoEmpty: "<?= lang('no_record_available') ?>",
                    info: "<?= lang('showing') ?> _START_–_END_ <?= lang('of') ?> _TOTAL_",
                    infoFiltered: ""
                }
            },
            onInit: function (api) {
                WOO.bind(api);
            }
        };
    });
</script>
```

> The en dash in `_START_–_END_` is literal UTF-8 `–` — keep it.

### Verification

```
php plan\_tools\woocommerce-redesign-test.php
```

Orders assertions flip green: page markers/KPI, `orderslist returns healthy modern JSON`
(D8), `orderslist global search works (D9)`, status/store pills, no-leak check stays
green. Products/customers/stores assertions remain red (their tasks). Run `composer
smoke` (controller touched).

**Commit:** `feat: restyle woocommerce orders list`

---

## Task 5 — Products page

**Files:** controller `products()` + `productlist()`; `views/products.php`.

### 5a. `products()`

```php
public function products()
{
    $data['title'] = lang('products');
    $data['active'] = 1;
    $data['kpi'] = array(
        array('cls' => 'cyan', 'icon' => '&#128230;', 'val' => number_format($this->db->count_all('tbl_woocommerce_products')), 'label' => lang('kpi_total_products')),
        array('cls' => 'ok', 'icon' => '&#10003;', 'val' => number_format($this->db->where('status', 'publish')->count_all_results('tbl_woocommerce_products')), 'label' => lang('kpi_published')),
        array('cls' => 'amb', 'icon' => '&#9998;', 'val' => number_format($this->db->where('status', 'draft')->count_all_results('tbl_woocommerce_products')), 'label' => lang('kpi_draft')),
        array('cls' => 'pur', 'icon' => '&#36;', 'val' => number_format((float) $this->db->query("SELECT COALESCE(SUM(sales + 0), 0) AS s FROM tbl_woocommerce_products")->row()->s), 'label' => lang('kpi_total_sales')),
    );
    $data['woo_stores'] = $this->db->order_by('store_name', 'ASC')->get('tbl_woocommerce_stores')->result();
    $data['subview'] = $this->load->view('woocommerce/products', $data, TRUE);
    $this->load->view('admin/_layout_main', $data);
}
```

### 5b. `productlist()`

```php
public function productlist()
{
    if ($this->input->is_ajax_request()) {
        $this->load->model('datatables');
        $this->datatables->table = 'tbl_woocommerce_products';
        $this->datatables->join_table = array('tbl_woocommerce_stores');
        $this->datatables->join_where = array('tbl_woocommerce_products.store_id=tbl_woocommerce_stores.store_id');
        $this->datatables->select = 'tbl_woocommerce_products.*,tbl_woocommerce_stores.store_name as store_name';
        $this->datatables->column_order = array(
            'tbl_woocommerce_products.name',
            'tbl_woocommerce_products.price',
            'tbl_woocommerce_products.sales',
            'tbl_woocommerce_products.status',
            'tbl_woocommerce_stores.store_name',
            'tbl_woocommerce_products.id',
        );
        $this->datatables->column_search = array(
            'tbl_woocommerce_products.name',
            'tbl_woocommerce_products.sku',
            'tbl_woocommerce_products.category',
            'tbl_woocommerce_products.status',
            'tbl_woocommerce_products.price',
            'tbl_woocommerce_products.sales',
            'tbl_woocommerce_stores.store_name',
            'tbl_woocommerce_products.id',
        );
        $where = array();
        if (!empty($_POST['woo_status'])) {
            $where['tbl_woocommerce_products.status'] = $_POST['woo_status'];
        }
        if (!empty($_POST['woo_store'])) {
            $where['tbl_woocommerce_products.store_id'] = $_POST['woo_store'];
        }
        $fetch_data = make_datatables($where);
        $data = array();
        foreach ($fetch_data as $_key => $v_announcements) {
            $e = function ($x) { return htmlspecialchars((string) $x, ENT_QUOTES, 'UTF-8'); };
            $sub_array = array();
            $sub_array[] = '<div class="cell-main">'
                . '<img class="thumb" src="' . $e($v_announcements->picture) . '" alt=""'
                . ' onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'grid\'">'
                . '<div class="thumb-fb">' . woo_initials($v_announcements->name) . '</div>'
                . '<div><div class="nm">' . $e($v_announcements->name) . '</div>'
                . '<div class="sub">' . $e(woo_product_sub($v_announcements->sku, $v_announcements->category)) . '</div></div>'
                . '</div>';
            $sub_array[] = '<span class="num">' . display_money($v_announcements->price, '$') . '</span>';
            $sub_array[] = '<span class="num col-soft">' . number_format((int) $v_announcements->sales) . '</span>';
            $sub_array[] = woo_pill($v_announcements->status);
            $sub_array[] = woo_store_tag($v_announcements->store_name);
            $action = '<div class="acts">';
            $action .= '<a target="_blank" href="' . $e($v_announcements->permalink) . '"'
                . ' class="btn btn-primary btn-xs" title="' . lang('view') . '"><span class="fa fa-list-alt"></span></a> ';
            $action .= '<a href="' . base_url() . 'woocommerce/edit_products/' . $v_announcements->id . '"'
                . ' class="btn btn-primary btn-xs" title="' . lang('edit') . '" data-toggle="modal"'
                . ' data-target="#myModal_lg"><span class="fa fa-pencil-square-o"></span></a> ';
            $action .= '<a href="' . base_url() . 'woocommerce/delete/products/' . $v_announcements->product_id . '/' . $v_announcements->id . '"'
                . ' class="btn btn-danger btn-xs" title="' . lang('delete') . '"><span class="fa fa-trash-o"></span></a>';
            $action .= '</div>';
            $sub_array[] = $action;
            $data[] = $sub_array;
        }
        render_table($data, $where);
    } else {
        redirect('admin/dashboard');
    }
}
```

### 5c. `views/products.php`

Same skeleton as orders (Task 4c) with:

- `h1` → `lang('products')`
- search placeholder → `lang('search_products')`
- status dropdown values → `publish, draft, pending, private` (labels via `woo_status_label`)
- thead: `<th>Product</th><th class="t-r">Price</th><th class="t-r">Sales</th><th>Status</th><th>Store</th><th class="t-r">Action</th>` (use `lang('name')`, `lang('price')`, `lang('sales')`, `lang('status')`, `lang('stores')`, `lang('action')`)
- `list = base_url + "admin/woocommerce/productlist";` (D4 — normalized `admin/` prefix)
- `columnDefs: [{targets: [5], orderable: false}, {targets: [1, 2, 5], className: 't-r'}]`
- same `ajaxData`/`oLanguage`/`onInit` block as orders

### Verification

Product assertions flip green (`productlist … JSON`, draft pill, no-match search).
`composer smoke`.

**Commit:** `feat: restyle woocommerce products list`

---

## Task 6 — Customers page + D1/D3

**Files:** controller `customers()` + `customerlist()`; `viewsCustomers.php`.

### 6a. `customers()`

```php
public function customers()
{
    $data['title'] = lang('customers');
    $data['kpi'] = array(
        array('cls' => 'cyan', 'icon' => '&#128101;', 'val' => number_format($this->db->count_all('tbl_woocommerce_customers')), 'label' => lang('kpi_total_customers')),
        array('cls' => 'ok', 'icon' => '&#10003;', 'val' => number_format(woo_summary_paying()), 'label' => lang('kpi_paying')),
        array('cls' => 'amb', 'icon' => '&#127978;', 'val' => number_format($this->db->count_all('tbl_woocommerce_stores')), 'label' => lang('kpi_active_stores')),
        array('cls' => 'pur', 'icon' => '&#128230;', 'val' => number_format($this->db->count_all('tbl_woocommerce_orders')), 'label' => lang('kpi_orders')),
    );
    $data['woo_stores'] = $this->db->order_by('store_name', 'ASC')->get('tbl_woocommerce_stores')->result();
    $data['subview'] = $this->load->view('woocommerce/customers', $data, TRUE);
    $this->load->view('admin/_layout_main', $data);
}
```

### 6b. `customerlist()`

```php
public function customerlist()
{
    if ($this->input->is_ajax_request()) {
        $this->load->model('datatables');
        $this->datatables->table = 'tbl_woocommerce_customers';
        $this->datatables->join_table = array('tbl_woocommerce_stores');
        $this->datatables->join_where = array('tbl_woocommerce_customers.store_id=tbl_woocommerce_stores.store_id');
        $this->datatables->select = 'tbl_woocommerce_customers.*,tbl_woocommerce_stores.store_name as store_name';
        // D1: real column is `phone` (phone_number does not exist)
        $this->datatables->column_order = array(
            'tbl_woocommerce_customers.last_name',
            'tbl_woocommerce_customers.email',
            'tbl_woocommerce_customers.phone',
            'tbl_woocommerce_stores.store_name',
            'tbl_woocommerce_customers.id',
            'tbl_woocommerce_customers.id',
        );
        $this->datatables->column_search = array(
            'tbl_woocommerce_customers.username',
            'tbl_woocommerce_customers.first_name',
            'tbl_woocommerce_customers.last_name',
            'tbl_woocommerce_customers.phone',
            'tbl_woocommerce_customers.email',
            'tbl_woocommerce_stores.store_name',
            'tbl_woocommerce_customers.id',
        );
        $where = array();
        if (!empty($_POST['woo_store'])) {
            $where['tbl_woocommerce_customers.store_id'] = $_POST['woo_store'];
        }
        $fetch_data = make_datatables($where);
        $data = array();
        foreach ($fetch_data as $_key => $v_announcements) {
            $e = function ($x) { return htmlspecialchars((string) $x, ENT_QUOTES, 'UTF-8'); };
            $nm = trim($v_announcements->first_name . ' ' . $v_announcements->last_name);
            if (!empty($v_announcements->avatar_url)) {
                $av = '<img class="av" src="' . $e($v_announcements->avatar_url) . '" alt=""'
                    . ' onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'grid\'">'
                    . '<div class="av-fb">' . woo_initials($nm) . '</div>';
            } else {
                $av = '<span class="av av-ini">' . woo_initials($nm) . '</span>';
            }
            $ordersCount = (int) $this->db->where('customer_id', $v_announcements->woo_customer_id)
                ->count_all_results('tbl_woocommerce_orders');
            $sub_array = array();
            $sub_array[] = '<div class="cell-main">' . $av
                . '<div><div class="nm">' . $e($nm !== '' ? $nm : $v_announcements->username) . '</div>'
                . '<div class="sub">' . $e($v_announcements->username) . '</div></div></div>';
            $sub_array[] = '<span class="col-soft">' . $e($v_announcements->email) . '</span>';
            $sub_array[] = '<span class="mono col-soft">' . ($v_announcements->phone !== '' && $v_announcements->phone !== null ? $e($v_announcements->phone) : '—') . '</span>';
            $sub_array[] = woo_store_tag($v_announcements->store_name);
            $sub_array[] = '<span class="num">' . number_format($ordersCount) . '</span>';
            $action = '<div class="acts">';
            $action .= '<a href="' . base_url() . 'woocommerce/edit_customer/' . $v_announcements->id . '"'
                . ' class="btn btn-primary btn-xs" title="' . lang('edit') . '" data-toggle="modal"'
                . ' data-target="#myModal_lg"><span class="fa fa-pencil-square-o"></span></a> ';
            $action .= '<a href="' . base_url() . 'woocommerce/delete/customers/' . $v_announcements->woo_customer_id . '/' . $v_announcements->id . '"'
                . ' class="btn btn-danger btn-xs" title="' . lang('delete') . '"><span class="fa fa-trash-o"></span></a>';
            $action .= '</div>';
            $sub_array[] = $action;
            $data[] = $sub_array;
        }
        render_table($data, $where);
    } else {
        redirect('admin/dashboard');
    }
}
```

### 6c. `views/customers.php`

Same skeleton with:

- drop the dead `can_action()` lines (D3)
- `h1` → `lang('customers')`; search placeholder → `lang('search_customers')`
- **store dropdown only** (no status)
- thead (6): Customer / Email / Phone / Store / Orders(t-r) / Action(t-r)
- `list = base_url + "admin/woocommerce/customerlist";` (D4)
- `columnDefs: [{targets: [4, 5], orderable: false}, {targets: [4, 5], className: 't-r'}]`

### Verification

Customer assertions green (JSON markers `av`/`nm`/`acts`, D1 no-match search, store
filter). `composer smoke`.

**Commit:** `feat: restyle woocommerce customers list`

---

## Task 7 — Stores page + D6/D10

**Files:** controller `stores()` + `storeslist()` + `delete_stores()` (D7 raw message);
`views/stores.php`.

### 7a. `stores()`

```php
public function stores($id = null)
{
    $data['title'] = lang('stores');
    $data['kpi'] = array(
        array('cls' => 'cyan', 'icon' => '&#127978;', 'val' => number_format($this->db->count_all('tbl_woocommerce_stores')), 'label' => lang('kpi_total_stores')),
        array('cls' => 'ok', 'icon' => '&#128722;', 'val' => number_format(woo_summary_totals('orders')), 'label' => lang('kpi_synced_orders')),
        array('cls' => 'amb', 'icon' => '&#128230;', 'val' => number_format(woo_summary_totals('products')), 'label' => lang('kpi_synced_products')),
        array('cls' => 'pur', 'icon' => '&#128101;', 'val' => number_format(woo_summary_totals('customers')), 'label' => lang('kpi_synced_customers')),
    );
    $data['subview'] = $this->load->view('woocommerce/stores', $data, TRUE);
    $this->load->view('admin/_layout_main', $data);
}
```

### 7b. `storeslist()`

```php
public function storeslist()
{
    if ($this->input->is_ajax_request()) {
        $this->load->model('datatables');
        $this->datatables->table = 'tbl_woocommerce_stores';
        // D10: `assignees` is not a column — it broke every stores search
        $this->datatables->column_order = array(
            'tbl_woocommerce_stores.store_name',
            'tbl_woocommerce_stores.store_id',
            'tbl_woocommerce_stores.store_id',
            'tbl_woocommerce_stores.date_created',
            'tbl_woocommerce_stores.store_id',
        );
        $this->datatables->column_search = array(
            'tbl_woocommerce_stores.store_name',
            'tbl_woocommerce_stores.url',
            'tbl_woocommerce_stores.date_created',
            'tbl_woocommerce_stores.store_id',
        );
        $where = array();
        $fetch_data = make_datatables($where);
        $data = array();
        foreach ($fetch_data as $_key => $v_announcements) {
            $e = function ($x) { return htmlspecialchars((string) $x, ENT_QUOTES, 'UTF-8'); };
            $userDetail = join_data('tbl_woocommerce_assigned', 'tbl_account_details.fullname', array('tbl_woocommerce_assigned.store_id' => $v_announcements->store_id), array('tbl_account_details' => 'tbl_account_details.user_id=tbl_woocommerce_assigned.user_id'), 'object');
            $asg = '<div class="assignees">';
            if (!empty($userDetail)) {
                foreach ($userDetail as $value) {
                    $asg .= '<span class="asg">' . $e($value->fullname) . '</span>';
                }
            }
            $asg .= '</div>';
            $synced = '<div class="synced">'
                . '<span><b>' . number_format(woo_store_synced($v_announcements->store_id, 'orders')) . '</b> ' . lang('orders') . '</span>'
                . '<span><b>' . number_format(woo_store_synced($v_announcements->store_id, 'products')) . '</b> ' . lang('products') . '</span>'
                . '<span><b>' . number_format(woo_store_synced($v_announcements->store_id, 'customers')) . '</b> ' . lang('customers') . '</span>'
                . '</div>';
            $action = '<div class="acts">';
            $action .= '<a data-id=' . $v_announcements->store_id . ' href="" onclick="updateWooStore(this)"'
                . ' class="btn btn-primary btn-xs" title="' . lang('check_update') . '" data-toggle="modal"'
                . '><span class="fa fa-refresh"></span></a> ';
            $action .= '<a data-id=' . $v_announcements->store_id . ' href="" onclick="wooco_test(this)"'
                . ' class="btn btn-info btn-xs" title="' . lang('test_connection') . '" data-toggle="modal"'
                . '><span class="fa fa-play"></span></a> ';
            $action .= '<a href="' . base_url() . 'admin/woocommerce/new_stores/' . $v_announcements->store_id . '"'
                . ' class="btn btn-primary btn-xs" title="' . lang('edit') . '" data-toggle="modal"'
                . ' data-target="#myModal_lg"><span class="fa fa-pencil-square-o"></span></a> ';
            $action .= '<a data-id=' . $v_announcements->store_id . ' href="" onclick="woo_reset(this)"'
                . ' class="btn btn-warning btn-xs" title="' . lang('reset') . '" data-toggle="modal"'
                . '><span class="fa fa-recycle"></span></a> ';
            $action .= ajax_anchor(base_url('admin/woocommerce/delete_stores/' . $v_announcements->store_id), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . $_key)) . ' ';
            $action .= '</div>';
            $sub_array = array();
            $sub_array[] = '<div class="cell-main"><div><div class="nm">' . $e($v_announcements->store_name) . '</div>'
                . '<div class="sub">' . $e($v_announcements->url) . '</div></div></div>';
            $sub_array[] = $asg;
            $sub_array[] = $synced;
            $sub_array[] = '<span class="mono col-soft">' . display_date($v_announcements->date_created) . '</span>';
            $sub_array[] = $action;
            $data[] = $sub_array;
        }
        render_table($data, $where);
    } else {
        redirect('admin/dashboard');
    }
}
```

Also in the controller: `delete_stores()` — change
`$message = "no_record_found";` → `$message = lang('no_record_found');` (D7).

### 7c. `views/stores.php`

```php
<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/css/woocommerce_modern.css?v=1">
<div class="woo-modern">
    <h1 class="page-t"><?= lang('all') ?> <?= lang('stores') ?></h1>

    <div class="kpi-strip">
        <div class="kpi-grid">
            <?php foreach ($kpi as $k) { ?>
                <div class="kpi-card">
                    <div class="k-i <?= $k['cls'] ?>"><?= $k['icon'] ?></div>
                    <div><div class="v"><?= $k['val'] ?></div><div class="l"><?= $k['label'] ?></div></div>
                </div>
            <?php } ?>
        </div>
    </div>

    <section class="card">
        <div class="list-toolbar">
            <div class="tsearch">
                <span class="ico">&#9906;</span>
                <input type="text" class="woo-q" placeholder="<?= lang('search_stores') ?>">
            </div>
            <div class="tb-right">
                <button type="button" class="icon-btn woo-xls" title="<?= lang('export_excel') ?>"><i class="fa fa-file-excel-o"></i></button>
                <button type="button" class="icon-btn woo-csv" title="<?= lang('export_csv') ?>"><i class="fa fa-download"></i></button>
                <button type="button" class="icon-btn woo-pdf" title="<?= lang('export_pdf') ?>"><i class="fa fa-file-pdf-o"></i></button>
                <button type="button" class="icon-btn woo-print" title="<?= lang('print') ?>"><i class="fa fa-print"></i></button>
                <button type="button" class="icon-btn woo-sel" title="<?= lang('print_selected') ?>"><i class="fa fa-files-o"></i></button>
                <button type="button" class="icon-btn woo-del" title="<?= lang('bulk_delete') ?>"><i class="fa fa-trash-o"></i></button>
                <a href="<?= base_url() ?>woocommerce/new_stores" class="btn-new" data-toggle="modal"
                   data-target="#myModal_lg"><i class="fa fa-plus"></i> <?= lang('new') ?> <?= lang('stores') ?></a>
            </div>
        </div>
        <div class="tbl-wrap">
            <table class="table table-striped DataTables tbl" id="DataTables" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th><?= lang('store_name') ?></th>
                    <th><?= lang('assigned_to') ?></th>
                    <th><?= lang('synced') ?></th>
                    <th><?= lang('date_created') ?></th>
                    <th class="t-r"><?= lang('action') ?></th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>
</div>
<script type="text/javascript" src="<?= base_url() ?>assets/js/woocommerce-modern.js"></script>
<script type="text/javascript">
    $(function () {
        list = base_url + "admin/woocommerce/storeslist";
        woo_opts = {
            ajaxData: function (d) {
                d.woo_store = WOO.store;
            },
            config: {
                dom: 'lrtip',
                columnDefs: [
                    {targets: [1, 2, 4], orderable: false},
                    {targets: [4], className: 't-r'}
                ],
                oLanguage: {
                    sLengthMenu: "_MENU_",
                    zeroRecords: "<?= lang('nothing_found_sorry') ?>",
                    infoEmpty: "<?= lang('no_record_available') ?>",
                    info: "<?= lang('showing') ?> _START_–_END_ <?= lang('of') ?> _TOTAL_",
                    infoFiltered: ""
                }
            },
            onInit: function (api) {
                WOO.bind(api);
            }
        };
    });
</script>
<script>
    function wooco_test(el) {
        "use strict";
        let woob = $(el);
        let id = woob.data('id');
        $.post(base_url + "admin/woocommerce/test_connection/" + id, {})
            .done(function (response) {
                if (response) {
                    response = JSON.parse(response);
                    if (response.success == true) {
                        toastr['success'](response.message);
                        woob.button('reset');
                    } else {
                        toastr['warning'](response.message);
                        woob.button('reset');
                    }
                }
            });
    }

    function woo_reset(el) {
        "use strict";
        let woob = $(el);
        let id = woob.data('id');
        $.post(base_url + 'admin/woocommerce/reset/' + id, {})
            .done(function (response) {
                toastr['success']('<?= addslashes(lang('woocommerce_reset_successful')) ?>');
                woob.button('reset');
            });
    }

    function updateWooStore(el) {
        "use strict";
        let woob = $(el);
        let id = woob.data('id');
        $.post(base_url + 'admin/woocommerce/refresh/' + id, {})
            .done(function () {
                toastr['success']('<?= addslashes(lang('woocommerce_check_successful')) ?>');
                woob.button('reset');
            });
    }
</script>
```

(D6: hardcoded toast strings → `lang()`.)

### Verification

Stores assertions green (JSON `synced`/`acts`, D10 search). All 4 list pages now pass
their list-side checks; only modal/detail/KPI-page checks may remain (Tasks 8–9).
`composer smoke`.

**Commit:** `feat: restyle woocommerce stores list`

---

## Task 8 — Order detail

**Files:** controller `order()`; `views/order.php` (full rewrite).

### 8a. Controller `order()`

```php
public function order($id = null)
{
    $data['title'] = lang('woocommerce_order');
    $data['orders'] = $this->get_order($id);
    $data['row'] = get_row('tbl_woocommerce_orders', array('order_id' => $id));
    $data['store_name'] = '';
    if (!empty($data['row']->store_id)) {
        $store = $this->woocommerce_model->get_store_id($data['row']->store_id);
        if (!empty($store->store_name)) {
            $data['store_name'] = $store->store_name;
        }
    }
    $data['subview'] = $this->load->view('woocommerce/order', $data, true);
    $this->load->view('admin/_layout_main', $data);
}
```

### 8b. `views/order.php` (complete file)

```php
<?php
$orders = isset($orders) ? $orders : null;
$row = isset($row) ? $row : null;
$apiOk = is_object($orders);

$e = function ($x) { return htmlspecialchars((string) $x, ENT_QUOTES, 'UTF-8'); };
$addr = function ($a) {
    if (!is_object($a)) {
        return '';
    }
    $bits = array();
    foreach (array('address_1', 'address_2', 'city', 'state', 'postcode', 'country') as $f) {
        if (!empty($a->{$f})) {
            $bits[] = $a->{$f};
        }
    }
    return implode(', ', $bits);
};

$bill = ($apiOk && !empty($orders->billing) && is_object($orders->billing)) ? $orders->billing : null;
$ship = ($apiOk && !empty($orders->shipping) && is_object($orders->shipping)) ? $orders->shipping : null;
$billAddr = $addr($bill);
$shipAddr = $addr($ship);
$custName = $bill ? trim($bill->first_name . ' ' . $bill->last_name) : '';
$currencySymbol = ($apiOk && !empty($orders->currency_symbol)) ? $orders->currency_symbol : '';

$lineItems = ($apiOk && !empty($orders->line_items) && is_array($orders->line_items)) ? $orders->line_items : array();
$subTotal = 0.0;
foreach ($lineItems as $item) {
    $subTotal += ((float) $item->quantity) * ((float) $item->price);
}
if (empty($lineItems) && $apiOk && isset($orders->total)) {
    $subTotal = (float) $orders->total;
}

$shipMethod = '';
if ($apiOk && !empty($orders->shipping_lines) && is_array($orders->shipping_lines)
    && isset($orders->shipping_lines[0]->method_title)) {
    $shipMethod = $orders->shipping_lines[0]->method_title;
}
?>
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/css/woocommerce_modern.css?v=1">
<div class="woo-modern" id="sales_details">
    <div class="dt-bar">
        <a href="<?= base_url('admin/woocommerce/orders') ?>" class="mini-btn">&lsaquo; <?= lang('back_to_orders') ?></a>
        <span class="dt-title">#<?= $e($row->order_number) ?></span>
        <?= woo_pill($row->status) ?>
        <span class="dt-when"><?= display_date($row->date_created) ?><?= !empty($store_name) ? ' &middot; ' . $e($store_name) : '' ?></span>
        <div class="tb-right">
            <button type="button" class="btn-ghost" onclick="window.print()"><i class="fa fa-print"></i> <?= lang('print') ?></button>
            <a class="btn-accent" href="<?= base_url('admin/woocommerce/edit_orders/' . $row->id) ?>"
               data-toggle="modal" data-target="#myModal_lg"><?= lang('edit_status') ?></a>
        </div>
    </div>

    <div class="dt-grid">
        <section class="card">
            <div class="card-h">
                <div>
                    <h3><?= lang('customer') ?></h3>
                    <div class="sub"><?= !empty($store_name) ? $e($store_name) : '&mdash;' ?></div>
                </div>
            </div>
            <div class="card-b">
                <div class="kv"><span class="k"><?= lang('name') ?></span><span class="val"><?= $e($custName !== '' ? $custName : ($bill && !empty($bill->email) ? $bill->email : '—')) ?></span></div>
                <div class="kv"><span class="k"><?= lang('email') ?></span><span class="val"><?= $e($bill && !empty($bill->email) ? $bill->email : '—') ?></span></div>
                <div class="kv"><span class="k"><?= lang('phone') ?></span><span class="val"><?= $e($bill && !empty($bill->phone) ? $bill->phone : '—') ?></span></div>
            </div>
        </section>
        <section class="card">
            <div class="card-h"><div><h3><?= lang('billing_details') ?></h3></div></div>
            <div class="card-b">
                <div class="kv"><span class="k"><?= lang('billing_address') ?></span><span class="val"><?= $e($billAddr !== '' ? $billAddr : '—') ?></span></div>
                <div class="kv"><span class="k"><?= lang('payment') ?></span><span class="val"><?= lang('woocommerce') ?></span></div>
                <div class="kv"><span class="k"><?= lang('currency') ?></span><span class="val"><?= $e($row->currency) ?></span></div>
            </div>
        </section>
        <section class="card">
            <div class="card-h"><div><h3><?= lang('shipping_details') ?></h3></div></div>
            <div class="card-b">
                <div class="kv"><span class="k"><?= lang('shipping_address') ?></span><span class="val"><?= $e($shipAddr !== '' ? $shipAddr : '—') ?></span></div>
                <?php if ($shipMethod !== '') { ?>
                    <div class="kv"><span class="k"><?= lang('shipping') ?></span><span class="val"><?= $e($shipMethod) ?></span></div>
                <?php } ?>
            </div>
        </section>
    </div>

    <div class="dt-2">
        <section class="card">
            <div class="card-h">
                <div>
                    <h3><?= lang('line_items') ?></h3>
                    <div class="sub"><?= lang('woocommerce') ?></div>
                </div>
            </div>
            <div class="card-b" style="padding:12px 0 0">
                <?php if (!empty($lineItems)) { ?>
                    <div class="tbl-wrap">
                        <table class="tbl" style="min-width:520px">
                            <thead>
                            <tr>
                                <th><?= lang('name') ?></th>
                                <th class="t-r"><?= lang('qty') ?></th>
                                <th class="t-r"><?= lang('price') ?></th>
                                <th class="t-r"><?= lang('total') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($lineItems as $item) { ?>
                                <tr>
                                    <td><div class="nm" style="max-width:420px"><?= $e($item->name) ?></div></td>
                                    <td class="num"><?= (int) $item->quantity ?></td>
                                    <td class="num"><?= display_money($item->price, $currencySymbol) ?></td>
                                    <td class="num"><?= display_money($item->total, $currencySymbol) ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="empty">
                        <div class="big">&#9776;</div>
                        <b><?= lang('line_items') ?></b>
                        <?= $apiOk ? lang('nothing_found_sorry') : lang('woo_details_unavailable') ?>
                    </div>
                <?php } ?>
            </div>
        </section>
        <section class="card">
            <div class="card-h"><div><h3><?= lang('order_totals') ?></h3></div></div>
            <div class="card-b">
                <div class="tot-row"><span><?= lang('sub_total') ?></span><span class="tv"><?= display_money($subTotal, $currencySymbol) ?></span></div>
                <div class="tot-row"><span><?= lang('discount') ?></span><span class="tv"><?= display_money(!empty($orders->discount_total) ? $orders->discount_total : 0, $currencySymbol) ?></span></div>
                <div class="tot-row"><span><?= lang('shipping') ?></span><span class="tv"><?= display_money(!empty($orders->shipping_total) ? $orders->shipping_total : 0, $currencySymbol) ?></span></div>
                <div class="tot-row"><span><?= lang('tax') ?></span><span class="tv"><?= display_money(!empty($orders->total_tax) ? $orders->total_tax : 0, $currencySymbol) ?></span></div>
                <div class="tot-row grand"><span><?= lang('total') ?></span><span class="tv"><?= display_money(isset($orders->total) ? $orders->total : 0, $currencySymbol) ?></span></div>
            </div>
        </section>
    </div>

    <?php if ($apiOk && !empty($orders->customer_note)) { ?>
        <div class="note-box"><b><?= lang('customer_note') ?></b><?= $e($orders->customer_note) ?></div>
    <?php } ?>
</div>

<?php include_once 'assets/js/sales.php'; ?>
```

Notes:
- `lang('qty')`, `lang('price')`, `lang('total')`, `lang('tax')`, `lang('discount')`,
  `lang('sub_total')`, `lang('name')`, `lang('email')`, `lang('status')` come from the
  global lang — already used by the old view, so they resolve.
- `!empty($orders->…)` guards are safe even when `$orders` is a string (`!empty` on a
  string's property is false without warnings — but `$orders->total` on a string WOULD
  warn in PHP 8: therefore totals use `isset($orders->total) ? … : 0` style, and when
  `$apiOk` is false the expression `isset($orders->total)` is false ⇒ 0 — verified safe
  because `isset()` on an inaccessible property never warns).
  For `$orders->discount_total` etc. use:
  `($apiOk && isset($orders->discount_total)) ? $orders->discount_total : 0` — apply the
  same `$apiOk &&` pattern to shipping_total/total_tax/total in the totals block
  (the `!empty()` shorthand above assumes `$apiOk`; use the explicit form).
- The demo's wrong `lang('discount')` label on the shipping row is replaced by
  `lang('shipping')` (new key).
- `#sales_details` id + `assets/js/sales.php` include kept (print/PDF machinery; verified
  no-op without invoice rows).

### Verification

Detail assertions: `dt-bar`, `dt-grid`, `tot-row`, `note-box` present, no error pattern
(or `SKIP` if the WooCommerce API is unreachable). Run the full test + `composer smoke`.

**Commit:** `feat: restyle woocommerce order detail`

---

## Task 9 — Modals + conditional stylesheet (D5/D7)

**Files:**
- `application/views/admin/_layout_modal_lg.php` — conditional `<link>` (insert after the
  `<style>` block, before the `$dropzone` if):

```php
<?php if (strpos(uri_string(), 'woocommerce') !== false) { ?>
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/css/woocommerce_modern.css?v=1">
<?php } ?>
```

- `modules/woocommerce/views/edit_products.php` — two fixes:
  - line 12: drop the stray unquoted token
    `<?= (!empty($products->product_id) ? $products->product_id : ''); ?>` after
    `action="...update/product/"` (POST `productId` already drives `update()`)
  - line 15: `lang('client')` → `lang('product_id')`
- `modules/woocommerce/views/edit_order.php` — add live pill preview after the status
  `form-group`:

```php
<div class="form-group">
    <label class="col-sm-3 control-label"><?= lang('preview') ?></label>
    <div class="col-sm-8">
        <div class="live-pill">
            <span class="lp-l"><?= lang('status') ?></span>
            <span id="wooPillPreview"></span>
        </div>
    </div>
</div>
```

  and append at the end of the view (no ready wrapper — the script runs when the modal
  HTML is injected, elements already exist):

```html
<script type="text/javascript">
    (function () {
        var labels = <?= json_encode(array(
            'pending' => woo_status_label('pending'),
            'processing' => woo_status_label('processing'),
            'on-hold' => woo_status_label('on-hold'),
            'completed' => woo_status_label('completed'),
            'cancelled' => woo_status_label('cancelled'),
            'refunded' => woo_status_label('refunded'),
            'failed' => woo_status_label('failed'),
        )) ?>;
        var sel = document.getElementById('status');
        if (!sel) {
            return;
        }
        function renderPill() {
            var v = sel.value;
            document.getElementById('wooPillPreview').innerHTML =
                '<span class="st st-' + v + '">' + (labels[v] || v) + '</span>';
        }
        renderPill();
        if (window.jQuery) {
            window.jQuery(sel).on('change', renderPill);
        } else {
            sel.addEventListener('change', renderPill);
        }
    })();
</script>
```

- `new_stores.php` / `edit_customer.php` — no markup changes (restyled purely by the
  `#myModal_lg` CSS; stray `name="tax_rates_id[]"` on the assignees select is harmless
  duplicate attr — leave).
- `delete_stores()` lang fix already done in Task 7.

### Verification

Modal GET assertions green (`new_stores` + `edit_products` CSS + form, fixed action
URL). Then full run:

```
php plan\_tools\woocommerce-redesign-test.php
composer smoke; composer test:menu; composer test:login; composer test:theme
```

All gates green (only possible remaining non-PASS is a detail `SKIP` when the live API
is down — allowed).

**Commit:** `feat: restyle woocommerce modal forms`

---

## Task 10 — Full verification

1. **Automated:**
   ```
   composer test:woo      # all PASS (SKIP only for an unreachable WooCommerce API)
   composer smoke
   composer test:menu
   composer test:login
   composer test:theme
   ```
2. **Regression spot-checks:** GET `admin/invoice/manage_invoice` and
   `admin/calendar` — no `woocommerce_modern.css` link, tables render, no console errors.
3. **Playwright manual pass** (login `forid`/`Admin@123`, demo side-by-side at
   `http://localhost:8788/woocommerce-full.html`, screenshots to `.playwright-mcp/`):
   - Orders / Products / Customers / Stores: KPI strip, toolbar, pills, table skin,
     pagination pills vs demo.
   - Interactions: type in search (300ms debounce filters), pick status pill then store
     pill, clear via Escape, click page 2, click row → select highlight, CSV/Excel/PDF/
     Print buttons fire without console errors, print-selected (select a row first),
     bulk-delete icon → confirm dialog appears (accept → no-op by design).
   - Stores: `+ New store` opens restyled modal; assignees selectpicker looks correct.
   - Order detail: back button, pill, 3-card grid, line items, totals panel, print.
   - Edit-status modal: live pill updates when status changes; save redirects with flash.
   - Console: zero errors/warnings on every page.
4. **Defect checklist:** tick D1–D10 (spec's 7 + D8 fan-out, D9 orders search, D10
   stores search).
5. Commit any doc/test adjustments (`docs: woocommerce redesign verification notes`).

---

## Deviations from spec (recorded)

- **Toolbar is a superset** of demo + spec L58: 6 icons (4 exports + print-selected +
  bulk delete) on every list, plus New store on stores — honors user decision #3
  ("keep everything").
- **Footer guard** uses `typeof list !== 'undefined' && list` (truthy), slightly beyond
  spec's `typeof` wording — fixes a pre-existing `ReferenceError` on list-less pages.
- **`dom: 'lrtip'`** (drops `f` as well as `B`) — spec says "without B"; dropping the
  default filter input is required because the toolbar search replaces it (and the
  default input would be unstyled/unwired). Buttons still auto-init (verified in
  `dataTables.buttons.min.js`).
- **Extra defects fixed beyond the spec's 7:** D8 orders join fan-out (1913 vs 1496),
  D9 orders ambiguous `phone` search (page-dead), D10 stores phantom `assignees` search —
  all three live-probed and each has a red test assertion.
- **E2E order-detail check is soft** (`SKIP`, exit 0) when the live WooCommerce API is
  unreachable — environment, not code.
- **Revenue KPI** renders `'$' + SUM(total)` mixing per-order currencies (demo parity);
  documented limitation, not a new endpoint to normalize currency.
- **CSS loading** follows spec L113 literally: per-view `<link>` **and** the conditional
  modal-layout link (both are needed — remotely fetched modal pages include their own
  `<link>`).
- Task order puts the e2e test first (TDD) rather than the spec's commit grouping.

## Out of scope (do not touch)

Swapped sync cursors (`refresh`/`test_connection` semantics), `woocommerce_cron()`
nonexistent model, `delete()` resolving the store via the products table, `$_POST['order']`
empty-array behaviour in `Datatables::make_query`, real bulk-delete endpoints/checkboxes,
per-currency revenue normalization, non-English translations.
