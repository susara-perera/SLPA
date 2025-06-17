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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f4f6f9; font-family: 'Poppins', sans-serif; }
        .dashboard-header {
            background: linear-gradient(90deg, #2563eb 0%, #00c6ff 100%);
            color: #fff;
            padding: 30px 30px 20px 30px;
            border-radius: 0 0 20px 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .dashboard-header h2 { margin:0; font-weight:700; letter-spacing:1px; font-size:2rem; }
        .logout-btn {
            background: #fff;
            color: #2563eb;
            border: none;
            font-weight: 600;
            border-radius: 8px;
            padding: 8px 22px;
            transition: background 0.2s, color 0.2s;
        }
        .logout-btn:hover {
            background: #2563eb;
            color: #fff;
            border: 1px solid #fff;
        }
        .card { border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
        .form-label { font-weight: 500; }
        .status-badge { padding: 0.35em 0.7em; border-radius: 8px; font-size: 0.95em; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .btn-primary, .btn-primary:active, .btn-primary:focus { background: #2563eb !important; border: none; }
        .btn-primary:hover { background: #1d4ed8 !important; }
        .form-select, .form-control { border-radius: 8px; }
        .table { margin-bottom: 0; }
        .export-btn { margin-left: 10px; }
        @media (max-width: 991px) {
            .dashboard-header { flex-direction: column; align-items: flex-start; gap: 10px; }
        }
    </style>
</head>
<body>
<div class="dashboard-header">
    <h2>
        <i class="fas fa-ship"></i> ADMIN DASHBOARD OF <?= htmlspecialchars($_SESSION['port_name']) ?>
    </h2>
    <div>
        <span class="me-3"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['port_user']) ?></span>
        <a href="port_logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>
<div class="container-fluid">
    <div class="row g-4">
        <!-- Assign Employee -->
        <div class="col-md-6">
            <div class="card p-4">
                <h5 class="mb-3">Assign Employee</h5>
                <form>
                    <div class="mb-2">
                        <label class="form-label">Employee ID</label>
                        <input type="text" class="form-control" placeholder="Enter Employee ID">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" placeholder="Enter Employee Name">
                    </div>
                  
                    <div class="mb-2">
                        <label class="form-label">Division</label>
                        <select class="form-select">
                            <option selected>Select Division</option>
                            <option>ECT</option>
                            <option>JCT</option>
                            <option>ITT</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select">
                            <option selected>Select Role</option>
                            <option>Granty Crane Operator</option>
                            <option>Transfer Crane Operator</option>
                            <option>Primover Operator</option>
                            <option>Signal Man</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Submit Assignment</button>
                </form>
            </div>
        </div>
        <!-- Role Management -->
        <div class="col-md-6">
            <div class="card p-4">
                <h5 class="mb-3">Role Management</h5>
                <form>
                    <div class="mb-2">
                        <label class="form-label">User ID</label>
                        <input type="text" class="form-control" placeholder="Enter User ID">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" placeholder="Enter User Name">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Current Role</label>
                        <input type="text" class="form-control" value="Employee" readonly>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">New Role</label>
                        <select class="form-select">
                            <option selected>Select New Role</option>
                            <option>Granty Crane Operator</option>
                            <option>Transfer Crane Operator</option>
                            <option>Primover Operator</option>
                            <option>Signal Man</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Status</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="active" value="Active" checked>
                            <label class="form-check-label" for="active">Active</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="nonactive" value="Non Active">
                            <label class="form-check-label" for="nonactive">Non Active</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assigned Port</label>
                        <select class="form-select">
                            <option selected>Select Port</option>
                            <option>Colombo</option>
                            <option>Galle</option>
                            <option>Trincomalee</option>
                            <option>Hambantota</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Update Role</button>
                </form>
            </div>
        </div>
        <!-- Transfer Requests -->
        <div class="col-md-6">
            <div class="card p-4">
                <h5 class="mb-3">Transfer Requests</h5>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Current Port</th>
                            <th>Requested Port</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>EMP001</td>
                            <td>John Smith</td>
                            <td>Colombo</td>
                            <td>Galle</td>
                            <td><span class="status-badge status-pending">Pending</span></td>
                        </tr>
                        <tr>
                            <td>EMP002</td>
                            <td>Sarah Johnson</td>
                            <td>Trincomalee</td>
                            <td>Hambantota</td>
                            <td><span class="status-badge status-approved">Approved</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Generate Reports -->
        <div class="col-md-6">
            <div class="card p-4">
                <h5 class="mb-3">Generate Reports</h5>
                <form class="row g-2 align-items-end">
                    <div class="col-6">
                        <label class="form-label">Select Port</label>
                        <select class="form-select">
                            <option>All Ports</option>
                            <option>Colombo</option>
                            <option>Galle</option>
                            <option>Trincomalee</option>
                            <option>Hambantota</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Select Division</label>
                        <select class="form-select">
                            <option>All Divisions</option>
                            <option>Operations</option>
                            <option>Logistics</option>
                            <option>Maintenance</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Role</label>
                        <select class="form-select">
                            <option>All Roles</option>
                            <option>Granty Crane Operator</option>
                            <option>Transfer Crane Operator</option>
                            <option>Primover Operator</option>
                            <option>Signal Man</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Status</label>
                        <select class="form-select">
                            <option>All Status</option>
                            <option>Pending</option>
                            <option>Approved</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control">
                    </div>
                    <div class="col-12 d-flex">
                        <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                        <button type="button" class="btn btn-outline-primary export-btn"><i class="fa fa-download"></i> Export</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>