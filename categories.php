<?php
// Include the database connection file
include 'db.php';

// Fetch categories
$query = "SELECT category_id, category_name FROM category";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo '<option value="' . $row['category_id'] . '">' . $row['category_name'] . '</option>';
    }
} else {
    echo '<option value="">No Categories Found</option>';
}

$conn->close();
?>
