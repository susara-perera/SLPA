<?php
include_once 'dbc.php';

echo "<h1>👥 SLPA User Management System</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .card { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .success { color: #27ae60; padding: 10px; background: #d4edda; border-radius: 4px; }
    .error { color: #e74c3c; padding: 10px; background: #f8d7da; border-radius: 4px; }
    .form-group { margin: 15px 0; }
    label { display: block; margin-bottom: 5px; font-weight: bold; }
    input, select { width: 100%; max-width: 300px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
    button { background: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
    button:hover { background: #2980b9; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

// Handle form submission
if ($_POST) {
    if (isset($_POST['add_user'])) {
        $employee_id = strtoupper(trim($_POST['employee_id']));
        $role = $_POST['role'];
        $password = $_POST['password'];
        
        if (empty($employee_id) || empty($role) || empty($password)) {
            echo "<div class='error'>❌ All fields are required!</div>";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Check if user already exists
            $checkSql = "SELECT id FROM users WHERE employee_ID = ?";
            $checkStmt = mysqli_prepare($connect, $checkSql);
            mysqli_stmt_bind_param($checkStmt, "s", $employee_id);
            mysqli_stmt_execute($checkStmt);
            $checkResult = mysqli_stmt_get_result($checkStmt);
            
            if (mysqli_num_rows($checkResult) > 0) {
                echo "<div class='error'>❌ User with Employee ID '$employee_id' already exists!</div>";
            } else {
                // Insert new user
                $insertSql = "INSERT INTO users (employee_ID, role, pwd, created_at) VALUES (?, ?, ?, NOW())";
                $insertStmt = mysqli_prepare($connect, $insertSql);
                mysqli_stmt_bind_param($insertStmt, "sss", $employee_id, $role, $hashed_password);
                
                if (mysqli_stmt_execute($insertStmt)) {
                    echo "<div class='success'>✅ User '$employee_id' with role '$role' added successfully!</div>";
                } else {
                    echo "<div class='error'>❌ Error adding user: " . mysqli_error($connect) . "</div>";
                }
                mysqli_stmt_close($insertStmt);
            }
            mysqli_stmt_close($checkStmt);
        }
    }
    
    if (isset($_POST['create_sample_users'])) {
        $sampleUsers = [
            ['EMP001', 'Super Admin', 'admin123'],
            ['EMP002', 'Admin', 'admin456'],
            ['EMP003', 'Employee', 'emp123'],
            ['EMP004', 'Employee', 'emp456'],
            ['MGR001', 'Admin', 'mgr123']
        ];
        
        $success_count = 0;
        foreach ($sampleUsers as $user) {
            $employee_id = $user[0];
            $role = $user[1];
            $password = password_hash($user[2], PASSWORD_DEFAULT);
            
            // Check if user exists
            $checkSql = "SELECT id FROM users WHERE employee_ID = ?";
            $checkStmt = mysqli_prepare($connect, $checkSql);
            mysqli_stmt_bind_param($checkStmt, "s", $employee_id);
            mysqli_stmt_execute($checkStmt);
            $checkResult = mysqli_stmt_get_result($checkStmt);
            
            if (mysqli_num_rows($checkResult) == 0) {
                $insertSql = "INSERT INTO users (employee_ID, role, pwd, created_at) VALUES (?, ?, ?, NOW())";
                $insertStmt = mysqli_prepare($connect, $insertSql);
                mysqli_stmt_bind_param($insertStmt, "sss", $employee_id, $role, $password);
                
                if (mysqli_stmt_execute($insertStmt)) {
                    $success_count++;
                }
                mysqli_stmt_close($insertStmt);
            }
            mysqli_stmt_close($checkStmt);
        }
        
        echo "<div class='success'>✅ Created $success_count sample users!</div>";
        echo "<div class='card'>";
        echo "<h3>🔑 Sample User Credentials:</h3>";
        echo "<table>";
        echo "<tr><th>Employee ID</th><th>Role</th><th>Password</th></tr>";
        foreach ($sampleUsers as $user) {
            echo "<tr><td>{$user[0]}</td><td>{$user[1]}</td><td>{$user[2]}</td></tr>";
        }
        echo "</table>";
        echo "</div>";
    }
}

?>

<div class='card'>
    <h2>➕ Add New User</h2>
    <form method="POST">
        <div class='form-group'>
            <label for="employee_id">Employee ID:</label>
            <input type="text" id="employee_id" name="employee_id" placeholder="e.g., EMP001" required>
        </div>
        
        <div class='form-group'>
            <label for="role">Role:</label>
            <select id="role" name="role" required>
                <option value="">Select Role</option>
                <option value="Super Admin">Super Admin</option>
                <option value="Admin">Admin</option>
                <option value="Employee">Employee</option>
            </select>
        </div>
        
        <div class='form-group'>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter password" required>
        </div>
        
        <button type="submit" name="add_user">Add User</button>
    </form>
</div>

<div class='card'>
    <h2>🎯 Quick Setup</h2>
    <p>Create sample users for testing:</p>
    <form method="POST">
        <button type="submit" name="create_sample_users">Create Sample Users</button>
    </form>
</div>

<div class='card'>
    <h2>👥 Current Users</h2>
    <?php
    $usersSql = "SELECT id, employee_ID, role, created_at FROM users ORDER BY created_at DESC";
    $usersResult = mysqli_query($connect, $usersSql);
    
    if ($usersResult && mysqli_num_rows($usersResult) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Employee ID</th><th>Role</th><th>Created At</th></tr>";
        while ($user = mysqli_fetch_assoc($usersResult)) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . $user['employee_ID'] . "</td>";
            echo "<td>" . $user['role'] . "</td>";
            echo "<td>" . $user['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No users found. Add some users above!</p>";
    }
    ?>
</div>

<div class='card'>
    <h2>🔗 Quick Links</h2>
    <p><a href="test_database_connection.php">🔍 Test Database Connection</a></p>
    <p><a href="login.php">🔐 Login Page</a></p>
    <p><a href="all_ports.php">🚢 All Ports</a></p>
</div>
