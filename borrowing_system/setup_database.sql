-- Borrowing and Inventory System - Setup Database
-- Run this file to set up the database with correct admin password

-- Create database
CREATE DATABASE IF NOT EXISTS borrowing_system;
USE borrowing_system;

-- =============================================
-- 1NF, 2NF, 3NF: Basic Tables
-- =============================================

-- Admins table (1NF: atomic values, 2NF: no partial dependencies, 3NF: no transitive dependencies)
CREATE TABLE admins (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Borrowers table (1NF, 2NF, 3NF)
CREATE TABLE borrowers (
    borrower_id INT PRIMARY KEY AUTO_INCREMENT,
    id_number VARCHAR(50) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    department_course_office VARCHAR(100) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    email_address VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Items table (1NF, 2NF, 3NF)
CREATE TABLE items (
    item_id INT PRIMARY KEY AUTO_INCREMENT,
    item_code VARCHAR(50) NOT NULL UNIQUE,
    item_name VARCHAR(100) NOT NULL,
    item_description TEXT,
    category VARCHAR(50) NOT NULL,
    total_quantity INT NOT NULL DEFAULT 0,
    available_quantity INT NOT NULL DEFAULT 0,
    unit VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =============================================
-- 4NF: Separate Multi-valued Dependencies
-- =============================================

-- Borrowing transactions table (4NF: separate transaction details)
CREATE TABLE borrowing_transactions (
    transaction_id INT PRIMARY KEY AUTO_INCREMENT,
    borrower_id INT NOT NULL,
    activity_purpose VARCHAR(255) NOT NULL,
    place_of_activity VARCHAR(100) NOT NULL,
    date_requested DATE NOT NULL,
    date_needed DATE NOT NULL,
    date_of_return DATE NOT NULL,
    status ENUM('pending', 'approved', 'issued', 'returned', 'overdue', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (borrower_id) REFERENCES borrowers(borrower_id) ON DELETE CASCADE
);

-- Borrowed items junction table (4NF: many-to-many relationship)
CREATE TABLE borrowed_items (
    borrowed_item_id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity_required INT NOT NULL,
    quantity_issued INT NOT NULL DEFAULT 0,
    quantity_returned INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES borrowing_transactions(transaction_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(item_id) ON DELETE CASCADE,
    UNIQUE KEY unique_transaction_item (transaction_id, item_id)
);

-- =============================================
-- 5NF: Eliminate Join Dependencies
-- =============================================

-- Approvals table (5NF: separate approval entities)
CREATE TABLE approvals (
    approval_id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_id INT NOT NULL,
    approval_type ENUM('issued_by', 'assessed_received_by', 'noted_by') NOT NULL,
    admin_id INT NOT NULL,
    approval_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES borrowing_transactions(transaction_id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admins(admin_id) ON DELETE CASCADE,
    UNIQUE KEY unique_transaction_approval_type (transaction_id, approval_type)
);

-- Activity logs table (5NF: separate logging entities)
CREATE TABLE activity_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT NOT NULL,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(admin_id) ON DELETE CASCADE
);

-- System settings table (5NF: separate configuration entities)
CREATE TABLE system_settings (
    setting_id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    setting_description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =============================================
-- Indexes for Performance
-- =============================================

-- Indexes for borrowers table
CREATE INDEX idx_borrowers_id_number ON borrowers(id_number);
CREATE INDEX idx_borrowers_email ON borrowers(email_address);
CREATE INDEX idx_borrowers_department ON borrowers(department_course_office);

-- Indexes for items table
CREATE INDEX idx_items_category ON items(category);
CREATE INDEX idx_items_available ON items(available_quantity);

-- Indexes for borrowing_transactions table
CREATE INDEX idx_transactions_borrower ON borrowing_transactions(borrower_id);
CREATE INDEX idx_transactions_dates ON borrowing_transactions(date_requested, date_needed, date_of_return);
CREATE INDEX idx_transactions_status ON borrowing_transactions(status);

-- Indexes for borrowed_items table
CREATE INDEX idx_borrowed_items_transaction ON borrowed_items(transaction_id);
CREATE INDEX idx_borrowed_items_item ON borrowed_items(item_id);

-- Indexes for approvals table
CREATE INDEX idx_approvals_transaction ON approvals(transaction_id);
CREATE INDEX idx_approvals_admin ON approvals(admin_id);

-- Indexes for activity_logs table
CREATE INDEX idx_logs_admin ON activity_logs(admin_id);
CREATE INDEX idx_logs_created ON activity_logs(created_at);

-- =============================================
-- Triggers for Data Integrity
-- =============================================

-- Trigger to update item available quantity when borrowed
DELIMITER $$
CREATE TRIGGER update_item_quantity_on_borrow
    AFTER INSERT ON borrowed_items
    FOR EACH ROW
BEGIN
    UPDATE items
    SET available_quantity = available_quantity - NEW.quantity_issued
    WHERE item_id = NEW.item_id;
END$$
DELIMITER ;

-- Trigger to update item available quantity when returned
DELIMITER $$
CREATE TRIGGER update_item_quantity_on_return
    AFTER UPDATE ON borrowed_items
    FOR EACH ROW
BEGIN
    IF NEW.quantity_returned > OLD.quantity_returned THEN
        UPDATE items
        SET available_quantity = available_quantity + (NEW.quantity_returned - OLD.quantity_returned)
        WHERE item_id = NEW.item_id;
    END IF;
END$$
DELIMITER ;

-- Trigger to log admin activities
DELIMITER $$
CREATE TRIGGER log_admin_activity
    AFTER INSERT ON borrowing_transactions
    FOR EACH ROW
BEGIN
    INSERT INTO activity_logs (admin_id, action, table_name, record_id, new_values)
    VALUES (1, 'CREATE', 'borrowing_transactions', NEW.transaction_id,
            JSON_OBJECT('borrower_id', NEW.borrower_id, 'activity_purpose', NEW.activity_purpose));
END$$
DELIMITER ;

-- =============================================
-- Initial Data
-- =============================================

-- Insert default admin (username: admin, password: password)
INSERT INTO admins (username, password_hash, email, full_name) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@system.com', 'System Administrator');

-- Insert system settings
INSERT INTO system_settings (setting_key, setting_value, setting_description) VALUES
('max_items_per_transaction', '5', 'Maximum number of items a borrower can request per transaction'),
('max_borrowing_days', '7', 'Maximum number of days items can be borrowed'),
('overdue_notice_days', '3', 'Number of days before due date to send notification');

-- Insert sample categories
INSERT INTO items (item_code, item_name, item_description, category, total_quantity, available_quantity, unit) VALUES
('LAPTOP001', 'Dell Laptop', 'Dell Inspiron 15 3000 Series', 'Electronics', 10, 10, 'pieces'),
('PROJECTOR001', 'Epson Projector', 'Epson EB-S41 SVGA Projector', 'Electronics', 5, 5, 'pieces'),
('WHITEBOARD001', 'Whiteboard', 'Standard Whiteboard 4x6 feet', 'Furniture', 8, 8, 'pieces'),
('MARKER001', 'Whiteboard Markers', 'Set of colored whiteboard markers', 'Supplies', 50, 50, 'sets');
