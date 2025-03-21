<?php
require_once 'vendor/autoload.php'; // Ensure TCPDF is autoloaded correctly
include 'db.php'; // Your database connection

// Fetch event details
$event_id = $_GET['event_id'] ?? null; // Get the event ID from the URL
if (!$event_id) {
    die("Event ID is required.");
}

$sql = "SELECT e.*, r.* FROM events e LEFT JOIN requisition r ON e.event_id = r.event_id WHERE e.event_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

// Fetch order details
$order_data = null;
if (!empty($data['requisition_id'])) {
    $sql_order = "SELECT * FROM `order` WHERE requisition_id = ?";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bind_param("i", $data['requisition_id']);
    $stmt_order->execute();
    $order_data = $stmt_order->get_result()->fetch_assoc();
}


// Fetch item details
$item_result = [];
if (!empty($order_data['order_id'])) {
    $item_sql = "SELECT i.item_name, i.serial_number, i.model, rh.return_date, rh.condition_on_return, i.remarks 
    FROM item i LEFT JOIN rentalhistory rh ON i.item_id = rh.item_id AND rh.order_id = ? 
    LEFT JOIN `order` o ON i.current_order_id = o.order_id AND o.order_id = ? 
    LEFT JOIN requisition req ON o.requisition_id = req.requisition_id 
    LEFT JOIN events e ON req.event_id = e.event_id WHERE i.current_order_id = ? OR rh.order_id = ?";

    $stmt = $conn->prepare($item_sql);
    $stmt->bind_param("iiii", $order_data['order_id'], $order_data['order_id'], $order_data['order_id'], $order_data['order_id']);
    $stmt->execute();
    $item_result = $stmt->get_result();
}


class CustomPDF extends TCPDF {
    // Custom Header
    public function Header() {
        $this->Image('company_header.jpg', -5, 5, 215);
        $this->SetY(50); // Ensure content starts below the header
    }

    // Custom Footer
    public function Footer() {
        $this->SetY(-15); // Position at 15mm from the bottom
        $this->SetFont('helvetica', 'I', 10);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

// Initialize PDF
$pdf = new CustomPDF();
$pdf->SetMargins(10, 50, 10); // Left, Top (ensures content starts below header), Right
$pdf->SetAutoPageBreak(true, 20); // Auto-break with margin at 20mm
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);

// Event Details Section
$pdf->SetFont('helvetica', 'B', 14); // 'B' makes it bold, 14 is the font size
$pdf->Cell(0, 10, "Details", 0, 1, 'C');
$pdf->SetFont('helvetica', '', 12); // Reset font for normal text

$pdf->SetX(10); // Left column position
$pdf->Cell(50, 10, "Event Name:", 0, 0); // 0 = No line break
$pdf->Cell(50, 10, $data['event_name'], 0, 0); // No line break

$pdf->SetX(110); // Move to the right column position
$pdf->Cell(50, 10, "Event Date:", 0, 0);
$pdf->Cell(50, 10, $data['event_date'], 0, 1); // 1 = Move to the next line after this


$pdf->SetX(10); // Left column position
$pdf->Cell(50, 10, "Event Location:", 0, 0); // 0 = No line break
$pdf->Cell(50, 10, $data['event_location'], 0, 0); // No line break

$pdf->SetX(110); // Move to the right column position
$pdf->Cell(50, 10, "Customer:", 0, 0);
$pdf->Cell(50, 10, $data['customer'], 0, 1); // 1 = Move to the next line after this


$pdf->SetX(10); // Left column position
$pdf->Cell(50, 10, "Responsible Person:", 0, 0); // 0 = No line break
$pdf->Cell(50, 10, $data['responsible_person_name'], 0, 0); // No line break

$pdf->SetX(110); // Move to the right column position
$pdf->Cell(50, 10, "Phone:", 0, 0);
$pdf->Cell(50, 10, $data['responsible_person_phone'], 0, 1); // 1 = Move to the next line after this


$pdf->Cell(50, 10, "Notes:", 0);
$pdf->Cell(0, 10, $data['notes'], 0, 1);
$pdf->Ln(5);

// Other Details Section
$pdf->SetFont('helvetica', 'B', 14); // 'B' makes it bold, 14 is the font size
$pdf->Cell(0, 10, "Other Details", 0, 1, 'C');
$pdf->SetFont('helvetica', '', 12); // Reset font for normal text
// Set starting Y position
$startY = $pdf->GetY();

// Left Column (Requisition Details)
$pdf->SetXY(10, $startY); // Move to left side
$pdf->Cell(50, 10, "Requisition Status:", 0);
$pdf->Cell(50, 10, !empty($data['requisition_id']) ? $data['approval_status'] : "N/A", 0, 1);

$pdf->SetX(10); 
$pdf->Cell(50, 10, "Expected Pickup:", 0);
$pdf->Cell(50, 10, !empty($data['requisition_id']) ? $data['expected_pick_up_date'] : "N/A", 0, 1);

$pdf->SetX(10); 
$pdf->Cell(50, 10, "Expected Pickup:", 0);
$pdf->Cell(50, 10, !empty($data['requisition_id']) ? $data['expected_return_date'] : "N/A", 0, 1);    

// Right Column (Order Details)
$pdf->SetXY(110, $startY); // Move to right side
$pdf->Cell(50, 10, "Order Status:", 0);
$pdf->Cell(50, 10, !empty($order_data) ? $order_data['status'] : "N/A", 0, 1);

$pdf->SetX(110); 
$pdf->Cell(50, 10, "Actual Return:", 0);
$pdf->Cell(50, 10, !empty($order_data) ? $order_data['actual_pick_up_date'] : "N/A", 0, 1);

$pdf->SetX(110); 
$pdf->Cell(50, 10, "Actual Return:", 0);
$pdf->Cell(50, 10, !empty($order_data) ? $order_data['actual_return_date'] : "N/A", 0, 1);

$pdf->Ln(5);



// Items List Section
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, "Items", 0, 1, 'C');

// Table Header
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(80, 10, "Item Name", 1, 0, 'C');
$pdf->Cell(30, 10, "Serial Num", 1, 0, 'C');
$pdf->Cell(30, 10, "Model", 1, 0, 'C');
$pdf->Cell(40, 10, "Condition on Return", 1, 1, 'C');

$pdf->SetFont('helvetica', '', 10);

// Loop through items (Ensure new pages are added if needed)
while ($row = $item_result->fetch_assoc()) {
    if ($pdf->GetY() > 260) { // Check if close to bottom
        $pdf->AddPage();
    }

    // Get the height based on the number of lines in the longest text column
    $cellHeight = 10; // Default row height
    $lineHeight = 5;  // Line height for wrapped text
    $maxLines = max(
        $pdf->getNumLines($row['item_name'], 80),
        $pdf->getNumLines($row['serial_number'], 30),
        $pdf->getNumLines($row['model'], 30),
        $pdf->getNumLines($row['condition_on_return'], 40)
    );
    $rowHeight = $maxLines * $lineHeight; // Adjusted row height

    // Save current position
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    // Print Item Name with wrapping
    $pdf->MultiCell(80, $rowHeight, $row['item_name'], 1, 'L', false);

    // Move to the next cell in the same row
    $pdf->SetXY($x + 80, $y);
    $pdf->Cell(30, $rowHeight, $row['serial_number'], 1, 0, 'C');

    $pdf->Cell(30, $rowHeight, $row['model'], 1, 0, 'C');

    $pdf->Cell(40, $rowHeight, $row['condition_on_return'], 1, 1, 'C'); // Move to next line
}


// Output PDF
$pdf->Output("event_details.pdf", "D");
