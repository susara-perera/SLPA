<?php
// Database Connection Test and Data Retrieval for SLPA_DB
include_once 'dbc.php';

echo "<h1>🔌 SLPA Database Connection & Data Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .card { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .success { color: #27ae60; }
    .error { color: #e74c3c; }
    .info { color: #3498db; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .status-ok { background-color: #d4edda; color: #155724; }
    .status-error { background-color: #f8d7da; color: #721c24; }
</style>";

// Test database connection
echo "<div class='card'>";
echo "<h2>📊 Database Connection Status</h2>";
if ($connect) {
    echo "<p class='success'>✅ Successfully connected to database: <strong>$dbName</strong></p>";
    echo "<p class='info'>📍 Server: $dbServerName | User: $dbUserName</p>";
} else {
    echo "<p class='error'>❌ Connection failed: " . mysqli_connect_error() . "</p>";
    exit();
}
echo "</div>";

// Function to display table data
function displayTableData($connect, $tableName, $limit = 10) {
    echo "<div class='card'>";
    echo "<h3>📋 Table: $tableName</h3>";
    
    // Check if table exists
    $checkTable = "SHOW TABLES LIKE '$tableName'";
    $tableExists = mysqli_query($connect, $checkTable);
    
    if (mysqli_num_rows($tableExists) == 0) {
        echo "<p class='error'>❌ Table '$tableName' does not exist</p>";
        echo "</div>";
        return;
    }
    
    // Get table structure
    $structure = mysqli_query($connect, "DESCRIBE $tableName");
    echo "<h4>🏗️ Table Structure:</h4>";
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = mysqli_fetch_assoc($structure)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Get record count
    $countQuery = "SELECT COUNT(*) as total FROM $tableName";
    $countResult = mysqli_query($connect, $countQuery);
    $count = mysqli_fetch_assoc($countResult)['total'];
    
    echo "<p class='info'>📊 Total Records: <strong>$count</strong></p>";
    
    if ($count > 0) {
        // Display data
        $dataQuery = "SELECT * FROM $tableName LIMIT $limit";
        $dataResult = mysqli_query($connect, $dataQuery);
        
        echo "<h4>📄 Sample Data (First $limit records):</h4>";
        echo "<table>";
        
        // Get column names
        $fields = mysqli_fetch_fields($dataResult);
        echo "<tr>";
        foreach ($fields as $field) {
            echo "<th>" . $field->name . "</th>";
        }
        echo "</tr>";
        
        // Display data rows
        while ($row = mysqli_fetch_assoc($dataResult)) {
            echo "<tr>";
            foreach ($row as $value) {
                // Truncate long values
                $displayValue = strlen($value) > 50 ? substr($value, 0, 50) . "..." : $value;
                echo "<td>" . htmlspecialchars($displayValue) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>⚠️ No data found in table</p>";
    }
    
    echo "</div>";
}

// Test all your tables
$tables = [
    'users',
    'login', 
    'ports',
    'port_users',
    'port_login_logs',
    'employees',
    'divisions',
    'sections',
    'attendance',
    'fingerprints',
    'role_access'
];

echo "<div class='card'>";
echo "<h2>🗂️ All Tables Overview</h2>";
$showTables = mysqli_query($connect, "SHOW TABLES");
echo "<table>";
echo "<tr><th>Table Name</th><th>Status</th><th>Records</th></tr>";
while ($table = mysqli_fetch_array($showTables)) {
    $tableName = $table[0];
    $countQuery = "SELECT COUNT(*) as total FROM `$tableName`";
    $countResult = mysqli_query($connect, $countQuery);
    $count = $countResult ? mysqli_fetch_assoc($countResult)['total'] : 0;
    $status = $count > 0 ? "status-ok" : "status-error";
    echo "<tr class='$status'>";
    echo "<td>$tableName</td>";
    echo "<td>" . ($count > 0 ? "✅ Has Data" : "⚠️ Empty") . "</td>";
    echo "<td>$count</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

// Display detailed data for key tables
foreach ($tables as $table) {
    displayTableData($connect, $table, 5);
}

// Test user authentication query
echo "<div class='card'>";
echo "<h2>🔐 User Authentication Test</h2>";
echo "<p>Testing the login query structure...</p>";

$testQuery = "SELECT id, role, employee_ID, pwd FROM users WHERE role IN ('Admin', 'Super Admin', 'Employee') LIMIT 5";
$testResult = mysqli_query($connect, $testQuery);

if ($testResult && mysqli_num_rows($testResult) > 0) {
    echo "<p class='success'>✅ User authentication query structure is working</p>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Employee ID</th><th>Role</th><th>Password (Hashed)</th></tr>";
    while ($user = mysqli_fetch_assoc($testResult)) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . $user['employee_ID'] . "</td>";
        echo "<td>" . $user['role'] . "</td>";
        echo "<td>" . (strlen($user['pwd']) > 20 ? "✅ Hashed" : "⚠️ " . htmlspecialchars($user['pwd'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>❌ No users found or query failed</p>";
}
echo "</div>";

// Connection info
echo "<div class='card'>";
echo "<h2>ℹ️ Connection Information</h2>";
echo "<p><strong>Database:</strong> " . mysqli_get_server_info($connect) . "</p>";
echo "<p><strong>Character Set:</strong> " . mysqli_character_set_name($connect) . "</p>";
echo "<p><strong>Connection ID:</strong> " . mysqli_thread_id($connect) . "</p>";
echo "</div>";

mysqli_close($connect);
?>
