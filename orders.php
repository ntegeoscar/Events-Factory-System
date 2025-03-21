<?php
require 'session_check.php';

// allow all
if ($user_role != 1 && $user_role != 2 && $user_role != 3) {
    echo "❌ Access Denied!";
    exit;
}

// Include the database connection file
include 'db.php';

$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

// Fetch orders data and join with events for event details
$orders_sql = "
    SELECT o.order_id, e.event_id, e.event_name, r.Items_list, e.responsible_person_name, 
        o.expected_pick_up_date, o.expected_return_date, o.actual_pick_up_date, o.actual_return_date, o.status
    FROM `order` o 
    LEFT JOIN requisition r ON o.requisition_id = r.requisition_id
    LEFT JOIN events e ON r.event_id = e.event_id
";

if (!empty($statusFilter)) {
    $orders_sql .= " WHERE o.status = '$statusFilter'";
}

$orders_sql .= " ORDER BY o.order_id DESC";
$orders_result = $conn->query($orders_sql);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Orders</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
        .alert {
            padding: 10px;
            margin: 15px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .download-dropdown {
            position: relative;
            display: inline-block;
        }

        .download-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            right: 0;
        }

        .download-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .download-content a:hover {
            background-color: #f1f1f1;
        }

        .download-dropdown:hover .download-content {
            display: block;
        }
    </style>
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
      <input type="text" class="search-bar" id="searchBar" placeholder="Search active table...">
      </div>
      <?php if (isset($_GET['message']) && isset($_GET['status'])): ?>
        <div class="alert alert-<?php echo $_GET['status']; ?>">
            <?php echo htmlspecialchars($_GET['message']); ?>
        </div>
      <?php endif; ?>
      <div class="Page_with_navbar">
        <div class="inventory-card">
          <div class="link-container">
            <a href="#" id="link1" class="inventory-link active-link">Orders</a>
            <!-- <a href="#" id="link2" class="inventory-link">Requisitions</a> -->
            <div class="line"></div>
          </div>

          <div class="table-container" id="table1">
            <div class="table-header">
              <span>Events</span>

              <div class="header-buttons">
                  <div class="filter-section">
                  <select id="Order_status_Filter">
                      <option value="" <?php if($statusFilter == '') echo 'selected'; ?>>All Status</option>
                      <option value="Awaiting" <?php if($statusFilter == 'Awaiting') echo 'selected'; ?>>Awaiting</option>
                      <option value="Completed" <?php if($statusFilter == 'Completed') echo 'selected'; ?>>Completed</option>
                      <option value="Overdue" <?php if($statusFilter == 'Overdue') echo 'selected'; ?>>Overdue</option>
                      <option value="Issued" <?php if($statusFilter == 'Issued') echo 'selected'; ?>>Issued</option>
                  </select>
                  </div>                
                <div class="download-dropdown">
                    <button class="download-button">Download ▼</button>
                    <div class="download-content">
                        <a href="download_orders.php?format=excel">Excel</a>
                    </div>
                </div>
              </div>
            </div>
            <table class="table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Event Name</th>
                  <th>Responsible name</th>
                  <th>Expected pickup</th>
                  <th>Expected Return</th>
                  <th>Actual pickup</th>
                  <th>Actual Return</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
                <tbody>
                  <?php if ($orders_result->num_rows > 0) { ?>
                    <?php while($row = $orders_result->fetch_assoc()) { ?>
                      <tr onclick="location.href='event_requisition_order_details.php?event_id=<?php echo $row['event_id']; ?>'">
                        <td><?php echo $row['order_id']; ?></td>
                        <td><?php echo $row['event_name']; ?></td>
                        <!-- <?php echo $row['Items_list']; ?> -->
                        <td><?php echo $row['responsible_person_name']; ?></td>
                        <td><?php echo $row['expected_pick_up_date']; ?></td>
                        <td><?php echo $row['expected_return_date']; ?></td>
                        <td><?php echo $row['actual_pick_up_date'] ? $row['actual_pick_up_date'] : 'N/A'; ?></td>
                        <td><?php echo $row['actual_return_date'] ? $row['actual_return_date'] : 'N/A'; ?></td>
                        <td><?php echo $row['status']; ?></td>
                        <td> <!-- Action column -->
                          <?php if ($row['status'] == 'Pending') { ?>
                            <a href="issue_order.php?order_id=<?php echo $row['order_id']; ?>" class="btn"><button type="button">Prepare</button></a>
                          <?php } elseif ($row['status'] == 'Awaiting') { ?>
                            <a href="confirm_pickup.php?order_id=<?php echo $row['order_id']; ?>" class="btn"><button type="button">Issue</button></a>
                          <?php } elseif ($row['status'] == 'Issued' || $row['status'] == 'Overdue') { ?>
                            <a href="return_items.php?order_id=<?php echo $row['order_id']; ?>" class="btn"><button type="button">Return</button></a>
                          <?php } elseif ($row['status'] == 'Completed') { ?>
                            <!-- No button for Completed status -->
                          <?php } ?>
                        </td>                  
                      </tr>
                    <?php } ?>
                  <?php } else { ?>
                    <!-- No orders found message -->
                    <tr>
                      <td colspan="7">No orders found</td>
                    </tr>
                  <?php } ?>
                </tbody>

            </table>
            <div id="table1Pagination" class="pagination"></div>
          </div>
        </div>
      </div>
    </div>
    <script src="script.js"></script>
    <script>
        const table1 = document.getElementById('table1');

        setupPagination(table1, document.getElementById('table1Pagination'));
    </script>
  </body>
</html>
