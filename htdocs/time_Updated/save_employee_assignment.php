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
    
    $employee_id = isset($input['employee_id']) ? trim($input['employee_id']) : '';
    $employee_name = isset($input['employee_name']) ? trim($input['employee_name']) : '';
    $division = isset($input['division']) ? trim($input['division']) : '';
    $section = isset($input['section']) ? trim($input['section']) : '';
    $role = isset($input['role']) ? trim($input['role']) : '';
    $port_name = $_SESSION['port_name'];
    $assigned_by = $_SESSION['port_user'];
    
    if (empty($employee_id) || empty($employee_name) || empty($role)) {
        echo json_encode(['success' => false, 'message' => 'Employee ID, Name, and Role are required']);
        exit();
    }
    
    try {
        // Check if employee exists in main employees table
        $check_sql = "SELECT employee_ID FROM employees WHERE employee_ID = ?";
        $check_stmt = $connect->prepare($check_sql);
        $check_stmt->bind_param("s", $employee_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Employee ID not found in system']);
            exit();
        }
        $check_stmt->close();
        
        // Check if assignment already exists for this employee and port
        $existing_sql = "SELECT id FROM assign_role WHERE employee_id = ? AND port_name = ?";
        $existing_stmt = $connect->prepare($existing_sql);
        $existing_stmt->bind_param("ss", $employee_id, $port_name);
        $existing_stmt->execute();
        $existing_result = $existing_stmt->get_result();
        
        if ($existing_result->num_rows > 0) {
            // Update existing assignment
            $update_sql = "UPDATE assign_role SET 
                          employee_name = ?, division = ?, section = ?, assigned_role = ?, 
                          assigned_by = ?, updated_date = NOW(), status = 'Active'
                          WHERE employee_id = ? AND port_name = ?";
            $update_stmt = $connect->prepare($update_sql);
            $update_stmt->bind_param("sssssss", $employee_name, $division, $section, $role, $assigned_by, $employee_id, $port_name);
            
            if ($update_stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Employee assignment updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update assignment']);
            }
            $update_stmt->close();
        } else {
            // Create new assignment
            $insert_sql = "INSERT INTO assign_role 
                          (employee_id, employee_name, division, section, assigned_role, port_name, assigned_by, status)
                          VALUES (?, ?, ?, ?, ?, ?, ?, 'Active')";
            $insert_stmt = $connect->prepare($insert_sql);
            $insert_stmt->bind_param("sssssss", $employee_id, $employee_name, $division, $section, $role, $port_name, $assigned_by);
            
            if ($insert_stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Employee assigned successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to assign employee']);
            }
            $insert_stmt->close();
        }
        $existing_stmt->close();
        
    } catch (Exception $e) {
        error_log("Error in save_employee_assignment.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

if ($connect) {
    $connect->close();
}
?>
