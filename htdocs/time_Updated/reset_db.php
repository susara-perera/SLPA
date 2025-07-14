<?php
// Database cleanup and reset script for SLPA
session_start();

echo "<h2>SLPA Database Cleanup & Reset</h2>";
echo "<hr>";

// Include database connection
include_once 'dbc.php';

if (!$connect) {
    echo "❌ <strong>Database Connection:</strong> FAILED<br>";
    echo "🚨 <strong>Error:</strong> " . mysqli_connect_error() . "<br><br>";
    exit();
}

echo "✅ <strong>Database Connection:</strong> SUCCESS<br><br>";

// Check if reset is requested
if (isset($_POST['reset_tables'])) {
    echo "<h3>🔄 Resetting Tables...</h3>";
    
    // Drop tables in correct order (child tables first due to foreign keys)
    $tables_to_drop = ['port_login_logs', 'port_users', 'ports'];
    
    foreach ($tables_to_drop as $table) {
        $drop_sql = "DROP TABLE IF EXISTS $table";
        if (mysqli_query($connect, $drop_sql)) {
            echo "✅ Dropped table: <strong>$table</strong><br>";
        } else {
            echo "❌ Failed to drop table: <strong>$table</strong> - " . mysqli_error($connect) . "<br>";
        }
    }
    
    echo "<br>🔄 Tables have been reset. <a href='test_ports_db.php'>Click here to recreate them</a><br>";
    echo "<hr>";
}

// Show current table status
echo "<h3>📊 Current Database Status:</h3>";

$tables = ['ports', 'port_users', 'port_login_logs'];
foreach ($tables as $table) {
    $check_sql = "SHOW TABLES LIKE '$table'";
    $result = mysqli_query($connect, $check_sql);
    
    if (mysqli_num_rows($result) > 0) {
        echo "✅ <strong>$table:</strong> EXISTS ";
        
        // Get record count
        $count_sql = "SELECT COUNT(*) as count FROM $table";
        $count_result = mysqli_query($connect, $count_sql);
        if ($count_result) {
            $count_row = mysqli_fetch_assoc($count_result);
            echo "(" . $count_row['count'] . " records)";
        }
        echo "<br>";
    } else {
        echo "❌ <strong>$table:</strong> DOES NOT EXIST<br>";
    }
}

echo "<br><hr>";
echo "<h3>🛠️ Available Actions:</h3>";

// Reset form
echo "<form method='POST' style='margin-bottom: 20px;'>";
echo "<button type='submit' name='reset_tables' onclick='return confirm(\"Are you sure you want to reset all tables? This will delete all data!\")' style='background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>";
echo "🗑️ Reset All Tables";
echo "</button>";
echo "</form>";

echo "<div style='background: #f8f9fa; padding: 15px; border-left: 4px solid #17a2b8; margin-bottom: 20px;'>";
echo "<strong>ℹ️ What this does:</strong><br>";
echo "• Drops the 'port_login_logs' table<br>";
echo "• Drops the 'port_users' table<br>";
echo "• Drops the 'ports' table<br>";
echo "• Allows you to recreate them fresh from scratch<br>";
echo "• Useful if there are foreign key constraint issues<br>";
echo "</div>";

echo "<h3>🔗 Quick Links:</h3>";
echo "<a href='test_ports_db.php' style='display: inline-block; background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; margin-right: 10px;'>🧪 Test Database</a>";
echo "<a href='all_ports.php' style='display: inline-block; background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; margin-right: 10px;'>🏠 All Ports</a>";
echo "<a href='port_login.php' style='display: inline-block; background: #6f42c1; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px;'>🔑 Port Login</a>";

mysqli_close($connect);
?>

<style>
body { 
    font-family: Arial, sans-serif; 
    margin: 20px; 
    background: #f8f9fa;
}
h2, h3 { 
    color: #333; 
}
a:hover { 
    opacity: 0.8; 
}
button:hover { 
    opacity: 0.9; 
}
</style>
