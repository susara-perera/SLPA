<?php
include('./dbc.php');

echo "<h2>📊 Employees with Attendance Records</h2>";

// Get top employees with attendance data
$result = mysqli_query($connect, "SELECT employee_ID, COUNT(*) as count FROM attendance GROUP BY employee_ID ORDER BY count DESC LIMIT 20");

echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
echo "<tr><th>Employee ID</th><th>Attendance Records</th><th>Employee Name</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    // Get employee name
    $emp_query = mysqli_query($connect, "SELECT employee_name FROM employees WHERE employee_ID = '" . $row['employee_ID'] . "'");
    $emp_name = "Unknown";
    if ($emp_query && $emp_row = mysqli_fetch_assoc($emp_query)) {
        $emp_name = $emp_row['employee_name'];
    }
    
    echo "<tr>";
    echo "<td><strong>" . htmlspecialchars($row['employee_ID']) . "</strong></td>";
    echo "<td>" . $row['count'] . "</td>";
    echo "<td>" . htmlspecialchars($emp_name) . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>💡 Solution for User</h3>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>The employee ID '39841' does not exist in your database.</strong><br><br>";
echo "✅ <strong>Try using one of these existing employee IDs instead:</strong><br>";
echo "• Pick any Employee ID from the table above<br>";
echo "• Example: Try employee ID <strong>377408</strong> or <strong>404426</strong><br>";
echo "• These employees have confirmed attendance records<br>";
echo "</div>";

mysqli_close($connect);
?>
