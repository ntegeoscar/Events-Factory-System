<?php
require_once 'vendor/autoload.php'; // Ensure TCPDF is autoloaded correctly
include 'db.php'; // Your database connection

// Fetch overdue details
$overdue_result = [];

$overdue_sql = "SELECT e.event_name, e.customer, o.expected_return_date, e.responsible_person_name
FROM `order` o
JOIN requisition r ON o.requisition_id = r.requisition_id
JOIN events e ON r.event_id = e.event_id
WHERE o.status = 'overdue' ORDER BY expected_return_date DESC";

$stmt = $conn->prepare($overdue_sql);
// $stmt->bind_param("",);
$stmt->execute();
$overdue_result = $stmt->get_result();



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


// overdues List Section
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, "overdues", 0, 1, 'C');

// Table Header
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(60, 10, "Event", 1, 0, 'C');
$pdf->Cell(40, 10, "Customer", 1, 0, 'C');
$pdf->Cell(35, 10, "Expected return", 1, 0, 'C');
$pdf->Cell(50, 10, "Responnsible person", 1, 1, 'C');

$pdf->SetFont('helvetica', '', 10);

// Loop through overdues (Ensure new pages are added if needed)
while ($row = $overdue_result->fetch_assoc()) {
    if ($pdf->GetY() > 260) { // Check if close to bottom
        $pdf->AddPage();
    }

    // Get the height based on the number of lines in the longest text column
    $cellHeight = 10; // Default row height
    $lineHeight = 5;  // Line height for wrapped text
    $maxLines = max(
        $pdf->getNumLines($row['event_name'], 60),
        $pdf->getNumLines($row['customer'], 40),
        $pdf->getNumLines($row['expected_return_date'], 35),
        $pdf->getNumLines($row['responsible_person_name'], 50)
    );
    $rowHeight = $maxLines * $lineHeight; // Adjusted row height

    // Save current position
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    // Print overdue Name with wrapping
    $pdf->MultiCell(60, $rowHeight, $row['event_name'], 1, 'L', false);

    // Move to the next cell in the same row
    $pdf->SetXY($x + 60, $y);
    $pdf->Cell(40, $rowHeight, $row['customer'], 1, 0, 'C');

    $pdf->Cell(35, $rowHeight, $row['expected_return_date'], 1, 0, 'C');

    $pdf->Cell(50, $rowHeight, $row['responsible_person_name'], 1, 1, 'C'); // Move to next line
}


// Output PDF
$pdf->Output("Overdue_report.pdf", "D");
