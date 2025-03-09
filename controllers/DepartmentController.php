<?php

require_once __DIR__ . '/../models/Department.php';

class DepartmentController {
    private $department;

    public function __construct($db) {
        $this->department = new Department($db);
    }

    public function index() {
        try {
            $departments = $this->department->getAll();
            return ['success' => true, 'data' => $departments];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to fetch departments: ' . $e->getMessage()];
        }
    }

    public function store($data) {
        try {
            // Validate required fields
            if (empty($data['name']) || empty($data['code'])) {
                return ['success' => false, 'error' => 'Name and code are required fields'];
            }

            // Validate code format (alphanumeric, max 10 chars)
            if (!preg_match('/^[A-Za-z0-9]{1,10}$/', $data['code'])) {
                return ['success' => false, 'error' => 'Code must be alphanumeric and maximum 10 characters'];
            }

            // Check if code is unique
            if (!$this->department->validateCode($data['code'])) {
                return ['success' => false, 'error' => 'Department code must be unique'];
            }

            if ($this->department->create($data)) {
                return ['success' => true, 'message' => 'Department created successfully'];
            }
            return ['success' => false, 'error' => 'Failed to create department'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to create department: ' . $e->getMessage()];
        }
    }

    public function update($id, $data) {
        try {
            // Validate required fields
            if (empty($data['name']) || empty($data['code'])) {
                return ['success' => false, 'error' => 'Name and code are required fields'];
            }

            // Validate code format
            if (!preg_match('/^[A-Za-z0-9]{1,10}$/', $data['code'])) {
                return ['success' => false, 'error' => 'Code must be alphanumeric and maximum 10 characters'];
            }

            // Check if code is unique (excluding current department)
            if (!$this->department->validateCode($data['code'], $id)) {
                return ['success' => false, 'error' => 'Department code must be unique'];
            }

            if ($this->department->update($id, $data)) {
                return ['success' => true, 'message' => 'Department updated successfully'];
            }
            return ['success' => false, 'error' => 'Failed to update department'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to update department: ' . $e->getMessage()];
        }
    }

    public function delete($id) {
        try {
            if ($this->department->delete($id)) {
                return ['success' => true, 'message' => 'Department deleted successfully'];
            }
            return ['success' => false, 'error' => 'Failed to delete department'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to delete department: ' . $e->getMessage()];
        }
    }

    public function getRequisitionOrders($id) {
        try {
            $orders = $this->department->getRequisitionOrders($id);
            return ['success' => true, 'data' => $orders];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to fetch requisition orders: ' . $e->getMessage()];
        }
    }

    public function search($term) {
        try {
            $departments = $this->department->search($term);
            return ['success' => true, 'data' => $departments];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to search departments: ' . $e->getMessage()];
        }
    }
}
