<?php
include_once './dbc.php';
session_start();

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to log activity
function logActivity($message) {
    error_log(date('Y-m-d H:i:s') . " - " . $message);
}

if (isset($_POST['user_id'], $_POST['role'], $_POST['password'])) {
    $user_id = trim($_POST['user_id']);
    $role = $_POST['role'];
    $password = trim($_POST['password']);

    // Convert user ID to uppercase for consistency
    $user_id = strtoupper($user_id);
    
    logActivity("Login attempt for User ID: $user_id with role: $role");

    // Check database connection
    if (!$connect) {
        logActivity("Database connection failed during login");
        $_SESSION['login_error'] = 'Database connection error. Please contact the administrator.';
        header("Location: login.php?error=db_connection");
        exit();
    }

    // SQL query to fetch user details based on employee_ID and role
    $sql = "SELECT id, role, employee_ID, pwd FROM users WHERE employee_ID = ? AND role = ?";
    $stmt = mysqli_prepare($connect, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $user_id, $role);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            logActivity("User found in database: " . $user['employee_ID']);

            // Verify password
            if (password_verify($password, $user['pwd'])) {
                // Password is correct - set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['employee_ID'] = $user['employee_ID'];
                $_SESSION['username'] = $user['employee_ID']; // For compatibility
                $_SESSION['user_type'] = $user['role']; // For compatibility
                
                logActivity("Successful login for user: " . $user['employee_ID']);
                
                // Insert login record into login table
                $sqlInsert = "INSERT INTO login (user_id, login_time, status) VALUES (?, NOW(), 'Active')";
                $stmtInsert = mysqli_prepare($connect, $sqlInsert);
                if ($stmtInsert) {
                    mysqli_stmt_bind_param($stmtInsert, "i", $_SESSION['user_id']);
                    if (mysqli_stmt_execute($stmtInsert)) {
                        logActivity("Login record inserted for user ID: " . $_SESSION['user_id']);
                    } else {
                        logActivity("Failed to insert login record: " . mysqli_error($connect));
                    }
                    mysqli_stmt_close($stmtInsert);
                } else {
                    logActivity("Failed to prepare login insert statement: " . mysqli_error($connect));
                }

                // Redirect based on role
                switch ($user['role']) {
                    case 'Super Admin':
                    case 'Admin':
                        header("Location: index.php");
                        break;
                    case 'Employee':
                        header("Location: user.php");
                        break;
                    default:
                        header("Location: index.php");
                        break;
                }
                exit();
            } else {
                logActivity("Password verification failed for user: " . $user['employee_ID']);
                $_SESSION['login_error'] = 'Invalid password. Please try again.';
                header("Location: login.php?error=password_incorrect");
                exit();
            }
        } else {
            logActivity("User not found or role mismatch for User ID: $user_id with role: $role");
            $_SESSION['login_error'] = 'Invalid User ID or role. Please check your credentials.';
            header("Location: login.php?error=user_not_found");
            exit();
        }

        mysqli_stmt_close($stmt);
    } else {
        logActivity("SQL prepare error: " . mysqli_error($connect));
        $_SESSION['login_error'] = 'Database error. Please contact the administrator.';
        header("Location: login.php?error=sql_error");
        exit();
    }
} else {
    logActivity("Incomplete form submission - missing required fields");
    $_SESSION['login_error'] = 'Please fill in all required fields.';
    header("Location: login.php?error=missing_fields");
    exit();
}
?>
