<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug information
echo "Current directory: " . __DIR__ . "<br>";
echo "Autoload path: " . __DIR__ . '/vendor/autoload.php' . "<br>";
echo "Autoload exists: " . (file_exists(__DIR__ . '/vendor/autoload.php') ? 'Yes' : 'No') . "<br>";

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die('Vendor autoload file not found. Please run: composer install');
}

require __DIR__ . '/vendor/autoload.php';

// Check for required classes
if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
    die('PhpSpreadsheet class not found. Check composer installation and autoloader.');
}
if (!class_exists('Mpdf\Mpdf')) {
    die('Mpdf class not found. Check composer installation and autoloader.');
}

require 'db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $format = isset($_GET['format']) ? $_GET['format'] : 'excel';

    // Fetch events data
    $sql = "SELECT event_id, event_name, event_date, event_location FROM events ORDER BY event_id DESC";
    $result = $conn->query($sql);

    // Create new Spreadsheet object
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set headers
    $sheet->setCellValue('A1', 'Event ID');
    $sheet->setCellValue('B1', 'Event Name');
    $sheet->setCellValue('C1', 'Date');
    $sheet->setCellValue('D1', 'Location');

    // Add data
    $row = 2;
    while ($data = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $row, $data['event_id']);
        $sheet->setCellValue('B' . $row, $data['event_name']);
        $sheet->setCellValue('C' . $row, $data['event_date']);
        $sheet->setCellValue('D' . $row, $data['event_location']);
        $row++;
    }

    // Auto-size columns
    foreach (range('A', 'D') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    if ($format === 'pdf') {
        // PDF export
        IOFactory::registerWriter('Pdf', \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class);
        $writer = IOFactory::createWriter($spreadsheet, 'Pdf');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment;filename="events.pdf"');
    } else {
        // Excel export
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="events.xlsx"');
    }

    // Clear any previous output
    ob_end_clean();

    // Save to php output
    $writer->save('php://output');
    exit;
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
} 