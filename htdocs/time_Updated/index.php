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
    SELECT COUNT(DISTINCT employee_id) as distinct_count
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

mysqli_close($connect);
?>

<head>
    <title>Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            padding: 20px;
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
    </style>
</head>

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
</div>

<script>
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
</script>

<?php include('includes/footer.php'); ?>