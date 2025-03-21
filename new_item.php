<?php

require 'session_check.php';

// Only allow Operator & Super Admin
if ($user_role != 1 && $user_role != 2) {
    echo "❌ Access Denied!";
    exit;
}

// Include the database connection file
include 'db.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form values
    $itemName = $_POST['itemName'];
    $itemCategory = $_POST['itemCategory'];
    $itemSubCategory = $_POST['itemSubCategory'];
    $itemGroup = $_POST['itemGroup'];
    $itemModel = $_POST['itemModel'];
    $itemSerialNumber = $_POST['itemSerialNumber'];
    $itemQuantity = $_POST['itemQuantity'];
    $itemUnit = $_POST['itemUnit'];
    $itemFlightCaseNumber = $_POST['itemFlightCaseNumber'];
    $remarks = $_POST['remarks'];

    // Insert the new item into the database
    $sql = "INSERT INTO item (item_name, category_id, sub_category_id, group_id, quantity, unit, model, serial_number, flight_case_number, remarks, availability) 
            VALUES ('$itemName', '$itemCategory', '$itemSubCategory', '$itemGroup', '$itemQuantity', '$itemUnit', '$itemModel', '$itemSerialNumber', '$itemFlightCaseNumber', '$remarks', 'Available')";

    if ($conn->query($sql) === TRUE) {
        // Redirect to inventory or show success message
        $feedback = "Item created successfully.";
        $feedback_class = "success";  
    } else {
        // Handle error
        // echo "<script>alert('Error: " . $sql . "<br>" . $conn->error . "');</script>";
        $feedback = "Error creating item: " . $sql . "<br>" . $conn->error;
        $feedback_class = "error";            
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>New Item</title>
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
        <div class="form-card">
            <h2>New Item</h2>

            <!-- Feedback Message -->
            <?php if (isset($feedback)): ?>
              <div class="feedback_<?= $feedback_class; ?>">
                  <?= $feedback; ?>
              </div>
            <?php endif; ?>  

            <form action="new_item.php" method="post">
                <div class="form-group">
                    <label for="itemName">Item Name</label>
                    <input type="text" id="itemName" name="itemName">
                </div>
                <div class="form-group">
                  <label for="itemCategory">Category</label>
                  <select id="itemCategory" name="itemCategory">
                      <option value=""></option>                    
                      <!-- Categories will be populated here from PHP -->
                  </select>
              </div>
              <div class="form-group">
                  <label for="itemSubCategory">Sub Category</label>
                  <select id="itemSubCategory" name="itemSubCategory">
                      <option value=""></option>
                      <!-- Subcategories will be populated dynamically -->
                  </select>
              </div>    
              <div class="form-group">
                  <label for="itemGroup">Group</label>
                  <select id="itemGroup" name="itemGroup">
                      <option value=""></option>
                      <!-- Groups will be populated dynamically -->
                  </select>
              </div>        
                <div class="form-group">
                  <label for="itemModel">Model</label>
                  <input type="text" id="itemModel" name="itemModel">
                </div>   
                <div class="form-group">
                  <label for="itemSerialNumber">Serial Number</label>
                  <input type="text" id="itemSerialNumber" name="itemSerialNumber">
                </div>  
                <div class="form-group">
                  <label for="itemUnit">Unit</label>
                  <select id="itemUnit" name="itemUnit">
                    <option value=""></option>
                    <option value="Sqm">Sqm</option>
                    <option value="M">M</option>
                    <option value="Kg">Kg</option>
                    <option value="L">L</option>
                    <option value="PC">PC</option>   
                    <option value="Cartons">Cartons</option>                    

                </select>                  
                </div>                 
                <div class="form-group">
                    <label for="itemQuantity">Quantity</label>
                    <input type="number" id="itemQuantity" name="itemQuantity">
                </div>
    
                <div class="form-group">
                  <label for="itemFlightCaseNumber">Flight case number</label>
                  <input type="text" id="itemFlightCaseNumber" name="itemFlightCaseNumber">
                </div>                                                        
                <div class="form-group">
                    <label for="remarks">Remarks</label>
                    <textarea id="remarks" name="remarks"></textarea>
                </div>

                <div class="button-container">
                    <button type="button" class="button-secondary">Discard</button>
                    <button type="submit" class="button-primary">Add Item</button>
                </div>
            </form>
    </div>
    <script src="dynamic-dropdowns.js"></script>
  </body>
</html>
