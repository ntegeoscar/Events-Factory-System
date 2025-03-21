<?php
require_once 'vendor/autoload.php'; // Ensure TCPDF is autoloaded correctly
include 'db.php'; // Your database connection

// Fetch damage details
$damage_result = [];

$damage_sql = "SELECT i.item_name, i.serial_number, i.model,e.customer, e.event_name, e.responsible_person_name, 
        rh.return_date AS actual_return_date
    FROM rentalhistory rh
    JOIN item i ON i.item_id = rh.item_id
    JOIN `order` o ON rh.order_id = o.order_id
    JOIN requisition r ON o.requisition_id = r.requisition_id
    JOIN events e ON r.event_id = e.event_id    
    WHERE i.availability = 'Damaged' ORDER BY actual_return_date DESC";

$stmt = $conn->prepare($damage_sql);
// $stmt->bind_param("",);
$stmt->execute();
$damage_result = $stmt->get_result();



class CustomPDF extends TCPDF {
    // Custom Header
    public function Header() {
        $this->Image('company_header.jpg', -5, 5, 300);
        $this->SetY(5); // Ensure content starts below the header
    }

    // Custom Footer
    public function Footer() {
        $this->SetY(-15); // Position at 15mm from the bottom
        $this->SetFont('helvetica', 'I', 10);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

// Initialize PDF (landscape gives you more width)
$pdf = new CustomPDF('L', 'mm', 'A4');
$pdf->SetMargins(10, 50, 10);
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);

// damages List Section
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, "Damages Report", 0, 1, 'C');

// Define column headers and widths
$columns = [
    ['title' => 'Item', 'width' => 50],
    ['title' => 'S/N', 'width' => 30],
    ['title' => 'Model', 'width' => 30],
    ['title' => 'Customer', 'width' => 35],
    ['title' => 'Event', 'width' => 50],
    ['title' => 'Responsible', 'width' => 45],
    ['title' => 'Return', 'width' => 37]
];

// Table Header
$pdf->SetFont('helvetica', 'B', 11);
foreach ($columns as $col) {
    $pdf->Cell($col['width'], 10, $col['title'], 1, 0, 'C', false);
}
$pdf->Ln();

// Table Body
$pdf->SetFont('helvetica', '', 9);
$fill = false; // Alternating row color toggle

while ($row = $damage_result->fetch_assoc()) {
    if ($pdf->GetY() > 180) { // Adjust depending on layout
        $pdf->AddPage();
    }

    // Determine max lines for height calc
    $maxLines = max(
        $pdf->getNumLines($row['item_name'], 50),
        $pdf->getNumLines($row['serial_number'], 30),
        $pdf->getNumLines($row['model'], 30),
        $pdf->getNumLines($row['customer'], 35),
        $pdf->getNumLines($row['event_name'], 50),
        $pdf->getNumLines($row['responsible_person_name'], 45),
        $pdf->getNumLines($row['actual_return_date'], 37)
    );
    $rowHeight = $maxLines * 5;

    $pdf->SetFillColor(245, 245, 245); // Light gray for alternating rows

    // Row cells
    $pdf->MultiCell(50, $rowHeight, $row['item_name'], 1, 'L', $fill, 0);
    $pdf->MultiCell(30, $rowHeight, $row['serial_number'], 1, 'C', $fill, 0);
    $pdf->MultiCell(30, $rowHeight, $row['model'], 1, 'C', $fill, 0);
    $pdf->MultiCell(35, $rowHeight, $row['customer'], 1, 'L', $fill, 0);
    $pdf->MultiCell(50, $rowHeight, $row['event_name'], 1, 'L', $fill, 0);
    $pdf->MultiCell(45, $rowHeight, $row['responsible_person_name'], 1, 'L', $fill, 0);
    $pdf->MultiCell(37, $rowHeight, $row['actual_return_date'], 1, 'C', $fill, 1);

    $fill = !$fill; // Toggle color for next row
}


// Output PDF
$pdf->Output("damage_report.pdf", "D");
