<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add autoload for PhpSpreadsheet
$autoloadFile = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoloadFile)) {
    die('Vendor autoload file not found. Please run: composer install');
}
require $autoloadFile;

require 'session_check.php';
require 'db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Get the format parameter
$format = $_GET['format'] ?? 'excel';

// Fetch data from the database
$sql = "SELECT order_id, requisition_id, expected_pick_up_date, expected_return_date FROM `order` ORDER BY order_id DESC";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set headers
$sheet->setCellValue('A1', 'Order ID');
$sheet->setCellValue('B1', 'Requisition ID');
$sheet->setCellValue('C1', 'Expected Pickup Date');
$sheet->setCellValue('D1', 'Expected Return Date');

// Add data
$row = 2;
while ($data = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $row, $data['order_id']);
    $sheet->setCellValue('B' . $row, $data['requisition_id']);
    $sheet->setCellValue('C' . $row, $data['expected_pick_up_date']);
    $sheet->setCellValue('D' . $row, $data['expected_return_date']);
    $row++;
}

// Auto-size columns
foreach (range('A', 'D') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Set the appropriate headers based on format
if ($format === 'pdf') {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment;filename="orders.pdf"');
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf($spreadsheet);
} else {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="orders.xlsx"');
    $writer = new Xlsx($spreadsheet);
}

// Save to php output
$writer->save('php://output');
exit;
?> 