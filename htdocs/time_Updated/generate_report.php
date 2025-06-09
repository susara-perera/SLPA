<?php
include('includes/header.php');
include('includes/navbar.php');
include('./dbc.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_type = $_POST['report_type'];
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];

    // Initialize SQL query and parameters
    $sql = "";
    $params = [];

    if ($report_type === 'individual') {
        $employee_ID = $_POST['employee_ID'];
        $sql = "SELECT a.employee_ID, e.employee_name, d.division_name, s.section_name, a.date_,
                       a.scan_type, a.time_, f.location
                FROM attendance a
                JOIN employees e ON a.employee_ID = e.employee_ID
                JOIN divisions d ON e.division = d.division_id
                JOIN sections s ON e.section = s.section_id
                JOIN fingerprints f ON f.fingerprint_id = a.fingerprint_id
                WHERE a.employee_ID = ? AND a.date_ BETWEEN ? AND ?
                ORDER BY a.date_, a.time_";
        $params = [$employee_ID, $from_date, $to_date];
    } else if ($report_type === 'group') {
        $division = $_POST['division'];// Get the selected division
        $section = $_POST['section']; // Get the selected section

        $sql = "SELECT a.employee_ID, e.employee_name, d.division_name, s.section_name, a.date_,
                       a.scan_type, a.time_, f.location
                FROM attendance a
                JOIN employees e ON a.employee_ID = e.employee_ID
                JOIN divisions d ON e.division = d.division_id
                JOIN sections s ON e.section = s.section_id
                JOIN fingerprints f ON f.fingerprint_id = a.fingerprint_id
                WHERE e.division = ? AND a.date_ BETWEEN ? AND ?";
        $params = [$division, $from_date, $to_date];

        if ($section != 'all') { // If a specific section is selected
            $sql .= " AND e.section = ?"; // section filter to SQL
            $params[] = $section;
        }

        $sql .= " ORDER BY a.date_, a.time_";
    }

    $stmt = $connect->prepare($sql);

    if ($report_type === 'individual') {
        $stmt->bind_param("sss", ...$params);
    } else if ($report_type === 'group') {
        if (isset($section) && $section != 'all') {
            $stmt->bind_param("ssss", ...$params);
        } else {
            $stmt->bind_param("sss", $params[0], $params[1], $params[2]);
        }
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Generate report 
    if ($result->num_rows > 0) {
        echo "<div class='container'>";
        echo "<h2>Attendance Report</h2>";
        echo "<table class='table table-striped'>";
        echo "<thead><tr><th>Employee ID</th><th>Employee Name</th><th>Division</th><th>Section</th><th>Date</th><th>On Time(s) and Location(s)</th><th>Off Time(s) and Location(s)</th></tr></thead>";
        echo "<tbody>";

        // Initialize variables to track previous employee and date
        $previous_employee_ID = null;
        $previous_date = null;

        $ontime_records = [];
        $offtime_records = [];

        while ($row = $result->fetch_assoc()) {
            $current_employee_ID = $row['employee_ID'];
            $current_date = $row['date_'];

            // Check if the employee and date have changed, indicating a new row
            if ($current_employee_ID !== $previous_employee_ID || $current_date !== $previous_date) {
                if ($previous_employee_ID !== null) {
                    // Print the previous row's data
                    echo "<tr>";
                    echo "<td>" . $previous_employee_ID . "</td>";
                    echo "<td>" . $previous_employee_name . "</td>";
                    echo "<td>" . $previous_division_name . "</td>";
                    echo "<td>" . $previous_section_name . "</td>"; 
                    echo "<td>" . $previous_date . "</td>";
                    echo "<td>" . implode("<br>", $ontime_records) . "</td>";
                    echo "<td>" . implode("<br>", $offtime_records) . "</td>";
                    echo "</tr>";

                    // Reset the records arrays
                    $ontime_records = [];
                    $offtime_records = [];
                }

                // Update the previous employee and date variables
                $previous_employee_ID = $current_employee_ID;
                $previous_employee_name = $row['employee_name'];
                $previous_division_name = $row['division_name'];
                $previous_section_name = $row['section_name']; // Track section name
                $previous_date = $current_date;
            }

            // Store the ontime and offtime records with their locations
            if ($row['scan_type'] === 'IN') {
                $ontime_records[] = $row['time_'] . " (" . $row['location'] . ")";
            } else if ($row['scan_type'] === 'OUT') {
                $offtime_records[] = $row['time_'] . " (" . $row['location'] . ")";
            }
        }

        // Print the last row's data
        echo "<tr>";
        echo "<td>" . $previous_employee_ID . "</td>";
        echo "<td>" . $previous_employee_name . "</td>";
        echo "<td>" . $previous_division_name . "</td>";
        echo "<td>" . $previous_section_name . "</td>"; 
        echo "<td>" . $previous_date . "</td>";
        echo "<td>" . implode("<br>", $ontime_records) . "</td>";
        echo "<td>" . implode("<br>", $offtime_records) . "</td>";
        echo "</tr>";

        echo "</tbody>";
        echo "</table>";
        echo "</div>";
    } else {
        echo "<div class='container'><p>No records found for the selected field.</p></div>";
    }

    $stmt->close();
    $connect->close();
}

include('includes/scripts.php');
include('includes/footer.php');
?>
