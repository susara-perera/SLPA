<?php

include('./dbc.php');
include('includes/header.php');
include('includes/navbar.php');
include('includes/check_access.php'); 


$page = 'division_manage.php';

// Check if the user has access to this page
if (!hasAccess($page)) {
    echo "<div class='container'><div class='row mx-md-n8'><div class='col px-md-5'><h1>Access Denied</h1><p>You do not have permission to access this page.</p></div></div></div>";
    include('includes/scripts.php');
    include('includes/footer.php');
    exit();
}

$divisions = [];
$error_message = '';
$success_message = '';

// Handle division deletion
if (isset($_GET['delete_division_id'])) {
    $division_id = $_GET['delete_division_id'];

    // Prepare and execute delete statement
    $deleteSql = "DELETE FROM divisions WHERE division_id = ?";
    $stmt = mysqli_prepare($connect, $deleteSql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $division_id);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            $success_message = "Division deleted successfully.";
        } else {
            mysqli_stmt_close($stmt);
            $error_message = "Failed to delete division: " . mysqli_stmt_error($stmt);
        }
    } else {
        $error_message = "SQL error: " . mysqli_error($connect);
    }
}

// Fetch all divisions from the database
$sqlDivisions = "SELECT * FROM divisions";
$result = mysqli_query($connect, $sqlDivisions);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $divisions[] = $row;
    }
} else {
    $error_message = "Failed to fetch divisions: " . mysqli_error($connect);
}

$totalDivisions = count($divisions);

mysqli_close($connect);
?>

<style>
/* Background decorative elements */
.content-wrapper {
    position: relative;
    overflow: hidden;
}

.content-wrapper::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 400px;
    height: 100%;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
    z-index: 0;
}

.decorative-elements {
    position: absolute;
    top: 0;
    right: 0;
    width: 400px;
    height: 100vh;
    pointer-events: none;
    z-index: 1;
}

.geometric-shape {
    position: absolute;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
}

.shape-1 {
    width: 120px;
    height: 120px;
    top: 10%;
    right: 10%;
    animation: float 6s ease-in-out infinite;
}

.shape-2 {
    width: 80px;
    height: 80px;
    top: 30%;
    right: 25%;
    animation: float 8s ease-in-out infinite reverse;
}

.shape-3 {
    width: 60px;
    height: 60px;
    top: 60%;
    right: 15%;
    animation: float 7s ease-in-out infinite;
}

.shape-4 {
    width: 100px;
    height: 100px;
    top: 80%;
    right: 30%;
    animation: float 9s ease-in-out infinite reverse;
}

.organizational-svg {
    position: absolute;
    top: 20%;
    right: 5%;
    width: 200px;
    height: 200px;
    opacity: 0.1;
    animation: pulse 4s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

@keyframes pulse {
    0%, 100% { opacity: 0.1; transform: scale(1); }
    50% { opacity: 0.2; transform: scale(1.05); }
}

.content {
    position: relative;
    z-index: 2;
}

/* Additional decorative lines */
.decorative-lines {
    position: absolute;
    top: 0;
    right: 0;
    width: 300px;
    height: 100%;
    pointer-events: none;
    z-index: 1;
}

.line {
    position: absolute;
    background: linear-gradient(90deg, transparent 0%, rgba(102, 126, 234, 0.1) 50%, transparent 100%);
    height: 1px;
}

.line-1 { top: 25%; width: 150px; right: 20%; }
.line-2 { top: 45%; width: 100px; right: 15%; }
.line-3 { top: 65%; width: 120px; right: 25%; }
.line-4 { top: 85%; width: 80px; right: 30%; }

.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    color: white;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.stats-number {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 5px;
}

.stats-label {
    font-size: 1rem;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.card-custom {
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border: none;
    overflow: hidden;
}

.card-header-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px 25px;
    border: none;
}

.table-custom {
    margin-bottom: 0;
}

.table-custom thead th {
    background-color: #f8f9fa;
    border: none;
    font-weight: 600;
    color: #2c3e50;
    padding: 15px;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 1px;
}

.table-custom tbody tr {
    transition: all 0.3s ease;
}

.table-custom tbody tr:hover {
    background-color: #f8f9fa;
    transform: translateY(-1px);
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.table-custom tbody td {
    padding: 15px;
    vertical-align: middle;
    border: none;
    border-bottom: 1px solid #e9ecef;
}

.btn-action {
    margin: 0 2px;
    border-radius: 8px;
    font-size: 0.8rem;
    padding: 6px 12px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-edit {
    background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
    border: none;
    color: white;
}

.btn-edit:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
    color: white;
}

.btn-delete {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    border: none;
    color: white;
}

.btn-delete:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
    color: white;
}

.btn-add {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    border-radius: 10px;
    padding: 12px 25px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
}

.btn-add:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
}

.alert-custom {
    border-radius: 10px;
    border: none;
    padding: 15px 20px;
    margin-bottom: 20px;
    font-weight: 500;
}

.alert-success-custom {
    background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
}

.alert-danger-custom {
    background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(244, 67, 54, 0.3);
}

.breadcrumb-custom {
    background: transparent;
    padding: 0;
    margin-bottom: 20px;
}

.breadcrumb-custom .breadcrumb-item {
    color: #6c757d;
}

.breadcrumb-custom .breadcrumb-item.active {
    color: #667eea;
    font-weight: 600;
}

.serial-number {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.8rem;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 20px;
    color: #dee2e6;
}
</style>

<div class="content-wrapper">
    <!-- Decorative Elements -->
    <div class="decorative-elements">
        <div class="geometric-shape shape-1"></div>
        <div class="geometric-shape shape-2"></div>
        <div class="geometric-shape shape-3"></div>
        <div class="geometric-shape shape-4"></div>
        
        <!-- Management Dashboard SVG -->
        <svg class="organizational-svg" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
            <!-- Dashboard/Management Icon -->
            <rect x="40" y="60" width="120" height="80" rx="8" fill="currentColor" opacity="0.2"/>
            <rect x="50" y="70" width="30" height="20" rx="3" fill="currentColor" opacity="0.3"/>
            <rect x="85" y="70" width="30" height="20" rx="3" fill="currentColor" opacity="0.3"/>
            <rect x="120" y="70" width="30" height="20" rx="3" fill="currentColor" opacity="0.3"/>
            <rect x="50" y="95" width="30" height="20" rx="3" fill="currentColor" opacity="0.3"/>
            <rect x="85" y="95" width="30" height="20" rx="3" fill="currentColor" opacity="0.3"/>
            <rect x="120" y="95" width="30" height="20" rx="3" fill="currentColor" opacity="0.3"/>
            
            <!-- Management Tools -->
            <circle cx="100" cy="40" r="12" fill="currentColor" opacity="0.4"/>
            <rect x="95" y="35" width="10" height="10" rx="2" fill="white" opacity="0.8"/>
        </svg>
    </div>
    
    <!-- Decorative Lines -->
    <div class="decorative-lines">
        <div class="line line-1"></div>
        <div class="line line-2"></div>
        <div class="line line-3"></div>
        <div class="line line-4"></div>
    </div>

    <section class="content" style="padding-top: 20px;">
        <div class="container-fluid">
            <!-- Statistics Card -->
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="stats-card">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="stats-number"><?php echo $totalDivisions; ?></div>
                                <div class="stats-label">Total Divisions</div>
                            </div>
                            <div class="ml-3">
                                <i class="fas fa-building fa-2x" style="opacity: 0.7;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-custom">
                        <div class="card-header card-header-custom">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-list mr-2"></i>Division List
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-light" style="font-size: 0.9rem;">
                                    <?php echo $totalDivisions; ?> Divisions
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($error_message)): ?>
                                <div class="alert alert-danger-custom mx-3 mt-3">
                                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error_message; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($success_message)): ?>
                                <div class="alert alert-success-custom mx-3 mt-3">
                                    <i class="fas fa-check-circle mr-2"></i><?php echo $success_message; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($divisions)): ?>
                                <div class="table-responsive">
                                    <table class="table table-custom">
                                        <thead>
                                            <tr>
                                                <th style="width: 80px;">#</th>
                                                <th>Division ID</th>
                                                <th>Division Name</th>
                                                <th style="width: 150px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $serialNumber = 1; ?>
                                            <?php foreach ($divisions as $division): ?>
                                                <tr>
                                                    <td>
                                                        <span class="serial-number"><?php echo $serialNumber++; ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="font-weight-bold text-primary">
                                                            <?php echo htmlspecialchars($division['division_id']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="font-weight-500">
                                                            <?php echo htmlspecialchars($division['division_name']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="division_edit.php?division_id=<?php echo htmlspecialchars($division['division_id']); ?>" 
                                                           class="btn btn-edit btn-action" 
                                                           title="Edit Division">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="?delete_division_id=<?php echo htmlspecialchars($division['division_id']); ?>" 
                                                           class="btn btn-delete btn-action" 
                                                           title="Delete Division"
                                                           onclick="return confirm('Are you sure you want to delete this division?');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h5>No Divisions Found</h5>
                                    <p>There are no divisions in the system yet.</p>
                                    <a href="division.php" class="btn btn-add mt-3">
                                        <i class="fas fa-plus mr-2"></i>Add First Division
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>