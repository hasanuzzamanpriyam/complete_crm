<?php
// Regression test for assets/css/bg-custom.php (A1-3): asserts the two syntax bugs stay fixed, a :root block is emitted, and every var() consumer has a definition.: bg-custom.php needs $this->uri and config_item().
$db = [];
$pdo = new PDO('mysql:host=127.0.0.1;dbname=tic_crm', 'root', '');
foreach ($pdo->query('SELECT config_key, value FROM tbl_config') as $r) {
    $db[$r['config_key']] = $r['value'];
}

function config_item($k)
{
    global $db;
    return isset($db[$k]) ? $db[$k] : '';
}
class StubUri
{
    public $seg;
    public function __construct($seg)
    {
        $this->seg = $seg;
    }
    public function segment($i)
    {
        return $this->seg;
    }
}
class Stub
{
    public $uri;
    public function __construct($seg)
    {
        $this->uri = new StubUri($seg);
    }
    public function render()
    {
        include 'assets/css/bg-custom.php';
    }
}

$mode = isset($argv[1]) ? $argv[1] : 'normal';
ob_start();
(new Stub($mode))->render();
$out = ob_get_clean();

echo "mode: $mode\n";
$bad = [];
$importantCount = preg_match_all('/!important/', $out);
echo "important occurrences: $importantCount\n";
// theme mode: $im is null, so only the two declarations that hardcode
// !important in the source (.sidebar .nav-heading, and the optional
// sidebar_font_size block) may carry it. sidebar_font_size is empty here,
// so exactly 1 is expected.
$expect = $mode === 'theme' ? 1 : 25;
if ($importantCount !== $expect) {
    $bad[] = "mode $mode: expected $expect !important, got $importantCount";
}

echo "bytes: " . strlen($out) . "\n";
$bo = substr_count($out, '{');
$bc = substr_count($out, '}');
echo "braces: $bo open / $bc close\n";
if ($bo !== $bc) {
    $bad[] = "unbalanced braces ($bo/$bc)";
}
if (strpos($out, '!important;;') !== false) {
    $bad[] = 'doubled semicolon remains';
}
if (preg_match('/solid(?!\s)[a-z#0-9]/i', $out)) {
    $bad[] = 'missing space after solid';
}
if (strpos($out, '<?=') !== false || strpos($out, '<?php') !== false) {
    $bad[] = 'raw PHP tag leaked';
}
if (strpos($out, 'inherits;') !== false) {
    $bad[] = 'invalid `color: inherits` remains';
}
if (!preg_match('/:root\s*\{/', $out)) {
    $bad[] = 'no :root block emitted';
}
if (preg_match('/config_item\(/', $out)) {
    $bad[] = 'raw config_item call leaked';
}
$vars = [];
preg_match_all('/var\((--[a-z0-9-]+)\)/', $out, $vm);
$vars = array_unique($vm[1]);
$defined = [];
preg_match_all('/(--[a-z0-9-]+)\s*:/', $out, $dm);
$defined = array_unique($dm[1]);
$undefined = array_diff($vars, $defined);

echo "distinct var() consumers: " . count($vars) . "\n";
echo "distinct vars defined:    " . count($defined) . "\n";
if ($undefined) {
    $bad[] = 'undefined vars: ' . implode(', ', $undefined);
}

echo $bad ? "FAIL: " . implode('; ', $bad) . "\n" : "PASS: defects gone, :root emitted, all vars defined\n";

preg_match('/:root\s*\{[^}]+\}/s', $out, $m);
echo "\n:root block emitted:\n  " . preg_replace('/\n\s*/', "\n  ", $m[0] ?? '(none)') . "\n";

exit($bad ? 1 : 0);
