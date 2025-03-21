<?php

require 'session_check.php';

// Only allow Operator & Super Admin
if ($user_role != 1 && $user_role != 2) {
    echo "❌ Access Denied!";
    exit;
}

// Include the database connection file
include 'db2.php';

// Fetch Overdue Report
$overdue_query = "
    SELECT e.event_name, e.customer, o.expected_return_date, e.responsible_person_name
FROM `order` o
JOIN requisition r ON o.requisition_id = r.requisition_id
JOIN events e ON r.event_id = e.event_id
WHERE o.status = 'overdue' ORDER BY expected_return_date DESC;
;
";
$overdue_data = $conn->query($overdue_query)->fetchAll(PDO::FETCH_ASSOC);

// Fetch Damaged Report
$damaged_query = "
    SELECT i.item_name, i.serial_number, i.model,e.customer, e.event_name, e.responsible_person_name, 
        rh.return_date AS actual_return_date
    FROM rentalhistory rh
    JOIN item i ON i.item_id = rh.item_id
    JOIN `order` o ON rh.order_id = o.order_id
    JOIN requisition r ON o.requisition_id = r.requisition_id
    JOIN events e ON r.event_id = e.event_id    
    WHERE i.availability = 'Damaged' ORDER BY actual_return_date DESC;
";
$damaged_data = $conn->query($damaged_query)->fetchAll(PDO::FETCH_ASSOC);

// Fetch Missing Report
$missing_query = "
    SELECT i.item_name, i.serial_number, i.model, e.customer, e.event_name, e.responsible_person_name, 
       rh.remarks,  rh.return_date AS actual_return_date
FROM rentalhistory rh
JOIN item i ON i.item_id = rh.item_id
JOIN `order` o ON rh.order_id = o.order_id
JOIN requisition r ON o.requisition_id = r.requisition_id
JOIN events e ON r.event_id = e.event_id
WHERE rh.remarks LIKE '%Lost%' OR i.availability = 'Lost'
ORDER BY actual_return_date DESC
LIMIT 0, 25;

";
$missing_data = $conn->query($missing_query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="sidebar">
    <img src="red_logo.png" alt="Logo" class="logo">
    <div class="nav-links">
        <a href="dashboard.php" class="nav-link"><div class="nav-item"><i class="icon"></i><span>Dashboard</span></div></a>
        <a href="inventory.php" class="nav-link"><div class="nav-item"><i class="icon"></i><span>Inventory</span></div></a>
        <a href="reports.php" class="nav-link"><div class="nav-item active"><i class="icon"></i><span>Reports</span></div></a>
        <a href="requisitions.php" class="nav-link"><div class="nav-item"><i class="icon"></i><span>Requisitions</span></div></a>
        <a href="orders.php" class="nav-link"><div class="nav-item"><i class="icon"></i><span>Orders</span></div></a>
        <a href="Manage_inventory.php" class="nav-link"><div class="nav-item"><i class="icon"></i><span>Manage Store</span></div></a>
        <a href="settings.php" class="nav-link"><div class="nav-item"><i class="icon"></i><span>Settings</span></div></a>
                <a href="logout.php" class="nav-link">
          <div class="nav-item">
            <i class="icon"></i>
            <span>Log Out</span>
          </div>
        </a>
    </div>
</div>
<div class="main-content">
    <div class="navbar">
    <input type="text" class="search-bar" id="searchBar" placeholder="Search active table...">
    </div>
    <div class="Page_with_navbar">
        <div class="inventory-card">
            <div class="link-container">
                <a href="#" id="link1" class="inventory-link active-link">Overdue Report</a>
                <a href="#" id="link2" class="inventory-link">Damaged Report</a>
                <a href="#" id="link3" class="inventory-link">Missing Report</a>
                <div class="line"></div>
            </div>

            <!-- Overdue Report -->
            <div class="table-container" id="table1">
                <div class="card-header">
                    <h1 id="groupTitle">Overdue Report</h1>
                    <div class="header-buttons">
                        <button class="download-button" onclick="window.location.href='generate_report_1.php'">Download</button>
                    </div>
                </div>
                <table class="table">
                    <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Expected Return Date</th>
                        <th>Responsible Person</th>
                        <th>Customer</th>
                        <th>Overdue days</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($overdue_data as $row): ?>
                        <tr>
                            <td><?= $row['event_name']; ?></td>
                            <td><?= $row['expected_return_date']; ?></td>
                            <td><?= $row['responsible_person_name']; ?></td>
                            <td><?= $row['customer']; ?></td>
                            <td>
                                <?php
                                $expectedDate = new DateTime($row['expected_return_date']);
                                $today = new DateTime();
                                $interval = $today->diff($expectedDate);
                                echo $interval->days;
                                ?>
                            </td>                            
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div id="table1Pagination" class="pagination"></div>                    
            </div>

            <!-- Damaged Report -->
            <div class="table-container" id="table2">
                <div class="card-header">
                    <h1 id="groupTitle">Damaged Report</h1>
                    <div class="header-buttons">
                        <button class="download-button" onclick="window.location.href='generate_report_2.php'">Download</button>
                    </div>
                </div>
                <table class="table">
                    <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Serial Number</th>
                        <th>Model</th>
                        <th>Event Name</th>
                        <th>Customer</th>
                        <th>Responsible Person</th>
                        <th>Actual Return Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($damaged_data as $row): ?>
                        <tr>
                            <td><?= $row['item_name']; ?></td>
                            <td><?= $row['serial_number']; ?></td>
                            <td><?= $row['model']; ?></td>
                            <td><?= $row['event_name']; ?></td>
                            <td><?= $row['customer']; ?></td>
                            <td><?= $row['responsible_person_name']; ?></td>
                            <td><?= $row['actual_return_date']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div id="table2Pagination" class="pagination"></div>                    
            </div>

            <!-- Missing Report -->
            <div class="table-container" id="table3">
                <div class="card-header">
                    <h1 id="groupTitle">Missing Report</h1>
                    <div class="header-buttons">
                        <button class="download-button" onclick="window.location.href='generate_report_3.php'">Download</button>
                    </div>
                </div>
                <table class="table">
                    <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Serial Number</th>
                        <th>Model</th>
                        <th>Event Name</th>
                        <th>Customer</th>
                        <th>Responsible Person</th>
                        <th>Remarks</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($missing_data as $row): ?>
                        <tr>
                            <td><?= $row['item_name']; ?></td>
                            <td><?= $row['serial_number']; ?></td>
                            <td><?= $row['model']; ?></td>
                            <td><?= $row['event_name']; ?></td>
                            <td><?= $row['customer']; ?></td>
                            <td><?= $row['responsible_person_name']; ?></td>
                            <td><?= $row['remarks']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div id="table3Pagination" class="pagination"></div>                    
            </div>
        </div>
    </div>
</div>
<script src="script.js"></script>
<script>
        const table1 = document.getElementById('table1');
        const table2 = document.getElementById('table2');
        const table3 = document.getElementById('table3');

        setupPagination(table1, document.getElementById('table1Pagination'));
        setupPagination(table2, document.getElementById('table2Pagination'));
        setupPagination(table3, document.getElementById('table3Pagination'));
</script>  
<script>

    function downloadPDF2() {
        const eventId = new URLSearchParams(window.location.search).get('event_id');
        if (eventId) {
            window.location.href = 'generate_pdf.php?event_id=' + eventId;
        } else {
            alert("Event ID is missing!");
        }
    }
    
    function downloadPDF3() {
        const eventId = new URLSearchParams(window.location.search).get('event_id');
        if (eventId) {
            window.location.href = 'generate_pdf.php?event_id=' + eventId;
        } else {
            alert("Event ID is missing!");
        }
    }    
</script>  
</body>
</html>
