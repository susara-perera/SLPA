<?php
session_start();
include('./dbc.php');

echo "<h2>🔐 Login Bypass for Testing</h2>";

// Check if user wants to create a test session
if (isset($_GET['action']) && $_GET['action'] === 'create_session') {
    $_SESSION['user_id'] = 999;
    $_SESSION['username'] = 'Test Admin';
    $_SESSION['role'] = 'Admin';
    $_SESSION['employee_ID'] = 'TEST001';
    $_SESSION['user_type'] = 'Admin';
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ <strong>Test session created successfully!</strong><br>";
    echo "You can now access the attendance report.<br>";
    echo "<a href='generate_report.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 10px;'>Go to Attendance Report</a>";
    echo "</div>";
} else {
    echo "<p>This will create a temporary test session to bypass login authentication.</p>";
    echo "<a href='?action=create_session' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Create Test Session</a>";
}

echo "<hr>";
echo "<h3>📊 Current Session Status</h3>";
echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
echo "<tr><th>Session Variable</th><th>Value</th></tr>";

$session_vars = ['user_id', 'username', 'role', 'employee_ID', 'user_type'];
foreach ($session_vars as $var) {
    $value = isset($_SESSION[$var]) ? $_SESSION[$var] : '<em>Not set</em>';
    echo "<tr><td>$var</td><td>$value</td></tr>";
}
echo "</table>";

if (isset($_SESSION['user_id'])) {
    echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "ℹ️ <strong>Session Active:</strong> You should be able to access the attendance report now.";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "❌ <strong>No Session:</strong> You need to create a test session or login properly.";
    echo "</div>";
}

echo "<hr>";
echo "<h3>🔗 Quick Links</h3>";
echo "<a href='generate_report.php' style='margin-right: 10px;'>Attendance Report</a>";
echo "<a href='test_report_fixed.php' style='margin-right: 10px;'>Test Report (No Auth)</a>";
echo "<a href='login.php' style='margin-right: 10px;'>Login Page</a>";

mysqli_close($connect);
?>
