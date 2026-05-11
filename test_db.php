<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'tic_crm');
$res = $mysqli->query('SELECT task_id, module, module_field_id, task_status FROM tbl_task WHERE module="domain" AND module_field_id=68 ORDER BY task_id DESC');
while($row = $res->fetch_assoc()){
    print_r($row);
}
?>
