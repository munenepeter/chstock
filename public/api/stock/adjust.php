<?php
// require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../controllers/stocks.php';

header('Content-Type: application/json');

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'store_manager'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$controller = new StockController();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['stock_id']) || !isset($data['quantity']) || !isset($data['transaction_type'])) {
            throw new Exception('Missing required fields');
        }

        $result = $controller->adjustStock(
            $data['stock_id'],
            $data['quantity'],
            $data['transaction_type'],
            $data['issued_to'] ?? null,
            $data['received_from'] ?? null
        );

        echo json_encode($result);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
