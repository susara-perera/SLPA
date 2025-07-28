<?php
include('./dbc.php');

// Set content type to JSON
header('Content-Type: application/json');

// Handle both GET and POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $employee_id = isset($input['employee_id']) ? trim($input['employee_id']) : '';
} else {
    $employee_id = isset($_GET['employee_id']) ? trim($_GET['employee_id']) : '';
}

if (empty($employee_id)) {
    echo json_encode(['success' => false, 'message' => 'Employee ID is required']);
    exit();
}

try {
    // Check database connection
    if (!$connect) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . mysqli_connect_error()]);
        exit();
    }
    
    // Simple query to get employee details
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
    
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'data' => [
                'employee_id' => $row['employee_ID'],
                'name' => $row['employee_name'],
                'division' => $row['division_name'],
                'section' => $row['section_name']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Employee not found with ID: ' . $employee_id
        ]);
    }
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Error in test_get_employee.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

if ($connect) {
    $connect->close();
}
?>
