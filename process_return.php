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
    $statusUpdates = $_POST['status'];
    $remarks = $_POST['remarks'];

    // Process each item return
    foreach ($statusUpdates as $item_id => $status) {
        $remark = $remarks[$item_id];
        $updateItem = "UPDATE item SET availability = '$status', current_order_id = NULL, remarks = '$remarks[$item_id]' WHERE item_id = '$item_id'";
        mysqli_query($conn, $updateItem);

        // Log in rental history
        $logReturn = "UPDATE rentalhistory SET return_date = CURDATE(), condition_on_return = '$status', remarks ='$remarks[$item_id]' WHERE item_id = '$item_id' and order_id = '$order_id'";
        mysqli_query($conn, $logReturn);
    }

    // Mark order as Completed
    $completeOrder = "UPDATE `order` SET actual_return_date = CURDATE(), status = 'Completed' WHERE order_id = '$order_id'";
    mysqli_query($conn, $completeOrder);

    echo "success:Items returned successfully!";
} else {
    echo "error:Order ID not provided.";
}
?>
