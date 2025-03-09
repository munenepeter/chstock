<?php

require_once __DIR__ . '/../models/Supplier.php';

class SupplierController {
    private $supplier;

    public function __construct($db) {
        $this->supplier = new Supplier($db);
    }

    public function index() {
        try {
            return [
                'success' => true,
                'data' => $this->supplier->getAll()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function show($id) {
        try {
            $supplier = $this->supplier->getById($id);
            if (!$supplier) {
                return [
                    'success' => false,
                    'error' => 'Supplier not found'
                ];
            }
            return [
                'success' => true,
                'data' => $supplier
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function store($data) {
        try {
            $this->validateData($data);
            $this->supplier->create($data);
            return [
                'success' => true,
                'message' => 'Supplier created successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function update($id, $data) {
        try {
            $this->validateData($data);
            if (!$this->supplier->getById($id)) {
                return [
                    'success' => false,
                    'error' => 'Supplier not found'
                ];
            }
            $this->supplier->update($id, $data);
            return [
                'success' => true,
                'message' => 'Supplier updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function delete($id) {
        try {
            if (!$this->supplier->getById($id)) {
                return [
                    'success' => false,
                    'error' => 'Supplier not found'
                ];
            }
            $this->supplier->delete($id);
            return [
                'success' => true,
                'message' => 'Supplier deleted successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function updateRating($id, $rating) {
        try {
            if (!$this->supplier->getById($id)) {
                return [
                    'success' => false,
                    'error' => 'Supplier not found'
                ];
            }
            $this->supplier->updateRating($id, $rating);
            return [
                'success' => true,
                'message' => 'Supplier rating updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function search($term) {
        try {
            return [
                'success' => true,
                'data' => $this->supplier->search($term)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function validateData($data) {
        $required = ['name', 'email', 'phone', 'address'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                throw new Exception("$field is required");
            }
        }

        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        if (isset($data['rating'])) {
            $rating = filter_var($data['rating'], FILTER_VALIDATE_FLOAT);
            if ($rating === false || $rating < 0 || $rating > 100) {
                throw new Exception("Rating must be a number between 0 and 100");
            }
        }
    }
}
