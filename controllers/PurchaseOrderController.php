<?php

require_once __DIR__ . '/../models/PurchaseOrder.php';

class PurchaseOrderController {
    private $purchaseOrder;

    public function __construct($db) {
        $this->purchaseOrder = new PurchaseOrder($db);
    }

    public function create($data) {
        try {
            // Validate required fields
            if (empty($data['lpo_number']) || empty($data['ro_number']) || empty($data['supplier_id'])) {
                return ['success' => false, 'error' => 'LPO number, RO number, and Supplier ID are required fields'];
            }

            // Validate procurement method
            if (!in_array($data['procurement_method'], ['tender', 'quotation', 'direct'])) {
                return ['success' => false, 'error' => 'Invalid procurement method'];
            }

            // Create purchase order
            $items = $data['items'] ?? [];
            if ($this->purchaseOrder->create($data['lpo_number'], $data['ro_number'], $data['supplier_id'], $data['procurement_method'], $data['procurement_reference'], $data['date_of_commitment'], $items)) {
                return ['success' => true, 'message' => 'Purchase order created successfully'];
            }
            return ['success' => false, 'error' => 'Failed to create purchase order'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to create purchase order: ' . $e->getMessage()];
        }
    }

    public function get($lpoNumber) {
        try {
            $purchaseOrder = $this->purchaseOrder->get($lpoNumber);
            return ['success' => true, 'data' => $purchaseOrder];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to fetch purchase order: ' . $e->getMessage()];
        }
    }

    public function list($status = null, $supplierId = null) {
        try {
            $purchaseOrders = $this->purchaseOrder->list($status, $supplierId);
            return ['success' => true, 'data' => $purchaseOrders];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to fetch purchase orders: ' . $e->getMessage()];
        }
    }

    public function updateStatus($lpoNumber, $status) {
        try {
            if ($this->purchaseOrder->updateStatus($lpoNumber, $status)) {
                return ['success' => true, 'message' => 'Purchase order status updated successfully'];
            }
            return ['success' => false, 'error' => 'Failed to update purchase order status'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to update purchase order status: ' . $e->getMessage()];
        }
    }

    public function extendExpiry($lpoNumber) {
        try {
            if ($this->purchaseOrder->extendExpiry($lpoNumber)) {
                return ['success' => true, 'message' => 'Purchase order expiry extended successfully'];
            }
            return ['success' => false, 'error' => 'Failed to extend purchase order expiry'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to extend purchase order expiry: ' . $e->getMessage()];
        }
    }

    public function recordDelivery($lpoNumber, $deliveryDate) {
        try {
            if ($this->purchaseOrder->recordDelivery($lpoNumber, $deliveryDate)) {
                return ['success' => true, 'message' => 'Delivery recorded successfully'];
            }
            return ['success' => false, 'error' => 'Failed to record delivery'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to record delivery: ' . $e->getMessage()];
        }
    }
}
