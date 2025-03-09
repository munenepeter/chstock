CREATE DATABASE IF NOT EXISTS chstock;

USE chstock;

-- Departments Table
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) NOT NULL UNIQUE,
    active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP
);

-- Items Table
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

-- Suppliers Table
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

-- Requisition Orders Table
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

-- Requisition Items Table
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

-- Purchase Orders Table
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

-- Purchase Order Items Table
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

-- Supplier Performance Table
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

-- Stock Items Table
CREATE TABLE stock_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    unit VARCHAR(50) NOT NULL,
    stock_level INT NOT NULL DEFAULT 0,
    reorder_level INT NOT NULL DEFAULT 5,
    expiry_date DATE,
    supplier_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers (id)
);

-- Stock Transactions Table
CREATE TABLE stock_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stock_id INT,
    transaction_type ENUM('received', 'issued'),
    quantity INT NOT NULL,
    transaction_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    issued_to VARCHAR(255),
    received_from VARCHAR(255),
    FOREIGN KEY (stock_id) REFERENCES stock_items (id)
);

-- Orders Table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    status ENUM('pending', 'delivered', 'cancelled'),
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    received_date DATETIME,
    FOREIGN KEY (supplier_id) REFERENCES suppliers (id)
);

-- Order Items Table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    stock_id INT,
    quantity INT NOT NULL,
    received_quantity INT DEFAULT 0,
    FOREIGN KEY (order_id) REFERENCES orders (id),
    FOREIGN KEY (stock_id) REFERENCES stock_items (id)
);

-- Alerts Table
CREATE TABLE alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stock_id INT,
    alert_type ENUM('low_stock', 'expired'),
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    resolved BOOLEAN DEFAULT 0,
    FOREIGN KEY (stock_id) REFERENCES stock_items (id)
);
-- Roles Table
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL
);

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles (id)
);


-- Permissions Table
CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL
);

-- Role-Permission Mapping
CREATE TABLE role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT,
    permission_id INT,
    FOREIGN KEY (role_id) REFERENCES roles (id),
    FOREIGN KEY (permission_id) REFERENCES permissions (id)
);

-- Activity Logs
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action TEXT NOT NULL,
    details TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id)
);

-- Indexes for performance
CREATE INDEX idx_users_email ON users (email);
CREATE INDEX idx_stock_name ON stock_items (name);
CREATE INDEX idx_transaction_date ON stock_transactions (transaction_date);
CREATE INDEX idx_order_status ON orders (status);
CREATE INDEX idx_alert_type ON alerts (alert_type);

-- Trigger for RO Number Generation
DELIMITER //

CREATE TRIGGER before_ro_insert
BEFORE INSERT ON requisition_orders
FOR EACH ROW
BEGIN
    DECLARE next_sequence INT;
    DECLARE current_fy VARCHAR(9);
    
    -- Calculate current financial year
    IF MONTH(CURDATE()) >= 7 THEN
        SET current_fy = CONCAT(YEAR(CURDATE()), '-', YEAR(CURDATE()) + 1);
    ELSE
        SET current_fy = CONCAT(YEAR(CURDATE()) - 1, '-', YEAR(CURDATE()));
    END IF;
    
    -- Get the last sequence number for the financial year
    SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(ro_number, '/', 1) AS UNSIGNED)), 0) + 1
    INTO next_sequence
    FROM requisition_orders
    WHERE financial_year = current_fy;
    
    -- Set the new RO number
    SET NEW.ro_number = CONCAT(
        LPAD(next_sequence, 3, '0'),
        '/',
        current_fy
    );
    
    -- Set the financial year
    SET NEW.financial_year = current_fy;
END //

DELIMITER ;

-- Trigger to Update RO Status when PO is Completed
DELIMITER //

CREATE TRIGGER after_po_update
AFTER UPDATE ON purchase_orders
FOR EACH ROW
BEGIN
    IF NEW.status = 'completed' THEN
        UPDATE requisition_orders
        SET status = 'completed'
        WHERE ro_number = NEW.ro_number;
    END IF;
END //

DELIMITER ;

-- Trigger to Check Purchase Limit Before RO Item Insert
DELIMITER //

CREATE TRIGGER before_ri_insert
BEFORE INSERT ON requisition_items
FOR EACH ROW
BEGIN
    DECLARE item_limit INT;
    DECLARE current_total INT;
    
    -- Get item's purchase limit
    SELECT purchase_limit INTO item_limit
    FROM items
    WHERE id = NEW.item_id;
    
    -- Get current total quantity for this item in this financial year
    SELECT COALESCE(SUM(ri.quantity), 0) INTO current_total
    FROM requisition_items ri
    JOIN requisition_orders ro ON ri.ro_number = ro.ro_number
    WHERE ri.item_id = NEW.item_id
    AND ro.financial_year = (
        SELECT financial_year
        FROM requisition_orders
        WHERE ro_number = NEW.ro_number
    );
    
    -- Check if new quantity would exceed limit
    IF (current_total + NEW.quantity) > item_limit THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Purchase limit exceeded for this item';
    END IF;
END //

DELIMITER ;

-- Trigger to Check PO Item Quantity Against RO
DELIMITER //

CREATE TRIGGER before_poi_insert
BEFORE INSERT ON purchase_order_items
FOR EACH ROW
BEGIN
    DECLARE ro_quantity INT;
    
    -- Get quantity from RO
    SELECT quantity INTO ro_quantity
    FROM requisition_items ri
    JOIN purchase_orders po ON ri.ro_number = po.ro_number
    WHERE po.lpo_number = NEW.lpo_number
    AND ri.item_id = NEW.item_id;
    
    -- Check if PO quantity exceeds RO quantity
    IF NEW.quantity > ro_quantity THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'PO quantity cannot exceed RO quantity';
    END IF;
END //

DELIMITER ;

-- Trigger to Update Supplier Rating
DELIMITER //

CREATE TRIGGER after_sp_insert
AFTER INSERT ON supplier_performance
FOR EACH ROW
BEGIN
    DECLARE avg_score DECIMAL(5,2);
    
    -- Calculate new average rating
    SELECT AVG(performance_score) INTO avg_score
    FROM supplier_performance
    WHERE supplier_id = NEW.supplier_id;
    
    -- Update supplier rating
    UPDATE suppliers
    SET rating = avg_score
    WHERE id = NEW.supplier_id;
END //

DELIMITER ;
