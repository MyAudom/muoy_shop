<?php
$servername = "db.pxxl.pro:37962";
$username = "user_9e4b4ee8";
$password = "1b1fad3b3f04e880b326487a6c1c2874";
$dbname = "db_59a146df";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>

