<?php
ob_start(); 
session_start();
include('./dbc.php');

// Check if user is Super Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Super_Ad') {
    header("Location: index.php");
    exit();
}

include('includes/header2.php');
include('includes/navbar.php');

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
    'section_Manage.php' => 'Manage Sections'
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

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200&display=swap" rel="stylesheet" />
<div class="row mx-md-n8">
    <div class="col px-md-5">
        <h1>Manage Role Access</h1>
        <div class="p-3 border bg-light">
            <div class="container">
                <?php if ($updateSuccess): ?>
                    <div id="success-message" class="alert alert-success">Access control updated successfully.</div>
                <?php elseif (!empty($message)): ?>
                    <div id="success-message" class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <form method="GET" id="roleForm">
                    <div class="form-group">
                        <label for="role">Select Role:</label>
                        <select name="role" id="role" class="form-control" onchange="this.form.submit()">
                            <option value="">-- Select Role --</option>
                            <?php while ($role = mysqli_fetch_assoc($roles)): ?>
                                <option value="<?php echo htmlspecialchars($role['role']); ?>" <?php echo $selectedRole === $role['role'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($role['role']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </form>

                <?php if ($selectedRole): ?>
                    <form method="POST" id="accessForm">
                        <input type="hidden" name="role" value="<?php echo htmlspecialchars($selectedRole); ?>">

                        <h2>Select Accessible Pages</h2>
                        <div class="form-check">
                            <input type="checkbox" id="select-all" class="form-check-input">
                            <label class="form-check-label" for="select-all">Select All</label>
                        </div>
                        <hr>

                        <?php 
                        foreach ($pages as $page => $description): ?>
                            <div class="form-check">
                                <input type="checkbox" name="pages[]" value="<?php echo htmlspecialchars($page); ?>" 
                                    <?php echo in_array($page, $currentAccess) ? 'checked' : ''; ?> 
                                    class="form-check-input page-checkbox">
                                <label class="form-check-label"><?php echo htmlspecialchars($description); ?></label>
                            </div>
                        <?php endforeach; ?>

                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-primary btn-lg btn-block" onclick="return confirmChanges()">Save Changes</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Loading spinner -->
<div id="loading" style="display:none;">
    <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Loading...</span>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Show loading spinner during form submission
    $('#roleForm, #accessForm').on('submit', function() {
        $('#loading').show();
    });

    // Toggle all checkboxes
    $('#select-all').click(function() {
        var checked = this.checked;
        $('.page-checkbox').each(function() {
            this.checked = checked;
        });
    });

    // "Select All" checkbox status based on individual selections
    $('.page-checkbox').change(function() {
        if ($('.page-checkbox:checked').length === $('.page-checkbox').length) {
            $('#select-all').prop('checked', true);
        } else {
            $('#select-all').prop('checked', false);
        }
    });

    // Hide loading spinner after page load
    $(window).on('load', function() {
        $('#loading').hide();
    });

    // Hide success message after 5 seconds
    setTimeout(function() {
        $('#success-message').fadeOut('slow');
    }, 5000);
});

// Confirmation before submitting the form
function confirmChanges() {
    return confirm("Are you sure you want to save the changes?");
}
</script>

<?php
include('includes/scripts.php');
include('includes/footer.php');
ob_end_flush(); 
?>
