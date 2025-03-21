<?php

require 'session_check.php';

// Only allow Operator & Super Admin
if ($user_role != 1 && $user_role != 2) {
    echo "❌ Access Denied!";
    exit;
}

// Database connection
include 'db.php'; 

// Fetch events data
$events_sql = "SELECT event_id, event_name, event_date, event_location FROM events ORDER BY event_id DESC";
$events_result = $conn->query($events_sql);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Events & Requisitions</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
      .status-pending {
        background-color: #FFF3CD;  /* Light yellow background */
        color: #856404;            /* Dark yellow text */
        padding: 5px 10px;
        border-radius: 4px;
        font-weight: 500;
      }

      .status-approved {
        background-color: #D4EDDA;  /* Light green background */
        color: #155724;            /* Dark green text */
        padding: 5px 10px;
        border-radius: 4px;
        font-weight: 500;
      }

      .status-rejected {
        background-color: #F8D7DA;  /* Light red background */
        color: #721C24;            /* Dark red text */
        padding: 5px 10px;
        border-radius: 4px;
        font-weight: 500;
      }

      .download-dropdown {
        position: relative;
        display: inline-block;
      }

      .download-options {
        display: none;
        position: absolute;
        right: 0;
        background-color: #f9f9f9;
        min-width: 160px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 1;
        border-radius: 4px;
      }

      .download-options a {
        color: black;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
      }

      .download-options a:hover {
        background-color: #f1f1f1;
      }

      .show {
        display: block;
      }
    </style>
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
          <div class="nav-item active">
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
      <div class="navbar">
        <input type="text" class="search-bar" id="searchBar" placeholder="Search active table...">
      </div>
      <div class="Page_with_navbar">
        <div class="inventory-card">
          <div class="link-container">
            <a href="#" id="link1" class="inventory-link active-link">Events</a>
            <a href="#" id="link2" class="inventory-link">Requisitions</a>
            <div class="line"></div>
          </div>

          <div class="table-container" id="table1">
            <div class="table-header">
              <span>Events</span>

              <div class="header-buttons">
                <a href="new_events_requisitions.php" class="edit-button">
                  Add Event
                </a>
                <div class="download-dropdown">
                  <button onclick="toggleDropdown('eventsDownload')" class="download-button">Download all</button>
                  <div id="eventsDownload" class="download-options">
                    <a href="#" onclick="downloadEvents('pdf')">Download PDF</a>
                    <a href="#" onclick="downloadEvents('excel')">Download Excel</a>
                  </div>
                </div>
              </div>
            </div>
            <table class="table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Event Name</th>
                  <th>Date</th>
                  <th>Location</th>
                </tr>
              </thead>
              <tbody>
                <?php while($row = $events_result->fetch_assoc()) { ?>
                  <tr onclick="location.href='event_requisition_order_details.php?event_id=<?php echo $row['event_id']; ?>'">
                  <td><?php echo $row['event_id']; ?></td>
                  <td><?php echo $row['event_name']; ?></td>
                  <td><?php echo $row['event_date']; ?></td>
                  <td><?php echo $row['event_location']; ?></td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
            <div id="table1Pagination" class="pagination"></div>
          </div>

          <div class="table-container" id="table2" style="display: none">
            <div class="table-header">
              <span>Requisitions</span>
              <div class="header-buttons">
                  <div class="filter-section">
                    <select id="Requisition_status_Filter">
                        <option value="">All Status</option>
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div> 
                <div class="download-dropdown">
                  <button onclick="toggleDropdown('requisitionsDownload')" class="download-button">Download all</button>
                  <div id="requisitionsDownload" class="download-options">
                    <a href="#" onclick="downloadRequisitions('pdf')">Download PDF</a>
                    <a href="#" onclick="downloadRequisitions('excel')">Download Excel</a>
                  </div>
                </div>
              </div>
            </div>
            <table class="table" id="requisitionsTable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Event ID</th>
                  <th>Pick-up</th>
                  <th>Return</th>
                  <th>Approval Status</th>
                  <th>Remarks</th>
                  <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1): ?>   
                  <th>Action</th>  
                  <?php endif; ?>                
                </tr>
              </thead>
              <tbody id="requisitionsBody">
                  <!-- Data will be loaded dynamically using AJAX -->
              </tbody>
            </table>

            <div id="table2Pagination" class="pagination"></div>
          </div>
        </div>
      </div>
    </div>
    <script src="script.js"></script>
    <script>
        const table1 = document.getElementById('table1');
        const table2 = document.getElementById('table2');

        setupPagination(table1, document.getElementById('table1Pagination'));
        setupPagination(table2, document.getElementById('table2Pagination'));
    </script>
    <script>
        // AJAX filtering
      document.getElementById("Requisition_status_Filter").addEventListener("change", function () {
            let status = this.value;
            loadRequisitions(status);
        });

        function loadRequisitions(status) {
            let xhr = new XMLHttpRequest();
            let url = "fetch_requisitions.php";
            if (status) {
                url += "?status=" + status;
            }
            xhr.open("GET", url, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.getElementById("requisitionsBody").innerHTML = xhr.responseText;
                    // Add status highlighting
                    const statusCells = document.querySelectorAll('#requisitionsBody td:nth-child(5)');
                    statusCells.forEach(cell => {
                        const status = cell.textContent.trim().toLowerCase();
                        cell.innerHTML = `<span class="status-${status}">${cell.textContent}</span>`;
                    });
                }
                setupPagination(document.getElementById('table2'), document.getElementById('table2Pagination'));
            };
            xhr.send();
        }

        // Load all requisitions when page loads
        loadRequisitions('');
    </script>
    <script>
        // Add these new functions
        function toggleDropdown(dropdownId) {
            document.getElementById(dropdownId).classList.toggle("show");
        }

        // Close the dropdown if clicked outside
        window.onclick = function(event) {
            if (!event.target.matches('.download-button')) {
                var dropdowns = document.getElementsByClassName("download-options");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }

        function downloadEvents(format) {
            window.location.href = `download_events.php?format=${format}`;
        }

        function downloadRequisitions(format) {
            // Get current filter status
            const status = document.getElementById("Requisition_status_Filter").value;
            window.location.href = `download_requisitions.php?format=${format}&status=${status}`;
        }
    </script>

  </body>
</html>
