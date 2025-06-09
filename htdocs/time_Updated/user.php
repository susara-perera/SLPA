<?php
session_start();
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
    exit();
}



// Fetch divisions from the database
$divisions = [];
$divisionSql = "SELECT division_id, division_name FROM divisions";
$result = mysqli_query($connect, $divisionSql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $divisions[] = $row;
    }
} else {
    $error_message = "Failed to fetch divisions: " . mysqli_error($connect);
}
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200&display=swap" rel="stylesheet" />

<div class="container">
    <div class="row mx-md-n8">
        <div class="col px-md-5">
            <h1>Create User</h1>
            <div class="p-3 border bg-light">
                <div id="error-message" style="color: red; text-align: center; margin-bottom: 1rem;">
                    <?php
                    // Display error messages
                    if (isset($_GET['error'])) {
                        $error_messages = [
                            'password_mismatch' => 'Passwords do not match.',
                            'password_complexity' => 'Password must include at least one uppercase letter, one lowercase letter, one number, and one special character.',
                            'employee_id_already_exists' => 'An account with this Employee ID already exists. Please recheck your Employee ID and try again.',
                            'insert_failed' => 'Failed to create the user.',
                            'sql_error' => 'A database error occurred.',
                            'missing_data' => 'All fields are required.'
                        ];
                        echo $error_messages[$_GET['error']] ?? 'An unknown error occurred.';
                    }
                    
                    // Display success message
                    if (isset($_GET['success']) && $_GET['success'] === 'user_created') {
                        echo '<p style="color: green;">You have successfully created the account!</p>';
                    }
                    ?>
                </div>

                <!-- Display the form -->
                <form method="POST" action="./user_action.php" onsubmit="return validateForm()">
                    <div class="form-group">
                        <label for="division">Division:</label>
                        <select name="division" id="division" class="form-control" required>
                            <option value="" disabled selected>Select Division</option>
                            <?php foreach ($divisions as $division) : ?>
                                <option value="<?php echo htmlspecialchars($division['division_id']); ?>">
                                    <?php echo htmlspecialchars($division['division_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="role">User Role:</label>
                        <select name="role" id="role" class="form-control" required>
                            <option value="" disabled selected>Select User Role</option>
                            <option value="Super_Ad">Super Admin</option>
                            <option value="Administration">Administration</option>
                            <option value="Administration_clerk">Administrative Clerk</option>
                            <option value="clerk">Clerk</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="employee_ID">Employee ID:</label>
                        <input type="text" id="employee_ID" name="employee_ID" class="form-control" placeholder="Enter your Employee ID" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Create Password:</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
                    </div>

                    <div class="form-group">
                        <label for="re-password">Confirm Password:</label>
                        <input type="password" id="re-password" name="re-password" class="form-control" placeholder="Confirm Password" required>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-lg btn-block">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function validateForm() {
        var errorText = '';
        var password = document.getElementById("password").value;
        var confirmPassword = document.getElementById("re-password").value;
        var errorMessageDiv = document.getElementById("error-message");

        errorMessageDiv.innerHTML = ''; // Clear previous errors

        if (password !== confirmPassword) {
            errorText += 'Passwords do not match.<br>';
        }
        if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W]).{8,}$/.test(password)) {
            errorText += 'Password must include at least one uppercase letter, one lowercase letter, one number, and one special character.<br>';
        }

        if (errorText.length > 0) {
            errorMessageDiv.innerHTML = errorText;
            return false;
        }

        return true;
    }
</script>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>
