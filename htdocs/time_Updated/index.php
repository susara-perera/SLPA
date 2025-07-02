<?php
include('includes/header.php');
include('includes/navbar.php');
include('./includes/scripts.php');
include_once 'dbc.php';

// Fetch all users
$allUsersQuery = "SELECT COUNT(*) as total_users FROM users";
$allUsersResult = mysqli_query($connect, $allUsersQuery);
$allUsers = mysqli_fetch_assoc($allUsersResult)['total_users'];

// Fetch active users
$activeUsersQuery = "SELECT COUNT(*) as active_users FROM login WHERE status = 'Active'";
$activeUsersResult = mysqli_query($connect, $activeUsersQuery);
$activeUsers = mysqli_fetch_assoc($activeUsersResult)['active_users'];

// Fetch employees count by division
$employeesByDivisionQuery = "SELECT division, COUNT(*) as total_employees FROM employees GROUP BY division";
$employeesByDivisionResult = mysqli_query($connect, $employeesByDivisionQuery);
$divisions = [];
$employeesCount = [];

while ($row = mysqli_fetch_assoc($employeesByDivisionResult)) {
    $divisions[] = $row['division'];
    $employeesCount[] = $row['total_employees'];
}

// Define current date
$today = date('Y-m-d');

// Fetch distinct employees who marked IN attendance today
    $distinctAttendanceQuery = "
        SELECT COUNT(DISTINCT employee_ID) as distinct_count
        FROM attendance
        WHERE date_ = '$today' AND scan_type = 'IN'";
$distinctAttendanceResult = mysqli_query($connect, $distinctAttendanceQuery);
$distinctCount = mysqli_fetch_assoc($distinctAttendanceResult)['distinct_count'];

// Fetch total employees count
$totalEmployeesQuery = "SELECT COUNT(*) as total_employees FROM employees";
$totalEmployeesResult = mysqli_query($connect, $totalEmployeesQuery);
$totalEmployees = mysqli_fetch_assoc($totalEmployeesResult)['total_employees'];

// Calculate the number of employees who haven't marked attendance
$notAttendedCount = $totalEmployees - $distinctCount;

// Fetch distinct earliest attendance times for today
$distinctTimesQuery = "
    SELECT employee_id, MIN(time_) as earliest_time
    FROM attendance
    WHERE date_ = '$today' AND scan_type = 'IN'
    GROUP BY employee_id";
$distinctTimesResult = mysqli_query($connect, $distinctTimesQuery);

$distinctTimes = [];
while ($row = mysqli_fetch_assoc($distinctTimesResult)) {
    $distinctTimes[] = $row['earliest_time'];
}

// Convert attendance times into numerical values for the wave chart
$waveChartData = [];
foreach ($distinctTimes as $time) {
    $timeParts = explode(':', $time);
    $waveChartData[] = $timeParts[0] + ($timeParts[1] / 60); // Convert time to hours like (08:30 becomes 8.5)
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
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .chart-container h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #333;
        }

        canvas {
            width: 55% !important;
            height: 300px !important;
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
                                <div class="notification-title">New Employees Added</div>
                                <div class="notification-text"><?php echo $notifications['employee']; ?> new employee(s) added today</div>
                            </div>
                        <?php endif; ?>
                        <?php if ($notifications['user'] > 0): ?>
                            <div class="notification-item">
                                <div class="notification-title">New Users Registered</div>
                                <div class="notification-text"><?php echo $notifications['user']; ?> new user(s) registered today</div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="notification-item">
                            <div class="notification-text">No new notifications today</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Navigation Buttons -->
        <div class="nav-buttons">
          
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
        <!-- Pie Chart for Active vs All Users -->
        <div class="chart-container">
            <h3>Users Overview</h3>
            <canvas id="usersChart"></canvas>
        </div>

        <!-- Bar Chart for Employees by Division -->
        <div class="chart-container">
            <h3>Employees by Division</h3>
            <canvas id="employeesChart"></canvas>
        </div>

        <!-- Wave Chart -->
        <div class="chart-container">
            <h3>Attendance Times for today</h3>
            <canvas id="waveChart"></canvas>
        </div>

        <!-- Doughnut Chart for Attendance Overview -->
        <div class="chart-container">
            <h3>Attendance Overview</h3>
            <canvas id="attendanceChart"></canvas>
        </div>

        <!-- Weekly Attendance Chart -->
        <div class="chart-container weekly-attendance">
            <h3>Weekly Attendance</h3>
            <canvas id="weeklyChart" class="weekly-chart"></canvas>
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

    // Sample weekly attendance data (you can replace this with actual data)
    const weeklyAttendanceData = {
        labels: ['M', 'T', 'W', 'T', 'F'],
        datasets: [{
            label: 'Daily Attendance',
            data: [<?php echo $distinctCount; ?>, <?php echo $distinctCount - 50; ?>, <?php echo $distinctCount + 20; ?>, <?php echo $distinctCount - 30; ?>, <?php echo $distinctCount - 10; ?>],
            backgroundColor: '#007bff',
            borderColor: '#0056b3',
            borderWidth: 1
        }]
    };

    // Data for Users Chart (Active vs All Users)
    const usersChartData = {
        labels: ['Active Users', 'All Users'],
        datasets: [{
            data: [<?php echo $activeUsers; ?>, <?php echo $allUsers; ?>],
            backgroundColor: ['#36A2EB', '#FF6384']
        }]
    };

    // Data for Employees by Division Chart
    const employeesChartData = {
        labels: <?php echo json_encode($divisions); ?>,
        datasets: [{
            label: 'Employees',
            data: <?php echo json_encode($employeesCount); ?>,
            backgroundColor: '#FFCE56'
        }]
    };

    // Data for Attendance Chart (Employees with attendance vs without attendance)
    const attendanceChartData = {
        labels: ['Marked Attendance', 'Did Not Mark Attendance'],
        datasets: [{
            data: [<?php echo $distinctCount; ?>, <?php echo $notAttendedCount; ?>],
            backgroundColor: ['#4CAF50', '#FF9800']
        }]
    };

    // Weekly Attendance Chart
    const ctxWeekly = document.getElementById('weeklyChart').getContext('2d');
    new Chart(ctxWeekly, {
        type: 'bar',
        data: weeklyAttendanceData,
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Pie Chart for Users
    const ctxUsers = document.getElementById('usersChart').getContext('2d');
    new Chart(ctxUsers, {
        type: 'pie',
        data: usersChartData,
    });

    // Bar Chart for Employees by Division
    const ctxEmployees = document.getElementById('employeesChart').getContext('2d');
    new Chart(ctxEmployees, {
        type: 'bar',
        data: employeesChartData,
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Doughnut Chart for Attendance
    const ctxAttendance = document.getElementById('attendanceChart').getContext('2d');
    new Chart(ctxAttendance, {
        type: 'doughnut',
        data: attendanceChartData,
    });

    // Wave Chart for distinct attendance times
    const ctxWave = document.getElementById('waveChart').getContext('2d');
    new Chart(ctxWave, {
        type: 'line', // Line chart to represent the wave of attendance times
        data: {
            labels: <?php echo json_encode(array_keys($distinctTimes)); ?>, // Employee IDs or indices as labels
            datasets: [{
                label: 'Attendance Time (Hours)',
                data: <?php echo json_encode($waveChartData); ?>,
                backgroundColor: 'rgba(0, 123, 255, 0.5)',
                borderColor: '#007bff',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: false,
                    title: {
                        display: true,
                        text: 'Time (Hours)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Employees'
                    }
                }
            }
        }
    });

    // System control functions
    function showAlert() {
        alert('System Alert: This is a demo alert function');
    }

    function printPage() {
        window.print();
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
    });
</script>

<?php include('includes/footer.php'); ?>