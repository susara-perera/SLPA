<?php
session_start();
if (!isset($_SESSION['port_user']) || !isset($_SESSION['port_name'])) {
    header("Location: port_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard of <?= htmlspecialchars($_SESSION['port_name']) ?></title>
    <link rel="icon" type="image/jpeg" href="dist/img/logo.jpg">
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f6f9; }
        .dashboard-header {
            background: linear-gradient(90deg, #007bff 0%, #00c6ff 100%);
            color: #fff;
            padding: 25px 30px 20px 30px;
            border-radius: 0 0 20px 20px;
            margin-bottom: 30px;
        }
        .dashboard-header h2 { margin:0; font-weight:700; letter-spacing:1px; font-size:2rem; }
        .sidebar-custom {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            padding: 20px 0;
            min-width: 210px;
            min-height: 350px;
        }
        .sidebar-custom .nav-link {
            color: #007bff;
            font-weight: 500;
            padding: 10px 25px;
        }
        .sidebar-custom .nav-link.active, .sidebar-custom .nav-link:hover {
            background: #e3f0ff;
            color: #0056b3;
            border-left: 4px solid #007bff;
        }
        .main-content {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            padding: 30px 30px 20px 30px;
            min-height: 350px;
        }
        .form-label { font-weight: 500; }
        .form-check-label { font-weight: 400; }
        .submit-btn { margin-top: 20px; }
        @media (max-width: 900px) {
            .dashboard-flex { flex-direction: column; }
            .sidebar-custom { min-width: 100%; margin-bottom: 20px; }
        }
    </style>
</head>
<body>
<div class="dashboard-header">
    <h2>ADMIN DASHBOARD OF <?= htmlspecialchars($_SESSION['port_name']) ?></h2>
</div>
<div class="container-fluid">
    <div class="row dashboard-flex" style="display:flex;">
        <!-- Sidebar -->
        <div class="col-md-3 sidebar-custom">
            <nav class="nav flex-column">
                <a class="nav-link active" href="#"><i class="fas fa-user-tie"></i> Designation Setup</a>
                <a class="nav-link" href="#"><i class="fas fa-calendar-check"></i> Attendance</a>
                <a class="nav-link" href="#"><i class="fas fa-list"></i> Designation Summary</a>
            </nav>
        </div>
        <!-- Main Content -->
        <div class="col-md-9 main-content">
            <form>
                <div class="mb-3">
                    <label for="username" class="form-label">User Setup</label>
                    <input type="text" class="form-control" id="username" placeholder="Enter Employee ID">
                </div>
                <div class="mb-3">
                    <label for="designation" class="form-label">Designation</label>
                    <select class="form-control" id="designation">
                        <option>Granty Crane Operator</option>
                        <option>Transfer Crane Operator</option>
                        <option>Primover Operator</option>
                        <option>Signal Man</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Role</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="role" id="active" value="active" checked>
                        <label class="form-check-label" for="active">Active</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="role" id="nonactive" value="nonactive">
                        <label class="form-check-label" for="nonactive">Non Active</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary submit-btn">Submit</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>