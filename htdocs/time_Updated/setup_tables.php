<?php
// Create necessary tables for port dashboard functionality
include('./dbc.php');

echo "<h2>Setting up Port Dashboard Tables</h2>";

try {
    if (!$connect) {
        throw new Exception('Database connection failed: ' . mysqli_connect_error());
    }
    
    echo "<p>Connected to database successfully.</p>";
    
    // Create assign_role table (main table used by our PHP code)
    $sql_assign_role = "CREATE TABLE IF NOT EXISTS assign_role (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (mysqli_query($connect, $sql_assign_role)) {
        echo "<p style='color: green;'>✓ assign_role table created successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating assign_role table: " . mysqli_error($connect) . "</p>";
    }
    
    // Create port_assignments table
    $sql_port_assignments = "CREATE TABLE IF NOT EXISTS port_assignments (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (mysqli_query($connect, $sql_port_assignments)) {
        echo "<p style='color: green;'>✓ port_assignments table created successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating port_assignments table: " . mysqli_error($connect) . "</p>";
    }
    
    // Create transfer_requests table
    $sql_transfer_requests = "CREATE TABLE IF NOT EXISTS transfer_requests (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (mysqli_query($connect, $sql_transfer_requests)) {
        echo "<p style='color: green;'>✓ transfer_requests table created successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating transfer_requests table: " . mysqli_error($connect) . "</p>";
    }
    
    // Insert sample data
    echo "<h3>Inserting Sample Data</h3>";
    
    // Sample data for assign_role
    $sample_assign_role = "INSERT IGNORE INTO assign_role (employee_id, employee_name, division, section, current_role, assigned_role, port_name, assigned_by, status) VALUES
        ('EMP001', 'John Smith', 'ECT', 'Operations', 'Employee', 'Granty Crane Operator', 'Colombo', 'admin', 'Active'),
        ('EMP002', 'Sarah Johnson', 'JCT', 'Logistics', 'Employee', 'Transfer Crane Operator', 'Trincomalee', 'admin', 'Active')";
    
    if (mysqli_query($connect, $sample_assign_role)) {
        echo "<p style='color: green;'>✓ Sample data inserted into assign_role table</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Sample data for assign_role: " . mysqli_error($connect) . "</p>";
    }
    
    // Sample data for transfer_requests
    $sample_transfer = "INSERT IGNORE INTO transfer_requests (employee_id, employee_name, current_port, requested_port, current_role, status, requested_by) VALUES
        ('EMP001', 'John Smith', 'Colombo', 'Galle', 'Granty Crane Operator', 'Pending', 'EMP001'),
        ('EMP002', 'Sarah Johnson', 'Trincomalee', 'Hambantota', 'Transfer Crane Operator', 'Approved', 'EMP002')";
    
    if (mysqli_query($connect, $sample_transfer)) {
        echo "<p style='color: green;'>✓ Sample data inserted into transfer_requests table</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Sample data for transfer_requests: " . mysqli_error($connect) . "</p>";
    }
    
    // Check tables
    echo "<h3>Table Status</h3>";
    $tables = ['assign_role', 'port_assignments', 'transfer_requests'];
    
    foreach ($tables as $table) {
        $result = mysqli_query($connect, "SHOW TABLES LIKE '$table'");
        if (mysqli_num_rows($result) > 0) {
            $count_result = mysqli_query($connect, "SELECT COUNT(*) as count FROM $table");
            $count = mysqli_fetch_assoc($count_result)['count'];
            echo "<p style='color: green;'>✓ Table '$table' exists with $count records</p>";
        } else {
            echo "<p style='color: red;'>✗ Table '$table' does not exist</p>";
        }
    }
    
    echo "<h3>Success!</h3>";
    echo "<p>All tables have been created. You can now:</p>";
    echo "<ul>";
    echo "<li><a href='test_autoload.php'>Test the auto-loading functionality</a></li>";
    echo "<li><a href='test_dashboard.php'>Try the test dashboard</a></li>";
    echo "<li><a href='create_test_session.php'>Create session and use real dashboard</a></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

if ($connect) {
    mysqli_close($connect);
}
?>
