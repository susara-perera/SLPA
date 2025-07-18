<?php
session_start();
ob_start();

include('./dbc.php');
include('includes/header2.php');
include('includes/navbar.php');
include('includes/check_access.php');

$page = 'userManage.php';

// Check if the user has access to this page
if (!hasAccess($page)) {
    echo "<div class='container'><div class='row mx-md-n8'><div class='col px-md-5'><h1>Access Denied</h1><p>You do not have permission to access this page.</p></div></div></div>";
    include('includes/scripts.php');
    include('includes/footer.php');
    exit();
}

// Initialize variables
$message = '';
$messageType = '';

// Handle user deletion
if (isset($_GET['delete_id'])) {
    $userId = mysqli_real_escape_string($connect, $_GET['delete_id']);

    // Check if user has active sessions before deletion
    $checkSessionSql = "SELECT COUNT(*) as active_sessions FROM login WHERE user_id = ? AND status = 'Active'";
    $checkStmt = mysqli_prepare($connect, $checkSessionSql);
    if ($checkStmt) {
        mysqli_stmt_bind_param($checkStmt, "i", $userId);
        mysqli_stmt_execute($checkStmt);
        $result = mysqli_stmt_get_result($checkStmt);
        $sessionData = mysqli_fetch_assoc($result);
        
        if ($sessionData['active_sessions'] > 0) {
            $message = "Cannot delete user: User has active login sessions. Please logout the user first.";
            $messageType = "warning";
        } else {
            // Proceed with deletion
            $sql = "DELETE FROM users WHERE id = ?";
            $stmt = mysqli_prepare($connect, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $userId);
                mysqli_stmt_execute($stmt);

                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    header("Location: userManage.php?success=user_deleted");
                    ob_end_flush(); 
                    exit();
                } else {
                    $message = "No user found with the specified ID.";
                    $messageType = "danger";
                }
                mysqli_stmt_close($stmt);
            } else {
                $message = "Database error occurred: " . mysqli_error($connect);
                $messageType = "danger";
            }
        }
        mysqli_stmt_close($checkStmt);
    }
}

// Handle success/error messages from URL
if (isset($_GET['success']) && $_GET['success'] === 'user_deleted') {
    $message = 'User has been successfully deleted from the system!';
    $messageType = 'success';
}

if (isset($_GET['error'])) {
    $error_messages = [
        'delete_failed' => 'Failed to delete the user. Please try again.',
        'sql_error' => 'Database error occurred. Please contact administrator.'
    ];
    $message = $error_messages[$_GET['error']] ?? 'An unknown error occurred.';
    $messageType = 'danger';
}

// Initialize variables for search
$searchRole = isset($_POST['role']) ? mysqli_real_escape_string($connect, $_POST['role']) : '';
$searchEmployeeID = isset($_POST['employee_id']) ? mysqli_real_escape_string($connect, $_POST['employee_id']) : '';
$searchDivision = isset($_POST['division']) ? mysqli_real_escape_string($connect, $_POST['division']) : '';

// Enhanced query with division join
$userSql = "SELECT u.*, d.division_name FROM users u LEFT JOIN divisions d ON u.division = d.division_id";
$countSql = "SELECT COUNT(*) as total FROM users u";
$conditions = [];

if (!empty($searchRole)) {
    $conditions[] = "u.role = '$searchRole'";
}
if (!empty($searchEmployeeID)) {
    $conditions[] = "u.employee_ID LIKE '%$searchEmployeeID%'";
}
if (!empty($searchDivision)) {
    $conditions[] = "u.division = '$searchDivision'";
}

if (!empty($conditions)) {
    $userSql .= " WHERE " . implode(' AND ', $conditions);
    $countSql .= " WHERE " . implode(' AND ', $conditions);
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

// Fetch the count of users based on search criteria
$countResult = mysqli_query($connect, $countSql);
$totalCount = 0;
if ($countResult) {
    $countRow = mysqli_fetch_assoc($countResult);
    $totalCount = $countRow['total'];
}

// Fetch divisions for filter dropdown
$divisions = [];
$divisionSql = "SELECT division_id, division_name FROM divisions ORDER BY division_name";
$divisionResult = mysqli_query($connect, $divisionSql);
if ($divisionResult) {
    while ($row = mysqli_fetch_assoc($divisionResult)) {
        $divisions[] = $row;
    }
}

// Get statistics
$totalUsersCount = 0;
$adminUsersCount = 0;
$clerkUsersCount = 0;

$statsSql = "SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN role LIKE '%admin%' OR role LIKE '%Super_Ad%' THEN 1 ELSE 0 END) as admin_users,
    SUM(CASE WHEN role LIKE '%clerk%' THEN 1 ELSE 0 END) as clerk_users
    FROM users";
$statsResult = mysqli_query($connect, $statsSql);
if ($statsResult) {
    $stats = mysqli_fetch_assoc($statsResult);
    $totalUsersCount = $stats['total_users'];
    $adminUsersCount = $stats['admin_users'];
    $clerkUsersCount = $stats['clerk_users'];
}
?>

<style>
/* Professional User Management Dashboard Styling */
.usermanage-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px 0;
    margin-bottom: 25px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.usermanage-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
    border: none;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    margin-bottom: 20px;
}

.usermanage-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.usermanage-card-header {
    background: linear-gradient(135deg, #e17055 0%, #fd79a8 100%);
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

.stats-card.admin {
    background: linear-gradient(135deg, #a29bfe 0%, #6c5ce7 100%);
    color: white;
}

.stats-card.danger {
    background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);
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

.usermanage-form-group {
    margin-bottom: 15px;
}

.usermanage-form-label {
    font-weight: 600;
    color: #2d3436;
    margin-bottom: 8px;
    display: block;
}

.usermanage-form-control {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 10px 12px;
    transition: all 0.3s ease;
    font-size: 14px;
}

.usermanage-form-control:focus {
    border-color: #fd79a8;
    box-shadow: 0 0 0 0.2rem rgba(253, 121, 168, 0.25);
    outline: none;
}

.usermanage-btn {
    padding: 10px 25px;
    border-radius: 25px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    border: none;
}

.usermanage-btn-primary {
    background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);
    color: white;
}

.usermanage-btn-primary:hover {
    background: linear-gradient(135deg, #e84393 0%, #2d3436 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    color: white;
}

.usermanage-btn-secondary {
    background: linear-gradient(135deg, #636e72 0%, #2d3436 100%);
    color: white;
}

.usermanage-btn-secondary:hover {
    background: linear-gradient(135deg, #2d3436 0%, #636e72 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    color: white;
}

.usermanage-btn-danger {
    background: linear-gradient(135deg, #e17055 0%, #d63031 100%);
    color: white;
    padding: 6px 15px;
    font-size: 12px;
}

.usermanage-btn-danger:hover {
    background: linear-gradient(135deg, #d63031 0%, #2d3436 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(0,0,0,0.2);
    color: white;
}

.usermanage-table {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
    border: none;
}

.usermanage-table thead {
    background: linear-gradient(135deg, #2d3436 0%, #636e72 100%);
    color: white;
}

.usermanage-table th {
    border: none;
    padding: 15px 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 12px;
}

.usermanage-table td {
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
    color: #fd79a8;
    margin-right: 8px;
}

.user-count-badge {
    background: linear-gradient(135deg, #e17055 0%, #fd79a8 100%);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.delete-warning {
    background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);
    color: white;
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 14px;
}

.danger-zone {
    border: 2px dashed #e17055;
    background: rgba(225, 112, 85, 0.1);
    border-radius: 10px;
    padding: 15px;
}

@media (max-width: 768px) {
    .stats-number {
        font-size: 2rem;
    }
    
    .usermanage-table {
        font-size: 12px;
    }
    
    .usermanage-table th, .usermanage-table td {
        padding: 8px;
    }
}
</style>

<div class="container-fluid">
    <!-- Header Section -->
    <div class="usermanage-header text-center">
        <h1 class="display-4 mb-3">🗑️ User Management & Deletion</h1>
        <p class="lead mb-2">Advanced User Management Dashboard for SLPA</p>
        <small class="text-light">Search, filter, and manage user accounts with secure deletion capabilities</small>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-professional alert-dismissible fade show">
            <strong><?= $messageType == 'success' ? 'Success!' : ($messageType == 'warning' ? 'Warning!' : 'Error!') ?></strong> <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Warning Notice -->
    <div class="delete-warning">
        <strong><i class="fas fa-exclamation-triangle"></i> Important Notice:</strong> 
        This is a critical administrative function. User deletion is permanent and cannot be undone. 
        Users with active sessions cannot be deleted for security reasons.
    </div>

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
            <div class="stats-card admin">
                <div class="stats-number"><?= $adminUsersCount ?></div>
                <div class="stats-label">
                    <i class="fas fa-user-shield"></i> Admin Users
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="stats-card danger">
                <div class="stats-number"><?= count($users) ?></div>
                <div class="stats-label">
                    <i class="fas fa-search"></i> Search Results
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="usermanage-card">
                <div class="usermanage-card-header">
                    <h4 class="mb-0"><i class="fas fa-search"></i> Advanced User Search & Filter</h4>
                </div>
                <div class="card-body p-3">
                    <form method="post" action="" class="row g-3">
                        <div class="col-lg-3">
                            <label class="usermanage-form-label" for="employee_id">
                                <i class="fas fa-id-badge search-icon"></i>Employee ID
                            </label>
                            <input type="text" id="employee_id" name="employee_id" 
                                   class="form-control usermanage-form-control" 
                                   placeholder="Search by Employee ID..." 
                                   value="<?= htmlspecialchars($searchEmployeeID) ?>">
                        </div>
                        <div class="col-lg-3">
                            <label class="usermanage-form-label" for="division">
                                <i class="fas fa-building search-icon"></i>Division
                            </label>
                            <select id="division" name="division" class="form-select usermanage-form-control">
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
                            <label class="usermanage-form-label" for="role">
                                <i class="fas fa-user-shield search-icon"></i>Role
                            </label>
                            <select id="role" name="role" class="form-select usermanage-form-control">
                                <option value="">All Roles</option>
                                <option value="Super_Ad" <?= $searchRole == 'Super_Ad' ? 'selected' : '' ?>>🔐 Super Admin</option>
                                <option value="Administration" <?= $searchRole == 'Administration' ? 'selected' : '' ?>>👨‍💼 Administration</option>
                                <option value="Administration_clerk" <?= $searchRole == 'Administration_clerk' ? 'selected' : '' ?>>📋 Admin Clerk</option>
                                <option value="clerk" <?= $searchRole == 'clerk' ? 'selected' : '' ?>>📝 Clerk</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="usermanage-form-label" style="opacity: 0;">Search</label>
                            <button type="submit" class="btn usermanage-btn usermanage-btn-primary w-100">
                                <i class="fas fa-search"></i> Search Users
                            </button>
                        </div>
                    </form>
                    <div class="mt-3">
                        <a href="userManage.php" class="btn usermanage-btn usermanage-btn-secondary btn-sm">
                            <i class="fas fa-refresh"></i> Clear All Filters
                        </a>
                        <?php if (!empty($searchRole)): ?>
                            <span class="ms-3 text-muted">
                                <strong>Found <?= $totalCount ?> users with role "<?= htmlspecialchars($searchRole) ?>"</strong>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Management Table -->
    <div class="row">
        <div class="col-12">
            <div class="usermanage-card danger-zone">
                <div class="usermanage-card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-trash-alt"></i> User Management & Deletion 
                        <span class="user-count-badge ms-2"><?= count($users) ?> users found</span>
                    </h4>
                </div>
                <div class="card-body p-0">
                    <?php if (count($users) > 0): ?>
                        <div class="table-responsive">
                            <table class="table usermanage-table mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Employee ID</th>
                                        <th>Division</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>⚠️ Danger Zone</th>
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
                                                        <i class="fas fa-circle"></i> Active Session
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-circle"></i> Inactive
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($isActive): ?>
                                                    <button class="btn usermanage-btn-danger" disabled title="Cannot delete: User has active session">
                                                        <i class="fas fa-lock"></i> Protected
                                                    </button>
                                                <?php else: ?>
                                                    <a href="userManage.php?delete_id=<?= urlencode($user['id']) ?>" 
                                                       class="btn usermanage-btn-danger" 
                                                       onclick="return confirmDeletion('<?= htmlspecialchars($user['employee_ID']) ?>')"
                                                       title="Permanently delete this user">
                                                        <i class="fas fa-trash-alt"></i> Delete
                                                    </a>
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
                                    No users match your current search criteria. Try adjusting your filters.
                                <?php else: ?>
                                    No users are currently registered in the system.
                                <?php endif; ?>
                            </p>
                            <a href="userList.php" class="btn usermanage-btn usermanage-btn-primary">
                                <i class="fas fa-list"></i> View All Users
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Enhanced User Management functionality
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
    }, 6000);

    // Enhanced search functionality
    const searchInput = document.getElementById('employee_id');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            if (this.value.length > 0) {
                this.style.borderColor = '#fd79a8';
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
    const tableRows = document.querySelectorAll('.usermanage-table tbody tr');
    tableRows.forEach(function(row) {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#fff5f5';
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

// Enhanced deletion confirmation
function confirmDeletion(employeeId) {
    return confirm(
        `⚠️ CRITICAL ACTION WARNING ⚠️\n\n` +
        `You are about to PERMANENTLY DELETE the user account for:\n` +
        `Employee ID: ${employeeId}\n\n` +
        `This action cannot be undone and will:\n` +
        `• Remove all user data permanently\n` +
        `• Delete login history\n` +
        `• Revoke all access permissions\n\n` +
        `Are you absolutely certain you want to proceed?\n\n` +
        `Click OK to DELETE or Cancel to abort.`
    );
}
</script>

<?php
include('includes/scripts.php');
include('includes/footer.php');

ob_end_flush(); 
?>