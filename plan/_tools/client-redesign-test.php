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
/* Sub-tab highlight: Bootstrap 3's tab plugin activates parent('li') inside
 * closest('ul'), but cl-subtabs markup is div > a — panes switched while the
 * active class stayed on General. The shared asset partial must carry the
 * delegated handler that moves it. */
$assets = file_get_contents($partial);
$ok(strpos($assets, '.cl-subtabs') !== false && strpos($assets, 'addEventListener') !== false,
    'shared assets move cl-subtabs active state');
/* Import client page: migrated to demo design — has .cl-tabs bar, .cl-form-card,
 * dropzone, and loads _client_assets. */
$import = ROOTPATH . 'application/views/admin/client/import_client.php';
$ok(file_exists($import) && strpos(file_get_contents($import), 'cl-tabs') !== false, 'import_client has cl-tabs');
$ok(strpos(file_get_contents($import), '_client_assets') !== false, 'import_client loads assets');
$ok(strpos(file_get_contents($import), 'cl-form-card') !== false, 'import_client uses cl-form-card');
$ok(strpos(file_get_contents($import), 'cl-dropzone') !== false, 'import_client has dropzone');
/* Detail subtabs — each migrated to modern .cl-card + .cl-tbl + .cl-sec-head */
$subtabs = [
    'contacts'    => ['cl-contacts', 'cl-sec-head', 'cl-spacer', 'cl-tbl', 'cl-btn-primary', 'cl-avatar'],
    'invoices'    => ['cl-tbl', 'cl-sec-head', 'cl-spacer', 'cl-btn-primary', 'cl-btn-ghost', 'cl-tag-mint'],
    'payments'    => ['cl-tbl', 'cl-sec-head', 'cl-spacer', 'cl-btn-ghost', 'cl-tag-success', 'cl-tag-danger'],
    'notes'       => ['cl-tbl', 'cl-sec-head', 'cl-spacer', 'cl-btn-ghost', 'cl-form-card', 'cl-form-body'],
    'estimates'   => ['cl-tbl', 'cl-sec-head', 'cl-spacer', 'cl-btn-primary', 'cl-btn-ghost', 'cl-tag-mint'],
    'proposals'   => ['cl-tbl', 'cl-sec-head', 'cl-spacer', 'cl-btn-primary', 'cl-btn-ghost', 'cl-tag-success', 'cl-tag-danger'],
    'transactions'=> ['cl-tbl', 'cl-sec-head', 'cl-btn-icon', 'cl-btn-icon danger', 'cl-tag-mint', 'cl-tag-amber'],
    'projects'    => ['cl-tbl', 'cl-sec-head', 'cl-spacer', 'cl-btn-primary', 'cl-tag-success', 'cl-tag-info', 'cl-tag-danger'],
    'tickets'     => ['cl-tbl', 'cl-sec-head', 'cl-spacer', 'cl-btn-primary', 'cl-tag-danger', 'cl-tag-success'],
    'bugs'        => ['cl-tbl', 'cl-sec-head', 'cl-spacer', 'cl-btn-primary', 'cl-tag-danger', 'cl-tag-info', 'cl-tag-purple'],
    'award_point' => ['cl-tbl', 'cl-sec-head', 'cl-spacer', 'cl-money-neg', 'cl-tag-mint'],
    'filemanager' => ['cl-embed-frame', 'elfinder'],
    'map'         => ['cl-form-card', 'cl-embed-frame', 'cl-btn-primary', 'cl-frow'],
];
foreach ($subtabs as $tab => $needles) {
    $path = ROOTPATH . "application/views/admin/client/details/$tab.php";
    $content = file_get_contents($path);
    foreach ($needles as $n) {
        $ok(strpos($content, $n) !== false, "detail subtab $tab has $n");
    }
}

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
$ok(strpos(file_get_contents($en), "new_clients_last_7_days") !== false, 'client lang keys added (3)');
$ok(strpos(file_get_contents($en), "goal_progress") !== false, 'client lang keys added (4)');
$ok(strpos(file_get_contents($en), "this_period") !== false, 'client lang keys added (5)');

/* =====================================================================
 * Task 3: Manage clients table + clientList row markup
 * ===================================================================== */
$manage = ROOTPATH . 'application/views/admin/client/manage_client.php';
$ok(strpos(file_get_contents($manage), 'cl-manage') !== false, 'manage view has cl-manage root');
$ctrl = ROOTPATH . 'application/controllers/admin/Client.php';
$ok(strpos(file_get_contents($ctrl), 'cl-chip') !== false, 'clientList emits cl-chip markup');

/* Goal strip markup integrity: div/span tags must balance inside the block.
 * A single mismatched close tag (e.g. </div> closing a <span>) makes the
 * HTML parser shut div.cl-goal-strip early, dropping cells 2-4 out of the
 * grid so they stack vertically full-width. */
$manage_src = file_get_contents($manage);
$gs_pos = strpos($manage_src, '<div class="cl-card cl-goal-strip">');
/* Block ends at the PHP branch close right after the strip (</div> <?php }). */
$gs_end = $gs_pos !== false ? strpos($manage_src, '<?php }', $gs_pos) : false;
$gs_block = ($gs_pos !== false && $gs_end !== false) ? substr($manage_src, $gs_pos, $gs_end - $gs_pos) : '';
$gs_open_div = substr_count($gs_block, '<div');
$gs_close_div = substr_count($gs_block, '</div>');
$gs_open_span = substr_count($gs_block, '<span');
$gs_close_span = substr_count($gs_block, '</span>');
$ok($gs_pos !== false && $gs_open_div === $gs_close_div && $gs_open_span === $gs_close_span,
    'goal strip block has balanced div/span tags');

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

/* =====================================================================
 * Task 6: Details contacts tab — table + inline form
 * ===================================================================== */
$contacts_tab = ROOTPATH . 'application/views/admin/client/details/contacts.php';
$ok(strpos(file_get_contents($contacts_tab), 'cl-contacts') !== false, 'client contacts tab has cl-contacts');

/* =====================================================================
 * Task 7: Financial detail tabs (invoices, payments, estimates, proposals, transactions)
 * ===================================================================== */
$inv = ROOTPATH . 'application/views/admin/client/details/invoices.php';
$ok(strpos(file_get_contents($inv), 'cl-tbl') !== false, 'invoices tab has cl-tbl');
$pay = ROOTPATH . 'application/views/admin/client/details/payments.php';
$ok(strpos(file_get_contents($pay), 'cl-tbl') !== false, 'payments tab has cl-tbl');
$est = ROOTPATH . 'application/views/admin/client/details/estimates.php';
$ok(strpos(file_get_contents($est), 'cl-tbl') !== false, 'estimates tab has cl-tbl');
$prop = ROOTPATH . 'application/views/admin/client/details/proposals.php';
$ok(strpos(file_get_contents($prop), 'cl-tbl') !== false, 'proposals tab has cl-tbl');
$trans = ROOTPATH . 'application/views/admin/client/details/transactions.php';
$ok(strpos(file_get_contents($trans), 'cl-tbl') !== false, 'transactions tab has cl-tbl');

/* =====================================================================
 * Task 8: Work/misc detail tabs (notes, projects, tickets, bugs, award_point)
 * ===================================================================== */
$notes = ROOTPATH . 'application/views/admin/client/details/notes.php';
$ok(strpos(file_get_contents($notes), 'cl-tbl') !== false, 'notes tab has cl-tbl');
$proj = ROOTPATH . 'application/views/admin/client/details/projects.php';
$ok(strpos(file_get_contents($proj), 'cl-tbl') !== false, 'projects tab has cl-tbl');
$tickets = ROOTPATH . 'application/views/admin/client/details/tickets.php';
$ok(strpos(file_get_contents($tickets), 'cl-tbl') !== false, 'tickets tab has cl-tbl');
$bugs = ROOTPATH . 'application/views/admin/client/details/bugs.php';
$ok(strpos(file_get_contents($bugs), 'cl-tbl') !== false, 'bugs tab has cl-tbl');
$award = ROOTPATH . 'application/views/admin/client/details/award_point.php';
$ok(strpos(file_get_contents($award), 'cl-tbl') !== false, 'award_point tab has cl-tbl');

/* =====================================================================
 * Task 9: Embed tabs (filemanager, map) — shell only
 * ===================================================================== */
$fm = ROOTPATH . 'application/views/admin/client/details/filemanager.php';
$ok(strpos(file_get_contents($fm), 'cl-embed') !== false, 'filemanager tab has cl-embed');
$map = ROOTPATH . 'application/views/admin/client/details/map.php';
$ok(strpos(file_get_contents($map), 'cl-embed') !== false, 'map tab has cl-embed');

/* =====================================================================
 * Task 10: Create/Edit form + quick-create modals + customer-group modal
 * ===================================================================== */
$create = ROOTPATH . 'application/views/admin/client/create_client.php';
$ok(strpos(file_get_contents($create), 'cl-form') !== false, 'create_client has cl-form');
/* Regression: the redesign dropped the <form> open tag in the non-AJAX
 * branch (only </form> remained) — inputs were outside any form, so the
 * Save buttons were dead. Both branches must open a form. */
$ok(substr_count(file_get_contents($create), '<form') >= 2, 'create_client opens form in both ajax and page branches');
/* The demo's 1fr/320px grid belongs to .form-wrap (card + side panel);
 * .cl-root.cl-form without a side panel must stay single column. */
$cl_css = file_get_contents(ROOTPATH . 'assets/css/client_modern.css');
$ok(strpos($cl_css, '.cl-form:has(') !== false, 'cl-form 2-col grid scoped to side panel via :has');
/* Create-client polish: native checkbox hidden + driven switch state,
 * textarea drag constrained to the card, legacy Permission form-group
 * aligned to the field grid, slim footer buttons. */
$ok(strpos($cl_css, '.cl-switch input') !== false, 'cl-switch hides the native checkbox');
$ok(strpos($cl_css, '.cl-switch:has(') !== false, 'cl-switch on-state driven by :checked');
$ok(strpos($cl_css, 'textarea.cl-input') !== false, 'short-note textarea resize constrained');
$ok(strpos($cl_css, '.cl-form-body .form-group') !== false, 'permission block aligned to field grid');
$ok(strpos($cl_css, '.cl-form-foot .cl-btn') !== false, 'form footer buttons slimmed');
/* Regression: the redesign dropped the $permissionL definition before the
 * get_permission() call — the page rendered an "Undefined variable" PHP
 * error box above the Permission block. */
$create_src = file_get_contents($create);
$ok(strpos($create_src, '$permissionL = ') !== false && strpos($create_src, 'get_permission(') !== false,
    'create_client defines $permissionL before get_permission');
$new = ROOTPATH . 'application/views/admin/client/new_client.php';
$ok(strpos(file_get_contents($new), 'cl-form') !== false, 'new_client has cl-form');
$quick = ROOTPATH . 'application/views/admin/client/_modal_quick_add.php';
$ok(strpos(file_get_contents($quick), 'cl-modal') !== false, 'quick_add modal has cl-modal');
$cgroup = ROOTPATH . 'application/views/admin/client/customer_group.php';
$ok(strpos(file_get_contents($cgroup), 'cl-modal') !== false, 'customer_group modal has cl-modal');

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
