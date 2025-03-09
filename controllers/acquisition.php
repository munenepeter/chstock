<?php
require_once __DIR__ . '/../models/RequisitionOrder.php';
require_once __DIR__ . '/../models/PurchaseOrder.php';

class AcquisitionController {
    private $db;
    private $ro;
    private $po;

    public function __construct($db) {
        $this->db = $db;
        $this->ro = new RequisitionOrder($db);
        $this->po = new PurchaseOrder($db);
    }

    public function createRequisition($data) {
        try {
            // Validate input
            if (!isset($data['department_id']) || !isset($data['items']) || !is_array($data['items']) || empty($data['items'])) {
                throw new Exception("Some data is missing", 500);
            }

            // Ensure items are properly formatted
            $items = [];
            foreach ($data['items'] as $item) {
                if (!isset($item['item_id'], $item['quantity'], $item['unit_price'])) {
                    throw new Exception("Invalid item data", 500);
                }
                $items[] = [
                    'item_id' => (int) $item['item_id'],
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => (float) $item['unit_price']
                ];
            }

            // Create RO
            $roNumber = $this->ro->create($data['department_id'], $items);

            return [
                'success' => true,
                'message' => 'Requisition order created successfully',
                'data' => ['ro_number' => $roNumber]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }


    public function createPurchaseOrder($data) {
        try {
            // Validate input
            if (
                !isset($data['lpo_number']) || !isset($data['ro_number']) ||
                !isset($data['supplier_id']) || !isset($data['procurement_method']) ||
                !isset($data['date_of_commitment']) || !isset($data['items']) ||
                empty($data['items'])
            ) {
                throw new Exception("Invalid input data");
            }

            // Create PO
            $this->po->create(
                $data['lpo_number'],
                $data['ro_number'],
                $data['supplier_id'],
                $data['procurement_method'],
                $data['procurement_reference'] ?? null,
                $data['date_of_commitment'],
                $data['items']
            );

            return [
                'success' => true,
                'message' => 'Purchase order created successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getRequisition($roNumber) {
        try {
            $ro = $this->ro->get($roNumber);
            if (!$ro) {
                throw new Exception("Requisition order not found");
            }

            $items = $this->ro->getItems($roNumber);

            return [
                'success' => true,
                'data' => [
                    'requisition' => $ro,
                    'items' => $items
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getPurchaseOrder($lpoNumber) {
        try {
            $po = $this->po->get($lpoNumber);
            if (!$po) {
                throw new Exception("Purchase order not found");
            }

            $items = $this->po->getItems($lpoNumber);

            return [
                'success' => true,
                'data' => [
                    'purchase_order' => $po,
                    'items' => $items
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function listRequisitions($status = null, $departmentId = null) {
        try {
            $requisitions = $this->ro->list($status, $departmentId);

            return [
                'success' => true,
                'data' => $requisitions
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function listPurchaseOrders($status = null, $supplierId = null) {
        try {
            $orders = $this->po->list($status, $supplierId);

            return [
                'success' => true,
                'data' => $orders
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function extendPurchaseOrder($lpoNumber) {
        try {
            $this->po->extendExpiry($lpoNumber);

            return [
                'success' => true,
                'message' => 'Purchase order expiry extended successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function recordDelivery($lpoNumber, $deliveryDate) {
        try {
            $this->po->recordDelivery($lpoNumber, $deliveryDate);

            return [
                'success' => true,
                'message' => 'Delivery recorded successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
