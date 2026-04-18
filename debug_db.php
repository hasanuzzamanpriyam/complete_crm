<?php
$mysqli = new mysqli("localhost", "root", "", "tic_crm");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$output = "";

$result = $mysqli->query("DESCRIBE tbl_users");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}
$output .= "Columns: " . implode(", ", $columns) . "\n\n";

$result = $mysqli->query("SELECT * FROM tbl_users WHERE username='tic'");
if ($row = $result->fetch_assoc()) {
    $output .= "User 'tic' details:\n";
    foreach ($row as $key => $value) {
        if ($key == 'password') continue;
        $output .= "$key: $value\n";
    }
} else {
    $output .= "User 'tic' not found.\n";
}

file_put_contents("debug_output.txt", $output);
$mysqli->close();
?>
