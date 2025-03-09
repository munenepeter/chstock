<?php
require_once __DIR__ . '/../../controllers/orders.php';

header('Content-Type: application/json');

// Check if user is logged in and has appropriate role
session_start();
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'store_manager'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$controller = new OrderController();

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (isset($_GET['id'])) {
                $order = $controller->getById($_GET['id']);
                if ($order) {
                    echo json_encode($order);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Order not found']);
                }
            } else {
                $orders = $controller->getAll();
                echo json_encode($orders);
            }
            break;

        case 'POST':
            // Validate required fields
            if (empty($_POST['supplier_id']) || empty($_POST['items'])) {
                throw new Exception('Supplier and items are required');
            }

            // Format items data
            $items = [];
            foreach ($_POST['items'] as $item) {
                if (!empty($item['stock_id']) && !empty($item['quantity'])) {
                    $items[] = [
                        'stock_id' => $item['stock_id'],
                        'quantity' => (int)$item['quantity']
                    ];
                }
            }

            if (empty($items)) {
                throw new Exception('At least one valid item is required');
            }

            $data = [
                'supplier_id' => $_POST['supplier_id'],
                'items' => $items
            ];

            $id = $controller->create($data);
            echo json_encode(['success' => true, 'id' => $id]);
            break;

        case 'PUT':
            parse_str(file_get_contents("php://input"), $_PUT);
            $id = $_GET['id'];
            $action = $_GET['action'] ?? '';

            if ($action === 'deliver') {
                // Format received items data
                $receivedItems = [];
                foreach ($_PUT['items'] as $item) {
                    if (isset($item['stock_id']) && isset($item['received_quantity'])) {
                        $receivedItems[] = [
                            'stock_id' => $item['stock_id'],
                            'received_quantity' => (int)$item['received_quantity']
                        ];
                    }
                }
                $success = $controller->updateStatus($id, 'delivered', $receivedItems);
            } elseif ($action === 'cancel') {
                $success = $controller->updateStatus($id, 'cancelled');
            } else {
                throw new Exception('Invalid action');
            }

            echo json_encode(['success' => $success]);
            break;

        case 'DELETE':
            $id = $_GET['id'];
            $success = $controller->delete($id);
            echo json_encode(['success' => $success]);
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
