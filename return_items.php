<?php

require 'session_check.php';

// allow all
if ($user_role != 1 && $user_role != 2 && $user_role != 3) {
    echo "❌ Access Denied!";
    exit;
}

// Include the database connection file
include 'db.php';

$order_id = $_GET['order_id']; // Get the order ID from the URL

?>

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Return Items</title>
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
                <span>Return Items</span>
            </div>
          <div class="link-container">
            <div class="line"></div>
          </div>
          <div class="table-container" id="table1">
            
          <form method="post" action="process_return.php">
    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">

    <table class="table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Model</th>
                <th>Serial Number</th>
                <th>Condition</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = "SELECT item_id, item_name, model, serial_number FROM item WHERE current_order_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['item_name']}</td>
                        <td>{$row['model']}</td>
                        <td>{$row['serial_number']}</td>
                        <td>
                            <select name='status[{$row['item_id']}]' required>
                                <option value=''>Select condition</option>
                                <option value='Available'>Good Condition</option>
                                <option value='Damaged'>Damaged</option>
                                <option value='Lost'>Lost</option>
                            </select>
                        </td>
                        <td class='hidden'>
                            <textarea name='remarks[{$row['item_id']}]' 
                                      placeholder='Enter remarks if damaged or lost' 
                                      style='display:none;'></textarea>
                        </td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
    <br>
    <div id="feedback-message"></div>
    <button type="button" class="button-primary" id="complete-button">Complete</button>
          </div>
        </div>
      </div>
    </div>
    <script src="script.js"></script>
    <script>
    document.querySelectorAll('select[name^="status"]').forEach(select => {
        select.addEventListener('change', function() {
            const itemId = this.name.match(/\[(\d+)\]/)[1];
            const textarea = document.querySelector(`textarea[name="remarks[${itemId}]"]`);
            
            if (this.value === 'Damaged' || this.value === 'Lost') {
                textarea.style.display = 'block';
                textarea.required = true;
            } else {
                textarea.style.display = 'none';
                textarea.required = false;
            }
        });
    });

    document.getElementById('complete-button').addEventListener('click', function(e) {
        // Collect all form data
        const formData = new FormData();
        formData.append('order_id', '<?php echo $order_id; ?>');
        
        // Add status and remarks for each item
        document.querySelectorAll('select[name^="status"]').forEach(select => {
            const itemId = select.name.match(/\[(\d+)\]/)[1];
            formData.append(`status[${itemId}]`, select.value);
            
            const textarea = document.querySelector(`textarea[name="remarks[${itemId}]"]`);
            formData.append(`remarks[${itemId}]`, textarea.value);
        });
        
        fetch('process_return.php', {
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
