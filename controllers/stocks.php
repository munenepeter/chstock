<?php
include_once __DIR__ . '/../database/database.php';

class StockController {
    private $db;

    public function __construct() {
        global $db;
        $this->db = $db;
    }

    // Create new stock item
    public function create($data) {
        try {
            // Start transaction
            $this->db->beginTransaction();

            // Create stock item with zero initial stock
            $stmt = $this->db->prepare("
                INSERT INTO stock_items (
                    name, category, unit, stock_level, 
                    reorder_level, expiry_date, supplier_id
                ) VALUES (
                    :name, :category, :unit, :stock_level, 
                    :reorder_level, :expiry_date, :supplier_id
                )
            ");

            $stmt->execute([
                'name' => $data['name'],
                'category' => $data['category'],
                'unit' => $data['unit'],
                'stock_level' => $data['stock_level'],
                'reorder_level' => $data['reorder_level'],
                'expiry_date' => $data['expiry_date'],
                'supplier_id' => $data['supplier_id']
            ]);

            $stock_id = $this->db->lastInsertId();

            // Record the initial stock transaction if stock_level > 0
            if (!empty($data['stock_level']) && $data['stock_level'] > 0) {
                $this->recordTransaction(
                    $stock_id, 
                    'received', 
                    $data['stock_level'], 
                    null, 
                    'Initial Stock'
                );
            }

            // Commit transaction
            $this->db->commit();
            return ['success' => true, 'message' => 'Stock item added successfully'];

        } catch (PDOException $e) {
            // Rollback transaction on error
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Read all stock items
    public function getAll() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    si.*, s.name as supplier_name,
                    CASE 
                        WHEN si.stock_level <= si.reorder_level THEN 'low'
                        WHEN si.expiry_date <= DATE('now', '+30 days') THEN 'expiring'
                        ELSE 'normal'
                    END as status
                FROM stock_items si
                LEFT JOIN suppliers s ON si.supplier_id = s.id
                ORDER BY si.name ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Get single stock item
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT si.*, s.name as supplier_name
                FROM stock_items si
                LEFT JOIN suppliers s ON si.supplier_id = s.id
                WHERE si.id = :id
            ");
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    // Update stock item
    public function update($id, $data) {
        try {
            $stmt = $this->db->prepare("
                UPDATE stock_items SET 
                    name = :name,
                    category = :category,
                    unit = :unit,
                    reorder_level = :reorder_level,
                    expiry_date = :expiry_date,
                    supplier_id = :supplier_id
                WHERE id = :id
            ");

            $result = $stmt->execute([
                'id' => $id,
                'name' => $data['name'],
                'category' => $data['category'],
                'unit' => $data['unit'],
                'reorder_level' => $data['reorder_level'],
                'expiry_date' => $data['expiry_date'],
                'supplier_id' => $data['supplier_id']
            ]);

            return ['success' => $result, 'message' => $result ? 'Stock updated successfully' : 'No changes made'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Delete stock item
    public function delete($id) {
        try {
            // Check if there are any transactions
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM stock_transactions WHERE stock_id = :id");
            $stmt->execute(['id' => $id]);
            if ($stmt->fetchColumn() > 0) {
                return ['success' => false, 'message' => 'Cannot delete: Stock item has transaction history'];
            }

            $stmt = $this->db->prepare("DELETE FROM stock_items WHERE id = :id");
            $result = $stmt->execute(['id' => $id]);

            return ['success' => $result, 'message' => $result ? 'Stock deleted successfully' : 'Failed to delete stock'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Record stock transaction
    private function recordTransaction($stock_id, $type, $quantity, $issued_to = null, $received_from = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO stock_transactions (
                    stock_id, transaction_type, quantity, 
                    issued_to, received_from, transaction_date
                ) VALUES (
                    :stock_id, :type, :quantity, 
                    :issued_to, :received_from, DATE('now')
                )
            ");

            return $stmt->execute([
                'stock_id' => $stock_id,
                'type' => $type,
                'quantity' => $quantity,
                'issued_to' => $issued_to,
                'received_from' => $received_from
            ]);
        } catch (PDOException $e) {
            throw $e; // Propagate the error to be handled by the calling method
        }
    }

    // Adjust stock level
    public function adjustStock($id, $quantity, $type, $issued_to = null, $received_from = null) {
        try {
            $this->db->beginTransaction();

            // Update stock level
            $stmt = $this->db->prepare("
                UPDATE stock_items SET 
                    stock_level = stock_level + :quantity
                WHERE id = :id
            ");

            $stmt->execute([
                'id' => $id,
                'quantity' => $type === 'received' ? $quantity : -$quantity
            ]);

            // Record transaction
            $this->recordTransaction($id, $type, $quantity, $issued_to, $received_from);

            $this->db->commit();
            return ['success' => true, 'message' => 'Stock level adjusted successfully'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // Get all suppliers for dropdown
    public function getSuppliers() {
        try {
            $stmt = $this->db->query("SELECT id, name FROM suppliers ORDER BY name ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Get stock transaction history
    public function getTransactionHistory($stock_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    t.*,
                    CASE 
                        WHEN t.transaction_type = 'received' THEN t.received_from
                        ELSE t.issued_to
                    END as transaction_with,
                    si.name as item_name,
                    si.unit
                FROM stock_transactions t
                JOIN stock_items si ON t.stock_id = si.id
                WHERE t.stock_id = ?
                ORDER BY t.transaction_date DESC
            ");
            $stmt->execute([$stock_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stockController = new StockController($db);

    $data = [];
    if(empty($_POST)){
        $data = json_decode(file_get_contents('php://input'), true);
    }else {
        $data = $_POST;
    }
    // $response = ['success' => false, 'message' => 'Invalid action'];
    $response = ['success' => false, 'message' => json_encode($data)];


    switch ($data['action']) {
        case 'create':
            $response = $stockController->create($data);
            break;

        case 'update':
            $response = $stockController->update($data['id'], $data);
            break;

        case 'delete':
            $response = $stockController->delete($data['id']);
            break;

        case 'adjust':
            $response = $stockController->adjustStock(
                $data['id'],
                $data['quantity'],
                $data['type'],
                $data['issued_to'] ?? null,
                $data['received_from'] ?? null
            );
            break;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
