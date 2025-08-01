<?php
// Quick test to check attendance data without authentication
include('./dbc.php');

echo "<h2>Direct Attendance Data Test</h2>";

// Test query similar to what generate_report.php uses
$from_date = '2025-03-01';  // Based on your screenshot showing March 2025 data
$to_date = '2025-03-31';

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
        WHERE a.date_ BETWEEN ? AND ?
        ORDER BY a.employee_ID ASC, a.date_, a.time_
        LIMIT 10";

$stmt = mysqli_prepare($connect, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ss", $from_date, $to_date);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Employee ID</th><th>Employee Name</th><th>Division</th><th>Section</th><th>Date</th><th>Time</th><th>Scan Type</th><th>Device</th></tr>";
    
    $count = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        $count++;
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['employee_ID']) . "</td>";
        echo "<td>" . htmlspecialchars($row['employee_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['division_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['section_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['date_']) . "</td>";
        echo "<td>" . htmlspecialchars($row['time_']) . "</td>";
        echo "<td>" . htmlspecialchars($row['scan_type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['fingerprint_device']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p>Records found: $count</p>";
    
    if ($count == 0) {
        echo "<h3>Testing with different date range...</h3>";
        
        // Test with a broader date range
        $from_date2 = '2024-01-01';
        $to_date2 = '2025-12-31';
        
        $stmt2 = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt2, "ss", $from_date2, $to_date2);
        mysqli_stmt_execute($stmt2);
        $result2 = mysqli_stmt_get_result($stmt2);
        
        $count2 = 0;
        while ($row2 = mysqli_fetch_assoc($result2)) {
            $count2++;
            if ($count2 <= 5) {  // Show first 5 records
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row2['employee_ID']) . "</td>";
                echo "<td>" . htmlspecialchars($row2['employee_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row2['division_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row2['section_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row2['date_']) . "</td>";
                echo "<td>" . htmlspecialchars($row2['time_']) . "</td>";
                echo "<td>" . htmlspecialchars($row2['scan_type']) . "</td>";
                echo "<td>" . htmlspecialchars($row2['fingerprint_device']) . "</td>";
                echo "</tr>";
            }
        }
        echo "<p>Records found with broader range: $count2</p>";
        mysqli_stmt_close($stmt2);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo "Error preparing statement: " . mysqli_error($connect);
}

// Check if employees table has data
echo "<h3>Employee Table Check</h3>";
$emp_check = mysqli_query($connect, "SELECT COUNT(*) as count FROM employees");
$emp_count = mysqli_fetch_assoc($emp_check);
echo "Total employees: " . $emp_count['count'] . "<br>";

// Check sample employees
$emp_sample = mysqli_query($connect, "SELECT * FROM employees LIMIT 5");
echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
echo "<tr><th>Employee ID</th><th>Employee Name</th><th>Division</th><th>Section</th></tr>";
while ($emp_row = mysqli_fetch_assoc($emp_sample)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($emp_row['employee_ID']) . "</td>";
    echo "<td>" . htmlspecialchars($emp_row['employee_name']) . "</td>";
    echo "<td>" . htmlspecialchars($emp_row['division']) . "</td>";
    echo "<td>" . htmlspecialchars($emp_row['section']) . "</td>";
    echo "</tr>";
}
echo "</table>";

mysqli_close($connect);
?>
