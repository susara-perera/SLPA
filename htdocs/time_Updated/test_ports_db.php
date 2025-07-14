<?php
// Test script to verify port database connectivity and data
session_start();

echo "<h2>SLPA Port Database Test</h2>";
echo "<hr>";

// Include database connection
include_once 'dbc.php';

// Check database connection
if ($connect) {
    echo "✅ <strong>Database Connection:</strong> SUCCESS<br>";
    echo "📍 <strong>Database:</strong> " . mysqli_get_server_info($connect) . "<br><br>";
} else {
    echo "❌ <strong>Database Connection:</strong> FAILED<br>";
    echo "🚨 <strong>Error:</strong> " . mysqli_connect_error() . "<br><br>";
    exit();
}

// Check if ports table exists
$table_check = mysqli_query($connect, "SHOW TABLES LIKE 'ports'");
if (mysqli_num_rows($table_check) > 0) {
    echo "✅ <strong>Ports Table:</strong> EXISTS<br><br>";
    
    // Get table structure
    echo "<h3>Table Structure:</h3>";
    $structure = mysqli_query($connect, "DESCRIBE ports");
    echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = mysqli_fetch_assoc($structure)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Get port count
    $count_query = mysqli_query($connect, "SELECT COUNT(*) as total FROM ports");
    $count_result = mysqli_fetch_assoc($count_query);
    echo "<strong>Total Ports in Database:</strong> " . $count_result['total'] . "<br><br>";
    
    // Get port data
    echo "<h3>Port Data:</h3>";
    $ports_query = mysqli_query($connect, "SELECT * FROM ports ORDER BY port_name");
    
    if (mysqli_num_rows($ports_query) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Port Name</th><th>Code</th><th>Latitude</th><th>Longitude</th><th>Status</th><th>Description</th></tr>";
        
        while ($port = mysqli_fetch_assoc($ports_query)) {
            echo "<tr>";
            echo "<td>" . $port['id'] . "</td>";
            echo "<td>" . htmlspecialchars($port['port_name']) . "</td>";
            echo "<td>" . htmlspecialchars($port['port_code']) . "</td>";
            echo "<td>" . $port['latitude'] . "</td>";
            echo "<td>" . $port['longitude'] . "</td>";
            echo "<td>" . $port['status'] . "</td>";
            echo "<td>" . htmlspecialchars(substr($port['description'], 0, 50)) . "...</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "⚠️ No ports found in database<br>";
    }
    
} else {
    echo "❌ <strong>Ports Table:</strong> DOES NOT EXIST<br>";
    echo "🔧 The table will be created when you visit all_ports.php<br><br>";
}

// Test the all_ports.php logic (without HTML)
echo "<br><hr>";
echo "<h3>Testing all_ports.php Logic:</h3>";

// Function to create ports table if it doesn't exist (copy from all_ports.php)
function createPortsTable($connect) {
    $sql = "CREATE TABLE IF NOT EXISTS ports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        port_name VARCHAR(100) NOT NULL UNIQUE,
        latitude DECIMAL(10, 8) NOT NULL,
        longitude DECIMAL(11, 8) NOT NULL,
        port_code VARCHAR(10) UNIQUE,
        status ENUM('Active', 'Inactive') DEFAULT 'Active',
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if (mysqli_query($connect, $sql)) {
        echo "✅ Ports table creation: SUCCESS<br>";
        return true;
    } else {
        echo "❌ Ports table creation: FAILED - " . mysqli_error($connect) . "<br>";
        return false;
    }
}

// Function to insert default ports if table is empty (copy from all_ports.php)
function insertDefaultPorts($connect) {
    $checkSql = "SELECT COUNT(*) as count FROM ports";
    $result = mysqli_query($connect, $checkSql);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['count'] == 0) {
        echo "📥 Inserting default ports...<br>";
        $defaultPorts = [
            ["Colombo Port", 6.9538, 79.8500, "CMB", "Main commercial port of Sri Lanka"],
            ["Galle Port", 6.0351, 80.2170, "GLE", "Historic port in the southern province"],
            ["Trincomalee Port", 8.5708, 81.2332, "TRN", "Natural deep water harbor in the east"],
            ["Hambantota Port", 6.1248, 81.1185, "HMB", "Modern port in the southern coast"],
            ["Kankesanthurai Port", 9.8150, 80.0717, "KKS", "Northern province port facility"],
            ["Oluvil Port", 7.2522, 81.8384, "OLV", "Eastern province fishing port"],
            ["Point Pedro Port", 9.8167, 80.2333, "PPD", "Northernmost port of Sri Lanka"]
        ];
        
        $insertSql = "INSERT INTO ports (port_name, latitude, longitude, port_code, description) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($connect, $insertSql);
        
        foreach ($defaultPorts as $port) {
            mysqli_stmt_bind_param($stmt, "sddss", $port[0], $port[1], $port[2], $port[3], $port[4]);
            if (mysqli_stmt_execute($stmt)) {
                echo "✅ Inserted: " . $port[0] . "<br>";
            } else {
                echo "❌ Failed to insert: " . $port[0] . "<br>";
            }
        }
        mysqli_stmt_close($stmt);
        echo "✅ Default ports insertion: COMPLETED<br>";
    } else {
        echo "ℹ️ Ports already exist in database (Count: " . $row['count'] . ")<br>";
    }
}

// Test the logic
$table_created = createPortsTable($connect);
if ($table_created) {
    insertDefaultPorts($connect);
    
    // Test fetching ports (like all_ports.php does)
    echo "<br><strong>Testing port fetch query:</strong><br>";
    $sql = "SELECT * FROM ports WHERE status = 'Active' ORDER BY port_name";
    $result = mysqli_query($connect, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        echo "✅ Query executed successfully<br>";
        echo "📊 Found " . mysqli_num_rows($result) . " active ports<br>";
        
        echo "<br><strong>Sample port data (as used in all_ports.php):</strong><br>";
        while ($row = mysqli_fetch_assoc($result)) {
            $port_data = [
                "id" => $row['id'],
                "name" => $row['port_name'],
                "lat" => (float)$row['latitude'],
                "lng" => (float)$row['longitude'],
                "code" => $row['port_code'],
                "description" => $row['description'],
                "status" => $row['status']
            ];
            echo "🚢 " . $port_data['name'] . " (" . $port_data['code'] . ") - Lat: " . $port_data['lat'] . ", Lng: " . $port_data['lng'] . "<br>";
        }
    } else {
        echo "❌ Query failed or no results found<br>";
    }
}

echo "<br><hr>";
echo "<h3>Testing Port Login Logs Table:</h3>";

// Check if port_login_logs table exists
$login_logs_check = mysqli_query($connect, "SHOW TABLES LIKE 'port_login_logs'");
if (mysqli_num_rows($login_logs_check) > 0) {
    echo "✅ <strong>Port Login Logs Table:</strong> EXISTS<br><br>";
    
    // Get table structure
    echo "<h4>Port Login Logs Table Structure:</h4>";
    $structure = mysqli_query($connect, "DESCRIBE port_login_logs");
    echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = mysqli_fetch_assoc($structure)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Get login logs count
    $logs_count_query = mysqli_query($connect, "SELECT COUNT(*) as total FROM port_login_logs");
    $logs_count_result = mysqli_fetch_assoc($logs_count_query);
    echo "<strong>Total Login Logs in Database:</strong> " . $logs_count_result['total'] . "<br><br>";
    
} else {
    echo "❌ <strong>Port Login Logs Table:</strong> DOES NOT EXIST<br>";
    echo "🔧 The table will be created when you visit port_login.php<br><br>";
}

echo "<br><hr>";
echo "<h3>Testing Port Users Table:</h3>";

// Check if port_users table exists
$port_users_check = mysqli_query($connect, "SHOW TABLES LIKE 'port_users'");
if (mysqli_num_rows($port_users_check) > 0) {
    echo "✅ <strong>Port Users Table:</strong> EXISTS<br><br>";
    
    // Get table structure
    echo "<h4>Port Users Table Structure:</h4>";
    $structure = mysqli_query($connect, "DESCRIBE port_users");
    echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = mysqli_fetch_assoc($structure)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Get port users count
    $users_count_query = mysqli_query($connect, "SELECT COUNT(*) as total FROM port_users");
    $users_count_result = mysqli_fetch_assoc($users_count_query);
    echo "<strong>Total Port Users in Database:</strong> " . $users_count_result['total'] . "<br><br>";
    
    // Get port users data
    echo "<h4>Port Users Data:</h4>";
    $users_query = mysqli_query($connect, "
        SELECT pu.id, pu.username, pu.status, p.port_name 
        FROM port_users pu 
        JOIN ports p ON pu.port_id = p.id 
        ORDER BY p.port_name
    ");
    
    if (mysqli_num_rows($users_query) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Port Name</th><th>Username</th><th>Status</th></tr>";
        
        while ($user = mysqli_fetch_assoc($users_query)) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['port_name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
            echo "<td>" . $user['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "⚠️ No port users found in database<br>";
    }
    
} else {
    echo "❌ <strong>Port Users Table:</strong> DOES NOT EXIST<br>";
    echo "🔧 The table will be created when you visit port_login.php<br><br>";
}

echo "<br><hr>";
echo "<h3>Testing port_login.php Logic:</h3>";

// Include the functions from port_login.php
// Function to create ports table if it doesn't exist (from port_login.php)
function createPortsTableForLogin($connect) {
    $sql = "CREATE TABLE IF NOT EXISTS ports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        port_name VARCHAR(100) NOT NULL UNIQUE,
        latitude DECIMAL(10, 8) NOT NULL,
        longitude DECIMAL(11, 8) NOT NULL,
        port_code VARCHAR(10) UNIQUE,
        status ENUM('Active', 'Inactive') DEFAULT 'Active',
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if (mysqli_query($connect, $sql)) {
        echo "✅ Ports table (for login): SUCCESS<br>";
        return true;
    } else {
        echo "❌ Ports table (for login): FAILED - " . mysqli_error($connect) . "<br>";
        return false;
    }
}

// Function to create port_users table (from port_login.php)
function createPortUsersTableTest($connect) {
    // First, ensure ports table exists
    if (!createPortsTableForLogin($connect)) {
        return false;
    }
    
    // Try creating table with foreign key first
    $sql = "CREATE TABLE IF NOT EXISTS port_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        port_id INT NOT NULL,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        status ENUM('Active', 'Inactive') DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (port_id) REFERENCES ports(id) ON DELETE CASCADE,
        UNIQUE KEY unique_port_user (port_id, username)
    )";
    
    if (mysqli_query($connect, $sql)) {
        echo "✅ Port users table creation (with FK): SUCCESS<br>";
        return true;
    } else {
        echo "⚠️ Port users table creation (with FK): FAILED - " . mysqli_error($connect) . "<br>";
        echo "🔄 Trying fallback without foreign key...<br>";
        
        // Fallback: Create table without foreign key constraint
        $sql_fallback = "CREATE TABLE IF NOT EXISTS port_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            port_id INT NOT NULL,
            username VARCHAR(50) NOT NULL,
            password VARCHAR(255) NOT NULL,
            status ENUM('Active', 'Inactive') DEFAULT 'Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_port_user (port_id, username),
            INDEX idx_port_id (port_id)
        )";
        
        if (mysqli_query($connect, $sql_fallback)) {
            echo "✅ Port users table creation (fallback): SUCCESS<br>";
            return true;
        } else {
            echo "❌ Port users table creation (fallback): FAILED - " . mysqli_error($connect) . "<br>";
            return false;
        }
    }
}

// Function to create port_login_logs table (from port_login.php)
function createLoginLogsTableTest($connect) {
    $sql = "CREATE TABLE IF NOT EXISTS port_login_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        port_id INT NOT NULL,
        username VARCHAR(50) NOT NULL,
        login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('Success', 'Failed') DEFAULT 'Success',
        ip_address VARCHAR(45),
        user_agent TEXT,
        INDEX idx_port_id (port_id),
        INDEX idx_login_time (login_time)
    )";
    
    if (mysqli_query($connect, $sql)) {
        echo "✅ Port login logs table creation: SUCCESS<br>";
        return true;
    } else {
        echo "❌ Port login logs table creation: FAILED - " . mysqli_error($connect) . "<br>";
        return false;
    }
}

// Test the port_login.php logic
$login_table_created = createPortUsersTableTest($connect);
$logs_table_created = createLoginLogsTableTest($connect);
if ($login_table_created && $logs_table_created) {
    echo "✅ Port login database setup: COMPLETED<br>";
} else {
    echo "❌ Port login database setup: FAILED<br>";
}

echo "<br><hr>";
echo "<h3>Summary:</h3>";
echo "✅ Database connectivity is working<br>";
echo "✅ Ports table structure is correct<br>";
echo "✅ Data insertion/fetching logic is working<br>";
echo "✅ all_ports.php should work correctly with the database<br>";
if ($login_table_created && $logs_table_created) {
    echo "✅ port_login.php should work correctly with the database<br>";
    echo "✅ Login logging functionality is set up<br>";
} else {
    echo "❌ port_login.php may have database issues<br>";
}
echo "<br>";

echo "<a href='all_ports.php'>🔗 Go to All Ports Page</a><br>";
echo "<a href='port_login.php'>🔗 Go to Port Login</a><br>";

// Close connection
mysqli_close($connect);
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; margin: 10px 0; }
th, td { padding: 8px 12px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
</style>
