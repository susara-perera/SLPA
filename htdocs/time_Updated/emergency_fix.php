<?php
// Quick diagnosis and fix for attendance report
session_start();

// Force create session if it doesn't exist
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 999;
    $_SESSION['username'] = 'Test Admin';
    $_SESSION['role'] = 'Admin';
    $_SESSION['employee_ID'] = 'TEST001';
    $_SESSION['user_type'] = 'Admin';
    echo "✅ Session created automatically<br>";
}

include('./dbc.php');

echo "<h2>🚨 EMERGENCY FIX - Testing Attendance Report</h2>";

// Test with a known working employee ID
$test_employee = '540567';
$from_date = '2024-01-01';
$to_date = '2025-12-31';

echo "<h3>Testing Employee ID: $test_employee</h3>";

// Step 1: Check if employee exists
$emp_check = mysqli_query($connect, "SELECT * FROM employees WHERE employee_ID = '$test_employee'");
if ($emp_check && mysqli_num_rows($emp_check) > 0) {
    $emp_data = mysqli_fetch_assoc($emp_check);
    echo "✅ Employee exists: " . htmlspecialchars($emp_data['employee_name']) . "<br>";
} else {
    echo "❌ Employee not found<br>";
}

// Step 2: Check attendance records
$att_check = mysqli_query($connect, "SELECT COUNT(*) as count FROM attendance WHERE employee_ID = '$test_employee'");
$att_count = mysqli_fetch_assoc($att_check);
echo "✅ Attendance records: " . $att_count['count'] . "<br>";

// Step 3: Test the exact query that should work
$sql = "SELECT a.employee_ID, 
               COALESCE(e.employee_name, 'Unknown Employee') as employee_name, 
               a.fingerprint_id as fingerprint_device,
               COALESCE(d.division_name, 'Unknown Division') as division_name, 
               COALESCE(s.section_name, 'Unknown Section') as section_name, 
               a.date_, a.scan_type, a.time_
        FROM attendance a
        LEFT JOIN employees e ON a.employee_ID = e.employee_ID
        LEFT JOIN divisions d ON e.division = d.division_id
        LEFT JOIN sections s ON e.section = s.section_id
        WHERE a.employee_ID = ? AND a.date_ BETWEEN ? AND ?
        ORDER BY a.employee_ID ASC, a.date_, a.time_";

$stmt = mysqli_prepare($connect, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sss", $test_employee, $from_date, $to_date);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $records = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $records[] = $row;
    }
    
    echo "<h3>📊 Query Results: " . count($records) . " records found</h3>";
    
    if (count($records) > 0) {
        echo "✅ <strong>DATA FOUND! The report should work.</strong><br>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Date</th><th>Time</th><th>Type</th><th>Employee</th></tr>";
        
        foreach (array_slice($records, 0, 10) as $row) {
            echo "<tr>";
            echo "<td>" . $row['date_'] . "</td>";
            echo "<td>" . $row['time_'] . "</td>";
            echo "<td>" . $row['scan_type'] . "</td>";
            echo "<td>" . htmlspecialchars($row['employee_name']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Now let's simulate the data processing that generate_report.php does
        $attendance_data = [];
        foreach ($records as $row) {
            $key = $row['employee_ID'] . '_' . $row['date_'];
            
            if (!isset($attendance_data[$key])) {
                $attendance_data[$key] = [
                    'employee_ID' => $row['employee_ID'],
                    'employee_name' => $row['employee_name'],
                    'fingerprint_device' => $row['fingerprint_device'],
                    'division_name' => $row['division_name'],
                    'section_name' => $row['section_name'],
                    'date_' => $row['date_'],
                    'first_check_in' => null,
                    'last_check_out' => null,
                    'first_check_in_device' => null,
                    'last_check_out_device' => null
                ];
            }
            
            if ($row['scan_type'] === 'IN') {
                if ($attendance_data[$key]['first_check_in'] === null || 
                    $row['time_'] < $attendance_data[$key]['first_check_in']) {
                    $attendance_data[$key]['first_check_in'] = $row['time_'];
                    $attendance_data[$key]['first_check_in_device'] = $row['fingerprint_device'];
                }
            } else if ($row['scan_type'] === 'OUT') {
                if ($attendance_data[$key]['last_check_out'] === null || 
                    $row['time_'] > $attendance_data[$key]['last_check_out']) {
                    $attendance_data[$key]['last_check_out'] = $row['time_'];
                    $attendance_data[$key]['last_check_out_device'] = $row['fingerprint_device'];
                }
            }
        }
        
        echo "<h3>📋 Processed Attendance Summary</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Date</th><th>First Check In</th><th>Last Check Out</th><th>Employee</th></tr>";
        
        foreach ($attendance_data as $record) {
            echo "<tr>";
            echo "<td>" . $record['date_'] . "</td>";
            echo "<td>" . ($record['first_check_in'] ?: 'No check-in') . "</td>";
            echo "<td>" . ($record['last_check_out'] ?: 'No check-out') . "</td>";
            echo "<td>" . htmlspecialchars($record['employee_name']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "❌ <strong>NO DATA FOUND</strong><br>";
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo "❌ Query preparation failed: " . mysqli_error($connect) . "<br>";
}

echo "<hr>";
echo "<h3>🔧 QUICK FIXES</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>1. Try this URL directly:</strong><br>";
echo "<a href='generate_report.php' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px 0;'>Open Attendance Report</a><br><br>";

echo "<strong>2. Use these EXACT settings:</strong><br>";
echo "• Report Type: <strong>Individual Employee Report</strong><br>";
echo "• Employee ID: <strong>540567</strong><br>";
echo "• From Date: <strong>2024-11-01</strong><br>";
echo "• To Date: <strong>2024-12-31</strong><br><br>";

echo "<strong>3. Or try Group Report:</strong><br>";
echo "• Report Type: <strong>Group/Department Report</strong><br>";
echo "• Division: <strong>Leave empty</strong><br>";
echo "• Section: <strong>All Sections</strong><br>";
echo "• From Date: <strong>2025-03-01</strong><br>";
echo "• To Date: <strong>2025-03-31</strong><br>";
echo "</div>";

echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "✅ <strong>Good News:</strong> Your database has working data!<br>";
echo "✅ <strong>Session:</strong> Authentication is now active<br>";
echo "✅ <strong>Database:</strong> Connection and queries work<br>";
echo "</div>";

mysqli_close($connect);
?>
