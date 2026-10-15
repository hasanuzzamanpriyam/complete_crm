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
