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
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body { 
            background: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Segoe UI Emoji', 'Segoe UI Symbol', sans-serif; 
            min-height: 100vh;
            color: #334155;
            line-height: 1.6;
            overflow-x: hidden;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            25% { background-position: 100% 25%; }
            50% { background-position: 50% 100%; }
            75% { background-position: 25% 0%; }
            100% { background-position: 0% 50%; }
        }

        /* Subtle background pattern */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(37, 99, 235, 0.02) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(37, 99, 235, 0.01) 0%, transparent 50%);
            z-index: -1;
            pointer-events: none;
        }

        /* Professional Dashboard Header */
        .dashboard-header {
            background: #ffffff;
            color: #1e293b;
            padding: 30px;
            border-radius: 12px;
            margin: 20px;
            margin-bottom: 30px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s ease;
        }

        .dashboard-header:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .dashboard-header::before {
            display: none;
        }

        .dashboard-header h2 { 
            margin: 0; 
            font-weight: 600; 
            letter-spacing: -0.025em; 
            font-size: 1.875rem;
            color: #1e293b;
        }

        .dashboard-header .fas.fa-ship {
            color: #2563eb;
            margin-right: 12px;
            font-size: 1.75rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-name {
            color: #64748b;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
        }

        .user-name i {
            color: #2563eb;
            font-size: 1.1rem;
        }

        .logout-btn {
            background: #dc2626;
            color: #ffffff;
            border: 1px solid #dc2626;
            font-weight: 600;
            border-radius: 8px;
            padding: 10px 16px;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }



        .logout-btn:hover {
            background: #b91c1c;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);
            text-decoration: none;
            border-color: #b91c1c;
        }

        .logout-btn i {
            font-size: 1rem;
        }

        /* Container styling */
        .container-fluid {
            padding: 0 20px;
        }

        /* Professional Card Design */
        .card { 
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px; 
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #2563eb, #3b82f6);
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .card h5 {
            color: #1e293b;
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 1.125rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card h5::before {
            content: '';
            width: 3px;
            height: 20px;
            background: #2563eb;
            border-radius: 2px;
        }

        .card h5::after {
            display: none;
        }

        /* Professional Form Elements */
        .form-label { 
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 0.9rem;
            display: block;
        }

        .form-control, .form-select {
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            color: #1f2937;
            padding: 12px 16px;
            font-size: 14px;
            font-weight: 400;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .form-control::placeholder {
            color: #9ca3af;
            font-weight: 400;
        }

        .form-control:focus, .form-select:focus {
            background: #ffffff;
            border-color: #3b82f6;
            box-shadow: 
                0 0 0 3px rgba(59, 130, 246, 0.1),
                0 1px 2px rgba(0, 0, 0, 0.05);
            color: #1f2937;
            outline: none;
        }

        .form-control[readonly] {
            background: #f9fafb;
            border-color: #e5e7eb;
            color: #6b7280;
            cursor: not-allowed;
        }

        .form-select option {
            background: #ffffff;
            color: #1f2937;
            padding: 10px;
        }

        /* Professional Radio Buttons */
        .form-check {
            margin-bottom: 8px;
        }

        .form-check-label {
            color: #374151;
            font-weight: 500;
            padding-left: 8px;
        }

        .form-check-input {
            background-color: #ffffff;
            border: 1px solid #d1d5db;
        }

        .form-check-input:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Professional Buttons */
        .btn-primary {
            background: #2563eb;
            border: 1px solid #2563eb;
            border-radius: 8px;
            padding: 12px 20px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }



        .btn-primary:hover {
            background: #1d4ed8;
            border-color: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        }

        .btn-outline-primary {
            background: transparent;
            color: #2563eb;
            border: 1px solid #2563eb;
            border-radius: 8px;
            padding: 12px 18px;
            font-weight: 500;
            transition: all 0.2s ease;
            font-size: 14px;
        }

        .btn-outline-primary:hover {
            background: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.15);
        }

        /* Professional Table styling */
        .table {
            margin-bottom: 0;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .table th {
            background: #f8fafc;
            color: #374151;
            font-weight: 600;
            border: none;
            padding: 16px 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.8rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .table td {
            color: #1f2937;
            border: none;
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
            font-weight: 500;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background: #f8fafc;
        }

        /* Professional Status badges */
        .status-badge { 
            padding: 6px 12px; 
            border-radius: 16px; 
            font-size: 0.75rem;
            font-weight: 600;
            text-align: center;
            display: inline-block;
            min-width: 80px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }



        .status-pending { 
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #f59e0b;
        }

        .status-approved { 
            background: #dcfce7;
            color: #166534;
            border: 1px solid #22c55e;
        }

        /* Export button */
        .export-btn { 
            margin-left: 15px;
            min-width: 140px;
            flex-shrink: 0;
        }

        /* Professional Responsive design */
        @media (max-width: 991px) {
            .dashboard-header { 
                flex-direction: column; 
                align-items: flex-start; 
                gap: 16px;
                margin: 15px;
                padding: 20px;
            }

            .dashboard-header h2 {
                font-size: 1.6rem;
            }

            .user-info {
                width: 100%;
                justify-content: space-between;
            }

            .container-fluid {
                padding: 0 15px;
            }
        }

        @media (max-width: 768px) {
            .card {
                margin-bottom: 20px;
                border-radius: 12px;
            }

            .form-control, .form-select {
                padding: 10px 12px;
                border-radius: 6px;
            }

            .btn-primary {
                padding: 10px 20px;
                border-radius: 6px;
            }

            .dashboard-header h2 {
                font-size: 1.4rem;
            }

            .table {
                font-size: 0.85rem;
                border-radius: 8px;
            }

            .table th,
            .table td {
                padding: 8px 6px;
            }

            .export-btn {
                margin-left: 0;
                margin-top: 12px;
                width: 100%;
            }

            .col-12.d-flex {
                flex-direction: column;
            }
        }

        /* Staggered entrance animation */
        .card:nth-child(1) { animation-delay: 0.05s; }
        .card:nth-child(2) { animation-delay: 0.1s; }
        .card:nth-child(3) { animation-delay: 0.15s; }
        .card:nth-child(4) { animation-delay: 0.2s; }

        /* Loading state for buttons */
        .btn.loading {
            position: relative;
            color: transparent;
        }

        .btn.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* Enhanced icons */
        .fas {
            transition: all 0.2s ease;
        }
    </style>
</head>
<body>
<div class="dashboard-header">
    <h2>
        <i class="fas fa-ship"></i> ADMIN DASHBOARD OF <?= htmlspecialchars($_SESSION['port_name']) ?>
    </h2>
    <div class="user-info">
        <span class="user-name"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['port_user']) ?></span>
        <a href="port_logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>
<div class="container-fluid">
    <div class="row g-4">
        <!-- Assign Employee -->
        <div class="col-md-6">
            <div class="card p-4">
                <h5 class="mb-3">Assign Employee</h5>
                <form id="assignEmployeeForm">
                    <div class="mb-2">
                        <label class="form-label">Employee ID</label>
                        <input type="text" id="assign_employee_id" class="form-control" placeholder="Enter Employee ID" onchange="loadEmployeeDetailsForAssign(this.value)">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Name</label>
                        <input type="text" id="assign_employee_name" class="form-control" placeholder="Enter Employee Name" readonly>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Division</label>
                        <input type="text" id="assign_division" class="form-control" placeholder="Division" readonly>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Section</label>
                        <input type="text" id="assign_section" class="form-control" placeholder="Section" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select id="assign_role" class="form-select" required>
                            <option value="">Select Role</option>
                            <option value="Granty Crane Operator">Granty Crane Operator</option>
                            <option value="Transfer Crane Operator">Transfer Crane Operator</option>
                            <option value="Primover Operator">Primover Operator</option>
                            <option value="Signal Man">Signal Man</option>
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
                <form id="roleManagementForm">
                    <div class="mb-2">
                        <label class="form-label">User ID</label>
                        <input type="text" id="role_user_id" class="form-control" placeholder="Enter User ID" onchange="loadUserDetailsForRole(this.value)">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Name</label>
                        <input type="text" id="role_user_name" class="form-control" placeholder="Enter User Name" readonly>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Division</label>
                        <input type="text" id="role_division" class="form-control" placeholder="Division" readonly>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Section</label>
                        <input type="text" id="role_section" class="form-control" placeholder="Section" readonly>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Current Role</label>
                        <input type="text" id="role_current_role" class="form-control" value="Employee" readonly>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">New Role</label>
                        <select id="role_new_role" class="form-select">
                            <option value="">Select New Role</option>
                            <option value="Granty Crane Operator">Granty Crane Operator</option>
                            <option value="Transfer Crane Operator">Transfer Crane Operator</option>
                            <option value="Primover Operator">Primover Operator</option>
                            <option value="Signal Man">Signal Man</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Status</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="active" value="Active" checked>
                            <label class="form-check-label" for="active">Active</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="inactive" value="Inactive">
                            <label class="form-check-label" for="inactive">Inactive</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assigned Port</label>
                        <input type="text" id="role_assigned_port" class="form-control" value="<?= htmlspecialchars($_SESSION['port_name']) ?>" readonly>
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

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Function to load employee details for assignment
function loadEmployeeDetailsForAssign(employeeId) {
    if (!employeeId.trim()) {
        clearAssignForm();
        return;
    }
    
    // Show loading state
    const nameField = document.getElementById('assign_employee_name');
    nameField.value = 'Loading...';
    
    fetch('test_get_employee.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            employee_id: employeeId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('assign_employee_name').value = data.data.name || '';
            document.getElementById('assign_division').value = data.data.division || '';
            document.getElementById('assign_section').value = data.data.section || '';
        } else {
            alert('Employee not found: ' + data.message);
            clearAssignForm();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading employee details');
        clearAssignForm();
    });
}

// Function to load user details for role management
function loadUserDetailsForRole(userId) {
    if (!userId.trim()) {
        clearRoleForm();
        return;
    }
    
    // Show loading state
    const nameField = document.getElementById('role_user_name');
    nameField.value = 'Loading...';
    
    fetch('test_get_role.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            user_id: userId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('role_user_name').value = data.data.name || '';
            document.getElementById('role_division').value = data.data.division || '';
            document.getElementById('role_section').value = data.data.section || '';
            document.getElementById('role_current_role').value = data.data.current_role || 'Employee';
            
            // Set status radio button
            if (data.data.status === 'Inactive') {
                document.getElementById('inactive').checked = true;
            } else {
                document.getElementById('active').checked = true;
            }
        } else {
            alert('User not found: ' + data.message);
            clearRoleForm();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading user details');
        clearRoleForm();
    });
}

// Clear assign form
function clearAssignForm() {
    document.getElementById('assign_employee_name').value = '';
    document.getElementById('assign_division').value = '';
    document.getElementById('assign_section').value = '';
    document.getElementById('assign_role').value = '';
}

// Clear role form
function clearRoleForm() {
    document.getElementById('role_user_name').value = '';
    document.getElementById('role_division').value = '';
    document.getElementById('role_section').value = '';
    document.getElementById('role_current_role').value = 'Employee';
    document.getElementById('role_new_role').value = '';
    document.getElementById('active').checked = true;
}

// Handle assign employee form submission
document.getElementById('assignEmployeeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        employee_id: document.getElementById('assign_employee_id').value,
        employee_name: document.getElementById('assign_employee_name').value,
        division: document.getElementById('assign_division').value,
        section: document.getElementById('assign_section').value,
        role: document.getElementById('assign_role').value
    };
    
    if (!formData.employee_id || !formData.employee_name || !formData.role) {
        alert('Please fill all required fields');
        return;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Assigning...';
    submitBtn.disabled = true;
    
    fetch('save_employee_assignment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Employee assigned successfully!');
            document.getElementById('assignEmployeeForm').reset();
            clearAssignForm();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving assignment');
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
});

// Handle role management form submission
document.getElementById('roleManagementForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        user_id: document.getElementById('role_user_id').value,
        user_name: document.getElementById('role_user_name').value,
        division: document.getElementById('role_division').value,
        section: document.getElementById('role_section').value,
        current_role: document.getElementById('role_current_role').value,
        new_role: document.getElementById('role_new_role').value,
        status: document.querySelector('input[name="status"]:checked').value,
        assigned_port: document.getElementById('role_assigned_port').value
    };
    
    if (!formData.user_id || !formData.user_name || !formData.new_role) {
        alert('Please fill all required fields');
        return;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Updating...';
    submitBtn.disabled = true;
    
    fetch('save_role_update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Role updated successfully!');
            document.getElementById('roleManagementForm').reset();
            clearRoleForm();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating role');
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
});
</script>
</body>
</html>