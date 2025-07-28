<?php
// Create a simple test session for port dashboard
session_start();

// Set test session variables
$_SESSION['port_user'] = 'test_admin';
$_SESSION['port_name'] = 'Test Port';

// Redirect to port dashboard
header("Location: port_dashboard.php");
exit();
?>
