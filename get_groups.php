<?php
include 'db.php';

$sub_category_id = isset($_GET['sub_category_id']) ? intval($_GET['sub_category_id']) : 0;

$groups = [];
if ($sub_category_id) {
    $sql = "SELECT group_id, group_name FROM itemgroup WHERE sub_category_id = $sub_category_id";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $groups[] = $row;
    }
}

echo json_encode($groups);
?>
