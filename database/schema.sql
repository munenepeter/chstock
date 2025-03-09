-- Database: LabStockDB
-- Suppliers Table
CREATE TABLE
    suppliers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        contact TEXT,
        email TEXT UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

-- Stock Items Table
CREATE TABLE
    stock_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        category TEXT,
        unit TEXT NOT NULL, -- e.g., "Bottles", "Boxes"
        stock_level INTEGER NOT NULL DEFAULT 0,
        reorder_level INTEGER NOT NULL DEFAULT 5, -- Alert if stock goes below this
        expiry_date DATE,
        supplier_id INTEGER,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (supplier_id) REFERENCES suppliers (id)
    );

-- Stock Transactions Table (Tracks stock movements)
CREATE TABLE
    stock_transactions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        stock_id INTEGER,
        transaction_type TEXT CHECK (transaction_type IN ('received', 'issued')),
        quantity INTEGER NOT NULL,
        transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        issued_to TEXT, -- Only applicable if type is 'issued'
        received_from TEXT, -- Only applicable if type is 'received'
        FOREIGN KEY (stock_id) REFERENCES stock_items (id)
    );

-- Orders Table (For tracking supplier orders)
CREATE TABLE
    orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        supplier_id INTEGER,
        order_number TEXT UNIQUE NOT NULL,
        status TEXT CHECK (status IN ('pending', 'delivered', 'cancelled')),
        order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        received_date TIMESTAMP,
        FOREIGN KEY (supplier_id) REFERENCES suppliers (id)
    );

-- Order Items Table (Items within an order)
CREATE TABLE
    order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER,
        stock_id INTEGER,
        quantity INTEGER NOT NULL,
        received_quantity INTEGER DEFAULT 0, -- Updates once delivered
        FOREIGN KEY (order_id) REFERENCES orders (id),
        FOREIGN KEY (stock_id) REFERENCES stock_items (id)
    );

-- Alerts Table (System generated alerts for low stock & expired items)
CREATE TABLE
    alerts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        stock_id INTEGER,
        alert_type TEXT CHECK (alert_type IN ('low_stock', 'expired')),
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        resolved INTEGER DEFAULT 0, -- 0 = Unresolved, 1 = Resolved
        FOREIGN KEY (stock_id) REFERENCES stock_items (id)
    );

-- Users Table (Authentication)
CREATE TABLE
    users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL, -- Hashed password
        role_id INTEGER NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (role_id) REFERENCES roles (id)
    );

-- Roles Table (RBAC)
CREATE TABLE
    roles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT UNIQUE NOT NULL -- (e.g., 'admin', 'store_manager', 'viewer')
    );

-- Permissions Table (Optional, for fine-grained access control in future)
CREATE TABLE
    permissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT UNIQUE NOT NULL -- (e.g., 'manage_stock', 'view_reports', 'approve_orders')
    );

-- Role-Permission Mapping (Optional, if implementing fine-grained permissions)
CREATE TABLE
    role_permissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        role_id INTEGER,
        permission_id INTEGER,
        FOREIGN KEY (role_id) REFERENCES roles (id),
        FOREIGN KEY (permission_id) REFERENCES permissions (id)
    );

-- Activity Logs (Audit Trail)
CREATE TABLE
    activity_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        action TEXT NOT NULL, -- (e.g., 'Added new stock', 'Updated order status')
        details TEXT,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (id)
    );

-- Indexes for performance
CREATE INDEX idx_users_email ON users (email);

CREATE INDEX idx_logs_timestamp ON activity_logs (timestamp);

CREATE INDEX idx_roles_name ON roles (name);

CREATE INDEX idx_stock_name ON stock_items (name);

CREATE INDEX idx_transaction_date ON stock_transactions (transaction_date);

CREATE INDEX idx_order_status ON orders (status);

CREATE INDEX idx_alert_type ON alerts (alert_type);