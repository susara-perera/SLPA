<?php
include_once 'dbc.php';

echo "<h1>🕒 Attendance Data Management</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .card { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .success { color: #27ae60; padding: 10px; background: #d4edda; border-radius: 4px; }
    .error { color: #e74c3c; padding: 10px; background: #f8d7da; border-radius: 4px; }
    .info { color: #3498db; padding: 10px; background: #d1ecf1; border-radius: 4px; }
    button { background: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
    button:hover { background: #2980b9; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

// Handle form submission
if ($_POST) {
    if (isset($_POST['create_sample_data'])) {
        createSampleAttendanceData();
    } elseif (isset($_POST['clear_data'])) {
        clearAttendanceData();
    }
}

function createSampleAttendanceData() {
    global $connect;
    
    // Sample employee IDs from your screenshot
    $employees = ['540567', '435917', '471474', '416032', '471193', '410522'];
    $success_count = 0;
    
    // Generate data for the last 30 days
    for ($i = 30; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        
        foreach ($employees as $emp_id) {
            // Morning IN (random time between 8:00-9:00)
            $in_time = sprintf("%02d:%02d:%02d", 
                rand(8, 8), 
                rand(0, 59), 
                rand(0, 59)
            );
            
            // Evening OUT (random time between 17:00-18:00)
            $out_time = sprintf("%02d:%02d:%02d", 
                rand(17, 17), 
                rand(0, 59), 
                rand(0, 59)
            );
            
            // Generate fingerprint IDs
            $fingerprint_in = 'COM' . rand(10, 15) . 'Aus';
            $fingerprint_out = 'COM' . rand(10, 15) . 'Aus';
            
            // Insert IN record
            $sql_in = "INSERT INTO attendance (employee_ID, fingerprint_id, date_, time_, scan_type) VALUES (?, ?, ?, ?, 'IN')";
            $stmt_in = mysqli_prepare($connect, $sql_in);
            if ($stmt_in) {
                mysqli_stmt_bind_param($stmt_in, "ssss", $emp_id, $fingerprint_in, $date, $in_time);
                if (mysqli_stmt_execute($stmt_in)) {
                    $success_count++;
                }
                mysqli_stmt_close($stmt_in);
            }
            
            // Insert OUT record (80% chance - sometimes people forget to clock out)
            if (rand(1, 100) <= 80) {
                $sql_out = "INSERT INTO attendance (employee_ID, fingerprint_id, date_, time_, scan_type) VALUES (?, ?, ?, ?, 'OUT')";
                $stmt_out = mysqli_prepare($connect, $sql_out);
                if ($stmt_out) {
                    mysqli_stmt_bind_param($stmt_out, "ssss", $emp_id, $fingerprint_out, $date, $out_time);
                    if (mysqli_stmt_execute($stmt_out)) {
                        $success_count++;
                    }
                    mysqli_stmt_close($stmt_out);
                }
            }
        }
    }
    
    echo "<div class='success'>✅ Created $success_count sample attendance records!</div>";
}

function clearAttendanceData() {
    global $connect;
    
    $sql = "DELETE FROM attendance WHERE 1=1";
    if (mysqli_query($connect, $sql)) {
        $deleted = mysqli_affected_rows($connect);
        echo "<div class='success'>✅ Cleared $deleted attendance records!</div>";
    } else {
        echo "<div class='error'>❌ Error clearing data: " . mysqli_error($connect) . "</div>";
    }
}

// Check current data
$count_sql = "SELECT COUNT(*) as total FROM attendance";
$count_result = mysqli_query($connect, $count_sql);
$total_records = $count_result ? mysqli_fetch_assoc($count_result)['total'] : 0;

echo "<div class='card'>";
echo "<h2>📊 Current Attendance Data</h2>";
echo "<div class='info'>Total attendance records in database: <strong>$total_records</strong></div>";

if ($total_records > 0) {
    // Show sample of existing data
    $sample_sql = "SELECT * FROM attendance ORDER BY date_ DESC, time_ DESC LIMIT 10";
    $sample_result = mysqli_query($connect, $sample_sql);
    
    if ($sample_result && mysqli_num_rows($sample_result) > 0) {
        echo "<h3>📋 Latest 10 Records:</h3>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Employee ID</th><th>Date</th><th>Time</th><th>Scan Type</th><th>Fingerprint ID</th></tr>";
        
        while ($row = mysqli_fetch_assoc($sample_result)) {
            echo "<tr>";
            echo "<td>" . $row['attendance_id'] . "</td>";
            echo "<td>" . $row['employee_ID'] . "</td>";
            echo "<td>" . $row['date_'] . "</td>";
            echo "<td>" . $row['time_'] . "</td>";
            echo "<td>" . $row['scan_type'] . "</td>";
            echo "<td>" . $row['fingerprint_id'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Show employee summary
    $employee_sql = "SELECT employee_ID, COUNT(*) as record_count, 
                     MIN(date_) as first_date, MAX(date_) as last_date
                     FROM attendance 
                     GROUP BY employee_ID 
                     ORDER BY record_count DESC";
    $employee_result = mysqli_query($connect, $employee_sql);
    
    if ($employee_result && mysqli_num_rows($employee_result) > 0) {
        echo "<h3>👥 Employee Summary:</h3>";
        echo "<table>";
        echo "<tr><th>Employee ID</th><th>Total Records</th><th>First Date</th><th>Last Date</th></tr>";
        
        while ($row = mysqli_fetch_assoc($employee_result)) {
            echo "<tr>";
            echo "<td>" . $row['employee_ID'] . "</td>";
            echo "<td>" . $row['record_count'] . "</td>";
            echo "<td>" . $row['first_date'] . "</td>";
            echo "<td>" . $row['last_date'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}
echo "</div>";

?>

<div class='card'>
    <h2>🛠️ Data Management Tools</h2>
    
    <?php if ($total_records == 0): ?>
    <div class='info'>
        <p>No attendance data found. You can create sample data for testing the reports.</p>
    </div>
    <?php endif; ?>
    
    <form method="POST" style="display: inline;">
        <button type="submit" name="create_sample_data" 
                onclick="return confirm('This will create sample attendance data for the last 30 days. Continue?')">
            📊 Create Sample Attendance Data
        </button>
    </form>
    
    <?php if ($total_records > 0): ?>
    <form method="POST" style="display: inline;">
        <button type="submit" name="clear_data" 
                onclick="return confirm('This will delete ALL attendance data. Are you sure?')"
                style="background: #dc3545;">
            🗑️ Clear All Data
        </button>
    </form>
    <?php endif; ?>
</div>

<div class='card'>
    <h2>🔗 Quick Links</h2>
    <p><a href="attendance_report.php">📈 View Attendance Reports</a></p>
    <p><a href="test_database_connection.php">🔍 Test Database Connection</a></p>
    <p><a href="user_management.php">👥 User Management</a></p>
</div>

<div class='card'>
    <h2>📝 Usage Instructions</h2>
    <ol>
        <li><strong>Create Sample Data:</strong> Click "Create Sample Attendance Data" to generate test records for the last 30 days</li>
        <li><strong>View Reports:</strong> Go to "attendance_report.php" to generate individual or group reports</li>
        <li><strong>Export Data:</strong> Use the export features in the report to download Excel/PDF files</li>
        <li><strong>Real Data:</strong> Replace sample data with real attendance records from your biometric devices</li>
    </ol>
</div>
