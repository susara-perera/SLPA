<?php
include('./dbc.php');
include('includes/header.php');
include('includes/navbar.php');
include('includes/check_access.php');

// Set timezone for Sri Lanka
date_default_timezone_set('Asia/Colombo');

$page = 'audit.php';

// Check if the user has access to this page
if (!hasAccess($page)) {
    echo "<div class='container'><div class='row mx-md-n8'><div class='col px-md-5'><h1>Access Denied</h1><p>You do not have permission to access this page.</p></div></div></div>";
    include('includes/scripts.php');
    include('includes/footer.php');
    exit();
}

// Fetch divisions from the database
$divisions = [];
$divisionSql = "SELECT division_id, division_name FROM divisions";
$result = mysqli_query($connect, $divisionSql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $divisions[] = $row;
    }
} else {
    $error_message = "Failed to fetch divisions: " . mysqli_error($connect);
}

// Debug: Check if we have divisions
if (empty($divisions)) {
    $error_message = "No divisions found in database. Please check your database connection and data.";
}

// Handle form submission for generating report
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $division_id = $_POST['division'] ?? '';
    $date_from = $_POST['date_from'] ?? '';
    $date_to = $_POST['date_to'] ?? '';

    // Debug: Log form submission
    error_log("Audit form submitted: Division=$division_id, From=$date_from, To=$date_to");

    // Validation for date range and division
    if (!empty($date_from) && !empty($date_to) && !empty($division_id)) {
        // Fetch division name
        if ($division_id === 'all') {
            $division_name = "All Divisions";
        } else {
            $divisionNameSql = "SELECT division_name FROM divisions WHERE division_id = ?";
            $stmt = mysqli_prepare($connect, $divisionNameSql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $division_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $division_name);
                mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);
            } else {
                $error_message = "SQL error: " . mysqli_error($connect);
            }
        }

        // Enhanced SQL query for comprehensive report data
        if ($division_id === 'all') {
            // Get detailed attendance data for all divisions
            $reportSql = "
                SELECT 
                    d.division_name,
                    COUNT(DISTINCT e.employee_ID) as total_employees,
                    COUNT(DISTINCT CASE 
                        WHEN a.scan_type = 'IN' THEN a.employee_ID 
                    END) as employees_with_attendance,
                    COUNT(CASE WHEN a.scan_type IS NOT NULL THEN 1 END) as total_attendance_records,
                    AVG(CASE 
                        WHEN in_times.earliest_in IS NOT NULL AND out_times.latest_out IS NOT NULL 
                        THEN TIMESTAMPDIFF(HOUR, in_times.earliest_in, out_times.latest_out) 
                        ELSE NULL 
                    END) as avg_working_hours,
                    COUNT(DISTINCT CASE 
                        WHEN a.scan_type = 'IN' AND TIME(a.time_) <= '08:30:00' 
                        THEN a.employee_ID 
                    END) as on_time_employees,
                    COUNT(DISTINCT CASE 
                        WHEN a.scan_type = 'IN' AND TIME(a.time_) > '08:30:00' 
                        THEN a.employee_ID 
                    END) as late_employees
                FROM divisions d
                LEFT JOIN employees e ON d.division_id = e.division AND e.status = 'active'
                LEFT JOIN attendance a ON e.employee_ID = a.employee_ID 
                    AND a.date_ BETWEEN ? AND ?
                LEFT JOIN (
                    SELECT employee_ID, date_, MIN(time_) as earliest_in
                    FROM attendance 
                    WHERE scan_type = 'IN' AND date_ BETWEEN ? AND ?
                    GROUP BY employee_ID, date_
                ) in_times ON a.employee_ID = in_times.employee_ID AND a.date_ = in_times.date_
                LEFT JOIN (
                    SELECT employee_ID, date_, MAX(time_) as latest_out
                    FROM attendance 
                    WHERE scan_type = 'OUT' AND date_ BETWEEN ? AND ?
                    GROUP BY employee_ID, date_
                ) out_times ON a.employee_ID = out_times.employee_ID AND a.date_ = out_times.date_
                GROUP BY d.division_id, d.division_name
                ORDER BY d.division_name";

            $stmt = mysqli_prepare($connect, $reportSql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssssss", $date_from, $date_to, $date_from, $date_to, $date_from, $date_to);
                mysqli_stmt_execute($stmt);
                $report_result = mysqli_stmt_get_result($stmt);
                $detailed_report = mysqli_fetch_all($report_result, MYSQLI_ASSOC);
                mysqli_stmt_close($stmt);
                
                // Debug: Check if we got results
                error_log("All Divisions Query Results: " . count($detailed_report) . " rows returned");
            } else {
                $error_message = "SQL preparation error (All Divisions): " . mysqli_error($connect);
                error_log("SQL preparation error: " . mysqli_error($connect));
            }
        } else {
            // Get detailed attendance data for specific division
            $reportSql = "
                SELECT 
                    d.division_name,
                    COUNT(DISTINCT e.employee_ID) as total_employees,
                    COUNT(DISTINCT CASE 
                        WHEN a.scan_type = 'IN' THEN a.employee_ID 
                    END) as employees_with_attendance,
                    COUNT(CASE WHEN a.scan_type IS NOT NULL THEN 1 END) as total_attendance_records,
                    AVG(CASE 
                        WHEN in_times.earliest_in IS NOT NULL AND out_times.latest_out IS NOT NULL 
                        THEN TIMESTAMPDIFF(HOUR, in_times.earliest_in, out_times.latest_out) 
                        ELSE NULL 
                    END) as avg_working_hours,
                    COUNT(DISTINCT CASE 
                        WHEN a.scan_type = 'IN' AND TIME(a.time_) <= '08:30:00' 
                        THEN a.employee_ID 
                    END) as on_time_employees,
                    COUNT(DISTINCT CASE 
                        WHEN a.scan_type = 'IN' AND TIME(a.time_) > '08:30:00' 
                        THEN a.employee_ID 
                    END) as late_employees
                FROM divisions d
                LEFT JOIN employees e ON d.division_id = e.division AND e.status = 'active'
                LEFT JOIN attendance a ON e.employee_ID = a.employee_ID 
                    AND a.date_ BETWEEN ? AND ?
                LEFT JOIN (
                    SELECT employee_ID, date_, MIN(time_) as earliest_in
                    FROM attendance 
                    WHERE scan_type = 'IN' AND date_ BETWEEN ? AND ?
                    GROUP BY employee_ID, date_
                ) in_times ON a.employee_ID = in_times.employee_ID AND a.date_ = in_times.date_
                LEFT JOIN (
                    SELECT employee_ID, date_, MAX(time_) as latest_out
                    FROM attendance 
                    WHERE scan_type = 'OUT' AND date_ BETWEEN ? AND ?
                    GROUP BY employee_ID, date_
                ) out_times ON a.employee_ID = out_times.employee_ID AND a.date_ = out_times.date_
                WHERE d.division_id = ?
                GROUP BY d.division_id, d.division_name";

            $stmt = mysqli_prepare($connect, $reportSql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sssssss", $date_from, $date_to, $date_from, $date_to, $date_from, $date_to, $division_id);
                mysqli_stmt_execute($stmt);
                $report_result = mysqli_stmt_get_result($stmt);
                $detailed_report = mysqli_fetch_all($report_result, MYSQLI_ASSOC);
                mysqli_stmt_close($stmt);
                
                // Debug: Check if we got results
                error_log("Specific Division Query Results: " . count($detailed_report) . " rows returned");
            } else {
                $error_message = "SQL preparation error (Specific Division): " . mysqli_error($connect);
                error_log("SQL preparation error: " . mysqli_error($connect));
            }
        }

        // Get daily attendance summary
        $dailySql = "
            SELECT 
                DATE(a.date_) as attendance_date,
                COUNT(DISTINCT CASE WHEN a.scan_type = 'IN' THEN a.employee_ID END) as daily_attendance,
                COUNT(DISTINCT CASE 
                    WHEN a.scan_type = 'IN' AND TIME(a.time_) <= '08:30:00' 
                    THEN a.employee_ID 
                END) as on_time_count,
                COUNT(DISTINCT CASE 
                    WHEN a.scan_type = 'IN' AND TIME(a.time_) > '08:30:00' 
                    THEN a.employee_ID 
                END) as late_count
            FROM attendance a
            INNER JOIN employees e ON a.employee_ID = e.employee_ID
            WHERE a.date_ BETWEEN ? AND ?
            " . ($division_id !== 'all' ? "AND e.division = ?" : "") . "
            AND e.status = 'active'
            AND a.scan_type = 'IN'
            GROUP BY DATE(a.date_)
            ORDER BY attendance_date DESC";

        $stmt = mysqli_prepare($connect, $dailySql);
        if ($stmt) {
            if ($division_id !== 'all') {
                mysqli_stmt_bind_param($stmt, "sss", $date_from, $date_to, $division_id);
            } else {
                mysqli_stmt_bind_param($stmt, "ss", $date_from, $date_to);
            }
            mysqli_stmt_execute($stmt);
            $daily_result = mysqli_stmt_get_result($stmt);
            $daily_summary = mysqli_fetch_all($daily_result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
        }

        if (!isset($stmt) || !$stmt) {
            $error_message = "SQL error: " . mysqli_error($connect);
        } else {
            // Debug: Final check
            if (isset($detailed_report)) {
                error_log("Final detailed_report count: " . count($detailed_report));
            } else {
                error_log("detailed_report is not set");
                $error_message = "No data returned from query";
            }
        }
    } else {
        $error_message = "All fields are required.";
    }
}
?>

<style>
.audit-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px 0;
    margin-bottom: 30px;
    border-radius: 10px;
}

.report-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-bottom: 25px;
    border-left: 5px solid #667eea;
}

.stat-card {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    margin-bottom: 15px;
}

.stat-card.success {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.stat-card.warning {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.stat-card.danger {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.form-container {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.btn-generate {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 25px;
    padding: 12px 30px;
    color: white;
    font-weight: bold;
    transition: all 0.3s ease;
}

.btn-generate:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    color: white;
}

.table-professional {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.table-professional thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.table-professional th, .table-professional td {
    padding: 15px;
    border: none;
    vertical-align: middle;
}

.table-professional tbody tr:nth-child(even) {
    background-color: #f8f9fa;
}

.table-professional tbody tr:hover {
    background-color: #e3f2fd;
    transition: all 0.3s ease;
}

.progress-bar-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    height: 25px !important;
    border-radius: 4px;
    color: white !important;
    font-weight: bold !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 12px !important;
    min-width: 50px;
}

.progress {
    background-color: #e9ecef !important;
    border-radius: 4px !important;
    overflow: hidden !important;
    height: 25px !important;
}

.badge-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.85em;
}

.daily-summary-card {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 10px;
}

/* Force visibility for debugging */
.container-fluid, .report-card, .form-container {
    visibility: visible !important;
    display: block !important;
    opacity: 1 !important;
}

/* Print Styles - SIMPLIFIED FOR GUARANTEED VISIBILITY */
@media print {
    * {
        visibility: visible !important;
        display: block !important;
        background: white !important;
        color: black !important;
    }
    
    body { 
        background: white !important;
        margin: 0 !important;
        padding: 10px !important;
        font-family: Arial, sans-serif !important;
    }
    
    /* Hide only specific elements */
    .form-container, .btn-generate, .btn-outline-primary, 
    .alert-success, .col-md-4, nav, .navbar {
        display: none !important;
    }
    
    /* Force all content to be visible */
    .container-fluid, .report-card, .stat-card, .table-professional,
    .audit-header, h1, h2, h3, h4, p, div, span, td, th {
        display: block !important;
        visibility: visible !important;
        background: white !important;
        color: black !important;
        border: 1px solid black !important;
        margin: 5px 0 !important;
        padding: 5px !important;
    }
    
    /* Table specific */
    table, tr, td, th {
        display: table !important;
        border: 1px solid black !important;
        background: white !important;
        color: black !important;
    }
    
    tr { display: table-row !important; }
    td, th { display: table-cell !important; }
    
    /* Make layout single column */
    .col-md-8 {
        width: 100% !important;
        float: none !important;
        display: block !important;
    }
    
    .row {
        display: block !important;
    }
    
    .col-md-3 {
        width: 24% !important;
        float: left !important;
        display: block !important;
    }
}
</style>

<div class="container-fluid">
    <div class="audit-header text-center">
        <h1><i class="fas fa-chart-line"></i> Professional Audit Report</h1>
        <p class="mb-0">Comprehensive Attendance Analysis & Statistics</p>
    </div>
</div>

<

<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="form-container">
                <h4 class="mb-4"><i class="fas fa-filter"></i> Report Filters</h4>
                
               
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group mb-3">
                        <label for="division" class="form-label"><i class="fas fa-building"></i> Division:</label>
                        <select name="division" id="division" class="form-select" required>
                            <option value="" disabled selected>Choose Division</option>
                            <option value="all">All Divisions</option>
                            <?php foreach ($divisions as $division): ?>
                                <option value="<?php echo htmlspecialchars($division['division_id']); ?>" 
                                    <?php echo (isset($division_id) && $division_id == $division['division_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($division['division_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="date_from" class="form-label"><i class="fas fa-calendar-alt"></i> From Date:</label>
                        <input type="date" id="date_from" name="date_from" class="form-control" 
                               value="<?php echo isset($date_from) ? $date_from : ''; ?>" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="date_to" class="form-label"><i class="fas fa-calendar-alt"></i> To Date:</label>
                        <input type="date" id="date_to" name="date_to" class="form-control" 
                               value="<?php echo isset($date_to) ? $date_to : ''; ?>" 
                               max="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <button type="submit" class="btn btn-generate w-100">
                        <i class="fas fa-chart-bar"></i> Generate Professional Report
                    </button>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <?php if (isset($detailed_report) && !empty($detailed_report)): ?>
                <!-- Summary Statistics -->
                <div class="row mb-4">
                    <?php 
                    $total_employees = array_sum(array_column($detailed_report, 'total_employees'));
                    $total_with_attendance = array_sum(array_column($detailed_report, 'employees_with_attendance'));
                    $total_on_time = array_sum(array_column($detailed_report, 'on_time_employees'));
                    $total_late = array_sum(array_column($detailed_report, 'late_employees'));
                    $attendance_percentage = $total_employees > 0 ? round(($total_with_attendance / $total_employees) * 100, 1) : 0;
                    ?>
                    
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h3><?php echo $total_employees; ?></h3>
                            <p><i class="fas fa-users"></i> Total Employees</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card success">
                            <h3><?php echo $total_with_attendance; ?></h3>
                            <p><i class="fas fa-user-check"></i> Active Attendance</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card warning">
                            <h3><?php echo $total_on_time; ?></h3>
                            <p><i class="fas fa-clock"></i> On Time</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card danger">
                            <h3><?php echo $total_late; ?></h3>
                            <p><i class="fas fa-exclamation-triangle"></i> Late Arrivals</p>
                        </div>
                    </div>
                </div>

                <!-- Report Header -->
                <div class="report-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3><i class="fas fa-chart-pie"></i> Detailed Audit Report</h3>
                            <p class="text-muted mb-0">
                                <strong>Period:</strong> <?php echo date('M d, Y', strtotime($date_from)); ?> - <?php echo date('M d, Y', strtotime($date_to)); ?><br>
                                <strong>Generated:</strong> <?php echo date('M d, Y H:i:s'); ?><br>
                                <strong>Attendance Rate:</strong> <span class="badge-custom"><?php echo $attendance_percentage; ?>%</span>
                            </p>
                        </div>
                        <div class="text-end">
                            <button class="btn btn-outline-primary" onclick="window.print()">
                                <i class="fas fa-print"></i> Print Report
                            </button>
                        </div>
                    </div>

                    <!-- Division Summary Table -->
                    <div class="table-responsive">
                        <table class="table table-professional">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-building"></i> Division</th>
                                    <th><i class="fas fa-users"></i> Total Employees</th>
                                    <th><i class="fas fa-user-check"></i> Active Attendance</th>
                                    <th><i class="fas fa-percentage"></i> Attendance Rate</th>
                                    <th><i class="fas fa-clock"></i> On Time</th>
                                    <th><i class="fas fa-exclamation-triangle"></i> Late</th>
                                    <th><i class="fas fa-hourglass-half"></i> Avg Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detailed_report as $row): ?>
                                    <?php 
                                    $rate = $row['total_employees'] > 0 ? round(($row['employees_with_attendance'] / $row['total_employees']) * 100, 1) : 0;
                                    $avg_hours = $row['avg_working_hours'] ? round($row['avg_working_hours'], 1) : 0;
                                    ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($row['division_name']); ?></strong></td>
                                        <td><?php echo $row['total_employees']; ?></td>
                                        <td><?php echo $row['employees_with_attendance']; ?></td>
                                        <td>
                                            <?php if ($rate > 0): ?>
                                                <div class="progress">
                                                    <div class="progress-bar progress-bar-custom" style="width: <?php echo max($rate, 5); ?>%;" role="progressbar" aria-valuenow="<?php echo $rate; ?>" aria-valuemin="0" aria-valuemax="100">
                                                        <?php echo $rate; ?>%
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">0%</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-success"><?php echo $row['on_time_employees']; ?></span></td>
                                        <td><span class="badge bg-warning"><?php echo $row['late_employees']; ?></span></td>
                                        <td><?php echo $avg_hours; ?>h</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

               
                              <!-- Daily Summary -->
                <?php if (isset($daily_summary) && !empty($daily_summary)): ?>
                    <div class="report-card">
                        <h4><i class="fas fa-calendar-day"></i> Daily Attendance Summary</h4>
                        <p class="text-muted">Daily breakdown of attendance patterns</p>
                        
                        <div class="row">
                            <?php foreach (array_slice($daily_summary, 0, 10) as $daily): ?>
                                <div class="col-md-6 mb-2">
                                    <div class="daily-summary-card">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo date('M d, Y', strtotime($daily['attendance_date'])); ?></strong><br>
                                                <small>Total: <?php echo $daily['daily_attendance']; ?> employees</small>
                                            </div>
                                            <div class="text-end">
                                                <small>On Time: <strong><?php echo $daily['on_time_count']; ?></strong></small><br>
                                                <small>Late: <strong><?php echo $daily['late_count']; ?></strong></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                                <?php endif; ?>
            <?php else: ?>
                <!-- Default Welcome Message -->
                <div class="report-card text-center">
                    <div class="py-5">
                        <i class="fas fa-chart-bar fa-5x text-muted mb-4"></i>
                        <h3 class="text-muted">Generate Your Audit Report</h3>
                        <p class="text-muted">Select a division and date range from the form on the left to generate a comprehensive attendance audit report.</p>
                        
                        <!-- Database Status Check -->
                        <div class="alert alert-info mt-4">
                            <h6><i class="fas fa-database"></i> Database Status:</h6>
                            <?php
                            // Quick database status check
                            $divisionCount = count($divisions);
                            $empQuery = mysqli_query($connect, "SELECT COUNT(*) as emp_count FROM employees");
                            $empCount = $empQuery ? mysqli_fetch_assoc($empQuery)['emp_count'] : 0;
                            $attQuery = mysqli_query($connect, "SELECT COUNT(*) as att_count FROM attendance");
                            $attCount = $attQuery ? mysqli_fetch_assoc($attQuery)['att_count'] : 0;
                            ?>
                            <p class="mb-1"><strong>Divisions:</strong> <?php echo $divisionCount; ?> found</p>
                            <p class="mb-1"><strong>Employees:</strong> <?php echo $empCount; ?> total</p>
                            <p class="mb-0"><strong>Attendance Records:</strong> <?php echo $attCount; ?> total</p>
                        </div>
                        
                        <div class="mt-4">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="feature-box p-3 border rounded">
                                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                        <h5>Employee Statistics</h5>
                                        <p class="small text-muted">View total employees and attendance rates</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="feature-box p-3 border rounded">
                                        <i class="fas fa-clock fa-2x text-success mb-2"></i>
                                        <h5>Punctuality Analysis</h5>
                                        <p class="small text-muted">Track on-time vs late arrivals</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="feature-box p-3 border rounded">
                                        <i class="fas fa-calendar-day fa-2x text-warning mb-2"></i>
                                        <h5>Daily Breakdown</h5>
                                        <p class="small text-muted">Day-by-day attendance patterns</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="feature-box p-3 border rounded">
                                        <i class="fas fa-print fa-2x text-info mb-2"></i>
                                        <h5>Print Ready Reports</h5>
                                        <p class="small text-muted">Professional formatted reports</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-dismiss alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        if (alert.querySelector('.btn-close')) {
            alert.querySelector('.btn-close').click();
        }
    });
}, 5000);

// Date validation
document.getElementById('date_from').addEventListener('change', function() {
    const fromDate = this.value;
    const toDateInput = document.getElementById('date_to');
    toDateInput.min = fromDate;
    
    if (toDateInput.value && toDateInput.value < fromDate) {
        toDateInput.value = fromDate;
    }
});
</script>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>