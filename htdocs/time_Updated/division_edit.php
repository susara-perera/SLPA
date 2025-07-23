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

$division_id = '';
$division_name = '';
$error_message = '';
$success_message = '';

// Fetch the division details for editing
if (isset($_GET['division_id'])) {
    $division_id = $_GET['division_id'];

    $fetchSql = "SELECT * FROM divisions WHERE division_id = ?";
    $stmt = mysqli_prepare($connect, $fetchSql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $division_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            $division_name = $row['division_name'];
        } else {
            $error_message = "Division not found.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "SQL error: " . mysqli_error($connect);
    }
}

// Update division details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_division_name = $_POST['division_name'];
    $division_id = $_POST['division_id'];

    // Check if the new division name already exists
    $checkSql = "SELECT COUNT(*) AS count FROM divisions WHERE division_name = ? AND division_id != ?";
    $stmt = mysqli_prepare($connect, $checkSql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $new_division_name, $division_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        if ($row['count'] > 0) {
            $error_message = "Division name already exists.";
        } else {
            // Proceed with the update if no duplicate name exists
            $updateSql = "UPDATE divisions SET division_name = ? WHERE division_id = ?";
            $stmt = mysqli_prepare($connect, $updateSql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "si", $new_division_name, $division_id);
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    $success_message = "Division updated successfully.";
                    $division_name = $new_division_name; // Update the display name
                } else {
                    mysqli_stmt_close($stmt);
                    $error_message = "Failed to update division: " . mysqli_stmt_error($stmt);
                }
            } else {
                $error_message = "SQL error: " . mysqli_error($connect);
            }
        }
    } else {
        $error_message = "SQL error: " . mysqli_error($connect);
    }
}

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
    background: linear-gradient(135deg, rgba(243, 156, 18, 0.05) 0%, rgba(230, 126, 34, 0.05) 100%);
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
    background: linear-gradient(135deg, rgba(243, 156, 18, 0.1) 0%, rgba(230, 126, 34, 0.1) 100%);
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
    background: linear-gradient(90deg, transparent 0%, rgba(243, 156, 18, 0.1) 50%, transparent 100%);
    height: 1px;
}

.line-1 { top: 25%; width: 150px; right: 20%; }
.line-2 { top: 45%; width: 100px; right: 15%; }
.line-3 { top: 65%; width: 120px; right: 25%; }
.line-4 { top: 85%; width: 80px; right: 30%; }

.card-custom {
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border: none;
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
}

.card-body-custom {
    background: white;
    border-radius: 0 0 15px 15px;
    color: #333;
}

.form-control-custom {
    border-radius: 10px;
    border: 2px solid #e9ecef;
    padding: 12px 15px;
    transition: all 0.3s ease;
}

.form-control-custom:focus {
    border-color: #f39c12;
    box-shadow: 0 0 0 0.2rem rgba(243, 156, 18, 0.25);
}

.btn-custom {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    border: none;
    border-radius: 10px;
    padding: 12px 30px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
}

.btn-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(243, 156, 18, 0.3);
}

.btn-outline-custom {
    border: 2px solid #f39c12;
    color: #f39c12;
    background: transparent;
    border-radius: 10px;
    padding: 10px 25px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-outline-custom:hover {
    background: #f39c12;
    color: white;
    transform: translateY(-2px);
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
    color: #f39c12;
    font-weight: 600;
}

.icon-wrapper {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: white;
    font-size: 24px;
}

.success-icon {
    background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
}

.error-icon {
    background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
}

.division-info {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    border-left: 4px solid #f39c12;
}
</style>

<div class="content-wrapper">
    <!-- Decorative Elements -->
    <div class="decorative-elements">
        <div class="geometric-shape shape-1"></div>
        <div class="geometric-shape shape-2"></div>
        <div class="geometric-shape shape-3"></div>
        <div class="geometric-shape shape-4"></div>
        
        <!-- Edit/Pencil SVG -->
        <svg class="organizational-svg" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
            <!-- Pencil Icon -->
            <rect x="120" y="40" width="20" height="80" rx="10" fill="currentColor" opacity="0.3" transform="rotate(45 130 80)"/>
            <rect x="125" y="35" width="10" height="10" rx="5" fill="currentColor" opacity="0.5" transform="rotate(45 130 40)"/>
            
            <!-- Edit lines -->
            <rect x="60" y="100" width="60" height="3" rx="1.5" fill="currentColor" opacity="0.3"/>
            <rect x="60" y="110" width="50" height="3" rx="1.5" fill="currentColor" opacity="0.3"/>
            <rect x="60" y="120" width="55" height="3" rx="1.5" fill="currentColor" opacity="0.3"/>
            <rect x="60" y="130" width="45" height="3" rx="1.5" fill="currentColor" opacity="0.3"/>
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
            <!-- Division Info Card -->
            <?php if (!empty($division_id) && empty($error_message)): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="division-info">
                            <div class="d-flex align-items-center">
                                <div class="mr-3">
                                    <i class="fas fa-edit fa-2x text-warning"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 font-weight-bold">Editing Division</h6>
                                    <p class="mb-0 text-muted">
                                        You are currently editing Division ID: <strong><?php echo htmlspecialchars($division_id); ?></strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card card-custom">
                        <div class="card-header text-center py-4">
                            <?php if (!empty($success_message)): ?>
                                <div class="icon-wrapper success-icon">
                                    <i class="fas fa-check"></i>
                                </div>
                            <?php elseif (!empty($error_message)): ?>
                                <div class="icon-wrapper error-icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                            <?php else: ?>
                                <div class="icon-wrapper">
                                    <i class="fas fa-edit"></i>
                                </div>
                            <?php endif; ?>
                            <h3 class="card-title mb-0" style="color: white; font-weight: 600;">Edit Division</h3>
                            <p class="mb-0" style="color: rgba(255,255,255,0.8);">Update division information</p>
                        </div>
                        <div class="card-body card-body-custom p-4">
                            <?php if (!empty($error_message)): ?>
                                <div class="alert alert-danger-custom">
                                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error_message; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($success_message)): ?>
                                <div class="alert alert-success-custom">
                                    <i class="fas fa-check-circle mr-2"></i><?php echo $success_message; ?>
                                </div>
                                <div class="text-center mb-3">
                                    <a href="division_manage.php" class="btn btn-outline-custom">
                                        <i class="fas fa-list mr-2"></i>Back to List
                                    </a>
                                    <a href="division.php" class="btn btn-custom ml-2">
                                        <i class="fas fa-plus mr-2"></i>Add New
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($division_id) && empty($error_message)): ?>
                                <form method="POST" action="" id="editDivisionForm">
                                    <div class="form-group mb-4">
                                        <label for="division_id_display" class="form-label font-weight-bold">
                                            <i class="fas fa-id-card text-warning mr-2"></i>Division ID
                                        </label>
                                        <input type="text" 
                                               id="division_id_display" 
                                               class="form-control form-control-custom" 
                                               value="<?php echo htmlspecialchars($division_id); ?>" 
                                               readonly>
                                        <small class="form-text text-muted">Division ID cannot be changed</small>
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="division_name" class="form-label font-weight-bold">
                                            <i class="fas fa-building text-warning mr-2"></i>Division Name
                                        </label>
                                        <input type="text" 
                                               name="division_name" 
                                               id="division_name" 
                                               class="form-control form-control-custom" 
                                               value="<?php echo htmlspecialchars($division_name); ?>" 
                                               placeholder="Enter division name"
                                               required>
                                        <small class="form-text text-muted">Enter the updated name for this division</small>
                                    </div>
                                    <input type="hidden" name="division_id" value="<?php echo htmlspecialchars($division_id); ?>">
                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-custom btn-lg px-5">
                                            <i class="fas fa-save mr-2"></i>Update Division
                                        </button>
                                    </div>
                                </form>
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