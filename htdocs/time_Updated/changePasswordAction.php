<?php
session_start();
ob_start(); 

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); 
    exit();
}

include_once './dbc.php';

// CSRF protection - you might want to implement this
// if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
//     $_SESSION['error_messages'] = ['Invalid request. Please try again.'];
//     header("Location: changePassword.php");
//     exit();
// }

if (isset($_POST['current_password'], $_POST['new_password'], $_POST['confirm_password'])) {
    $currentPassword = trim($_POST['current_password']);
    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);

    $errorMessages = [];

    // Input validation
    if (empty($currentPassword)) {
        $errorMessages[] = "Current password is required.";
    }

    if (empty($newPassword)) {
        $errorMessages[] = "New password is required.";
    }

    if (empty($confirmPassword)) {
        $errorMessages[] = "Password confirmation is required.";
    }

    // Password match validation
    if ($newPassword !== $confirmPassword) {
        $errorMessages[] = "New passwords do not match.";
    }

    // Password strength validation
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W]).{8,}$/', $newPassword)) {
        $errorMessages[] = "Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.";
    }

    // Check if new password is same as current password
    if ($currentPassword === $newPassword) {
        $errorMessages[] = "New password must be different from current password.";
    }

    // If no validation errors, proceed with password verification and update
    if (empty($errorMessages)) {
        try {
            // Verify current password
            $stmt = mysqli_prepare($connect, "SELECT pwd FROM users WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Database prepare statement failed: " . mysqli_error($connect));
            }

            mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Database execute failed: " . mysqli_stmt_error($stmt));
            }

            mysqli_stmt_bind_result($stmt, $hashedPassword);
            
            if (!mysqli_stmt_fetch($stmt)) {
                mysqli_stmt_close($stmt);
                $errorMessages[] = "User not found.";
            } else {
                mysqli_stmt_close($stmt);

                if (!password_verify($currentPassword, $hashedPassword)) {
                    $errorMessages[] = "Current password is incorrect.";
                } else {
                    // Current password is correct, update to new password
                    $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    
                    $updateStmt = mysqli_prepare($connect, "UPDATE users SET pwd = ?, updated_at = NOW() WHERE id = ?");
                    if (!$updateStmt) {
                        throw new Exception("Database prepare statement failed: " . mysqli_error($connect));
                    }

                    mysqli_stmt_bind_param($updateStmt, "si", $newHashedPassword, $_SESSION['user_id']);
                    
                    if (!mysqli_stmt_execute($updateStmt)) {
                        throw new Exception("Password update failed: " . mysqli_stmt_error($updateStmt));
                    }

                    if (mysqli_stmt_affected_rows($updateStmt) > 0) {
                        $_SESSION['success_message'] = "Password changed successfully! Please remember your new password.";
                        
                        // Log the password change activity (optional)
                        $logStmt = mysqli_prepare($connect, "INSERT INTO user_activity_log (user_id, activity, timestamp) VALUES (?, 'Password Changed', NOW())");
                        if ($logStmt) {
                            mysqli_stmt_bind_param($logStmt, "i", $_SESSION['user_id']);
                            mysqli_stmt_execute($logStmt);
                            mysqli_stmt_close($logStmt);
                        }
                    } else {
                        $errorMessages[] = "Failed to change password. No changes were made.";
                    }
                    mysqli_stmt_close($updateStmt);
                }
            }
        } catch (Exception $e) {
            error_log("Password change error for user " . $_SESSION['user_id'] . ": " . $e->getMessage());
            $errorMessages[] = "An error occurred while changing your password. Please try again later.";
        }
    }

    // Store error messages in session if any
    if (!empty($errorMessages)) {
        $_SESSION['error_messages'] = $errorMessages;
    }

    // Close database connection
    mysqli_close($connect);

    // Redirect back to password change page
    header("Location: changePassword.php");
    exit();
} else {
    // Invalid request - missing required fields
    $_SESSION['error_messages'] = ['Invalid request. All fields are required.'];
    header("Location: changePassword.php");
    exit();
}
?>
