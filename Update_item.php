<?php

require 'session_check.php';

// Only allow Operator & Super Admin
if ($user_role != 1 && $user_role != 2) {
    echo "❌ Access Denied!";
    exit;
}

// Include the database connection file
include 'db.php';

// Get the item ID from the URL parameter
$item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch item details along with category, sub-category, group, and current event (if rented)
$item_sql = "
    SELECT 
        item.*, 
        category.category_name, 
        subcategory.sub_category_name, 
        itemgroup.group_name, 
        events.event_name AS current_event_name
    FROM item
    JOIN category ON item.category_id = category.category_id
    JOIN subcategory ON item.sub_category_id = subcategory.sub_category_id
    JOIN itemgroup ON item.group_id = itemgroup.group_id
    LEFT JOIN events ON item.event_id = events.event_id
    WHERE item.item_id = $item_id";
$item_result = $conn->query($item_sql);
$item = $item_result->fetch_assoc();


// Initialize variables for feedback
$feedback = "";
$feedback_class = "";

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form inputs
    $item_name = $conn->real_escape_string($_POST['itemName']);
    $category_id = intval($_POST['itemCategory']);
    $sub_category_id = intval($_POST['itemSubCategory']);
    $group_id = intval($_POST['itemGroup']);
    $model = $conn->real_escape_string($_POST['itemModel']);
    $serial_number = $conn->real_escape_string($_POST['itemSerialNumber']);
    $unit = $conn->real_escape_string($_POST['itemUnit']);
    $availability = $conn->real_escape_string($_POST['itemAvailability']);
    $quantity = intval($_POST['itemQuantity']);
    $flight_case_number = $conn->real_escape_string($_POST['itemFlightCaseNumber']);
    $remarks = $conn->real_escape_string($_POST['remarks']);

    // Update query
    $update_sql = "
        UPDATE item 
        SET 
            item_name = '$item_name',
            category_id = $category_id,
            sub_category_id = $sub_category_id,
            group_id = $group_id,
            model = '$model',
            serial_number = '$serial_number',
            unit = '$unit',
            availability = '$availability',
            quantity = $quantity,
            flight_case_number = '$flight_case_number',
            remarks = '$remarks'
        WHERE item_id = $item_id
    ";

    if ($conn->query($update_sql) === TRUE) {
        $feedback = "Item updated successfully!";
        $feedback_class = "success";
    } else {
        $feedback = "Error updating item: " . $conn->error;
        $feedback_class = "error";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Update Item</title>
    <link rel="stylesheet" href="styles.css" />
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
        <div class="form-card">
            <h2>Update Item</h2>

                        <!-- Feedback Message -->
            <?php if ($feedback): ?>
                <div class="feedback_<?= $feedback_class; ?>">
                    <?= $feedback; ?>
                </div>
            <?php endif; ?>
            <h1 value=<?= $item['item_name'] ?>>hhhh<h1>
            <form action="Update_item.php?id=<?= $item_id; ?>" method="post">
                <div class="form-group">
                    <label for="itemName">Item Name</label>
                    <input type="text" id="itemName" name="itemName" value=<?= $item['item_name'] ?>>
                </div>
                <div class="form-group">
                  <label for="itemCategory">Category</label>
                  <select id="itemCategory" name="itemCategory">
                    <option value=<?= $item['category_id'] ?>><?= $item['category_name'] ?></option>                    
                      <!-- Categories will be populated here from PHP -->
                  </select>
              </div>
              <div class="form-group">
                  <label for="itemSubCategory">Sub Category</label>
                  <select id="itemSubCategory" name="itemSubCategory">
                  <option value=<?= $item['sub_category_id'] ?>><?= $item['sub_category_name'] ?></option>                    
                      <!-- Subcategories will be populated dynamically -->
                  </select>
              </div>    
              <div class="form-group">
                  <label for="itemGroup">Group</label>
                  <select id="itemGroup" name="itemGroup">
                      <option value=<?= $item['group_id'] ?>><?= $item['group_name'] ?></option>
                      <!-- Groups will be populated dynamically -->
                  </select>
              </div>        
                <div class="form-group">
                  <label for="itemModel">Model</label>
                  <input type="text" id="itemModel" name="itemModel" value=<?= $item['model'] ?>>
                </div>   
                <div class="form-group">
                  <label for="itemSerialNumber">Serial Number</label>
                  <input type="text" id="itemSerialNumber" name="itemSerialNumber" value=<?= $item['serial_number'] ?>>
                </div>  
                <div class="form-group">
                  <label for="itemUnit">Unit</label>
                  <select id="itemUnit" name="itemUnit">
                    <option value= <?= $item['unit'] ?>><?= $item['unit'] ?></option>
                    <option value="Sqm">Sqm</option>
                    <option value="M">M</option>
                    <option value="Kg">Kg</option>
                    <option value="L">L</option>
                    <option value="PC">PC</option>   
                    <option value="Cartons">Cartons</option>                    
                  </select>                  
                </div>         
                <div class="form-group">
                  <label for="itemAvailability">Availability</label>
                  <select id="itemAvailability" name="itemAvailability">
                    <option value=<?= $item['availability'] ?>><?= $item['availability'] ?></option>
                    <option value="Available">Available</option>
                    <option value="Damaged">Damaged</option>      
                    <option value="Rented">Rented</option>
                    <option value="Lost">Lost</option>                                     
                  </select>                  
                </div>                          
                <div class="form-group">
                    <label for="itemQuantity">Quantity</label>
                    <input type="number" id="itemQuantity" name="itemQuantity" value=<?= $item['quantity'] ?>>
                </div>
    
                <div class="form-group">
                  <label for="itemFlightCaseNumber">Flight case number</label>
                  <input type="text" id="itemFlightCaseNumber" name="itemFlightCaseNumber" value=<?= $item['flight_case_number'] ?>>
                </div>                                                        
                <div class="form-group">
                    <label for="remarks">Remarks</label>
                    <textarea id="remarks" name="remarks"><?= $item['remarks'] ?></textarea>
                </div>

                <div class="button-container">
                  <a href="Manage_Inventory.php" >
                    <button type="button" class="button-secondary">Discard</button>
                  </a>
                  <button type="submit" class="button-primary">Update</button>
                </div>
            </form>
    </div>
    <script src="dynamic-dropdowns.js"></script>
  </body>
</html>
