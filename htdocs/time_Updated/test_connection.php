<?php
header('Content-Type: application/json');

// Test database connection
$dbServerName = "localhost";
$dbUserName = "root";
$dbPassword = "";
$dbName = "slpa_db";

try {
    $connect = mysqli_connect($dbServerName, $dbUserName, $dbPassword, $dbName);
    
    if (!$connect) {
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . mysqli_connect_error()
        ]);
        exit();
    }
    
    // Test if employees table exists
    $result = mysqli_query($connect, "SHOW TABLES LIKE 'employees'");
    if (mysqli_num_rows($result) == 0) {
        echo json_encode([
            'success' => false,
            'message' => 'employees table does not exist'
        ]);
        exit();
    }
    
    // Test if we can select from employees table
    $result = mysqli_query($connect, "SELECT COUNT(*) as count FROM employees");
    if (!$result) {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot query employees table: ' . mysqli_error($connect)
        ]);
        exit();
    }
    
    $row = mysqli_fetch_assoc($result);
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'employees_count' => $row['count']
    ]);
    
    mysqli_close($connect);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
