<?php


include_once './dbc.php';
session_start();

// Restrict this page to users who are logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

$userId = $_SESSION['user_id'];
$logoutTime = date('Y-m-d H:i:s');

// Update logout time and status for the user
$sqlUpdate = "UPDATE login SET logout_time = ?, status = 'Logged Out' WHERE user_id = ? AND logout_time IS NULL";
$stmtUpdate = mysqli_prepare($connect, $sqlUpdate);
if ($stmtUpdate) {
    mysqli_stmt_bind_param($stmtUpdate, "si", $logoutTime, $userId);
    mysqli_stmt_execute($stmtUpdate);
    if (mysqli_stmt_affected_rows($stmtUpdate) > 0) {
        // Logout successful
    } else {
        // Error handling
        error_log("Logout update failed for user ID: $userId");
    }
    mysqli_stmt_close($stmtUpdate);
} else {
    // SQL error
    error_log("SQL error during logout: " . mysqli_error($connect));
}

// Close database connection
mysqli_close($connect);

// Destroy session and redirect to login
session_unset();
session_destroy();
header('Location: login.php');
