<?php
include 'db.php';

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

$subcategories = [];
if ($category_id) {
    $sql = "SELECT sub_category_id, sub_category_name FROM subcategory WHERE category_id = $category_id";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $subcategories[] = $row;
    }
}

echo json_encode($subcategories);
?>
