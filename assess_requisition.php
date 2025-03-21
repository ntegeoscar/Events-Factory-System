<?php
require 'session_check.php';

// Only Super Admins can access this
if ($user_role != 1) {
  echo "❌ Access Denied!";
  exit;
}

// Include the database connection file
require 'db2.php';

require 'vendor/autoload.php'; // Ensure PHPMailer is loaded
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get the requisition_id and success message from the URL
$requisition_id = $_GET['requisition_id'] ?? null;
$success = $_GET['success'] ?? null;

if (!$requisition_id) {
    echo "No requisition ID provided.";
    exit;
}

// Fetch requisition details
$requisitionQuery = "SELECT * FROM requisition WHERE requisition_id = ?";
$requisitionStmt = $conn->prepare($requisitionQuery);
$requisitionStmt->execute([$requisition_id]);
$requisition = $requisitionStmt->fetch(PDO::FETCH_ASSOC);

if (!$requisition) {
    echo "Requisition not found.";
    exit;
}

// Fetch event details based on event_id in the requisition
$eventQuery = "SELECT * FROM events WHERE event_id = ?";
$eventStmt = $conn->prepare($eventQuery);
$eventStmt->execute([$requisition['event_id']]);
$event = $eventStmt->fetch();

// Handle POST request for approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $approval_status = $_POST['approval_status'];
  $expected_pick_up_date = $requisition['expected_pick_up_date'];
  $expected_return_date = $requisition['expected_return_date'];
  $remarks = $_POST['remarks'] ?? '';

    // Start transaction
    $conn->beginTransaction();

    try {
        if ($approval_status === 'Approved') {
            // Update requisition
            $updateQuery = "UPDATE requisition SET approval_status = ?, remarks = ? WHERE requisition_id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->execute([$approval_status, $remarks, $requisition_id]);

            // Create order
            $orderQuery = "INSERT INTO `order` (requisition_id, status, expected_pick_up_date, expected_return_date, created_at) 
                          VALUES (?, 'Awaiting', ?, ?, NOW())";
            $orderStmt = $conn->prepare($orderQuery);
            $orderStmt->execute([$requisition_id, $expected_pick_up_date, $expected_return_date]);
            $order_id = $conn->lastInsertId();

            // Update items linked to the requisition
            $updateItemsQuery = "UPDATE `item` SET `current_order_id`= ?, reserved_requisition_id = NULL, `availability` = 'Rented' WHERE `reserved_requisition_id`= ?";
            $updateItemsStmt = $conn->prepare($updateItemsQuery);
            $updateItemsStmt->execute([$order_id, $requisition_id]);

            if ($updateItemsStmt->rowCount() === 0) {
                throw new Exception("No items were updated. Ensure that items are linked to this requisition.");
            }



            // ✅ Send Email Notification to Admin
            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->Host = 'smtp.sendgrid.net';
            $mail->SMTPAuth = true;
            $mail->Username = 'apikey';

            // Load .env file
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
            $dotenv->safeLoad();

            $mail->Password = $_ENV['SENDGRID_API_KEY']; // Secure API key
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('eventsfactorysystem@gmail.com', 'Inventory System');
            $mail->addReplyTo('info@eventsfactory.rw', 'Events Factory Support');
            $mail->addAddress('ntegeoscar9@gmail.com', 'StoreKeeper');

            $mail->Subject = "New Order Created (ID: $order_id)";
            $mail->Body = "
            Hello StoreKeeper,

            A new order has been created and needs your attention.

            📌 **Order ID:** $order_id
            📌 **Expected Pick-up Date:** $expected_pick_up_date
            📌 **Expected Return Date:** $expected_return_date

            Please review and proceed with the necessary actions.

            Best Regards,
            Inventory System
            ";

            if (!$mail->send()) {
                throw new Exception("Email sending failed: " . $mail->ErrorInfo);
            }
            

            $mail->clearAddresses();
            $mail->addAddress('danganza266@gmail.com', 'Operator'); //Operator email
            $mail->Subject = "Your Requisition Has Been Approved";
            $mail->Body = "
            Hello,

            Your requisition (ID: $requisition_id) has been approved.

            📌 **Order ID:** $order_id
            📌 **Expected Pick-up Date:** $expected_pick_up_date
            📌 **Expected Return Date:** $expected_return_date
            📌 **Remarks:** $remarks

            Please proceed accordingly.

            Best Regards,  
            Inventory System  
            ";

            // Send the email
            if (!$mail->send()) {
              throw new Exception("Email sending failed: " . $mail->ErrorInfo);
            }

            // Commit transaction
            $conn->commit();

            // ✅ Set success feedback message
            $feedback = "Requisition approved, and an order was created successfully.";
            $feedback_class = "success";

        } elseif ($approval_status === 'Rejected') {
            // Reset reserved items
            $resetItemsQuery = "UPDATE item SET availability = 'Available', reserved_requisition_id = NULL WHERE reserved_requisition_id = ?";
            $resetItemsStmt = $conn->prepare($resetItemsQuery);
            $resetItemsStmt->execute([$requisition_id]);

            // Update requisition status
            $updateQuery = "UPDATE requisition SET approval_status = ?, remarks = ? WHERE requisition_id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->execute([$approval_status, $remarks, $requisition_id]);

            $conn->commit();

            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->Host = 'smtp.sendgrid.net';
            $mail->SMTPAuth = true;
            $mail->Username = 'apikey';

            // Load .env file
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
            $dotenv->safeLoad();

            $mail->Password = $_ENV['SENDGRID_API_KEY']; // Secure API key
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('eventsfactorysystem@gmail.com', 'Inventory System');
            $mail->addReplyTo('info@eventsfactory.rw', 'Events Factory Support');
            $mail->addAddress('ntegeoscar9@gmail.com', 'Operator'); // Operator email address

            $mail->Subject = "Your Requisition Has Been Rejected";
            $mail->Body = "
            Hello,

            Your requisition (ID: $requisition_id) has been rejected.

            📌 **Remarks:** $remarks

            Please proceed accordingly.

            Best Regards,  
            Inventory System  
            ";

            if (!$mail->send()) {
                throw new Exception("Email sending failed: " . $mail->ErrorInfo);
            }            

            // ✅ Set rejection feedback
            $feedback = "Requisition rejected and items reset to available.";
            $feedback_class = "success";
        }

    } catch (Exception $e) {
        // Rollback transaction on failure
        $conn->rollBack();
        $feedback = "Error processing request: " . $e->getMessage();
        $feedback_class = "error";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Assess Requisition</title>
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
    

            <div class="form-card">
            <h2>Assess Requisition</h2>

            <!-- Feedback Message -->
            <?php if (isset($feedback)): ?>
              <div class="feedback_<?= $feedback_class; ?>">
                  <?= $feedback; ?>
              </div>
            <?php endif; ?> 

            <!-- Requisition Details -->
            <h2>Requisition Details</h2>
            <h4>Requisition ID: <?= htmlspecialchars($requisition['requisition_id']) ?></h4>
            <p>Expected Pick-up Date: <?= htmlspecialchars($requisition['expected_pick_up_date']) ?></p>
            <p>Expected Return Date: <?= htmlspecialchars($requisition['expected_return_date']) ?></p>
            <p>Approval Status: <?= htmlspecialchars($requisition['approval_status']) ?></p>
            <p>Remarks: <?= htmlspecialchars($requisition['remarks']) ?></p>    
            <p>Items List:</p>
                <ul>
                    <?php 
                    $items = explode('|', $requisition['Items_list']); // Split string into an array
                    foreach ($items as $item): ?>
                        <li><?= htmlspecialchars(trim($item)) ?></li> 
                    <?php endforeach; ?>
                </ul>

            <!-- Event Details -->
            <h2>Event Details</h2>
            <p>Event Name: <?= htmlspecialchars($event['event_name']) ?></p>
            <p>Event Date: <?= htmlspecialchars($event['event_date']) ?></p>
            <p>Event Location: <?= htmlspecialchars($event['event_location']) ?></p>
            <p>Event Type: <?= htmlspecialchars($event['event_type']) ?></p>
            <p>Responsible Person: <?= htmlspecialchars($event['responsible_person_name']) ?></p>
            <p>Contact Email: <?= htmlspecialchars($event['responsible_person_email']) ?></p>
            <p>Contact Phone: <?= htmlspecialchars($event['responsible_person_phone']) ?></p>
            <p>Urgency: <?= htmlspecialchars($event['urgency']) ?></p>

            <!-- Form to update the requisition -->
            <form method="post">
                <div class="form-group">
                  <label for="approval_status">Approval Status:</label>
                  <select name="approval_status" id="approval_status" required>
                    <option value="Approved">Approved</option>
                    <option value="Rejected">Rejected</option>
                  </select> 
                </div>     

                <!-- Remarks -->
                <div class="form-group">
                    <label for="remarks">Remarks</label>
                    <textarea name="remarks" id="remarks" rows="4" placeholder="Add any remarks here..."><?= htmlspecialchars($requisition['remarks']) ?></textarea>
                </div>                

                <!-- Submit Button -->
                <div class="button-container">
                    <button type="submit" class="button-primary">Submit</button>
                </div>                
            </form>
            </div>
 
    </div>
</body>
</html>
