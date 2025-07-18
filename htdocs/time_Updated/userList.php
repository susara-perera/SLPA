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

// Define the page name
$page = 'userList.php';

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
$searchEmployeeID = isset($_POST['employee_id']) ? mysqli_real_escape_string($connect, $_POST['employee_id']) : '';
$searchDivision = isset($_POST['division']) ? mysqli_real_escape_string($connect, $_POST['division']) : '';
$searchRole = isset($_POST['role']) ? mysqli_real_escape_string($connect, $_POST['role']) : '';

// Initialize query and conditions
$userSql = "SELECT u.*, d.division_name FROM users u LEFT JOIN divisions d ON u.division = d.division_id";
$conditions = [];

if (!empty($searchEmployeeID)) {
    $conditions[] = "u.employee_ID LIKE '%$searchEmployeeID%'";
}

if (!empty($searchDivision)) {
    $conditions[] = "u.division = '$searchDivision'";
}

if (!empty($searchRole)) {
    $conditions[] = "u.role = '$searchRole'";
}

if (!empty($conditions)) {
    $userSql .= " WHERE " . implode(' AND ', $conditions);
}

$userSql .= " ORDER BY u.employee_ID ASC";

// Fetch users
$users = [];
$result = mysqli_query($connect, $userSql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
} else {
    $message = "Failed to fetch users: " . mysqli_error($connect);
    $messageType = "danger";
}

// Get total users count
$totalUsers = count($users);

// Fetch divisions for filter dropdown
$divisions = [];
$divisionSql = "SELECT division_id, division_name FROM divisions ORDER BY division_name";
$divisionResult = mysqli_query($connect, $divisionSql);
if ($divisionResult) {
    while ($row = mysqli_fetch_assoc($divisionResult)) {
        $divisions[] = $row;
    }
}

// Get user statistics
$totalUsersCount = 0;
$activeUsersCount = 0;
$adminUsersCount = 0;

$statsSql = "SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN role LIKE '%admin%' OR role LIKE '%Super_Ad%' THEN 1 ELSE 0 END) as admin_users
    FROM users";
$statsResult = mysqli_query($connect, $statsSql);
if ($statsResult) {
    $stats = mysqli_fetch_assoc($statsResult);
    $totalUsersCount = $stats['total_users'];
    $adminUsersCount = $stats['admin_users'];
}

// Get currently active users count
$activeSql = "SELECT COUNT(DISTINCT user_id) as active_count FROM login WHERE status = 'Active'";
$activeResult = mysqli_query($connect, $activeSql);
if ($activeResult) {
    $activeStats = mysqli_fetch_assoc($activeResult);
    $activeUsersCount = $activeStats['active_count'];
}
?>

<style>
/* Professional User List Dashboard Styling */
.userlist-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px 0;
    margin-bottom: 25px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.userlist-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
    border: none;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    margin-bottom: 20px;
}

.userlist-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.userlist-card-header {
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

.stats-card.total {
    background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    color: white;
}

.stats-card.active {
    background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
    color: white;
}

.stats-card.admin {
    background: linear-gradient(135deg, #a29bfe 0%, #6c5ce7 100%);
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

.userlist-form-group {
    margin-bottom: 15px;
}

.userlist-form-label {
    font-weight: 600;
    color: #2d3436;
    margin-bottom: 8px;
    display: block;
}

.userlist-form-control {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 10px 12px;
    transition: all 0.3s ease;
    font-size: 14px;
}

.userlist-form-control:focus {
    border-color: #74b9ff;
    box-shadow: 0 0 0 0.2rem rgba(116, 185, 255, 0.25);
    outline: none;
}

.userlist-btn {
    padding: 10px 25px;
    border-radius: 25px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    border: none;
}

.userlist-btn-primary {
    background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    color: white;
}

.userlist-btn-primary:hover {
    background: linear-gradient(135deg, #0984e3 0%, #2d3436 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    color: white;
}

.userlist-btn-secondary {
    background: linear-gradient(135deg, #636e72 0%, #2d3436 100%);
    color: white;
}

.userlist-btn-secondary:hover {
    background: linear-gradient(135deg, #2d3436 0%, #636e72 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    color: white;
}

.userlist-table {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
    border: none;
}

.userlist-table thead {
    background: linear-gradient(135deg, #2d3436 0%, #636e72 100%);
    color: white;
}

.userlist-table th {
    border: none;
    padding: 15px 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 12px;
}

.userlist-table td {
    border: none;
    padding: 12px;
    border-bottom: 1px solid #f1f3f4;
    vertical-align: middle;
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

.search-icon {
    color: #74b9ff;
    margin-right: 8px;
}

.user-count-badge {
    background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

@media (max-width: 768px) {
    .stats-number {
        font-size: 2rem;
    }
    
    .userlist-table {
        font-size: 12px;
    }
    
    .userlist-table th, .userlist-table td {
        padding: 8px;
    }
}
</style>

<div class="container-fluid">
    <!-- Header Section -->
    <div class="userlist-header text-center">
        <h1 class="display-4 mb-3">👥 User Management Dashboard</h1>
        <p class="lead mb-2">Complete User Directory & Management for SLPA</p>
        <small class="text-light">Search, filter, and manage all system users efficiently</small>
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
            <div class="stats-card total">
                <div class="stats-number"><?= $totalUsersCount ?></div>
                <div class="stats-label">
                    <i class="fas fa-users"></i> Total Users
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="stats-card active">
                <div class="stats-number"><?= $activeUsersCount ?></div>
                <div class="stats-label">
                    <i class="fas fa-circle text-success"></i> Currently Active
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="stats-card admin">
                <div class="stats-number"><?= $adminUsersCount ?></div>
                <div class="stats-label">
                    <i class="fas fa-user-shield"></i> Admin Users
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="userlist-card">
                <div class="userlist-card-header">
                    <h4 class="mb-0"><i class="fas fa-search"></i> Search & Filter Users</h4>
                </div>
                <div class="card-body p-3">
                    <form method="post" action="" class="row g-3">
                        <div class="col-lg-4">
                            <label class="userlist-form-label" for="employee_id">
                                <i class="fas fa-id-badge search-icon"></i>Employee ID
                            </label>
                            <input type="text" id="employee_id" name="employee_id" 
                                   class="form-control userlist-form-control" 
                                   placeholder="Search by Employee ID..." 
                                   value="<?= htmlspecialchars($searchEmployeeID) ?>">
                        </div>
                        <div class="col-lg-3">
                            <label class="userlist-form-label" for="division">
                                <i class="fas fa-building search-icon"></i>Division
                            </label>
                            <select id="division" name="division" class="form-select userlist-form-control">
                                <option value="">All Divisions</option>
                                <?php foreach ($divisions as $division): ?>
                                    <option value="<?= htmlspecialchars($division['division_id']) ?>" 
                                            <?= $searchDivision == $division['division_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($division['division_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="userlist-form-label" for="role">
                                <i class="fas fa-user-shield search-icon"></i>Role
                            </label>
                            <select id="role" name="role" class="form-select userlist-form-control">
                                <option value="">All Roles</option>
                                <option value="Super_Ad" <?= $searchRole == 'Super_Ad' ? 'selected' : '' ?>>Super Admin</option>
                                <option value="Administration" <?= $searchRole == 'Administration' ? 'selected' : '' ?>>Administration</option>
                                <option value="Administration_clerk" <?= $searchRole == 'Administration_clerk' ? 'selected' : '' ?>>Administrative Clerk</option>
                                <option value="clerk" <?= $searchRole == 'clerk' ? 'selected' : '' ?>>Clerk</option>
                            </select>
                        </div>
                        <div class="col-lg-2">
                            <label class="userlist-form-label" style="opacity: 0;">Search</label>
                            <button type="submit" class="btn userlist-btn userlist-btn-primary w-100">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </form>
                    <div class="mt-3">
                        <a href="userList.php" class="btn userlist-btn userlist-btn-secondary btn-sm">
                            <i class="fas fa-refresh"></i> Clear Filters
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="row">
        <div class="col-12">
            <div class="userlist-card">
                <div class="userlist-card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-table"></i> User Directory 
                        <span class="user-count-badge ms-2"><?= count($users) ?> users found</span>
                    </h4>
                </div>
                <div class="card-body p-0">
                    <?php if (count($users) > 0): ?>
                        <div class="table-responsive">
                            <table class="table userlist-table mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Employee ID</th>
                                        <th>Division</th>
                                        <th>Role</th>
                                        <th>Account Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $serialNumber = 1;
                                    foreach ($users as $user): 
                                        // Role badge class
                                        $roleClass = 'role-badge-clerk';
                                        if (strpos(strtolower($user['role']), 'super') !== false) {
                                            $roleClass = 'role-badge-super';
                                        } elseif (strpos(strtolower($user['role']), 'admin') !== false) {
                                            $roleClass = 'role-badge-admin';
                                        }
                                        
                                        // Check if user is currently active
                                        $userActiveSql = "SELECT COUNT(*) as is_active FROM login WHERE user_id = '{$user['id']}' AND status = 'Active'";
                                        $userActiveResult = mysqli_query($connect, $userActiveSql);
                                        $isActive = false;
                                        if ($userActiveResult) {
                                            $activeData = mysqli_fetch_assoc($userActiveResult);
                                            $isActive = $activeData['is_active'] > 0;
                                        }
                                    ?>
                                        <tr>
                                            <td><strong><?= $serialNumber++ ?></strong></td>
                                            <td>
                                                <span class="fw-bold text-primary">
                                                    <?= htmlspecialchars($user['employee_ID']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    <?= htmlspecialchars($user['division_name'] ?? $user['division']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="role-badge <?= $roleClass ?>">
                                                    <?= htmlspecialchars($user['role']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($isActive): ?>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-circle"></i> Online
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-circle"></i> Offline
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No users found</h5>
                            <p class="text-muted">
                                <?php if ($searchEmployeeID || $searchDivision || $searchRole): ?>
                                    Try adjusting your search criteria or clearing the filters.
                                <?php else: ?>
                                    No users are currently registered in the system.
                                <?php endif; ?>
                            </p>
                            <a href="user.php" class="btn userlist-btn userlist-btn-primary">
                                <i class="fas fa-user-plus"></i> Add New User
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Enhanced User List functionality
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

    // Enhanced search functionality
    const searchInput = document.getElementById('employee_id');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            if (this.value.length > 0) {
                this.style.borderColor = '#74b9ff';
            } else {
                this.style.borderColor = '#e9ecef';
            }
        });
    }

    // Form auto-submit on filter change
    const divisionSelect = document.getElementById('division');
    const roleSelect = document.getElementById('role');
    
    if (divisionSelect) {
        divisionSelect.addEventListener('change', function() {
            if (this.value) {
                this.form.submit();
            }
        });
    }
    
    if (roleSelect) {
        roleSelect.addEventListener('change', function() {
            if (this.value) {
                this.form.submit();
            }
        });
    }

    // Add table row hover effects
    const tableRows = document.querySelectorAll('.userlist-table tbody tr');
    tableRows.forEach(function(row) {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8f9fa';
            this.style.transform = 'scale(1.01)';
            this.style.transition = 'all 0.2s ease';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
            this.style.transform = 'scale(1)';
        });
    });

    // Add loading effect for search
    const searchForm = document.querySelector('form');
    if (searchForm) {
        searchForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
                submitBtn.disabled = true;
            }
        });
    }
});
</script>

<?php
include('includes/scripts.php');
include('includes/footer.php');

ob_end_flush(); 
?>
