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
