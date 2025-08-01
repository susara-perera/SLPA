<?php
include('./dbc.php');

echo "<h2>🔍 Debugging Employee ID: 39841</h2>";

// Test 1: Check if employee exists
echo "<h3>1. Employee Existence Check</h3>";
$emp_check = mysqli_query($connect, "SELECT * FROM employees WHERE employee_ID = '39841'");
if ($emp_check && mysqli_num_rows($emp_check) > 0) {
    $emp_data = mysqli_fetch_assoc($emp_check);
    echo "✅ Employee found in database:<br>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    foreach ($emp_data as $key => $value) {
        echo "<tr><td>$key</td><td>" . htmlspecialchars($value) . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "❌ Employee 39841 NOT found in employees table<br>";
    
    // Check similar IDs
    echo "<br><strong>Checking for similar employee IDs:</strong><br>";
    $similar_check = mysqli_query($connect, "SELECT employee_ID, employee_name FROM employees WHERE employee_ID LIKE '%39841%' OR employee_ID LIKE '39841%' OR employee_ID LIKE '%39841' LIMIT 10");
    if ($similar_check && mysqli_num_rows($similar_check) > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Employee ID</th><th>Name</th></tr>";
        while ($row = mysqli_fetch_assoc($similar_check)) {
            echo "<tr><td>" . htmlspecialchars($row['employee_ID']) . "</td><td>" . htmlspecialchars($row['employee_name']) . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "No similar employee IDs found.<br>";
    }
}

// Test 2: Check attendance records for this employee
echo "<h3>2. Attendance Records Check</h3>";
$att_check = mysqli_query($connect, "SELECT COUNT(*) as count FROM attendance WHERE employee_ID = '39841'");
if ($att_check) {
    $att_count = mysqli_fetch_assoc($att_check);
    echo "Attendance records for employee 39841: " . $att_count['count'] . "<br>";
    
    if ($att_count['count'] > 0) {
        // Show sample attendance records
        $att_sample = mysqli_query($connect, "SELECT * FROM attendance WHERE employee_ID = '39841' ORDER BY date_ DESC, time_ DESC LIMIT 10");
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Date</th><th>Time</th><th>Scan Type</th><th>Device</th></tr>";
        while ($att_row = mysqli_fetch_assoc($att_sample)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($att_row['date_']) . "</td>";
            echo "<td>" . htmlspecialchars($att_row['time_']) . "</td>";
            echo "<td>" . htmlspecialchars($att_row['scan_type']) . "</td>";
            echo "<td>" . htmlspecialchars($att_row['fingerprint_id']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "Error checking attendance: " . mysqli_error($connect) . "<br>";
}

// Test 3: Check for attendance records with different data types
echo "<h3>3. Alternative Employee ID Checks</h3>";
$alt_checks = [
    "employee_ID = 39841" => "As integer",
    "employee_ID = '039841'" => "With leading zero",
    "CAST(employee_ID AS UNSIGNED) = 39841" => "Cast as number",
    "employee_ID LIKE '%39841%'" => "Contains 39841"
];

foreach ($alt_checks as $condition => $description) {
    $alt_query = "SELECT COUNT(*) as count FROM attendance WHERE $condition";
    $alt_result = mysqli_query($connect, $alt_query);
    if ($alt_result) {
        $alt_count = mysqli_fetch_assoc($alt_result);
        echo "$description: " . $alt_count['count'] . " records<br>";
    }
}

// Test 4: Sample of all employee IDs to understand the format
echo "<h3>4. Sample Employee IDs (to understand format)</h3>";
$sample_emp = mysqli_query($connect, "SELECT DISTINCT employee_ID FROM attendance ORDER BY employee_ID LIMIT 20");
if ($sample_emp) {
    echo "Sample employee IDs in attendance table:<br>";
    echo "<div style='columns: 4; column-gap: 20px;'>";
    while ($row = mysqli_fetch_assoc($sample_emp)) {
        echo htmlspecialchars($row['employee_ID']) . "<br>";
    }
    echo "</div>";
}

// Test 5: Test the exact query that generate_report.php uses
echo "<h3>5. Testing Generate Report Query</h3>";
$from_date = '2024-01-01';
$to_date = '2025-12-31';
$employee_ID = '39841';

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

echo "Query: <pre>" . htmlspecialchars($sql) . "</pre>";
echo "Parameters: employee_ID='$employee_ID', from_date='$from_date', to_date='$to_date'<br>";

$stmt = mysqli_prepare($connect, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sss", $employee_ID, $from_date, $to_date);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $count = 0;
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Employee ID</th><th>Name</th><th>Division</th><th>Section</th><th>Date</th><th>Time</th><th>Type</th></tr>";
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
        echo "</tr>";
    }
    echo "</table>";
    echo "Total records found: $count<br>";
    
    mysqli_stmt_close($stmt);
} else {
    echo "Error preparing statement: " . mysqli_error($connect) . "<br>";
}

mysqli_close($connect);
?>
