<?php
require 'session_check.php';

// Only allow Operator & Super Admin
if ($user_role != 1 && $user_role != 2) {
    echo "❌ Access Denied!";
    exit;
}

include 'db.php';

$categories_result = $conn->query("SELECT category_id, category_name FROM category");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory Management</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Inventory Management</title>
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
          <div class="nav-item active">
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
        
      <div class="inventory-card">
                <div class="link-container">
                    <a href="#" id="link1" class="inventory-link active-link">Items</a>
                    <div class="line"></div>
                </div>

                <!-- Items Table -->
                <div class="table-container" id="table1" >
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
                                <th>Update</th>
                                <th>Delete</th>                                
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <div id="table1Pagination" class="pagination"></div>                    
                </div>
            </div>
      </div>
    </div>
    <script src="script.js"></script>
    <script src="script2.js"></script>
    <script>
        const table1 = document.getElementById('table1');
        setupPagination(table1, document.getElementById('table1Pagination'));
    </script> 
</body>
</html>