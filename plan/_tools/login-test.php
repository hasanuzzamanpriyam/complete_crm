<?php
/**
 * Login page regression test
 *
 * Guards against the bug where #trackerDownloadModal auto-opened on /login:
 * Bootstrap then set body.modal-open -> overflow:hidden (page could not scroll)
 * and laid a full-viewport z-index:1050 .modal container over the sign-in form,
 * so elementFromPoint() at the Sign-in button returned the modal, not the button.
 *
 * The promo must be rendered NON-modally. This asserts the contract at the
 * HTML level so `composer test:login` catches a regression without a browser.
 *
 *   php plan/_tools/login-test.php [--base=http://localhost/complete_crm]
 *
 * Exits 0 when all assertions pass, 1 otherwise.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$opts = getopt('', ['base::', 'help']);
if (isset($opts['help'])) {
    echo "Usage: php plan/_tools/login-test.php [--base=URL]\n";
    exit(0);
}
$baseUrl = rtrim($opts['base'] ?? 'http://localhost/complete_crm', '/');

// --------------------------------------------------------------- fetch ----

$ch = curl_init($baseUrl . '/login');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_USERAGENT      => 'login-test',
]);
$html     = (string) curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err      = curl_error($ch);
curl_close($ch);

if ($html === '' || $err !== '') {
    fwrite(STDERR, "FATAL: could not fetch {$baseUrl}/login — {$err}\n");
    exit(1);
}

// ------------------------------------------------------------ assertions ----
// Each returns null when it passes, or a reason string when it fails.

$checks = [];

$checks['HTTP 200'] =
    ($httpCode === 200) ? null : "expected 200, got {$httpCode}";

// THE BUG: no Bootstrap modal may be rendered on the login page at all.
// Any element with class="modal" becomes a fixed z-index:1050 full-viewport
// layer that swallows clicks and locks body scroll.
$checks['no Bootstrap modal is rendered on /login'] =
    preg_match('/class="[^"]*\bmodal\b[^"]*"/', $html)
        ? 'found class="modal" — a modal overlays the sign-in form and locks scroll'
        : null;

// THE BUG: no script may auto-open a modal on page load.
$checks['no script auto-opens a modal on /login'] =
    preg_match('/\.modal\(\s*[\'"]show[\'"]\s*\)/', $html)
        ? "found .modal('show') — body gains modal-open and overflow:hidden"
        : null;

// Guard: the sign-in form must survive the fix.
$checks['sign-in form renders user_name field'] =
    preg_match('/name=["\']user_name["\']/', $html) ? null : 'user_name input missing';

$checks['sign-in form renders password field'] =
    preg_match('/name=["\']password["\']/', $html) ? null : 'password input missing';

// Guard: the TimeSync promo itself must survive — this is a relocation, not a
// deletion of the feature.
$checks['TimeSync promo content is still offered'] =
    stripos($html, 'TimeSync') !== false ? null : 'promo content was deleted, not relocated';

// Guard: page must not ship PHP errors.
$checks['no PHP error output'] =
    preg_match('/Fatal error|Parse error|Uncaught\s|<b>Warning\b|<b>Notice\b/i', $html)
        ? 'PHP error text present in the rendered page'
        : null;

// --------------------------------------------------------------- report ----

$fail = 0;
echo str_repeat('=', 76) . "\n";
printf("login-test  %s\n", $baseUrl);
echo str_repeat('=', 76) . "\n";
foreach ($checks as $name => $reason) {
    if ($reason === null) {
        printf("PASS  %s\n", $name);
    } else {
        printf("FAIL  %s\n      -> %s\n", $name, $reason);
        $fail++;
    }
}
echo str_repeat('=', 76) . "\n";
printf("%d assertions — %d passed, %d failed\n", count($checks), count($checks) - $fail, $fail);
exit($fail === 0 ? 0 : 1);
