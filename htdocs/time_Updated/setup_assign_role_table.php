<?php
// Check and create assign_role table if needed
include('./dbc.php');

echo "<h2>Database Table Check - assign_role</h2>";

// Check if assign_role table exists
$check_table = "SHOW TABLES LIKE 'assign_role'";
$result = mysqli_query($connect, $check_table);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<p style='color: green;'>✓ Table 'assign_role' already exists</p>";
    
    // Show table structure
    $describe = "DESCRIBE assign_role";
    $desc_result = mysqli_query($connect, $describe);
    if ($desc_result) {
        echo "<h3>Current table structure:</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = mysqli_fetch_assoc($desc_result)) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p style='color: orange;'>⚠ Table 'assign_role' does not exist. Creating now...</p>";
    
    // Create assign_role table
    $create_sql = "CREATE TABLE assign_role (
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
    
    if (mysqli_query($connect, $create_sql)) {
        echo "<p style='color: green;'>✓ Table 'assign_role' created successfully!</p>";
        
        // Show the new table structure
        $describe = "DESCRIBE assign_role";
        $desc_result = mysqli_query($connect, $describe);
        if ($desc_result) {
            echo "<h3>New table structure:</h3>";
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            while ($row = mysqli_fetch_assoc($desc_result)) {
                echo "<tr>";
                echo "<td>" . $row['Field'] . "</td>";
                echo "<td>" . $row['Type'] . "</td>";
                echo "<td>" . $row['Null'] . "</td>";
                echo "<td>" . $row['Key'] . "</td>";
                echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p style='color: red;'>✗ Error creating table: " . mysqli_error($connect) . "</p>";
    }
}

// Test sample data
echo "<h3>Sample Data Check</h3>";
$sample_sql = "SELECT COUNT(*) as count FROM assign_role";
$sample_result = mysqli_query($connect, $sample_sql);
if ($sample_result) {
    $row = mysqli_fetch_assoc($sample_result);
    echo "<p>Current records in assign_role table: " . $row['count'] . "</p>";
} else {
    echo "<p style='color: red;'>✗ Error checking data: " . mysqli_error($connect) . "</p>";
}

mysqli_close($connect);
?>
