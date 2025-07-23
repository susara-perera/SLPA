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
$page = 'section_Manage.php';

// Check if the user has access to this page
if (!hasAccess($page)) {
    echo "<div class='container'><div class='row mx-md-n8'><div class='col px-md-5'><h1>Access Denied</h1><p>You do not have permission to access this page.</p></div></div></div>";
    include('includes/scripts.php');
    include('includes/footer.php');
    ob_end_flush(); 
    exit();
}

// Initialize variables
$divisions = [];
$sections = [];
$selected_division = '';
$selected_division_name = '';
$error_message = '';
$success_message = '';

// Handle section deletion
if (isset($_GET['delete_section_id'])) {
    $section_id = $_GET['delete_section_id'];

    // Prepare and execute delete statement
    $deleteSql = "DELETE FROM sections WHERE section_id = ?";
    $stmt = mysqli_prepare($connect, $deleteSql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $section_id);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            $success_message = "Section deleted successfully.";
        } else {
            mysqli_stmt_close($stmt);
            $error_message = "Failed to delete section: " . mysqli_stmt_error($stmt);
        }
    } else {
        $error_message = "SQL error: " . mysqli_error($connect);
    }
}

// Fetch all divisions for the dropdown
$divisionSql = "SELECT division_id, division_name FROM divisions ORDER BY division_name";
$result = mysqli_query($connect, $divisionSql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $divisions[] = $row;
    }
} else {
    $error_message = "Failed to fetch divisions: " . mysqli_error($connect);
}

// Fetch sections based on selected division or all sections
if (isset($_GET['division_id']) && !empty($_GET['division_id'])) {
    $selected_division = $_GET['division_id'];

    // Fetch sections for the selected division
    $sectionSql = "SELECT s.*, d.division_name FROM sections s 
                   JOIN divisions d ON s.division_id = d.division_id 
                   WHERE s.division_id = ? 
                   ORDER BY s.section_name";
    $stmt = mysqli_prepare($connect, $sectionSql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $selected_division);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $sections[] = $row;
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "SQL error: " . mysqli_error($connect);
    }

    // Fetch the division name for the selected division
    $divisionNameSql = "SELECT division_name FROM divisions WHERE division_id = ?";
    $stmt = mysqli_prepare($connect, $divisionNameSql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $selected_division);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            $selected_division_name = $row['division_name'];
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "SQL error: " . mysqli_error($connect);
    }
} else {
    // Fetch all sections
    $sectionSql = "SELECT s.*, d.division_name FROM sections s 
                   JOIN divisions d ON s.division_id = d.division_id 
                   ORDER BY d.division_name, s.section_name";
    $result = mysqli_query($connect, $sectionSql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $sections[] = $row;
        }
    } else {
        $error_message = "Failed to fetch sections: " . mysqli_error($connect);
    }
}

// Calculate statistics
$totalSections = count($sections);
$totalDivisions = count($divisions);
$sectionsInSelectedDivision = $selected_division ? count($sections) : 0;

mysqli_close($connect);
?>

<style>
.card-custom {
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border: none;
    background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
    color: white;
}

.card-body-custom {
    background: white;
    border-radius: 0 0 15px 15px;
    color: #333;
}

.stats-card {
    background: linear-gradient(135deg, rgba(111, 66, 193, 0.1) 0%, rgba(90, 50, 163, 0.1) 100%);
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 20px;
    border-left: 4px solid #6f42c1;
    transition: all 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(111, 66, 193, 0.2);
}

.filter-card {
    background: linear-gradient(135deg, rgba(111, 66, 193, 0.05) 0%, rgba(90, 50, 163, 0.05) 100%);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid rgba(111, 66, 193, 0.2);
}

.table-custom {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.table-custom thead {
    background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
    color: white;
}

.table-custom thead th {
    border: none;
    padding: 15px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.table-custom tbody tr {
    transition: all 0.3s ease;
}

.table-custom tbody tr:hover {
    background: linear-gradient(135deg, rgba(111, 66, 193, 0.05) 0%, rgba(90, 50, 163, 0.05) 100%);
    transform: scale(1.01);
}

.table-custom tbody td {
    padding: 15px;
    border: none;
    border-bottom: 1px solid #e9ecef;
}

.btn-primary-custom {
    background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
    border: none;
    border-radius: 10px;
    padding: 10px 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    color: white;
}

.btn-primary-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(111, 66, 193, 0.3);
    color: white;
}

.btn-edit {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    border: none;
    border-radius: 8px;
    padding: 8px 15px;
    font-weight: 600;
    transition: all 0.3s ease;
    color: white;
    font-size: 12px;
}

.btn-edit:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(243, 156, 18, 0.3);
    color: white;
}

.btn-delete {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    border: none;
    border-radius: 8px;
    padding: 8px 15px;
    font-weight: 600;
    transition: all 0.3s ease;
    color: white;
    font-size: 12px;
}

.btn-delete:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
    color: white;
}

.btn-secondary-custom {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    border: none;
    border-radius: 10px;
    padding: 10px 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    color: white;
}

.btn-secondary-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(108, 117, 125, 0.3);
    color: white;
}

.btn-success-custom {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    border: none;
    border-radius: 10px;
    padding: 12px 30px;
    font-weight: 600;
    transition: all 0.3s ease;
    color: white;
}

.btn-success-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
    color: white;
}

.form-control-custom {
    border-radius: 10px;
    border: 2px solid #e9ecef;
    padding: 10px 15px;
    transition: all 0.3s ease;
}

.form-control-custom:focus {
    border-color: #6f42c1;
    box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
}

.icon-wrapper {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: white;
    font-size: 24px;
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
    background: linear-gradient(135deg, rgba(111, 66, 193, 0.05) 0%, rgba(90, 50, 163, 0.05) 100%);
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
    background: linear-gradient(135deg, rgba(111, 66, 193, 0.1) 0%, rgba(90, 50, 163, 0.1) 100%);
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

.management-svg {
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

.no-data {
    text-align: center;
    padding: 40px;
    color: #6c757d;
}

.no-data i {
    font-size: 48px;
    margin-bottom: 20px;
    opacity: 0.5;
}

/* Stats icons */
.stats-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    margin-right: 15px;
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
    background: linear-gradient(90deg, transparent 0%, rgba(111, 66, 193, 0.1) 50%, transparent 100%);
    height: 1px;
}

.line-1 { top: 25%; width: 150px; right: 20%; }
.line-2 { top: 45%; width: 100px; right: 15%; }
.line-3 { top: 65%; width: 120px; right: 25%; }
.line-4 { top: 85%; width: 80px; right: 30%; }

.action-buttons {
    background: linear-gradient(135deg, rgba(111, 66, 193, 0.05) 0%, rgba(90, 50, 163, 0.05) 100%);
    border-radius: 10px;
    padding: 20px;
    margin-top: 20px;
    border: 1px solid rgba(111, 66, 193, 0.1);
}
</style>

<div class="content-wrapper">
    <!-- Decorative Elements -->
    <div class="decorative-elements">
        <div class="geometric-shape shape-1"></div>
        <div class="geometric-shape shape-2"></div>
        <div class="geometric-shape shape-3"></div>
        <div class="geometric-shape shape-4"></div>
        
        <!-- Management/Settings SVG -->
        <svg class="management-svg" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
            <!-- Settings gear -->
            <circle cx="100" cy="100" r="25" fill="none" stroke="currentColor" stroke-width="3" opacity="0.3"/>
            <circle cx="100" cy="100" r="8" fill="currentColor" opacity="0.4"/>
            
            <!-- Gear teeth -->
            <rect x="97" y="65" width="6" height="10" fill="currentColor" opacity="0.3"/>
            <rect x="97" y="125" width="6" height="10" fill="currentColor" opacity="0.3"/>
            <rect x="65" y="97" width="10" height="6" fill="currentColor" opacity="0.3"/>
            <rect x="125" y="97" width="10" height="6" fill="currentColor" opacity="0.3"/>
            
            <!-- Diagonal teeth -->
            <rect x="85" y="75" width="8" height="6" fill="currentColor" opacity="0.3" transform="rotate(45 89 78)"/>
            <rect x="107" y="75" width="8" height="6" fill="currentColor" opacity="0.3" transform="rotate(-45 111 78)"/>
            <rect x="85" y="119" width="8" height="6" fill="currentColor" opacity="0.3" transform="rotate(-45 89 122)"/>
            <rect x="107" y="119" width="8" height="6" fill="currentColor" opacity="0.3" transform="rotate(45 111 122)"/>
            
            <!-- Management symbols -->
            <rect x="50" y="40" width="20" height="3" rx="1" fill="currentColor" opacity="0.3"/>
            <rect x="50" y="47" width="15" height="3" rx="1" fill="currentColor" opacity="0.3"/>
            <rect x="50" y="54" width="18" height="3" rx="1" fill="currentColor" opacity="0.3"/>
            
            <rect x="130" y="40" width="20" height="3" rx="1" fill="currentColor" opacity="0.3"/>
            <rect x="130" y="47" width="15" height="3" rx="1" fill="currentColor" opacity="0.3"/>
            <rect x="130" y="54" width="18" height="3" rx="1" fill="currentColor" opacity="0.3"/>
            
            <rect x="50" y="150" width="20" height="3" rx="1" fill="currentColor" opacity="0.3"/>
            <rect x="50" y="157" width="15" height="3" rx="1" fill="currentColor" opacity="0.3"/>
            <rect x="50" y="164" width="18" height="3" rx="1" fill="currentColor" opacity="0.3"/>
            
            <rect x="130" y="150" width="20" height="3" rx="1" fill="currentColor" opacity="0.3"/>
            <rect x="130" y="157" width="15" height="3" rx="1" fill="currentColor" opacity="0.3"/>
            <rect x="130" y="164" width="18" height="3" rx="1" fill="currentColor" opacity="0.3"/>
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
            <!-- Header Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-custom">
                        <div class="card-header text-center py-4">
                            <div class="icon-wrapper">
                                <i class="fas fa-cogs"></i>
                            </div>
                            <h3 class="card-title mb-0" style="color: white; font-weight: 600;">Section Management</h3>
                            <p class="mb-0" style="color: rgba(255,255,255,0.8);">Manage all sections across divisions</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if (!empty($error_message)): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-danger-custom">
                            <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error_message; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-success-custom">
                            <i class="fas fa-check-circle mr-2"></i><?php echo $success_message; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon">
                                <i class="fas fa-sitemap"></i>
                            </div>
                            <div>
                                <h4 class="mb-1" style="color: #6f42c1; font-weight: 600;"><?php echo $totalSections; ?></h4>
                                <p class="mb-0 text-muted">Total Sections</p>
                                <small class="text-info">Across all divisions</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div>
                                <h4 class="mb-1" style="color: #6f42c1; font-weight: 600;"><?php echo $totalDivisions; ?></h4>
                                <p class="mb-0 text-muted">Available Divisions</p>
                                <small class="text-info">With sections</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon">
                                <i class="fas fa-filter"></i>
                            </div>
                            <div>
                                <h4 class="mb-1" style="color: #6f42c1; font-weight: 600;">
                                    <?php echo $selected_division ? $sectionsInSelectedDivision : $totalSections; ?>
                                </h4>
                                <p class="mb-0 text-muted">
                                    <?php echo $selected_division ? 'In Selected Division' : 'Currently Viewing'; ?>
                                </p>
                                <small class="text-info">
                                    <?php echo $selected_division ? $selected_division_name : 'All sections'; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="filter-card">
                        <h5 class="mb-3" style="color: #6f42c1;">
                            <i class="fas fa-filter mr-2"></i>Filter by Division
                        </h5>
                        <form method="GET" action="">
                            <div class="row align-items-end">
                                <div class="col-md-8">
                                    <label for="division_id" class="form-label font-weight-bold">Select Division:</label>
                                    <select name="division_id" id="division_id" class="form-control form-control-custom" onchange="this.form.submit()">
                                        <option value="">All Divisions</option>
                                        <?php foreach ($divisions as $division): ?>
                                            <option value="<?php echo htmlspecialchars($division['division_id']); ?>" 
                                                    <?php echo ($selected_division === $division['division_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($division['division_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <a href="section_Manage.php" class="btn btn-secondary-custom">
                                        <i class="fas fa-times mr-2"></i>Clear Filter
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sections Management Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header" style="background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%); color: white;">
                            <h5 class="mb-0">
                                <i class="fas fa-sitemap mr-2"></i>
                                <?php if ($selected_division): ?>
                                    Sections in <?php echo htmlspecialchars($selected_division_name); ?>
                                <?php else: ?>
                                    All Sections Management
                                <?php endif; ?>
                            </h5>
                        </div>
                        <div class="card-body card-body-custom p-0">
                            <?php if (empty($sections)): ?>
                                <div class="no-data">
                                    <i class="fas fa-inbox"></i>
                                    <h5>No Sections Found</h5>
                                    <p>
                                        <?php if ($selected_division): ?>
                                            No sections found in the selected division.
                                        <?php else: ?>
                                            No sections have been created yet.
                                        <?php endif; ?>
                                    </p>
                                    <a href="section.php" class="btn btn-success-custom">
                                        <i class="fas fa-plus mr-2"></i>Add First Section
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-custom mb-0">
                                        <thead>
                                            <tr>
                                                <th width="10%">
                                                    <i class="fas fa-hashtag mr-2"></i>No.
                                                </th>
                                                <th width="15%">
                                                    <i class="fas fa-id-badge mr-2"></i>Section ID
                                                </th>
                                                <th width="30%">
                                                    <i class="fas fa-sitemap mr-2"></i>Section Name
                                                </th>
                                                <?php if (!$selected_division): ?>
                                                <th width="25%">
                                                    <i class="fas fa-building mr-2"></i>Division
                                                </th>
                                                <?php endif; ?>
                                                <th width="<?php echo $selected_division ? '45%' : '20%'; ?>">
                                                    <i class="fas fa-cogs mr-2"></i>Actions
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $serialNumber = 1; 
                                            foreach ($sections as $section): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge badge-primary" style="font-size: 12px; padding: 8px 12px; background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%); border: none;">
                                                            <?php echo $serialNumber++; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <strong style="color: #6f42c1;">
                                                            <?php echo htmlspecialchars($section['section_id']); ?>
                                                        </strong>
                                                    </td>
                                                    <td>
                                                        <span style="color: #333; font-weight: 500;">
                                                            <?php echo htmlspecialchars($section['section_name']); ?>
                                                        </span>
                                                    </td>
                                                    <?php if (!$selected_division): ?>
                                                    <td>
                                                        <span style="color: #6c757d;">
                                                            <i class="fas fa-building mr-2"></i>
                                                            <?php echo htmlspecialchars($section['division_name']); ?>
                                                        </span>
                                                    </td>
                                                    <?php endif; ?>
                                                    <td>
                                                        <a href="section_edit.php?section_id=<?php echo htmlspecialchars($section['section_id']); ?>&division_id=<?php echo htmlspecialchars($section['division_id']); ?>" 
                                                           class="btn btn-edit mr-2" title="Edit Section">
                                                            <i class="fas fa-edit mr-1"></i>Edit
                                                        </a>
                                                        <a href="?delete_section_id=<?php echo htmlspecialchars($section['section_id']); ?><?php echo $selected_division ? '&division_id=' . $selected_division : ''; ?>" 
                                                           class="btn btn-delete" 
                                                           title="Delete Section"
                                                           onclick="return confirm('Are you sure you want to delete the section \'<?php echo htmlspecialchars($section['section_name']); ?>\'? This action cannot be undone.');">
                                                            <i class="fas fa-trash mr-1"></i>Delete
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row">
                <div class="col-12">
                    <div class="action-buttons text-center">
                        <h5 class="mb-3" style="color: #6f42c1;">
                            <i class="fas fa-tools mr-2"></i>Quick Actions
                        </h5>
                        <a href="section.php" class="btn btn-success-custom btn-lg px-4 mr-3">
                            <i class="fas fa-plus mr-2"></i>Add New Section
                        </a>
                        <a href="section_List.php" class="btn btn-primary-custom btn-lg px-4 mr-3">
                            <i class="fas fa-list mr-2"></i>View Sections List
                        </a>
                        <a href="division_manage.php" class="btn btn-secondary-custom btn-lg px-4">
                            <i class="fas fa-building mr-2"></i>Manage Divisions
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
include('includes/scripts.php');
include('includes/footer.php');
ob_end_flush(); 
?>
