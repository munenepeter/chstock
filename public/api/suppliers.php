<?php
require_once __DIR__ . '/../../controllers/suppliers.php';

header('Content-Type: application/json');

$controller = new SupplierController();

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (isset($_GET['id'])) {
                $supplier = $controller->getById($_GET['id']);
                echo json_encode($supplier);
            } else {
                $suppliers = $controller->getAll();
                echo json_encode($suppliers);
            }
            break;

        case 'POST':
            $data = [
                'name' => $_POST['name'],
                'contact' => $_POST['contact'] ?? null,
                'email' => $_POST['email'] ?? null
            ];
            $id = $controller->create($data);
            echo json_encode(['success' => true, 'id' => $id]);
            break;

        case 'PUT':
            parse_str(file_get_contents("php://input"), $_PUT);
            $id = $_GET['id'];
            $data = [
                'name' => $_PUT['name'],
                'contact' => $_PUT['contact'] ?? null,
                'email' => $_PUT['email'] ?? null
            ];
            $success = $controller->update($id, $data);
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
