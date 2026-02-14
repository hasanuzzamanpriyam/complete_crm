<?php
$mysqli = new mysqli("localhost", "root", "", "tic_crm");

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

echo "Querying tbl_users for role_id = 3...\n";
$query = "SELECT user_id, username, facebook_url, instagram_url, x_url, linkedin_url FROM tbl_users WHERE role_id = 3";
$result = $mysqli->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "User ID: " . $row['user_id'] . "\n";
        echo "Username: " . $row['username'] . "\n";
        echo "Facebook: " . $row['facebook_url'] . "\n";
        echo "Instagram: " . $row['instagram_url'] . "\n";
        echo "X: " . $row['x_url'] . "\n";
        echo "LinkedIn: " . $row['linkedin_url'] . "\n";
        echo "--------------------------------------------------\n";
    }
    $result->free();
}
else {
    echo "Error: " . $mysqli->error;
}

$mysqli->close();
?>
