<?php
// Simple test dashboard without authentication
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Port Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8fafc; font-family: 'Inter', sans-serif; }
        .card { border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: none; }
        .form-control, .form-select { border-radius: 8px; border: 2px solid #e5e7eb; }
        .form-control:focus, .form-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        .btn-primary { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border: none; border-radius: 8px; }
        .header { background: linear-gradient(135deg, #1e40af 0%, #3730a3 100%); color: white; padding: 20px; border-radius: 15px 15px 0 0; }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="header text-center mb-4">
        <h2><i class="fas fa-ship"></i> TEST PORT DASHBOARD</h2>
        <p>Employee Auto-Loading Test Environment</p>
    </div>
    
    <div class="row g-4">
        <!-- Assign Employee -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-plus"></i> Assign Employee</h5>
                </div>
                <div class="card-body">
                    <form id="assignEmployeeForm">
                        <div class="mb-3">
                            <label class="form-label">Employee ID</label>
                            <input type="text" id="assign_employee_id" class="form-control" placeholder="Enter Employee ID" onchange="loadEmployeeDetailsForAssign(this.value)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" id="assign_employee_name" class="form-control" placeholder="Employee Name" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Division</label>
                            <input type="text" id="assign_division" class="form-control" placeholder="Division" readonly>
                        </div>
                        <div class="mb-3">
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
        </div>

        <!-- Role Management -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-users-cog"></i> Role Management</h5>
                </div>
                <div class="card-body">
                    <form id="roleManagementForm">
                        <div class="mb-3">
                            <label class="form-label">User ID</label>
                            <input type="text" id="role_user_id" class="form-control" placeholder="Enter User ID" onchange="loadUserDetailsForRole(this.value)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" id="role_user_name" class="form-control" placeholder="User Name" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Division</label>
                            <input type="text" id="role_division" class="form-control" placeholder="Division" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Section</label>
                            <input type="text" id="role_section" class="form-control" placeholder="Section" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Current Role</label>
                            <input type="text" id="role_current_role" class="form-control" value="Employee" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Role</label>
                            <select id="role_new_role" class="form-select">
                                <option value="">Select New Role</option>
                                <option value="Granty Crane Operator">Granty Crane Operator</option>
                                <option value="Transfer Crane Operator">Transfer Crane Operator</option>
                                <option value="Primover Operator">Primover Operator</option>
                                <option value="Signal Man">Signal Man</option>
                            </select>
                        </div>
                        <div class="mb-3">
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
                        <button type="submit" class="btn btn-success w-100">Update Role</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Testing Info</h5>
                </div>
                <div class="card-body">
                    <p><strong>How to test:</strong></p>
                    <ol>
                        <li>First run the <a href="test_autoload.php" target="_blank">test page</a> to check database connectivity</li>
                        <li>Get sample employee IDs from the "Check Sample Employees" button</li>
                        <li>Enter a valid employee ID in either form above</li>
                        <li>The name, division, and section should auto-load</li>
                    </ol>
                    <p><strong>API endpoints being used:</strong></p>
                    <ul>
                        <li>Employee Assignment: <code>test_get_employee.php</code></li>
                        <li>Role Management: <code>test_get_role.php</code></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Function to load employee details for assignment
function loadEmployeeDetailsForAssign(employeeId) {
    if (!employeeId.trim()) {
        clearAssignForm();
        return;
    }
    
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
        alert('Error loading employee details: ' + error);
        clearAssignForm();
    });
}

// Function to load user details for role management
function loadUserDetailsForRole(userId) {
    if (!userId.trim()) {
        clearRoleForm();
        return;
    }
    
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
        alert('Error loading user details: ' + error);
        clearRoleForm();
    });
}

// Clear forms
function clearAssignForm() {
    document.getElementById('assign_employee_name').value = '';
    document.getElementById('assign_division').value = '';
    document.getElementById('assign_section').value = '';
    document.getElementById('assign_role').value = '';
}

function clearRoleForm() {
    document.getElementById('role_user_name').value = '';
    document.getElementById('role_division').value = '';
    document.getElementById('role_section').value = '';
    document.getElementById('role_current_role').value = 'Employee';
    document.getElementById('role_new_role').value = '';
    document.getElementById('active').checked = true;
}

// Form submissions (simplified for testing)
document.getElementById('assignEmployeeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    alert('Assignment form submitted! (This is just a test - no data saved)');
});

document.getElementById('roleManagementForm').addEventListener('submit', function(e) {
    e.preventDefault();
    alert('Role update form submitted! (This is just a test - no data saved)');
});
</script>
</body>
</html>
