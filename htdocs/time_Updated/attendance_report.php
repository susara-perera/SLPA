<?php
include_once 'dbc.php';
session_start();

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_name = isset($_SESSION['username']) ? $_SESSION['username'] : $_SESSION['employee_ID'];
$user_role = $_SESSION['role'] ?? $_SESSION['user_type'] ?? 'Employee';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unit Attendance Report - SLPA</title>
    <link rel="icon" type="image/jpeg" href="dist/img/logo.jpg">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .report-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
        }
        .form-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .form-control {
            border-radius: 6px;
            border: 1px solid #ced4da;
            padding: 10px 12px;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .radio-group {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-generate {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 6px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-generate:hover {
            background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
        }
        .results-section {
            display: none;
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .table {
            margin-bottom: 0;
        }
        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
            color: #333;
        }
        .table td {
            vertical-align: middle;
        }
        .status-in {
            color: #28a745;
            font-weight: 600;
        }
        .status-out {
            color: #dc3545;
            font-weight: 600;
        }
        .loading {
            text-align: center;
            padding: 40px;
            display: none;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            display: none;
        }
        .summary-cards {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        .summary-card {
            flex: 1;
            min-width: 200px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .summary-number {
            font-size: 2rem;
            font-weight: bold;
            color: #007bff;
        }
        .summary-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        .export-buttons {
            margin-bottom: 20px;
            display: none;
        }
        .btn-export {
            margin-right: 10px;
            padding: 8px 16px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
            background: white;
            color: #333;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-export:hover {
            background: #f8f9fa;
            text-decoration: none;
            color: #333;
        }
    </style>
</head>
<body>

<div class="report-container">
    <!-- Header -->
    <div class="card">
        <div class="card-header">
            <h1 class="mb-0">
                <i class="fas fa-chart-line me-2"></i>
                Unit Attendance Report
            </h1>
            <p class="mb-0 mt-2">Generate comprehensive attendance reports for individuals or groups</p>
        </div>
    </div>

    <!-- Report Generation Form -->
    <div class="form-section">
        <form id="reportForm">
            <!-- Report Type Selection -->
            <div class="form-group">
                <label class="form-label">Select Report Type:</label>
                <div class="radio-group">
                    <div class="radio-option">
                        <input type="radio" id="individual" name="report_type" value="individual" checked>
                        <label for="individual">Individual</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" id="group" name="report_type" value="group">
                        <label for="group">Group</label>
                    </div>
                </div>
            </div>

            <!-- Employee ID Input -->
            <div class="form-group" id="employeeIdGroup">
                <label for="employee_id" class="form-label">Employee ID</label>
                <input type="text" class="form-control" id="employee_id" name="employee_id" 
                       placeholder="Enter Employee ID (e.g., 540567)">
            </div>

            <!-- Date Range -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="from_date" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="from_date" name="from_date" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="to_date" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="to_date" name="to_date" required>
                    </div>
                </div>
            </div>

            <!-- Generate Button -->
            <div class="text-center">
                <button type="submit" class="btn btn-generate">
                    <i class="fas fa-chart-bar me-2"></i>
                    Generate Report
                </button>
            </div>
        </form>
    </div>

    <!-- Loading Indicator -->
    <div class="loading" id="loading">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Generating report...</p>
    </div>

    <!-- Results Section -->
    <div class="results-section" id="resultsSection">
        <!-- Summary Cards -->
        <div class="summary-cards" id="summaryCards">
            <!-- Dynamic summary cards will be inserted here -->
        </div>

        <!-- Export Buttons -->
        <div class="export-buttons" id="exportButtons">
            <a href="#" class="btn-export" id="exportExcel">
                <i class="fas fa-file-excel me-1"></i> Export to Excel
            </a>
            <a href="#" class="btn-export" id="exportPDF">
                <i class="fas fa-file-pdf me-1"></i> Export to PDF
            </a>
            <button type="button" class="btn-export" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Report
            </button>
        </div>

        <!-- Results Table -->
        <div class="table-responsive">
            <table class="table table-striped" id="attendanceTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Employee ID</th>
                        <th>Time</th>
                        <th>Scan Type</th>
                        <th>Fingerprint ID</th>
                    </tr>
                </thead>
                <tbody id="attendanceTableBody">
                    <!-- Dynamic data will be inserted here -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- No Data Section -->
    <div class="no-data" id="noData">
        <i class="fas fa-search fa-3x text-muted mb-3"></i>
        <h4>No Attendance Records Found</h4>
        <p>Try adjusting your search criteria or date range.</p>
    </div>
</div>

<footer class="text-center mt-5 py-4 text-muted">
    <p>Copyright © 2025 <a href="#" class="text-primary">Created by: Ports Authority IS Division</a>. All rights reserved.</p>
    <p class="mb-0">Version 3.1.0</p>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // Set default dates (last 30 days)
    const today = new Date();
    const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
    
    $('#to_date').val(today.toISOString().split('T')[0]);
    $('#from_date').val(thirtyDaysAgo.toISOString().split('T')[0]);

    // Handle report type change
    $('input[name="report_type"]').change(function() {
        if ($(this).val() === 'group') {
            $('#employeeIdGroup').hide();
            $('#employee_id').removeAttr('required');
        } else {
            $('#employeeIdGroup').show();
            $('#employee_id').attr('required', 'required');
        }
    });

    // Handle form submission
    $('#reportForm').submit(function(e) {
        e.preventDefault();
        
        // Show loading
        $('#loading').show();
        $('#resultsSection').hide();
        $('#noData').hide();
        
        // Get form data
        const formData = {
            report_type: $('input[name="report_type"]:checked').val(),
            employee_id: $('#employee_id').val(),
            from_date: $('#from_date').val(),
            to_date: $('#to_date').val()
        };
        
        // AJAX request to get attendance data
        $.ajax({
            url: 'get_attendance_report.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                $('#loading').hide();
                
                if (response.success && response.data.length > 0) {
                    displayResults(response);
                } else {
                    $('#noData').show();
                }
            },
            error: function(xhr, status, error) {
                $('#loading').hide();
                console.error('Error:', error);
                alert('An error occurred while generating the report. Please try again.');
            }
        });
    });

    function displayResults(response) {
        // Display summary
        displaySummary(response.summary);
        
        // Display table data
        displayTable(response.data);
        
        // Show results section
        $('#resultsSection').show();
        $('#exportButtons').show();
        
        // Set up export links
        setupExportLinks(response.query_params);
    }

    function displaySummary(summary) {
        const summaryHtml = `
            <div class="summary-card">
                <div class="summary-number">${summary.total_records}</div>
                <div class="summary-label">Total Records</div>
            </div>
            <div class="summary-card">
                <div class="summary-number">${summary.unique_employees}</div>
                <div class="summary-label">Employees</div>
            </div>
            <div class="summary-card">
                <div class="summary-number">${summary.in_scans}</div>
                <div class="summary-label">IN Scans</div>
            </div>
            <div class="summary-card">
                <div class="summary-number">${summary.out_scans}</div>
                <div class="summary-label">OUT Scans</div>
            </div>
        `;
        $('#summaryCards').html(summaryHtml);
    }

    function displayTable(data) {
        let tableHtml = '';
        
        data.forEach(function(record) {
            const statusClass = record.scan_type === 'IN' ? 'status-in' : 'status-out';
            tableHtml += `
                <tr>
                    <td>${record.date_}</td>
                    <td>${record.employee_ID}</td>
                    <td>${record.time_}</td>
                    <td><span class="${statusClass}">${record.scan_type}</span></td>
                    <td>${record.fingerprint_id}</td>
                </tr>
            `;
        });
        
        $('#attendanceTableBody').html(tableHtml);
    }

    function setupExportLinks(queryParams) {
        const params = new URLSearchParams(queryParams);
        params.append('export', 'excel');
        $('#exportExcel').attr('href', 'export_attendance.php?' + params.toString());
        
        params.set('export', 'pdf');
        $('#exportPDF').attr('href', 'export_attendance.php?' + params.toString());
    }
});
</script>

</body>
</html>
