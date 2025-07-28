<?php
include('./dbc.php');
header('Content-Type: application/json');

try {
    if (!$connect) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit();
    }
    
    // Get sample employees
    $sql = "SELECT employee_ID, employee_name, division, section FROM employees LIMIT 10";
    $result = mysqli_query($connect, $sql);
    
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Query failed: ' . mysqli_error($connect)]);
        exit();
    }
    
    $employees = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Sample employees retrieved',
        'employees' => $employees
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

if ($connect) {
    mysqli_close($connect);
}
?>
