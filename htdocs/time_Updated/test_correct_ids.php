<?php
include('./dbc.php');

// Test the likely correct employee ID
$test_ids = ['398412', '539841'];

foreach ($test_ids as $emp_id) {
    echo "<h3>Testing Employee ID: $emp_id</h3>";
    
    // Check attendance count
    $result = mysqli_query($connect, "SELECT COUNT(*) as count FROM attendance WHERE employee_ID = '$emp_id'");
    $row = mysqli_fetch_assoc($result);
    echo "Attendance records: " . $row['count'] . "<br>";
    
    if ($row['count'] > 0) {
        // Get employee details
        $emp_result = mysqli_query($connect, "SELECT * FROM employees WHERE employee_ID = '$emp_id'");
        if ($emp_row = mysqli_fetch_assoc($emp_result)) {
            echo "Employee Name: " . htmlspecialchars($emp_row['employee_name']) . "<br>";
            echo "Division: " . htmlspecialchars($emp_row['division']) . "<br>";
            echo "Section: " . htmlspecialchars($emp_row['section']) . "<br>";
        }
        
        // Show recent attendance
        $att_result = mysqli_query($connect, "SELECT * FROM attendance WHERE employee_ID = '$emp_id' ORDER BY date_ DESC, time_ DESC LIMIT 5");
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Date</th><th>Time</th><th>Scan Type</th></tr>";
        while ($att_row = mysqli_fetch_assoc($att_result)) {
            echo "<tr>";
            echo "<td>" . $att_row['date_'] . "</td>";
            echo "<td>" . $att_row['time_'] . "</td>";
            echo "<td>" . $att_row['scan_type'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "✅ This employee ID works!<br><br>";
    } else {
        echo "❌ No attendance records found<br><br>";
    }
}

mysqli_close($connect);
?>
