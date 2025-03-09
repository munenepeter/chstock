<?php

class Department {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll($activeOnly = true) {
        $sql = "SELECT * FROM departments";
        if ($activeOnly) {
            $sql .= " WHERE active = TRUE";
        }
        $sql .= " ORDER BY name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $sql = "SELECT * FROM departments WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByCode($code) {
        $sql = "SELECT * FROM departments WHERE code = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$code]);
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO departments (name, code, active) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['code'],
            isset($data['active']) ? $data['active'] : true
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE departments 
                SET name = ?, code = ?, active = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['code'],
            isset($data['active']) ? $data['active'] : true,
            $id
        ]);
    }

    public function delete($id) {
        // Check if department has any requisition orders
        $sql = "SELECT COUNT(*) as count FROM requisition_orders WHERE department_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            // If department has requisition orders, just set it as inactive
            $sql = "UPDATE departments SET active = FALSE, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } else {
            // If no requisition orders, we can safely delete
            $sql = "DELETE FROM departments WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        }
    }

    public function search($term) {
        $term = "%$term%";
        $sql = "SELECT * FROM departments 
                WHERE (name LIKE ? OR code LIKE ?) 
                AND active = TRUE 
                ORDER BY name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$term, $term]);
        return $stmt->fetchAll();
    }

    public function getRequisitionOrders($departmentId) {
        $sql = "SELECT ro.*, 
                       COUNT(ri.id) as items_count,
                       SUM(ri.quantity) as total_items
                FROM requisition_orders ro
                LEFT JOIN requisition_items ri ON ro.ro_number = ri.ro_number
                WHERE ro.department_id = ?
                GROUP BY ro.ro_number
                ORDER BY ro.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$departmentId]);
        return $stmt->fetchAll();
    }

    public function validateCode($code, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM departments WHERE code = ?";
        $params = [$code];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'] === 0;
    }
}
