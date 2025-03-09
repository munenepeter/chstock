<?php
include_once __DIR__ . '/../database/database.php';

class OrderController {
    private $db;

    public function __construct() {
        global $db;
        $this->db = $db;
    }

    public function generateOrderNumber() {
        return 'PO-' . date('Ymd') . '-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
    }

    public function getAll() {
        $stmt = $this->db->query("
            SELECT o.*, s.name as supplier_name,
                   COUNT(oi.id) as total_items,
                   SUM(oi.quantity) as total_quantity,
                   SUM(oi.received_quantity) as total_received
            FROM orders o
            LEFT JOIN suppliers s ON o.supplier_id = s.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            GROUP BY o.id
            ORDER BY o.order_date DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        // Get order details
        $stmt = $this->db->prepare("
            SELECT o.*, s.name as supplier_name
            FROM orders o
            LEFT JOIN suppliers s ON o.supplier_id = s.id
            WHERE o.id = ?
        ");
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) return null;

        // Get order items
        $stmt = $this->db->prepare("
            SELECT oi.*, si.name as item_name, si.unit
            FROM order_items oi
            JOIN stock_items si ON oi.stock_id = si.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$id]);
        $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $order;
    }

    public function create($data) {
        try {
            $this->db->beginTransaction();

            // Create order
            $stmt = $this->db->prepare("
                INSERT INTO orders (supplier_id, order_number, status)
                VALUES (?, ?, 'pending')
            ");
            $stmt->execute([
                $data['supplier_id'],
                $this->generateOrderNumber()
            ]);
            $orderId = $this->db->lastInsertId();

            // Add order items
            $stmt = $this->db->prepare("
                INSERT INTO order_items (order_id, stock_id, quantity)
                VALUES (?, ?, ?)
            ");
            foreach ($data['items'] as $item) {
                $stmt->execute([
                    $orderId,
                    $item['stock_id'],
                    $item['quantity']
                ]);
            }

            $this->db->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateStatus($id, $status, $receivedItems = null) {
        try {
            $this->db->beginTransaction();

            // Update order status
            $stmt = $this->db->prepare("
                UPDATE orders 
                SET status = ?,
                    received_date = ?
                WHERE id = ?
            ");
            $receivedDate = $status === 'delivered' ? date('Y-m-d H:i:s') : null;
            $stmt->execute([$status, $receivedDate, $id]);

            // If delivered, update received quantities and create stock transactions
            if ($status === 'delivered' && is_array($receivedItems)) {
                $updateStmt = $this->db->prepare("
                    UPDATE order_items 
                    SET received_quantity = ?
                    WHERE order_id = ? AND stock_id = ?
                ");

                $transactionStmt = $this->db->prepare("
                    INSERT INTO stock_transactions (
                        stock_id, transaction_type, quantity, received_from
                    ) VALUES (?, 'received', ?, ?)
                ");

                $stockUpdateStmt = $this->db->prepare("
                    UPDATE stock_items 
                    SET stock_level = stock_level + ?
                    WHERE id = ?
                ");

                foreach ($receivedItems as $item) {
                    // Update received quantity
                    $updateStmt->execute([
                        $item['received_quantity'],
                        $id,
                        $item['stock_id']
                    ]);

                    // Create stock transaction
                    $transactionStmt->execute([
                        $item['stock_id'],
                        $item['received_quantity'],
                        'Purchase Order #' . $id
                    ]);

                    // Update stock level
                    $stockUpdateStmt->execute([
                        $item['received_quantity'],
                        $item['stock_id']
                    ]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete($id) {
        // Only allow deletion of pending orders
        $stmt = $this->db->prepare("
            SELECT status FROM orders WHERE id = ?
        ");
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order || $order['status'] !== 'pending') {
            throw new Exception('Only pending orders can be deleted');
        }

        try {
            $this->db->beginTransaction();

            // Delete order items first
            $stmt = $this->db->prepare("DELETE FROM order_items WHERE order_id = ?");
            $stmt->execute([$id]);

            // Delete order
            $stmt = $this->db->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->execute([$id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
