-- SQL script to create necessary tables for port dashboard functionality (MSSQL compatible)

-- Create port_assignments table
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='port_assignments' AND xtype='U')
BEGIN
    CREATE TABLE port_assignments (
        id INT IDENTITY(1,1) PRIMARY KEY,
        employee_id NVARCHAR(50) NOT NULL,
        employee_name NVARCHAR(255) NOT NULL,
        division NVARCHAR(100),
        role NVARCHAR(100) NOT NULL,
        port_name NVARCHAR(100) NOT NULL,
        assigned_by NVARCHAR(100) NOT NULL,
        assigned_date DATETIME DEFAULT GETDATE(),
        status NVARCHAR(20) DEFAULT 'Active' CHECK (status IN ('Active', 'Inactive')),
        created_at DATETIME DEFAULT GETDATE(),
        updated_at DATETIME DEFAULT GETDATE()
    );
    
    CREATE INDEX idx_employee_id ON port_assignments (employee_id);
    CREATE INDEX idx_port_name ON port_assignments (port_name);
    CREATE INDEX idx_status ON port_assignments (status);
END

-- Create port_roles table for role tracking
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='port_roles' AND xtype='U')
BEGIN
    CREATE TABLE port_roles (
        id INT IDENTITY(1,1) PRIMARY KEY,
        employee_id NVARCHAR(50) NOT NULL,
        employee_name NVARCHAR(255) NOT NULL,
        role_name NVARCHAR(100) NOT NULL,
        port_name NVARCHAR(100) NOT NULL,
        status NVARCHAR(20) DEFAULT 'Active' CHECK (status IN ('Active', 'Inactive')),
        assigned_by NVARCHAR(100) NOT NULL,
        assigned_date DATETIME DEFAULT GETDATE(),
        updated_by NVARCHAR(100),
        updated_date DATETIME,
        created_at DATETIME DEFAULT GETDATE(),
        updated_at DATETIME DEFAULT GETDATE()
    );
    
    CREATE INDEX idx_employee_id_roles ON port_roles (employee_id);
    CREATE INDEX idx_role_name ON port_roles (role_name);
    CREATE INDEX idx_port_name_roles ON port_roles (port_name);
    CREATE INDEX idx_status_roles ON port_roles (status);
END

-- Create transfer_requests table for handling transfer requests
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='transfer_requests' AND xtype='U')
BEGIN
    CREATE TABLE transfer_requests (
        id INT IDENTITY(1,1) PRIMARY KEY,
        employee_id NVARCHAR(50) NOT NULL,
        employee_name NVARCHAR(255) NOT NULL,
        current_port NVARCHAR(100) NOT NULL,
        requested_port NVARCHAR(100) NOT NULL,
        current_role NVARCHAR(100),
        reason NTEXT,
        status NVARCHAR(20) DEFAULT 'Pending' CHECK (status IN ('Pending', 'Approved', 'Rejected')),
        requested_by NVARCHAR(100) NOT NULL,
        requested_date DATETIME DEFAULT GETDATE(),
        reviewed_by NVARCHAR(100),
        reviewed_date DATETIME,
        comments NTEXT,
        created_at DATETIME DEFAULT GETDATE(),
        updated_at DATETIME DEFAULT GETDATE()
    );
    
    CREATE INDEX idx_employee_id_transfer ON transfer_requests (employee_id);
    CREATE INDEX idx_status_transfer ON transfer_requests (status);
    CREATE INDEX idx_current_port ON transfer_requests (current_port);
    CREATE INDEX idx_requested_port ON transfer_requests (requested_port);
END

-- Insert sample data for testing
IF NOT EXISTS (SELECT * FROM port_assignments WHERE employee_id = 'EMP001')
BEGIN
    INSERT INTO port_assignments (employee_id, employee_name, division, role, port_name, assigned_by, status) VALUES
    ('EMP001', 'John Smith', 'ECT', 'Granty Crane Operator', 'Colombo', 'admin', 'Active'),
    ('EMP002', 'Sarah Johnson', 'JCT', 'Transfer Crane Operator', 'Trincomalee', 'admin', 'Active');
END

IF NOT EXISTS (SELECT * FROM transfer_requests WHERE employee_id = 'EMP001')
BEGIN
    INSERT INTO transfer_requests (employee_id, employee_name, current_port, requested_port, current_role, status, requested_by) VALUES
    ('EMP001', 'John Smith', 'Colombo', 'Galle', 'Granty Crane Operator', 'Pending', 'EMP001'),
    ('EMP002', 'Sarah Johnson', 'Trincomalee', 'Hambantota', 'Transfer Crane Operator', 'Approved', 'EMP002');
END

PRINT 'Port dashboard tables created successfully!';
