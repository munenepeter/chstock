<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../../../controllers/suppliers.php';
require_once __DIR__ . '/../../../database/database.php';

$controller = new SupplierController($db);

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                echo json_encode($controller->show($_GET['id']));
            } elseif (isset($_GET['search'])) {
                echo json_encode($controller->search($_GET['search']));
            } else {
                echo json_encode($controller->index());
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid request data']);
                exit;
            }
            echo json_encode($controller->store($data));
            break;

        case 'PUT':
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID is required']);
                exit;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid request data']);
                exit;
            }
            echo json_encode($controller->update($_GET['id'], $data));
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID is required']);
                exit;
            }
            echo json_encode($controller->delete($_GET['id']));
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
