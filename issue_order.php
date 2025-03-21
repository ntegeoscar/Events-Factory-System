<?php

require 'session_check.php';

// allow all
if ($user_role != 1 && $user_role != 2 && $user_role != 3) {
    echo "❌ Access Denied!";
    exit;
}

// Include the database connection file
include 'db.php';

$order_id = $_GET['order_id']; // Fetch order_id from URL

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Issue Order</title>
    <link rel="stylesheet" href="styles.css" />
  </head>
  <body>
    <div class="sidebar">
      <img src="red_logo.png" alt="Logo" class="logo" />
      <div class="nav-links">
      <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] != 3): ?>         
        <a href="dashboard.php" class="nav-link">
          <div class="nav-item">
            <i class="icon"></i>
            <span>Dashboard</span>
          </div>
        </a>
      <?php endif; ?>  

      <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] != 3): ?>         
        <a href="inventory.php" class="nav-link">
          <div class="nav-item">
            <i class="icon"></i>
            <span>Inventory</span>
          </div>
        </a>
      <?php endif; ?>  

      <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] != 3): ?>         
        <a href="reports.php" class="nav-link">
          <div class="nav-item">
            <i class="icon"></i>
            <span>Reports</span>
          </div>
        </a>
      <?php endif; ?>        

      <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] != 3): ?>         
        <a href="requisitions.php" class="nav-link">
          <div class="nav-item">
            <i class="icon"></i>
            <span>Requisitions</span>
          </div>
        </a>
      <?php endif; ?>

        <a href="orders.php" class="nav-link">
          <div class="nav-item active">
            <i class="icon"></i>
            <span>Orders</span>
          </div>
        </a>

      <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] != 3): ?>  
        <a href="Manage_inventory.php" class="nav-link">
          <div class="nav-item">
            <i class="icon"></i>
            <span>Manage Store</span>
          </div>
        </a>
      <?php endif; ?>

      <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] != 3): ?>  
        <a href="settings.php" class="nav-link">
          <div class="nav-item">
            <i class="icon"></i>
            <span>Settings</span>
          </div>
        </a>
      <?php endif; ?>

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
        <input type="text" class="search-bar" placeholder="Search..." />
      </div>
      <div class="Page_with_navbar">
        <div class="inventory-card">
            <div class="table-header">
                <span>Issue Order</span>
            </div>
          <div class="link-container">
            <div class="line"></div>
          </div>

          <div class="table-container" id="table1">
          <form id="orderForm" method="post" action="process_issue.php">
            <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">

              <div class="form-group">
                      <select id="manualItem">
                          <option value="">Select Item</option>
                          <?php
                          // Fetch available items to populate the dropdown
                          $query = "SELECT item_id, item_name, model, serial_number FROM item WHERE availability = 'Available'";
                          $result = mysqli_query($conn, $query);
                          while ($row = mysqli_fetch_assoc($result)) {
                              echo "<option value='" . $row['item_id'] . "'>" . $row['item_name'] . " - " . $row['model'] . " (SN: " . $row['serial_number'] . ")</option>";
                          }
                          ?>
                      </select>
                    <label for="manualItem"><button type="button" style="margin-left: 5px;" class="button-primary" onclick="addItem()">Add Item</button></label>
              </div>

                <div class="form-group"></div>


            <h3>Order Items</h3>
            <table id="orderTable" class="table">
                <thead>
                    <tr>
                        <th>Item ID</th>
                        <th>Item Name</th>
                        <th>Model</th>
                        <th>Serial Number</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Dynamically added items will appear here -->
                </tbody>
            </table>
  <br>
            <button type="submit" type="button" class="button-primary">Submit</button>
        </form>
          </div>
        </div>
      </div>
    </div>
    <script src="script.js"></script>
  </body>
</html>
