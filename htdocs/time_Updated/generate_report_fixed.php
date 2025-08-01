<?php
session_start();
include('./dbc.php');

// Set timezone for accurate timestamps
date_default_timezone_set('Asia/Colombo'); // Sri Lanka timezone

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get current user info
$current_user = isset($_SESSION['username']) ? $_SESSION['username'] : 'SLPA User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLPA - Unit Attendance Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Modern Professional Styling */
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --border-radius: 12px;
            --card-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        /* Header Styling */
        .report-header {
            background: linear-gradient(135deg, var(--primary-color), #34495e);
            color: white;
            padding: 25px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            box-shadow: var(--card-shadow);
            position: relative;
            overflow: hidden;
        }

        .report-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="80" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="60" r="1" fill="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.5;
        }

        .slpa-logo {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #fff, #f8f9fa);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 20px;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        /* Form Container */
        .form-container {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            padding: 35px;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 25px;
        }

        .form-section-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 25px;
            text-align: center;
            position: relative;
        }

        .form-section-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            border-radius: 2px;
        }

        /* Form Controls */
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.9);
        }

        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.15);
            background: white;
            transform: translateY(-1px);
            border-color: var(--secondary-color);
        }

        .form-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
            font-size: 14px;
        }

        /* Radio Button Styling */
        .form-check {
            background: rgba(255,255,255,0.8);
            padding: 15px 20px;
            border-radius: 10px;
            margin: 8px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .form-check:hover {
            border-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.2);
        }

        .form-check-input:checked + .form-check-label {
            color: var(--secondary-color);
            font-weight: 600;
        }

        /* Button Styling */
        .btn-custom {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            border: none;
            padding: 15px 40px;
            border-radius: 25px;
            color: white;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .btn-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(52, 152, 219, 0.4);
            color: white;
        }

        .btn-download {
            background: linear-gradient(135deg, var(--success-color), #2ecc71);
            border: none;
            padding: 12px 25px;
            border-radius: 20px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 0 5px;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }

        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4);
            color: white;
        }

        /* Back Button */
        .back-button {
            background: linear-gradient(135deg, #6c757d, #495057);
            border: none;
            padding: 12px 25px;
            border-radius: 20px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-right: 10px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
            color: white;
            text-decoration: none;
        }

        /* Current Time Display */
        .current-time {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            padding: 12px 20px;
            border-radius: 20px;
            color: #1976d2;
            font-weight: 700;
            margin-left: 10px;
            border: 2px solid rgba(25, 118, 210, 0.2);
            box-shadow: 0 4px 15px rgba(25, 118, 210, 0.2);
        }

        /* Header Controls */
        .header-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* Report Container */
        .report-container {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            padding: 35px;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(255,255,255,0.2);
        }

        /* Table Styling */
        .table-container {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--card-shadow);
            background: white;
        }

        .table {
            margin-bottom: 0;
            border-radius: var(--border-radius);
        }

        .table thead th {
            background: linear-gradient(135deg, var(--primary-color), #34495e);
            color: white;
            border: none;
            padding: 18px 15px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 13px;
        }

        .table tbody td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.005);
            transition: all 0.2s ease;
        }

        /* Report Meta */
        .report-meta {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 25px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            border-left: 5px solid var(--secondary-color);
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        /* Status Badges */
        .status-badge {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-in {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border: 1px solid #b8dabc;
        }

        .status-out {
            background: linear-gradient(135deg, #f8d7da, #f1b0b7);
            color: #721c24;
            border: 1px solid #f1b0b7;
        }

        /* Content Layout */
        .container-fluid {
            margin-left: 0;
            width: 100%;
            padding: 20px;
            transition: all 0.3s ease;
        }

        /* Summary Cards */
        .summary-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            padding: 20px;
            text-align: center;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }

        .summary-card h4 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 10px;
        }

        .summary-card .number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--secondary-color);
        }

        /* Loading animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--secondary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Avatar styling */
        .avatar-sm {
            width: 32px;
            height: 32px;
            font-size: 14px;
            font-weight: 600;
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container-fluid {
                padding: 10px;
            }
            
            .form-container, .report-container {
                padding: 20px;
            }
            
            .header-controls {
                justify-content: center;
                margin-top: 15px;
            }
            
            .current-time {
                margin-left: 0;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="report-header">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="slpa-logo">
                    <i class="fas fa-anchor"></i>
                </div>
            </div>
            <div class="col">
                <h1 class="mb-1"><i class="fas fa-chart-line me-2"></i>SLPA Unit Attendance Report</h1>
                <p class="mb-0 opacity-75">Sri Lanka Port Authority - Comprehensive Attendance Management System</p>
            </div>
            <div class="col-auto">
                <div class="header-controls">
                    <a href="index.php" class="back-button">
                        <i class="fas fa-arrow-left me-2"></i>Back to Home
                    </a>
                    <div class="current-time" id="currentTime">
                        <i class="fas fa-clock me-2"></i>
                        <span id="timeDisplay"><?php echo date('M d, Y H:i:s'); ?></span>
                    </div>
                </div>
                <div class="mt-2 text-end">
                    <small class="d-block opacity-75">Generated by: <strong><?php echo htmlspecialchars($current_user); ?></strong></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Generation Form -->
    <div class="form-container fade-in-up">
        <form method="POST" id="reportForm">
            <!-- Report Configuration Header -->
            <div class="form-section-title">
                <i class="fas fa-cogs me-2"></i>Report Configuration
            </div>

            <!-- Report Type Selection -->
            <div class="row mb-4">
                <div class="col-12">
                    <label class="form-label">
                        <i class="fas fa-list-ul me-2"></i>Report Type
                    </label>
                    <div class="d-flex gap-3">
                        <div class="form-check flex-fill">
                            <input class="form-check-input" type="radio" name="report_type" id="individual" value="individual" checked>
                            <label class="form-check-label w-100" for="individual">
                                <i class="fas fa-user me-2"></i>Individual Employee Report
                                <small class="d-block text-muted mt-1">Generate report for a specific employee</small>
                            </label>
                        </div>
                        <div class="form-check flex-fill">
                            <input class="form-check-input" type="radio" name="report_type" id="group" value="group">
                            <label class="form-check-label w-100" for="group">
                                <i class="fas fa-users me-2"></i>Group/Department Report
                                <small class="d-block text-muted mt-1">Generate report for multiple employees</small>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Employee ID (for individual) -->
                <div class="col-md-6 mb-4" id="employee_id_section">
                    <label for="employee_ID" class="form-label">
                        <i class="fas fa-id-badge me-2"></i>Employee ID
                    </label>
                    <input type="text" class="form-control" id="employee_ID" name="employee_ID" 
                           value="540567"
                           placeholder="Enter Employee ID (e.g., 540567)" 
                           data-bs-toggle="tooltip" title="Enter the unique employee identification number">
                </div>

                <!-- Division (for group) -->
                <div class="col-md-6 mb-4" id="division_section" style="display: none;">
                    <label for="division" class="form-label">
                        <i class="fas fa-building me-2"></i>Division
                    </label>
                    <select class="form-select" id="division" name="division">
                        <option value="">Select Division</option>
                        <?php
                        $div_query = "SELECT * FROM divisions ORDER BY division_name";
                        $div_result = mysqli_query($connect, $div_query);
                        while($div_row = mysqli_fetch_assoc($div_result)) {
                            echo "<option value='" . $div_row['division_id'] . "'>" . htmlspecialchars($div_row['division_name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Section (for group) -->
                <div class="col-md-6 mb-4" id="section_section" style="display: none;">
                    <label for="section" class="form-label">
                        <i class="fas fa-sitemap me-2"></i>Section
                    </label>
                    <select class="form-select" id="section" name="section">
                        <option value="all">All Sections</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div class="col-md-6 mb-4">
                    <label for="from_date" class="form-label">
                        <i class="fas fa-calendar-alt me-2"></i>From Date
                    </label>
                    <input type="date" class="form-control" id="from_date" name="from_date" required
                           value="2024-11-01"
                           data-bs-toggle="tooltip" title="Select the start date for the report">
                </div>

                <div class="col-md-6 mb-4">
                    <label for="to_date" class="form-label">
                        <i class="fas fa-calendar-alt me-2"></i>To Date
                    </label>
                    <input type="date" class="form-control" id="to_date" name="to_date" required
                           value="2024-11-30"
                           data-bs-toggle="tooltip" title="Select the end date for the report">
                </div>
            </div>

            <!-- Generate Button -->
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-custom btn-lg">
                    <i class="fas fa-chart-bar me-2"></i>Generate Attendance Report
                </button>
            </div>
        </form>
    </div>

    <?php
    // Show valid employee IDs if requested
    if (isset($_GET['show_employees'])) {
        ?>
        <div class="report-container fade-in-up">
            <h3 class="mb-4"><i class="fas fa-users me-2"></i>Valid Employee IDs with Attendance Records</h3>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Employee Name</th>
                            <th>Attendance Records</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $emp_list_query = "SELECT a.employee_ID, e.employee_name, COUNT(*) as record_count 
                                          FROM attendance a 
                                          LEFT JOIN employees e ON a.employee_ID = e.employee_ID 
                                          GROUP BY a.employee_ID 
                                          ORDER BY record_count DESC 
                                          LIMIT 20";
                        $emp_list_result = mysqli_query($connect, $emp_list_query);
                        while ($emp_row = mysqli_fetch_assoc($emp_list_result)) {
                            echo "<tr>";
                            echo "<td><strong>" . htmlspecialchars($emp_row['employee_ID']) . "</strong></td>";
                            echo "<td>" . htmlspecialchars($emp_row['employee_name'] ?: 'Unknown') . "</td>";
                            echo "<td><span class='badge bg-primary'>" . $emp_row['record_count'] . " records</span></td>";
                            echo "<td><button class='btn btn-sm btn-success' onclick='useEmployeeId(\"" . htmlspecialchars($emp_row['employee_ID']) . "\")'>Use This ID</button></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="text-center mt-3">
                <a href="?" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Report Form
                </a>
            </div>
        </div>
        <?php
    }
    
    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_type'])) {
        // Generate timestamp when report is actually created
        $report_generated_time = date('Y-m-d H:i:s');
        
        $report_type = $_POST['report_type'];
        $from_date = $_POST['from_date'];
        $to_date = $_POST['to_date'];

        // Initialize variables for report data
        $report_records = [];
        $total_employees = 0;
        $total_records = 0;

        // Build query based on report type
        if ($report_type === 'individual') {
            $employee_ID = $_POST['employee_ID'];
            
            // Validate if employee exists
            $emp_check_query = "SELECT employee_name FROM employees WHERE employee_ID = ?";
            $emp_check_stmt = mysqli_prepare($connect, $emp_check_query);
            mysqli_stmt_bind_param($emp_check_stmt, "s", $employee_ID);
            mysqli_stmt_execute($emp_check_stmt);
            $emp_check_result = mysqli_stmt_get_result($emp_check_stmt);
            
            if (mysqli_num_rows($emp_check_result) == 0) {
                // Employee doesn't exist - show helpful error message
                ?>
                <div class="report-container text-center fade-in-up">
                    <div class="py-5">
                        <div class="mb-4">
                            <i class="fas fa-user-slash fa-5x text-danger opacity-50"></i>
                        </div>
                        <h3 class="text-danger mb-3">Employee ID Not Found</h3>
                        <p class="text-muted mb-4">
                            Employee ID <strong><?php echo htmlspecialchars($employee_ID); ?></strong> does not exist in the system.<br>
                            Please verify the employee ID and try again.
                        </p>
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-lightbulb me-2"></i>Suggestions:</h6>
                                    <ul class="mb-0 text-start">
                                        <li>Double-check the employee ID format</li>
                                        <li>Try these working employee IDs: <strong>540567</strong>, <strong>471193</strong>, <strong>416032</strong></li>
                                        <li>Use "Group Report" to see all employees</li>
                                        <li>Contact your administrator to verify the correct employee ID</li>
                                    </ul>
                                </div>
                                <a href="?show_employees=1" class="btn btn-info me-2">
                                    <i class="fas fa-list me-2"></i>Show Valid Employee IDs
                                </a>
                                <a href="#reportForm" class="btn btn-secondary" onclick="document.getElementById('employee_ID').focus();">
                                    <i class="fas fa-edit me-2"></i>Try Different ID
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                mysqli_stmt_close($emp_check_stmt);
                // Exit early to prevent further processing
                echo '</div></body></html>';
                exit();
            }
            
            mysqli_stmt_close($emp_check_stmt);
            
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
            mysqli_stmt_bind_param($stmt, "sss", $employee_ID, $from_date, $to_date);
        } else {
            // Group report
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
            
            $stmt = mysqli_prepare($connect, $sql);
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        // Process results for display
        $attendance_data = [];
        while ($row = mysqli_fetch_assoc($result)) {
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
                // Set first check-in if not set or if this time is earlier
                if ($attendance_data[$key]['first_check_in'] === null || 
                    $row['time_'] < $attendance_data[$key]['first_check_in']) {
                    $attendance_data[$key]['first_check_in'] = $row['time_'];
                    $attendance_data[$key]['first_check_in_device'] = $row['fingerprint_device'];
                }
            } else {
                $attendance_data[$key]['check_out_times'][] = $row['time_'];
                $attendance_data[$key]['check_out_devices'][] = $row['fingerprint_device'];
                // Set last check-out if not set or if this time is later
                if ($attendance_data[$key]['last_check_out'] === null || 
                    $row['time_'] > $attendance_data[$key]['last_check_out']) {
                    $attendance_data[$key]['last_check_out'] = $row['time_'];
                    $attendance_data[$key]['last_check_out_device'] = $row['fingerprint_device'];
                }
            }
        }

        $total_records = count($attendance_data);
        $total_employees = count(array_unique(array_column($attendance_data, 'employee_ID')));
        
        // Calculate additional statistics
        $total_check_ins = 0;
        $total_check_outs = 0;
        $total_working_hours = 0;
        $complete_attendance_days = 0;
        $unique_devices = [];
        
        foreach ($attendance_data as $record) {
            $total_check_ins += count($record['check_in_times']);
            $total_check_outs += count($record['check_out_times']);
            
            // Collect unique devices
            $all_devices = array_merge($record['check_in_devices'], $record['check_out_devices']);
            foreach ($all_devices as $device) {
                $unique_devices[$device] = true;
            }
            
            // Calculate working hours for complete attendance (has both first check-in and last check-out)
            if ($record['first_check_in'] && $record['last_check_out']) {
                $check_in_time = strtotime($record['first_check_in']);
                $check_out_time = strtotime($record['last_check_out']);
                $working_hours = ($check_out_time - $check_in_time) / 3600; // Convert to hours
                $total_working_hours += $working_hours;
                $complete_attendance_days++;
            }
        }
        
        $average_working_hours = $complete_attendance_days > 0 ? round($total_working_hours / $complete_attendance_days, 2) : 0;
        $total_devices_used = count($unique_devices);

        // Sort attendance data by employee ID in ascending order
        uasort($attendance_data, function($a, $b) {
            return strcmp($a['employee_ID'], $b['employee_ID']);
        });

        // Get division and section names for group reports
        $division_name = '';
        $section_name = '';
        if ($report_type === 'group') {
            if (!empty($division)) {
                $div_query = "SELECT division_name FROM divisions WHERE division_id = ?";
                $div_stmt = mysqli_prepare($connect, $div_query);
                mysqli_stmt_bind_param($div_stmt, "s", $division);
                mysqli_stmt_execute($div_stmt);
                $div_result = mysqli_stmt_get_result($div_stmt);
                if ($div_row = mysqli_fetch_assoc($div_result)) {
                    $division_name = $div_row['division_name'];
                }
                mysqli_stmt_close($div_stmt);
            }
            
            if (!empty($section) && $section != 'all') {
                $sec_query = "SELECT section_name FROM sections WHERE section_id = ?";
                $sec_stmt = mysqli_prepare($connect, $sec_query);
                mysqli_stmt_bind_param($sec_stmt, "s", $section);
                mysqli_stmt_execute($sec_stmt);
                $sec_result = mysqli_stmt_get_result($sec_stmt);
                if ($sec_row = mysqli_fetch_assoc($sec_result)) {
                    $section_name = $sec_row['section_name'];
                }
                mysqli_stmt_close($sec_stmt);
            } else if ($section == 'all') {
                $section_name = 'All Sections';
            }
        }

        if ($total_records > 0) {
            ?>
            <!-- Report Results Section -->
            <div class="report-container fade-in-up">
                <?php if ($report_type === 'group' && (!empty($division_name) || !empty($section_name))): ?>
                <!-- Division and Section Header for Group Reports -->
                <div class="text-center mb-4">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="bg-primary text-white p-3 rounded">
                                <h4 class="mb-2">
                                    <i class="fas fa-building me-2"></i>
                                    <?php echo !empty($division_name) ? htmlspecialchars($division_name) : 'All Divisions'; ?>
                                </h4>
                                <h5 class="mb-0">
                                    <i class="fas fa-sitemap me-2"></i>
                                    <?php echo !empty($section_name) ? htmlspecialchars($section_name) : 'All Sections'; ?>
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="summary-card">
                            <h4>Total Records</h4>
                            <div class="number"><?php echo $total_records; ?></div>
                            <small class="text-muted">Attendance days</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card">
                            <h4>Total Employees</h4>
                            <div class="number"><?php echo $total_employees; ?></div>
                            <small class="text-muted">Unique employees</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card">
                            <h4>Total Check-ins</h4>
                            <div class="number"><?php echo $total_check_ins; ?></div>
                            <small class="text-muted">IN scans recorded</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card">
                            <h4>Total Check-outs</h4>
                            <div class="number"><?php echo $total_check_outs; ?></div>
                            <small class="text-muted">OUT scans recorded</small>
                        </div>
                    </div>
                </div>

                <!-- Additional Statistics Row -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="summary-card">
                            <h4>Complete Days</h4>
                            <div class="number"><?php echo $complete_attendance_days; ?></div>
                            <small class="text-muted">Days with both IN & OUT</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card">
                            <h4>Avg. Working Hours</h4>
                            <div class="number"><?php echo $average_working_hours; ?>h</div>
                            <small class="text-muted">Per complete day</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card">
                            <h4>Devices Used</h4>
                            <div class="number"><?php echo $total_devices_used; ?></div>
                            <small class="text-muted">Fingerprint machines</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card">
                            <h4>Report Period</h4>
                            <div class="number"><?php echo ceil((strtotime($to_date) - strtotime($from_date)) / (60*60*24)) + 1; ?></div>
                            <small class="text-muted">Days covered</small>
                        </div>
                    </div>
                </div>

                <!-- Report Meta Information -->
                <div class="report-meta">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="mb-2"><i class="fas fa-calendar-alt me-2 text-primary"></i>Report Period</h6>
                                    <p class="mb-0"><strong><?php echo date('M d, Y', strtotime($from_date)); ?></strong> to <strong><?php echo date('M d, Y', strtotime($to_date)); ?></strong></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="mb-2"><i class="fas fa-clock me-2 text-primary"></i>Generated</h6>
                                    <p class="mb-0">
                                        <strong><?php echo date('M d, Y at H:i:s', strtotime($report_generated_time)); ?></strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <h6 class="mb-2"><i class="fas fa-user me-2 text-primary"></i>Generated by</h6>
                            <p class="mb-0"><strong><?php echo htmlspecialchars($current_user); ?></strong></p>
                        </div>
                    </div>
                </div>

                <!-- Export Actions -->
                <div class="text-center mb-4">
                    <button onclick="window.print()" class="btn btn-download">
                        <i class="fas fa-print me-2"></i>Print Report
                    </button>
                </div>

                <!-- Results Table -->
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag me-2"></i>Employee ID</th>
                                    <th><i class="fas fa-user me-2"></i>Employee Name</th>
                                    <th><i class="fas fa-calendar me-2"></i>Date</th>
                                    <th><i class="fas fa-sign-in-alt me-2"></i>First Check In</th>
                                    <th><i class="fas fa-sign-out-alt me-2"></i>Last Check Out</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $row_count = 0;
                                foreach ($attendance_data as $record): 
                                    $row_count++;
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="badge bg-secondary rounded-pill me-2"><?php echo $row_count; ?></div>
                                            <strong><?php echo htmlspecialchars($record['employee_ID']); ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <?php echo strtoupper(substr($record['employee_name'], 0, 1)); ?>
                                            </div>
                                            <?php echo htmlspecialchars($record['employee_name']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo date('M d, Y', strtotime($record['date_'])); ?></div>
                                        <small class="text-muted"><?php echo date('l', strtotime($record['date_'])); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($record['first_check_in']): ?>
                                            <div class="d-flex flex-column">
                                                <span class="status-badge status-in mb-1">
                                                    <i class="fas fa-sign-in-alt me-1"></i>
                                                    <?php echo htmlspecialchars(date('H:i:s', strtotime($record['first_check_in']))); ?>
                                                </span>
                                                <small class="text-muted">
                                                    <i class="fas fa-fingerprint me-1"></i>
                                                    <?php echo htmlspecialchars($record['first_check_in_device']); ?>
                                                </small>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">No check-in</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($record['last_check_out']): ?>
                                            <div class="d-flex flex-column">
                                                <span class="status-badge status-out mb-1">
                                                    <i class="fas fa-sign-out-alt me-1"></i>
                                                    <?php echo htmlspecialchars(date('H:i:s', strtotime($record['last_check_out']))); ?>
                                                </span>
                                                <small class="text-muted">
                                                    <i class="fas fa-fingerprint me-1"></i>
                                                    <?php echo htmlspecialchars($record['last_check_out_device']); ?>
                                                </small>
                                            </div>
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

                <!-- Footer Summary -->
                <div class="mt-4 p-3 bg-light rounded">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <h6 class="text-muted mb-0">Total Attendance Records</h6>
                            <h4 class="text-primary mb-0"><?php echo $total_records; ?></h4>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-0">Unique Employees</h6>
                            <h4 class="text-success mb-0"><?php echo $total_employees; ?></h4>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-0">Report Coverage</h6>
                            <h4 class="text-info mb-0"><?php echo ceil((strtotime($to_date) - strtotime($from_date)) / (60*60*24)) + 1; ?> Days</h4>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        } else {
            ?>
            <div class="report-container text-center fade-in-up">
                <div class="py-5">
                    <div class="mb-4">
                        <i class="fas fa-search fa-5x text-muted opacity-50"></i>
                    </div>
                    <h3 class="text-muted mb-3">No Attendance Records Found</h3>
                    <p class="text-muted mb-4">
                        No attendance records were found for the selected criteria.<br>
                        Please try adjusting your search parameters and generate the report again.
                    </p>
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-lightbulb me-2"></i>Suggestions:</h6>
                                <ul class="mb-0 text-start">
                                    <li>Check if the employee ID is correct</li>
                                    <li>Expand the date range</li>
                                    <li>Try selecting a different division or section</li>
                                    <li>Verify that attendance data exists for the selected period</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        
        $stmt->close();
    }
    ?>

</div>

<!-- JavaScript for form interactions -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Update current time every second
    function updateCurrentTime() {
        const now = new Date();
        
        // Create a date object with Sri Lanka timezone offset (+5:30)
        const sriLankaTime = new Date(now.getTime() + (5.5 * 60 * 60 * 1000));
        
        // Format date components
        const year = sriLankaTime.getUTCFullYear();
        const month = sriLankaTime.toLocaleDateString('en-US', { month: 'short', timeZone: 'UTC' });
        const day = String(sriLankaTime.getUTCDate()).padStart(2, '0');
        const hours = String(sriLankaTime.getUTCHours()).padStart(2, '0');
        const minutes = String(sriLankaTime.getUTCMinutes()).padStart(2, '0');
        const seconds = String(sriLankaTime.getUTCSeconds()).padStart(2, '0');
        
        // Create clean formatted string
        const formattedDateTime = `${month} ${day}, ${year} ${hours}:${minutes}:${seconds}`;
        
        const timeDisplay = document.getElementById('timeDisplay');
        if (timeDisplay) {
            timeDisplay.textContent = formattedDateTime;
        }
    }
    
    // Update time immediately and then every second
    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);

    // Report type change handler
    const reportTypeRadios = document.querySelectorAll('input[name="report_type"]');
    const employeeSection = document.getElementById('employee_id_section');
    const divisionSection = document.getElementById('division_section');
    const sectionSection = document.getElementById('section_section');

    function handleReportTypeChange() {
        const selectedType = document.querySelector('input[name="report_type"]:checked').value;
        
        if (selectedType === 'individual') {
            employeeSection.style.display = 'block';
            divisionSection.style.display = 'none';
            sectionSection.style.display = 'none';
        } else {
            employeeSection.style.display = 'none';
            divisionSection.style.display = 'block';
            sectionSection.style.display = 'block';
        }
    }

    // Attach event listeners
    reportTypeRadios.forEach(radio => {
        radio.addEventListener('change', handleReportTypeChange);
    });

    // Initial call to set correct display
    handleReportTypeChange();

    // Division change handler for sections
    const divisionSelect = document.getElementById('division');
    const sectionSelect = document.getElementById('section');

    if (divisionSelect) {
        divisionSelect.addEventListener('change', function() {
            const divisionId = this.value;
            
            // Clear current sections
            sectionSelect.innerHTML = '<option value="all">All Sections</option>';
            
            if (divisionId) {
                // Add loading option
                sectionSelect.innerHTML += '<option value="">Loading sections...</option>';
                
                // Fetch sections for this division
                fetch('fetch_sections.php?division_id=' + divisionId)
                    .then(response => response.json())
                    .then(data => {
                        sectionSelect.innerHTML = '<option value="all">All Sections</option>';
                        
                        data.forEach(section => {
                            const option = document.createElement('option');
                            option.value = section.section_id;
                            option.textContent = section.section_name;
                            sectionSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching sections:', error);
                        sectionSelect.innerHTML = '<option value="all">All Sections</option>';
                    });
            }
        });
    }

    // Form submission handler
    const reportForm = document.getElementById('reportForm');
    if (reportForm) {
        reportForm.addEventListener('submit', function(e) {
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.innerHTML = '<div class="loading me-2"></div>Generating Report...';
                submitButton.disabled = true;
            }
        });
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Alt + G to generate report
        if (e.altKey && e.key === 'g') {
            e.preventDefault();
            const form = document.getElementById('reportForm');
            if (form) {
                form.submit();
            }
        }
        
        // Escape to scroll to form
        if (e.key === 'Escape') {
            document.querySelector('.form-container').scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
    
    // Function to use selected employee ID
    window.useEmployeeId = function(employeeId) {
        // Set the employee ID in the form
        document.getElementById('employee_ID').value = employeeId;
        
        // Select individual report type
        document.getElementById('individual').checked = true;
        
        // Trigger the report type change event
        document.getElementById('individual').dispatchEvent(new Event('change'));
        
        // Scroll to form
        document.querySelector('.form-container').scrollIntoView({ 
            behavior: 'smooth',
            block: 'start'
        });
        
        // Focus on the employee ID field
        setTimeout(() => {
            document.getElementById('employee_ID').focus();
            document.getElementById('employee_ID').select();
        }, 500);
    };
});
</script>

</body>
</html>
