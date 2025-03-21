<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'session_check.php';

// Get URL parameters
$event_id = $_GET['event_id'] ?? null;

// Send store keeper to details
if ($user_role == 3) {
    header("Location: store_keeper_details.php?event_id=$event_id");;
    exit;
}

// Only allow Operator & Super Admin
if ($user_role != 1 && $user_role != 2) {
    echo "❌ Access Denied!";
    exit;
}
// Include the database connection file
include 'db.php';



if ($event_id) {
    // Fetch details for event and requisition
    $sql = "SELECT e.*, r.* 
            FROM events e 
            LEFT JOIN requisition r ON e.event_id = r.event_id 
            WHERE e.event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    // Fetch order details (if exists)
    if (!empty($data['requisition_id'])) {
        $sql_order = "SELECT o.* 
                      FROM `order` o 
                      WHERE o.requisition_id = ?";
        $stmt_order = $conn->prepare($sql_order);
        $stmt_order->bind_param("i", $data['requisition_id']);
        $stmt_order->execute();
        $order_result = $stmt_order->get_result();
        $order_data = $order_result->fetch_assoc();
    } else {
        $order_data = null;
    }

    // Fetch items related to the order
    $item_result = [];
    if (!empty($order_data['order_id'])) {
        $item_sql = "SELECT i.item_name, e.event_name, rh.rental_date, rh.return_date, rh.condition_on_return, i.remarks 
        FROM item i LEFT JOIN rentalhistory rh ON i.item_id = rh.item_id AND rh.order_id = ? 
        LEFT JOIN `order` o ON i.current_order_id = o.order_id AND o.order_id = ? 
        LEFT JOIN requisition req ON o.requisition_id = req.requisition_id 
        LEFT JOIN events e ON req.event_id = e.event_id WHERE i.current_order_id = ? OR rh.order_id = ?";

        $stmt = $conn->prepare($item_sql);
        $stmt->bind_param("iiii", $order_data['order_id'], $order_data['order_id'], $order_data['order_id'], $order_data['order_id']);
        $stmt->execute();
        $item_result = $stmt->get_result();
    }
    
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event, Requisition, and Order Details</title>
    <link rel="stylesheet" href="styles.css">
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

    <!-- Main content -->
    <div class="main-content">
        <!-- Navbar -->
        <div class="navbar">
        <input type="text" class="search-bar" id="searchBar" placeholder="Search active table...">
        </div>

        <!-- Content card -->
        <div class="content-card">
            <!-- Title and buttons -->
            <div class="card-header">
                <h1 id="itemTitle"><?php echo $data['event_name'] ?? 'Event Details'; ?></h1>
                <div class="header-buttons">
                    <!-- <button class="edit-button">Edit</button> -->
                    <button class="download-button" onclick="downloadPDF()">Download PDF</button>
                </div>
            </div>

            <!-- Links for changing content (tabs) -->
            <div class="content-links">
                <a href="#" id="link1" class="inventory-link">Event</a>
                <a href="#" id="link2" class="inventory-link">Requisition</a>
                <a href="#" id="link3" class="inventory-link">Order</a>
                <a href="#" id="link4" class="inventory-link">Items</a>
            </div>

            <hr />

            <!-- Event Details -->
            <div class="table-container" id="table1">
                <p class="details_titles">Event Details</p>
                <div class="row">
                    <p class="details_subject">Event Name:</p>
                    <p class="details_answer"><?php echo $data['event_name']; ?></p>
                </div>
                <div class="row">
                    <p class="details_subject">Event Date:</p>
                    <p class="details_answer"><?php echo $data['event_date']; ?></p>
                </div>
                <div class="row">
                    <p class="details_subject">Event Location:</p>
                    <p class="details_answer"><?php echo $data['event_location']; ?></p>
                </div>
                <div class="row">
                    <p class="details_subject">Customer:</p>
                    <p class="details_answer"><?php echo $data['customer']; ?></p>
                </div>                
                <div class="row">
                    <p class="details_subject">Responsible Person:</p>
                    <p class="details_answer"><?php echo $data['responsible_person_name']; ?></p>
                </div>
                <div class="row">
                    <p class="details_subject">Responsible Person Phone:</p>
                    <p class="details_answer"><?php echo $data['responsible_person_phone']; ?></p>
                </div>
                <div class="row">
                    <p class="details_subject">Responsible Person Email:</p>
                    <p class="details_answer"><?php echo $data['responsible_person_email']; ?></p>
                </div>
                <div class="row">
                    <p class="details_subject">Urgency:</p>
                    <p class="details_answer"><?php echo $data['urgency']; ?></p>
                </div>
                <div class="row">
                    <p class="details_subject">Notes:</p>
                    <p class="details_answer"><?php echo $data['notes']; ?></p>
                </div>
            </div>

            <!-- Requisition Details -->
            <div class="table-container" id="table2">
                <p class="details_titles">Requisition Details</p>
                <?php if (!empty($data['requisition_id'])): ?>
                <div class="row">
                    <p class="details_subject">Requisition Status:</p>
                    <p class="details_answer"><?php echo $data['approval_status']; ?></p>
                </div>
                <div class="row">
                    <p class="details_subject">Expected Pickup Date:</p>
                    <p class="details_answer"><?php echo $data['expected_pick_up_date']; ?></p>
                </div>
                <div class="row">
                    <p class="details_subject">Expected Return Date:</p>
                    <p class="details_answer"><?php echo $data['expected_return_date']; ?></p>
                </div>
                <div class="row">
                    <p class="details_subject">Item List:</p>
                    <ul > 
                    <?php 
                    $items = explode('|', $data['Items_list']); // Split string into an array
                    foreach ($items as $item): ?>
                        <li><p class="details_answer"><?= htmlspecialchars(trim($item)) ?></p></li> 
                    <?php endforeach; ?>
                    </ul>
                </div>
                <?php else: ?>
                <p>No requisition available.</p>
                <?php endif; ?>
            </div>

            <!-- Order Details -->
            <div class="table-container" id="table3">
                <p class="details_titles">Order Details</p>
                <?php if (!empty($order_data['order_id'])): ?>
                <div class="row">
                    <p class="details_subject">Order Status:</p>
                    <p class="details_answer"><?php echo $order_data['status']; ?></p>
                </div>
                <div class="row">
                    <p class="details_subject">Expected Pickup Date:</p>
                    <p class="details_answer"><?php echo $order_data['expected_pick_up_date']; ?></p>
                </div>
                <div class="row">
                    <p class="details_subject">Expected Return Date:</p>
                    <p class="details_answer"><?php echo $order_data['expected_return_date']; ?></p>
                </div>
                <div class="row">
                    <p class="details_subject">Actual Pickup Date:</p>
                    <p class="details_answer"><?php echo $order_data['actual_pick_up_date']; ?></p>
                </div>                 
                <div class="row">
                    <p class="details_subject">Actual Return Date:</p>
                    <p class="details_answer"><?php echo $order_data['actual_return_date']; ?></p>
                </div>                
                <?php else: ?>
                <p>No order available.</p>
                <?php endif; ?>
            </div>

            <!-- Items Table -->
            <div class="table-container" id="table4">
                <div class="table-header">
                    <span>Event's Items</span>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Rental Date</th>
                            <th>Return Date</th>
                            <th>Condition on Return</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($order_data['order_id']) && $item_result->num_rows > 0): ?>
                            <?php while($row = $item_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['item_name']; ?></td>
                                    <td><?php echo $row['rental_date']; ?></td>
                                    <td><?php echo $row['return_date']; ?></td>
                                    <td><?php echo $row['condition_on_return']; ?></td>
                                    <td><?php echo $row['remarks']; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6">No items associated with this order.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div id="table4Pagination" class="pagination"></div>                    
            </div>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        const table4 = document.getElementById('table4');

        setupPagination(table4, document.getElementById('table4Pagination'));
    </script>     

    <script>
    function downloadPDF() {
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
