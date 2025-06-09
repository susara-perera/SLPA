<?php
include_once './dbc.php';
session_start();

// Restricted this page for super admins only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Super_Ad') {
    header("Location: index.php?error=unauthorized_access");
    exit();
}

if (isset($_POST['division'], $_POST['role'], $_POST['employee_ID'], $_POST['password'], $_POST['re-password'])) {
    $division = mysqli_real_escape_string($connect, $_POST['division']);
    $role = mysqli_real_escape_string($connect, $_POST['role']);
    $employeeID = mysqli_real_escape_string($connect, $_POST['employee_ID']);
    $password = mysqli_real_escape_string($connect, $_POST['password']);
    $confirmPassword = $_POST['re-password'];

    if ($password !== $confirmPassword) {
        header("Location: user.php?error=password_mismatch");
        exit();
    }

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W]).{8,}$/', $password)) {
        header("Location: user.php?error=password_complexity");
        exit();
    }

    // Check if employee_ID already exists
    $sqlCheck = "SELECT COUNT(*) FROM users WHERE employee_id = ?";
    $stmtCheck = mysqli_prepare($connect, $sqlCheck);
    mysqli_stmt_bind_param($stmtCheck, "s", $employeeID);
    mysqli_stmt_execute($stmtCheck);
    mysqli_stmt_bind_result($stmtCheck, $count);
    mysqli_stmt_fetch($stmtCheck);
    mysqli_stmt_close($stmtCheck);

    if ($count > 0) {
        header("Location: user.php?error=employee_id_already_exists");
        exit();
    }

    // Proceed with creating the user
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (division, role, employee_id, pwd) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($connect, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", $division, $role, $employeeID, $hashedPassword);
        mysqli_stmt_execute($stmt);
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            header("Location: user.php?success=user_created");
        } else {
            header("Location: user.php?error=insert_failed");
        }
    } else {
        header("Location: user.php?error=sql_error");
    }
    mysqli_stmt_close($stmt);
    mysqli_close($connect);
} else {
    header("Location: user.php?error=missing_data");
}
