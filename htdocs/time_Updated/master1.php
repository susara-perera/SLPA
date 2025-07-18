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
$page = 'master1.php';

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['division_id'])) {
    $employee_ID = mysqli_real_escape_string($connect, $_POST['employee_ID']);
    $employee_name = mysqli_real_escape_string($connect, $_POST['employee_name']);
    $division = mysqli_real_escape_string($connect, $_POST['division']);
    $section = mysqli_real_escape_string($connect, $_POST['section']);
    $designation = mysqli_real_escape_string($connect, $_POST['designation']);
    $appointment_date = mysqli_real_escape_string($connect, $_POST['appointment_date']);
    $gender = mysqli_real_escape_string($connect, $_POST['gender']);
    $status = mysqli_real_escape_string($connect, $_POST['status']);
    $nic_number = mysqli_real_escape_string($connect, $_POST['nic_number']);
    $telephone_number = mysqli_real_escape_string($connect, $_POST['telephone_number']);
    $address = mysqli_real_escape_string($connect, $_POST['address']);
    $card_valid_date = mysqli_real_escape_string($connect, $_POST['card_valid_date']);
    $card_issued_date = mysqli_real_escape_string($connect, $_POST['card_issued_date']);

    // Validate Employee ID uniqueness
    $checkEmployeeId = "SELECT COUNT(*) as count FROM employees WHERE employee_ID = ?";
    $checkStmt = mysqli_prepare($connect, $checkEmployeeId);
    if ($checkStmt) {
        mysqli_stmt_bind_param($checkStmt, "s", $employee_ID);
        mysqli_stmt_execute($checkStmt);
        $result = mysqli_stmt_get_result($checkStmt);
        $idCheck = mysqli_fetch_assoc($result);
        
        if ($idCheck['count'] > 0) {
            $message = "Employee ID already exists. Please use a different Employee ID.";
            $messageType = "danger";
        } else {
            // Handle file upload with validation
            $picture = $_FILES['picture'];
            $picture_path = '';

            if ($picture['error'] == UPLOAD_ERR_OK) {
                // Validate file type
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                $file_type = mime_content_type($picture['tmp_name']);
                
                if (in_array($file_type, $allowed_types)) {
                    // Create unique filename
                    $file_extension = pathinfo($picture['name'], PATHINFO_EXTENSION);
                    $unique_filename = $employee_ID . '_' . time() . '.' . $file_extension;
                    $target_dir = "uploads/employees/";
                    
                    // Create directory if it doesn't exist
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    
                    $target_file = $target_dir . $unique_filename;
                    
                    if (move_uploaded_file($picture["tmp_name"], $target_file)) {
                        $picture_path = $target_file;
                    } else {
                        $message = "Failed to upload picture. Please try again.";
                        $messageType = "danger";
                    }
                } else {
                    $message = "Invalid file type. Please upload JPG, PNG, or GIF images only.";
                    $messageType = "danger";
                }
            } else {
                $message = "Error with picture upload. Please select a valid image file.";
                $messageType = "danger";
            }

            // Proceed with insertion if no errors
            if (empty($message) && !empty($picture_path)) {
                $insertSql = "INSERT INTO employees (employee_ID, employee_name, division, section, designation, appointment_date, gender, status, nic_number, telephone_number, address, card_valid_date, card_issued_date, picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($connect, $insertSql);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "ssssssssssssss", $employee_ID, $employee_name, $division, $section, $designation, $appointment_date, $gender, $status, $nic_number, $telephone_number, $address, $card_valid_date, $card_issued_date, $picture_path);
                    if (mysqli_stmt_execute($stmt)) {
                        $message = "Employee record created successfully! Employee ID: " . $employee_ID;
                        $messageType = "success";
                        
                        // Clear form data on success
                        $_POST = array();
                    } else {
                        $message = "Failed to create employee record. Please try again.";
                        $messageType = "danger";
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $message = "Database error occurred: " . mysqli_error($connect);
                    $messageType = "danger";
                }
            }
        }
        mysqli_stmt_close($checkStmt);
    }
}

// Fetch divisions from the database
$divisions = [];
$divisionSql = "SELECT division_id, division_name FROM divisions ORDER BY division_name";
$result = mysqli_query($connect, $divisionSql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $divisions[] = $row;
    }
} else {
    $message = "Failed to fetch divisions: " . mysqli_error($connect);
    $messageType = "warning";
}

// Get statistics
$totalEmployees = 0;
$activeEmployees = 0;
$recentEmployees = 0;

$statsSql = "SELECT 
    COUNT(*) as total_employees,
    SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_employees,
    SUM(CASE WHEN appointment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as recent_employees
    FROM employees";
$statsResult = mysqli_query($connect, $statsSql);
if ($statsResult) {
    $stats = mysqli_fetch_assoc($statsResult);
    $totalEmployees = $stats['total_employees'];
    $activeEmployees = $stats['active_employees'];
    $recentEmployees = $stats['recent_employees'];
}
?>

<style>
/* Professional Employee Creation Dashboard Styling */
.employee-header {
    background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
    color: white;
    padding: 25px 0;
    margin-bottom: 25px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.employee-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
    border: none;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    margin-bottom: 20px;
}

.employee-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.employee-card-header {
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

.stats-card.recent {
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

.employee-form-group {
    margin-bottom: 20px;
}

.employee-form-label {
    font-weight: 600;
    color: #2d3436;
    margin-bottom: 8px;
    display: block;
    font-size: 14px;
}

.employee-form-control {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 12px 15px;
    transition: all 0.3s ease;
    font-size: 14px;
    width: 100%;
}

.employee-form-control:focus {
    border-color: #00b894;
    box-shadow: 0 0 0 0.2rem rgba(0, 184, 148, 0.25);
    outline: none;
}

.employee-btn {
    padding: 12px 30px;
    border-radius: 25px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    border: none;
    width: 100%;
}

.employee-btn-primary {
    background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
    color: white;
}

.employee-btn-primary:hover {
    background: linear-gradient(135deg, #00a085 0%, #2d3436 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    color: white;
}

.employee-btn-secondary {
    background: linear-gradient(135deg, #636e72 0%, #2d3436 100%);
    color: white;
}

.employee-btn-secondary:hover {
    background: linear-gradient(135deg, #2d3436 0%, #636e72 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
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

.form-icon {
    color: #00b894;
    margin-right: 8px;
}

.file-upload-area {
    border: 2px dashed #00b894;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    background: rgba(0, 184, 148, 0.1);
    transition: all 0.3s ease;
}

.file-upload-area:hover {
    background: rgba(0, 184, 148, 0.2);
    border-color: #00a085;
}

.form-step {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
}

.step-header {
    background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
    color: white;
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: center;
}

.required-field {
    color: #e17055;
    font-weight: bold;
}

.form-hint {
    font-size: 12px;
    color: #636e72;
    margin-top: 5px;
}

@media (max-width: 768px) {
    .stats-number {
        font-size: 2rem;
    }
    
    .employee-form-control {
        padding: 10px 12px;
        font-size: 13px;
    }
    
    .employee-btn {
        padding: 10px 25px;
    }
}
</style>

<div class="container-fluid">
    <!-- Header Section -->
    <div class="employee-header text-center">
        <h1 class="display-4 mb-3">👤 Employee Registration System</h1>
        <p class="lead mb-2">Comprehensive Employee Data Management for SLPA</p>
        <small class="text-light">Create detailed employee records with complete information and photo documentation</small>
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
                <div class="stats-number"><?= $totalEmployees ?></div>
                <div class="stats-label">
                    <i class="fas fa-users"></i> Total Employees
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="stats-card active">
                <div class="stats-number"><?= $activeEmployees ?></div>
                <div class="stats-label">
                    <i class="fas fa-user-check"></i> Active Employees
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="stats-card recent">
                <div class="stats-number"><?= $recentEmployees ?></div>
                <div class="stats-label">
                    <i class="fas fa-user-plus"></i> Recent Hires (30 days)
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Registration Form -->
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="employee-card">
                <div class="employee-card-header">
                    <h4 class="mb-0"><i class="fas fa-user-plus"></i> New Employee Registration Form</h4>
                </div>
                <div class="card-body p-4">
                    <div id="validation-errors" class="alert alert-danger alert-professional" style="display: none;"></div>

                    <form method="POST" action="" enctype="multipart/form-data" id="employeeForm" onsubmit="return validateForm()">
                        <!-- Basic Information Step -->
                        <div class="form-step">
                            <div class="step-header">
                                <h5 class="mb-0"><i class="fas fa-id-card"></i> Basic Information</h5>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="employee-form-group">
                                        <label class="employee-form-label" for="employee_ID">
                                            <i class="fas fa-id-badge form-icon"></i>Employee ID <span class="required-field">*</span>
                                        </label>
                                        <input type="text" id="employee_ID" name="employee_ID" 
                                               class="form-control employee-form-control" 
                                               placeholder="Enter unique Employee ID (e.g., EMP001)" 
                                               value="<?= isset($_POST['employee_ID']) ? htmlspecialchars($_POST['employee_ID']) : '' ?>"
                                               required>
                                        <div class="form-hint">Must be unique and alphanumeric</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="employee-form-group">
                                        <label class="employee-form-label" for="employee_name">
                                            <i class="fas fa-user form-icon"></i>Full Name <span class="required-field">*</span>
                                        </label>
                                        <input type="text" id="employee_name" name="employee_name" 
                                               class="form-control employee-form-control" 
                                               placeholder="Enter full name as per official documents"
                                               value="<?= isset($_POST['employee_name']) ? htmlspecialchars($_POST['employee_name']) : '' ?>"
                                               required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="employee-form-group">
                                        <label class="employee-form-label" for="nic_number">
                                            <i class="fas fa-id-card-alt form-icon"></i>NIC Number <span class="required-field">*</span>
                                        </label>
                                        <input type="text" id="nic_number" name="nic_number" 
                                               class="form-control employee-form-control" 
                                               placeholder="Enter NIC number (e.g., 123456789V)"
                                               value="<?= isset($_POST['nic_number']) ? htmlspecialchars($_POST['nic_number']) : '' ?>"
                                               required>
                                        <div class="form-hint">National Identity Card number</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="employee-form-group">
                                        <label class="employee-form-label" for="gender">
                                            <i class="fas fa-venus-mars form-icon"></i>Gender <span class="required-field">*</span>
                                        </label>
                                        <select name="gender" id="gender" class="form-select employee-form-control" required>
                                            <option value="" disabled selected>Select Gender</option>
                                            <option value="Male" <?= (isset($_POST['gender']) && $_POST['gender'] == 'Male') ? 'selected' : '' ?>>👨 Male</option>
                                            <option value="Female" <?= (isset($_POST['gender']) && $_POST['gender'] == 'Female') ? 'selected' : '' ?>>👩 Female</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Organization Information Step -->
                        <div class="form-step">
                            <div class="step-header">
                                <h5 class="mb-0"><i class="fas fa-building"></i> Organization Details</h5>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="employee-form-group">
                                        <label class="employee-form-label" for="division">
                                            <i class="fas fa-sitemap form-icon"></i>Division <span class="required-field">*</span>
                                        </label>
                                        <select name="division" id="division" class="form-select employee-form-control" required>
                                            <option value="" disabled selected>Select Division</option>
                                            <?php foreach ($divisions as $division): ?>
                                                <option value="<?= htmlspecialchars($division['division_id']) ?>"
                                                        <?= (isset($_POST['division']) && $_POST['division'] == $division['division_id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($division['division_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="employee-form-group">
                                        <label class="employee-form-label" for="section">
                                            <i class="fas fa-layer-group form-icon"></i>Section <span class="required-field">*</span>
                                        </label>
                                        <select name="section" id="section" class="form-select employee-form-control" required>
                                            <option value="" disabled selected>Select Section</option>
                                        </select>
                                        <div class="form-hint">Sections will load based on selected division</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="employee-form-group">
                                        <label class="employee-form-label" for="designation">
                                            <i class="fas fa-briefcase form-icon"></i>Designation <span class="required-field">*</span>
                                        </label>
                                        <input type="text" id="designation" name="designation" 
                                               class="form-control employee-form-control" 
                                               placeholder="Enter job designation/title"
                                               value="<?= isset($_POST['designation']) ? htmlspecialchars($_POST['designation']) : '' ?>"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="employee-form-group">
                                        <label class="employee-form-label" for="status">
                                            <i class="fas fa-toggle-on form-icon"></i>Employment Status <span class="required-field">*</span>
                                        </label>
                                        <select name="status" id="status" class="form-select employee-form-control" required>
                                            <option value="" disabled selected>Select Employment Status</option>
                                            <option value="Active" <?= (isset($_POST['status']) && $_POST['status'] == 'Active') ? 'selected' : '' ?>>✅ Active</option>
                                            <option value="Inactive" <?= (isset($_POST['status']) && $_POST['status'] == 'Inactive') ? 'selected' : '' ?>>❌ Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="employee-form-group">
                                        <label class="employee-form-label" for="appointment_date">
                                            <i class="fas fa-calendar-plus form-icon"></i>Appointment Date <span class="required-field">*</span>
                                        </label>
                                        <input type="date" id="appointment_date" name="appointment_date" 
                                               class="form-control employee-form-control"
                                               value="<?= isset($_POST['appointment_date']) ? htmlspecialchars($_POST['appointment_date']) : '' ?>"
                                               max="<?= date('Y-m-d') ?>" required>
                                        <div class="form-hint">Date when employee joined the organization</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information Step -->
                        <div class="form-step">
                            <div class="step-header">
                                <h5 class="mb-0"><i class="fas fa-address-book"></i> Contact Information</h5>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="employee-form-group">
                                        <label class="employee-form-label" for="telephone_number">
                                            <i class="fas fa-phone form-icon"></i>Telephone Number <span class="required-field">*</span>
                                        </label>
                                        <input type="tel" id="telephone_number" name="telephone_number" 
                                               class="form-control employee-form-control" 
                                               placeholder="Enter contact number (e.g., 0771234567)"
                                               value="<?= isset($_POST['telephone_number']) ? htmlspecialchars($_POST['telephone_number']) : '' ?>"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="employee-form-group">
                                        <label class="employee-form-label" for="address">
                                            <i class="fas fa-map-marker-alt form-icon"></i>Residential Address <span class="required-field">*</span>
                                        </label>
                                        <textarea id="address" name="address" 
                                                  class="form-control employee-form-control" 
                                                  placeholder="Enter complete residential address" 
                                                  rows="3" required><?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Information Step -->
                        <div class="form-step">
                            <div class="step-header">
                                <h5 class="mb-0"><i class="fas fa-credit-card"></i> ID Card Information</h5>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="employee-form-group">
                                        <label class="employee-form-label" for="card_issued_date">
                                            <i class="fas fa-calendar-check form-icon"></i>Card Issued Date <span class="required-field">*</span>
                                        </label>
                                        <input type="date" id="card_issued_date" name="card_issued_date" 
                                               class="form-control employee-form-control"
                                               value="<?= isset($_POST['card_issued_date']) ? htmlspecialchars($_POST['card_issued_date']) : '' ?>"
                                               max="<?= date('Y-m-d') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="employee-form-group">
                                        <label class="employee-form-label" for="card_valid_date">
                                            <i class="fas fa-calendar-times form-icon"></i>Card Valid Until <span class="required-field">*</span>
                                        </label>
                                        <input type="date" id="card_valid_date" name="card_valid_date" 
                                               class="form-control employee-form-control"
                                               value="<?= isset($_POST['card_valid_date']) ? htmlspecialchars($_POST['card_valid_date']) : '' ?>"
                                               min="<?= date('Y-m-d') ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Photo Upload Step -->
                        <div class="form-step">
                            <div class="step-header">
                                <h5 class="mb-0"><i class="fas fa-camera"></i> Employee Photo</h5>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="employee-form-group">
                                        <label class="employee-form-label" for="picture">
                                            <i class="fas fa-image form-icon"></i>Employee Picture <span class="required-field">*</span>
                                        </label>
                                        <div class="file-upload-area">
                                            <i class="fas fa-cloud-upload-alt fa-3x mb-3 text-muted"></i>
                                            <p class="mb-2"><strong>Click to select or drag and drop an image</strong></p>
                                            <p class="text-muted mb-0">Supported formats: JPG, PNG, GIF (Max 5MB)</p>
                                            <input type="file" id="picture" name="picture" 
                                                   class="form-control employee-form-control mt-3" 
                                                   accept="image/*" required>
                                        </div>
                                        <div class="form-hint">Professional headshot photo recommended (passport size)</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-step">
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="submit" class="btn employee-btn employee-btn-primary">
                                        <i class="fas fa-user-plus"></i> Create Employee Record
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="reset" class="btn employee-btn employee-btn-secondary">
                                        <i class="fas fa-undo"></i> Clear Form
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Enhanced Employee Form functionality
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

    // Real-time validation feedback
    const formInputs = document.querySelectorAll('.employee-form-control');
    formInputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                validateField(this);
            }
        });
    });

    // Phone number formatting
    const phoneInput = document.getElementById('telephone_number');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^\d]/g, '');
        });
    }

    // NIC validation
    const nicInput = document.getElementById('nic_number');
    if (nicInput) {
        nicInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }

    // Employee ID formatting
    const empIdInput = document.getElementById('employee_ID');
    if (empIdInput) {
        empIdInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });
    }

    // File upload preview
    const pictureInput = document.getElementById('picture');
    if (pictureInput) {
        pictureInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB');
                    this.value = '';
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    let preview = document.getElementById('image-preview');
                    if (!preview) {
                        preview = document.createElement('div');
                        preview.id = 'image-preview';
                        preview.innerHTML = '<h6 class="mt-3">Preview:</h6><img id="preview-img" style="max-width: 200px; max-height: 200px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">';
                        pictureInput.parentNode.appendChild(preview);
                    }
                    document.getElementById('preview-img').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});

// AJAX for division-section dependency
$(document).ready(function() {
    $('#division').change(function() {
        var division_id = $(this).val();
        if (division_id) {
            $.ajax({
                type: 'POST',
                url: '',
                data: {
                    division_id: division_id
                },
                success: function(response) {
                    $('#section').html(response);
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    $('#section').html('<option value="" disabled>Error loading sections</option>');
                }
            });
        } else {
            $('#section').html('<option value="" disabled selected>Select Section</option>');
        }
    });
});

// Enhanced form validation
function validateForm() {
    let isValid = true;
    let errors = [];
    
    // Clear previous errors
    document.getElementById('validation-errors').style.display = 'none';
    
    // Employee ID validation
    const empId = document.getElementById('employee_ID').value;
    if (empId.length < 3) {
        errors.push('Employee ID must be at least 3 characters long');
        isValid = false;
    }
    
    // NIC validation
    const nic = document.getElementById('nic_number').value;
    if (nic.length < 10) {
        errors.push('NIC number must be at least 10 characters');
        isValid = false;
    }
    
    // Phone validation
    const phone = document.getElementById('telephone_number').value;
    if (phone.length < 10) {
        errors.push('Telephone number must be at least 10 digits');
        isValid = false;
    }
    
    // Date validations
    const appointmentDate = new Date(document.getElementById('appointment_date').value);
    const cardIssuedDate = new Date(document.getElementById('card_issued_date').value);
    const cardValidDate = new Date(document.getElementById('card_valid_date').value);
    const today = new Date();
    
    if (appointmentDate > today) {
        errors.push('Appointment date cannot be in the future');
        isValid = false;
    }
    
    if (cardIssuedDate > today) {
        errors.push('Card issued date cannot be in the future');
        isValid = false;
    }
    
    if (cardValidDate <= cardIssuedDate) {
        errors.push('Card valid date must be after card issued date');
        isValid = false;
    }
    
    // File validation
    const picture = document.getElementById('picture').files[0];
    if (picture && picture.size > 5 * 1024 * 1024) {
        errors.push('Picture file size must be less than 5MB');
        isValid = false;
    }
    
    if (!isValid) {
        const errorDiv = document.getElementById('validation-errors');
        errorDiv.innerHTML = '<strong>Please fix the following errors:</strong><br>' + errors.join('<br>');
        errorDiv.style.display = 'block';
        errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }
    
    // Show loading state
    const submitBtn = document.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Employee...';
    submitBtn.disabled = true;
    
    return true;
}

// Field validation helper
function validateField(field) {
    field.classList.remove('is-valid', 'is-invalid');
    
    if (field.value.trim() === '' && field.required) {
        field.classList.add('is-invalid');
        return false;
    } else if (field.value.trim() !== '') {
        field.classList.add('is-valid');
        return true;
    }
    
    return true;
}
</script>

<?php
include('includes/scripts.php');
include('includes/footer.php');


if (isset($_POST['division_id'])) {
    $division_id = $_POST['division_id'];

    // Fetch sections related to the selected division
    $sql = "SELECT section_id, section_name FROM sections WHERE division_id = ?";
    $stmt = mysqli_prepare($connect, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $division_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            echo '<option value="" disabled selected>Select Section</option>';
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<option value="' . htmlspecialchars($row['section_id']) . '">' . htmlspecialchars($row['section_name']) . '</option>';
            }
        } else {
            echo '<option value="" disabled>No Sections Available</option>';
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "Error: " . mysqli_error($connect);
    }
}
?>