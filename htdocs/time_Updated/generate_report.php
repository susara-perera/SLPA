<?php
session_start();
include('./dbc.php');

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get current user info
$current_user = isset($_SESSION['username']) ? $_SESSION['username'] : 'SLPA User';
$current_time = date('Y-m-d H:i:s');
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
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #f8f9fa;
            --card-shadow: 0 8px 32px rgba(0,0,0,0.12);
            --border-radius: 12px;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        /* Header Styling */
        .report-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #34495e 50%, var(--primary-color) 100%);
            color: white;
            padding: 25px 35px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            box-shadow: var(--card-shadow);
            border-bottom: 4px solid var(--secondary-color);
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
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            pointer-events: none;
        }

        .slpa-logo {
            width: 65px;
            height: 65px;
            background: rgba(255,255,255,0.95);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-weight: bold;
            font-size: 26px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
        }

        /* Form Container */
        .form-container {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            padding: 35px;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            margin-bottom: 25px;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .form-section-title {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 3px solid var(--secondary-color);
            position: relative;
        }

        .form-section-title::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--success-color);
        }

        /* Form Controls */
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 14px 18px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.9);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.15);
            background: white;
            transform: translateY(-1px);
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .container-fluid {
                margin-left: 0;
                width: 100%;
                padding: 15px;
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

        /* Avatar styling */
        .avatar-sm {
            width: 32px;
            height: 32px;
            font-size: 14px;
            font-weight: 600;
        }

        /* Enhanced table styling */
        .table tbody tr {
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .table tbody tr:last-child {
            border-bottom: none;
        }

        /* Print styles */
        @media print {
            .back-button, .btn-download, .header-controls {
                display: none !important;
            }
            
            .container-fluid {
                margin-left: 0 !important;
                width: 100% !important;
                padding: 0 !important;
            }
            
            .report-header {
                background: #2c3e50 !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
                margin-bottom: 20px !important;
            }

            .form-container {
                display: none !important;
            }

            .report-container {
                box-shadow: none !important;
                border: none !important;
                padding: 20px !important;
                margin: 0 !important;
            }

            .summary-card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }

            .table-container {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }

            body {
                background: white !important;
            }

            .report-meta {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
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
    </style>
</head>
<body style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh;">

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
                           data-bs-toggle="tooltip" title="Select the start date for the report">
                </div>

                <div class="col-md-6 mb-4">
                    <label for="to_date" class="form-label">
                        <i class="fas fa-calendar-alt me-2"></i>To Date
                    </label>
                    <input type="date" class="form-control" id="to_date" name="to_date" required
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
    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_type'])) {
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
            $sql = "SELECT a.employee_ID, 
                           COALESCE(e.employee_name, 'Unknown Employee') as employee_name, 
                           COALESCE(d.division_name, 'Unknown Division') as division_name, 
                           COALESCE(s.section_name, 'Unknown Section') as section_name, 
                           a.date_, a.scan_type, a.time_
                    FROM attendance a
                    LEFT JOIN employees e ON a.employee_ID = e.employee_ID
                    LEFT JOIN divisions d ON e.division = d.division_id
                    LEFT JOIN sections s ON e.section = s.section_id
                    WHERE a.employee_ID = ? AND a.date_ BETWEEN ? AND ?
                    ORDER BY a.date_, a.time_";
            
            $stmt = $connect->prepare($sql);
            $stmt->bind_param("sss", $employee_ID, $from_date, $to_date);
        } else {
            // Group report
            $division = $_POST['division'];
            $section = $_POST['section'];
            
            $sql = "SELECT a.employee_ID, 
                           COALESCE(e.employee_name, 'Unknown Employee') as employee_name, 
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
            
            $sql .= " ORDER BY a.date_, a.time_";
            
            $stmt = $connect->prepare($sql);
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        // Process results for display
        $attendance_data = [];
        while ($row = $result->fetch_assoc()) {
            $key = $row['employee_ID'] . '_' . $row['date_'];
            
            if (!isset($attendance_data[$key])) {
                $attendance_data[$key] = [
                    'employee_ID' => $row['employee_ID'],
                    'employee_name' => $row['employee_name'],
                    'division_name' => $row['division_name'],
                    'section_name' => $row['section_name'],
                    'date_' => $row['date_'],
                    'ontime' => [],
                    'offtime' => []
                ];
            }
            
            if ($row['scan_type'] === 'IN') {
                $attendance_data[$key]['ontime'][] = $row['time_'];
            } else {
                $attendance_data[$key]['offtime'][] = $row['time_'];
            }
        }

        $total_records = count($attendance_data);
        $total_employees = count(array_unique(array_column($attendance_data, 'employee_ID')));

        if ($total_records > 0) {
            ?>
            <!-- Report Results Section -->
            <div class="report-container fade-in-up">
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="summary-card">
                            <h4>Total Records</h4>
                            <div class="number"><?php echo $total_records; ?></div>
                            <small class="text-muted">Attendance entries</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card">
                            <h4>Employees</h4>
                            <div class="number"><?php echo $total_employees; ?></div>
                            <small class="text-muted">Total employees</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card">
                            <h4>Report Type</h4>
                            <div class="number">
                                <?php echo $report_type === 'individual' ? '<i class="fas fa-user"></i>' : '<i class="fas fa-users"></i>'; ?>
                            </div>
                            <small class="text-muted"><?php echo ucfirst($report_type); ?></small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card">
                            <h4>Period</h4>
                            <div class="number"><?php echo ceil((strtotime($to_date) - strtotime($from_date)) / (60*60*24)) + 1; ?></div>
                            <small class="text-muted">Days</small>
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
                                    <p class="mb-0"><?php echo date('M d, Y \a\t H:i:s', strtotime($current_time)); ?></p>
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
                                    <th><i class="fas fa-building me-2"></i>Division</th>
                                    <th><i class="fas fa-sitemap me-2"></i>Section</th>
                                    <th><i class="fas fa-calendar me-2"></i>Date</th>
                                    <th><i class="fas fa-sign-in-alt me-2"></i>Check In</th>
                                    <th><i class="fas fa-sign-out-alt me-2"></i>Check Out</th>
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
                                    <td><?php echo htmlspecialchars($record['division_name']); ?></td>
                                    <td><?php echo htmlspecialchars($record['section_name']); ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo date('M d, Y', strtotime($record['date_'])); ?></div>
                                        <small class="text-muted"><?php echo date('l', strtotime($record['date_'])); ?></small>
                                    </td>
                                    <td>
                                        <?php if (!empty($record['ontime'])): ?>
                                            <?php foreach ($record['ontime'] as $time): ?>
                                                <span class="status-badge status-in d-block mb-1">
                                                    <i class="fas fa-sign-in-alt me-1"></i><?php echo htmlspecialchars($time); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted">No check-in</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($record['offtime'])): ?>
                                            <?php foreach ($record['offtime'] as $time): ?>
                                                <span class="status-badge status-out d-block mb-1">
                                                    <i class="fas fa-sign-out-alt me-1"></i><?php echo htmlspecialchars($time); ?>
                                                </span>
                                            <?php endforeach; ?>
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
        const options = { 
            year: 'numeric', 
            month: 'short', 
            day: '2-digit', 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit',
            hour12: false
        };
        const timeString = now.toLocaleDateString('en-US', options).replace(',', '');
        const timeDisplay = document.getElementById('timeDisplay');
        if (timeDisplay) {
            timeDisplay.textContent = timeString;
        }
    }
    
    // Update time immediately and then every second
    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);

    // Set default dates (last 30 days)
    const today = new Date();
    const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
    
    const toDateInput = document.getElementById('to_date');
    const fromDateInput = document.getElementById('from_date');
    
    if (toDateInput && fromDateInput) {
        toDateInput.value = today.toISOString().split('T')[0];
        fromDateInput.value = thirtyDaysAgo.toISOString().split('T')[0];
    }

    // Handle report type change with smooth animations
    const reportTypeInputs = document.querySelectorAll('input[name="report_type"]');
    reportTypeInputs.forEach(input => {
        input.addEventListener('change', function() {
            const employeeSection = document.getElementById('employee_id_section');
            const divisionSection = document.getElementById('division_section');
            const sectionSection = document.getElementById('section_section');
            const employeeIdInput = document.getElementById('employee_ID');
            const divisionInput = document.getElementById('division');
            
            if (this.value === 'individual') {
                // Show employee section, hide division/section
                employeeSection.style.display = 'block';
                divisionSection.style.display = 'none';
                sectionSection.style.display = 'none';
                
                if (employeeIdInput) employeeIdInput.required = true;
                if (divisionInput) divisionInput.required = false;
                
                // Clear group form fields
                if (divisionInput) divisionInput.value = '';
                const sectionSelect = document.getElementById('section');
                if (sectionSelect) sectionSelect.innerHTML = '<option value="all">All Sections</option>';
            } else {
                // Show division/section, hide employee
                employeeSection.style.display = 'none';
                divisionSection.style.display = 'block';
                sectionSection.style.display = 'block';
                
                if (employeeIdInput) employeeIdInput.required = false;
                if (divisionInput) divisionInput.required = true;
                
                // Clear individual form field
                if (employeeIdInput) employeeIdInput.value = '';
            }
        });
    });

    // Handle division change to load sections with loading indicator
    const divisionSelect = document.getElementById('division');
    if (divisionSelect) {
        divisionSelect.addEventListener('change', function() {
            const divisionId = this.value;
            const sectionSelect = document.getElementById('section');
            
            if (!sectionSelect) return;
            
            // Show loading state
            sectionSelect.innerHTML = '<option value="">Loading sections...</option>';
            sectionSelect.disabled = true;
            
            if (divisionId) {
                // Fetch sections for selected division
                fetch('fetch_sections.php?division_id=' + encodeURIComponent(divisionId))
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Clear current options
                        sectionSelect.innerHTML = '<option value="all">All Sections</option>';
                        
                        // Add fetched sections
                        if (Array.isArray(data)) {
                            data.forEach(section => {
                                const option = document.createElement('option');
                                option.value = section.section_id;
                                option.textContent = section.section_name;
                                sectionSelect.appendChild(option);
                            });
                        }
                        
                        sectionSelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error fetching sections:', error);
                        sectionSelect.innerHTML = '<option value="all">All Sections</option>';
                        sectionSelect.disabled = false;
                    });
            } else {
                // Reset to default if no division selected
                sectionSelect.innerHTML = '<option value="all">All Sections</option>';
                sectionSelect.disabled = false;
            }
        });
    }

    // Form validation
    const reportForm = document.getElementById('reportForm');
    if (reportForm) {
        reportForm.addEventListener('submit', function(e) {
            const reportType = document.querySelector('input[name="report_type"]:checked').value;
            const fromDate = document.getElementById('from_date').value;
            const toDate = document.getElementById('to_date').value;
            
            // Validate date range
            if (new Date(fromDate) > new Date(toDate)) {
                e.preventDefault();
                alert('From date cannot be later than To date. Please check your date selection.');
                return false;
            }
            
            // Validate date range (not more than 1 year)
            const daysDiff = (new Date(toDate) - new Date(fromDate)) / (1000 * 60 * 60 * 24);
            if (daysDiff > 365) {
                e.preventDefault();
                alert('Date range cannot exceed 365 days. Please select a shorter period.');
                return false;
            }
            
            // Validate individual report fields
            if (reportType === 'individual') {
                const employeeId = document.getElementById('employee_ID').value;
                if (!employeeId.trim()) {
                    e.preventDefault();
                    alert('Please enter an Employee ID for individual reports.');
                    return false;
                }
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="loading me-2"></span>Generating Report...';
                submitBtn.disabled = true;
                
                // Re-enable button after 5 seconds (in case of issues)
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 5000);
            }
        });
    }

    // Smooth scroll to results
    const urlParams = new URLSearchParams(window.location.search);
    if (document.querySelector('.report-container') && window.location.hash !== '#form') {
        setTimeout(() => {
            document.querySelector('.report-container').scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        }, 500);
    }

    // Enhanced print functionality for PDF generation
    window.printReport = function() {
        // Hide unnecessary elements before printing
        const elementsToHide = document.querySelectorAll('.btn-download, .back-button, .form-container');
        elementsToHide.forEach(el => el.style.display = 'none');
        
        // Add print-specific styles for better PDF output
        const printStyle = document.createElement('style');
        printStyle.innerHTML = `
            @page {
                size: A4;
                margin: 0.5in;
            }
            @media print {
                body { 
                    font-size: 12px !important; 
                    background: white !important;
                    color: black !important;
                }
                .report-header {
                    background: #2c3e50 !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                    color: white !important;
                }
                .slpa-logo {
                    background: white !important;
                    color: #2c3e50 !important;
                }
                .summary-card, .table-container, .report-meta {
                    border: 1px solid #ddd !important;
                    box-shadow: none !important;
                    break-inside: avoid;
                }
                .table thead th {
                    background: #2c3e50 !important;
                    color: white !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
                .status-badge {
                    border: 1px solid #ccc !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
                .status-in {
                    background: #d4edda !important;
                    color: #155724 !important;
                }
                .status-out {
                    background: #f8d7da !important;
                    color: #721c24 !important;
                }
            }
        `;
        document.head.appendChild(printStyle);
        
        // Set page title for PDF filename
        const originalTitle = document.title;
        document.title = 'SLPA_Attendance_Report_' + new Date().toISOString().split('T')[0];
        
        window.print();
        
        // Restore elements and title after printing
        setTimeout(() => {
            elementsToHide.forEach(el => el.style.display = '');
            document.head.removeChild(printStyle);
            document.title = originalTitle;
        }, 1000);
    };

    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + P for print
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
            if (document.querySelector('.report-container')) {
                e.preventDefault();
                window.printReport();
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
});
</script>

</body>
</html>
