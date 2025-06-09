<?php
include('./dbc.php');
include('includes/header.php');
include('includes/navbar.php');
include('includes/check_access.php');


$page = 'audit.php';

// Check if the user has access to this page
if (!hasAccess($page)) {
    echo "<div class='container'><div class='row mx-md-n8'><div class='col px-md-5'><h1>Access Denied</h1><p>You do not have permission to access this page.</p></div></div></div>";
    include('includes/scripts.php');
    include('includes/footer.php');
    exit();
}

// Fetch divisions from the database
$divisions = [];
$divisionSql = "SELECT division_id, division_name FROM divisions";
$result = mysqli_query($connect, $divisionSql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $divisions[] = $row;
    }
} else {
    $error_message = "Failed to fetch divisions: " . mysqli_error($connect);
}

// Handle form submission for generating report
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $division_id = $_POST['division'];
    $date_from = $_POST['date_from'];
    $date_to = $_POST['date_to'];

    // Validation for date range and division
    if (!empty($date_from) && !empty($date_to) && !empty($division_id)) {
        // Fetch division name
        if ($division_id === 'all') {
            $division_name = "All Divisions";
        } else {
            $divisionNameSql = "SELECT division_name FROM divisions WHERE division_id = ?";
            $stmt = mysqli_prepare($connect, $divisionNameSql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $division_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $division_name);
                mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);
            } else {
                $error_message = "SQL error: " . mysqli_error($connect);
            }
        }

        // SQL query to count the employees based on date range and division
        if ($division_id === 'all') {
            $reportSql = "
                SELECT COUNT(DISTINCT attendance.employee_ID) AS employee_count
                FROM attendance
                INNER JOIN employees ON attendance.employee_ID = employees.employee_ID
                WHERE date_ BETWEEN ? AND ?
                  AND (ontime IS NOT NULL OR offtime IS NOT NULL)";

            $stmt = mysqli_prepare($connect, $reportSql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ss", $date_from, $date_to);
            }
        } else {
            $reportSql = "
                SELECT COUNT(DISTINCT attendance.employee_ID) AS employee_count
                FROM attendance
                INNER JOIN employees ON attendance.employee_ID = employees.employee_ID
                WHERE employees.division = ?
                  AND date_ BETWEEN ? AND ?
                  AND (ontime IS NOT NULL OR offtime IS NOT NULL)";

            $stmt = mysqli_prepare($connect, $reportSql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sss", $division_id, $date_from, $date_to);
            }
        }

        if ($stmt) {
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $employee_count);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);
        } else {
            $error_message = "SQL error: " . mysqli_error($connect);
        }
    } else {
        $error_message = "All fields are required.";
    }
}
?>

<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Audits Report</h1>
        </div>
    </div>
</div><!-- /.container-fluid -->

<div class="row mx-md-n8">
    <div class="col px-md-5">
        <div class="p-3 border bg-light">
            <div class="container">
                <?php if (isset($error_message)): ?>
                    <div id="error-message" style="color: red;"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="division">Select Division:</label>
                        <select name="division" id="division" class="form-control" required>
                            <option value="" disabled selected>Select Division</option>
                            <option value="all">All Divisions</option>
                            <?php foreach ($divisions as $division): ?>
                                <option value="<?php echo htmlspecialchars($division['division_id']); ?>">
                                    <?php echo htmlspecialchars($division['division_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date_from">From:</label>
                        <input type="date" id="date_from" name="date_from" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="date_to">To:</label>
                        <input type="date" id="date_to" name="date_to" class="form-control" required max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-lg btn-block">Generate Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if (isset($employee_count)): ?>
    <div class="row mx-md-n8 mt-5">
        <div class="col px-md-5">
            <div class="p-3 border bg-light">
                <div class="container">
                    <h2><b>Report Result</b></h2>
                    <p><b>Division: </b><?php echo htmlspecialchars($division_name); ?></p>
                    <p><b>Date Range: </b><?php echo htmlspecialchars($date_from); ?> to <?php echo htmlspecialchars($date_to); ?></p>
                    <p><b>Total Employees: </b><?php echo htmlspecialchars($employee_count); ?></p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>