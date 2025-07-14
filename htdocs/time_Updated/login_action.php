<?php

include_once './dbc.php';
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['user_id'], $_POST['role'], $_POST['password'])) {
    $user_id = trim($_POST['user_id']);
    $role = $_POST['role'];
    $password = trim($_POST['password']); // Trim Password to remove accidental whitespaces

    // Convert user ID to uppercase for consistency
    $user_id = strtoupper($user_id);

    // SQL query to fetch details based on both user_id and role for security
    $sql = "SELECT id, role, employee_ID, pwd FROM users WHERE employee_ID = ? AND role = ?";
    $stmt = mysqli_prepare($connect, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $user_id, $role);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            // Debugging: Print fetched data
            error_log("Fetched User Data: " . print_r($user, true));

            if (password_verify($password, $user['pwd'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['employee_ID'] = $user['employee_ID'];
                
                // Insert login record
                $sqlInsert = "INSERT INTO login (user_id, login_time, status) VALUES (?, NOW(), 'Active')";
                $stmtInsert = mysqli_prepare($connect, $sqlInsert);
                if ($stmtInsert) {
                    mysqli_stmt_bind_param($stmtInsert, "i", $_SESSION['user_id']);
                    mysqli_stmt_execute($stmtInsert);
                    mysqli_stmt_close($stmtInsert);
                } else {
                    // Handle SQL error
                    error_log('Database error during login insert. Please contact the administrator.');
                    $_SESSION['login_error'] = 'Database error during login. Please contact the administrator.';
                    header("Location: login.php");
                    exit();
                }

                // Successful login - redirect to appropriate page
                header("Location: index.php");
                exit();
            } else {
                // Debugging: Print password verification failure
                error_log("Password verification failed for user: " . $user['employee_ID']);
                $_SESSION['login_error'] = 'Invalid password. Please try again.';
                header("Location: login.php?error=password_incorrect");
                exit();
            }
        } else {
            // User not found or role mismatch
            error_log("User not found or role mismatch for User ID: " . $user_id . " with role: " . $role);
            $_SESSION['login_error'] = 'Invalid User ID or credentials. Please check your User ID.';
            header("Location: login.php?error=user_not_found");
            exit();
        }

        mysqli_stmt_close($stmt);
    } else {
        error_log('SQL prepare error: ' . mysqli_error($connect));
        $_SESSION['login_error'] = 'Database error. Please contact the administrator.';
        header("Location: login.php");
        exit();
    }
} else {
    $_SESSION['login_error'] = 'Please fill in all required fields.';
    header("Location: login.php");
    exit();
}
