<?php
// Include the database connection file
include 'db.php';

// Get the group ID from the URL parameter
$group_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch group details including category and sub-category
$group_sql = "
    SELECT 
        itemgroup.group_name, 
        itemgroup.created_at, 
        itemgroup.updated_at,
        subcategory.sub_category_name,
        category.category_name
    FROM itemgroup
    JOIN subcategory ON itemgroup.sub_category_id = subcategory.sub_category_id
    JOIN category ON subcategory.category_id = category.category_id
    WHERE itemgroup.group_id = $group_id";
$group_result = $conn->query($group_sql);
$group = $group_result->fetch_assoc();

// Fetch all items belonging to the group
$items_sql = "SELECT item_id, item_name, availability, model, serial_number FROM item WHERE group_id = $group_id";
$items_result = $conn->query($items_sql);

// Fetch counts for rented, damaged, and available items
$counts_sql = "
    SELECT 
        SUM(CASE WHEN availability = 'Rented' THEN 1 ELSE 0 END) AS rented_items,
        SUM(CASE WHEN availability = 'Damaged' THEN 1 ELSE 0 END) AS damaged_items,
        SUM(CASE WHEN availability = 'Available' THEN 1 ELSE 0 END) AS available_items,
        COUNT(*) AS total_items
    FROM item WHERE group_id = $group_id";
$counts_result = $conn->query($counts_sql);
$counts = $counts_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Group Details</title>
    <link rel="stylesheet" href="styles.css" />

    <script>
      // JavaScript to retrieve URL parameters and display them
      window.onload = function () {
        // Get URL parameters
        const params = new URLSearchParams(window.location.search);

        // Extract 'id' and 'group_name' parameters
        const id = params.get("id");
        const groupName = params.get("group_name");

        // Set the h1 text to the group name
        if (groupName) {
          document.getElementById("groupTitle").textContent = groupName;
        }

        // Display the extracted values on the page (if needed elsewhere)
        document.getElementById("idValue").textContent = id;
      };
    </script>
  </head>
  <body>
    <!-- Sidebar (common) -->
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

    <!-- Main content -->
    <div class="main-content">
      <!-- Navbar (common) -->
      <div class="navbar">
      <input type="text" class="search-bar" id="searchBar" placeholder="Search active table...">

      </div>

      <!-- White content card -->
      <div class="content-card">
        <!-- Title and buttons -->
        <div class="card-header">
          <h1 id="groupTitle"><?= $group['group_name'] ?></h1>
          <div class="header-buttons">
            <!-- <button class="edit-button">Edit</button> -->
            <button class="download-button">Download</button>
          </div>
        </div>

        <!-- Links for changing content (tabs) -->
        <div class="content-links">
          <a href="#" id="link1" class="inventory-link">Details</a>
          <a href="#" id="link2" class="inventory-link">Items</a>
        </div>

        <hr />

        <!-- Table 1: Group Details -->
        <div class="table-container" id="table1">
          <div class="row">
            <div class="">
              <p class="details_titles">Group Details</p>
              <div class="row">
                  <p class="details_subject" style="margin-right: 10px">Total Items:</p>
                  <p class="details_answer"><?= $counts['total_items'] ?></p>
              </div>
              <div class="row">
                  <p class="details_subject" style="margin-right: 10px">Rented Items:</p>
                  <p class="details_answer"><?= $counts['rented_items'] ?></p>
              </div>
              <div class="row">
                  <p class="details_subject" style="margin-right: 10px">Damaged Items:</p>
                  <p class="details_answer"><?= $counts['damaged_items'] ?></p>
              </div>
              <div class="row">
                  <p class="details_subject" style="margin-right: 10px">Available Items:</p>
                  <p class="details_answer"><?= $counts['available_items'] ?></p>
              </div>
            </div>

            <div class="">
              <p class="details_titles">Extra Group Details</p>
              <div class="row">
                <p class="details_subject" style="margin-right: 10px">Category:</p>
                <p class="details_answer"><?= $group['category_name'] ?></p>
              </div>
              <div class="row">
                <p class="details_subject" style="margin-right: 10px">Sub-Category:</p>
                <p class="details_answer"><?= $group['sub_category_name'] ?></p>
              </div>
            </div>
          </div>
        </div>

        <!-- Table 2: Items in the Group -->
        <div class="table-container" id="table2">
          <div class="table-header">
            <span>Items</span>
            <div class="header-buttons">
              <a href="new_item.php" class="edit-button"> Add Item </a>
              <button class="download-button">Download all</button>
            </div>
          </div>
          <table class="table">
            <thead>
              <tr>
                <th>id</th>
                <th>name</th>
                <th>availability</th>
                <th>Model</th>
                <th>Serial Number</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($item = $items_result->fetch_assoc()) { ?>
              <tr
                onclick="location.href='item_details.php?id=<?= $item['item_id'] ?>&item_name=<?= urlencode($item['item_name']) ?>'">
                <td><?= $item['item_id'] ?></td>
                <td><?= $item['item_name'] ?></td>
                <td><?= $item['availability'] ?></td>
                <td><?= $item['model'] ?></td>
                <td><?= $item['serial_number'] ?></td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
          <div id="table2Pagination" class="pagination"></div>
        </div>
      </div>
    </div>

    <script src="script.js"></script>
    <script>
        const table1 = document.getElementById('table2');

        setupPagination(table1, document.getElementById('table2Pagination'));
    </script>    
  </body>
</html>

<?php
// Close the database connection
$conn->close();
?>
