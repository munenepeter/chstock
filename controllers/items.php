<?php

require_once __DIR__ . '/../models/Item.php';

class ItemController {
    private $db;
    private $item;
    
    public function __construct($db) {
        $this->db = $db;
        $this->item = new Item($db);
    }
    
    public function getAll($activeOnly = true) {
        try {
            $items = $this->item->getAll($activeOnly);
            return [
                'success' => true,
                'data' => $items
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function getById($id) {
        try {
            $item = $this->item->getById($id);
            if (!$item) {
                throw new Exception("Item not found");
            }
            
            return [
                'success' => true,
                'data' => $item
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function create($data) {
        try {
            // Validate required fields
            if (!isset($data['name']) || !isset($data['unit_price']) || 
                !isset($data['unit_of_issue']) || !isset($data['purchase_limit'])) {
                throw new Exception("Missing required fields");
            }
            
            // Validate numeric fields
            if (!is_numeric($data['unit_price']) || $data['unit_price'] <= 0) {
                throw new Exception("Invalid unit price");
            }
            
            if (!is_numeric($data['purchase_limit']) || $data['purchase_limit'] <= 0) {
                throw new Exception("Invalid purchase limit");
            }
            
            $itemId = $this->item->create($data);
            
            return [
                'success' => true,
                'message' => 'Item created successfully',
                'data' => ['id' => $itemId]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function update($id, $data) {
        try {
            // Check if item exists
            $existingItem = $this->item->getById($id);
            if (!$existingItem) {
                throw new Exception("Item not found");
            }
            
            // Validate numeric fields if provided
            if (isset($data['unit_price']) && (!is_numeric($data['unit_price']) || $data['unit_price'] <= 0)) {
                throw new Exception("Invalid unit price");
            }
            
            if (isset($data['purchase_limit']) && (!is_numeric($data['purchase_limit']) || $data['purchase_limit'] <= 0)) {
                throw new Exception("Invalid purchase limit");
            }
            
            $result = $this->item->update($id, $data);
            
            return [
                'success' => true,
                'message' => 'Item updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function delete($id) {
        try {
            // Check if item exists
            $existingItem = $this->item->getById($id);
            if (!$existingItem) {
                throw new Exception("Item not found");
            }
            
            $result = $this->item->delete($id);
            
            return [
                'success' => true,
                'message' => 'Item deleted successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
