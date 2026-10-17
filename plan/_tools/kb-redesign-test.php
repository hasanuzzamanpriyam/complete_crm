<?php
/**
 * Knowledge Base redesign gate (TDD companion - written BEFORE implementation).
 *
 * Task 1 scaffold: asserts the KB asset pipeline exists (kb_modern.css consumes
 * tokens, the shared _kb_assets partial links it). Later tasks APPEND their
 * assertions above the report block — see docs/superpowers/plans/
 * 2026-10-15-knowledgebase-redesign.md.
 *
 *   php plan/_tools/kb-redesign-test.php
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
 * Task 1: KB asset pipeline + gate scaffold
 * ===================================================================== */
$css = ROOTPATH . 'assets/css/kb_modern.css';
$ok(file_exists($css), 'kb_modern.css exists');
$ok(strpos(file_get_contents($css), '--c-accent') !== false, 'kb css consumes tokens');
$partial = ROOTPATH . 'application/views/kb_shared/_kb_assets.php';
$ok(file_get_contents($partial, true) !== false && strpos(file_get_contents($partial), 'kb_modern.css') !== false, 'asset partial links css');

$en = ROOTPATH . 'application/language/english/utilities_lang.php';
$ok(strpos(file_get_contents($en), "kb_how_can_we_help") !== false, 'kb lang keys added');

/* =====================================================================
 * Task 3: Admin overview view (kb_list.php)
 * ===================================================================== */
$kb_list = ROOTPATH . 'application/views/admin/kb/kb_list.php';
$kb_list_src = file_get_contents($kb_list);
$ok(strpos($kb_list_src, 'kb-overview') !== false, 'admin kb_list has kb-overview root');
$ok(strpos($kb_list_src, 'kb-hero') !== false, 'admin kb_list has kb-hero');

/* =====================================================================
 * Task 4: Admin category + article details view (articles_details.php)
 * ===================================================================== */
$kb_details_src = file_get_contents(ROOTPATH . 'application/views/admin/kb/articles_details.php');
$ok(strpos($kb_details_src, 'kb-details') !== false && strpos($kb_details_src, 'kb-article') !== false,
    'admin kb articles_details has kb-details + kb-article');

/* =====================================================================
 * Task 5: Admin manage tables + controller row markup
 * ===================================================================== */
$kb_categories_src = file_get_contents(ROOTPATH . 'application/views/admin/kb/categories.php');
$kb_articles_src = file_get_contents(ROOTPATH . 'application/views/admin/kb/articles.php');
$kb_controller_src = file_get_contents(ROOTPATH . 'application/controllers/admin/Knowledgebase.php');
$ok(strpos($kb_categories_src, 'kb-tbl') !== false, 'admin kb categories view has kb-tbl');
$ok(strpos($kb_articles_src, 'kb-tbl') !== false, 'admin kb articles view has kb-tbl');
$ok(strpos($kb_controller_src, 'kb-toggle') !== false, 'kb controller list rows use kb-toggle');
$ok(strpos($kb_controller_src, 'kb-tag') !== false, 'kb controller list rows use kb-tag');

/* =====================================================================
 * Task 6: Admin new/edit article form (new_articles.php)
 * ===================================================================== */
$kb_new_article_src = file_get_contents(ROOTPATH . 'application/views/admin/kb/new_articles.php');
$ok(strpos($kb_new_article_src, 'kb-form') !== false, 'admin kb new_articles has kb-form');
$ok(strpos($kb_new_article_src, '.summernote(') !== false, 'admin kb new_articles inits summernote on description');
$ok(strpos($kb_new_article_src, 'kb-fin select_box') !== false, 'admin kb new_articles category select keeps select_box');

/* =====================================================================
 * Task 7: Admin new/edit category modal (new_categories.php)
 * ===================================================================== */
$kb_new_category_src = file_get_contents(ROOTPATH . 'application/views/admin/kb/new_categories.php');
$ok(strpos($kb_new_category_src, 'kb-modal') !== false, 'admin kb new_categories has kb-modal');

/* =====================================================================
 * Task 8: Admin search box restyle (search_box.php)
 * ===================================================================== */
$kb_search_box_src = file_get_contents(ROOTPATH . 'application/views/admin/kb/search_box.php');
$ok(preg_match('/"class"\s*=>\s*"kb-search/', $kb_search_box_src) === 1,
    'admin search box input uses kb-search marker class');

/* =====================================================================
 * Task 9: Public frontend KB surfaces (frontend/kb/*)
 * ===================================================================== */
foreach (['kb_list', 'articles_details', 'search_box'] as $kb_public_view) {
    $kb_public_src = file_get_contents(ROOTPATH . 'application/views/frontend/kb/' . $kb_public_view . '.php');
    $ok(strpos($kb_public_src, 'kb-public') !== false,
        'public kb ' . $kb_public_view . ' has kb-public marker');
}

/* ---------------------------------------------------------------------
 * Later tasks append their assertions ABOVE this line, e.g.:
 *   $en = ROOTPATH . 'application/language/english/utilities_lang.php';
 *   $ok(strpos(file_get_contents($en), 'kb_how_can_we_help') !== false, 'kb lang keys added');
 * --------------------------------------------------------------------- */

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
