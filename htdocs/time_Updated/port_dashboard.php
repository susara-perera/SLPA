<?php
session_start();
if (!isset($_SESSION['port_user']) || !isset($_SESSION['port_name'])) {
    header("Location: port_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Port Dashboard</title>
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['port_user']) ?>!</h2>
    <p>You are logged in for <?= htmlspecialchars($_SESSION['port_name']) ?>.</p>
    <a href="port_logout.php" class="btn btn-danger">Logout</a>
</div>
</body>
</html>