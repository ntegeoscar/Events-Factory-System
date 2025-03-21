<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "events_factory_test";

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>