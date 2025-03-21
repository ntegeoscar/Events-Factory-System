<?php
session_start();
require_once 'config.php';

// Check if user is logged in and has Superoperator privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Superoperator') {
    http_response_code(403);
    exit();
}

if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    header('Content-Type: application/json');
    echo json_encode($user);
}
?> 