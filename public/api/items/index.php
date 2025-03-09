<?php
require_once __DIR__ . '/../../../database/database.php';
require_once __DIR__ . '/../../../controllers/items.php';

header('Content-Type: application/json');

// Initialize controller
$controller = new ItemController($db);

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $response = $controller->getById($_GET['id']);
            } else {
                $activeOnly = isset($_GET['active']) ? filter_var($_GET['active'], FILTER_VALIDATE_BOOLEAN) : true;
                $response = $controller->getAll($activeOnly);
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $response = $controller->create($data);
            break;
            
        case 'PUT':
            if (!isset($_GET['id'])) {
                throw new Exception("Item ID is required");
            }
            $data = json_decode(file_get_contents('php://input'), true);
            $response = $controller->update($_GET['id'], $data);
            break;
            
        case 'DELETE':
            if (!isset($_GET['id'])) {
                throw new Exception("Item ID is required");
            }
            $response = $controller->delete($_GET['id']);
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
