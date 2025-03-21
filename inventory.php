<?php
require 'session_check.php';

// Only allow Operator & Super Admin
if ($user_role != 1 && $user_role != 2) {
    echo "❌ Access Denied!";
    exit;
}

include 'db.php';

// Fetch data for the Overall Inventory card
$categories_result = $conn->query("SELECT COUNT(*) as total_categories FROM category");
$categories_count = $categories_result->fetch_assoc()['total_categories'];

$subcategories_result = $conn->query("SELECT COUNT(*) as total_subcategories FROM subcategory");
$subcategories_count = $subcategories_result->fetch_assoc()['total_subcategories'];

$groups_result = $conn->query("SELECT COUNT(*) as total_groups FROM itemgroup");
$groups_count = $groups_result->fetch_assoc()['total_groups'];

$total_items_result = $conn->query("SELECT COUNT(*) as total_items FROM item");
$total_items_count = $total_items_result->fetch_assoc()['total_items'];

$rented_items_result = $conn->query("SELECT COUNT(*) as total_rented FROM item WHERE availability = 'Rented'");
$rented_items_count = $rented_items_result->fetch_assoc()['total_rented'];

$damaged_items_result = $conn->query("SELECT COUNT(*) as total_damaged FROM item WHERE availability = 'Damaged'");
$damaged_items_count = $damaged_items_result->fetch_assoc()['total_damaged'];

// Fetch categories
$categories_result = $conn->query("SELECT category_id, category_name FROM category");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Inventory Management</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="styles.css" />   
</head>
<body>
    <div class="sidebar">
      <img src="red_logo.png" alt="Logo" class="logo" />
      <div class="nav-links">
        <a href="dashboard.php" class="nav-link">
          <div class="nav-item">
            <i class="icon"></i>
            <span>Dashboard</span>
          </div>
        </a>
        <a href="inventory.php" class="nav-link">
          <div class="nav-item active">
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
        <div class="navbar">
            <input type="text" class="search-bar" id="searchBar" placeholder="Search active table...">
        </div>
        <div class="Page_with_navbar">

            <div class="card long">
                <h2 class="card-title">Overall Inventory</h2>
                <div class="card-content">
                    <div class="box">
                    <p class="box-title" style="color: #1570ef">Categories</p>
                    <p class="number"><?php echo $categories_count; ?></p>
                    </div>
                    <div class="box">
                    <p class="box-title" style="color: #e19133">Sub Categories</p>
                    <p class="number"><?php echo $subcategories_count; ?></p>
                    </div>
                    <div class="box">
                    <p class="box-title" style="color: #845ebc">Groups</p>
                    <p class="number"><?php echo $groups_count; ?></p>
                    </div>
                    <div class="box">
                    <p class="box-title" style="color: #e19133">Total Items</p>
                    <p class="number"><?php echo $total_items_count; ?></p>
                    </div>
                    <div class="box">
                    <p class="box-title" style="color: #10a760">Rented</p>
                    <p class="number"><?php echo $rented_items_count; ?></p>
                    </div>
                    <div class="box">
                    <p class="box-title" style="color: #da3e33">Damaged</p>
                    <p class="number"><?php echo $damaged_items_count; ?></p>
                    </div>
                </div>
            </div>

            <div class="inventory-card">
                <div class="link-container">
                    <a href="#" id="link1" class="inventory-link active-link">Groups</a>
                    <a href="#" id="link2" class="inventory-link">Items</a>
                    <div class="line"></div>
                </div>

                <!-- Groups Table -->
             
                <div class="table-container" id="table1">
                    <div class="table-header">
                        <span>Groups</span>
                        <div class="header-buttons">
                            <div class="filter-section">
                                <select id="categoryFilter1">
                                    <option value="">Select Category</option>
                                    <?php while ($row = $categories_result->fetch_assoc()) { ?>
                                        <option value="<?= $row['category_id']; ?>"><?= $row['category_name']; ?></option>
                                    <?php } ?>
                                </select>
                                <select id="subcategoryFilter1" disabled>
                                    <option value="">Select Subcategory</option>
                                </select>                              
                            </div>
                            <a href="new_item.php" class="edit-button"> Add Item </a>
                            <button class="download-button">Download</button>                              
                        </div>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Group</th>
                                <th>Quantity</th>
                                <th>Available</th>
                                <th>Rented</th>
                                <th>Damaged</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <div id="table1Pagination" class="pagination"></div>
                </div>

                <!-- Items Table -->
                <div class="table-container" id="table2" style="display: none;">
                    <div class="table-header">
                        <span>Items</span>
                        <div class="header-buttons">
                            <div class="filter-section">
                                <select id="categoryFilter2">
                                    <option value="">Select Category</option>
                                    <?php
                                    $categories_result->data_seek(0);
                                    while ($row = $categories_result->fetch_assoc()) { ?>
                                        <option value="<?= $row['category_id']; ?>"><?= $row['category_name']; ?></option>
                                    <?php } ?>
                                </select>
                                <select id="subcategoryFilter2" disabled>
                                    <option value="">Select Subcategory</option>
                                </select>
                                <select id="groupFilter2" disabled>
                                    <option value="">Select Group</option>
                                </select>
                            </div>
                            <a href="new_item.php" class="edit-button"> Add Item </a>
                            <button class="download-button">Download</button>                              
                        </div>
                    </div>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Availability</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <div id="table2Pagination" class="pagination"></div>                    
                </div>
            </div>
        </div>
    </div>
    <script src="script.js"></script>
    <!-- <script src="script2.js"></script> -->
    <script>
        const table1 = document.getElementById('table1');
        const table2 = document.getElementById('table2');

        setupPagination(table1, document.getElementById('table1Pagination'));
        setupPagination(table2, document.getElementById('table2Pagination'));
    </script>    
</body>
</html>
