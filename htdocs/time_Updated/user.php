<?php
session_start();
ob_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); 
    exit();
}

include('includes/header2.php');
include('includes/navbar.php');
include('./dbc.php');
include('includes/check_access.php');

// Define the page name
$page = 'user.php';

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

// Handle error and success messages
if (isset($_GET['error'])) {
    $error_messages = [
        'password_mismatch' => 'Passwords do not match.',
        'password_complexity' => 'Password must include at least one uppercase letter, one lowercase letter, one number, and one special character.',
        'employee_id_already_exists' => 'An account with this Employee ID already exists. Please recheck your Employee ID and try again.',
        'insert_failed' => 'Failed to create the user.',
        'sql_error' => 'A database error occurred.',
        'missing_data' => 'All fields are required.'
    ];
    $message = $error_messages[$_GET['error']] ?? 'An unknown error occurred.';
    $messageType = 'danger';
}

if (isset($_GET['success']) && $_GET['success'] === 'user_created') {
    $message = 'User account has been successfully created!';
    $messageType = 'success';
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
?>

<style>
/* Professional User Management Styling */
.user-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px 0;
    margin-bottom: 25px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.user-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
    border: none;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    margin-bottom: 25px;
}

.user-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.user-card-header {
    background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    color: white;
    border-radius: 15px 15px 0 0;
    padding: 20px 25px;
    border: none;
}

.user-form-group {
    margin-bottom: 20px;
}

.user-form-label {
    font-weight: 600;
    color: #2d3436;
    margin-bottom: 8px;
    display: block;
    font-size: 14px;
}

.user-form-control {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 12px 15px;
    transition: all 0.3s ease;
    font-size: 14px;
    margin-bottom: 5px;
    width: 100%;
}

.user-form-control:focus {
    border-color: #74b9ff;
    box-shadow: 0 0 0 0.2rem rgba(116, 185, 255, 0.25);
    outline: none;
}

.user-btn {
    padding: 12px 30px;
    border-radius: 25px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    border: none;
    margin-top: 10px;
    width: 100%;
}

.user-btn-primary {
    background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    color: white;
}

.user-btn-primary:hover {
    background: linear-gradient(135deg, #0984e3 0%, #2d3436 100%);
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

.password-requirements {
    background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);
    color: white;
    padding: 12px 15px;
    border-radius: 10px;
    margin-top: 8px;
    font-size: 12px;
}

.password-requirements ul {
    margin: 0;
    padding-left: 20px;
}

.user-icon {
    font-size: 1.2em;
    margin-right: 8px;
    color: #74b9ff;
}

.container-fluid {
    padding: 20px;
}

.form-row {
    display: flex;
    gap: 15px;
}

.form-row .user-form-group {
    flex: 1;
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
        gap: 0;
    }
}
</style>

<div class="container-fluid">
    <!-- Header Section -->
    <div class="user-header text-center">
        <h1 class="display-4 mb-3">👥 User Management System</h1>
        <p class="lead mb-2">User Account Creation for SLPA</p>
        <small class="text-light">Create new user accounts with proper role assignments and security</small>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-professional alert-dismissible fade show">
            <strong><?= $messageType == 'success' ? 'Success!' : ($messageType == 'warning' ? 'Warning!' : 'Error!') ?></strong> <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-6">
            <div class="user-card">
                <div class="user-card-header">
                    <h4 class="mb-0"><i class="fas fa-user-plus"></i> Create New User Account</h4>
                </div>
                <div class="card-body p-3">
                    <div id="error-message" class="alert alert-danger alert-professional" style="display: none;"></div>

                    <form method="POST" action="./user_action.php" onsubmit="return validateForm()">
                        <div class="form-row">
                            <div class="user-form-group">
                                <label class="user-form-label" for="division">
                                    <i class="fas fa-building user-icon"></i>Division
                                </label>
                                <select name="division" id="division" class="form-select user-form-control" required>
                                    <option value="" disabled selected>Select Division</option>
                                    <?php foreach ($divisions as $division) : ?>
                                        <option value="<?php echo htmlspecialchars($division['division_id']); ?>">
                                            <?php echo htmlspecialchars($division['division_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="user-form-group">
                                <label class="user-form-label" for="role">
                                    <i class="fas fa-user-shield user-icon"></i>User Role
                                </label>
                                <select name="role" id="role" class="form-select user-form-control" required>
                                    <option value="" disabled selected>Select User Role</option>
                                    <option value="Super_Ad">🔐 Super Admin</option>
                                    <option value="Administration">👨‍💼 Administration</option>
                                    <option value="Administration_clerk">📋 Administrative Clerk</option>
                                    <option value="clerk">📝 Clerk</option>
                                </select>
                            </div>
                        </div>

                        <div class="user-form-group">
                            <label class="user-form-label" for="employee_ID">
                                <i class="fas fa-id-badge user-icon"></i>Employee ID
                            </label>
                            <input type="text" id="employee_ID" name="employee_ID" class="form-control user-form-control" 
                                   placeholder="Enter Employee ID (e.g., EMP001)" required>
                        </div>

                        <div class="user-form-group">
                            <label class="user-form-label" for="password">
                                <i class="fas fa-lock user-icon"></i>Create Password
                            </label>
                            <input type="password" id="password" name="password" class="form-control user-form-control" 
                                   placeholder="Enter a secure password" required>
                            <div class="password-requirements">
                                <strong>Password Requirements:</strong>
                                <ul>
                                    <li>At least 8 characters long</li>
                                    <li>At least one uppercase letter (A-Z)</li>
                                    <li>At least one lowercase letter (a-z)</li>
                                    <li>At least one number (0-9)</li>
                                    <li>At least one special character (!@#$%^&*)</li>
                                </ul>
                            </div>
                        </div>

                        <div class="user-form-group">
                            <label class="user-form-label" for="re-password">
                                <i class="fas fa-lock user-icon"></i>Confirm Password
                            </label>
                            <input type="password" id="re-password" name="re-password" class="form-control user-form-control" 
                                   placeholder="Re-enter password to confirm" required>
                        </div>

                        <div class="user-form-group">
                            <button type="submit" class="btn user-btn user-btn-primary">
                                <i class="fas fa-user-plus"></i> Create User Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Enhanced form validation with professional styling
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

    // Real-time password validation
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('re-password');
    
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            validatePasswordStrength(this.value);
        });
    }

    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            validatePasswordMatch();
        });
    }
});

function validatePasswordStrength(password) {
    const requirements = document.querySelector('.password-requirements');
    const items = requirements.querySelectorAll('li');
    
    const checks = [
        password.length >= 8,
        /[A-Z]/.test(password),
        /[a-z]/.test(password),
        /\d/.test(password),
        /[\W]/.test(password)
    ];
    
    items.forEach((item, index) => {
        if (checks[index]) {
            item.style.color = '#00b894';
            item.style.textDecoration = 'line-through';
        } else {
            item.style.color = 'white';
            item.style.textDecoration = 'none';
        }
    });
}

function validatePasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('re-password').value;
    const confirmInput = document.getElementById('re-password');
    
    if (confirmPassword && password !== confirmPassword) {
        confirmInput.style.borderColor = '#e84393';
        confirmInput.style.boxShadow = '0 0 0 0.2rem rgba(232, 67, 147, 0.25)';
    } else if (confirmPassword) {
        confirmInput.style.borderColor = '#00b894';
        confirmInput.style.boxShadow = '0 0 0 0.2rem rgba(0, 184, 148, 0.25)';
    }
}

function validateForm() {
    var errorText = '';
    var password = document.getElementById("password").value;
    var confirmPassword = document.getElementById("re-password").value;
    var errorMessageDiv = document.getElementById("error-message");
    var employeeId = document.getElementById("employee_ID").value;

    errorMessageDiv.innerHTML = ''; // Clear previous errors
    errorMessageDiv.style.display = 'none';

    // Employee ID validation
    if (employeeId.length < 3) {
        errorText += 'Employee ID must be at least 3 characters long.<br>';
    }

    // Password validation
    if (password !== confirmPassword) {
        errorText += 'Passwords do not match.<br>';
    }
    
    if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W]).{8,}$/.test(password)) {
        errorText += 'Password must include at least one uppercase letter, one lowercase letter, one number, and one special character, and be at least 8 characters long.<br>';
    }

    if (errorText.length > 0) {
        errorMessageDiv.innerHTML = '<strong>Please fix the following errors:</strong><br>' + errorText;
        errorMessageDiv.style.display = 'block';
        
        // Scroll to error message
        errorMessageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }

    return true;
}
</script>

<?php
include('includes/scripts.php');
include('includes/footer.php');
ob_end_flush();
?>
