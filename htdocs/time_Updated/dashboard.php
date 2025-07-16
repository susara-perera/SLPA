<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_name = isset($_SESSION['username']) ? $_SESSION['username'] : $_SESSION['employee_ID'];
$user_role = $_SESSION['role'] ?? $_SESSION['user_type'] ?? 'Employee';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLPA Dashboard - Quick Access</title>
    <link rel="icon" type="image/jpeg" href="dist/img/logo.jpg">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .dashboard-container {
            padding: 40px 20px;
        }
        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }
        .quick-access-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .access-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }
        .access-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            text-decoration: none;
            color: inherit;
        }
        .access-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #667eea;
        }
        .access-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }
        .access-description {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.4;
        }
        .user-info {
            background: rgba(255, 255, 255, 0.9);
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            transition: background 0.2s ease;
        }
        .logout-btn:hover {
            background: #c82333;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container dashboard-container">
    <!-- User Info -->
    <div class="user-info d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Welcome, <?= htmlspecialchars($user_name) ?></h5>
            <small class="text-muted">Role: <?= htmlspecialchars($user_role) ?></small>
        </div>
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt me-1"></i> Logout
        </a>
    </div>

    <!-- Welcome Section -->
    <div class="welcome-card text-center">
        <h1 class="display-4 mb-3">
            <i class="fas fa-anchor text-primary me-3"></i>
            SLPA Management System
        </h1>
        <p class="lead">Sri Lanka Port Authority - Time Attendance & Management Dashboard</p>
        <hr class="my-4">
        <p class="mb-0">Access all your management tools and reports from one central location</p>
    </div>

    <!-- Quick Access Grid -->
    <div class="quick-access-grid">
        
        <!-- Attendance Reports -->
        <a href="attendance_report.php" class="access-card">
            <div class="access-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="access-title">Attendance Reports</div>
            <div class="access-description">
                Generate individual or group attendance reports with date filtering and export options
            </div>
        </a>

        <!-- All Ports -->
        <a href="all_ports.php" class="access-card">
            <div class="access-icon">
                <i class="fas fa-ship"></i>
            </div>
            <div class="access-title">All Ports</div>
            <div class="access-description">
                View all Sri Lanka ports, access port-specific login systems and management tools
            </div>
        </a>

        <!-- User Management -->
        <a href="user_management.php" class="access-card">
            <div class="access-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="access-title">User Management</div>
            <div class="access-description">
                Add, edit, and manage user accounts for the system with role-based permissions
            </div>
        </a>

        <!-- Attendance Data -->
        <a href="manage_attendance_data.php" class="access-card">
            <div class="access-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="access-title">Attendance Data</div>
            <div class="access-description">
                Manage attendance records, create sample data, and view attendance statistics
            </div>
        </div>

        <!-- Database Tools -->
        <a href="test_database_connection.php" class="access-card">
            <div class="access-icon">
                <i class="fas fa-database"></i>
            </div>
            <div class="access-title">Database Tools</div>
            <div class="access-description">
                Test database connections, view table data, and troubleshoot system issues
            </div>
        </a>

        <!-- Port Testing -->
        <a href="test_ports_db.php" class="access-card">
            <div class="access-icon">
                <i class="fas fa-anchor"></i>
            </div>
            <div class="access-title">Port System Test</div>
            <div class="access-description">
                Test port-specific databases, user authentication, and port management features
            </div>
        </a>

        <?php if ($user_role === 'Super Admin' || $user_role === 'Admin'): ?>
        <!-- Admin Only: Reset Database -->
        <a href="reset_db.php" class="access-card" style="border: 2px solid #dc3545;">
            <div class="access-icon" style="color: #dc3545;">
                <i class="fas fa-trash-restore"></i>
            </div>
            <div class="access-title" style="color: #dc3545;">Reset Database</div>
            <div class="access-description">
                <strong>Admin Only:</strong> Reset port databases and restore default configurations
            </div>
        </a>
        <?php endif; ?>

        <!-- Employee Self-Service -->
        <a href="user.php" class="access-card">
            <div class="access-icon">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="access-title">Employee Portal</div>
            <div class="access-description">
                View personal attendance records, update profile, and access employee features
            </div>
        </a>

        <!-- Reports & Analytics -->
        <a href="#" class="access-card" onclick="alert('Coming Soon!')">
            <div class="access-icon">
                <i class="fas fa-chart-pie"></i>
            </div>
            <div class="access-title">Analytics Dashboard</div>
            <div class="access-description">
                Advanced analytics, charts, and comprehensive reporting tools (Coming Soon)
            </div>
        </a>

    </div>

    <!-- Footer -->
    <div class="text-center mt-5">
        <p class="text-white-50">
            Copyright © 2025 Created by: Ports Authority IS Division. All rights reserved. | Version 3.1.0
        </p>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
