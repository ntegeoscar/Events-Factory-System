<?php

require 'session_check.php';

// Only allow Operator & Super Admin
if ($user_role != 1 && $user_role != 2) {
    echo "❌ Access Denied!";
    exit;
}
// Include the database connection file
include 'db.php';

// Enable error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Fetch Inventory Summary (Available, Rented, Lost, Damaged)
$inventorySummaryQuery = "
    SELECT 
        COUNT(CASE WHEN availability = 'Available' THEN item_id END) AS available,
        COUNT(CASE WHEN availability = 'Lost' THEN item_id END) AS lost,
        COUNT(CASE WHEN availability = 'Rented' THEN item_id END) AS rented,
        COUNT(CASE WHEN availability = 'Damaged' THEN item_id END) AS damaged
    FROM `item`";
$inventorySummary = $conn->query($inventorySummaryQuery)->fetch_assoc();

// Fetch Overdue Items
$overdueItemsQuery = "
    SELECT COUNT(DISTINCT rh.item_id) AS overdue_items
    FROM rentalhistory rh
    JOIN `order` o ON rh.order_id = o.order_id
    WHERE o.status = 'Overdue'";
$overdueItems = $conn->query($overdueItemsQuery)->fetch_assoc()['overdue_items'];

// Fetch Categories/Month Data
$categoriesMonthQuery = "
    SELECT 
        c.category_name,
        MONTH(rh.rental_date) AS month,
        COUNT(DISTINCT i.item_id) AS items_rented
    FROM rentalhistory rh
    JOIN item i ON rh.item_id = i.item_id
    JOIN category c ON i.category_id = c.category_id
    WHERE YEAR(rh.rental_date) = YEAR(CURDATE())
    GROUP BY c.category_name, MONTH(rh.rental_date)";
$categoriesMonth = $conn->query($categoriesMonthQuery);

// Prepare data for the chart
$categoriesChartData = [];
while ($row = $categoriesMonth->fetch_assoc()) {
    $categoriesChartData[$row['category_name']][$row['month']] = $row['items_rented'];
}

// Fetch Items/Month Data
$itemsMonthQuery = "
    SELECT 
        MONTH(rh.rental_date) AS month,
        COUNT(DISTINCT rh.item_id) AS items_rented
    FROM rentalhistory rh
    WHERE YEAR(rh.rental_date) = YEAR(CURDATE())
    GROUP BY MONTH(rh.rental_date)";
$itemsMonth = $conn->query($itemsMonthQuery);

// Prepare data for the items/month chart
$itemsChartData = [];
while ($row = $itemsMonth->fetch_assoc()) {
    $itemsChartData[$row['month']] = $row['items_rented'];
}

// Fetch Top Rented Groups
$topRentedQuery = "
    SELECT 
        ig.group_name,
        COUNT(DISTINCT rh.item_id) AS rented_items,
        SUM(CASE WHEN i.availability = 'Available' THEN 1 ELSE 0 END) AS available_items
    FROM rentalhistory rh
    JOIN item i ON rh.item_id = i.item_id
    JOIN itemgroup ig ON i.group_id = ig.group_id
    WHERE YEAR(rh.rental_date) = YEAR(CURDATE())
    GROUP BY ig.group_name
    ORDER BY rented_items DESC
    LIMIT 3";
$topRented = $conn->query($topRentedQuery);

// Fetch Low Stock Groups
$lowStockQuery = "
    SELECT 
        ig.group_name,
        SUM(CASE WHEN i.availability = 'Available' THEN 1 ELSE 0 END) AS available_items,
        COUNT(i.item_id) AS total_items
    FROM item i
    JOIN itemgroup ig ON i.group_id = ig.group_id
    GROUP BY ig.group_name
    HAVING available_items < (total_items / 3)
    LIMIT 3";
$lowStock = $conn->query($lowStockQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="sidebar">
      <img src="red_logo.png" alt="Logo" class="logo" />
      <div class="nav-links">
        <a href="dashboard.php" class="nav-link">
          <div class="nav-item active">
            <i class="icon"></i>
            <span>Dashboard</span>
          </div>
        </a>
        <a href="inventory.php" class="nav-link">
          <div class="nav-item">
            <i class="icon"></i>
            <span>Inventory</span>
          </div>
        </a>
        <a href="reports.php" class="nav-link">
          <div class="nav-item">
            <i class="icon"></i>
            <span>Reports</span>
          </div>
        </a>
        <a href="requisitions.php" class="nav-link">
          <div class="nav-item">
            <i class="icon"></i>
            <span>Requisitions</span>
          </div>
        </a>
        <a href="orders.php" class="nav-link">
          <div class="nav-item">
            <i class="icon"></i>
            <span>Orders</span>
          </div>
        </a>
        <a href="Manage_inventory.php" class="nav-link">
          <div class="nav-item">
            <i class="icon"></i>
            <span>Manage Store</span>
          </div>
        </a>
        <a href="settings.php" class="nav-link">
          <div class="nav-item">
            <i class="icon"></i>
            <span>Settings</span>
          </div>
        </a>

        <a href="logout.php" class="nav-link">
          <div class="nav-item">
            <i class="icon"></i>
            <span>Log Out</span>
          </div>
        </a>

      </div>
    </div>

<div class="main-content">

    <div class="">
        <!-- Row 1: Inventory Summary -->
        <div class="row">
          <div class="card medium" id="card1">
            <h2>Inventory Summary</h2>
            <div class="card-content">
              <div class="box">
                <i class="icon"></i>
                <p class="number"><?= $inventorySummary['available'] ?? 0 ?></p>
                <p class="description" style="color: #1570ef">Available</p>
              </div>
              <div class="box">
                <i class="icon"></i>
                <p class="number"><?= $inventorySummary['lost'] ?? 0 ?></p>
                <p class="description" style="color: #845ebc">Lost</p>
              </div>
              <div class="box">
                <i class="icon"></i>
                <p class="number"><?= $inventorySummary['damaged'] ?? 0 ?></p>
                <p class="description" style="color: #da3e33">Damaged</p>
              </div>
            </div>
          </div>
          <div class="card small" id="card2">
            <h2>Rent Overview</h2>
            <div class="card-content">
              <div class="box">
                <i class="icon"></i>
                <p class="number"><?= $inventorySummary['rented'] ?? 0 ?></p>
                <p class="description" style="color: #10a760">Rented</p>
              </div>
              <div class="box">
                <i class="icon"></i>
                <p class="number"><?= $overdueItems ?? 0 ?></p>
                <p class="description" style="color: #e19133">Overdue</p>
              </div>
            </div>
          </div>
        </div>
        <!-- Row 3: Charts -->
        <div class="row">
            <div class="card medium" id="card5">
                <h2>Categories/Month</h2>
                <div>
                    <canvas id="lineChart"></canvas>
                </div>
            </div>
            <div class="card medium" id="card6">
                <h2>Items/Month</h2>
                <div>
                    <canvas id="barChart"></canvas>
                </div>
            </div>
        </div>
        <!-- Row 4: Top Rented -->
        <div class="row">
            <div class="card medium" id="card7">
                <h2>
                    Top Rented
                    <a href="#" class="see-all">See All</a>
                </h2>
                <div class="card-content">
                    <table class="table">
                        <tr>
                            <th>Group Name</th>
                            <th>Rented Items</th>
                            <th>Available Items</th>
                        </tr>
                        <?php while ($row = $topRented->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['group_name'] ?></td>
                                <td><?= $row['rented_items'] ?></td>
                                <td><?= $row['available_items'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            </div>
            <div class="card small" id="card8">
                <h2>
                    Low Stock
                    <a href="#" class="see-all">See All</a>
                </h2>
                <div class="card-content">
                    <?php while ($row = $lowStock->fetch_assoc()): ?>
                        <div class="low-stock-box">
                            <div class="row">
                                <p class="group-name"><?= $row['group_name'] ?></p>
                                <p class="remaining">Available: <?= $row['available_items'] ?> / Total: <?= $row['total_items'] ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Pass PHP data to JavaScript for charts
    const categoriesChartData = <?= json_encode($categoriesChartData) ?>;
    const itemsChartData = <?= json_encode($itemsChartData) ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="charts.js"></script>
<script src="script.js"></script>
</body>
</html>
