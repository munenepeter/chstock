<?php

class PurchaseOrder {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function create($lpoNumber, $roNumber, $supplierId, $procurementMethod, $procurementRef, $dateOfCommitment, $items) {
        try {
            $this->db->beginTransaction();
            
            // Calculate expiry date (30 days from commitment)
            $expiryDate = date('Y-m-d', strtotime($dateOfCommitment . ' + 30 days'));
            
            // Insert PO
            $stmt = $this->db->prepare("
                INSERT INTO purchase_orders (
                    lpo_number, ro_number, supplier_id, procurement_method,
                    procurement_reference, date_of_commitment, expiry_date, total_amount
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 0)
            ");
            $stmt->execute([
                $lpoNumber, $roNumber, $supplierId, $procurementMethod,
                $procurementRef, $dateOfCommitment, $expiryDate
            ]);
            
            $totalAmount = 0;
            
            // Insert items and calculate total
            foreach ($items as $item) {
                $stmt = $this->db->prepare("
                    INSERT INTO purchase_order_items (
                        lpo_number, item_id, quantity, unit_price, total_price
                    ) VALUES (?, ?, ?, ?, ?)
                ");
                
                $totalPrice = $item['quantity'] * $item['unit_price'];
                $totalAmount += $totalPrice;
                
                $stmt->execute([
                    $lpoNumber,
                    $item['item_id'],
                    $item['quantity'],
                    $item['unit_price'],
                    $totalPrice
                ]);
            }
            
            // Update PO total
            $stmt = $this->db->prepare("
                UPDATE purchase_orders
                SET total_amount = ?
                WHERE lpo_number = ?
            ");
            $stmt->execute([$totalAmount, $lpoNumber]);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function get($lpoNumber) {
        $stmt = $this->db->prepare("
            SELECT po.*, s.name as supplier_name, ro.department_id
            FROM purchase_orders po
            JOIN suppliers s ON po.supplier_id = s.id
            JOIN requisition_orders ro ON po.ro_number = ro.ro_number
            WHERE po.lpo_number = ?
        ");
        $stmt->execute([$lpoNumber]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getItems($lpoNumber) {
        $stmt = $this->db->prepare("
            SELECT poi.*, i.name as item_name, i.unit_of_issue
            FROM purchase_order_items poi
            JOIN items i ON poi.item_id = i.id
            WHERE poi.lpo_number = ?
        ");
        $stmt->execute([$lpoNumber]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function list($status = null, $supplierId = null) {
        $sql = "
            SELECT po.*, s.name as supplier_name, ro.department_id
            FROM purchase_orders po
            JOIN suppliers s ON po.supplier_id = s.id
            JOIN requisition_orders ro ON po.ro_number = ro.ro_number
            WHERE 1=1
        ";
        $params = [];
        
        if ($status) {
            $sql .= " AND po.status = ?";
            $params[] = $status;
        }
        
        if ($supplierId) {
            $sql .= " AND po.supplier_id = ?";
            $params[] = $supplierId;
        }
        
        $sql .= " ORDER BY po.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function updateStatus($lpoNumber, $status) {
        $stmt = $this->db->prepare("
            UPDATE purchase_orders
            SET status = ?
            WHERE lpo_number = ?
        ");
        return $stmt->execute([$status, $lpoNumber]);
    }
    
    public function extendExpiry($lpoNumber) {
        $stmt = $this->db->prepare("
            UPDATE purchase_orders
            SET expiry_date = DATE_ADD(expiry_date, INTERVAL 30 DAY),
                status = 'extended'
            WHERE lpo_number = ?
        ");
        return $stmt->execute([$lpoNumber]);
    }
    
    public function recordDelivery($lpoNumber, $deliveryDate) {
        try {
            $this->db->beginTransaction();
            
            $po = $this->get($lpoNumber);
            if (!$po) {
                throw new Exception("PO not found");
            }
            
            // Calculate days taken
            $daysTaken = (strtotime($deliveryDate) - strtotime($po['date_of_commitment'])) / (60 * 60 * 24);
            
            // Calculate performance score
            $performanceScore = 100; // Default for on-time delivery
            if ($daysTaken > 30) {
                $performanceScore = 30; // Below 15 days overdue
            } elseif ($daysTaken > 15) {
                $performanceScore = 55; // 15 days overdue
            }
            
            // Record supplier performance
            $stmt = $this->db->prepare("
                INSERT INTO supplier_performance (
                    supplier_id, lpo_number, delivery_date,
                    days_taken, performance_score
                ) VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $po['supplier_id'],
                $lpoNumber,
                $deliveryDate,
                $daysTaken,
                $performanceScore
            ]);
            
            // Update PO status
            $this->updateStatus($lpoNumber, 'completed');
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
