<?php
// require_once __DIR__ . '/../../../controllers/auth.php';
require_once __DIR__ . '/../../../controllers/stocks.php';

header('Content-Type: application/json');

// Check if user is logged in and has appropriate role
// if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'store_manager'])) {
//     http_response_code(403);
//     echo json_encode(['message' => 'Unauthorized, Only Admins can access this page']);
//     exit;
// }

$controller = new StockController();

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (isset($_GET['id'])) {
                $stock = $controller->getById($_GET['id']);
                if ($stock) {
                    echo json_encode($stock);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Stock not found']);
                }
            } else {
                $stocks = $controller->getAll();
                echo json_encode($stocks);
            }
            break;

        case 'POST':
            // Validate required fields
            $requiredFields = ['name', 'category', 'unit', 'stock_level', 'reorder_level', 'expiry_date', 'supplier_id'];
            $data = json_decode(file_get_contents('php://input'), true);
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Field '$field' is required");
                }
            }

            $result = $controller->create($data);
            if ($result['success']) {
                http_response_code(201);
            }
            echo json_encode($result);
            break;

        case 'PUT':
            if (!isset($_GET['id'])) {
                throw new Exception('Stock ID is required');
            }

            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data)) {
                throw new Exception('No data provided');
            }

            $result = $controller->update($_GET['id'], $data);
            echo json_encode($result);
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) {
                throw new Exception('Stock ID is required');
            }

            $result = $controller->delete($_GET['id']);
            echo json_encode($result);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
