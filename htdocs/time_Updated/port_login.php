<?php
session_start();

// Include database connection
include_once 'dbc.php';

// Clear previous port session data before new login
unset($_SESSION['port_user']);
unset($_SESSION['port_name']);

// Function to create ports table if it doesn't exist (needed for foreign key)
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

// Function to create port_users table if it doesn't exist
function createPortUsersTable($connect) {
    // First, ensure ports table exists
    if (!createPortsTable($connect)) {
        return false;
    }
    insertDefaultPorts($connect);
    
    // Try creating table with foreign key first
    $sql = "CREATE TABLE IF NOT EXISTS port_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        port_id INT NOT NULL,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        status ENUM('Active', 'Inactive') DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (port_id) REFERENCES ports(id) ON DELETE CASCADE,
        UNIQUE KEY unique_port_user (port_id, username)
    )";
    
    if (mysqli_query($connect, $sql)) {
        error_log("Port users table created successfully with foreign key");
        return true;
    } else {
        error_log("Error creating port users table with foreign key: " . mysqli_error($connect));
        
        // Fallback: Create table without foreign key constraint
        $sql_fallback = "CREATE TABLE IF NOT EXISTS port_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            port_id INT NOT NULL,
            username VARCHAR(50) NOT NULL,
            password VARCHAR(255) NOT NULL,
            status ENUM('Active', 'Inactive') DEFAULT 'Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_port_user (port_id, username),
            INDEX idx_port_id (port_id)
        )";
        
        if (mysqli_query($connect, $sql_fallback)) {
            error_log("Port users table created successfully without foreign key (fallback)");
            return true;
        } else {
            error_log("Error creating port users table (fallback): " . mysqli_error($connect));
            return false;
        }
    }
}

// Function to insert default port users if table is empty
function insertDefaultPortUsers($connect) {
    // Check if port_users table has data
    $checkSql = "SELECT COUNT(*) as count FROM port_users";
    $result = mysqli_query($connect, $checkSql);
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        
        if ($row['count'] == 0) {
            // Get all ports first
            $portsSql = "SELECT id, port_name FROM ports WHERE status = 'Active'";
            $portsResult = mysqli_query($connect, $portsSql);
            
            if ($portsResult && mysqli_num_rows($portsResult) > 0) {
                $insertSql = "INSERT INTO port_users (port_id, username, password) VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($connect, $insertSql);
                
                while ($port = mysqli_fetch_assoc($portsResult)) {
                    // Create admin username based on port name
                    $username = str_replace([' ', 'Port'], ['', ''], $port['port_name']) . 'Admin';
                    $password = password_hash($username . '123!', PASSWORD_DEFAULT);
                    
                    mysqli_stmt_bind_param($stmt, "iss", $port['id'], $username, $password);
                    mysqli_stmt_execute($stmt);
                }
                mysqli_stmt_close($stmt);
                error_log("Default port users created successfully");
            }
        }
    }
}

// Function to create port_login_logs table if it doesn't exist
function createLoginLogsTable($connect) {
    $sql = "CREATE TABLE IF NOT EXISTS port_login_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        port_id INT NOT NULL,
        username VARCHAR(50) NOT NULL,
        login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('Success', 'Failed') DEFAULT 'Success',
        ip_address VARCHAR(45),
        user_agent TEXT,
        INDEX idx_port_id (port_id),
        INDEX idx_login_time (login_time)
    )";
    
    if (mysqli_query($connect, $sql)) {
        error_log("Port login logs table created successfully or already exists");
        return true;
    } else {
        error_log("Error creating port login logs table: " . mysqli_error($connect));
        return false;
    }
}

// Initialize database tables
$db_error = '';
if ($connect) {
    $table_created = createPortUsersTable($connect);
    $logs_table_created = createLoginLogsTable($connect);
    
    if ($table_created) {
        insertDefaultPortUsers($connect);
    } else {
        $db_error = "Failed to initialize database tables.";
        error_log("Database initialization failed in port_login.php");
    }
} else {
    $db_error = "Database connection failed.";
}

$error = '';
$selected_port = isset($_GET['port']) ? $_GET['port'] : (isset($_POST['port_name']) ? $_POST['port_name'] : '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $port_name = $_POST['port_name'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate credentials against database
    $sql = "SELECT pu.*, p.port_name 
            FROM port_users pu 
            JOIN ports p ON pu.port_id = p.id 
            WHERE p.port_name = ? AND pu.username = ? AND pu.status = 'Active'";
    
    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $port_name, $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['port_user'] = $username;
            $_SESSION['port_name'] = $port_name;
            $_SESSION['port_id'] = $user['port_id'];
            
            // Try to log successful login (optional - won't break login if it fails)
            try {
                $logSql = "INSERT INTO port_login_logs (port_id, username, login_time, status, ip_address) VALUES (?, ?, NOW(), 'Success', ?)";
                $logStmt = mysqli_prepare($connect, $logSql);
                if ($logStmt) {
                    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
                    mysqli_stmt_bind_param($logStmt, "iss", $user['port_id'], $username, $ip_address);
                    mysqli_stmt_execute($logStmt);
                    mysqli_stmt_close($logStmt);
                }
            } catch (Exception $e) {
                // Log the error but don't break the login process
                error_log("Failed to log login activity: " . $e->getMessage());
            }
            
            header("Location: port_dashboard.php");
            exit();
        } else {
            $error = "Invalid credentials";
        }
    } else {
        $error = "Invalid credentials";
    }
    mysqli_stmt_close($stmt);
}

if (!$selected_port) {
    header("Location: all_ports.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Port Login - <?= htmlspecialchars($selected_port) ?></title>
    <link rel="icon" type="image/jpeg" href="dist/img/logo.jpg">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #2C5282 25%, #1A202C 50%, #2D3748 75%, #4A5568 100%);
            background-size: 400% 400%;
            animation: gradientShift 8s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Animated background elements */
        .bg-shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .bg-shape:nth-child(1) {
            width: 100px;
            height: 100px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .bg-shape:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 70%;
            right: 10%;
            animation-delay: 2s;
        }

        .bg-shape:nth-child(3) {
            width: 80px;
            height: 80px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        .bg-shape:nth-child(4) {
            width: 120px;
            height: 120px;
            top: 30%;
            right: 25%;
            animation-delay: 1s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .login-container {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 50px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            position: relative;
            animation: slideUp 0.8s ease-out;
            z-index: 10;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .port-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(72, 187, 120, 0.3);
            animation: pulse 2s ease-in-out infinite;
            padding: 8px;
        }

        .port-logo {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 50%;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .login-title {
            color: white;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .port-name {
            color: #F6E05E;
            font-size: 18px;
            font-weight: 500;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 15px 50px 15px 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .form-input:focus {
            outline: none;
            border-color: #48BB78;
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 20px rgba(72, 187, 120, 0.3);
            transform: translateY(-2px);
        }

        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.6);
            font-size: 18px;
            transition: color 0.3s ease;
        }

        .form-input:focus + .input-icon {
            color: #48BB78;
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #48BB78, #38A169);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: 10px;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(72, 187, 120, 0.4);
            background: linear-gradient(135deg, #38A169, #48BB78);
        }

        .login-btn:active {
            transform: translateY(-1px);
        }

        .alert {
            background: rgba(229, 62, 62, 0.2);
            border: 1px solid rgba(229, 62, 62, 0.4);
            color: #E53E3E;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            backdrop-filter: blur(10px);
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .back-link {
            position: absolute;
            top: 30px;
            left: 30px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            z-index: 20;
        }

        .back-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-5px);
            text-decoration: none;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .login-container {
                margin: 20px;
                padding: 40px 30px;
                max-width: calc(100% - 40px);
            }

            .back-link {
                top: 20px;
                left: 20px;
            }

            .login-title {
                font-size: 24px;
            }

            .port-name {
                font-size: 16px;
            }
        }

        /* Loading animation */
        .loading {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .login-btn.loading .loading {
            display: inline-block;
        }
    </style>
</head>
<body>
    <!-- Animated background shapes -->
    <div class="bg-shape"></div>
    <div class="bg-shape"></div>
    <div class="bg-shape"></div>
    <div class="bg-shape"></div>

    <!-- Back to All Ports Link -->
    <a href="all_ports.php" class="back-link">
        <i class="fas fa-arrow-left"></i>
        <span>Back to All Ports</span>
    </a>

    <div class="login-container">
        <div class="login-header">
            <div class="port-icon">
                <!-- Try multiple possible logo paths -->
                <img src="dist/img/logo.png" alt="Port Authority Logo" class="port-logo" 
                     onerror="this.onerror=null; this.src='dist/img/logo.jpg'; if(this.complete && this.naturalHeight === 0) { this.style.display='none'; this.nextElementSibling.style.display='flex'; }">
                <div class="fallback-icon" style="display: none; width: 100%; height: 100%; background: linear-gradient(135deg, #48BB78, #38A169); border-radius: 50%; align-items: center; justify-content: center;">
                    <i class="fas fa-anchor" style="font-size: 32px; color: white;"></i>
                </div>
            </div>
            <h1 class="login-title">Port Login</h1>
            <p class="port-name"><?= htmlspecialchars($selected_port) ?></p>
        </div>

        <?php if ($error): ?>
            <div class="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($db_error): ?>
            <div class="alert" style="background: rgba(229, 62, 62, 0.25); border-color: rgba(229, 62, 62, 0.5);">
                <i class="fas fa-database"></i>
                Database Error: <?= htmlspecialchars($db_error) ?>
                <br><small>Please contact system administrator if this persists.</small>
            </div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <input type="hidden" name="port_name" value="<?= htmlspecialchars($selected_port) ?>">
            
            <div class="form-group">
                <label class="form-label">Username</label>
                <div class="input-wrapper">
                    <input 
                        name="username" 
                        class="form-input" 
                        type="text"
                        placeholder="Enter your username"
                        required 
                        autocomplete="off"
                        value=""
                    >
                    <i class="fas fa-user input-icon"></i>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-wrapper">
                    <input 
                        name="password" 
                        class="form-input" 
                        type="password"
                        placeholder="Enter your password"
                        required 
                        autocomplete="off"
                        value=""
                    >
                    <i class="fas fa-lock input-icon"></i>
                </div>
            </div>

            <button type="submit" class="login-btn" id="loginBtn">
                <div class="loading"></div>
                <i class="fas fa-sign-in-alt"></i>
                Login to Port
            </button>
        </form>
    </div>

    <script>
        // Logo loading fallback
        document.addEventListener('DOMContentLoaded', function() {
            const logo = document.querySelector('.port-logo');
            const fallback = document.querySelector('.fallback-icon');
            
            // Try different logo paths
            const logoPaths = [
                'dist/img/logo.png',
                'dist/img/logo.jpg',
                'images/logo.png',
                'images/logo.jpg',
                '../images/logo.png',
                './dist/img/logo.png'
            ];
            
            let currentIndex = 0;
            
            function tryNextLogo() {
                if (currentIndex < logoPaths.length) {
                    logo.src = logoPaths[currentIndex];
                    currentIndex++;
                } else {
                    // All paths failed, show fallback
                    logo.style.display = 'none';
                    fallback.style.display = 'flex';
                }
            }
            
            logo.onerror = function() {
                tryNextLogo();
            };
            
            // Initial load check
            if (logo.complete && logo.naturalHeight === 0) {
                tryNextLogo();
            }
        });

        // Add loading animation on form submit
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('loading');
            btn.innerHTML = '<div class="loading"></div>Logging in...';
        });

        // Add focus effects
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentNode.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentNode.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>