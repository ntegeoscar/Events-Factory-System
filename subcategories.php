<?php
// Include the database connection file
include 'db.php';

$category_id = $_POST['category_id'];

// Fetch subcategories for the selected category
$query = "SELECT sub_category_id, sub_category_name FROM subcategory WHERE category_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo '<option value="' . $row['sub_category_id'] . '">' . $row['sub_category_name'] . '</option>';
    }
} else {
    echo '<option value="">No Subcategories Found</option>';
}

$stmt->close();
$conn->close();
?>
