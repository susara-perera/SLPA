<?php
include('./dbc.php');

// Set content type to JSON
header('Content-Type: application/json');

// Handle both GET and POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = isset($input['user_id']) ? trim($input['user_id']) : '';
} else {
    $user_id = isset($_GET['user_id']) ? trim($_GET['user_id']) : '';
}

if (empty($user_id)) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit();
}

try {
    // Check database connection
    if (!$connect) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . mysqli_connect_error()]);
        exit();
    }
    
    // Get employee details with division and section names
    $sql = "SELECT e.employee_ID, e.employee_name,
                   COALESCE(d.division_name, 'Unknown Division') as division_name,
                   COALESCE(s.section_name, 'Unknown Section') as section_name
            FROM employees e
            LEFT JOIN divisions d ON e.division = d.division_id
            LEFT JOIN sections s ON e.section = s.section_id
            WHERE e.employee_ID = ?";
    
    $stmt = $connect->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Query prepare failed: ' . $connect->error]);
        exit();
    }
    
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Get current role and status from assign_role table
        $current_role = 'Employee';
        $status = 'Active';
        
        // Check assign_role table for current assignment
        $role_sql = "SELECT assigned_role, status FROM assign_role WHERE employee_id = ? ORDER BY assigned_date DESC LIMIT 1";
        $role_stmt = $connect->prepare($role_sql);
        if ($role_stmt) {
            $role_stmt->bind_param("s", $user_id);
            $role_stmt->execute();
            $role_result = $role_stmt->get_result();
            if ($role_row = $role_result->fetch_assoc()) {
                $current_role = $role_row['assigned_role'] ?: 'Employee';
                $status = $role_row['status'] ?: 'Active';
            }
            $role_stmt->close();
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'user_id' => $row['employee_ID'],
                'name' => $row['employee_name'],
                'division' => $row['division_name'],
                'section' => $row['section_name'],
                'current_role' => $current_role,
                'status' => $status
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'User not found with ID: ' . $user_id
        ]);
    }
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Error in test_get_role.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

if ($connect) {
    $connect->close();
}
?>
