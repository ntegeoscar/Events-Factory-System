<?php
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'db.php'; // Your database connection file

    $data = json_decode(file_get_contents("php://input"), true);
    $item_id = $data["item_id"];

    if (!$item_id) {
        echo json_encode(["success" => false]);
        exit;
    }

    // Delete item and its related entries
    $deleteEntries = $conn->prepare("DELETE FROM rentalhistory WHERE item_id = ?");
    $deleteEntries->bind_param("i", $item_id);
    $deleteEntries->execute();

    $deleteItem = $conn->prepare("DELETE FROM item WHERE item_id = ?");
    $deleteItem->bind_param("i", $item_id);
    $deleteSuccess = $deleteItem->execute();

    echo json_encode(["success" => $deleteSuccess]);
}
?>
