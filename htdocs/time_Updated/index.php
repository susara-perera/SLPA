<?php
include('includes/header.php');
include('includes/navbar.php');
include('./includes/scripts.php');
include_once 'dbc.php';

// Define current date
$today = date('Y-m-d');
$currentWeek = date('Y-m-d', strtotime('monday this week'));
$currentWeekEnd = date('Y-m-d', strtotime('sunday this week'));

// Fetch all users
$allUsersQuery = "SELECT COUNT(*) as total_users FROM users";
$allUsersResult = mysqli_query($connect, $allUsersQuery);
$allUsers = mysqli_fetch_assoc($allUsersResult)['total_users'];

// Fetch active users (logged in today)
$activeUsersQuery = "SELECT COUNT(DISTINCT user_id) as active_users FROM login WHERE DATE(login_time) = '$today' AND status = 'Active'";
$activeUsersResult = mysqli_query($connect, $activeUsersQuery);
$activeUsers = mysqli_fetch_assoc($activeUsersResult)['active_users'];

// Fetch employees count by division
$employeesByDivisionQuery = "SELECT division, COUNT(*) as total_employees FROM employees WHERE status = 'Active' GROUP BY division ORDER BY total_employees DESC";
$employeesByDivisionResult = mysqli_query($connect, $employeesByDivisionQuery);
$divisions = [];
$employeesCount = [];

while ($row = mysqli_fetch_assoc($employeesByDivisionResult)) {
    $divisions[] = $row['division'];
    $employeesCount[] = $row['total_employees'];
}

// Fetch employees count by gender
$genderQuery = "SELECT gender, COUNT(*) as count FROM employees WHERE status = 'Active' GROUP BY gender";
$genderResult = mysqli_query($connect, $genderQuery);
$genderData = [];
$genderLabels = [];
$genderCounts = [];

while ($row = mysqli_fetch_assoc($genderResult)) {
    $genderLabels[] = $row['gender'];
    $genderCounts[] = $row['count'];
}

// Fetch distinct employees who marked IN attendance today
$distinctAttendanceQuery = "
    SELECT COUNT(DISTINCT employee_ID) as distinct_count
    FROM attendance
    WHERE date_ = '$today' AND scan_type = 'IN'";
$distinctAttendanceResult = mysqli_query($connect, $distinctAttendanceQuery);
$distinctCount = mysqli_fetch_assoc($distinctAttendanceResult)['distinct_count'];

// Fetch total active employees count
$totalEmployeesQuery = "SELECT COUNT(*) as total_employees FROM employees WHERE status = 'Active'";
$totalEmployeesResult = mysqli_query($connect, $totalEmployeesQuery);
$totalEmployees = mysqli_fetch_assoc($totalEmployeesResult)['total_employees'];

// Calculate the number of employees who haven't marked attendance
$notAttendedCount = $totalEmployees - $distinctCount;

// Fetch attendance data for the last 7 days for weekly chart
$weeklyAttendanceQuery = "
    SELECT DATE(date_) as attendance_date, COUNT(DISTINCT employee_ID) as daily_count
    FROM attendance
    WHERE date_ >= DATE_SUB('$today', INTERVAL 6 DAY) 
    AND date_ <= '$today' 
    AND scan_type = 'IN'
    GROUP BY DATE(date_)
    ORDER BY attendance_date";
$weeklyAttendanceResult = mysqli_query($connect, $weeklyAttendanceQuery);

$weeklyLabels = [];
$weeklyData = [];
$weeklyDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

// Initialize arrays for last 7 days
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dayName = date('D', strtotime($date));
    $weeklyLabels[] = $dayName;
    $weeklyData[$date] = 0;
}

// Fill in actual data
while ($row = mysqli_fetch_assoc($weeklyAttendanceResult)) {
    $weeklyData[$row['attendance_date']] = $row['daily_count'];
}

// Convert to sequential array for Chart.js
$weeklyDataValues = array_values($weeklyData);

// Fetch attendance times for today (for wave chart)
$attendanceTimesQuery = "
    SELECT employee_ID, TIME(MIN(time_)) as earliest_time
    FROM attendance
    WHERE date_ = '$today' AND scan_type = 'IN'
    GROUP BY employee_ID
    ORDER BY earliest_time";
$attendanceTimesResult = mysqli_query($connect, $attendanceTimesQuery);

$attendanceTimes = [];
$employeeIds = [];
while ($row = mysqli_fetch_assoc($attendanceTimesResult)) {
    $time = $row['earliest_time'];
    $timeParts = explode(':', $time);
    $timeInHours = $timeParts[0] + ($timeParts[1] / 60); // Convert to decimal hours
    $attendanceTimes[] = $timeInHours;
    $employeeIds[] = $row['employee_ID'];
}

// Fetch attendance data by scan type for today
$scanTypeQuery = "
    SELECT scan_type, COUNT(*) as count
    FROM attendance
    WHERE date_ = '$today'
    GROUP BY scan_type";
$scanTypeResult = mysqli_query($connect, $scanTypeQuery);

$scanTypeData = ['IN' => 0, 'OUT' => 0];
while ($row = mysqli_fetch_assoc($scanTypeResult)) {
    $scanTypeData[$row['scan_type']] = $row['count'];
}

// Get department-wise attendance for today
$deptAttendanceQuery = "
    SELECT e.division, COUNT(DISTINCT a.employee_ID) as present_count
    FROM attendance a
    JOIN employees e ON a.employee_ID = e.employee_ID
    WHERE a.date_ = '$today' AND a.scan_type = 'IN' AND e.status = 'Active'
    GROUP BY e.division
    ORDER BY present_count DESC";
$deptAttendanceResult = mysqli_query($connect, $deptAttendanceQuery);

$deptAttendanceLabels = [];
$deptAttendanceData = [];
while ($row = mysqli_fetch_assoc($deptAttendanceResult)) {
    $deptAttendanceLabels[] = $row['division'];
    $deptAttendanceData[] = $row['present_count'];
}

// Get current time and determine appropriate greeting
$currentHour = date('H');
if ($currentHour >= 5 && $currentHour < 12) {
    $greeting = "Good morning";
    $greetingIcon = "fas fa-sun";
    $greetingColor = "#ffd700";
} elseif ($currentHour >= 12 && $currentHour < 17) {
    $greeting = "Good afternoon";
    $greetingIcon = "fas fa-cloud-sun";
    $greetingColor = "#ff8c00";
} elseif ($currentHour >= 17 && $currentHour < 21) {
    $greeting = "Good evening";
    $greetingIcon = "fas fa-moon";
    $greetingColor = "#4169e1";
} else {
    $greeting = "Good night";
    $greetingIcon = "fas fa-star";
    $greetingColor = "#191970";
}

// Get current date information
$currentDate = date('l, F j, Y');
$currentTime = date('g:i A');

// Get notifications for new employees and users added today
// First, check if created_at columns exist, if not use alternative approach
$notificationsQuery = "
    SELECT 'employee' as type, 0 as count 
    UNION ALL
    SELECT 'user' as type, 0 as count";

// Try to get actual notification data, but handle errors gracefully
$notifications = ['employee' => 0, 'user' => 0];
$totalNotifications = 0;

// Check if employees table has created_at column
$checkEmployeeColumn = "SHOW COLUMNS FROM employees LIKE 'created_at'";
$employeeColumnResult = mysqli_query($connect, $checkEmployeeColumn);

if (mysqli_num_rows($employeeColumnResult) > 0) {
    // created_at column exists, use it
    $employeeNotificationQuery = "SELECT COUNT(*) as count FROM employees WHERE DATE(created_at) = CURDATE()";
    $employeeNotificationResult = mysqli_query($connect, $employeeNotificationQuery);
    if ($employeeNotificationResult) {
        $notifications['employee'] = mysqli_fetch_assoc($employeeNotificationResult)['count'];
    }
}

// Check if users table has created_at column
$checkUserColumn = "SHOW COLUMNS FROM users LIKE 'created_at'";
$userColumnResult = mysqli_query($connect, $checkUserColumn);

if (mysqli_num_rows($userColumnResult) > 0) {
    // created_at column exists, use it
    $userNotificationQuery = "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()";
    $userNotificationResult = mysqli_query($connect, $userNotificationQuery);
    if ($userNotificationResult) {
        $notifications['user'] = mysqli_fetch_assoc($userNotificationResult)['count'];
    }
}

$totalNotifications = $notifications['employee'] + $notifications['user'];

mysqli_close($connect);
?>

<head>
    <title>Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Background Video Styles */
        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: -2;
            object-fit: cover;
            opacity: 0.3;
        }

        .video-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            z-index: -1;
        }

        /* Fixed dashboard header */
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 25px 30px;
            background: linear-gradient(135deg, #4169e1 0%, #87ceeb 50%, #add8e6 100%);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: fixed;
            top: 60px; /* Below the navbar */
            left: 250px; /* Account for sidebar width */
            right: 20px;
            z-index: 999;
            margin: 0 20px 0 0;
        }
        
        /* Responsive adjustments for fixed header */
        .sidebar-collapse .top-header {
            left: 4.6rem;
        }
        
        @media (max-width: 767.98px) {
            .top-header {
                left: 20px;
                right: 20px;
                margin: 0;
            }
        }
        
        /* Content spacing to account for fixed header */
        .dashboard-content {
            margin-top: 180px; /* Space for fixed header */
            padding: 0 20px;
        }

        .welcome-section {
            display: flex;
            flex-direction: column;
        }

        .welcome-text {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            margin-bottom: 2px;
        }

        .status-text {
            color: rgba(255, 255, 255, 0.85);
            margin-top: 2px;
            font-size: 16px;
            font-weight: 400;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .greeting-icon {
            transition: all 0.3s ease;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        .status-text:hover .greeting-icon {
            transform: scale(1.1) rotate(10deg);
        }

        .date-time-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
            margin-top: 3px;
        }

        .current-date {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
        }

        .current-time {
            font-size: 16px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            letter-spacing: 0.5px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-buttons {
            display: flex;
            gap: 15px;
        }

        .nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .nav-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .nav-btn:hover::before {
            left: 100%;
        }

        .home-btn {
            background: linear-gradient(135deg, #56ab2f, #a8e6cf);
            color: #ffffff;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .home-btn:hover {
            background: linear-gradient(135deg, #4a9625, #96d4b5);
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 25px rgba(86, 171, 47, 0.3);
        }

        .logout-btn {
            background: linear-gradient(135deg, #ff6b6b, #ffa726);
            color: #ffffff;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .logout-btn:hover {
            background: linear-gradient(135deg, #ff5252, #ff9800);
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 25px rgba(255, 107, 107, 0.3);
        }

        .nav-btn i {
            font-size: 14px;
            transition: transform 0.3s ease;
        }

        .nav-btn:hover i {
            transform: scale(1.2);
        }

        .time-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notification-container {
            position: relative;
            cursor: pointer;
        }

        .notification-icon {
            font-size: 24px;
            color: #2c3e50;
            transition: all 0.3s ease;
            padding: 8px;
            border-radius: 50%;
            background: rgba(44, 62, 80, 0.15);
            backdrop-filter: blur(10px);
            box-shadow: 0 0 15px rgba(44, 62, 80, 0.3);
        }

        .notification-icon:hover {
            color: #34495e;
            background: rgba(44, 62, 80, 0.25);
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(44, 62, 80, 0.5);
        }

        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: linear-gradient(135deg, #ff4757, #ff3838);
            color: white;
            font-size: 12px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 50%;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 10px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 300px;
            max-height: 400px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .notification-dropdown.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .notification-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            font-weight: 700;
            color: #2c3e50;
            background: #f8f9fa;
            border-radius: 12px 12px 0 0;
        }

        .notification-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s ease;
        }

        .notification-item:hover {
            background: #f8f9fa;
        }

        .notification-item:last-child {
            border-bottom: none;
            border-radius: 0 0 12px 12px;
        }

        .notification-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .notification-text {
            color: #666;
            font-size: 14px;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
            padding: 0 0 25px 0;
            margin-bottom: 20px;
        }

        .summary-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            padding: 30px 25px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
            position: relative;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .card-title {
            font-size: 16px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
        }

        .card-value {
            font-size: 42px;
            font-weight: 800;
            color: #2980b9;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            line-height: 1;
        }

        .card-change {
            font-size: 16px;
            font-weight: 600;
            color: #27ae60;
            background: rgba(39, 174, 96, 0.1);
            padding: 5px 12px;
            border-radius: 20px;
            display: inline-block;
            margin-top: 5px;
        }

        .card-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 24px;
            color: rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
        }

        .summary-card:hover .card-icon {
            color: rgba(102, 126, 234, 0.6);
            transform: scale(1.1);
        }

        .dashboard-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            padding: 0;
            background-color: #f4f4f4;
        }

        .chart-container {
            background: linear-gradient(145deg, #ffffff 0%, #f8fafb 100%);
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            text-align: center;
            border: 1px solid rgba(0, 0, 0, 0.04);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .chart-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            border-radius: 16px 16px 0 0;
        }

        .chart-container:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }

        .chart-container h3 {
            font-size: 20px;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .chart-container p {
            margin-bottom: 20px;
            font-size: 13px;
            color: #6c757d;
            font-style: italic;
            font-weight: 500;
        }

        .chart-stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 15px;
            padding: 10px;
            background: rgba(102, 126, 234, 0.05);
            border-radius: 8px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 18px;
            font-weight: 700;
            color: #667eea;
        }

        .stat-label {
            font-size: 11px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        canvas {
            width: 100% !important;
            height: 320px !important;
            margin-top: 15px;
        }

        .weekly-attendance {
            grid-column: span 2;
        }

        .weekly-chart {
            height: 200px !important;
        }

        .bottom-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            padding: 20px;
        }

        .recent-activities {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .system-controls {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .control-btn {
            display: block;
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .control-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<!-- Background Video -->
<video autoplay muted loop playsinline class="video-background" id="bgVideo">
    <source src="./images/web_video_slpa.mp4" type="video/mp4">
    <source src="images/web_video_slpa.mp4" type="video/mp4">
    <source src="./images/web_video_slpa.webm" type="video/webm">
</video>

<!-- Video Overlay -->
<div class="video-overlay"></div>

<!-- Top Header Section -->
<div class="top-header">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="welcome-text">👋 Welcome Admin</div>
        <div class="status-text">
            <i class="<?php echo $greetingIcon; ?> greeting-icon" style="color: <?php echo $greetingColor; ?>;"></i>
            <?php echo $greeting; ?>
        </div>
        <div class="date-time-info">
            <div class="current-date"><?php echo $currentDate; ?></div>
            <div class="current-time" id="currentTime"><?php echo $currentTime; ?></div>
        </div>
    </div>
    
    <div class="header-right">
        <!-- Header Actions -->
        <div class="header-actions">
            <!-- Notification Icon -->
            <div class="notification-container" onclick="toggleNotifications()">
                <i class="fas fa-bell notification-icon"></i>
                <?php if ($totalNotifications > 0): ?>
                    <span class="notification-badge"><?php echo $totalNotifications; ?></span>
                <?php endif; ?>
                
                <!-- Notification Dropdown -->
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        Recent Notifications
                    </div>
                    <?php if ($totalNotifications > 0): ?>
                        <?php if ($notifications['employee'] > 0): ?>
                            <div class="notification-item">
                                <div class="notification-title">
                                    <i class="fas fa-user-plus text-success"></i> New Employees Added
                                </div>
                                <div class="notification-text"><?php echo $notifications['employee']; ?> new employee(s) added today</div>
                            </div>
                        <?php endif; ?>
                        <?php if ($notifications['user'] > 0): ?>
                            <div class="notification-item">
                                <div class="notification-title">
                                    <i class="fas fa-users text-primary"></i> New Users Registered
                                </div>
                                <div class="notification-text"><?php echo $notifications['user']; ?> new user(s) registered today</div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="notification-item">
                            <div class="notification-text">
                                <i class="fas fa-check-circle text-muted"></i> No new notifications today
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Quick Stats in Notifications -->
                    <div class="notification-item" style="background: #f8f9fa; border-top: 2px solid #dee2e6;">
                        <div class="notification-title">Today's Quick Stats</div>
                        <div class="notification-text">
                            <small>
                                <i class="fas fa-users text-primary"></i> <?php echo $distinctCount; ?> present • 
                                <i class="fas fa-building text-success"></i> <?php echo count($divisions); ?> divisions • 
                                <i class="fas fa-percentage text-warning"></i> <?php echo $totalEmployees > 0 ? round(($distinctCount / $totalEmployees) * 100, 1) : 0; ?>% attendance
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Navigation Buttons -->
        <div class="nav-buttons">
            <button onclick="refreshDashboard()" class="nav-btn" style="background: linear-gradient(135deg, #17a2b8, #20c997); color: white; border: 2px solid rgba(255, 255, 255, 0.2);">
                <i class="fas fa-sync-alt"></i>
                Refresh
            </button>
        </div>
        
        <a href="logout.php" class="nav-btn logout-btn" onclick="return confirm('Are you sure you want to logout?')" style="padding: 6px 10px; font-size: 12px; margin-left: 15px;">
            <i class="fas fa-sign-out-alt"></i>
            Logout
        </a>
    </div>
</div>

<!-- Dashboard Content Container -->
<div class="dashboard-content">
    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <i class="fas fa-users card-icon"></i>
            <div class="card-title">Total Employees</div>
            <div class="card-value"><?php echo $totalEmployees; ?></div>
            <div class="card-change">Registered</div>
        </div>
        <div class="summary-card">
            <i class="fas fa-user-check card-icon"></i>
            <div class="card-title">Present Today</div>
            <div class="card-value"><?php echo $distinctCount; ?></div>
            <div class="card-change">+<?php echo ($distinctCount > 0) ? round(($distinctCount / $totalEmployees) * 100, 1) : 0; ?>% Attendance</div>
        </div>
    </div>

    <div class="dashboard-container">
        <!-- Users Overview Chart -->
        <div class="chart-container">
            <h3><i class="fas fa-users text-primary"></i> User Activity</h3>
            <p class="text-muted small">System login activity comparison</p>
            <div class="chart-stats">
                <div class="stat-item">
                    <div class="stat-value"><?php echo $activeUsers; ?></div>
                    <div class="stat-label">Active Today</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $allUsers; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $allUsers > 0 ? round(($activeUsers / $allUsers) * 100, 1) : 0; ?>%</div>
                    <div class="stat-label">Login Rate</div>
                </div>
            </div>
            <canvas id="usersChart"></canvas>
        </div>

        <!-- Employees by Division Chart -->
        <div class="chart-container">
            <h3><i class="fas fa-building text-success"></i> Division Distribution</h3>
            <p class="text-muted small">Active employee count per department</p>
            <div class="chart-stats">
                <div class="stat-item">
                    <div class="stat-value"><?php echo count($divisions); ?></div>
                    <div class="stat-label">Active Divisions</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $totalEmployees; ?></div>
                    <div class="stat-label">Total Employees</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo count($divisions) > 0 ? round($totalEmployees / count($divisions), 1) : 0; ?></div>
                    <div class="stat-label">Avg/Division</div>
                </div>
            </div>
            <canvas id="employeesChart"></canvas>
        </div>

        <!-- Today's Attendance Overview -->
        <div class="chart-container">
            <h3><i class="fas fa-calendar-check text-info"></i> Attendance Status</h3>
            <p class="text-muted small">Real-time attendance breakdown for today</p>
            <div class="chart-stats">
                <div class="stat-item">
                    <div class="stat-value"><?php echo $distinctCount; ?></div>
                    <div class="stat-label">Present</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $notAttendedCount; ?></div>
                    <div class="stat-label">Absent</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $totalEmployees > 0 ? round(($distinctCount / $totalEmployees) * 100, 1) : 0; ?>%</div>
                    <div class="stat-label">Rate</div>
                </div>
            </div>
            <canvas id="attendanceChart"></canvas>
        </div>

        <!-- Gender Distribution Chart -->
        <div class="chart-container">
            <h3><i class="fas fa-venus-mars text-warning"></i> Employee Demographics</h3>
            <p class="text-muted small">Gender distribution of active employees</p>
            <div class="chart-stats">
                <div class="stat-item">
                    <div class="stat-value"><?php echo isset($genderCounts[0]) ? $genderCounts[0] : 0; ?></div>
                    <div class="stat-label"><?php echo isset($genderLabels[0]) ? $genderLabels[0] : 'N/A'; ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo isset($genderCounts[1]) ? $genderCounts[1] : 0; ?></div>
                    <div class="stat-label"><?php echo isset($genderLabels[1]) ? $genderLabels[1] : 'N/A'; ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php 
                        $ratio = 0;
                        if (isset($genderCounts[0]) && isset($genderCounts[1]) && $genderCounts[1] > 0) {
                            $ratio = round($genderCounts[0] / $genderCounts[1], 2);
                        }
                        echo $ratio;
                    ?></div>
                    <div class="stat-label">Ratio</div>
                </div>
            </div>
            <canvas id="genderChart"></canvas>
        </div>

        <!-- Attendance Times Distribution -->
        <div class="chart-container">
            <h3><i class="fas fa-clock text-info"></i> Check-in Timeline</h3>
            <p class="text-muted small">Today's employee arrival pattern</p>
            <div class="chart-stats">
                <div class="stat-item">
                    <div class="stat-value"><?php echo count($attendanceTimes); ?></div>
                    <div class="stat-label">Checked In</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php 
                        if (!empty($attendanceTimes)) {
                            $earliest = min($attendanceTimes);
                            $hours = floor($earliest);
                            $minutes = round(($earliest - $hours) * 60);
                            echo sprintf('%02d:%02d', $hours, $minutes);
                        } else {
                            echo 'N/A';
                        }
                    ?></div>
                    <div class="stat-label">Earliest</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php 
                        if (!empty($attendanceTimes)) {
                            $latest = max($attendanceTimes);
                            $hours = floor($latest);
                            $minutes = round(($latest - $hours) * 60);
                            echo sprintf('%02d:%02d', $hours, $minutes);
                        } else {
                            echo 'N/A';
                        }
                    ?></div>
                    <div class="stat-label">Latest</div>
                </div>
            </div>
            <canvas id="waveChart"></canvas>
        </div>

        <!-- Weekly Attendance Trend -->
        <div class="chart-container weekly-attendance">
            <h3><i class="fas fa-chart-line text-primary"></i> Weekly Trend Analysis</h3>
            <p class="text-muted small">7-day attendance pattern with trend indicators</p>
            <div class="chart-stats">
                <div class="stat-item">
                    <div class="stat-value"><?php echo array_sum($weeklyDataValues); ?></div>
                    <div class="stat-label">Total Week</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo count($weeklyDataValues) > 0 ? round(array_sum($weeklyDataValues) / count($weeklyDataValues), 1) : 0; ?></div>
                    <div class="stat-label">Daily Avg</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo !empty($weeklyDataValues) ? max($weeklyDataValues) : 0; ?></div>
                    <div class="stat-label">Peak Day</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php 
                        if (count($weeklyDataValues) >= 2) {
                            $trend = end($weeklyDataValues) - prev($weeklyDataValues);
                            echo $trend > 0 ? '+' . $trend : $trend;
                        } else {
                            echo '0';
                        }
                    ?></div>
                    <div class="stat-label">Trend</div>
                </div>
            </div>
            <canvas id="weeklyChart" class="weekly-chart"></canvas>
        </div>
    </div>

    <!-- Additional Statistics Cards -->
    <div class="summary-cards" style="margin-top: 30px;">
        <div class="summary-card">
            <i class="fas fa-chart-pie card-icon"></i>
            <div class="card-title">Attendance Rate</div>
            <div class="card-value"><?php echo $totalEmployees > 0 ? round(($distinctCount / $totalEmployees) * 100, 1) : 0; ?>%</div>
            <div class="card-change">Today's Rate</div>
        </div>
        <div class="summary-card">
            <i class="fas fa-user-friends card-icon"></i>
            <div class="card-title">Active Divisions</div>
            <div class="card-value"><?php echo count($divisions); ?></div>
            <div class="card-change">With Employees</div>
        </div>
        <div class="summary-card">
            <i class="fas fa-clock card-icon"></i>
            <div class="card-title">Early Birds</div>
            <div class="card-value"><?php 
                $earlyCount = 0;
                foreach ($attendanceTimes as $time) {
                    if ($time <= 8.0) $earlyCount++; // Before 8:00 AM
                }
                echo $earlyCount;
            ?></div>
            <div class="card-change">Before 8:00 AM</div>
        </div>
        <div class="summary-card">
            <i class="fas fa-user-graduate card-icon"></i>
            <div class="card-title">Active Users</div>
            <div class="card-value"><?php echo $activeUsers; ?></div>
            <div class="card-change">Logged in Today</div>
        </div>
    </div>
</div>



    
<script>
    // Update time display and greeting
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        });
        document.getElementById('currentTime').textContent = timeString;
    }
    
    // Update greeting based on current time
    function updateGreeting() {
        const now = new Date();
        const hour = now.getHours();
        const greetingElement = document.querySelector('.status-text');
        const iconElement = document.querySelector('.greeting-icon');
        
        let greeting, iconClass, iconColor;
        
        if (hour >= 5 && hour < 12) {
            greeting = "Good morning";
            iconClass = "fas fa-sun";
            iconColor = "#ffd700";
        } else if (hour >= 12 && hour < 17) {
            greeting = "Good afternoon";
            iconClass = "fas fa-cloud-sun";
            iconColor = "#ff8c00";
        } else if (hour >= 17 && hour < 21) {
            greeting = "Good evening";
            iconClass = "fas fa-moon";
            iconColor = "#4169e1";
        } else {
            greeting = "Good night";
            iconClass = "fas fa-star";
            iconColor = "#191970";
        }
        
        // Update icon
        iconElement.className = iconClass + " greeting-icon";
        iconElement.style.color = iconColor;
        
        // Update greeting text (keep the icon)
        greetingElement.innerHTML = `<i class="${iconClass} greeting-icon" style="color: ${iconColor};"></i> ${greeting}`;
    }
    
    // Update date display
    function updateDate() {
        const now = new Date();
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        };
        const dateString = now.toLocaleDateString('en-US', options);
        document.querySelector('.current-date').textContent = dateString;
    }
    
    // Update time every second
    setInterval(updateTime, 1000);
    // Update greeting every minute
    setInterval(updateGreeting, 60000);
    // Update date every hour
    setInterval(updateDate, 3600000);
    
    // Initial calls
    updateTime();
    updateGreeting();
    updateDate();

    // Notification functions
    function toggleNotifications() {
        const dropdown = document.getElementById('notificationDropdown');
        dropdown.classList.toggle('show');
    }

    // Close notifications when clicking outside
    document.addEventListener('click', function(event) {
        const notificationContainer = document.querySelector('.notification-container');
        const dropdown = document.getElementById('notificationDropdown');
        
        if (!notificationContainer.contains(event.target)) {
            dropdown.classList.remove('show');
        }
    });

    // Professional color schemes
    const primaryColors = {
        blue: '#3498db',
        green: '#2ecc71',
        orange: '#f39c12',
        red: '#e74c3c',
        purple: '#9b59b6',
        teal: '#1abc9c',
        indigo: '#6c5ce7',
        pink: '#fd79a8'
    };

    const gradientColors = {
        blueGradient: ['#667eea', '#764ba2'],
        greenGradient: ['#11998e', '#38ef7d'],
        orangeGradient: ['#ff9a9e', '#fecfef'],
        redGradient: ['#667eea', '#764ba2'],
        purpleGradient: ['#a8edea', '#fed6e3']
    };

    // Enhanced Weekly Attendance Data
    const weeklyAttendanceData = {
        labels: <?php echo json_encode($weeklyLabels); ?>,
        datasets: [{
            label: 'Daily Attendance',
            data: <?php echo json_encode($weeklyDataValues); ?>,
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            borderColor: '#667eea',
            borderWidth: 4,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#667eea',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 3,
            pointRadius: 8,
            pointHoverRadius: 12,
            pointHoverBackgroundColor: '#4834d4',
            pointHoverBorderColor: '#ffffff',
            pointHoverBorderWidth: 4
        }]
    };

    // Enhanced Users Chart Data
    const usersChartData = {
        labels: ['Active Today', 'Inactive Users'],
        datasets: [{
            data: [<?php echo $activeUsers; ?>, <?php echo $allUsers - $activeUsers; ?>],
            backgroundColor: [
                'rgba(52, 152, 219, 0.9)',
                'rgba(149, 165, 166, 0.7)'
            ],
            borderColor: [
                '#3498db',
                '#95a5a6'
            ],
            borderWidth: 3,
            hoverBackgroundColor: [
                'rgba(52, 152, 219, 1)',
                'rgba(149, 165, 166, 0.9)'
            ],
            hoverBorderWidth: 4
        }]
    };

    // Enhanced Employees by Division Data
    const employeesChartData = {
        labels: <?php echo json_encode($divisions); ?>,
        datasets: [{
            label: 'Active Employees',
            data: <?php echo json_encode($employeesCount); ?>,
            backgroundColor: [
                'rgba(46, 204, 113, 0.8)',
                'rgba(52, 152, 219, 0.8)',
                'rgba(155, 89, 182, 0.8)',
                'rgba(241, 196, 15, 0.8)',
                'rgba(230, 126, 34, 0.8)',
                'rgba(231, 76, 60, 0.8)',
                'rgba(26, 188, 156, 0.8)',
                'rgba(108, 92, 231, 0.8)'
            ],
            borderColor: [
                '#2ecc71',
                '#3498db',
                '#9b59b6',
                '#f1c40f',
                '#e67e22',
                '#e74c3c',
                '#1abc9c',
                '#6c5ce7'
            ],
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false
        }]
    };

    // Enhanced Attendance Chart Data
    const attendanceChartData = {
        labels: ['Present Today', 'Absent Today'],
        datasets: [{
            data: [<?php echo $distinctCount; ?>, <?php echo $notAttendedCount; ?>],
            backgroundColor: [
                'rgba(46, 204, 113, 0.9)',
                'rgba(231, 76, 60, 0.7)'
            ],
            borderColor: [
                '#2ecc71',
                '#e74c3c'
            ],
            borderWidth: 3,
            hoverBackgroundColor: [
                'rgba(46, 204, 113, 1)',
                'rgba(231, 76, 60, 0.9)'
            ],
            hoverBorderWidth: 4
        }]
    };

    // Gender Distribution Chart Data
    const genderChartData = {
        labels: <?php echo json_encode($genderLabels); ?>,
        datasets: [{
            data: <?php echo json_encode($genderCounts); ?>,
            backgroundColor: [
                'rgba(108, 92, 231, 0.9)',
                'rgba(253, 121, 168, 0.9)'
            ],
            borderColor: [
                '#6c5ce7',
                '#fd79a8'
            ],
            borderWidth: 3,
            hoverBackgroundColor: [
                'rgba(108, 92, 231, 1)',
                'rgba(253, 121, 168, 1)'
            ],
            hoverBorderWidth: 4
        }]
    };

    // Chart.js default settings
    Chart.defaults.font.family = "'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#2c3e50';

    // Weekly Attendance Chart
    const ctxWeekly = document.getElementById('weeklyChart').getContext('2d');
    new Chart(ctxWeekly, {
        type: 'line',
        data: weeklyAttendanceData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: false
                },
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(44, 62, 80, 0.9)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: '#667eea',
                    borderWidth: 2,
                    cornerRadius: 8,
                    displayColors: false,
                    titleFont: { weight: 'bold', size: 14 },
                    bodyFont: { size: 13 },
                    callbacks: {
                        label: function(context) {
                            return `${context.parsed.y} employees attended`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        lineWidth: 1
                    },
                    ticks: {
                        color: '#7f8c8d',
                        font: { size: 11, weight: '600' }
                    },
                    title: {
                        display: true,
                        text: 'Attendance Count',
                        color: '#2c3e50',
                        font: { size: 12, weight: 'bold' }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#7f8c8d',
                        font: { size: 11, weight: '600' }
                    }
                }
            },
            elements: {
                point: {
                    hoverRadius: 12
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });

    // Users Chart (Doughnut)
    const ctxUsers = document.getElementById('usersChart').getContext('2d');
    new Chart(ctxUsers, {
        type: 'doughnut',
        data: usersChartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: false
                },
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        font: { size: 12, weight: '600' },
                        color: '#2c3e50'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(44, 62, 80, 0.9)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: '#3498db',
                    borderWidth: 2,
                    cornerRadius: 8,
                    displayColors: true,
                    titleFont: { weight: 'bold', size: 14 },
                    bodyFont: { size: 13 },
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed * 100) / total).toFixed(1);
                            return `${context.label}: ${context.parsed} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });

    // Employees by Division Chart (Bar)
    const ctxEmployees = document.getElementById('employeesChart').getContext('2d');
    new Chart(ctxEmployees, {
        type: 'bar',
        data: employeesChartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: false
                },
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(44, 62, 80, 0.9)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: '#2ecc71',
                    borderWidth: 2,
                    cornerRadius: 8,
                    displayColors: false,
                    titleFont: { weight: 'bold', size: 14 },
                    bodyFont: { size: 13 },
                    callbacks: {
                        label: function(context) {
                            return `${context.parsed.y} employees in ${context.label}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        lineWidth: 1
                    },
                    ticks: {
                        color: '#7f8c8d',
                        font: { size: 11, weight: '600' }
                    },
                    title: {
                        display: true,
                        text: 'Employee Count',
                        color: '#2c3e50',
                        font: { size: 12, weight: 'bold' }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#7f8c8d',
                        font: { size: 11, weight: '600' },
                        maxRotation: 45
                    }
                }
            }
        }
    });

    // Attendance Chart (Doughnut)
    const ctxAttendance = document.getElementById('attendanceChart').getContext('2d');
    new Chart(ctxAttendance, {
        type: 'doughnut',
        data: attendanceChartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: false
                },
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        font: { size: 12, weight: '600' },
                        color: '#2c3e50'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(44, 62, 80, 0.9)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: '#2ecc71',
                    borderWidth: 2,
                    cornerRadius: 8,
                    displayColors: true,
                    titleFont: { weight: 'bold', size: 14 },
                    bodyFont: { size: 13 },
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed * 100) / total).toFixed(1);
                            return `${context.label}: ${context.parsed} employees (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });

    // Gender Distribution Chart (Pie)
    const ctxGender = document.getElementById('genderChart').getContext('2d');
    new Chart(ctxGender, {
        type: 'pie',
        data: genderChartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: false
                },
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        font: { size: 12, weight: '600' },
                        color: '#2c3e50'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(44, 62, 80, 0.9)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: '#6c5ce7',
                    borderWidth: 2,
                    cornerRadius: 8,
                    displayColors: true,
                    titleFont: { weight: 'bold', size: 14 },
                    bodyFont: { size: 13 },
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed * 100) / total).toFixed(1);
                            return `${context.label}: ${context.parsed} employees (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // Attendance Times Wave Chart
    const ctxWave = document.getElementById('waveChart').getContext('2d');
    new Chart(ctxWave, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_map(function($id, $index) { return 'Employee ' . ($index + 1); }, $employeeIds, array_keys($employeeIds))); ?>,
            datasets: [{
                label: 'Check-in Time',
                data: <?php echo json_encode($attendanceTimes); ?>,
                backgroundColor: 'rgba(26, 188, 156, 0.2)',
                borderColor: '#1abc9c',
                borderWidth: 4,
                fill: true,
                tension: 0.6,
                pointBackgroundColor: '#1abc9c',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 10,
                pointHoverBackgroundColor: '#16a085',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: false
                },
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(44, 62, 80, 0.9)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: '#1abc9c',
                    borderWidth: 2,
                    cornerRadius: 8,
                    displayColors: false,
                    titleFont: { weight: 'bold', size: 14 },
                    bodyFont: { size: 13 },
                    callbacks: {
                        label: function(context) {
                            const timeValue = context.parsed.y;
                            const hours = Math.floor(timeValue);
                            const minutes = Math.round((timeValue - hours) * 60);
                            const timeString = hours.toString().padStart(2, '0') + ':' + minutes.toString().padStart(2, '0');
                            return `Check-in time: ${timeString}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    min: 6,
                    max: 12,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        lineWidth: 1
                    },
                    ticks: {
                        color: '#7f8c8d',
                        font: { size: 11, weight: '600' },
                        callback: function(value) {
                            const hours = Math.floor(value);
                            const minutes = Math.round((value - hours) * 60);
                            return hours.toString().padStart(2, '0') + ':' + minutes.toString().padStart(2, '0');
                        }
                    },
                    title: {
                        display: true,
                        text: 'Check-in Time (24H Format)',
                        color: '#2c3e50',
                        font: { size: 12, weight: 'bold' }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#7f8c8d',
                        font: { size: 11, weight: '600' },
                        maxTicksLimit: 8,
                        callback: function(value, index) {
                            return index % 2 === 0 ? this.getLabelForValue(value) : '';
                        }
                    },
                    title: {
                        display: true,
                        text: 'Employees (Ordered by Check-in Time)',
                        color: '#2c3e50',
                        font: { size: 12, weight: 'bold' }
                    }
                }
            },
            elements: {
                point: {
                    hoverRadius: 10
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });

    // System control functions
    function showAlert() {
        alert('System Alert: Dashboard data updated successfully!');
    }

    function printPage() {
        window.print();
    }

    function refreshDashboard() {
        if (confirm('Refresh dashboard data? This will reload the page.')) {
            window.location.reload();
        }
    }

    // Auto-refresh dashboard every 5 minutes (300000 ms)
    setInterval(function() {
        console.log('Auto-refreshing dashboard data...');
        // You can implement AJAX refresh here instead of full page reload
        // For now, we'll just show a notification
        showAutoRefreshNotification();
    }, 300000);

    function showAutoRefreshNotification() {
        // Create a temporary notification
        const notification = document.createElement('div');
        notification.innerHTML = `
            <div style="position: fixed; top: 100px; right: 20px; background: #28a745; color: white; 
                        padding: 10px 15px; border-radius: 5px; z-index: 10000; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                <i class="fas fa-sync-alt"></i> Dashboard data refreshed automatically
            </div>
        `;
        document.body.appendChild(notification);
        
        // Remove notification after 3 seconds
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 3000);
    }

    // Background video handling
    document.addEventListener('DOMContentLoaded', function() {
        const video = document.getElementById('bgVideo');
        if (video) {
            video.play().catch(function(error) {
                console.log('Video autoplay failed:', error);
                // Video failed to play, hide video element
                video.style.display = 'none';
            });
        }
        
        // Initialize tooltips if using Bootstrap
        if (typeof bootstrap !== 'undefined') {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    });
</script>

<?php include('includes/footer.php'); ?>