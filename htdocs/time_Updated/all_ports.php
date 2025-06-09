<?php
session_start();
// List of major ports in Sri Lanka
$ports = [
    ["name" => "Colombo Port", "lat" => 6.9538, "lng" => 79.8500],
    ["name" => "Galle Port", "lat" => 6.0351, "lng" => 80.2170],
    ["name" => "Trincomalee Port", "lat" => 8.5708, "lng" => 81.2332],
    ["name" => "Hambantota Port", "lat" => 6.1248, "lng" => 81.1185],
    ["name" => "Kankesanthurai Port", "lat" => 9.8150, "lng" => 80.0717],
    ["name" => "Oluvil Port", "lat" => 7.2522, "lng" => 81.8384],
    ["name" => "Point Pedro Port", "lat" => 9.8167, "lng" => 80.2333]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Ports In Sri Lanka</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f6f9; }
        .main-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            min-height: 100vh;
            z-index: 1030;
        }
        .content-wrapper {
            margin-left: 250px;
            background: #f4f6f9;
            min-height: 100vh;
            padding-bottom: 30px;
        }
        @media (max-width: 768px) {
            .content-wrapper { margin-left: 0; }
        }
        .dashboard-header {
            background: linear-gradient(90deg, #007bff 0%, #00c6ff 100%);
            color: #fff;
            padding: 30px 30px 20px 30px;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            margin-bottom: 30px;
        }
        .dashboard-header h1 {
            margin:0; font-weight:700; letter-spacing:1px; font-size:2.2rem;
        }
        .dashboard-header span { color: #ffe082; }
        .content-header { padding: 0 0 10px 0; }
        .card { 
            box-shadow: 0 2px 8px rgba(0,0,0,0.07); 
            border-radius: 12px; 
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }
        .card-title { font-weight: 600; }
        .search-box { max-width: 350px; margin-bottom: 20px; }
        .list-group-item { font-size: 1.1rem; }
        .list-group-item .fa-map-marker-alt { margin-right: 10px; }
        .list-group-item { transition: background 0.2s; }
        .list-group-item:hover { background: #e3f2fd; }
        @media (max-width: 600px) {
            .card { margin: 0 5px; }
            .dashboard-header { padding: 20px 10px 10px 10px; }
        }
        .center-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 60vh;
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
    </div>
    <section class="content-header">
        <div class="container-fluid">
            <h2 class="mb-2"><i class="fas fa-ship"></i> All Ports In Sri Lanka</h2>
            <p class="text-muted">Quickly view and search all major ports in Sri Lanka.</p>
        </div>
    </section>
    <!-- Place the map here, right after the section header -->
    <div id="sl-map" style="height: 400px; width: 100%; border-radius: 12px; margin: 0 auto 30px auto; box-shadow: 0 2px 8px rgba(0,0,0,0.07); max-width: 900px;"></div>
    <section class="content">
        <div class="center-container">
            <div class="card">
                <div class="card-header bg-primary">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <span class="card-title text-white"><i class="fas fa-anchor"></i> Ports List</span>
                        <div class="input-group search-box" style="max-width:350px;">
                            <input type="text" class="form-control" id="searchPort" placeholder="Search ports..." aria-label="Search ports">
                            <button class="btn btn-light" id="clearSearch" type="button" title="Clear search" style="border:1px solid #ced4da;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-group" id="portsList">
                        <?php foreach ($ports as $port): ?>
                            <li class="list-group-item d-flex align-items-center">
                                <i class="fas fa-map-marker-alt text-primary"></i>
                                <a href="port_login.php?port=<?= urlencode($port['name']) ?>" style="text-decoration:none;color:inherit;">
                                    <?= htmlspecialchars($port['name']) ?>
                                </a>
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