<?php
/**
 * Client redesign gate (TDD companion - written BEFORE implementation).
 *
 * Task 1 scaffold: asserts the client asset pipeline exists (client_modern.css
 * consumes tokens, the _client_assets partial links it). Later tasks APPEND
 * their assertions above the report block — see docs/superpowers/plans/
 * 2026-10-22-client-redesign.md.
 *
 *   php plan/_tools/client-redesign-test.php
 *
 * Exits 0 when all assertions pass, 1 otherwise.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Project root with trailing separator (CLI stand-in for index.php's ROOTPATH).
define('ROOTPATH', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);

/** Lazy PDO handle for later DB assertions (unused by the task-1 scaffold). */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('mysql:host=localhost;dbname=tic_crm', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $pdo;
}

$checks = [];

/** Record one assertion: pass stores null (report prints PASS), fail stores the reason. */
$ok = function (bool $pass, string $name) use (&$checks): void {
    $checks[$name] = $pass ? null : 'assertion failed';
};

/* =====================================================================
 * Task 1: Client asset pipeline + gate scaffold
 * ===================================================================== */
$css = ROOTPATH . 'assets/css/client_modern.css';
$ok(file_exists($css), 'client_modern.css exists');
$ok(strpos(file_get_contents($css), '--c-accent') !== false, 'client css consumes tokens');
$partial = ROOTPATH . 'application/views/admin/client/_client_assets.php';
$ok(strpos(file_get_contents($partial), 'client_modern.css') !== false, 'asset partial links css');

/* ---------------------------------------------------------------------
 * Later tasks append their assertions ABOVE this line, e.g.:
 *   $manage = ROOTPATH . 'application/views/admin/client/manage_clients.php';
 *   $ok(strpos(file_get_contents($manage), 'cl-root') !== false, 'admin manage view has cl-root');
 * --------------------------------------------------------------------- */

/* =====================================================================
 * Task 2: Client redesign language keys
 * ===================================================================== */
$en = ROOTPATH . 'application/language/english/client_lang.php';
$ok(strpos(file_get_contents($en), "client_paid_percentage") !== false, 'client lang keys added');
$ok(strpos(file_get_contents($en), "client_address_and_hosting") !== false, 'client lang keys added (2)');

/* =====================================================================
 * Task 3: Manage clients table + clientList row markup
 * ===================================================================== */
$manage = ROOTPATH . 'application/views/admin/client/manage_client.php';
$ok(strpos(file_get_contents($manage), 'cl-manage') !== false, 'manage view has cl-manage root');
$ctrl = ROOTPATH . 'application/controllers/admin/Client.php';
$ok(strpos(file_get_contents($ctrl), 'cl-chip') !== false, 'clientList emits cl-chip markup');

/* =====================================================================
 * Task 4: Client details shell — KPI cards + client-specific tab rail
 * ===================================================================== */
$details_view = ROOTPATH . 'application/views/admin/client/client_details.php';
$ok(strpos(file_get_contents($details_view), 'cl-details') !== false, 'client details view has cl-details');
$ok(strpos(file_get_contents($details_view), '_client_tabs') !== false, 'client details view loads _client_tabs');
$client_tabs = ROOTPATH . 'application/views/admin/client/_client_tabs.php';
$ok(file_exists($client_tabs) && strpos(file_get_contents($client_tabs), 'cl-side-nav') !== false, 'client tabs partial uses cl-side-nav');

/* =====================================================================
 * Task 5: Details profile tab — hero + info grid on the details pane
 * ===================================================================== */
$details_tab = ROOTPATH . 'application/views/admin/client/details.php';
$ok(strpos(file_get_contents($details_tab), 'cl-profile') !== false, 'client details tab has cl-profile');

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
exit($fail > 0 ? 1 : 0);
