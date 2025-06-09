<?php
session_start();

// Clear previous port session data before new login
unset($_SESSION['port_user']);
unset($_SESSION['port_name']);

// Hardcoded port logins: [port_name][username] => password
$port_logins = [
    "Colombo Port" => ["ColomboAdmin" => "Colombo123!"],
    "Galle Port" => ["GalleAdmin" => "Galle123!"],
    "Trincomalee Port" => ["TrincomaleeAdmin" => "Trincomalee123!"],
    "Hambantota Port" => ["HambantotaAdmin" => "Hambantota123!"],
    "Kankesanthurai Port" => ["KankesanthuraiAdmin" => "Kankesanthurai123!"],
    "Oluvil Port" => ["OluvilAdmin" => "Oluvil123!"],
    "Point Pedro Port" => ["Point_PedroAdmin" => "Point_Pedro123!"]
];

$error = '';
$selected_port = isset($_GET['port']) ? $_GET['port'] : (isset($_POST['port_name']) ? $_POST['port_name'] : '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $port_name = $_POST['port_name'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (
        isset($port_logins[$port_name]) &&
        isset($port_logins[$port_name][$username]) &&
        $port_logins[$port_name][$username] === $password
    ) {
        session_regenerate_id(true); // Add this line
        $_SESSION['port_user'] = $username;
        $_SESSION['port_name'] = $port_name;
        header("Location: port_dashboard.php");
        exit();
    } else {
        $error = "Invalid credentials";
    }
}

if (!$selected_port) {
    header("Location: all_ports.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Port Login</title>
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Login for <?= htmlspecialchars($selected_port) ?></h2>
    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST">
        <input type="hidden" name="port_name" value="<?= htmlspecialchars($selected_port) ?>">
        <div class="form-group">
            <label>Username</label>
            <input name="username" class="form-control" required autocomplete="off" value="">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input name="password" type="password" class="form-control" required autocomplete="off" value="">
        </div>
        <button class="btn btn-primary">Login</button>
    </form>
</div>
</body>
</html>