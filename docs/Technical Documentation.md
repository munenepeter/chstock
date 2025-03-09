# Procurement System Technical Documentation

## 1. System Overview
The procurement system is designed to manage the complete lifecycle of procurement processes, from requisition to purchase orders, with integrated stock management and supplier rating features. The system is built using PHP and MySQL, emphasizing minimal third-party dependencies for maximum control and maintainability.

## 2. Architecture Components

### 2.1 Core Modules

#### 2.1.1 Requisition Order Module
- **Purpose**: Manages creation and tracking of requisition orders
- **Key Features**:
  - Auto-generated RO numbers with financial year format
  - Multi-item support with quantity tracking
  - Automatic total calculations
  - Status management (pending/completed)

#### 2.1.2 Purchase Order Module
- **Purpose**: Handles purchase order creation and management
- **Key Features**:
  - Manual LPO number entry
  - Links to requisition orders
  - Expiry tracking with 30-day policy
  - Multiple procurement methods (tender/quotation/direct)
  - Quantity validation against RO

#### 2.1.3 Stock Management Module
- **Purpose**: Tracks items and their purchase limits
- **Key Features**:
  - Purchase limit enforcement
  - Unit of issue management
  - Active/inactive item tracking

#### 2.1.4 Supplier Management Module
- **Purpose**: Manages supplier information and performance
- **Key Features**:
  - Supplier profile management
  - Automatic performance rating calculation
  - Delivery timeline tracking

### 2.2 Database Structure

#### 2.2.1 Core Tables
```sql

-- Create Departments table
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) NOT NULL UNIQUE,
    active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP
);

-- Create Items table
CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    unit_of_issue VARCHAR(50) NOT NULL,
    purchase_limit INT NOT NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_item_active (active),
    CONSTRAINT chk_unit_price CHECK (unit_price > 0),
    CONSTRAINT chk_purchase_limit CHECK (purchase_limit > 0)
);

-- Create Suppliers table
CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    active BOOLEAN DEFAULT TRUE,
    rating DECIMAL(5,2) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_supplier_active (active),
    INDEX idx_supplier_rating (rating),
    CONSTRAINT chk_rating CHECK (rating >= 0 AND rating <= 100)
);

-- Create Requisition Orders table
CREATE TABLE requisition_orders (
    ro_number VARCHAR(20) PRIMARY KEY,
    department_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    status ENUM('pending', 'completed') DEFAULT 'pending',
    financial_year VARCHAR(9) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    INDEX idx_ro_status (status),
    INDEX idx_ro_financial_year (financial_year),
    CONSTRAINT chk_total_amount CHECK (total_amount >= 0)
);

-- Create Requisition Items table
CREATE TABLE requisition_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ro_number VARCHAR(20) NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ro_number) REFERENCES requisition_orders(ro_number),
    FOREIGN KEY (item_id) REFERENCES items(id),
    INDEX idx_ri_ro_number (ro_number),
    CONSTRAINT chk_ri_quantity CHECK (quantity > 0),
    CONSTRAINT chk_ri_unit_price CHECK (unit_price > 0),
    CONSTRAINT chk_ri_total_price CHECK (total_price > 0)
);

-- Create Purchase Orders table
CREATE TABLE purchase_orders (
    lpo_number VARCHAR(20) PRIMARY KEY,
    ro_number VARCHAR(20) NOT NULL,
    supplier_id INT NOT NULL,
    status ENUM('pending', 'completed', 'expired', 'extended') DEFAULT 'pending',
    procurement_method ENUM('tender', 'quotation', 'direct') NOT NULL,
    procurement_reference VARCHAR(50),
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    date_of_commitment DATE NOT NULL,
    expiry_date DATE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ro_number) REFERENCES requisition_orders(ro_number),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    INDEX idx_po_status (status),
    INDEX idx_po_supplier (supplier_id),
    INDEX idx_po_expiry (expiry_date),
    CONSTRAINT chk_po_total_amount CHECK (total_amount >= 0),
    CONSTRAINT chk_po_dates CHECK (expiry_date >= date_of_commitment)
);

-- Create Purchase Order Items table
CREATE TABLE purchase_order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lpo_number VARCHAR(20) NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lpo_number) REFERENCES purchase_orders(lpo_number),
    FOREIGN KEY (item_id) REFERENCES items(id),
    INDEX idx_poi_lpo_number (lpo_number),
    CONSTRAINT chk_poi_quantity CHECK (quantity > 0),
    CONSTRAINT chk_poi_unit_price CHECK (unit_price > 0),
    CONSTRAINT chk_poi_total_price CHECK (total_price > 0)
);

-- Create Supplier Performance table
CREATE TABLE supplier_performance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    lpo_number VARCHAR(20) NOT NULL,
    delivery_date DATE,
    days_taken INT,
    performance_score DECIMAL(5,2),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    FOREIGN KEY (lpo_number) REFERENCES purchase_orders(lpo_number),
    INDEX idx_sp_supplier (supplier_id),
    CONSTRAINT chk_performance_score CHECK (performance_score >= 0 AND performance_score <= 100)
);

-- Create Audit Log table
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(50) NOT NULL,
    record_id VARCHAR(50) NOT NULL,
    action ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    old_values JSON,
    new_values JSON,
    user_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_table (table_name),
    INDEX idx_audit_record (record_id),
    INDEX idx_audit_action (action)
);

-- Trigger for RO Number Generation
DELIMITER //
CREATE TRIGGER before_ro_insert
BEFORE INSERT ON requisition_orders
FOR EACH ROW
BEGIN
    DECLARE next_sequence INT;
    
    -- Get the last sequence number for the financial year
    SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(ro_number, '/', 1) AS UNSIGNED)), 0) + 1
    INTO next_sequence
    FROM requisition_orders
    WHERE financial_year = NEW.financial_year;
    
    -- Format: 001/2024-2025
    SET NEW.ro_number = CONCAT(
        LPAD(next_sequence, 3, '0'),
        '/',
        NEW.financial_year
    );
END;
//
DELIMITER ;

-- Trigger for Updating RO Total Amount
DELIMITER //
CREATE TRIGGER after_ri_insert_update
AFTER INSERT ON requisition_items
FOR EACH ROW
BEGIN
    UPDATE requisition_orders
    SET total_amount = (
        SELECT SUM(total_price)
        FROM requisition_items
        WHERE ro_number = NEW.ro_number
    )
    WHERE ro_number = NEW.ro_number;
END;
//
DELIMITER ;

-- Trigger for Updating PO Total Amount
DELIMITER //
CREATE TRIGGER after_poi_insert_update
AFTER INSERT ON purchase_order_items
FOR EACH ROW
BEGIN
    UPDATE purchase_orders
    SET total_amount = (
        SELECT SUM(total_price)
        FROM purchase_order_items
        WHERE lpo_number = NEW.lpo_number
    )
    WHERE lpo_number = NEW.lpo_number;
END;
//
DELIMITER ;

-- Trigger for Updating Supplier Rating
DELIMITER //
CREATE TRIGGER after_performance_update
AFTER INSERT ON supplier_performance
FOR EACH ROW
BEGIN
    UPDATE suppliers s
    SET rating = (
        SELECT AVG(performance_score)
        FROM supplier_performance
        WHERE supplier_id = NEW.supplier_id
    )
    WHERE id = NEW.supplier_id;
END;
//
DELIMITER ;

-- Views for Reports
CREATE VIEW v_requisition_summary AS
SELECT 
    ro.ro_number,
    d.name as department,
    ro.total_amount,
    ro.status,
    ro.financial_year,
    COUNT(ri.id) as total_items,
    ro.created_at
FROM requisition_orders ro
JOIN departments d ON ro.department_id = d.id
LEFT JOIN requisition_items ri ON ro.ro_number = ri.ro_number
GROUP BY ro.ro_number;

CREATE VIEW v_purchase_order_summary AS
SELECT 
    po.lpo_number,
    po.ro_number,
    s.name as supplier,
    po.procurement_method,
    po.total_amount,
    po.status,
    po.date_of_commitment,
    po.expiry_date,
    COUNT(poi.id) as total_items
FROM purchase_orders po
JOIN suppliers s ON po.supplier_id = s.id
LEFT JOIN purchase_order_items poi ON po.lpo_number = poi.lpo_number
GROUP BY po.lpo_number;

CREATE VIEW v_supplier_performance_summary AS
SELECT 
    s.id as supplier_id,
    s.name as supplier_name,
    s.rating as current_rating,
    COUNT(DISTINCT po.lpo_number) as total_orders,
    AVG(sp.days_taken) as avg_delivery_days,
    MIN(sp.performance_score) as min_performance,
    MAX(sp.performance_score) as max_performance
FROM suppliers s
LEFT JOIN purchase_orders po ON s.id = po.supplier_id
LEFT JOIN supplier_performance sp ON po.lpo_number = sp.lpo_number
GROUP BY s.id;

```

## 3. Business Logic Implementation

### 3.1 RO Number Generation
```php
class RequisitionOrderService {
    private function generateRONumber(): string {
        // Financial year determination
        $currentMonth = (int)date('m');
        $currentYear = (int)date('Y');
        $financialYear = $currentMonth >= 7 
            ? "$currentYear-" . ($currentYear + 1)
            : ($currentYear - 1) . "-$currentYear";
        
        // Get last sequence number
        $lastRO = $this->getLastRONumberForFinancialYear($financialYear);
        $sequence = str_pad(($lastRO + 1), 3, '0', STR_PAD_LEFT);
        
        return "{$sequence}/{$financialYear}";
    }
}
```

### 3.2 Purchase Order Management
```php
class PurchaseOrderService {
    public function createPO(array $data): array {
        // Validate RO exists and is pending
        $this->validateRequisitionOrder($data['ro_number']);
        
        // Validate quantities against RO
        $this->validateQuantities($data['items'], $data['ro_number']);
        
        // Calculate expiry date
        $expiryDate = date('Y-m-d', strtotime($data['date_of_commitment'] . ' + 30 days'));
        
        // Create PO record
        $poData = [
            'lpo_number' => $data['lpo_number'],
            'ro_number' => $data['ro_number'],
            'supplier_id' => $data['supplier_id'],
            'status' => 'pending',
            'expiry_date' => $expiryDate,
            // ... additional fields
        ];
        
        return $this->purchaseOrderRepository->create($poData);
    }
}
```

### 3.3 Supplier Rating Calculation
```php
class SupplierService {
    public function calculateRating(int $supplierId): float {
        $completedPOs = $this->getPOsForSupplier($supplierId);
        $totalScore = 0;
        $count = 0;
        
        foreach ($completedPOs as $po) {
            $deliveryDays = $this->calculateDeliveryDays($po);
            $score = $this->calculateDeliveryScore($deliveryDays);
            $totalScore += $score;
            $count++;
        }
        
        return $count > 0 ? ($totalScore / $count) : 0;
    }
    
    private function calculateDeliveryScore(int $days): float {
        if ($days <= 30) return 100;
        if ($days <= 45) return 60;
        return 40;
    }
}
```

## 4. Key Processes

### 4.1 Requisition Order Creation
1. Generate unique RO number
2. Validate department exists
3. For each item:
   - Check purchase limit
   - Calculate item total
   - Validate stock availability
4. Calculate RO total
5. Create RO record
6. Create requisition items records

### 4.2 Purchase Order Creation
1. Validate LPO number format
2. Link to existing RO
3. Validate supplier
4. For each item:
   - Validate quantity against RO
   - Calculate new totals
5. Set commitment date and calculate expiry
6. Create PO record
7. Create purchase order items records

### 4.3 Status Management
```php
class StatusManager {
    const STATUS_TRANSITIONS = [
        'purchase_order' => [
            'pending' => ['completed', 'expired'],
            'expired' => ['extended'],
            'extended' => ['completed'],
            'completed' => []
        ]
    ];
    
    public function canTransition(string $entity, string $currentStatus, string $newStatus): bool {
        return in_array($newStatus, self::STATUS_TRANSITIONS[$entity][$currentStatus] ?? []);
    }
}
```

## 5. Security Considerations

### 5.1 Input Validation
- All user inputs must be validated and sanitized
- Use prepared statements for database queries
- Implement CSRF protection
- Validate file uploads if implemented

### 5.2 Access Control
```php
class ProcurementAuthorization {
    private $permissions = [
        'create_ro' => ['procurement_officer', 'admin'],
        'approve_ro' => ['department_head', 'admin'],
        'create_po' => ['procurement_officer', 'admin'],
        'manage_suppliers' => ['procurement_officer', 'admin']
    ];
    
    public function canPerformAction(string $action, array $userRoles): bool {
        return !empty(array_intersect($this->permissions[$action] ?? [], $userRoles));
    }
}
```

## 6. Error Handling
```php
class ProcurementException extends Exception {
    const INVALID_QUANTITY = 1001;
    const EXPIRED_PO = 1002;
    const INVALID_STATUS_TRANSITION = 1003;
    
    public static function quantityExceedsLimit(int $limit): self {
        return new self("Quantity exceeds purchase limit of {$limit}", self::INVALID_QUANTITY);
    }
}
```

## 7. Deployment Considerations

### 7.1 Database Indexes
```sql
-- Example indexes for performance
CREATE INDEX idx_ro_status ON requisition_orders(status);
CREATE INDEX idx_po_supplier ON purchase_orders(supplier_id);
CREATE INDEX idx_item_active ON items(active);
```

### 7.2 Configuration
```php
return [
    'financial_year' => [
        'start_month' => 7,
        'start_day' => 1
    ],
    'po_expiry_days' => 30,
    'supplier_rating' => [
        'excellent' => 90,
        'good' => 70,
        'fair' => 50,
        'poor' => 30
    ]
];
```

## 8. Testing Strategy

### 8.1 Unit Tests
```php
class RequisitionOrderTest extends TestCase {
    public function testRONumberGeneration() {
        $service = new RequisitionOrderService();
        $roNumber = $service->generateRONumber();
        
        $this->assertMatchesRegularExpression(
            '/^\d{3}\/\d{4}-\d{4}$/',
            $roNumber
        );
    }
}
```

### 8.2 Integration Tests
Focus on testing:
- Complete procurement workflow
- Status transitions
- Calculations accuracy
- Database constraints
- Business rule enforcement

## 9. Monitoring and Maintenance

### 9.1 Key Metrics
- RO to PO conversion time
- Supplier delivery performance
- System response times
- Error rates
- Purchase limit violations

### 9.2 Logging
```php
class ProcurementLogger {
    public function logTransaction(string $type, array $data): void {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $type,
            'user_id' => $_SESSION['user_id'] ?? null,
            'data' => json_encode($data)
        ];
        
        // Log to file/database
    }
}
```