<?php
include_once 'dbc.php';
session_start();

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get parameters
$report_type = $_GET['report_type'] ?? 'individual';
$employee_id = $_GET['employee_id'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$export_type = $_GET['export'] ?? 'excel';

// Validate required fields
if (empty($from_date) || empty($to_date)) {
    die('Date range is required');
}

if ($report_type === 'individual' && empty($employee_id)) {
    die('Employee ID is required for individual reports');
}

try {
    // Build query
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

    if ($report_type === 'individual') {
        $sql .= " AND employee_ID = ?";
        $params[] = $employee_id;
        $param_types .= "s";
    }

    $sql .= " ORDER BY date_ DESC, time_ DESC";

    // Execute query
    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, $param_types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    mysqli_stmt_close($stmt);

    if ($export_type === 'excel') {
        exportToExcel($data, $report_type, $employee_id, $from_date, $to_date);
    } elseif ($export_type === 'pdf') {
        exportToPDF($data, $report_type, $employee_id, $from_date, $to_date);
    }

} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}

function exportToExcel($data, $report_type, $employee_id, $from_date, $to_date) {
    $filename = "attendance_report_" . date('Y-m-d') . ".csv";
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Add header info
    fputcsv($output, ['SLPA Unit Attendance Report']);
    fputcsv($output, ['Report Type: ' . ucfirst($report_type)]);
    if ($report_type === 'individual') {
        fputcsv($output, ['Employee ID: ' . $employee_id]);
    }
    fputcsv($output, ['Date Range: ' . $from_date . ' to ' . $to_date]);
    fputcsv($output, ['Generated: ' . date('Y-m-d H:i:s')]);
    fputcsv($output, []); // Empty row
    
    // Add column headers
    fputcsv($output, ['Date', 'Employee ID', 'Time', 'Scan Type', 'Fingerprint ID']);
    
    // Add data rows
    foreach ($data as $row) {
        fputcsv($output, [
            $row['date_'],
            $row['employee_ID'],
            $row['time_'],
            $row['scan_type'],
            $row['fingerprint_id']
        ]);
    }
    
    fclose($output);
    exit();
}

function exportToPDF($data, $report_type, $employee_id, $from_date, $to_date) {
    // Simple HTML to PDF conversion
    $html = generatePDFHTML($data, $report_type, $employee_id, $from_date, $to_date);
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="attendance_report_' . date('Y-m-d') . '.pdf"');
    
    // For a simple solution, we'll output HTML that can be printed to PDF
    // In production, you might want to use libraries like TCPDF or DomPDF
    echo $html;
    exit();
}

function generatePDFHTML($data, $report_type, $employee_id, $from_date, $to_date) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Attendance Report</title>
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .info { margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
            .status-in { color: #28a745; font-weight: bold; }
            .status-out { color: #dc3545; font-weight: bold; }
            .footer { text-align: center; margin-top: 30px; font-size: 10px; color: #666; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>SLPA Unit Attendance Report</h1>
        </div>
        
        <div class="info">
            <p><strong>Report Type:</strong> ' . ucfirst($report_type) . '</p>';
    
    if ($report_type === 'individual') {
        $html .= '<p><strong>Employee ID:</strong> ' . htmlspecialchars($employee_id) . '</p>';
    }
    
    $html .= '
            <p><strong>Date Range:</strong> ' . $from_date . ' to ' . $to_date . '</p>
            <p><strong>Generated:</strong> ' . date('Y-m-d H:i:s') . '</p>
            <p><strong>Total Records:</strong> ' . count($data) . '</p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Employee ID</th>
                    <th>Time</th>
                    <th>Scan Type</th>
                    <th>Fingerprint ID</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($data as $row) {
        $statusClass = strtoupper($row['scan_type']) === 'IN' ? 'status-in' : 'status-out';
        $html .= '
                <tr>
                    <td>' . htmlspecialchars($row['date_']) . '</td>
                    <td>' . htmlspecialchars($row['employee_ID']) . '</td>
                    <td>' . htmlspecialchars($row['time_']) . '</td>
                    <td class="' . $statusClass . '">' . htmlspecialchars($row['scan_type']) . '</td>
                    <td>' . htmlspecialchars($row['fingerprint_id']) . '</td>
                </tr>';
    }
    
    $html .= '
            </tbody>
        </table>
        
        <div class="footer">
            <p>Copyright © 2025 Created by: Ports Authority IS Division. All rights reserved. | Version 3.1.0</p>
        </div>
    </body>
    </html>';
    
    return $html;
}
?>
