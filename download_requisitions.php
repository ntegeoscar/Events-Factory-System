<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add this debug code
$autoloadFile = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoloadFile)) {
    die('Vendor autoload file not found. Please run: composer install');
}
require $autoloadFile;

// Check if the class exists
if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
    die('PhpSpreadsheet class not found. Please check your composer installation.');
}

require 'db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Pdf;

// Get filter status if any
$status = isset($_GET['status']) ? $_GET['status'] : '';
$format = isset($_GET['format']) ? $_GET['format'] : 'excel';

// Debug: Print the SQL query
$sql = "SELECT 
    requisition_id,
    requisition_number,
    requester_id,
    request_date,
    status,
    description
FROM requisitions";

if (!empty($status)) {
    $sql .= " WHERE status = '" . $conn->real_escape_string($status) . "'";
}
$sql .= " ORDER BY requisition_id DESC";

// Debug: Print the query
echo "SQL Query: " . $sql . "<br>";

$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set headers
$sheet->setCellValue('A1', 'Requisition ID');
$sheet->setCellValue('B1', 'Requisition Number');
$sheet->setCellValue('C1', 'Requester ID');
$sheet->setCellValue('D1', 'Request Date');
$sheet->setCellValue('E1', 'Status');
$sheet->setCellValue('F1', 'Description');

// Add data
$row = 2;
while ($data = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $row, $data['requisition_id']);
    $sheet->setCellValue('B' . $row, $data['requisition_number']);
    $sheet->setCellValue('C' . $row, $data['requester_id']);
    $sheet->setCellValue('D' . $row, $data['request_date']);
    $sheet->setCellValue('E' . $row, $data['status']);
    $sheet->setCellValue('F' . $row, $data['description']);
    $row++;
}

// Auto-size columns
foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Set the appropriate headers based on format
if ($format === 'pdf') {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment;filename="requisitions.pdf"');
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf($spreadsheet);
} else {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="requisitions.xlsx"');
    $writer = new Xlsx($spreadsheet);
}

// Save to php output
$writer->save('php://output');
exit; 