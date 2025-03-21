<?php
// Include the database connection file
include 'db.php';

$sub_category_id = $_POST['sub_category_id'];

// Fetch groups for the selected subcategory
$query = "SELECT group_id, group_name FROM itemgroup WHERE sub_category_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $sub_category_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo '<option value="' . $row['group_id'] . '">' . $row['group_name'] . '</option>';
    }
} else {
    echo '<option value="">No Groups Found</option>';
}

$stmt->close();
$conn->close();
?>
