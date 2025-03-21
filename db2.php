<?php
// Connect to the database
$host = 'localhost'; // Update as needed
$dbname = 'events_factory_test'; // Update as needed
$user = 'root'; // Update as needed
$pass = ''; // Update as needed

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
