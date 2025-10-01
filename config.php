<?php
$servername = "db.pxxl.pro:25543";
$username = "user_f4df9832";
$password = "d540fc259c55d102bd5f787000a82d85";
$dbname = "db_59a146df";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
