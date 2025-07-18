<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login if the session is not set.
    exit();
}

include('./dbc.php');
include('includes/header2.php');
include('includes/navbar.php');
include('includes/check_access.php'); // Include the check_access.php for the access control function

// Define the page name
$page = 'master_records_view.php';

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
/* Professional Employee Records Dashboard Styling - Matching Master1.php */
.records-header {
    background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
    color: white;
    padding: 25px 0;
    margin-bottom: 25px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.records-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
    border: none;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    margin-bottom: 20px;
}

.records-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.records-card-header {
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

.records-btn {
    padding: 12px 30px;
    border-radius: 25px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    border: none;
}

.records-btn-primary {
    background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
    color: white;
}

.records-btn-primary:hover {
    background: linear-gradient(135deg, #00a085 0%, #2d3436 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    color: white;
}

.records-btn-secondary {
    background: linear-gradient(135deg, #636e72 0%, #2d3436 100%);
    color: white;
}

.records-btn-secondary:hover {
    background: linear-gradient(135deg, #2d3436 0%, #636e72 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    color: white;
}

.records-btn-info {
    background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    color: white;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 12px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

.records-btn-info:hover {
    background: linear-gradient(135deg, #0984e3 0%, #2d3436 100%);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    color: white;
    text-decoration: none;
}

.records-table {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
    margin-top: 20px;
}

.records-table thead {
    background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
    color: white;
}

.records-table thead th {
    border: none;
    padding: 15px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.records-table tbody tr {
    border-bottom: 1px solid #f8f9fa;
    transition: background-color 0.3s ease;
}

.records-table tbody tr:hover {
    background-color: rgba(0, 184, 148, 0.1);
}

.records-table tbody td {
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

@media (max-width: 768px) {
    .stats-number {
        font-size: 2rem;
    }
    
    .search-form-control {
        padding: 10px 12px;
        font-size: 13px;
    }
    
    .records-btn {
        padding: 10px 25px;
    }
    
    .records-table {
        font-size: 14px;
    }
}
</style>

<div class="container-fluid">
    <!-- Header Section -->
    <div class="records-header text-center">
        <h1 class="display-4 mb-3">📊 Employee Master Records</h1>
        <p class="lead mb-2">Comprehensive Employee Database Management for SLPA</p>
        <small class="text-light">Search, filter and view detailed employee records with advanced filtering options</small>
    </div>

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
                    <button type="submit" class="btn records-btn records-btn-primary w-100">
                        <i class="fas fa-search"></i> Search Records
                    </button>
                </div>
                <div class="col-md-6">
                    <a href="master_records_view.php" class="btn records-btn records-btn-secondary w-100">
                        <i class="fas fa-refresh"></i> Clear Filters
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Section -->
    <div class="records-card">
        <div class="records-card-header">
            <h4 class="mb-0">
                <i class="fas fa-table"></i> Employee Records 
                <span class="badge bg-light text-dark ms-2" id="recordCount">Loading...</span>
            </h4>
        </div>
        <div class="card-body p-0">
            <table class="table records-table mb-0">
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
                                       class='records-btn-info'
                                       title='View Employee Details'>
                                        <i class='fas fa-eye'></i> View Details
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
</div>

<script>
// Enhanced Records Dashboard functionality
document.addEventListener('DOMContentLoaded', function() {
    // Update record count
    const recordCount = <?= $recordCount ?>;
    document.getElementById('recordCount').textContent = recordCount + ' Records Found';

    // Real-time search functionality
    const searchInputs = document.querySelectorAll('.search-form-control');
    searchInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            // Add visual feedback for active filters
            if (this.value.trim() !== '') {
                this.style.borderColor = '#00b894';
                this.style.backgroundColor = 'rgba(0, 184, 148, 0.1)';
            } else {
                this.style.borderColor = '#e9ecef';
                this.style.backgroundColor = 'white';
            }
        });
    });

    // Enhanced table interactions
    const tableRows = document.querySelectorAll('.records-table tbody tr');
    tableRows.forEach(function(row) {
        row.addEventListener('click', function(e) {
            if (!e.target.closest('a')) {
                const viewLink = this.querySelector('a[href*="master_details_view.php"]');
                if (viewLink) {
                    window.location.href = viewLink.href;
                }
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

    // Auto-submit on filter change (optional)
    const filterSelects = document.querySelectorAll('#search_division, #search_status');
    filterSelects.forEach(function(select) {
        select.addEventListener('change', function() {
            // Optional: Auto-submit form when filter changes
            // searchForm.submit();
        });
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl + F to focus on name search
        if (e.ctrlKey && e.key === 'f') {
            e.preventDefault();
            document.getElementById('search_name').focus();
        }
        
        // Escape to clear all filters
        if (e.key === 'Escape') {
            const clearBtn = document.querySelector('a[href="master_records_view.php"]');
            if (clearBtn) {
                window.location.href = clearBtn.href;
            }
        }
    });
});
</script>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>