<?php
// Temporary bypass for testing - REMOVE IN PRODUCTION
session_start();
$_SESSION['user_id'] = 'test';
$_SESSION['username'] = 'Test User';

include('./dbc.php');

// Set timezone for accurate timestamps
date_default_timezone_set('Asia/Colombo'); // Sri Lanka timezone

// Get current user info
$current_user = isset($_SESSION['username']) ? $_SESSION['username'] : 'SLPA User';

// Auto-generate a test report with recent data
$_POST['report_type'] = 'group';
$_POST['from_date'] = '2025-03-01';
$_POST['to_date'] = '2025-03-31';
$_POST['division'] = '';
$_POST['section'] = 'all';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLPA - Unit Attendance Report (Test)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 1200px; }
        .table { font-size: 14px; }
        .table th { background: #2c3e50; color: white; }
        .table tbody tr:hover { background-color: #f8f9fa; }
    </style>
</head>
<body>

<div class="container">
    <div class="alert alert-warning">
        <strong>Test Mode:</strong> Authentication bypassed for debugging. This shows attendance data from March 2025.
    </div>

    <?php
    // Process the test report
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || true) { // Force processing
        $report_generated_time = date('Y-m-d H:i:s');
        
        $report_type = $_POST['report_type'];
        $from_date = $_POST['from_date'];
        $to_date = $_POST['to_date'];

        echo "<h2>🔍 Debugging Information</h2>";
        echo "<p><strong>Report Type:</strong> $report_type</p>";
        echo "<p><strong>Date Range:</strong> $from_date to $to_date</p>";

        // Initialize variables for report data
        $report_records = [];
        $total_employees = 0;
        $total_records = 0;

        // Build query for group report
        $division = $_POST['division'];
        $section = $_POST['section'];
        
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
                WHERE a.date_ BETWEEN ? AND ?";
        
        $params = [$from_date, $to_date];
        $types = "ss";
        
        if (!empty($division)) {
            $sql .= " AND e.division = ?";
            $params[] = $division;
            $types .= "s";
        }
        
        if (!empty($section) && $section != 'all') {
            $sql .= " AND e.section = ?";
            $params[] = $section;
            $types .= "s";
        }
        
        $sql .= " ORDER BY a.employee_ID ASC, a.date_, a.time_";
        
        echo "<h3>📊 SQL Query:</h3>";
        echo "<pre>" . htmlspecialchars($sql) . "</pre>";
        echo "<p><strong>Parameters:</strong> " . implode(', ', $params) . "</p>";

        $stmt = mysqli_prepare($connect, $sql);
        if (!$stmt) {
            echo "<div class='alert alert-danger'>SQL Prepare Error: " . mysqli_error($connect) . "</div>";
        } else {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            // Process results for display
            $attendance_data = [];
            $raw_count = 0;
            while ($row = mysqli_fetch_assoc($result)) {
                $raw_count++;
                $key = $row['employee_ID'] . '_' . $row['date_'];
                
                if (!isset($attendance_data[$key])) {
                    $attendance_data[$key] = [
                        'employee_ID' => $row['employee_ID'],
                        'employee_name' => $row['employee_name'],
                        'fingerprint_device' => $row['fingerprint_device'],
                        'division_name' => $row['division_name'],
                        'section_name' => $row['section_name'],
                        'date_' => $row['date_'],
                        'check_in_times' => [],
                        'check_out_times' => [],
                        'check_in_devices' => [],
                        'check_out_devices' => [],
                        'first_check_in' => null,
                        'last_check_out' => null,
                        'first_check_in_device' => null,
                        'last_check_out_device' => null
                    ];
                }
                
                if ($row['scan_type'] === 'IN') {
                    $attendance_data[$key]['check_in_times'][] = $row['time_'];
                    $attendance_data[$key]['check_in_devices'][] = $row['fingerprint_device'];
                    if ($attendance_data[$key]['first_check_in'] === null || 
                        $row['time_'] < $attendance_data[$key]['first_check_in']) {
                        $attendance_data[$key]['first_check_in'] = $row['time_'];
                        $attendance_data[$key]['first_check_in_device'] = $row['fingerprint_device'];
                    }
                } else if ($row['scan_type'] === 'OUT') {
                    $attendance_data[$key]['check_out_times'][] = $row['time_'];
                    $attendance_data[$key]['check_out_devices'][] = $row['fingerprint_device'];
                    if ($attendance_data[$key]['last_check_out'] === null || 
                        $row['time_'] > $attendance_data[$key]['last_check_out']) {
                        $attendance_data[$key]['last_check_out'] = $row['time_'];
                        $attendance_data[$key]['last_check_out_device'] = $row['fingerprint_device'];
                    }
                }
            }

            $total_records = count($attendance_data);
            $total_employees = count(array_unique(array_column($attendance_data, 'employee_ID')));
            
            echo "<h3>📈 Processing Results:</h3>";
            echo "<p><strong>Raw records from DB:</strong> $raw_count</p>";
            echo "<p><strong>Processed attendance days:</strong> $total_records</p>";
            echo "<p><strong>Unique employees:</strong> $total_employees</p>";

            if ($total_records > 0) {
                ?>
                <div class="alert alert-success">
                    ✅ <strong>Success!</strong> Found <?php echo $total_records; ?> attendance records for <?php echo $total_employees; ?> employees.
                </div>

                <!-- Results Table -->
                <div class="mt-4">
                    <h3>📋 Attendance Report Results</h3>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Employee ID</th>
                                    <th>Employee Name</th>
                                    <th>Division</th>
                                    <th>Section</th>
                                    <th>Date</th>
                                    <th>First Check In</th>
                                    <th>Last Check Out</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $row_count = 0;
                                foreach ($attendance_data as $record): 
                                    $row_count++;
                                ?>
                                <tr>
                                    <td><?php echo $row_count; ?></td>
                                    <td><strong><?php echo htmlspecialchars($record['employee_ID']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($record['employee_name']); ?></td>
                                    <td><?php echo htmlspecialchars($record['division_name']); ?></td>
                                    <td><?php echo htmlspecialchars($record['section_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($record['date_'])); ?></td>
                                    <td>
                                        <?php if ($record['first_check_in']): ?>
                                            <span class="badge bg-success">
                                                <?php echo date('H:i:s', strtotime($record['first_check_in'])); ?>
                                            </span><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($record['first_check_in_device']); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">No check-in</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($record['last_check_out']): ?>
                                            <span class="badge bg-danger">
                                                <?php echo date('H:i:s', strtotime($record['last_check_out'])); ?>
                                            </span><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($record['last_check_out_device']); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">No check-out</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="alert alert-info mt-4">
                    <h5>🔧 What this proves:</h5>
                    <ul>
                        <li>✅ Database connection works</li>
                        <li>✅ MySQLi queries execute properly</li>
                        <li>✅ Data joins work (employees, divisions, sections)</li>
                        <li>✅ Data processing logic works</li>
                        <li>✅ Table display works</li>
                    </ul>
                    <p><strong>Main Issue:</strong> The original generate_report.php requires user authentication. Users need to log in first, or you need to access it through the proper login flow.</p>
                </div>
                <?php
            } else {
                ?>
                <div class="alert alert-warning">
                    ⚠️ <strong>No records found.</strong> This could be due to:
                    <ul>
                        <li>Date range doesn't match available data</li>
                        <li>No employees assigned to divisions/sections</li>
                        <li>Attendance data doesn't have matching employee records</li>
                    </ul>
                </div>
                <?php
            }
            
            mysqli_stmt_close($stmt);
        }
    }
    ?>
    
    <div class="mt-4">
        <a href="generate_report.php" class="btn btn-primary">Go to Original Report (Requires Login)</a>
        <a href="create_session.php" class="btn btn-secondary">Create Test Session</a>
    </div>
</div>

</body>
</html>
