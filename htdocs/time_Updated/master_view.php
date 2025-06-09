<?php
include('includes/header.php');
include('includes/navbar.php');
include('./dbc.php');

$employee_ID = $_GET['id'];

// Fetch employee details along with section name
$employeeSql = "
    SELECT e.*, s.section_name 
    FROM employees e
    LEFT JOIN sections s ON e.section = s.section_id
    WHERE e.employee_ID = ?
";
$stmt = mysqli_prepare($connect, $employeeSql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $employee_ID);
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
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200&display=swap" rel="stylesheet" />

<div class="row mx-md-n8">
    <div class="col px-md-5">
        <h1>View Employee</h1>
        <div class="p-3 border bg-light">
            <div class="container">
                <?php if (isset($error_message)): ?>
                    <div id="error-message" style="color: red;"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <form>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="employee_ID">Employee ID:</label>
                                <input type="text" id="employee_ID" name="employee_ID" class="form-control" value="<?php echo htmlspecialchars($employee['employee_ID']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="employee_name">Employee Name:</label>
                                <input type="text" id="employee_name" name="employee_name" class="form-control" value="<?php echo htmlspecialchars($employee['employee_name']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="division">Division:</label>
                                <select name="division" id="division" class="form-control" disabled>
                                    <?php foreach ($divisions as $division): ?>
                                        <option value="<?php echo htmlspecialchars($division['division_id']); ?>" <?php echo ($employee['division'] == $division['division_id'] ? 'selected' : ''); ?>>
                                            <?php echo htmlspecialchars($division['division_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="section">Section:</label>
                                <input type="text" id="section" name="section" class="form-control" value="<?php echo htmlspecialchars($employee['section_name']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="designation">Designation:</label>
                                <input type="text" id="designation" name="designation" class="form-control" value="<?php echo htmlspecialchars($employee['designation']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="appointment_date">Appointment Date:</label>
                                <input type="text" id="appointment_date" name="appointment_date" class="form-control" value="<?php echo htmlspecialchars($employee['appointment_date']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="status">Status:</label>
                                <input type="text" id="status" name="status" class="form-control" value="<?php echo htmlspecialchars($employee['status']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="gender">Gender:</label>
                                <input type="text" id="gender" name="gender" class="form-control" value="<?php echo htmlspecialchars($employee['gender']); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nic_number">NIC Number:</label>
                                <input type="text" id="nic_number" name="nic_number" class="form-control" value="<?php echo htmlspecialchars($employee['nic_number']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="telephone_number">Telephone Number:</label>
                                <input type="text" id="telephone_number" name="telephone_number" class="form-control" value="<?php echo htmlspecialchars($employee['telephone_number']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="address">Address:</label>
                                <input type="text" id="address" name="address" class="form-control" value="<?php echo htmlspecialchars($employee['address']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="card_valid_date">Card Valid Date:</label>
                                <input type="text" id="card_valid_date" name="card_valid_date" class="form-control" value="<?php echo htmlspecialchars($employee['card_valid_date']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="card_issued_date">Card Issued Date:</label>
                                <input type="text" id="card_issued_date" name="card_issued_date" class="form-control" value="<?php echo htmlspecialchars($employee['card_issued_date']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="picture">Picture:</label>
                                <?php if ($employee['picture']): ?>
                                    <div>
                                        <img src="uploads/<?php echo htmlspecialchars($employee['picture']); ?>" alt="Employee Picture" style="max-width: 200px; max-height: 200px;">
                                    </div>
                                <?php else: ?>
                                    <p>No picture available</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <a href="master_edit.php?id=<?php echo $employee_ID; ?>" class="btn btn-primary">Edit</a>
                        <a href="master_records.php" class="btn btn-secondary">Back</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>