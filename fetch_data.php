<?php
include 'db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$response = [];

if ($action == 'get_subcategories' && isset($_GET['category_id'])) {
    $category_id = intval($_GET['category_id']);
    $query = "SELECT sub_category_id, sub_category_name FROM subcategory WHERE category_id = $category_id";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $response[] = $row;
    }
} elseif ($action == 'get_groups' && isset($_GET['subcategory_id'])) {
    $subcategory_id = intval($_GET['subcategory_id']);

    $query = "SELECT 
            g.group_id, 
            g.group_name, 
            COUNT(i.item_id) AS total_items,
            COUNT(CASE WHEN i.availability = 'Available' THEN 1 END) AS available,
            COUNT(CASE WHEN i.availability = 'Rented' THEN 1 END) AS rented,
            COUNT(CASE WHEN i.availability = 'Damaged' THEN 1 END) AS damaged
        FROM 
            itemgroup g 
        LEFT JOIN 
            item i ON g.group_id = i.group_id 
        WHERE 
            g.sub_category_id = $subcategory_id
        GROUP BY 
            g.group_id, g.group_name;";

    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $response[] = $row;
    }
} elseif ($action == 'get_items' && isset($_GET['group_id'])) {
    $group_id = intval($_GET['group_id']);
    $query = "SELECT item_id, item_name, availability FROM item WHERE group_id = $group_id";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $response[] = $row;
    }
}

echo json_encode($response);
?>
