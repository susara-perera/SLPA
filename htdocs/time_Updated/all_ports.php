<?php
session_start();

// Include database connection
include_once 'dbc.php';

// Check if user is authenticated (optional - for future admin features)
$is_admin = isset($_SESSION['user_type']) && ($_SESSION['user_type'] === 'Super Admin' || $_SESSION['user_type'] === 'Admin');
$user_name = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';

// Database connection status
$db_connected = ($connect !== false);
$db_status = $db_connected ? 'Connected' : 'Disconnected';

// Function to create ports table if it doesn't exist
function createPortsTable($connect) {
    $sql = "CREATE TABLE IF NOT EXISTS ports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        port_name VARCHAR(100) NOT NULL UNIQUE,
        latitude DECIMAL(10, 8) NOT NULL,
        longitude DECIMAL(11, 8) NOT NULL,
        port_code VARCHAR(10) UNIQUE,
        status ENUM('Active', 'Inactive') DEFAULT 'Active',
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if (mysqli_query($connect, $sql)) {
        error_log("Ports table created successfully or already exists");
        return true;
    } else {
        error_log("Error creating ports table: " . mysqli_error($connect));
        return false;
    }
}

// Function to insert default ports if table is empty
function insertDefaultPorts($connect) {
    // Check if ports table has data
    $checkSql = "SELECT COUNT(*) as count FROM ports";
    $result = mysqli_query($connect, $checkSql);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['count'] == 0) {
        $defaultPorts = [
            ["Colombo Port", 6.9538, 79.8500, "CMB", "Main commercial port of Sri Lanka"],
            ["Galle Port", 6.0351, 80.2170, "GLE", "Historic port in the southern province"],
            ["Trincomalee Port", 8.5708, 81.2332, "TRN", "Natural deep water harbor in the east"],
            ["Hambantota Port", 6.1248, 81.1185, "HMB", "Modern port in the southern coast"],
            ["Kankesanthurai Port", 9.8150, 80.0717, "KKS", "Northern province port facility"],
            ["Oluvil Port", 7.2522, 81.8384, "OLV", "Eastern province fishing port"],
            ["Point Pedro Port", 9.8167, 80.2333, "PPD", "Northernmost port of Sri Lanka"]
        ];
        
        $insertSql = "INSERT INTO ports (port_name, latitude, longitude, port_code, description) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($connect, $insertSql);
        
        foreach ($defaultPorts as $port) {
            mysqli_stmt_bind_param($stmt, "sddss", $port[0], $port[1], $port[2], $port[3], $port[4]);
            mysqli_stmt_execute($stmt);
        }
        mysqli_stmt_close($stmt);
        error_log("Default ports inserted successfully");
    }
}

// Initialize ports table and get statistics
$table_created = false;
$ports_count = 0;
$active_ports_count = 0;
$error_message = '';

if ($db_connected) {
    $table_created = createPortsTable($connect);
    if ($table_created) {
        insertDefaultPorts($connect);
        
        // Get port statistics
        $stats_sql = "SELECT 
            COUNT(*) as total_ports,
            SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_ports
            FROM ports";
        $stats_result = mysqli_query($connect, $stats_sql);
        if ($stats_result) {
            $stats = mysqli_fetch_assoc($stats_result);
            $ports_count = $stats['total_ports'];
            $active_ports_count = $stats['active_ports'];
        }
    }
}

// Fetch ports from database
$ports = [];
if ($db_connected && $table_created) {
    $sql = "SELECT * FROM ports WHERE status = 'Active' ORDER BY port_name";
    $result = mysqli_query($connect, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $ports[] = [
                "id" => $row['id'],
                "name" => $row['port_name'],
                "lat" => (float)$row['latitude'],
                "lng" => (float)$row['longitude'],
                "code" => $row['port_code'],
                "description" => $row['description'],
                "status" => $row['status']
            ];
        }
    } else {
        $error_message = "No active ports found in database.";
    }
}

// If no ports found from database or database connection failed, use fallback
if (empty($ports)) {
    $error_message = $db_connected ? "No active ports found in database." : "Database connection failed.";
    // Fallback to static data
    $ports = [
        ["id" => 1, "name" => "Colombo Port", "lat" => 6.9538, "lng" => 79.8500, "code" => "CMB", "description" => "Main commercial port of Sri Lanka", "status" => "Active"],
        ["id" => 2, "name" => "Galle Port", "lat" => 6.0351, "lng" => 80.2170, "code" => "GLE", "description" => "Historic port in the southern province", "status" => "Active"],
        ["id" => 3, "name" => "Trincomalee Port", "lat" => 8.5708, "lng" => 81.2332, "code" => "TRN", "description" => "Natural deep water harbor in the east", "status" => "Active"],
        ["id" => 4, "name" => "Hambantota Port", "lat" => 6.1248, "lng" => 81.1185, "code" => "HMB", "description" => "Modern port in the southern coast", "status" => "Active"],
        ["id" => 5, "name" => "Kankesanthurai Port", "lat" => 9.8150, "lng" => 80.0717, "code" => "KKS", "description" => "Northern province port facility", "status" => "Active"],
        ["id" => 6, "name" => "Oluvil Port", "lat" => 7.2522, "lng" => 81.8384, "code" => "OLV", "description" => "Eastern province fishing port", "status" => "Active"],
        ["id" => 7, "name" => "Point Pedro Port", "lat" => 9.8167, "lng" => 80.2333, "code" => "PPD", "description" => "Northernmost port of Sri Lanka", "status" => "Active"]
    ];
    if (!$db_connected) {
        $ports_count = count($ports);
        $active_ports_count = count($ports);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Ports In Sri Lanka</title>
    <link rel="icon" type="image/jpeg" href="dist/img/logo.jpg">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        /* Professional Base Styling */
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .main-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            min-height: 100vh;
            z-index: 1030;
            background: #343a40 !important;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .content-wrapper {
            margin-left: 250px;
            background: #f8f9fa;
            min-height: 100vh;
            padding: 20px;
        }

        @media (max-width: 768px) {
            .content-wrapper { 
                margin-left: 0; 
                padding: 15px;
            }
        }

        /* Professional Header */
        .dashboard-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            margin-bottom: 30px;
            border-left: 4px solid #3498db;
        }

        .dashboard-header h1 {
            margin: 0; 
            font-weight: 600; 
            font-size: 1.8rem;
            letter-spacing: 0.5px;
        }

        .dashboard-header span { 
            color: #3498db; 
            font-weight: 400;
        }

        /* Status Bar */
        .status-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(52, 152, 219, 0.3);
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 15px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .status-item:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-1px);
        }

        .status-label {
            font-size: 0.85rem;
            opacity: 0.9;
            font-weight: 500;
        }

        .status-value {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .status-value i {
            margin-right: 4px;
            font-size: 0.9rem;
        }

        .text-success { color: #27ae60 !important; }
        .text-danger { color: #e74c3c !important; }
        .text-info { color: #3498db !important; }
        .text-primary { color: #2980b9 !important; }

        .alert {
            margin-top: 15px;
            border: none;
            border-radius: 6px;
            padding: 12px 15px;
            font-size: 0.9rem;
        }

        .alert-warning {
            background: rgba(241, 196, 15, 0.2);
            color: #f39c12;
            border-left: 4px solid #f39c12;
        }

        /* Content Header */
        .content-header { 
            padding: 0 0 25px 0; 
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 25px;
        }

        .content-header h2 {
            color: #2c3e50;
            font-weight: 600;
            font-size: 1.6rem;
            margin-bottom: 8px;
        }

        .content-header p {
            color: #6c757d;
            font-size: 1rem;
            margin: 0;
        }

        /* Professional Map Styling */
        #sl-map {
            height: 400px;
            width: 100%;
            border-radius: 8px;
            margin: 0 0 30px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border: 1px solid #dee2e6;
        }

        /* Map Container */
        .map-container {
            max-width: 100%;
            margin: 0 0 30px 0;
            padding: 0;
        }

        /* Clean Card Design */
        .card { 
            background: #ffffff;
            border: 1px solid #dee2e6;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            width: 100%;
            margin: 0;
            overflow: hidden;
        }

        .card-header {
            background: #f8f9fa !important;
            border-bottom: 1px solid #dee2e6;
            padding: 20px 25px;
        }

        .card-title { 
            font-weight: 600; 
            font-size: 1.2rem;
            color: #2c3e50 !important;
            margin: 0;
        }

        .card-body {
            padding: 0;
            background: #ffffff;
        }

        /* Professional Search Box */
        .search-box { 
            max-width: 300px; 
            margin-bottom: 0;
        }

        .search-box .form-control {
            background: #ffffff;
            border: 1px solid #ced4da;
            color: #495057;
            border-radius: 4px 0 0 4px;
            padding: 10px 15px;
            font-size: 0.9rem;
        }

        .search-box .form-control::placeholder {
            color: #6c757d;
        }

        .search-box .form-control:focus {
            background: #ffffff;
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
            color: #495057;
        }

        .search-box .btn {
            background: #3498db;
            border: 1px solid #3498db;
            color: #ffffff;
            border-radius: 0 4px 4px 0;
            padding: 10px 15px;
            transition: all 0.2s ease;
        }

        .search-box .btn:hover {
            background: #2980b9;
            border-color: #2980b9;
        }

        /* Clean List Items */
        .list-group {
            border-radius: 0;
            margin: 0;
        }

        .list-group-item { 
            background: #ffffff;
            border: none;
            border-bottom: 1px solid #e9ecef;
            color: #495057;
            font-size: 1rem;
            padding: 20px 25px;
            transition: background-color 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .list-group-item:hover {
            background: #f8f9fa;
            transform: translateX(5px);
        }

        .list-group-item:last-child {
            border-bottom: none;
        }

        .list-group-item .fa-map-marker-alt { 
            margin-right: 12px; 
            color: #3498db;
            font-size: 1.2rem;
        }

        .list-group-item .fa-map-pin {
            margin-right: 5px;
            color: #6c757d;
        }

        .list-group-item a {
            text-decoration: none !important;
            color: #2c3e50 !important;
            font-weight: 600;
            font-size: 1.1rem;
            transition: color 0.2s ease;
        }

        .list-group-item:hover a {
            color: #3498db !important;
        }

        .list-group-item .fw-bold {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .list-group-item .text-muted {
            color: #6c757d !important;
            font-size: 0.9rem;
            margin-bottom: 4px;
        }

        .list-group-item .small {
            font-size: 0.8rem;
            color: #17a2b8 !important;
        }

        /* Professional Badge */
        .badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
            margin: 0 4px;
        }

        .bg-primary {
            background-color: #3498db !important;
            color: #ffffff;
        }

        .bg-success {
            background-color: #27ae60 !important;
            color: #ffffff;
        }

        .btn-outline-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: 1px solid #007bff;
            color: #ffffff;
            padding: 8px 16px;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 123, 255, 0.2);
        }

        .btn-outline-primary:hover {
            background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
            border-color: #0056b3;
            color: #ffffff;
            text-decoration: none;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
        }

        .btn-outline-primary:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 123, 255, 0.2);
        }

        .btn-outline-primary:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }

        .gap-2 {
            gap: 0.5rem !important;
        }

        .list-group-item:hover .badge {
            transform: scale(1.05);
        }

        /* No Results Message */
        #noResults {
            color: #6c757d !important;
            text-align: center;
            font-style: italic;
            background: #f8f9fa !important;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .card { 
                margin: 0;
                max-width: 100%;
            }
            .dashboard-header { 
                padding: 20px; 
                margin: 0 0 25px 0;
            }
            .dashboard-header h1 {
                font-size: 1.5rem;
            }
            .status-bar {
                gap: 10px;
                margin-top: 15px;
                padding-top: 15px;
            }
            .status-item {
                padding: 6px 12px;
                font-size: 0.8rem;
            }
            .status-label {
                display: none;
            }
            .content-header h2 {
                font-size: 1.4rem;
            }
            .card-header {
                padding: 15px 20px;
                flex-direction: column;
                gap: 15px;
            }
            .search-box {
                max-width: 100%;
            }
            #sl-map {
                margin: 0 0 25px 0;
                height: 300px;
            }
            .map-container {
                margin: 0 0 25px 0;
            }
        }

        .center-container {
            width: 100%;
            padding: 0;
            margin: 0;
        }

        /* Professional Icons */
        .fas.fa-ship, .fas.fa-anchor, .fas.fa-user-shield {
            margin-right: 8px;
            color: #3498db;
        }

        /* Table-like alternating rows */
        .list-group-item:nth-child(even) {
            background: #fbfbfb;
        }

        .list-group-item:nth-child(even):hover {
            background: #f1f3f4;
        }

        /* Professional hover effects */
        .card:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
            transition: box-shadow 0.3s ease;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
<?php include "includes/navbar.php"; ?>
<div class="content-wrapper">
    <div class="dashboard-header">
        <h1>
            <i class="fas fa-user-shield"></i> ADMIN DASHBOARD <span>Sri Lanka Port Authority</span>
        </h1>
        <div class="status-bar">
            <div class="status-item">
                <span class="status-label">Database:</span>
                <span class="status-value <?= $db_connected ? 'text-success' : 'text-danger' ?>">
                    <i class="fas fa-<?= $db_connected ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                    <?= $db_status ?>
                </span>
            </div>
            <div class="status-item">
                <span class="status-label">User:</span>
                <span class="status-value text-info">
                    <i class="fas fa-user"></i>
                    <?= htmlspecialchars($user_name) ?>
                </span>
            </div>
            <div class="status-item">
                <span class="status-label">Total Ports:</span>
                <span class="status-value text-primary">
                    <i class="fas fa-anchor"></i>
                    <?= $ports_count ?>
                </span>
            </div>
            <div class="status-item">
                <span class="status-label">Active:</span>
                <span class="status-value text-success">
                    <i class="fas fa-circle"></i>
                    <?= $active_ports_count ?>
                </span>
            </div>
        </div>
        <?php if (!empty($error_message)): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <?= htmlspecialchars($error_message) ?>
            <?= !$db_connected ? ' Using fallback data.' : '' ?>
        </div>
        <?php endif; ?>
    </div>
    <section class="content-header">
        <div class="container-fluid">
            <h2><i class="fas fa-ship"></i> Port Management System</h2>
            <p>Comprehensive directory of all major ports in Sri Lanka</p>
        </div>
    </section>
    
    <!-- Map Section -->
    <div class="map-container">
        <div id="sl-map"></div>
    </div>
    <section class="content">
        <div class="center-container">
            <div class="card">
                <div class="card-header bg-primary">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <span class="card-title text-white"><i class="fas fa-anchor"></i> Ports Directory</span>
                        <div class="input-group search-box">
                            <input type="text" class="form-control" id="searchPort" placeholder="Search ports..." aria-label="Search ports">
                            <button class="btn" id="clearSearch" type="button" title="Clear search">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-group" id="portsList">
                        <?php foreach ($ports as $index => $port): ?>
                            <li class="list-group-item d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <div class="ms-3">
                                        <div class="fw-bold">
                                            <a href="port_login.php?port=<?= urlencode($port['name']) ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($port['name']) ?>
                                            </a>
                                        </div>
                                        <?php if (isset($port['description']) && !empty($port['description'])): ?>
                                            <small class="text-muted"><?= htmlspecialchars($port['description']) ?></small>
                                        <?php endif; ?>
                                        <div class="small text-info">
                                            <i class="fas fa-map-pin"></i> 
                                            Lat: <?= number_format($port['lat'], 4) ?>, 
                                            Lng: <?= number_format($port['lng'], 4) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if (isset($port['code'])): ?>
                                        <span class="badge bg-primary"><?= htmlspecialchars($port['code']) ?></span>
                                    <?php endif; ?>
                                    <span class="badge bg-success">
                                        <?= isset($port['status']) ? htmlspecialchars($port['status']) : 'Active' ?>
                                    </span>
                                    <a href="port_login.php?port=<?= urlencode($port['name']) ?>" 
                                       class="btn btn-sm btn-outline-primary"
                                       title="Login to <?= htmlspecialchars($port['name']) ?>"
                                       aria-label="Login to port <?= htmlspecialchars($port['name']) ?>">
                                        <i class="fas fa-sign-in-alt"></i>
                                        <span>Login</span>
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</div>
<!-- AdminLTE JS -->
<script src="plugins/jquery/jquery.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    // PHP to JS: Pass ports array
    const ports = <?php echo json_encode($ports); ?>;

    // Initialize map centered on Sri Lanka
    var map = L.map('sl-map').setView([7.8731, 80.7718], 7);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Add markers for each port
    ports.forEach(function(port) {
        L.marker([port.lat, port.lng])
            .addTo(map)
            .bindPopup('<b>' + port.name + '</b>');
    });

    // Enhanced search/filter for ports with clear button and accessibility
    const searchInput = document.getElementById('searchPort');
    const clearBtn = document.getElementById('clearSearch');
    const portsList = document.getElementById('portsList');

    function filterPorts() {
        const filter = searchInput.value.toLowerCase();
        let anyVisible = false;
        // Remove previous "No ports found" message if exists
        const noResults = document.getElementById('noResults');
        if (noResults) noResults.remove();

        portsList.querySelectorAll('li').forEach(function(item) {
            const text = item.textContent.toLowerCase();
            const match = text.includes(filter);
            item.style.display = match ? '' : 'none';
            if (match) anyVisible = true;
        });

        // Show a "No ports found" message if nothing matches
        if (!anyVisible) {
            const noResults = document.createElement('li');
            noResults.id = 'noResults';
            noResults.className = 'list-group-item text-danger';
            noResults.textContent = 'No ports found.';
            portsList.appendChild(noResults);
        }
    }

    searchInput.addEventListener('keyup', filterPorts);
    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        filterPorts();
        searchInput.focus();
    });
</script>
</body>
</html>