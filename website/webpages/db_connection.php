<?php
$host = "localhost";
$username = "root";
$password = ""; // or your actual DB password
$dbname = "test"; // replace with your DB name

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
