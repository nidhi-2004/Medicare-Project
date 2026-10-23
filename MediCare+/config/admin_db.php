
<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "medicare_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Admin database connection failed.");
}

$conn->set_charset("utf8mb4");
?>
