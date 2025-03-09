<?php
require_once __DIR__ . '/../../../database/database.php';
require_once __DIR__ . '/../../../controllers/acquisition.php';

header('Content-Type: application/json');

// Initialize controller
$controller = new AcquisitionController($db);

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];

$endpoint = $_POST["endpoint"] ?? $_GET["endpoint"] ?? null;

try {
    switch ($method) {
        case 'GET':
            if ($endpoint === 'requisitions') {
                $status = $_GET['status'] ?? null;
                $departmentId = $_GET['department_id'] ?? null;
                $response = $controller->listRequisitions($status, $departmentId);
            } elseif ($endpoint === 'view') {
                $roNumber = $_GET["roNumber"];
                $response = $controller->getRequisition($roNumber);
            } elseif ($endpoint === 'purchase-orders') {
                $status = $_GET['status'] ?? null;
                $supplierId = $_GET['supplier_id'] ?? null;
                $response = $controller->listPurchaseOrders($status, $supplierId);
            } elseif (preg_match('/^purchase-orders\/([^\/]+)$/', $endpoint, $matches)) {
                $lpoNumber = $matches[1];
                $response = $controller->getPurchaseOrder($lpoNumber);
            } else {
                throw new Exception("Invalid endpoint GET {$endpoint}");
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data) {
                $data = $_POST;
            }

            if ($endpoint === 'requisitions') {
                $response = $controller->createRequisition($data);
            } elseif ($endpoint === 'purchase-orders') {
                $response = $controller->createPurchaseOrder($data);
            } elseif (preg_match('/^purchase-orders\/([^\/]+)\/extend$/', $endpoint, $matches)) {
                $lpoNumber = $matches[1];
                $response = $controller->extendPurchaseOrder($lpoNumber);
            } elseif (preg_match('/^purchase-orders\/([^\/]+)\/delivery$/', $endpoint, $matches)) {
                $lpoNumber = $matches[1];
                if (!isset($data['delivery_date'])) {
                    throw new Exception("Delivery date is required");
                }
                $response = $controller->recordDelivery($lpoNumber, $data['delivery_date']);
            } else {
                throw new Exception("Invalid endpoint {$endpoint}");
            }
            break;

        default:
            throw new Exception("Method not allowed");
    }

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
