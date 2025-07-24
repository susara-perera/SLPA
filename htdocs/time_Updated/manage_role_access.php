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
$page = 'manage_role_access.php';

// Check if the user has access to this page or is Super Admin
if (!hasAccess($page) && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Super_Ad')) {
    echo "<div class='container'><div class='row mx-md-n8'><div class='col px-md-5'><h1>Access Denied</h1><p>You do not have permission to access this page.</p></div></div></div>";
    include('includes/scripts.php');
    include('includes/footer.php');
    ob_end_flush(); 
    exit();
}

// Fetch roles
$roles = mysqli_query($connect, "SELECT DISTINCT role FROM users");

// Pages to manage access for
$pages = [
    'unit.php' => 'Unit Attendance Report',
    'audit.php' => 'Audit Report',
    'meal.php' => 'Meal Report',
    'user.php' => 'Create User',
    'user_status.php' => 'Users Status',
    'userList.php' => 'Users List',
    'userManage.php' => 'Manage Users',
    'master1.php' => 'Create Employee',
    'master_records_view.php' => 'Employee List',
    'master_records.php' => 'Manage Employees',
    'division.php' => 'Create New Division',
    'division_List.php' => 'All Divisions',
    'division_manage.php' => 'Manage Divisions',
    'section.php' => 'Create New Section',
    'section_List.php' => 'Sections List',
    'section_Manage.php' => 'Manage Sections',
    'section_edit.php' => 'Edit Section',
    'section_action.php' => 'Section Actions',
    'changePassword.php' => 'Change Password',
    'manage_role_access.php' => 'Manage Role Access'
];

// Initialize message variable
$message = "";

// Function to fetch role pages
function getRolePages($role) {
    global $connect;
    $role = mysqli_real_escape_string($connect, $role);
    $result = mysqli_query($connect, "SELECT page FROM role_access WHERE role = '$role'");
    return array_column(mysqli_fetch_all($result, MYSQLI_ASSOC), 'page');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role'])) {
    $role = mysqli_real_escape_string($connect, $_POST['role']);
    $access = isset($_POST['pages']) ? $_POST['pages'] : [];

    // Fetch current access settings
    $currentAccess = getRolePages($role);

    // Check if there are changes
    $changesMade = false;

    // Determine if there are differences
    if (array_diff($access, $currentAccess) || array_diff($currentAccess, $access)) {
        $changesMade = true;
    }

    // Update access control in the database if changes were detected
    if ($changesMade) {
        // Delete existing access for the role
        mysqli_query($connect, "DELETE FROM role_access WHERE role = '$role'");

        // Insert new access settings
        foreach ($access as $page) {
            $page = mysqli_real_escape_string($connect, $page);
            if (!mysqli_query($connect, "INSERT INTO role_access (role, page) VALUES ('$role', '$page')")) {
                $message = "Error updating access control: " . mysqli_error($connect);
                break;
            }
        }
        if (empty($message)) {
            $message = "Access control updated successfully.";
        }

        // Redirect to avoid form resubmission
        header("Location: " . $_SERVER['PHP_SELF'] . "?role=" . urlencode($role) . "&updated=true");
        exit();
    } else {
        $message = "No changes made.";
    }
}

// If a role is selected, get its current access
$selectedRole = isset($_GET['role']) ? $_GET['role'] : (isset($_POST['role']) ? $_POST['role'] : '');
$currentAccess = $selectedRole ? getRolePages($selectedRole) : [];

// Check if the page is reloaded after an update
$updateSuccess = isset($_GET['updated']) && $_GET['updated'] === 'true';
?>

<style>
.role-access-wrapper {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 20px 0;
    position: relative;
    overflow: hidden;
}

.role-access-wrapper::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 400px;
    height: 100%;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
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
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.2) 0%, rgba(118, 75, 162, 0.2) 100%);
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

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

.main-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    position: relative;
    z-index: 2;
    margin: 0 auto;
    max-width: 1200px;
}

.card-header-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    text-align: center;
    position: relative;
}

.card-header-custom::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.icon-wrapper-large {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: white;
    font-size: 32px;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.card-body-custom {
    padding: 40px;
}

.form-section {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    border: 1px solid rgba(102, 126, 234, 0.1);
}

.form-control-modern {
    border-radius: 12px;
    border: 2px solid #e9ecef;
    padding: 15px 20px;
    font-size: 16px;
    transition: all 0.3s ease;
    background: white;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.form-control-modern:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    background: white;
}

.form-label-modern {
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
    display: block;
    font-size: 16px;
}

.checkbox-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.checkbox-item {
    background: white;
    border-radius: 10px;
    padding: 15px 20px;
    border: 2px solid #f8f9fa;
    transition: all 0.3s ease;
    position: relative;
    cursor: pointer;
}

.checkbox-item:hover {
    border-color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
}

.checkbox-item input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

.checkbox-item .checkmark {
    position: absolute;
    top: 50%;
    right: 20px;
    transform: translateY(-50%);
    height: 20px;
    width: 20px;
    background-color: #eee;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.checkbox-item input:checked ~ .checkmark {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.checkbox-item .checkmark:after {
    content: "";
    position: absolute;
    display: none;
    left: 7px;
    top: 3px;
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 3px 3px 0;
    transform: rotate(45deg);
}

.checkbox-item input:checked ~ .checkmark:after {
    display: block;
}

.checkbox-item label {
    cursor: pointer;
    font-weight: 500;
    color: #333;
    margin: 0;
    padding-right: 50px;
}

.select-all-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
}

.select-all-section input[type="checkbox"] {
    width: 20px;
    height: 20px;
    accent-color: white;
}

.select-all-section label {
    font-weight: 600;
    font-size: 16px;
    margin: 0;
    cursor: pointer;
}

.btn-save-modern {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 12px;
    padding: 15px 40px;
    font-weight: 600;
    font-size: 16px;
    color: white;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    text-transform: uppercase;
    letter-spacing: 1px;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.btn-save-modern::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn-save-modern:hover::before {
    left: 100%;
}

.btn-save-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
    color: white;
}

.alert-modern {
    border-radius: 12px;
    border: none;
    padding: 20px 25px;
    margin-bottom: 25px;
    font-weight: 500;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.alert-success-modern {
    background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
    color: white;
}

.alert-info-modern {
    background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
    color: white;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.loading-spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.stats-section {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 30px;
    text-align: center;
}

.stats-item {
    display: inline-block;
    margin: 0 20px;
}

.stats-number {
    font-size: 32px;
    font-weight: 700;
    color: #667eea;
    display: block;
}

.stats-label {
    font-size: 14px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.page-title {
    font-size: 28px;
    font-weight: 700;
    margin: 0;
    position: relative;
    z-index: 1;
}

.page-subtitle {
    font-size: 16px;
    opacity: 0.9;
    margin: 10px 0 0 0;
    position: relative;
    z-index: 1;
}
</style>

<div class="role-access-wrapper">
    <!-- Decorative Elements -->
    <div class="decorative-elements">
        <div class="geometric-shape shape-1"></div>
        <div class="geometric-shape shape-2"></div>
        <div class="geometric-shape shape-3"></div>
        <div class="geometric-shape shape-4"></div>
    </div>

    <div class="container-fluid px-4">
        <div class="main-card">
            <div class="card-header-custom">
                <div class="icon-wrapper-large">
                    <i class="fas fa-users-cog"></i>
                </div>
                <h1 class="page-title">Role Access Management</h1>
                <p class="page-subtitle">Configure user role permissions and access control</p>
            </div>
            
            <div class="card-body-custom">
                <?php if ($updateSuccess): ?>
                    <div id="success-message" class="alert alert-success-modern">
                        <i class="fas fa-check-circle mr-2"></i>Access control updated successfully!
                    </div>
                <?php elseif (!empty($message)): ?>
                    <div id="success-message" class="alert alert-info-modern">
                        <i class="fas fa-info-circle mr-2"></i><?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <div class="stats-section">
                    <div class="stats-item">
                        <span class="stats-number"><?php echo mysqli_num_rows(mysqli_query($connect, "SELECT DISTINCT role FROM users")); ?></span>
                        <span class="stats-label">Total Roles</span>
                    </div>
                    <div class="stats-item">
                        <span class="stats-number"><?php echo count($pages); ?></span>
                        <span class="stats-label">Available Pages</span>
                    </div>
                    <?php if ($selectedRole): ?>
                    <div class="stats-item">
                        <span class="stats-number"><?php echo count($currentAccess); ?></span>
                        <span class="stats-label">Granted Permissions</span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="form-section">
                    <form method="GET" id="roleForm">
                        <label for="role" class="form-label-modern">
                            <i class="fas fa-user-tag text-primary mr-2"></i>Select Role to Manage
                        </label>
                        <select name="role" id="role" class="form-control form-control-modern" onchange="this.form.submit()">
                            <option value="">-- Choose a role to configure --</option>
                            <?php 
                            mysqli_data_seek($roles, 0); // Reset pointer
                            while ($role = mysqli_fetch_assoc($roles)): 
                            ?>
                                <option value="<?php echo htmlspecialchars($role['role']); ?>" <?php echo $selectedRole === $role['role'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($role['role']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </form>
                </div>

                <?php if ($selectedRole): ?>
                    <div class="form-section">
                        <form method="POST" id="accessForm">
                            <input type="hidden" name="role" value="<?php echo htmlspecialchars($selectedRole); ?>">

                            <h3 style="color: #333; margin-bottom: 20px; font-weight: 600;">
                                <i class="fas fa-shield-alt text-primary mr-2"></i>
                                Configure Access Permissions for: <span style="color: #667eea;"><?php echo htmlspecialchars($selectedRole); ?></span>
                            </h3>

                            <div class="select-all-section">
                                <input type="checkbox" id="select-all">
                                <label for="select-all">
                                    <i class="fas fa-check-double mr-2"></i>Select All Permissions
                                </label>
                            </div>

                            <div class="checkbox-grid">
                                <?php foreach ($pages as $page => $description): ?>
                                    <div class="checkbox-item">
                                        <input type="checkbox" name="pages[]" value="<?php echo htmlspecialchars($page); ?>" 
                                            <?php echo in_array($page, $currentAccess) ? 'checked' : ''; ?> 
                                            class="page-checkbox" id="page_<?php echo md5($page); ?>">
                                        <span class="checkmark"></span>
                                        <label for="page_<?php echo md5($page); ?>">
                                            <i class="fas fa-file-alt text-muted mr-2"></i>
                                            <?php echo htmlspecialchars($description); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-save-modern" onclick="return confirmChanges()">
                                    <i class="fas fa-save mr-2"></i>Save Access Configuration
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Loading overlay -->
<div id="loading" class="loading-overlay" style="display:none;">
    <div class="loading-spinner"></div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Show loading overlay during form submission
    $('#roleForm, #accessForm').on('submit', function() {
        $('#loading').fadeIn(300);
    });

    // Toggle all checkboxes with smooth animation
    $('#select-all').change(function() {
        var checked = this.checked;
        $('.page-checkbox').each(function() {
            this.checked = checked;
            // Add visual feedback
            if (checked) {
                $(this).closest('.checkbox-item').addClass('selected');
            } else {
                $(this).closest('.checkbox-item').removeClass('selected');
            }
        });
        updateSelectAllStatus();
    });

    // Individual checkbox changes
    $('.page-checkbox').change(function() {
        if (this.checked) {
            $(this).closest('.checkbox-item').addClass('selected');
        } else {
            $(this).closest('.checkbox-item').removeClass('selected');
        }
        updateSelectAllStatus();
    });

    // Update "Select All" checkbox status
    function updateSelectAllStatus() {
        var totalCheckboxes = $('.page-checkbox').length;
        var checkedCheckboxes = $('.page-checkbox:checked').length;
        
        if (checkedCheckboxes === totalCheckboxes && totalCheckboxes > 0) {
            $('#select-all').prop('checked', true);
        } else {
            $('#select-all').prop('checked', false);
        }
        
        // Update stats if visible
        $('.stats-number:last').text(checkedCheckboxes);
    }

    // Hide loading overlay after page load
    $(window).on('load', function() {
        $('#loading').fadeOut(300);
    });

    // Auto-hide success/info messages
    setTimeout(function() {
        $('#success-message').fadeOut('slow');
    }, 5000);

    // Add hover effects to checkbox items
    $('.checkbox-item').hover(
        function() {
            $(this).addClass('hover-effect');
        },
        function() {
            $(this).removeClass('hover-effect');
        }
    );

    // Initialize selected state for checked items
    $('.page-checkbox:checked').each(function() {
        $(this).closest('.checkbox-item').addClass('selected');
    });

    // Add smooth transitions for role selection
    $('#role').change(function() {
        if ($(this).val()) {
            $('.form-section').addClass('loading');
        }
    });

    // Add click handler for checkbox items
    $('.checkbox-item').click(function(e) {
        if (e.target.type !== 'checkbox') {
            var checkbox = $(this).find('input[type="checkbox"]');
            checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
        }
    });

    // Smooth scroll to permissions section when role is selected
    if ($('#accessForm').length > 0) {
        $('html, body').animate({
            scrollTop: $('#accessForm').offset().top - 100
        }, 1000);
    }

    // Add confirmation animation
    function confirmChanges() {
        var checkedCount = $('.page-checkbox:checked').length;
        var roleName = $('input[name="role"]').val();
        
        return confirm(`Are you sure you want to save access permissions for role "${roleName}"?\n\nSelected permissions: ${checkedCount} out of ${$('.page-checkbox').length} pages.`);
    }

    // Make confirmChanges available globally
    window.confirmChanges = confirmChanges;

    // Add real-time permission counter
    function updatePermissionCounter() {
        var checkedCount = $('.page-checkbox:checked').length;
        var totalCount = $('.page-checkbox').length;
        
        if ($('.permission-counter').length === 0) {
            $('.select-all-section').append('<span class="permission-counter" style="margin-left: auto; font-size: 14px; opacity: 0.8;"></span>');
        }
        
        $('.permission-counter').text(`${checkedCount}/${totalCount} permissions selected`);
    }

    $('.page-checkbox').change(updatePermissionCounter);
    updatePermissionCounter(); // Initial count
});

// Enhanced visual feedback for form interactions
document.addEventListener('DOMContentLoaded', function() {
    // Add ripple effect to buttons
    const buttons = document.querySelectorAll('.btn-save-modern');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            let ripple = document.createElement('span');
            ripple.classList.add('ripple');
            this.appendChild(ripple);

            let x = e.clientX - e.target.offsetLeft;
            let y = e.clientY - e.target.offsetTop;

            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;

            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
});
</script>

<style>
.checkbox-item.selected {
    border-color: #667eea;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
}

.checkbox-item.hover-effect {
    border-color: #667eea;
}

.form-section.loading {
    opacity: 0.7;
    pointer-events: none;
}

.ripple {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.6);
    transform: scale(0);
    animation: ripple-animation 0.6s linear;
    pointer-events: none;
}

@keyframes ripple-animation {
    to {
        transform: scale(4);
        opacity: 0;
    }
}

.permission-counter {
    font-weight: 500;
    background: rgba(255, 255, 255, 0.2);
    padding: 5px 12px;
    border-radius: 20px;
    margin-left: auto;
}
</style>

<?php
mysqli_close($connect);
include('includes/scripts.php');
include('includes/footer.php');
ob_end_flush(); 
?>
