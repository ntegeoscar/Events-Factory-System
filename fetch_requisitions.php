<?php
require 'session_check.php';
include 'db.php';

// Set correct content type for AJAX response
header("Content-Type: text/html; charset=UTF-8");

// Get filter value
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$sql = "SELECT r.requisition_id, r.event_id, r.expected_pick_up_date, r.expected_return_date, r.approval_status, r.remarks
        FROM requisition r";

if (!empty($status_filter)) {
    $sql .= " WHERE r.approval_status = '$status_filter'";
}

$sql .= " ORDER BY r.requisition_id DESC";
$result = $conn->query($sql);

// Generate table rows
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr onclick=\"location.href='event_requisition_order_details.php?event_id=" . $row['event_id'] . "'\">";
        echo "<td>" . $row['requisition_id'] . "</td>";
        echo "<td>" . $row['event_id'] . "</td>";
        echo "<td>" . $row['expected_pick_up_date'] . "</td>";
        echo "<td>" . $row['expected_return_date'] . "</td>";
        echo "<td>" . $row['approval_status'] . "</td>";
        echo "<td>" . $row['remarks'] . "</td>";

    if ($_SESSION['role_id'] == 1){

        echo "<td>";
        if ($row['approval_status'] === 'Pending') {
            echo "<a href='assess_requisition.php?requisition_id=" . $row['requisition_id'] . "' class='btn'><button type='button'>Take action</button></a>";
        }
        echo "</td>";
    }        
        echo "</tr>";
    

    }
} else {
    echo "<tr><td colspan='7'>No requisitions found</td></tr>";
}
?>
