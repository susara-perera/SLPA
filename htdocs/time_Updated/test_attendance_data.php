<?php
include_once 'dbc.php';

echo "<h2>Testing Attendance Data Connection</h2>";

// Test 1: Check if we can connect to database
echo "<h3>1. Database Connection Test</h3>";
if ($connect) {
    echo "✅ Database connected successfully<br>";
    echo "Connected to database: " . $dbName . "<br><br>";
} else {
    echo "❌ Database connection failed: " . mysqli_connect_error() . "<br><br>";
    exit;
}

// Test 2: Check if attendance table exists
echo "<h3>2. Attendance Table Check</h3>";
$table_check = mysqli_query($connect, "SHOW TABLES LIKE 'attendance'");
if (mysqli_num_rows($table_check) > 0) {
    echo "✅ Attendance table exists<br><br>";
} else {
    echo "❌ Attendance table does not exist<br><br>";
    exit;
}

// Test 3: Check attendance table structure
echo "<h3>3. Attendance Table Structure</h3>";
$structure = mysqli_query($connect, "DESCRIBE attendance");
echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($row = mysqli_fetch_assoc($structure)) {
    echo "<tr>";
    echo "<td>{$row['Field']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Key']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "<td>{$row['Extra']}</td>";
    echo "</tr>";
}
echo "</table><br>";

// Test 4: Count total records
echo "<h3>4. Record Count</h3>";
$count_query = mysqli_query($connect, "SELECT COUNT(*) as total FROM attendance");
$count_result = mysqli_fetch_assoc($count_query);
echo "Total attendance records: " . $count_result['total'] . "<br><br>";

// Test 5: Show sample records (first 10)
echo "<h3>5. Sample Records (First 10)</h3>";
if ($count_result['total'] > 0) {
    $sample_query = mysqli_query($connect, "SELECT * FROM attendance ORDER BY date_ DESC, time_ DESC LIMIT 10");
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>ID</th><th>Employee ID</th><th>Fingerprint ID</th><th>Date</th><th>Time</th><th>Scan Type</th></tr>";
    
    while ($row = mysqli_fetch_assoc($sample_query)) {
        echo "<tr>";
        echo "<td>{$row['attendance_id']}</td>";
        echo "<td>{$row['employee_ID']}</td>";
        echo "<td>{$row['fingerprint_id']}</td>";
        echo "<td>{$row['date_']}</td>";
        echo "<td>{$row['time']}</td>";
        echo "<td>{$row['scan_type']}</td>";
        echo "</tr>";
    }
    echo "</table><br>";
} else {
    echo "No records found in attendance table.<br><br>";
}

// Test 6: Check for specific employee (from the screenshot - 540567)
echo "<h3>6. Test Employee 540567 Records</h3>";
$test_employee = "540567";
$emp_query = mysqli_query($connect, "SELECT * FROM attendance WHERE employee_ID = '$test_employee' ORDER BY date_ DESC, time_ DESC LIMIT 5");
$emp_count = mysqli_num_rows($emp_query);

echo "Records found for employee $test_employee: $emp_count<br>";
if ($emp_count > 0) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Date</th><th>Time</th><th>Scan Type</th><th>Fingerprint ID</th></tr>";
    
    while ($row = mysqli_fetch_assoc($emp_query)) {
        echo "<tr>";
        echo "<td>{$row['date_']}</td>";
        echo "<td>{$row['time']}</td>";
        echo "<td>{$row['scan_type']}</td>";
        echo "<td>{$row['fingerprint_id']}</td>";
        echo "</tr>";
    }
    echo "</table><br>";
}

// Test 7: Check date range (last 30 days)
echo "<h3>7. Recent Records (Last 30 Days)</h3>";
$thirty_days_ago = date('Y-m-d', strtotime('-30 days'));
$today = date('Y-m-d');

$recent_query = mysqli_query($connect, "SELECT COUNT(*) as recent_count FROM attendance WHERE date_ BETWEEN '$thirty_days_ago' AND '$today'");
$recent_result = mysqli_fetch_assoc($recent_query);
echo "Records in last 30 days ($thirty_days_ago to $today): " . $recent_result['recent_count'] . "<br><br>";

// Test 8: Test the exact query from get_attendance_report.php
echo "<h3>8. Testing Report Query</h3>";
$report_query = "SELECT 
    attendance_id,
    employee_ID,
    fingerprint_id,
    date_,
    time_,
    scan_type
FROM attendance 
WHERE date_ BETWEEN '$thirty_days_ago' AND '$today'
ORDER BY date_ DESC, time_ DESC
LIMIT 5";

echo "Query: <pre>$report_query</pre>";

$report_result = mysqli_query($connect, $report_query);
if ($report_result) {
    $report_count = mysqli_num_rows($report_result);
    echo "Query executed successfully. Records found: $report_count<br>";
    
    if ($report_count > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Employee ID</th><th>Date</th><th>Time</th><th>Scan Type</th><th>Fingerprint ID</th></tr>";
        
        while ($row = mysqli_fetch_assoc($report_result)) {
            echo "<tr>";
            echo "<td>{$row['employee_ID']}</td>";
            echo "<td>{$row['date_']}</td>";
            echo "<td>{$row['time_']}</td>";
            echo "<td>{$row['scan_type']}</td>";
            echo "<td>{$row['fingerprint_id']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "❌ Query failed: " . mysqli_error($connect) . "<br>";
}

mysqli_close($connect);
?>
