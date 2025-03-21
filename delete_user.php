<?php
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'db.php'; // Your database connection file

    $data = json_decode(file_get_contents("php://input"), true);
    $user_id = $data["user_id"];

    if (!$user_id) {
        echo json_encode(["status" => "error", "message" => "User ID is required"]);
        exit;
    }

    $deleteuser = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $deleteuser->bind_param("i", $user_id);
    $deleteSuccess = $deleteuser->execute();

    if ($deleteSuccess) {
        echo json_encode(["status" => "success", "message" => "User deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete user"]);
    }
}
?>
