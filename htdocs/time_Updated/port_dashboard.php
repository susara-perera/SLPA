<?php
session_start();
if (!isset($_SESSION['port_user']) || !isset($_SESSION['port_name'])) {
    header("Location: port_login.php");
    exit();
}

// Example: You can fetch port-specific data here if you have a database.
// For now, we'll use the same $ports array as in all_ports.php for demo.
$ports = [
    ["name" => "Colombo Port", "lat" => 6.9538, "lng" => 79.8500],
    ["name" => "Galle Port", "lat" => 6.0351, "lng" => 80.2170],
    ["name" => "Trincomalee Port", "lat" => 8.5708, "lng" => 81.2332],
    ["name" => "Hambantota Port", "lat" => 6.1248, "lng" => 81.1185],
    ["name" => "Kankesanthurai Port", "lat" => 9.8150, "lng" => 80.0717],
    ["name" => "Oluvil Port", "lat" => 7.2522, "lng" => 81.8384],
    ["name" => "Point Pedro Port", "lat" => 9.8167, "lng" => 80.2333]
];

// Find the current port's details
$current_port = null;
foreach ($ports as $port) {
    if ($port['name'] === $_SESSION['port_name']) {
        $current_port = $port;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($_SESSION['port_name']) ?> Dashboard</title>
    <link rel="icon" type="image/jpeg" href="dist/img/logo.jpg">
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f6f9; }
        .dashboard-header {
            background: linear-gradient(90deg, #007bff 0%, #00c6ff 100%);
            color: #fff;
            padding: 30px 30px 20px 30px;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            margin-bottom: 30px;
        }
        .dashboard-header h1 { margin:0; font-weight:700; letter-spacing:1px; font-size:2.2rem; }
        .dashboard-header span { color: #ffe082; }
        .content-header { padding: 0 0 10px 0; }
        .card { box-shadow: 0 2px 8px rgba(0,0,0,0.07); border-radius: 12px; width: 100%; max-width: 600px; margin: 0 auto; }
        .card-title { font-weight: 600; }
        .center-container { display: flex; justify-content: center; align-items: center; min-height: 60vh; }
        .logout-btn { float: right; }
    </style>
</head>
<body>
<?php include "includes/navbar.php"; ?>
<div class="content-wrapper">
    <div class="dashboard-header">
        <h1>
            <i class="fas fa-ship"></i> <?= htmlspecialchars($_SESSION['port_name']) ?> <span>Dashboard</span>
            <a href="port_logout.php" class="btn btn-danger logout-btn">Logout</a>
        </h1>
        <p>Welcome, <?= htmlspecialchars($_SESSION['port_user']) ?>!</p>
    </div>
    <section class="content-header">
        <div class="container-fluid">
            <h2 class="mb-2"><i class="fas fa-info-circle"></i> Port Information</h2>
            <p class="text-muted">Location and details for <?= htmlspecialchars($_SESSION['port_name']) ?>.</p>
        </div>
    </section>
    <div id="port-map" style="height: 350px; width: 100%; border-radius: 12px; margin: 0 auto 30px auto; box-shadow: 0 2px 8px rgba(0,0,0,0.07); max-width: 900px;"></div>
    <section class="content">
        <div class="center-container" style="flex-direction:column;align-items:stretch;gap:30px;">
            <!-- Ship Schedules -->
            <div class="card">
                <div class="card-header bg-info">
                    <span class="card-title text-white"><i class="fas fa-ship"></i> Ship Schedules</span>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Ship Name</th>
                                <th>Arrival</th>
                                <th>Departure</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>MV Lanka Queen</td><td>2025-06-10 08:00</td><td>2025-06-11 18:00</td><td>Docked</td></tr>
                            <tr><td>MV Ocean Pearl</td><td>2025-06-12 10:00</td><td>2025-06-13 16:00</td><td>Expected</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Cargo Details -->
            <div class="card">
                <div class="card-header bg-success">
                    <span class="card-title text-white"><i class="fas fa-boxes"></i> Cargo Details</span>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Cargo ID</th>
                                <th>Description</th>
                                <th>Weight (tons)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>C123</td><td>Tea Crates</td><td>50</td><td>Unloaded</td></tr>
                            <tr><td>C124</td><td>Machinery</td><td>120</td><td>In Transit</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Staff List -->
            <div class="card">
                <div class="card-header bg-warning">
                    <span class="card-title text-white"><i class="fas fa-users"></i> Staff List</span>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Contact</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>John Silva</td><td>Supervisor</td><td>071-1234567</td></tr>
                            <tr><td>Priya Perera</td><td>Loader</td><td>077-9876543</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Notifications -->
            <div class="card">
                <div class="card-header bg-danger">
                    <span class="card-title text-white"><i class="fas fa-bell"></i> Notifications</span>
                </div>
                <div class="card-body">
                    <ul>
                        <li>06/09/2025: Heavy rain expected tomorrow. Take precautions.</li>
                        <li>06/08/2025: New safety guidelines issued for cargo handling.</li>
                    </ul>
                </div>
            </div>
            <!-- Reports -->
            <div class="card">
                <div class="card-header bg-secondary">
                    <span class="card-title text-white"><i class="fas fa-chart-line"></i> Reports</span>
                </div>
                <div class="card-body">
                    <ul>
                        <li><a href="#">Download Monthly Ship Movement Report (PDF)</a></li>
                        <li><a href="#">Download Cargo Summary (Excel)</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</div>
<script src="plugins/jquery/jquery.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    // Show only the current port on the map
    var map = L.map('port-map').setView([<?= $current_port['lat'] ?>, <?= $current_port['lng'] ?>], 10);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    L.marker([<?= $current_port['lat'] ?>, <?= $current_port['lng'] ?>])
        .addTo(map)
        .bindPopup('<b><?= htmlspecialchars($current_port['name']) ?></b>');
</script>
</body>
</html>