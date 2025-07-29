<?php
/**
 * MySQL Connection Diagnostic Tool
 * This file helps diagnose MySQL connection issues
 */

echo "<h2>MySQL Connection Diagnostic Tool</h2>";
echo "<hr>";

// Test 1: Check if MySQL extension is loaded
echo "<h3>1. Checking PHP MySQL Extensions</h3>";
if (extension_loaded('mysqli')) {
    echo "✅ MySQLi extension is loaded<br>";
} else {
    echo "❌ MySQLi extension is NOT loaded<br>";
}

if (extension_loaded('pdo_mysql')) {
    echo "✅ PDO MySQL extension is loaded<br>";
} else {
    echo "❌ PDO MySQL extension is NOT loaded<br>";
}

echo "<br>";

// Test 2: Check MySQL service status (Windows specific)
echo "<h3>2. Checking MySQL Service Status</h3>";
$output = shell_exec('sc query mysql 2>&1');
if ($output) {
    if (strpos($output, 'RUNNING') !== false) {
        echo "✅ MySQL service is running<br>";
    } else {
        echo "❌ MySQL service is not running<br>";
        echo "Service status: <pre>" . htmlspecialchars($output) . "</pre>";
    }
} else {
    echo "⚠️ Could not check MySQL service status<br>";
}

echo "<br>";

// Test 3: Test database connection
echo "<h3>3. Testing Database Connection</h3>";

// Database configuration
$dbServerName = "localhost";
$dbUserName = "root";
$dbPassword = "";
$dbName = "slpa_db";
$dbPort = 3306;

echo "Attempting to connect to:<br>";
echo "Server: $dbServerName<br>";
echo "Port: $dbPort<br>";
echo "Username: $dbUserName<br>";
echo "Database: $dbName<br><br>";

// Test connection without selecting database first
echo "<h4>3.1 Testing basic connection (without database)</h4>";
$testConnect = @mysqli_connect($dbServerName, $dbUserName, $dbPassword, null, $dbPort);

if ($testConnect) {
    echo "✅ Basic connection successful<br>";
    echo "MySQL Server Version: " . mysqli_get_server_info($testConnect) . "<br>";
    
    // List available databases
    echo "<h4>3.2 Available databases:</h4>";
    $databases = mysqli_query($testConnect, "SHOW DATABASES");
    if ($databases) {
        while ($db = mysqli_fetch_array($databases)) {
            echo "- " . $db['Database'] . "<br>";
        }
    }
    
    mysqli_close($testConnect);
    echo "<br>";
} else {
    echo "❌ Basic connection failed<br>";
    echo "Error: " . mysqli_connect_error() . "<br>";
    echo "Error Code: " . mysqli_connect_errno() . "<br><br>";
}

// Test connection with specific database
echo "<h4>3.3 Testing connection with specific database</h4>";
$connect = @mysqli_connect($dbServerName, $dbUserName, $dbPassword, $dbName, $dbPort);

if ($connect) {
    echo "✅ Database connection successful<br>";
    echo "Connected to database: $dbName<br>";
    
    // Test a simple query
    $result = mysqli_query($connect, "SELECT DATABASE() as current_db, NOW() as current_time");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "Current database: " . $row['current_db'] . "<br>";
        echo "Current time: " . $row['current_time'] . "<br>";
    }
    
    mysqli_close($connect);
} else {
    echo "❌ Database connection failed<br>";
    echo "Error: " . mysqli_connect_error() . "<br>";
    echo "Error Code: " . mysqli_connect_errno() . "<br>";
}

echo "<br>";

// Test 4: Check common MySQL ports
echo "<h3>4. Testing MySQL Port Connectivity</h3>";
$ports = [3306, 3307, 3308];
foreach ($ports as $port) {
    $connection = @fsockopen('localhost', $port, $errno, $errstr, 1);
    if ($connection) {
        echo "✅ Port $port is open and responding<br>";
        fclose($connection);
    } else {
        echo "❌ Port $port is not responding<br>";
    }
}

echo "<br>";

// Test 5: PHP Configuration
echo "<h3>5. PHP Configuration</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Operating System: " . PHP_OS . "<br>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";

echo "<br>";

// Test 6: MySQL Error Log Location Suggestions
echo "<h3>6. MySQL Error Log Locations (check these files)</h3>";
echo "Common MySQL error log locations on Windows:<br>";
echo "- C:\\xampp\\mysql\\data\\mysql_error.log<br>";
echo "- C:\\wamp\\bin\\mysql\\mysql[version]\\data\\[computer-name].err<br>";
echo "- C:\\Program Files\\MySQL\\MySQL Server [version]\\data\\[computer-name].err<br>";
echo "- Check XAMPP/WAMP control panel logs<br>";

echo "<br>";

// Solutions
echo "<h3>7. Common Solutions for MySQL Shutdown Issues</h3>";
echo "<div style='background-color: #f0f0f0; padding: 10px; border-radius: 5px;'>";
echo "<h4>Try these solutions in order:</h4>";
echo "<ol>";
echo "<li><strong>Port Conflict:</strong> Check if port 3306 is being used by another application</li>";
echo "<li><strong>Restart Services:</strong> Stop and restart MySQL service from XAMPP/WAMP control panel</li>";
echo "<li><strong>Run as Administrator:</strong> Run XAMPP/WAMP control panel as administrator</li>";
echo "<li><strong>Antivirus:</strong> Add XAMPP/WAMP folder to antivirus exclusions</li>";
echo "<li><strong>Windows Services:</strong> Check if 'MySQL' service is running in Windows Services</li>";
echo "<li><strong>Configuration Files:</strong> Check my.ini or my.cnf for syntax errors</li>";
echo "<li><strong>Disk Space:</strong> Ensure sufficient disk space is available</li>";
echo "<li><strong>Permissions:</strong> Check folder permissions for MySQL data directory</li>";
echo "</ol>";
echo "</div>";

echo "<br>";
echo "<p><strong>Generated on:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
