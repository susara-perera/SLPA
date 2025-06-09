<?php

include_once './dbc.php';
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['role'], $_POST['password'])) {
    $role = $_POST['role'];
    $password = trim($_POST['password']); // Trim Password to remove accidental whitespaces

    // SQL query to fetch details
    $sql = "SELECT id, role, employee_ID, pwd FROM users WHERE role = ?";
    $stmt = mysqli_prepare($connect, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $role);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $userFound = false;
        while ($user = mysqli_fetch_assoc($result)) {
            // Debugging: Print fetched data
            error_log("Fetched User Data: " . print_r($user, true));

            if (password_verify($password, $user['pwd'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['employee_ID'] = $user['employee_ID'];
                $userFound = true;

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

                break;
            } else {
                // Debugging: Print password verification failure
                error_log("Password verification failed for user: " . $user['employee_ID']);
            }
        }

        mysqli_stmt_close($stmt);

        if ($userFound) {
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['login_error'] = 'Invalid password. Please try again.';
            header("Location: login.php?error=password_incorrect");
            exit();
        }
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
