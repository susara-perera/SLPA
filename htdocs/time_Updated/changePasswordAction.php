<?php
include_once './dbc.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['current_password'], $_POST['new_password'], $_POST['confirm_password'])) {
    $currentPassword = mysqli_real_escape_string($connect, $_POST['current_password']);
    $newPassword = mysqli_real_escape_string($connect, $_POST['new_password']);
    $confirmPassword = mysqli_real_escape_string($connect, $_POST['confirm_password']);

    $errorMessages = [];

    // Verify current password
    $stmt = mysqli_prepare($connect, "SELECT pwd FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $hashedPassword);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (!password_verify($currentPassword, $hashedPassword)) {
        $errorMessages[] = "Current password is incorrect.";
    }

    if ($newPassword !== $confirmPassword) {
        $errorMessages[] = "New passwords do not match.";
    }

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W]).{8,}$/', $newPassword)) {
        $errorMessages[] = "Password must include at least one uppercase letter, one lowercase letter, one number, and one special character.";
    }

    if (empty($errorMessages)) {
        $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateStmt = mysqli_prepare($connect, "UPDATE users SET pwd = ? WHERE id = ?");
        mysqli_stmt_bind_param($updateStmt, "si", $newHashedPassword, $_SESSION['user_id']);
        mysqli_stmt_execute($updateStmt);

        if (mysqli_stmt_affected_rows($updateStmt) > 0) {
            $_SESSION['success_message'] = "Password changed successfully.";
        } else {
            $errorMessages[] = "Failed to change password.";
        }
        mysqli_stmt_close($updateStmt);
    }

    if (!empty($errorMessages)) {
        $_SESSION['error_messages'] = $errorMessages;
    }

    header("Location: ChangePassword.php");
    exit();
} else {
    header("Location: ChangePassword.php");
    exit();
}
