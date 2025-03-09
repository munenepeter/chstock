<?php
// require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../controllers/stocks.php';

header('Content-Type: application/json');

// Check if user is logged in
// if (!isset($_SESSION['user'])) {
//     http_response_code(403);
//     echo json_encode(['error' => 'Unauthorized']);
//     exit;
// }

$controller = new StockController();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['stock_id'])) {
        $transactions = $controller->getTransactionHistory($_GET['stock_id']);
        echo json_encode($transactions);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
