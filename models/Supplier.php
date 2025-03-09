<?php

class Supplier {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll($activeOnly = true) {
        $sql = "SELECT * FROM suppliers";
        if ($activeOnly) {
            $sql .= " WHERE active = TRUE";
        }
        $sql .= " ORDER BY name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $sql = "SELECT * FROM suppliers WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO suppliers (name, email, phone, address, active, rating) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['address'],
            isset($data['active']) ? $data['active'] : true,
            isset($data['rating']) ? $data['rating'] : 0
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE suppliers 
                SET name = ?, email = ?, phone = ?, address = ?, 
                    active = ?, rating = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['address'],
            isset($data['active']) ? $data['active'] : true,
            isset($data['rating']) ? $data['rating'] : 0,
            $id
        ]);
    }

    public function delete($id) {
        // Soft delete by setting active to false
        $sql = "UPDATE suppliers SET active = FALSE, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function updateRating($id, $rating) {
        if ($rating < 0 || $rating > 100) {
            throw new Exception("Rating must be between 0 and 100");
        }
        
        $sql = "UPDATE suppliers SET rating = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$rating, $id]);
    }

    public function search($term) {
        $term = "%$term%";
        $sql = "SELECT * FROM suppliers 
                WHERE (name LIKE ? OR email LIKE ? OR phone LIKE ? OR address LIKE ?) 
                AND active = TRUE 
                ORDER BY name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$term, $term, $term, $term]);
        return $stmt->fetchAll();
    }
}
