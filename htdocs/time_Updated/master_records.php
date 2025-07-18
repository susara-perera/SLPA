<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); 
    exit();
}

include('./dbc.php');
include('includes/header2.php');
include('includes/navbar.php');
include('includes/check_access.php'); 

// Define the page name
$page = 'master_records.php';

// Check if the user has access to this page
if (!hasAccess($page)) {
    echo "<div class='container'><div class='row mx-md-n8'><div class='col px-md-5'><h1>Access Denied</h1><p>You do not have permission to access this page.</p></div></div></div>";
    include('includes/scripts.php');
    include('includes/footer.php');
    exit();
}

// Get statistics for dashboard
$statsQuery = "SELECT 
    COUNT(*) as total_employees,
    SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_employees,
    SUM(CASE WHEN status = 'Inactive' THEN 1 ELSE 0 END) as inactive_employees,
    COUNT(DISTINCT division) as total_divisions
    FROM employees";
$statsResult = mysqli_query($connect, $statsQuery);
$stats = mysqli_fetch_assoc($statsResult);

$totalEmployees = $stats['total_employees'] ?? 0;
$activeEmployees = $stats['active_employees'] ?? 0;
$inactiveEmployees = $stats['inactive_employees'] ?? 0;
$totalDivisions = $stats['total_divisions'] ?? 0;

// Enhanced deletion with security measures
$deleteSuccess = false;
$deleteError = false;
if (isset($_GET['delete_id']) && isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    $delete_id = $_GET['delete_id'];
    
    // Additional security: Check if employee exists and get details for logging
    $checkSql = "SELECT employee_name, status FROM employees WHERE employee_ID = ?";
    $checkStmt = mysqli_prepare($connect, $checkSql);
    if ($checkStmt) {
        mysqli_stmt_bind_param($checkStmt, "i", $delete_id);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        $employeeToDelete = mysqli_fetch_assoc($checkResult);
        mysqli_stmt_close($checkStmt);
        
        if ($employeeToDelete) {
            // Perform the deletion
            $deleteSql = "DELETE FROM employees WHERE employee_ID = ?";
            $stmt = mysqli_prepare($connect, $deleteSql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $delete_id);
                if (mysqli_stmt_execute($stmt)) {
                    $success_message = "Employee '{$employeeToDelete['employee_name']}' (ID: {$delete_id}) has been successfully deleted from the system.";
                    $deleteSuccess = true;
                } else {
                    $error_message = "Failed to delete employee. Database error occurred.";
                    $deleteError = true;
                }
                mysqli_stmt_close($stmt);
            } else {
                $error_message = "SQL preparation error: " . mysqli_error($connect);
                $deleteError = true;
            }
        } else {
            $error_message = "Employee not found or already deleted.";
            $deleteError = true;
        }
    }
}

// Handle search and filters
$search_id = '';
$search_name = '';
$search_division = '';
$search_status = '';

if (isset($_GET['search_id'])) {
    $search_id = trim($_GET['search_id']);
}
if (isset($_GET['search_name'])) {
    $search_name = trim($_GET['search_name']);
}
if (isset($_GET['search_division'])) {
    $search_division = $_GET['search_division'];
}
if (isset($_GET['search_status'])) {
    $search_status = $_GET['search_status'];
}

// Get all divisions for filter dropdown
$divisionsQuery = "SELECT division_id, division_name FROM divisions ORDER BY division_name";
$divisionsResult = mysqli_query($connect, $divisionsQuery);
$divisions = [];
while ($row = mysqli_fetch_assoc($divisionsResult)) {
    $divisions[] = $row;
}
?>

<style>
/* Professional Employee Management Dashboard Styling - Matching Master Pages */
.management-header {
    background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
    color: white;
    padding: 25px 0;
    margin-bottom: 25px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.management-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
    border: none;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    margin-bottom: 20px;
}

.management-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.management-card-header {
    background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
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

.stats-card.inactive {
    background: linear-gradient(135deg, #e17055 0%, #d63031 100%);
    color: white;
}

.stats-card.divisions {
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

.search-form-group {
    margin-bottom: 20px;
}

.search-form-label {
    font-weight: 600;
    color: #2d3436;
    margin-bottom: 8px;
    display: block;
    font-size: 14px;
}

.search-form-control {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 12px 15px;
    transition: all 0.3s ease;
    font-size: 14px;
    width: 100%;
}

.search-form-control:focus {
    border-color: #00b894;
    box-shadow: 0 0 0 0.2rem rgba(0, 184, 148, 0.25);
    outline: none;
}

.management-btn {
    padding: 12px 30px;
    border-radius: 25px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    border: none;
}

.management-btn-primary {
    background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
    color: white;
}

.management-btn-primary:hover {
    background: linear-gradient(135deg, #00a085 0%, #2d3436 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    color: white;
}

.management-btn-secondary {
    background: linear-gradient(135deg, #636e72 0%, #2d3436 100%);
    color: white;
}

.management-btn-secondary:hover {
    background: linear-gradient(135deg, #2d3436 0%, #636e72 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    color: white;
}

.management-btn-info {
    background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    color: white;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 12px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
    margin-right: 5px;
}

.management-btn-info:hover {
    background: linear-gradient(135deg, #0984e3 0%, #2d3436 100%);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    color: white;
    text-decoration: none;
}

.management-btn-danger {
    background: linear-gradient(135deg, #e17055 0%, #d63031 100%);
    color: white;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 12px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

.management-btn-danger:hover {
    background: linear-gradient(135deg, #d63031 0%, #2d3436 100%);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    color: white;
    text-decoration: none;
}

.management-table {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
    margin-top: 20px;
}

.management-table thead {
    background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
    color: white;
}

.management-table thead th {
    border: none;
    padding: 15px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.management-table tbody tr {
    border-bottom: 1px solid #f8f9fa;
    transition: background-color 0.3s ease;
}

.management-table tbody tr:hover {
    background-color: rgba(0, 184, 148, 0.1);
}

.management-table tbody td {
    padding: 15px;
    vertical-align: middle;
    border: none;
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

.form-icon {
    color: #00b894;
    margin-right: 8px;
}

.status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-active {
    background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
    color: white;
}

.status-inactive {
    background: linear-gradient(135deg, #e17055 0%, #d63031 100%);
    color: white;
}

.search-section {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
}

.search-header {
    background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
    color: white;
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: center;
}

.no-records {
    text-align: center;
    padding: 40px;
    color: #636e72;
    font-style: italic;
}

.danger-zone {
    background: linear-gradient(135deg, rgba(231, 112, 85, 0.1) 0%, rgba(214, 48, 49, 0.1) 100%);
    border: 2px solid #e17055;
    border-radius: 15px;
    padding: 20px;
    margin-top: 20px;
}

.danger-zone-header {
    color: #d63031;
    font-weight: 700;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.delete-confirmation-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.delete-confirmation-content {
    background: white;
    border-radius: 15px;
    padding: 30px;
    max-width: 500px;
    width: 90%;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.confirmation-icon {
    font-size: 4rem;
    color: #e17055;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .stats-number {
        font-size: 2rem;
    }
    
    .search-form-control {
        padding: 10px 12px;
        font-size: 13px;
    }
    
    .management-btn {
        padding: 10px 25px;
    }
    
    .management-table {
        font-size: 14px;
    }
    
    .management-btn-info, .management-btn-danger {
        padding: 6px 12px;
        font-size: 11px;
    }
}
</style>

<div class="container-fluid">
    <!-- Header Section -->
    <div class="management-header text-center">
        <h1 class="display-4 mb-3">🗂️ Employee Records Management</h1>
        <p class="lead mb-2">Comprehensive Employee Database Administration for SLPA</p>
        <small class="text-light">Search, view, and manage employee records with advanced administrative controls</small>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-professional alert-dismissible fade show">
            <strong><i class="fas fa-check-circle"></i> Success!</strong> <?= htmlspecialchars($success_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-professional alert-dismissible fade show">
            <strong><i class="fas fa-exclamation-triangle"></i> Error!</strong> <?= htmlspecialchars($error_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-3">
        <div class="col-lg-3 col-md-6">
            <div class="stats-card total">
                <div class="stats-number"><?= $totalEmployees ?></div>
                <div class="stats-label">
                    <i class="fas fa-users"></i> Total Employees
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stats-card active">
                <div class="stats-number"><?= $activeEmployees ?></div>
                <div class="stats-label">
                    <i class="fas fa-user-check"></i> Active Employees
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stats-card inactive">
                <div class="stats-number"><?= $inactiveEmployees ?></div>
                <div class="stats-label">
                    <i class="fas fa-user-times"></i> Inactive Employees
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stats-card divisions">
                <div class="stats-number"><?= $totalDivisions ?></div>
                <div class="stats-label">
                    <i class="fas fa-sitemap"></i> Total Divisions
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Search Section -->
    <div class="search-section">
        <div class="search-header">
            <h5 class="mb-0"><i class="fas fa-search"></i> Advanced Employee Search & Filters</h5>
        </div>
        
        <form method="GET" action="" id="searchForm">
            <div class="row">
                <div class="col-md-3">
                    <div class="search-form-group">
                        <label class="search-form-label" for="search_id">
                            <i class="fas fa-id-badge form-icon"></i>Employee ID
                        </label>
                        <input type="text" id="search_id" name="search_id" 
                               class="form-control search-form-control" 
                               placeholder="Enter Employee ID"
                               value="<?= htmlspecialchars($search_id) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="search-form-group">
                        <label class="search-form-label" for="search_name">
                            <i class="fas fa-user form-icon"></i>Employee Name
                        </label>
                        <input type="text" id="search_name" name="search_name" 
                               class="form-control search-form-control" 
                               placeholder="Enter Employee Name"
                               value="<?= htmlspecialchars($search_name) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="search-form-group">
                        <label class="search-form-label" for="search_division">
                            <i class="fas fa-sitemap form-icon"></i>Division
                        </label>
                        <select name="search_division" id="search_division" class="form-select search-form-control">
                            <option value="">All Divisions</option>
                            <?php foreach ($divisions as $division): ?>
                                <option value="<?= htmlspecialchars($division['division_id']) ?>"
                                        <?= ($search_division == $division['division_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($division['division_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="search-form-group">
                        <label class="search-form-label" for="search_status">
                            <i class="fas fa-toggle-on form-icon"></i>Status
                        </label>
                        <select name="search_status" id="search_status" class="form-select search-form-control">
                            <option value="">All Status</option>
                            <option value="Active" <?= ($search_status == 'Active') ? 'selected' : '' ?>>✅ Active</option>
                            <option value="Inactive" <?= ($search_status == 'Inactive') ? 'selected' : '' ?>>❌ Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <button type="submit" class="btn management-btn management-btn-primary w-100">
                        <i class="fas fa-search"></i> Search Records
                    </button>
                </div>
                <div class="col-md-6">
                    <a href="master_records.php" class="btn management-btn management-btn-secondary w-100">
                        <i class="fas fa-refresh"></i> Clear Filters
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Section -->
    <div class="management-card">
        <div class="management-card-header">
            <h4 class="mb-0">
                <i class="fas fa-table"></i> Employee Management Records 
                <span class="badge bg-light text-dark ms-2" id="recordCount">Loading...</span>
            </h4>
        </div>
        <div class="card-body p-0">
            <table class="table management-table mb-0">
                <thead>
                    <tr>
                        <th scope="col"><i class="fas fa-id-badge"></i> Employee ID</th>
                        <th scope="col"><i class="fas fa-user"></i> Name</th>
                        <th scope="col"><i class="fas fa-sitemap"></i> Division</th>
                        <th scope="col"><i class="fas fa-briefcase"></i> Designation</th>
                        <th scope="col"><i class="fas fa-toggle-on"></i> Status</th>
                        <th scope="col"><i class="fas fa-cogs"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Build the search query with all filters
                    $conditions = [];
                    $params = [];
                    $types = '';

                    if (!empty($search_id)) {
                        $conditions[] = "e.employee_ID LIKE ?";
                        $params[] = "%{$search_id}%";
                        $types .= 's';
                    }

                    if (!empty($search_name)) {
                        $conditions[] = "e.employee_name LIKE ?";
                        $params[] = "%{$search_name}%";
                        $types .= 's';
                    }

                    if (!empty($search_division)) {
                        $conditions[] = "e.division = ?";
                        $params[] = $search_division;
                        $types .= 'i';
                    }

                    if (!empty($search_status)) {
                        $conditions[] = "e.status = ?";
                        $params[] = $search_status;
                        $types .= 's';
                    }

                    $sql = "SELECT e.employee_ID, e.employee_name, d.division_name, e.designation, e.status
                            FROM employees e
                            JOIN divisions d ON e.division = d.division_id";

                    if (!empty($conditions)) {
                        $sql .= " WHERE " . implode(" AND ", $conditions);
                    }

                    $sql .= " ORDER BY e.employee_name ASC";

                    if (!empty($conditions)) {
                        $stmt = mysqli_prepare($connect, $sql);
                        if (!empty($types)) {
                            mysqli_stmt_bind_param($stmt, $types, ...$params);
                        }
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                    } else {
                        $result = mysqli_query($connect, $sql);
                    }

                    $recordCount = 0;
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $recordCount++;
                            $statusClass = ($row['status'] == 'Active') ? 'status-active' : 'status-inactive';
                            echo "<tr>
                                <td><strong>{$row['employee_ID']}</strong></td>
                                <td>{$row['employee_name']}</td>
                                <td>{$row['division_name']}</td>
                                <td>{$row['designation']}</td>
                                <td>
                                    <span class='status-badge {$statusClass}'>
                                        {$row['status']}
                                    </span>
                                </td>
                                <td>
                                    <a href='master_details_view.php?id={$row['employee_ID']}' 
                                       class='management-btn-info'
                                       title='View Employee Details'>
                                        <i class='fas fa-eye'></i> View
                                    </a>
                                    <a href='#' 
                                       class='management-btn-danger delete-employee'
                                       data-id='{$row['employee_ID']}'
                                       data-name='{$row['employee_name']}'
                                       title='Delete Employee Record'>
                                        <i class='fas fa-trash-alt'></i> Delete
                                    </a>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr>
                            <td colspan='6' class='no-records'>
                                <i class='fas fa-search fa-3x mb-3 text-muted'></i>
                                <h5>No employees found</h5>
                                <p class='text-muted'>
                                    " . (!empty($search_id) || !empty($search_name) || !empty($search_division) || !empty($search_status) 
                                        ? "No employees match your search criteria. Try adjusting your filters." 
                                        : "No employee records available in the database.") . "
                                </p>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Danger Zone Information -->
    <div class="danger-zone">
        <div class="danger-zone-header">
            <i class="fas fa-exclamation-triangle"></i>
            <span>⚠️ Administrative Warning</span>
        </div>
        <p><strong>Important:</strong> Employee deletion is permanent and cannot be undone. This action will:</p>
        <ul>
            <li>Permanently remove the employee record from the database</li>
            <li>Delete all associated employment history</li>
            <li>Remove access to all system functionalities</li>
            <li>Cannot be reversed once confirmed</li>
        </ul>
        <p class="mb-0"><strong>Please ensure you have proper authorization before deleting any employee records.</strong></p>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="delete-confirmation-modal">
    <div class="delete-confirmation-content">
        <div class="confirmation-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h4 class="mb-3">Confirm Employee Deletion</h4>
        <p class="mb-4">Are you absolutely sure you want to delete this employee?</p>
        <div class="employee-info mb-4" style="background: #f8f9fa; padding: 15px; border-radius: 10px;">
            <strong>Employee:</strong> <span id="employeeName"></span><br>
            <strong>ID:</strong> <span id="employeeId"></span>
        </div>
        <div class="alert alert-danger mb-4">
            <strong>⚠️ Warning:</strong> This action cannot be undone. The employee record will be permanently deleted.
        </div>
        <div class="d-flex gap-3 justify-content-center">
            <button type="button" class="btn management-btn management-btn-secondary" onclick="closeDeleteModal()">
                <i class="fas fa-times"></i> Cancel
            </button>
            <a href="#" id="confirmDeleteBtn" class="btn management-btn-danger">
                <i class="fas fa-trash-alt"></i> Yes, Delete Employee
            </a>
        </div>
    </div>
</div>

<script>
// Enhanced Employee Management functionality
document.addEventListener('DOMContentLoaded', function() {
    // Update record count
    const recordCount = <?= $recordCount ?>;
    document.getElementById('recordCount').textContent = recordCount + ' Records Found';

    // Auto-dismiss alerts
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(function(alert) {
            if (alert.classList.contains('show')) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        });
    }, 5000);

    // Delete confirmation modal functionality
    const deleteButtons = document.querySelectorAll('.delete-employee');
    const deleteModal = document.getElementById('deleteModal');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const employeeNameSpan = document.getElementById('employeeName');
    const employeeIdSpan = document.getElementById('employeeId');

    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const employeeId = this.getAttribute('data-id');
            const employeeName = this.getAttribute('data-name');
            
            employeeNameSpan.textContent = employeeName;
            employeeIdSpan.textContent = employeeId;
            
            confirmDeleteBtn.href = `master_records.php?delete_id=${employeeId}&confirm=yes`;
            
            deleteModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
    });

    // Real-time search functionality
    const searchInputs = document.querySelectorAll('.search-form-control');
    searchInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                this.style.borderColor = '#00b894';
                this.style.backgroundColor = 'rgba(0, 184, 148, 0.1)';
            } else {
                this.style.borderColor = '#e9ecef';
                this.style.backgroundColor = 'white';
            }
        });
    });

    // Form validation and submission
    const searchForm = document.getElementById('searchForm');
    searchForm.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
        submitBtn.disabled = true;
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // ESC to close modal
        if (e.key === 'Escape') {
            closeDeleteModal();
        }
        
        // Ctrl + F to focus on name search
        if (e.ctrlKey && e.key === 'f') {
            e.preventDefault();
            document.getElementById('search_name').focus();
        }
    });
});

// Close delete modal function
function closeDeleteModal() {
    const deleteModal = document.getElementById('deleteModal');
    deleteModal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>