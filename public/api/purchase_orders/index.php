<?php
require_once __DIR__ . '/../../../database/database.php';
require_once __DIR__ . '/../../../controllers/PurchaseOrderController.php';

header('Content-Type: application/json');

$controller = new PurchaseOrderController($db);

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['lpo_number'])) {
                echo json_encode($controller->get($_GET['lpo_number']));
            } else {
                echo json_encode($controller->list(isset($_GET['status']) ? $_GET['status'] : null, isset($_GET['supplier_id']) ? $_GET['supplier_id'] : null));
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
                break;
            }
            echo json_encode($controller->create($data));
            break;

        case 'PUT':
            if (!isset($_GET['lpo_number'])) {
                echo json_encode(['success' => false, 'error' => 'LPO number is required']);
                break;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
                break;
            }
            echo json_encode($controller->updateStatus($_GET['lpo_number'], $data['status']));
            break;

        case 'DELETE':
            if (!isset($_GET['lpo_number'])) {
                echo json_encode(['success' => false, 'error' => 'LPO number is required']);
                break;
            }
            echo json_encode($controller->recordDelivery($_GET['lpo_number'], $data['delivery_date']));
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
