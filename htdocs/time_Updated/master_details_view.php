<?php
include('includes/header.php');
include('includes/navbar.php');
include('./dbc.php');

$original_employee_ID = $_GET['id'];

// Fetch employee details along with section name
$employeeSql = "
    SELECT e.*, s.section_name 
    FROM employees e
    LEFT JOIN sections s ON e.section = s.section_id
    WHERE e.employee_ID = ?
";
$stmt = mysqli_prepare($connect, $employeeSql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $original_employee_ID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $employee = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
} else {
    $error_message = "Failed to prepare SQL statement: " . mysqli_error($connect);
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

// Fetch sections from the database
$sections = [];
$sectionSql = "SELECT section_id, section_name FROM sections";
$result = mysqli_query($connect, $sectionSql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $sections[] = $row;
    }
} else {
    $error_message = "Failed to fetch sections: " . mysqli_error($connect);
}
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200&display=swap" rel="stylesheet" />

<div class="row mx-md-n8">
    <div class="col px-md-5">
        <h1>View Employee Details</h1>
        <div class="p-3 border bg-light">
            <div class="container">
                <?php if (isset($error_message)): ?>
                    <div id="error-message" style="color: red;"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="employee_ID">Employee ID:</label>
                            <p><?php echo htmlspecialchars($employee['employee_ID']); ?></p>
                        </div>
                        <div class="form-group">
                            <label for="employee_name">Employee Name:</label>
                            <p><?php echo htmlspecialchars($employee['employee_name']); ?></p>
                        </div>
                        <div class="form-group">
                            <label for="division">Division:</label>
                            <p><?php 
                                foreach ($divisions as $division) {
                                    if ($employee['division'] == $division['division_id']) {
                                        echo htmlspecialchars($division['division_name']);
                                    }
                                }
                            ?></p>
                        </div>
                        <div class="form-group">
                            <label for="section">Section:</label>
                            <p><?php echo htmlspecialchars($employee['section_name']); ?></p>
                        </div>
                        <div class="form-group">
                            <label for="designation">Designation:</label>
                            <p><?php echo htmlspecialchars($employee['designation']); ?></p>
                        </div>
                        <div class="form-group">
                            <label for="appointment_date">Appointment Date:</label>
                            <p><?php echo htmlspecialchars($employee['appointment_date']); ?></p>
                        </div>
                        <div class="form-group">
                            <label for="status">Status:</label>
                            <p><?php echo htmlspecialchars($employee['status']); ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="gender">Gender:</label>
                            <p><?php echo htmlspecialchars($employee['gender']); ?></p>
                        </div>
                        <div class="form-group">
                            <label for="nic_number">NIC Number:</label>
                            <p><?php echo htmlspecialchars($employee['nic_number']); ?></p>
                        </div>
                        <div class="form-group">
                            <label for="telephone_number">Telephone Number:</label>
                            <p><?php echo htmlspecialchars($employee['telephone_number']); ?></p>
                        </div>
                        <div class="form-group">
                            <label for="address">Address:</label>
                            <p><?php echo htmlspecialchars($employee['address']); ?></p>
                        </div>
                        <div class="form-group">
                            <label for="card_valid_date">Card Valid Date:</label>
                            <p><?php echo htmlspecialchars($employee['card_valid_date']); ?></p>
                        </div>
                        <div class="form-group">
                            <label for="card_issued_date">Card Issued Date:</label>
                            <p><?php echo htmlspecialchars($employee['card_issued_date']); ?></p>
                        </div>
                        <div class="form-group">
                            <label for="picture">Picture:</label>
                            <?php if (!empty($employee['picture'])): ?>
                                <img src="<?php echo htmlspecialchars($employee['picture']); ?>" alt="Employee Picture" style="width: 100px; height: 100px;">
                            <?php else: ?>
                                <p>No picture available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>
