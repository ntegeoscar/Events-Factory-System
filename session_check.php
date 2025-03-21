<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirect to login if not logged in
    exit;
}

// Get user role
$user_role = $_SESSION['role_id'];


// Auto logout after 10 minutes of inactivity
$timeout_duration = 1200; // 1200 seconds = 20 minutes

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: index.php?timeout"); // Redirect with a timeout message
    exit;
}

$_SESSION['last_activity'] = time(); // Update last activity time

?>
