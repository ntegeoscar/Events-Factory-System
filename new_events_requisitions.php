<?php
require 'session_check.php';

// Only allow Operator & Super Admin
if ($user_role != 1 && $user_role != 2) {
    echo "❌ Access Denied!";
    exit;
}

// Include the database connection file
include 'db.php';

// Fetch categories for dropdown
$categories = $conn->query("SELECT DISTINCT category_id, category_name FROM category");

// Only execute when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $event_name = $_POST['eventName'];
    $event_date = $_POST['eventDate'];
    $event_end_date = $_POST['eventEndDate'];
    $event_location = $_POST['eventLocation'];
    $event_type = $_POST['eventType'];
    $customer = $_POST['customer'];
    $responsible_person_name = $_POST['responsiblePersonName'];
    $responsible_person_phone = $_POST['responsiblePersonPhone'];
    $responsible_person_email = $_POST['responsiblePersonEmail'];
    $urgency = $_POST['urgency'];
    $notes = $_POST['notes'];
    $expected_pick_up_date = $_POST['expectedPickUpDate'];
    $expected_return_date = $_POST['expectedReturnDate'];
    $items = $_POST['items'] ?? []; // Selected items

  // Start transaction
  $conn->begin_transaction();

  try {
      // Insert event
      $event_sql = "INSERT INTO events (event_name, event_date, event_end_date, event_location, event_type, customer, responsible_person_name, responsible_person_phone, responsible_person_email, urgency, notes, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
      $stmt = $conn->prepare($event_sql);
      $stmt->bind_param("sssssssssss", $event_name, $event_date, $event_end_date, $event_location, $event_type, $customer, $responsible_person_name, $responsible_person_phone, $responsible_person_email, $urgency, $notes);
      
      if (!$stmt->execute()) {
          throw new Exception("Event insert failed: " . $stmt->error);
      }
      $event_id = $stmt->insert_id;

      // Prepare the item_list text
      $item_list = [];
      foreach ($items as $item_id) {
          $item_query = "SELECT item_name, serial_number, model FROM item WHERE item_id = ?";
          $stmt = $conn->prepare($item_query);
          $stmt->bind_param("i", $item_id);
          $stmt->execute();
          $result = $stmt->get_result();
          if (!$result) {
              throw new Exception("Item fetch failed.");
          }
          $item_details = $result->fetch_assoc();
          $item_list[] = "{$item_details['item_name']} (SN: {$item_details['serial_number']}, Model: {$item_details['model']})";
      }

      // Convert item_list array to text
      $item_list_text = implode("| ", $item_list);

      // Insert requisition
      $requisition_sql = "INSERT INTO requisition (event_id, expected_pick_up_date, expected_return_date, items_list, approval_status, created_at)
                          VALUES (?, ?, ?, ?, 'Pending', NOW())";
      $stmt = $conn->prepare($requisition_sql);
      $stmt->bind_param("isss", $event_id, $expected_pick_up_date, $expected_return_date, $item_list_text);
      
      if (!$stmt->execute()) {
          throw new Exception("Requisition insert failed: " . $stmt->error);
      }
      $req_id = $stmt->insert_id;

      // Reserve items
      foreach ($items as $item_id) {
          $reserve_sql = "UPDATE item SET availability = 'Reserved', reserved_requisition_id = ? WHERE item_id = ?";
          $stmt = $conn->prepare($reserve_sql);
          $stmt->bind_param("ii", $req_id, $item_id);
          if (!$stmt->execute()) {
              throw new Exception("Item reservation failed for Item ID: $item_id");
          }
      }

      // Send email notification
      require 'vendor/autoload.php';  // Composer autoload
  
      
      class CustomPDF extends TCPDF {
          // Custom Header
          public function Header() {
              $this->Image('company_header.jpg', -5, 5, 300);
              $this->SetY(5); // Ensure content starts below the header
          }
      
          // Custom Footer
          public function Footer() {
              $this->SetY(-15);
              $this->SetFont('helvetica', 'I', 10);
              $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, 0, 'C');
          }
      }
      
      // Initialize PDF (Landscape mode for more width)
      $pdf = new CustomPDF('L', 'mm', 'A4');
      $pdf->SetMargins(10, 50, 10);
      $pdf->SetAutoPageBreak(true, 20);
      $pdf->AddPage();
      $pdf->SetFont('helvetica', '', 12);
      
      // Requisition Title
      $pdf->SetFont('helvetica', 'B', 16);
      $pdf->Cell(0, 10, "Requisition Details", 0, 1, 'C');
      $pdf->Ln(5);
      
      // Requisition Info
      $pdf->SetFont('helvetica', '', 12);
      $info = [
          'Requisition ID' => $req_id,
          'Event Name' => $event_name,
          'Requested By' => $responsible_person_name,
          'Expected Pick-up Date' => $expected_pick_up_date,
          'Expected Return Date' => $expected_return_date
      ];
      
      foreach ($info as $key => $value) {
          $pdf->Cell(70, 8, "$key:", 0, 0, 'L', false);
          $pdf->Cell(100, 8, $value, 0, 1, 'L', false);
      }
      $pdf->Ln(5);
      
      // Table Headers
      $columns = [
          ['title' => 'Item', 'width' => 70],
          ['title' => 'Quantity', 'width' => 30],
          ['title' => 'Model', 'width' => 60],
          ['title' => 'S/N', 'width' => 60],
          ['title' => 'Remarks', 'width' => 50]
      ];
      
      $pdf->SetFont('helvetica', 'B', 11);
      foreach ($columns as $col) {
          $pdf->Cell($col['width'], 10, $col['title'], 1, 0, 'C', false);
      }
      $pdf->Ln();
      
      // Table Body
      $pdf->SetFont('helvetica', '', 10);
      $fill = false;
      
      $item_details_list = [];
      foreach ($items as $item_id) {
          $item_query = "SELECT item_name, quantity, model, serial_number, remarks FROM item WHERE item_id = ?";
          $stmt = $conn->prepare($item_query);
          $stmt->bind_param("i", $item_id);
          $stmt->execute();
          $result = $stmt->get_result();
          if ($row = $result->fetch_assoc()) {
              $item_details_list[] = $row;
          }
      }

      foreach ($item_details_list as $item) {
          $maxLines = max(
              $pdf->getNumLines($item['item_name'], 70),
              $pdf->getNumLines($item['quantity'], 30),
              $pdf->getNumLines($item['model'], 60),
              $pdf->getNumLines($item['serial_number'], 60),
              $pdf->getNumLines($item['remarks'], 50)
          );
          $rowHeight = $maxLines * 5;
      
          $pdf->SetFillColor(245, 245, 245);
      
          $pdf->MultiCell(70, $rowHeight, $item['item_name'], 1, 'L', $fill, 0);
          $pdf->MultiCell(30, $rowHeight, $item['quantity'], 1, 'C', $fill, 0);
          $pdf->MultiCell(60, $rowHeight, $item['model'], 1, 'L', $fill, 0);
          $pdf->MultiCell(60, $rowHeight, $item['serial_number'], 1, 'C', $fill, 0);
          $pdf->MultiCell(50, $rowHeight, $item['remarks'], 1, 'L', $fill, 1);
      
          $fill = !$fill;
      }
      
      // Save PDF
      $pdf_file = __DIR__ . "/requisition_$req_id.pdf";
      $pdf->Output($pdf_file, 'F');
      
      // Send Email
      $mail = new PHPMailer\PHPMailer\PHPMailer();
      $mail->isSMTP();
      $mail->Host = 'smtp.sendgrid.net';
      $mail->SMTPAuth = true;
      $mail->Username = 'apikey';
      
      $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
      $dotenv->load();
      
      $mail->Password = $_ENV['SENDGRID_API_KEY'];
      $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port = 587;
      
      $mail->setFrom('eventsfactorysystem@gmail.com', 'Inventory System');
      $mail->addReplyTo('info@eventsfactory.rw', 'Events Factory Support');
      $mail->addAddress('ntegeoscar9@gmail.com', 'Admin');
      
      $mail->Subject = "New Requisition Created (ID: $req_id)";
      $mail->Body = "Hello Admin,\n\nA new requisition has been created. The details are attached as a PDF.\n\nBest Regards,\nInventory System";
      
      // Attach PDF
      $mail->addAttachment($pdf_file);
      
      if (!$mail->send()) {
          throw new Exception("Email sending failed: " . $mail->ErrorInfo);
      }
      
      // Delete the temp PDF after sending
      unlink($pdf_file);
      
      $conn->commit();
      $feedback = "Event and requisition created successfully.";
      $feedback_class = "success";   
  } catch (Exception $e) {
      // Rollback transaction on failure
      $conn->rollback();

      $feedback = "Error updating item: " . $e->getMessage();
      $feedback_class = "error";    
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>New Event & Requisition</title>
    <link rel="stylesheet" href="styles.css" />
    <script src="script.js" defer></script>
</head>
<body>
    <div class="sidebar">
      <img src="red_logo.png" alt="Logo" class="logo" />
      <div class="nav-links">
        <a href="dashboard.php" class="nav-link">
          <div class="nav-item active">
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
        <h2>Create New Event & Requisition</h2>
            <!-- Feedback Message -->
            <?php if (isset($feedback)): ?>
              <div class="feedback_<?= $feedback_class; ?>">
                  <?= $feedback; ?>
              </div>
            <?php endif; ?>        
        <form method="post">
            <!-- Event Information -->
                <h3>Event Information</h3>

                <div class="form-group">
                  <label for="eventName">Event Name</label>
                  <input type="text" id="eventName" name="eventName" required>
                </div>
                <div class="form-group">
                  <label for="eventDate">Event Date</label>
                  <input type="date" id="eventDate" name="eventDate" required>
                </div>
                <div class="form-group">
                  <label for="eventEndDate">End Date</label>
                  <input type="date" id="eventEndDate" name="eventEndDate" required>
                </div>              
                <div class="form-group">
                  <label for="eventLocation">Event Location</label>
                  <input type="text" id="eventLocation" name="eventLocation" required>
                </div>
              
                <div class="form-group">
                  <label for="eventType">Event Type</label>
                  <input type="text" id="eventType" name="eventType" required>                  
                </div>

                <div class="form-group">
                  <label for="customer">Customer</label>
                  <input type="text" id="customer" name="customer" required>                  
                </div>                
              
                <div class="form-group">
                  <label for="responsiblePersonName">Responsible Person Name</label>
                  <input type="text" id="responsiblePersonName" name="responsiblePersonName" required>
                </div>
                
                <div class="form-group">
                  <label for="responsiblePersonPhone">Responsible Person Phone</label>
                  <input type="tel" id="responsiblePersonPhone" name="responsiblePersonPhone" required>
                </div>
              
                <div class="form-group">
                  <label for="responsiblePersonEmail">Responsible Person Email</label>
                  <input type="email" id="responsiblePersonEmail" name="responsiblePersonEmail" required>
                </div>
              
                <div class="form-group">
                  <label for="urgency">Urgency</label>
                  <select id="urgency" name="urgency" required>
                    <option value=""></option>
                    <option value="low">Low</option>
                    <option value="moderate">Moderate</option>
                    <option value="high">High</option>
                  </select>
                </div>

                <div class="form-group">
                  <label for="notes">Notes</label>
                  <textarea id="notes" name="notes"></textarea>
                </div>
            
                <hr>
              
                <!-- Requisition Information -->
                <h3>Requisition Information</h3>

                <div class="form-group">
                  <label for="expectedPickUpDate">Expected Pick-Up Date</label>
                  <input type="date" id="expectedPickUpDate" name="expectedPickUpDate" required>
                </div>
              
                <div class="form-group">
                  <label for="expectedReturnDate">Expected Return Date</label>
                  <input type="date" id="expectedReturnDate" name="expectedReturnDate" required>
                </div>

            <!-- Category Selection -->
            <div class="form-group">
            <label for="category">Category:</label>
            <select id="category" onchange="fetchSubcategories()">
                    <option value="">Select Category</option>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['category_id'] ?>"><?= $cat['category_name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>    
            
            <!-- subcategory Selection -->
            <div class="form-group">
            <label for="subcategory">Subcategory:</label>
                <select id="subcategory" onchange="fetchGroups()" disabled>
                    <option value="">Select Subcategory</option>
                </select>
            </div>  

            <!-- Group Selection -->
            <div class="form-group">
            <label for="group">Group:</label>
                <select id="group" onchange="fetchItems()" disabled>
                    <option value="">Select Group</option>
                </select>
            </div>  

            <!-- Item Selection -->
            <div class="form-group">
            <label for="manualItem">Item:</label>
                <select id="manualItem" disabled>
                    <option value="">Select Item</option>
                </select>
                <button type="button" onclick="addItem()">Add Item</button>
            </div>              

            <div class="form-group">
                <label for="itemCount">Number of Items:</label>
                <input type="number" id="itemCount" name="itemCount" min="1" value="1">
                <button type="button" onclick="autoSelectItems()">Add Items</button>
            </div>


            <fieldset>
              <table id="orderTable">
                  <thead>
                      <tr>
                          <th>Item Name</th>
                          <th>Model</th>
                          <th>Serial Number</th>
                          <th>Action</th>
                      </tr>
                  </thead>
                  <tbody>
                      <!-- Items will appear here -->
                  </tbody>
              </table>
            </fieldset>

            <!-- Buttons -->
            <div class="button-container">
                <button type="submit" class="button-primary">Submit</button>
            </div>            
        </form>
    </div>
    </div>
</body>
</html>
