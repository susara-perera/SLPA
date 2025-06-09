<?php

session_start();

include_once 'dbc.php';  // database connection 

// Function to create tables
function createTables($connect)
{
    // Create the 'users' table
    $sqlUsers = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        division VARCHAR(255) NOT NULL,
        role VARCHAR(50) NOT NULL,
        employee_ID VARCHAR(50) NOT NULL UNIQUE,
        pwd VARCHAR(255) NOT NULL
    )";

    // Create the 'employees' table
    $sqlEmployees = "CREATE TABLE IF NOT EXISTS employees (
        employee_ID INT PRIMARY KEY,
        employee_name VARCHAR(255) NOT NULL,
        division VARCHAR(255) NOT NULL,
        section VARCHAR(255) NOT NULL,
        designation VARCHAR(255) NOT NULL,
        appointment_date DATE NOT NULL,
        gender ENUM('Male', 'Female') NOT NULL,
        status ENUM('Active', 'Inactive') NOT NULL,
        nic_number VARCHAR(50) NOT NULL,
        telephone_number VARCHAR(50) NOT NULL,
        address TEXT NOT NULL,
        card_valid_date DATE NOT NULL,
        card_issued_date DATE NOT NULL,
        picture VARCHAR(255) NOT NULL
    )";

    // Create the 'login' table
    $sqlLogin = "CREATE TABLE IF NOT EXISTS login (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        logout_time TIMESTAMP NULL DEFAULT NULL,
        status ENUM('Active', 'Logged Out') DEFAULT 'Active',
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";

    // Create the 'divisions' table
    $sqlDivisions = "CREATE TABLE IF NOT EXISTS divisions (
        division_id VARCHAR(50) PRIMARY KEY,
        division_name VARCHAR(100) NOT NULL UNIQUE
    )";

    // Create the 'sections' table
    $sqlSections = "CREATE TABLE IF NOT EXISTS sections (
        section_id INT AUTO_INCREMENT PRIMARY KEY,
        section_name VARCHAR(255) NOT NULL,
        division_id VARCHAR(50) NOT NULL,
        FOREIGN KEY (division_id) REFERENCES divisions(division_id)
    )";

    // Create the 'fingerprints' table
    $sqlFingerprints = "CREATE TABLE IF NOT EXISTS fingerprints (
        fingerprint_id VARCHAR(50) PRIMARY KEY,
        location VARCHAR(255) NOT NULL
    )";

   
    // Create the 'attendance' table
    $sqlAttendance = "CREATE TABLE IF NOT EXISTS attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_ID INT,
    fingerprint_id VARCHAR(50),
    date_ DATE NOT NULL,
    time_ TIME,
    scan_type ENUM('IN', 'OUT'),
    FOREIGN KEY (employee_ID) REFERENCES employees(employee_ID),
    FOREIGN KEY (fingerprint_id) REFERENCES fingerprints(fingerprint_id)
)";


    // Create the 'role_access' table
    $sqlRoleAccess = "CREATE TABLE IF NOT EXISTS role_access (
        id INT AUTO_INCREMENT PRIMARY KEY,
        role VARCHAR(255) NOT NULL,
        page VARCHAR(255) NOT NULL
    )";

    // Execute the table creation queries
    $tables = [
        'users' => $sqlUsers,
        'employees' => $sqlEmployees,
        'login' => $sqlLogin,
        'divisions' => $sqlDivisions,
        'sections' => $sqlSections,
        'fingerprints' => $sqlFingerprints,
        'attendance' => $sqlAttendance,
        'role_access' => $sqlRoleAccess
    ];

    foreach ($tables as $name => $sql) {
        if (mysqli_query($connect, $sql)) {
            echo "Table '$name' created successfully.\n";
        } else {
            echo "Error creating '$name' table: " . mysqli_error($connect) . "\n";
        }
    }
}

// Function to insert the initial super admin
function insertInitialSuperAdmin($connect)
{
    $role = "Super_Ad";  // User role 
    $division = "IS";  // division for super admin
    $employeeID = "SA1001";
    $password = "SuperAdmin123!";
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if super admin already exists based on role
    $checkAdmin = "SELECT COUNT(*) FROM users WHERE role = ?";
    $stmt = mysqli_prepare($connect, $checkAdmin);
    mysqli_stmt_bind_param($stmt, "s", $role);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($count == 0) {
        $insertSql = "INSERT INTO users (division, role, employee_ID, pwd) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($connect, $insertSql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssss", $division, $role, $employeeID, $hashedPassword);
            mysqli_stmt_execute($stmt);
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                echo "Super admin created successfully.\n";
            } else {
                echo "Failed to insert super admin.\n";
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "SQL error: " . mysqli_error($connect) . "\n";
        }
    } else {
        echo "Super admin already exists.\n";
    }
}

// Execute the functions
createTables($connect);
insertInitialSuperAdmin($connect);
mysqli_close($connect);
