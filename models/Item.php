<?php

class Item {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getAll($activeOnly = true) {
        $sql = "SELECT * FROM items";
        if ($activeOnly) {
            $sql .= " WHERE active = 1";
        }
        $sql .= " ORDER BY name";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM items WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO items (name, unit_price, unit_of_issue, purchase_limit)
                VALUES (?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $data['name'],
                $data['unit_price'],
                $data['unit_of_issue'],
                $data['purchase_limit']
            ]);
            
            return $result ? $this->db->lastInsertId() : false;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    public function update($id, $data) {
        try {
            $stmt = $this->db->prepare("
                UPDATE items 
                SET name = ?, 
                    unit_price = ?, 
                    unit_of_issue = ?, 
                    purchase_limit = ?,
                    active = ?
                WHERE id = ?
            ");
            
            return $stmt->execute([
                $data['name'],
                $data['unit_price'],
                $data['unit_of_issue'],
                $data['purchase_limit'],
                $data['active'] ?? true,
                $id
            ]);
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    public function delete($id) {
        try {
            // Soft delete by setting active = false
            $stmt = $this->db->prepare("UPDATE items SET active = false WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    public function validatePurchaseLimit($itemId, $quantity) {
        $item = $this->getById($itemId);
        if (!$item) {
            throw new Exception("Item not found");
        }
        
        if ($quantity > $item['purchase_limit']) {
            throw new Exception("Quantity exceeds purchase limit of {$item['purchase_limit']} {$item['unit_of_issue']}");
        }
        
        return true;
    }
}
