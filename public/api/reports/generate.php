<?php
require_once __DIR__ . '/../../../controllers/reports.php';
require_once __DIR__ . '/../../../database/database.php';

// Initialize database connection
$db = (new Database())->getConnection();
$reportsController = new ReportsController($db);

try {
    // Get report type and date range if provided
    $type = $_GET['type'] ?? '';
    $startDate = $_GET['start_date'] ?? null;
    $endDate = $_GET['end_date'] ?? null;

    if (empty($type)) {
        throw new Exception('Report type is required');
    }

    $data = [];
    $title = '';

    switch ($type) {
        case 'stock_levels':
            $data = $reportsController->getStockLevelsReport();
            $title = 'Stock Levels Report';
            break;

        case 'transactions':
            $data = $reportsController->getTransactionsReport($startDate, $endDate);
            $title = 'Stock Transactions Report';
            if ($startDate && $endDate) {
                $title .= " ($startDate to $endDate)";
            }
            break;

        case 'low_stock':
            $data = $reportsController->getLowStockReport();
            $title = 'Low Stock Items Report';
            break;

        case 'expiring':
            $data = $reportsController->getExpiringItemsReport();
            $title = 'Expiring Items Report';
            break;

        default:
            throw new Exception('Invalid report type');
    }

    if (empty($data)) {
        throw new Exception('No data available for this report');
    }

    // Generate PDF
    $reportsController->generatePDF($type, $data, $title);
    exit();

} catch (Exception $e) {
    // If there's an error, return JSON error response
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Error generating report: ' . $e->getMessage()
    ]);
    exit();
}
