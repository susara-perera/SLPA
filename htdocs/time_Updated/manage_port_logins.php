<?php

session_start();
include('./dbc.php');

// Only allow admin (add your own admin check here)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Super_Ad') {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $port_name = $_POST['port_name'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if port login exists
    $stmt = mysqli_prepare($connect, "SELECT id FROM port_logins WHERE port_name = ?");
    mysqli_stmt_bind_param($stmt, "s", $port_name);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        // Update existing
        $stmt2 = mysqli_prepare($connect, "UPDATE port_logins SET username=?, password=? WHERE port_name=?");
        mysqli_stmt_bind_param($stmt2, "sss", $username, $password, $port_name);
        mysqli_stmt_execute($stmt2);
        $msg = "Updated login for $port_name";
    } else {
        // Insert new
        $stmt2 = mysqli_prepare($connect, "INSERT INTO port_logins (port_name, username, password) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt2, "sss", $port_name, $username, $password);
        mysqli_stmt_execute($stmt2);
        $msg = "Added login for $port_name";
    }
    mysqli_stmt_close($stmt);
}

// List of ports (reuse your $ports array)
$ports = [
    "Colombo Port", "Galle Port", "Trincomalee Port", "Hambantota Port",
    "Kankesanthurai Port", "Oluvil Port", "Point Pedro Port"
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Port Logins</title>
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Manage Port Logins</h2>
    <?php if (!empty($msg)) echo "<div class='alert alert-success'>$msg</div>"; ?>
    <form method="POST">
        <div class="form-group">
            <label>Port Name</label>
            <select name="port_name" class="form-control" required>
                <?php foreach ($ports as $port): ?>
                    <option value="<?= htmlspecialchars($port) ?>"><?= htmlspecialchars($port) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Username</label>
            <input name="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input name="password" type="password" class="form-control" required>
        </div>
        <button class="btn btn-primary">Save</button>
    </form>
</div>
</body>
</html>