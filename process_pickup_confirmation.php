<?php

require 'session_check.php';

// allow all
if ($user_role != 1 && $user_role != 2 && $user_role != 3) {
    echo "❌ Access Denied!";
    exit;
}

// Include the database connection file
include 'db.php';

if (isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    
    // Update order to 'Issued' and set the actual pickup date
    $updateOrder = "UPDATE `order` SET status = 'Issued', actual_pick_up_date = NOW() WHERE order_id = ?";
    $stmt = $conn->prepare($updateOrder);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    // Log each item as issued in rental history
    $itemsQuery = "SELECT item_id FROM item WHERE current_order_id = ?";
    $stmt = $conn->prepare($itemsQuery);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $itemsResult = $stmt->get_result();
    
    while ($item = $itemsResult->fetch_assoc()) {
        $item_id = $item['item_id'];
        // Insert item log into rental history
        $logRental = "INSERT INTO rentalhistory (item_id, order_id, rental_date, created_at) VALUES (?, ?, NOW(), NOW())";
        $stmt = $conn->prepare($logRental);
        $stmt->bind_param("ii", $item_id, $order_id);
        $stmt->execute();
    }

    echo "success:Order confirmed successfully!";
} else {
    echo "error:Order ID not provided.";
}
?>
