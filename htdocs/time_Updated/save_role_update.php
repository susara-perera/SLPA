<?php
session_start();
include('./dbc.php');

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is authenticated
if (!isset($_SESSION['port_user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $user_id = isset($input['user_id']) ? trim($input['user_id']) : '';
    $user_name = isset($input['user_name']) ? trim($input['user_name']) : '';
    $division = isset($input['division']) ? trim($input['division']) : '';
    $section = isset($input['section']) ? trim($input['section']) : '';
    $current_role = isset($input['current_role']) ? trim($input['current_role']) : '';
    $new_role = isset($input['new_role']) ? trim($input['new_role']) : '';
    $status = isset($input['status']) ? trim($input['status']) : 'Active';
    $assigned_port = isset($input['assigned_port']) ? trim($input['assigned_port']) : $_SESSION['port_name'];
    $assigned_by = $_SESSION['port_user'];
    
    if (empty($user_id) || empty($user_name) || empty($new_role)) {
        echo json_encode(['success' => false, 'message' => 'User ID, Name, and New Role are required']);
        exit();
    }
    
    try {
        // Check if user exists in main employees table
        $check_sql = "SELECT employee_ID FROM employees WHERE employee_ID = ?";
        $check_stmt = $connect->prepare($check_sql);
        $check_stmt->bind_param("s", $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'User ID not found in system']);
            exit();
        }
        $check_stmt->close();
        
        // Check if role assignment already exists for this user and port
        $existing_sql = "SELECT id FROM assign_role WHERE employee_id = ? AND port_name = ?";
        $existing_stmt = $connect->prepare($existing_sql);
        $existing_stmt->bind_param("ss", $user_id, $assigned_port);
        $existing_stmt->execute();
        $existing_result = $existing_stmt->get_result();
        
        if ($existing_result->num_rows > 0) {
            // Update existing role assignment
            $update_sql = "UPDATE assign_role SET 
                          employee_name = ?, division = ?, section = ?, 
                          current_role = ?, assigned_role = ?, status = ?,
                          assigned_by = ?, updated_date = NOW()
                          WHERE employee_id = ? AND port_name = ?";
            $update_stmt = $connect->prepare($update_sql);
            $update_stmt->bind_param("sssssssss", $user_name, $division, $section, $current_role, $new_role, $status, $assigned_by, $user_id, $assigned_port);
            
            if ($update_stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Role updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update role']);
            }
            $update_stmt->close();
        } else {
            // Create new role assignment
            $insert_sql = "INSERT INTO assign_role 
                          (employee_id, employee_name, division, section, current_role, assigned_role, port_name, status, assigned_by)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $connect->prepare($insert_sql);
            $insert_stmt->bind_param("sssssssss", $user_id, $user_name, $division, $section, $current_role, $new_role, $assigned_port, $status, $assigned_by);
            
            if ($insert_stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Role assigned successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to assign role']);
            }
            $insert_stmt->close();
        }
        $existing_stmt->close();
        
    } catch (Exception $e) {
        error_log("Error in save_role_update.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

if ($connect) {
    $connect->close();
}
?>
