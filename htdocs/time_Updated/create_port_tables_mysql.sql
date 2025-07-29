-- SQL script to create necessary tables for port dashboard functionality (MySQL compatible)

-- Create assign_role table (using the naming from our PHP code)
CREATE TABLE IF NOT EXISTS assign_role (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50) NOT NULL,
    employee_name VARCHAR(255) NOT NULL,
    division VARCHAR(100),
    section VARCHAR(100),
    current_role VARCHAR(100) DEFAULT 'Employee',
    assigned_role VARCHAR(100),
    port_name VARCHAR(100),
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    assigned_by VARCHAR(100),
    assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_employee_id (employee_id),
    INDEX idx_port_name (port_name),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create port_assignments table
CREATE TABLE IF NOT EXISTS port_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50) NOT NULL,
    employee_name VARCHAR(255) NOT NULL,
    division VARCHAR(100),
    role VARCHAR(100) NOT NULL,
    port_name VARCHAR(100) NOT NULL,
    assigned_by VARCHAR(100) NOT NULL,
    assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_employee_id (employee_id),
    INDEX idx_port_name (port_name),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create port_roles table for role tracking
CREATE TABLE IF NOT EXISTS port_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50) NOT NULL,
    employee_name VARCHAR(255) NOT NULL,
    role_name VARCHAR(100) NOT NULL,
    port_name VARCHAR(100) NOT NULL,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    assigned_by VARCHAR(100) NOT NULL,
    assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_by VARCHAR(100),
    updated_date TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_employee_id (employee_id),
    INDEX idx_role_name (role_name),
    INDEX idx_port_name (port_name),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create transfer_requests table for handling transfer requests
CREATE TABLE IF NOT EXISTS transfer_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50) NOT NULL,
    employee_name VARCHAR(255) NOT NULL,
    current_port VARCHAR(100) NOT NULL,
    requested_port VARCHAR(100) NOT NULL,
    current_role VARCHAR(100),
    reason TEXT,
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    requested_by VARCHAR(100) NOT NULL,
    requested_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_by VARCHAR(100),
    reviewed_date TIMESTAMP NULL DEFAULT NULL,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_employee_id (employee_id),
    INDEX idx_status (status),
    INDEX idx_current_port (current_port),
    INDEX idx_requested_port (requested_port)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data for testing
INSERT IGNORE INTO assign_role (employee_id, employee_name, division, section, current_role, assigned_role, port_name, assigned_by, status) VALUES
('EMP001', 'John Smith', 'ECT', 'Operations', 'Employee', 'Granty Crane Operator', 'Colombo', 'admin', 'Active'),
('EMP002', 'Sarah Johnson', 'JCT', 'Logistics', 'Employee', 'Transfer Crane Operator', 'Trincomalee', 'admin', 'Active');

INSERT IGNORE INTO port_assignments (employee_id, employee_name, division, role, port_name, assigned_by, status) VALUES
('EMP001', 'John Smith', 'ECT', 'Granty Crane Operator', 'Colombo', 'admin', 'Active'),
('EMP002', 'Sarah Johnson', 'JCT', 'Transfer Crane Operator', 'Trincomalee', 'admin', 'Active');

INSERT IGNORE INTO transfer_requests (employee_id, employee_name, current_port, requested_port, current_role, status, requested_by) VALUES
('EMP001', 'John Smith', 'Colombo', 'Galle', 'Granty Crane Operator', 'Pending', 'EMP001'),
('EMP002', 'Sarah Johnson', 'Trincomalee', 'Hambantota', 'Transfer Crane Operator', 'Approved', 'EMP002');

SELECT 'Port dashboard tables created successfully!' AS Status;
