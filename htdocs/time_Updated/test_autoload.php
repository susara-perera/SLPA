<!DOCTYPE html>
<html>
<head>
    <title>Test Employee Auto-Load Functionality</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { border: 1px solid #ccc; padding: 15px; margin: 10px 0; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
    </style>
</head>
<body>
    <h1>Employee Auto-Load Test Page</h1>
    
    <div class="test-section">
        <h3>Test 1: Database Connection</h3>
        <button onclick="testConnection()">Test Database Connection</button>
        <div id="connectionResult"></div>
    </div>
    
    <div class="test-section">
        <h3>Test 2: Sample Employees</h3>
        <button onclick="checkEmployees()">Check Sample Employees</button>
        <div id="employeesResult"></div>
    </div>
    
    <div class="test-section">
        <h3>Test 3: Database Setup Check</h3>
        <p><a href="setup_assign_role_table.php" target="_blank">1. Check/Create assign_role table</a></p>
        <p><a href="test_db_connection.php" target="_blank">2. Test database connection and tables</a></p>
    </div>
    
    <div class="test-section">
        <h3>Test 4: Employee Details API</h3>
        <p>Test Employee ID: <input type="text" id="testEmployeeId" value="22152" placeholder="Enter Employee ID"></p>
        <button onclick="testEmployeeAPI()">Test Get Employee Details (Simple)</button>
        <button onclick="testEmployeeAPIFlexible()">Test Get Employee Details (Flexible)</button>
        <div id="employeeResult"></div>
    </div>
    
    <div class="test-section">
        <h3>Test 5: Role Details API</h3>
        <p>Test User ID: <input type="text" id="testUserId" value="22152" placeholder="Enter User ID"></p>
        <button onclick="testRoleAPI()">Test Get Role Details</button>
        <div id="roleResult"></div>
    </div>
    
    <div class="test-section">
        <h3>Test 6: Port Dashboard</h3>
        <p><a href="test_dashboard.php" target="_blank">Go to Test Dashboard (No Login Required)</a></p>
        <p><a href="create_test_session.php" target="_blank">Create Test Session for Real Dashboard</a></p>
        <p><a href="port_dashboard.php" target="_blank">Go to Real Port Dashboard (Requires Login)</a></p>
        <p class="info">Try entering employee ID in the forms to test auto-loading functionality</p>
    </div>

    <script>
        function testConnection() {
            const resultDiv = document.getElementById('connectionResult');
            resultDiv.innerHTML = '<p class="info">Testing database connection...</p>';
            
            fetch('test_connection.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML = `
                        <p class="success">✓ ${data.message}</p>
                        <p>Total employees in database: ${data.employees_count}</p>
                    `;
                } else {
                    resultDiv.innerHTML = `<p class="error">✗ ${data.message}</p>`;
                }
            })
            .catch(error => {
                resultDiv.innerHTML = `<p class="error">✗ Network Error: ${error}</p>`;
            });
        }
        
        function checkEmployees() {
            const resultDiv = document.getElementById('employeesResult');
            resultDiv.innerHTML = '<p class="info">Checking employees...</p>';
            
            fetch('check_employees.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let html = `<p class="success">✓ ${data.message}</p><table border="1" style="border-collapse: collapse; width: 100%;">
                               <tr><th>Employee ID</th><th>Name</th><th>Division</th><th>Section</th></tr>`;
                    data.employees.forEach(emp => {
                        html += `<tr><td>${emp.employee_ID}</td><td>${emp.employee_name || 'N/A'}</td><td>${emp.division || 'N/A'}</td><td>${emp.section || 'N/A'}</td></tr>`;
                    });
                    html += '</table>';
                    resultDiv.innerHTML = html;
                } else {
                    resultDiv.innerHTML = `<p class="error">✗ ${data.message}</p>`;
                }
            })
            .catch(error => {
                resultDiv.innerHTML = `<p class="error">✗ Network Error: ${error}</p>`;
            });
        }
        
        function testEmployeeAPI() {
            const employeeId = document.getElementById('testEmployeeId').value;
            const resultDiv = document.getElementById('employeeResult');
            
            if (!employeeId) {
                resultDiv.innerHTML = '<p class="error">Please enter an Employee ID</p>';
                return;
            }
            
            resultDiv.innerHTML = '<p class="info">Testing...</p>';
            
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
                    resultDiv.innerHTML = `
                        <p class="success">✓ Success!</p>
                        <ul>
                            <li>Employee ID: ${data.data.employee_id}</li>
                            <li>Name: ${data.data.name}</li>
                            <li>Division: ${data.data.division}</li>
                            <li>Section: ${data.data.section}</li>
                        </ul>
                    `;
                } else {
                    resultDiv.innerHTML = `<p class="error">✗ Error: ${data.message}</p>`;
                }
            })
            .catch(error => {
                resultDiv.innerHTML = `<p class="error">✗ Network Error: ${error}</p>`;
            });
        }
        
        function testEmployeeAPIFlexible() {
            const employeeId = document.getElementById('testEmployeeId').value;
            const resultDiv = document.getElementById('employeeResult');
            
            if (!employeeId) {
                resultDiv.innerHTML = '<p class="error">Please enter an Employee ID</p>';
                return;
            }
            
            resultDiv.innerHTML = '<p class="info">Testing flexible API...</p>';
            
            fetch('get_employee_details_flexible.php', {
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
                    resultDiv.innerHTML = `
                        <p class="success">✓ Flexible API Success!</p>
                        <ul>
                            <li>Employee ID: ${data.data.employee_id}</li>
                            <li>Name: ${data.data.name}</li>
                            <li>Division: ${data.data.division}</li>
                            <li>Section: ${data.data.section}</li>
                        </ul>
                    `;
                } else {
                    resultDiv.innerHTML = `<p class="error">✗ Error: ${data.message}</p>`;
                }
            })
            .catch(error => {
                resultDiv.innerHTML = `<p class="error">✗ Network Error: ${error}</p>`;
            });
        }
        
        function testRoleAPI() {
            const userId = document.getElementById('testUserId').value;
            const resultDiv = document.getElementById('roleResult');
            
            if (!userId) {
                resultDiv.innerHTML = '<p class="error">Please enter a User ID</p>';
                return;
            }
            
            resultDiv.innerHTML = '<p class="info">Testing...</p>';
            
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
                    resultDiv.innerHTML = `
                        <p class="success">✓ Success!</p>
                        <ul>
                            <li>User ID: ${data.data.user_id}</li>
                            <li>Name: ${data.data.name}</li>
                            <li>Division: ${data.data.division}</li>
                            <li>Section: ${data.data.section}</li>
                            <li>Current Role: ${data.data.current_role}</li>
                            <li>Status: ${data.data.status}</li>
                        </ul>
                    `;
                } else {
                    resultDiv.innerHTML = `<p class="error">✗ Error: ${data.message}</p>`;
                }
            })
            .catch(error => {
                resultDiv.innerHTML = `<p class="error">✗ Network Error: ${error}</p>`;
            });
        }
    </script>
</body>
</html>
