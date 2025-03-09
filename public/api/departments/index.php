<?php
// require_once __DIR__ . '/../../../includes/session.php';
require_once __DIR__ . '/../../../database/database.php';
require_once __DIR__ . '/../../../controllers/DepartmentController.php';

header('Content-Type: application/json');

$controller = new DepartmentController($db);

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'search' && isset($_GET['term'])) {
                echo json_encode($controller->search($_GET['term']));
            } elseif ($action === 'requisition_orders' && isset($_GET['id'])) {
                echo json_encode($controller->getRequisitionOrders($_GET['id']));
            } else {
                echo json_encode($controller->index());
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
                break;
            }
            echo json_encode($controller->store($data));
            break;

        case 'PUT':
            if (!isset($_GET['id'])) {
                echo json_encode(['success' => false, 'error' => 'Department ID is required']);
                break;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
                break;
            }
            echo json_encode($controller->update($_GET['id'], $data));
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) {
                echo json_encode(['success' => false, 'error' => 'Department ID is required']);
                break;
            }
            echo json_encode($controller->delete($_GET['id']));
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
