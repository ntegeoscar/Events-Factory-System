<?php
// Include the database connection file
include 'db.php';

if ($_GET['action'] == 'subcategories') {
    $categoryId = $_GET['category_id'];
    $query = "SELECT sub_category_id, sub_category_name FROM subcategory WHERE category_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    exit;
}

if ($_GET['action'] == 'groups') {
    $subcategoryId = $_GET['subcategory_id'];
    $query = "SELECT group_id, group_name FROM itemgroup WHERE sub_category_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $subcategoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    exit;
}

if ($_GET['action'] == 'items') {
    $groupId = $_GET['group_id'];
    $query = "SELECT item_id, item_name, model, serial_number FROM item WHERE group_id = ? AND availability = 'Available'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $groupId);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    exit;
}

if ($_GET['action'] == 'auto_select') {
    header('Content-Type: application/json'); // Force JSON response

    $group_id = $_GET['group_id'] ?? null;
    $count = intval($_GET['count']) ?? 1;

    try {
        // Base query for available items
        $query = "SELECT * FROM item WHERE availability = 'Available'";
        if ($group_id) $query .= " AND group_id = $group_id";
        $query .= " LIMIT $count";

        $result = $conn->query($query);
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = [
                'item_id' => $row['item_id'],
                'item_name' => $row['item_name'],
                'model' => $row['model'],
                'serial_number' => $row['serial_number']
            ];
        }

        echo json_encode($items); // Output JSON response
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]); // Gracefully handle errors
    }

    exit;
}
