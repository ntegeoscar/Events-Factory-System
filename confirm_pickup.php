<?php

require 'session_check.php';

// allow all
if ($user_role != 1 && $user_role != 2 && $user_role != 3) {
    echo "❌ Access Denied!";
    exit;
}

// Include the database connection file
include 'db.php';

$order_id = $_GET['order_id']; // Get order ID from the URL

// Fetch order details along with responsible person info
$sql_order = "SELECT o.*, e.responsible_person_name, e.responsible_person_phone
              FROM `order` o
              LEFT JOIN requisition r ON o.requisition_id = r.requisition_id
              LEFT JOIN events e ON r.event_id = e.event_id
              WHERE o.order_id = ?";
$stmt = $conn->prepare($sql_order);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_data = $stmt->get_result()->fetch_assoc();

// Fetch items related to the order
$item_sql = "SELECT item_name, model, serial_number FROM item WHERE current_order_id = ?";
$stmt_items = $conn->prepare($item_sql);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items_result = $stmt_items->get_result();

?>

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Confirm Pickup</title>
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
                <span>Pickup Order</span>
            </div>
          <div class="link-container">
            <div class="line"></div>
          </div>
          <div class="responsible-details">
            <p><strong>Responsible Person:</strong> <?php echo $order_data['responsible_person_name']; ?></p>
            <p><strong>Phone Number:</strong> <?php echo $order_data['responsible_person_phone']; ?></p>
          </div>
          <div class="table-container" id="table1">
            <table class="table">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Model</th>
                        <th>Serial Number</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $items_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $item['item_name']; ?></td>
                            <td><?php echo $item['model']; ?></td>
                            <td><?php echo $item['serial_number']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <br>
            <div id="feedback-message"></div>
            <form id="pickup-form" method="post" action="process_pickup_confirmation.php">
              <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
              <button type="submit" class="button-primary">Confirm</button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <script src="script.js"></script>
    <script>
    document.getElementById('pickup-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('process_pickup_confirmation.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            const [status, message] = data.split(':');
            document.getElementById('feedback-message').innerHTML = 
                `<div class="alert ${status === 'success' ? 'alert-success' : 'alert-error'}">${message}</div>`;
            
            if (status === 'success') {
                setTimeout(() => {
                    window.location.href = 'orders.php';
                }, 1500); // Redirect after 1.5 seconds
            }
        })
        .catch(error => {
            document.getElementById('feedback-message').innerHTML = 
                '<div class="alert alert-error">An error occurred. Please try again.</div>';
        });
    });
    </script>
    <style>
    .alert {
        padding: 10px;
        margin-bottom: 15px;
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
    
    #feedback-message {
        margin-bottom: 15px;
    }
    </style>
  </body>
</html>
