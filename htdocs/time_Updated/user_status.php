<?php
session_start();
ob_start(); 

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); 
    exit();
}

include('./dbc.php');
include('includes/header2.php');
include('includes/navbar.php');
include('includes/check_access.php'); 

// Set timezone for Sri Lanka
date_default_timezone_set('Asia/Colombo');

// Define the page name
$page = 'user_status.php';

// Check if the user has access to this page
if (!hasAccess($page)) {
    echo "<div class='container'><div class='row mx-md-n8'><div class='col px-md-5'><h1>Access Denied</h1><p>You do not have permission to access this page.</p></div></div></div>";
    include('includes/scripts.php');
    include('includes/footer.php');
    ob_end_flush(); 
    exit();
}

// Initialize variables
$message = '';
$messageType = '';
$searchOption = '';
$selectedDate = '';

// Initialize filter variables to check the user status
$filter = '';
if (isset($_POST['search'])) {
    $searchOption = $_POST['search_option'];
    $selectedDate = $_POST['date'] ?? '';

    switch ($searchOption) {
        case 'active':
            $filter = "WHERE l.status = 'Active'";
            break;
        case 'last_hour':
            $filter = "WHERE l.login_time >= NOW() - INTERVAL 1 HOUR";
            break;
        case 'specific_date':
            if ($selectedDate) {
                $filter = "WHERE DATE(l.login_time) = '" . mysqli_real_escape_string($connect, $selectedDate) . "'";
            }
            break;
    }
}

// Fetch login status from the database
$logins = [];
$sqlLogins = "
    SELECT u.id, u.division, u.role, u.employee_ID, l.login_time, l.logout_time, l.status 
    FROM login l
    JOIN users u ON l.user_id = u.id
    $filter
    ORDER BY l.login_time DESC
";
$result = mysqli_query($connect, $sqlLogins);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $logins[] = $row;
    }
} else {
    $message = "Failed to fetch login records: " . mysqli_error($connect);
    $messageType = "danger";
}

// Count currently active users
$activeCount = 0;
$todayLogins = 0;
$totalUsers = 0;

// Get active users count
$sqlActiveCount = "SELECT COUNT(*) as active_count FROM login WHERE status = 'Active'";
$resultActiveCount = mysqli_query($connect, $sqlActiveCount);
if ($resultActiveCount) {
    $row = mysqli_fetch_assoc($resultActiveCount);
    $activeCount = $row['active_count'];
}

// Get today's logins count
$sqlTodayLogins = "SELECT COUNT(DISTINCT user_id) as today_count FROM login WHERE DATE(login_time) = CURDATE()";
$resultTodayLogins = mysqli_query($connect, $sqlTodayLogins);
if ($resultTodayLogins) {
    $row = mysqli_fetch_assoc($resultTodayLogins);
    $todayLogins = $row['today_count'];
}

// Get total users count
$sqlTotalUsers = "SELECT COUNT(*) as total_count FROM users";
$resultTotalUsers = mysqli_query($connect, $sqlTotalUsers);
if ($resultTotalUsers) {
    $row = mysqli_fetch_assoc($resultTotalUsers);
    $totalUsers = $row['total_count'];
}

mysqli_close($connect);
?>

<style>
/* Professional User Status Dashboard Styling */
.status-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px 0;
    margin-bottom: 25px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.status-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
    border: none;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    margin-bottom: 20px;
}

.status-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.status-card-header {
    background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    color: white;
    border-radius: 15px 15px 0 0;
    padding: 20px 25px;
    border: none;
}

.stats-card {
    background: white;
    border-radius: 15px;
    padding: 20px;
    text-align: center;
    margin-bottom: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-3px);
}

.stats-card.active {
    background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
    color: white;
}

.stats-card.today {
    background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    color: white;
}

.stats-card.total {
    background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);
    color: white;
}

.stats-number {
    font-size: 2.2rem;
    font-weight: 700;
    margin-bottom: 8px;
}

.stats-label {
    font-size: 1rem;
    opacity: 0.9;
}

.status-form-group {
    margin-bottom: 15px;
}

.status-form-label {
    font-weight: 600;
    color: #2d3436;
    margin-bottom: 8px;
    display: block;
}

.status-form-control {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 10px 12px;
    transition: all 0.3s ease;
    font-size: 14px;
}

.status-form-control:focus {
    border-color: #74b9ff;
    box-shadow: 0 0 0 0.2rem rgba(116, 185, 255, 0.25);
    outline: none;
}

.status-btn {
    padding: 10px 25px;
    border-radius: 25px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    border: none;
}

.status-btn-primary {
    background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    color: white;
}

.status-btn-primary:hover {
    background: linear-gradient(135deg, #0984e3 0%, #2d3436 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    color: white;
}

.status-table {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
    border: none;
}

.status-table thead {
    background: linear-gradient(135deg, #2d3436 0%, #636e72 100%);
    color: white;
}

.status-table th {
    border: none;
    padding: 15px 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 12px;
}

.status-table td {
    border: none;
    padding: 12px;
    border-bottom: 1px solid #f1f3f4;
    vertical-align: middle;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.status-badge-active {
    background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
    color: white;
}

.status-badge-inactive {
    background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);
    color: white;
}

.role-badge {
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
}

.role-badge-super {
    background: linear-gradient(135deg, #a29bfe 0%, #6c5ce7 100%);
    color: white;
}

.role-badge-admin {
    background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    color: white;
}

.role-badge-clerk {
    background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);
    color: white;
}

.alert-professional {
    border: none;
    border-radius: 10px;
    padding: 15px 20px;
    margin-bottom: 20px;
    font-weight: 500;
}

.container-fluid {
    padding: 20px;
}

@media (max-width: 768px) {
    .stats-number {
        font-size: 2rem;
    }
    
    .status-table {
        font-size: 12px;
    }
}
</style>

<div class="container-fluid">
    <!-- Header Section -->
    <div class="status-header text-center">
        <h1 class="display-4 mb-3">📊 User Activity Dashboard</h1>
        <p class="lead mb-2">Real-time User Login Status & Activity Monitoring for SLPA</p>
        <small class="text-light">Monitor user sessions, track login activities, and manage system access</small>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-professional alert-dismissible fade show">
            <strong><?= $messageType == 'success' ? 'Success!' : ($messageType == 'warning' ? 'Warning!' : 'Error!') ?></strong> <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-3">
        <div class="col-lg-4 col-md-6">
            <div class="stats-card active">
                <div class="stats-number"><?= $activeCount ?></div>
                <div class="stats-label">
                    <i class="fas fa-circle text-success"></i> Currently Active
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="stats-card today">
                <div class="stats-number"><?= $todayLogins ?></div>
                <div class="stats-label">
                    <i class="fas fa-calendar-day"></i> Today's Logins
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="stats-card total">
                <div class="stats-number"><?= $totalUsers ?></div>
                <div class="stats-label">
                    <i class="fas fa-users"></i> Total Users
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="status-card">
                <div class="status-card-header">
                    <h4 class="mb-0"><i class="fas fa-search"></i> Filter & Search Options</h4>
                </div>
                <div class="card-body p-3">
                    <form method="post" action="" class="row g-3">
                        <div class="col-lg-4">
                            <label class="status-form-label" for="search_option">
                                <i class="fas fa-filter"></i> Filter by:
                            </label>
                            <select id="search_option" name="search_option" class="form-select status-form-control">
                                <option value="">🔍 All Records</option>
                                <option value="active" <?= $searchOption == 'active' ? 'selected' : '' ?>>
                                    🟢 Currently Active Users
                                </option>
                                <option value="last_hour" <?= $searchOption == 'last_hour' ? 'selected' : '' ?>>
                                    ⏰ Logged in Last Hour
                                </option>
                                <option value="specific_date" <?= $searchOption == 'specific_date' ? 'selected' : '' ?>>
                                    📅 Specific Date
                                </option>
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <label class="status-form-label" for="date">
                                <i class="fas fa-calendar"></i> Select Date:
                            </label>
                            <input type="date" id="date" name="date" class="form-control status-form-control" 
                                   value="<?= htmlspecialchars($selectedDate) ?>" max="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-lg-4">
                            <label class="status-form-label" style="opacity: 0;">Search</label>
                            <button type="submit" name="search" class="btn status-btn status-btn-primary w-100">
                                <i class="fas fa-search"></i> Apply Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- User Activity Table -->
    <div class="row">
        <div class="col-12">
            <div class="status-card">
                <div class="status-card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-table"></i> User Login Activity 
                        <span class="badge bg-light text-dark ms-2"><?= count($logins) ?> records</span>
                    </h4>
                </div>
                <div class="card-body p-0">
                    <?php if (count($logins) > 0): ?>
                        <div class="table-responsive">
                            <table class="table status-table mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Employee ID</th>
                                        <th>Division</th>
                                        <th>Role</th>
                                        <th>Login Time</th>
                                        <th>Logout Time</th>
                                        <th>Status</th>
                                        <th>Duration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $serialNumber = 1;
                                    foreach ($logins as $login): 
                                        // Calculate session duration
                                        $loginTime = new DateTime($login['login_time']);
                                        $logoutTime = $login['logout_time'] ? new DateTime($login['logout_time']) : new DateTime();
                                        $duration = $loginTime->diff($logoutTime);
                                        $durationStr = $duration->format('%h:%I:%S');
                                        
                                        // Role badge class
                                        $roleClass = 'role-badge-clerk';
                                        if (strpos(strtolower($login['role']), 'super') !== false) {
                                            $roleClass = 'role-badge-super';
                                        } elseif (strpos(strtolower($login['role']), 'admin') !== false) {
                                            $roleClass = 'role-badge-admin';
                                        }
                                    ?>
                                        <tr>
                                            <td><strong><?= $serialNumber++ ?></strong></td>
                                            <td>
                                                <span class="fw-bold text-primary">
                                                    <?= htmlspecialchars($login['employee_ID']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($login['division']) ?></td>
                                            <td>
                                                <span class="role-badge <?= $roleClass ?>">
                                                    <?= htmlspecialchars($login['role']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('M j, g:i A', strtotime($login['login_time'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= $login['logout_time'] ? 
                                                        date('M j, g:i A', strtotime($login['logout_time'])) : 
                                                        '<span class="text-success">Still Active</span>' ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="status-badge <?= $login['status'] == 'Active' ? 'status-badge-active' : 'status-badge-inactive' ?>">
                                                    <?= htmlspecialchars($login['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-info fw-bold"><?= $durationStr ?></small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No user activity found</h5>
                            <p class="text-muted">
                                <?= $searchOption ? 'Try adjusting your filter criteria' : 'No login records available' ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Enhanced User Status Dashboard functionality
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            if (alert.classList.contains('alert-dismissible')) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        });
    }, 5000);

    // Handle search option changes
    const searchOption = document.getElementById('search_option');
    const dateInput = document.getElementById('date');
    
    if (searchOption) {
        searchOption.addEventListener('change', function() {
            if (this.value === 'specific_date') {
                dateInput.style.borderColor = '#74b9ff';
                dateInput.focus();
            }
        });
    }

    // Auto-refresh functionality
    let autoRefresh = false;
    const refreshButton = document.createElement('button');
    refreshButton.innerHTML = '<i class="fas fa-sync-alt"></i> Auto Refresh: OFF';
    refreshButton.className = 'btn btn-outline-secondary btn-sm ms-2';
    refreshButton.onclick = toggleAutoRefresh;
    
    const header = document.querySelector('.status-card-header h4');
    if (header) {
        header.appendChild(refreshButton);
    }

    function toggleAutoRefresh() {
        autoRefresh = !autoRefresh;
        refreshButton.innerHTML = autoRefresh ? 
            '<i class="fas fa-sync-alt fa-spin"></i> Auto Refresh: ON' : 
            '<i class="fas fa-sync-alt"></i> Auto Refresh: OFF';
        refreshButton.className = autoRefresh ? 
            'btn btn-success btn-sm ms-2' : 
            'btn btn-outline-secondary btn-sm ms-2';
            
        if (autoRefresh) {
            startAutoRefresh();
        } else {
            stopAutoRefresh();
        }
    }

    let refreshInterval;
    function startAutoRefresh() {
        refreshInterval = setInterval(function() {
            // Only refresh if no filters are applied or if showing active users
            const currentFilter = document.getElementById('search_option').value;
            if (!currentFilter || currentFilter === 'active') {
                location.reload();
            }
        }, 30000); // Refresh every 30 seconds
    }

    function stopAutoRefresh() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    }

    // Add real-time timestamp updates
    updateTimestamps();
    setInterval(updateTimestamps, 60000); // Update every minute

    function updateTimestamps() {
        const rows = document.querySelectorAll('.status-table tbody tr');
        rows.forEach(function(row) {
            const statusCell = row.querySelector('td:nth-child(7)');
            if (statusCell && statusCell.textContent.trim() === 'Active') {
                const durationCell = row.querySelector('td:nth-child(8)');
                if (durationCell) {
                    // This would need server-side implementation for accurate updates
                    // For now, just add a visual indicator
                    durationCell.innerHTML += ' <i class="fas fa-circle text-success blink" style="font-size: 8px;"></i>';
                }
            }
        });
    }

    // Add blinking animation for active status
    const style = document.createElement('style');
    style.textContent = `
        .blink {
            animation: blink 2s infinite;
        }
        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.3; }
        }
    `;
    document.head.appendChild(style);
});
</script>

<?php
include('includes/scripts.php');
include('includes/footer.php');
ob_end_flush();
?>
