<?php
// Create a test session for debugging
session_start();

// Set test session variables
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'Admin Test';

echo "Test session created. Now redirecting to generate_report.php...<br>";
echo "<a href='generate_report.php'>Click here to access the report</a>";
?>
