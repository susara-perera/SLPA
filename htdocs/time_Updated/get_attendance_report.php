<?php
include_once 'dbc.php';
session_start();

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

try {
    // Get form data
    $report_type = $_POST['report_type'] ?? 'individual';
    $employee_id = $_POST['employee_id'] ?? '';
    $from_date = $_POST['from_date'] ?? '';
    $to_date = $_POST['to_date'] ?? '';

    // Validate required fields
    if (empty($from_date) || empty($to_date)) {
        echo json_encode(['success' => false, 'message' => 'Date range is required']);
        exit();
    }

    // Validate employee ID for individual reports
    if ($report_type === 'individual' && empty($employee_id)) {
        echo json_encode(['success' => false, 'message' => 'Employee ID is required for individual reports']);
        exit();
    }

    // Build base query
    $sql = "SELECT 
                attendance_id,
                employee_ID,
                fingerprint_id,
                date_,
                time_,
                scan_type
            FROM attendance 
            WHERE date_ BETWEEN ? AND ?";
    
    $params = [$from_date, $to_date];
    $param_types = "ss";

    // Add employee filter for individual reports
    if ($report_type === 'individual') {
        $sql .= " AND employee_ID = ?";
        $params[] = $employee_id;
        $param_types .= "s";
    }

    // Add ordering
    $sql .= " ORDER BY date_ DESC, time_ DESC";

    // Prepare and execute query
    $stmt = mysqli_prepare($connect, $sql);
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . mysqli_error($connect));
    }

    mysqli_stmt_bind_param($stmt, $param_types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $attendance_data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $attendance_data[] = $row;
    }

    mysqli_stmt_close($stmt);

    // Calculate summary statistics
    $summary = [
        'total_records' => count($attendance_data),
        'unique_employees' => count(array_unique(array_column($attendance_data, 'employee_ID'))),
        'in_scans' => count(array_filter($attendance_data, function($record) {
            return strtoupper($record['scan_type']) === 'IN';
        })),
        'out_scans' => count(array_filter($attendance_data, function($record) {
            return strtoupper($record['scan_type']) === 'OUT';
        })),
        'date_range' => [
            'from' => $from_date,
            'to' => $to_date
        ]
    ];

    // Prepare query parameters for export
    $query_params = [
        'report_type' => $report_type,
        'employee_id' => $employee_id,
        'from_date' => $from_date,
        'to_date' => $to_date
    ];

    // Return response
    echo json_encode([
        'success' => true,
        'data' => $attendance_data,
        'summary' => $summary,
        'query_params' => $query_params,
        'message' => 'Report generated successfully'
    ]);

} catch (Exception $e) {
    error_log("Attendance Report Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while generating the report: ' . $e->getMessage()
    ]);
}
?>
