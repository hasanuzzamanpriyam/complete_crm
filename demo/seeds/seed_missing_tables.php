<?php
// Copy missing reference data from main DB to demo DB
$main = new mysqli('localhost', 'root', '', 'tic_crm');
$demo = new mysqli('localhost', 'root', '', 'tic_crm_demo');

$tables = ['tbl_locales', 'tbl_goal_type', 'tbl_accounts', 'tbl_form'];

foreach ($tables as $table) {
    $demo->query("TRUNCATE TABLE `$table`");
    
    $r = $main->query("SELECT * FROM `$table`");
    if (!$r) { echo "ERROR querying main.$table: " . $main->error . "\n"; continue; }
    
    $cols = [];
    while ($field = $r->fetch_field()) { $cols[] = $field->name; }
    $col_list = '`' . implode('`, `', $cols) . '`';
    
    $count = 0;
    while ($row = $r->fetch_assoc()) {
        $vals = [];
        foreach ($cols as $col) {
            $v = $row[$col];
            if (is_null($v)) {
                $vals[] = 'NULL';
            } else {
                $vals[] = "'" . $demo->real_escape_string($v) . "'";
            }
        }
        $sql = "INSERT INTO `$table` ($col_list) VALUES (" . implode(', ', $vals) . ")";
        if ($demo->query($sql)) {
            $count++;
        } else {
            echo "ERROR: $table insert failed: " . $demo->error . "\n";
        }
    }
    echo "$table: $count rows copied\n";
}
echo "\nDone.\n";
