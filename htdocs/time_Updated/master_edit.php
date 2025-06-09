<?php
include('includes/header.php');
include('includes/navbar.php');
include('./dbc.php');

$original_employee_ID = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_ID = $_POST['employee_ID'];
    $employee_name = $_POST['employee_name'];
    $division = $_POST['division'];
    $section = $_POST['section'];
    $designation = $_POST['designation'];
    $appointment_date = $_POST['appointment_date'];
    $gender = $_POST['gender'];
    $status = $_POST['status'];
    $nic_number = $_POST['nic_number'];
    $telephone_number = $_POST['telephone_number'];
    $address = $_POST['address'];
    $card_valid_date = $_POST['card_valid_date'];
    $card_issued_date = $_POST['card_issued_date'];
    $picture = $_FILES['picture']['name'];

    if (!empty($employee_ID) && !empty($employee_name) && !empty($division)) {
        // Handle picture upload
        if ($picture) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($picture);
            move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file);
        } else {
            // Use the existing picture if no new picture is uploaded
            $picture = $_POST['existing_picture'];
        }

        $updateSql = "UPDATE employees SET 
            employee_ID = ?, 
            employee_name = ?, 
            division = ?, 
            section = ?, 
            designation = ?, 
            appointment_date = ?, 
            gender = ?, 
            status = ?, 
            nic_number = ?, 
            telephone_number = ?, 
            address = ?, 
            card_valid_date = ?, 
            card_issued_date = ?, 
            picture = ? 
            WHERE employee_ID = ?";

        $stmt = mysqli_prepare($connect, $updateSql);
        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "isssssssssssssi",
                $employee_ID,
                $employee_name,
                $division,
                $section,
                $designation,
                $appointment_date,
                $gender,
                $status,
                $nic_number,
                $telephone_number,
                $address,
                $card_valid_date,
                $card_issued_date,
                $picture,
                $original_employee_ID
            );
            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Employee updated successfully.";
                $original_employee_ID = $employee_ID;
            } else {
                $error_message = "Failed to update employee.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_message = "SQL error: " . mysqli_error($connect);
        }
    } else {
        $error_message = "All fields are required.";
    }
}

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
        <h1>Edit Employee</h1>
        <div class="p-3 border bg-light">
            <div class="container">
                <?php if (isset($error_message)): ?>
                    <div id="error-message" style="color: red;"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <?php if (isset($success_message)): ?>
                    <div id="success-message" style="color: green;"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="employee_ID">Employee ID:</label>
                                <input type="text" id="employee_ID" name="employee_ID" class="form-control" value="<?php echo isset($success_message) ? '' : htmlspecialchars($employee['employee_ID']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="employee_name">Employee Name:</label>
                                <input type="text" id="employee_name" name="employee_name" class="form-control" value="<?php echo isset($success_message) ? '' : htmlspecialchars($employee['employee_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="division">Division:</label>
                                <select name="division" id="division" class="form-control" required>
                                    <option value="" disabled>Select Division</option>
                                    <?php foreach ($divisions as $division): ?>
                                        <option value="<?php echo htmlspecialchars($division['division_id']); ?>" <?php echo ($employee['division'] == $division['division_id'] ? 'selected' : ''); ?>>
                                            <?php echo htmlspecialchars($division['division_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="section">Section:</label>
                                <select name="section" id="section" class="form-control">
                                    <option value="" disabled>Select Section</option>
                                    <?php foreach ($sections as $sec): ?>
                                        <option value="<?php echo htmlspecialchars($sec['section_id']); ?>" <?php echo ($employee['section'] == $sec['section_id'] ? 'selected' : ''); ?>>
                                            <?php echo htmlspecialchars($sec['section_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="designation">Designation:</label>
                                <input type="text" id="designation" name="designation" class="form-control" value="<?php echo isset($success_message) ? '' : htmlspecialchars($employee['designation']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="appointment_date">Appointment Date:</label>
                                <input type="date" id="appointment_date" name="appointment_date" class="form-control" value="<?php echo isset($success_message) ? '' : htmlspecialchars($employee['appointment_date']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="status">Status:</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="" disabled>Select Status</option>
                                    <option value="Active" <?php echo ($employee['status'] == 'Active' ? 'selected' : ''); ?>>Active</option>
                                    <option value="Inactive" <?php echo ($employee['status'] == 'Inactive' ? 'selected' : ''); ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gender">Gender:</label>
                                <select name="gender" id="gender" class="form-control">
                                    <option value="" disabled>Select Gender</option>
                                    <option value="Male" <?php echo ($employee['gender'] == 'Male' ? 'selected' : ''); ?>>Male</option>
                                    <option value="Female" <?php echo ($employee['gender'] == 'Female' ? 'selected' : ''); ?>>Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="nic_number">NIC Number:</label>
                                <input type="text" id="nic_number" name="nic_number" class="form-control" value="<?php echo isset($success_message) ? '' : htmlspecialchars($employee['nic_number']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="telephone_number">Telephone Number:</label>
                                <input type="text" id="telephone_number" name="telephone_number" class="form-control" value="<?php echo isset($success_message) ? '' : htmlspecialchars($employee['telephone_number']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="address">Address:</label>
                                <textarea id="address" name="address" class="form-control"><?php echo isset($success_message) ? '' : htmlspecialchars($employee['address']); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="card_valid_date">Card Valid Date:</label>
                                <input type="date" id="card_valid_date" name="card_valid_date" class="form-control" value="<?php echo isset($success_message) ? '' : htmlspecialchars($employee['card_valid_date']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="card_issued_date">Card Issued Date:</label>
                                <input type="date" id="card_issued_date" name="card_issued_date" class="form-control" value="<?php echo isset($success_message) ? '' : htmlspecialchars($employee['card_issued_date']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="picture">Picture:</label>
                                <input type="file" id="picture" name="picture" class="form-control">
                                <?php if (!empty($employee['picture'])): ?>
                                    <img src="<?php echo htmlspecialchars($employee['picture']); ?>" alt="Current Picture" style="width: 100px; height: 100px;">
                                    <input type="hidden" name="existing_picture" value="<?php echo htmlspecialchars($employee['picture']); ?>">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Employee</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('division').addEventListener('change', function() {
        var division_id = this.value;
        fetch('fetch_sections.php?division_id=' + division_id)
            .then(response => response.json())
            .then(data => {
                var sectionSelect = document.getElementById('section');
                sectionSelect.innerHTML = '<option value="">Select Section</option>';
                data.sections.forEach(function(section) {
                    var option = document.createElement('option');
                    option.value = section.section_id;
                    option.textContent = section.section_name;
                    sectionSelect.appendChild(option);
                });
            });
    });
</script>
<?php
include('includes/scripts.php');
include('includes/footer.php');
?>