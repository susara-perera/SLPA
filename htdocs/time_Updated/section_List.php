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
$page = 'section_List.php'; 

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

// Fetch sections based on selected division
if (isset($_GET['division_id']) && !empty($_GET['division_id'])) {
    $selected_division = $_GET['division_id'];

    // Fetch sections for the selected division
    $sectionSql = "SELECT * FROM sections WHERE division_id = ? ORDER BY section_name";
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
}

// Calculate statistics
$totalSections = count($sections);
$totalDivisions = count($divisions);

mysqli_close($connect);
?>

<style>
.card-custom {
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border: none;
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
}

.card-body-custom {
    background: white;
    border-radius: 0 0 15px 15px;
    color: #333;
}

.stats-card {
    background: linear-gradient(135deg, rgba(23, 162, 184, 0.1) 0%, rgba(19, 132, 150, 0.1) 100%);
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    border-left: 4px solid #17a2b8;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100%;
    background: linear-gradient(135deg, rgba(23, 162, 184, 0.05) 0%, rgba(19, 132, 150, 0.05) 100%);
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 35px rgba(23, 162, 184, 0.2);
    border-left-color: #138496;
}

.filter-card {
    background: linear-gradient(135deg, rgba(23, 162, 184, 0.08) 0%, rgba(19, 132, 150, 0.08) 100%);
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 25px;
    border: 1px solid rgba(23, 162, 184, 0.2);
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.table-custom {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border: none;
}

.table-custom thead {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
}

.table-custom thead th {
    border: none;
    padding: 18px 15px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 13px;
}

.table-custom tbody tr {
    transition: all 0.3s ease;
    border: none;
}

.table-custom tbody tr:hover {
    background: linear-gradient(135deg, rgba(23, 162, 184, 0.08) 0%, rgba(19, 132, 150, 0.08) 100%);
    transform: scale(1.02);
    box-shadow: 0 4px 15px rgba(23, 162, 184, 0.15);
}

.table-custom tbody td {
    padding: 18px 15px;
    border: none;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    vertical-align: middle;
}

.btn-filter {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    border: none;
    border-radius: 10px;
    padding: 12px 30px;
    font-weight: 600;
    transition: all 0.3s ease;
    color: white;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
}

.btn-filter::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn-filter:hover::before {
    left: 100%;
}

.btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(23, 162, 184, 0.3);
    color: white;
}

.btn-edit {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    border: none;
    border-radius: 8px;
    padding: 8px 16px;
    font-weight: 600;
    transition: all 0.3s ease;
    color: white;
    font-size: 12px;
    text-transform: uppercase;
}

.btn-edit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(243, 156, 18, 0.3);
    color: white;
}

.btn-delete {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    border: none;
    border-radius: 8px;
    padding: 8px 16px;
    font-weight: 600;
    transition: all 0.3s ease;
    color: white;
    font-size: 12px;
    text-transform: uppercase;
}

.btn-delete:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(220, 53, 69, 0.3);
    color: white;
}

.btn-clear {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    border: none;
    border-radius: 10px;
    padding: 12px 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    color: white;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-clear:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(108, 117, 125, 0.3);
    color: white;
}

.btn-success-custom {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    border: none;
    border-radius: 10px;
    padding: 15px 35px;
    font-weight: 600;
    transition: all 0.3s ease;
    color: white;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-success-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
    color: white;
}

.form-control-custom {
    border-radius: 10px;
    border: 2px solid #e9ecef;
    padding: 12px 15px;
    transition: all 0.3s ease;
    font-size: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    appearance: none;
    background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 4 5"><path fill="%23666" d="M2 0L0 2h4zm0 5L0 3h4z"/></svg>');
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 12px;
    padding-right: 40px;
    background-color: white;
}

.form-control-custom:focus {
    border-color: #17a2b8;
    box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
    background-color: white;
}

.icon-wrapper {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    color: white;
    font-size: 28px;
    box-shadow: 0 8px 25px rgba(23, 162, 184, 0.3);
}

.alert-custom {
    border-radius: 12px;
    border: none;
    padding: 18px 25px;
    margin-bottom: 25px;
    font-weight: 500;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.alert-success-custom {
    background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
    color: white;
    box-shadow: 0 6px 20px rgba(76, 175, 80, 0.3);
}

.alert-danger-custom {
    background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
    color: white;
    box-shadow: 0 6px 20px rgba(244, 67, 54, 0.3);
}

/* Background decorative elements */
.content-wrapper {
    position: relative;
    overflow: hidden;
    min-height: 100vh;
}

.content-wrapper::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 400px;
    height: 100%;
    background: linear-gradient(135deg, rgba(23, 162, 184, 0.05) 0%, rgba(19, 132, 150, 0.05) 100%);
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
    background: linear-gradient(135deg, rgba(23, 162, 184, 0.1) 0%, rgba(19, 132, 150, 0.1) 100%);
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

.list-svg {
    position: absolute;
    top: 15%;
    right: 5%;
    width: 220px;
    height: 220px;
    opacity: 0.08;
    animation: pulse 4s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

@keyframes pulse {
    0%, 100% { opacity: 0.08; transform: scale(1); }
    50% { opacity: 0.15; transform: scale(1.05); }
}

.content {
    position: relative;
    z-index: 2;
}

.no-data {
    text-align: center;
    padding: 60px 40px;
    color: #6c757d;
}

.no-data i {
    font-size: 64px;
    margin-bottom: 25px;
    opacity: 0.4;
    color: #17a2b8;
}

.no-data h5 {
    font-size: 24px;
    margin-bottom: 15px;
    color: #495057;
}

.no-data p {
    font-size: 16px;
    margin-bottom: 30px;
}

/* Stats icons */
.stats-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    margin-right: 20px;
    box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
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
    background: linear-gradient(90deg, transparent 0%, rgba(23, 162, 184, 0.1) 50%, transparent 100%);
    height: 1px;
}

.line-1 { top: 25%; width: 150px; right: 20%; }
.line-2 { top: 45%; width: 100px; right: 15%; }
.line-3 { top: 65%; width: 120px; right: 25%; }
.line-4 { top: 85%; width: 80px; right: 30%; }

.section-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.division-text {
    color: #17a2b8;
    font-weight: 600;
    font-size: 18px;
}

.action-buttons-container {
    background: linear-gradient(135deg, rgba(23, 162, 184, 0.05) 0%, rgba(19, 132, 150, 0.05) 100%);
    border-radius: 15px;
    padding: 25px;
    margin-top: 30px;
    text-align: center;
    border: 1px solid rgba(23, 162, 184, 0.1);
}
</style>

<div class="content-wrapper">
    <!-- Decorative Elements -->
    <div class="decorative-elements">
        <div class="geometric-shape shape-1"></div>
        <div class="geometric-shape shape-2"></div>
        <div class="geometric-shape shape-3"></div>
        <div class="geometric-shape shape-4"></div>
        
        <!-- List/Sections SVG -->
        <svg class="list-svg" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
            <!-- List items representing sections -->
            <rect x="40" y="30" width="120" height="12" rx="6" fill="currentColor" opacity="0.3"/>
            <rect x="40" y="50" width="100" height="12" rx="6" fill="currentColor" opacity="0.3"/>
            <rect x="40" y="70" width="110" height="12" rx="6" fill="currentColor" opacity="0.3"/>
            <rect x="40" y="90" width="90" height="12" rx="6" fill="currentColor" opacity="0.3"/>
            <rect x="40" y="110" width="130" height="12" rx="6" fill="currentColor" opacity="0.3"/>
            <rect x="40" y="130" width="105" height="12" rx="6" fill="currentColor" opacity="0.3"/>
            <rect x="40" y="150" width="95" height="12" rx="6" fill="currentColor" opacity="0.3"/>
            <rect x="40" y="170" width="115" height="12" rx="6" fill="currentColor" opacity="0.3"/>
            
            <!-- List bullets -->
            <circle cx="25" cy="36" r="4" fill="currentColor" opacity="0.4"/>
            <circle cx="25" cy="56" r="4" fill="currentColor" opacity="0.4"/>
            <circle cx="25" cy="76" r="4" fill="currentColor" opacity="0.4"/>
            <circle cx="25" cy="96" r="4" fill="currentColor" opacity="0.4"/>
            <circle cx="25" cy="116" r="4" fill="currentColor" opacity="0.4"/>
            <circle cx="25" cy="136" r="4" fill="currentColor" opacity="0.4"/>
            <circle cx="25" cy="156" r="4" fill="currentColor" opacity="0.4"/>
            <circle cx="25" cy="176" r="4" fill="currentColor" opacity="0.4"/>
            
            <!-- Document outline -->
            <rect x="15" y="15" width="170" height="175" rx="8" fill="none" stroke="currentColor" stroke-width="2" opacity="0.2"/>
            
            <!-- Header line -->
            <line x1="25" y1="25" x2="175" y2="25" stroke="currentColor" stroke-width="2" opacity="0.3"/>
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
                                <i class="fas fa-list-alt"></i>
                            </div>
                            <h3 class="card-title mb-0" style="color: white; font-weight: 600;">Sections Directory</h3>
                            <p class="mb-0" style="color: rgba(255,255,255,0.8);">Browse and view sections organized by divisions</p>
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
                <div class="col-md-6">
                    <div class="stats-card">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon">
                                <i class="fas fa-sitemap"></i>
                            </div>
                            <div>
                                <h4 class="mb-1" style="color: #17a2b8; font-weight: 600;"><?php echo $totalSections; ?></h4>
                                <p class="mb-0 text-muted">Sections Found</p>
                                <?php if (!empty($selected_division_name)): ?>
                                    <small class="text-info">In <?php echo htmlspecialchars($selected_division_name); ?></small>
                                <?php else: ?>
                                    <small class="text-info">Select a division to filter</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stats-card">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div>
                                <h4 class="mb-1" style="color: #17a2b8; font-weight: 600;"><?php echo $totalDivisions; ?></h4>
                                <p class="mb-0 text-muted">Available Divisions</p>
                                <small class="text-info">Choose one to explore</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="filter-card">
                        <h5 class="mb-3" style="color: #17a2b8; font-weight: 600;">
                            <i class="fas fa-filter mr-2"></i>Filter by Division
                        </h5>
                        <form method="GET" action="">
                            <div class="row align-items-end">
                                <div class="col-md-8">
                                    <label for="division_id" class="form-label font-weight-bold" style="color: #495057; font-size: 15px;">
                                        <i class="fas fa-building text-info mr-2"></i>Select Division:
                                    </label>
                                    <select name="division_id" id="division_id" class="form-control form-control-custom" onchange="this.form.submit()">
                                        <option value=""></option>
                                        <?php foreach ($divisions as $division): ?>
                                            <option value="<?php echo htmlspecialchars($division['division_id']); ?>" 
                                                    <?php echo ($selected_division === $division['division_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($division['division_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text" style="color: #6c757d; font-size: 13px;">
                                        Choose a division to view its sections
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <a href="section_List.php" class="btn btn-clear">
                                        <i class="fas fa-times mr-2"></i>Clear Filter
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sections Table -->
            <?php if (!empty($selected_division_name)): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; padding: 20px 25px;">
                                <h5 class="mb-0" style="font-weight: 600;">
                                    <i class="fas fa-sitemap mr-2"></i>
                                    Sections in <span class="division-text" style="color: #fff; text-decoration: underline;"><?php echo htmlspecialchars($selected_division_name); ?></span>
                                </h5>
                            </div>
                            <div class="card-body card-body-custom p-0">
                                <?php if (empty($sections)): ?>
                                    <div class="no-data">
                                        <i class="fas fa-folder-open"></i>
                                        <h5>No Sections Found</h5>
                                        <p>No sections have been created for this division yet.</p>
                                        <a href="section.php" class="btn btn-success-custom">
                                            <i class="fas fa-plus mr-2"></i>Create First Section
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-custom mb-0">
                                            <thead>
                                                <tr>
                                                    <th width="15%">
                                                        <i class="fas fa-hashtag mr-2"></i>Serial No.
                                                    </th>
                                                    <th width="20%">
                                                        <i class="fas fa-id-badge mr-2"></i>Section ID
                                                    </th>
                                                    <th width="40%">
                                                        <i class="fas fa-sitemap mr-2"></i>Section Name
                                                    </th>
                                                    <th width="25%">
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
                                                            <span class="section-badge">
                                                                <?php echo $serialNumber++; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <strong style="color: #17a2b8; font-size: 16px;">
                                                                #<?php echo htmlspecialchars($section['section_id']); ?>
                                                            </strong>
                                                        </td>
                                                        <td>
                                                            <span style="color: #495057; font-weight: 500; font-size: 16px;">
                                                                <i class="fas fa-sitemap text-info mr-2"></i>
                                                                <?php echo htmlspecialchars($section['section_name']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="section_edit.php?section_id=<?php echo htmlspecialchars($section['section_id']); ?>&division_id=<?php echo htmlspecialchars($section['division_id']); ?>" 
                                                               class="btn btn-edit mr-2" title="Edit Section">
                                                                <i class="fas fa-edit mr-1"></i>Edit
                                                            </a>
                                                            <a href="?delete_section_id=<?php echo htmlspecialchars($section['section_id']); ?>&division_id=<?php echo htmlspecialchars($selected_division); ?>" 
                                                               class="btn btn-delete" 
                                                               title="Delete Section"
                                                               onclick="return confirm('Are you sure you want to delete the section \'<?php echo htmlspecialchars($section['section_name']); ?>\'?\n\nThis action cannot be undone.');">
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
            <?php else: ?>
                <div class="row">
                    <div class="col-12">
                        <div class="no-data">
                            <i class="fas fa-hand-point-up"></i>
                            <h5>Choose a Division</h5>
                            <p>Please select a division from the dropdown above to view its sections and start exploring.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="row">
                <div class="col-12">
                    <div class="action-buttons-container">
                        <h5 class="mb-4" style="color: #17a2b8; font-weight: 600;">
                            <i class="fas fa-tools mr-2"></i>Quick Actions
                        </h5>
                        <a href="section.php" class="btn btn-success-custom btn-lg px-4 mr-3">
                            <i class="fas fa-plus mr-2"></i>Add New Section
                        </a>
                        <a href="section_Manage.php" class="btn btn-filter btn-lg px-4">
                            <i class="fas fa-cogs mr-2"></i>Manage Sections
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
