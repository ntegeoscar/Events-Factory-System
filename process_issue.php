<?php

require 'session_check.php';

// allow all
if ($user_role != 1 && $user_role != 2 && $user_role != 3) {
    echo "❌ Access Denied!";
    exit;
}

// Include the database connection file
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $items = $_POST['items']; // Array of selected items' IDs

    // Fetch event_id from the order’s associated requisition
    $eventQuery = "SELECT e.event_id 
                   FROM `order` o 
                   LEFT JOIN requisition r ON o.requisition_id = r.requisition_id 
                   LEFT JOIN events e ON r.event_id = e.event_id 
                   WHERE o.order_id = ?";
    $stmt = $conn->prepare($eventQuery);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $event_id = $stmt->get_result()->fetch_assoc()['event_id'];

    // Update order status to 'Awaiting'
    $updateOrder = "UPDATE `order` SET status = 'Awaiting' WHERE order_id = '$order_id'";
    mysqli_query($conn, $updateOrder);

    // Update each selected item to mark as rented, associate with the order, and set event_id
    foreach ($items as $item_id) {
        $updateItem = "UPDATE item 
                       SET availability = 'Rented', current_order_id = '$order_id', event_id = '$event_id' 
                       WHERE item_id = '$item_id'";
        mysqli_query($conn, $updateItem);
    }

    echo "Order #$order_id has been issued and is now awaiting pickup.";
} else {
    echo "Invalid request.";
}
?>
