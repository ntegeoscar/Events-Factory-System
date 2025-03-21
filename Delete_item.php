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

// Fetch rental history for the item
$rental_history_sql = "
    SELECT 
        events.event_name, 
        rentalhistory.rental_date, 
        rentalhistory.return_date, 
        rentalhistory.condition_on_return, 
        item.remarks
    FROM rentalhistory
    JOIN item ON rentalhistory.item_id = item.item_id
    JOIN `order` ON rentalhistory.order_id = `order`.order_id
    JOIN requisition ON `order`.requisition_id = requisition.requisition_id
    JOIN events ON requisition.event_id = events.event_id
    WHERE rentalhistory.item_id = $item_id";
$rental_history_result = $conn->query($rental_history_sql);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title> Delete Item</title>
    <link rel="stylesheet" href="styles.css" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
          <h1 id="itemTitle">DELETE ITEM</h1>
          <div class="header-buttons">
            <a href="Manage_Inventory.php" >
              <button class="edit-button">Back</button>
            </a>            
            <button id="deleteButton" class="edit-button">Delete</button>
          </div>
        </div>

        <!-- Links for changing content (tabs) -->
        <div class="content-links">
          <a href="#" id="link1" class="inventory-link">Details</a>
          <a href="#" id="link2" class="inventory-link">History</a>
        </div>

        <hr />

        <div class="table-container" id="table1">
          <!-- Table 1 -->
          <div class="row">
            <div class="" id="">
              <p class="details_titles">Item details</p>
              <div class="">
              <div class="row">
                <p class="details_subject" style="margin-right: 10px">Name:</p>
                <p class="details_answer"><?= $item['item_name'] ?></p>
              </div>
              <div class="row">
                <p class="details_subject" style="margin-right: 10px">Category:</p>
                <p class="details_answer"><?= $item['category_name'] ?></p>
              </div>
              <div class="row">
                <p class="details_subject" style="margin-right: 10px">Sub-Category:</p>
                <p class="details_answer"><?= $item['sub_category_name'] ?></p>
              </div>
              <div class="row">
                <p class="details_subject" style="margin-right: 10px">Group:</p>
                <p class="details_answer"><?= $item['group_name'] ?></p>
              </div>
              <div class="row">
                <p class="details_subject" style="margin-right: 10px">Availability:</p>
                <p class="details_answer"><?= $item['availability'] ?></p>
              </div>              
              </div>
            </div>
            <div class="" id="">
              <p class="details_titles">Extra Item details</p>
              <div class="">
              <?php if ($item['quantity']) { ?>
              <div class="row">
                <p class="details_subject" style="margin-right: 10px">Quantity:</p>
                <p class="details_answer"><?= $item['quantity'] ?></p>
              </div>
              <?php } ?>
              
              <?php if ($item['unit']) { ?>
              <div class="row">
                <p class="details_subject" style="margin-right: 10px">Unit:</p>
                <p class="details_answer"><?= $item['unit'] ?></p>
              </div>
              <?php } ?>
              <div class="row">
                <p class="details_subject" style="margin-right: 10px">Model:</p>
                <p class="details_answer"><?= $item['model'] ?></p>
              </div>
              <div class="row">
                <p class="details_subject" style="margin-right: 10px">Serial Number:</p>
                <p class="details_answer"><?= $item['serial_number'] ?></p>
              </div>
              <?php if ($item['flight_case_number']) { ?>
              <div class="row">
                <p class="details_subject" style="margin-right: 10px">Flight Case Number:</p>
                <p class="details_answer"><?= $item['flight_case_number'] ?></p>
              </div>
              <?php } ?>

              <?php if ($item['remarks']) { ?>
              <div class="row">
                <p class="details_subject" style="margin-right: 10px">Remarks:</p>
                <p class="details_answer"><?= $item['remarks'] ?></p>
              </div>
              <?php } ?>
              <?php if ($item['current_event_name']) { ?>
              <div class="row">
                <p class="details_subject" style="margin-right: 10px">Current Event (if rented):</p>
                <p class="details_answer"><?= $item['current_event_name'] ?></p>
              </div>
              <?php } ?>
              </div>
            </div>
          </div>
        </div>

        <div class="table-container" id="table2" style="display: none">
          <div class="table-header">
            <span>Rental History</span>
            <div class="button-container">
              <!-- <a href="new_item.html" class="button button-primary">Add Item</a> -->
              <!-- <a href="#" class="button button-secondary">Download all</a> -->
            </div>
          </div>
          <table class="table">
            <thead>
              <tr>
                <th>Event Name</th>
                <th>Rental Date</th>
                <th>Return Date</th>
                <th>Condition</th>
                <th>Remarks</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $rental_history_result = $conn->query($rental_history_sql);

              if ($rental_history_result && $rental_history_result->num_rows > 0) {
                  while ($rental = $rental_history_result->fetch_assoc()) {
              ?>
                  <tr>
                    <td><?= $rental['event_name'] ?></td>
                    <td><?= $rental['rental_date'] ?></td>
                    <td><?= $rental['return_date'] ?></td>
                    <td><?= $rental['condition_on_return'] === 'Available' ? 'Good' : $rental['condition_on_return'] ?></td>
                    <td><?= $rental['remarks'] ?></td>
                  </tr>
              <?php 
                  }
              } else { 
              ?>
                  <tr>
                    <td colspan="5">No rental history available for this item.</td>
                  </tr>
              <?php 
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <script src="script.js"></script>
    <script>
      document.addEventListener("DOMContentLoaded", function () {
          // Ensure the button exists before attaching event
          let deleteBtn = document.getElementById("deleteButton");
          if (deleteBtn) {
              deleteBtn.addEventListener("click", function () {
                  Swal.fire({
                    title: "Are you sure?",
                    text: "This item and its entries will be permanently deleted!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Delete",
                    cancelButtonText: "Discard",
                    confirmButtonColor: "#FF4444",  // Red
                    cancelButtonColor: "#ffffff",  // White
                    customClass: {
                        popup: "custom-popup",
                        title: "custom-title",
                        htmlContainer: "custom-message",
                        confirmButton: "custom-confirm",
                        cancelButton: "custom-cancel"
                    }
                  }).then((result) => {
                      if (result.isConfirmed) {
                          // Fake item ID for now (replace dynamically)
                          let itemId = <?= $item_id ?>;  

                          // Send AJAX request
                          fetch("delete_logic.php", {
                              method: "POST",
                              body: JSON.stringify({ item_id: itemId }),
                              headers: { "Content-Type": "application/json" }
                          })
                          .then(response => response.json())
                          .then(data => {
                              if (data.success) {
                                  Swal.fire({
                                      title: "Deleted!",
                                      text: "The item has been removed.",
                                      icon: "success",
                                      timer: 3000,
                                      showConfirmButton: false
                                  });
                                  setTimeout(() => {
                                      window.location.href = "Manage_inventory.php";
                                  }, 3000);
                              } else {
                                  Swal.fire({
                                      title: "Error",
                                      text: "Something went wrong. Try again.",
                                      icon: "error"
                                  });
                              }
                          })
                          .catch(error => {
                              Swal.fire({
                                  title: "Error",
                                  text: "Could not connect to the server.",
                                  icon: "error"
                              });
                          });
                      }
                  });
              });
          } else {
              console.error("Delete button not found!");
          }
      });
    </script>
  </body>
</html>
