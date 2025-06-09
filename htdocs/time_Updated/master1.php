<?php
session_start();
ob_start(); 

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); 
    exit();
}

include('./dbc.php');
include('includes/header2.php');
include('includes/navbar.php');
include('includes/check_access.php'); 

// Define the page name
$page = 'master1.php';

// Check if the user has access to this page
if (!hasAccess($page)) {
    echo "<div class='container'><div class='row mx-md-n8'><div class='col px-md-5'><h1>Access Denied</h1><p>You do not have permission to access this page.</p></div></div></div>";
    include('includes/scripts.php');
    include('includes/footer.php');
    ob_end_flush(); // Flush the output buffer
    exit();
}


// Handle form submission
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

    // Handle file upload
    $picture = $_FILES['picture'];
    $picture_path = '';

    if ($picture['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($picture["name"]);
        if (move_uploaded_file($picture["tmp_name"], $target_file)) {
            $picture_path = $target_file;
        } else {
            $error_message = "Failed to upload picture.";
        }
    } else {
        $error_message = "Error with picture upload.";
    }

    if (!empty($employee_ID) && !empty($employee_name) && !empty($division) && !empty($section) && !empty($designation) && !empty($appointment_date) && !empty($gender) && !empty($status) && !empty($nic_number) && !empty($telephone_number) && !empty($address) && !empty($card_valid_date) && !empty($card_issued_date) && !empty($picture_path)) {
        $insertSql = "INSERT INTO employees (employee_ID, employee_name, division, section, designation, appointment_date, gender, status, nic_number, telephone_number, address, card_valid_date, card_issued_date, picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($connect, $insertSql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "isssssssssssss", $employee_ID, $employee_name, $division, $section, $designation, $appointment_date, $gender, $status, $nic_number, $telephone_number, $address, $card_valid_date, $card_issued_date, $picture_path);
            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Employee created successfully.";
            } else {
                $error_message = "Failed to create employee.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_message = "SQL error: " . mysqli_error($connect);
        }
    } else {
        $error_message = "All fields are required.";
    }
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
$sectionSql = "SELECT section_id, section_name, division_id FROM sections";
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
        <h1>Create Employee</h1>
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
                                <input type="text" id="employee_ID" name="employee_ID" class="form-control" placeholder="Enter Employee ID" required>
                            </div>
                            <div class="form-group">
                                <label for="employee_name">Employee Name:</label>
                                <input type="text" id="employee_name" name="employee_name" class="form-control" placeholder="Enter Employee Name" required>
                            </div>
                            <div class="form-group">
                                <label for="division">Division:</label>
                                <select name="division" id="division" class="form-control" required>
                                    <option value="" disabled selected>Select Division</option>
                                    <?php foreach ($divisions as $division): ?>
                                        <option value="<?php echo htmlspecialchars($division['division_id']); ?>">
                                            <?php echo htmlspecialchars($division['division_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="section">Section:</label>
                                <select name="section" id="section" class="form-control" required>
                                    <option value="" disabled selected>Select Section</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="designation">Designation:</label>
                                <input type="text" id="designation" name="designation" class="form-control" placeholder="Enter Designation" required>
                            </div>
                            <div class="form-group">
                                <label for="appointment_date">Appointment Date:</label>
                                <input type="date" id="appointment_date" name="appointment_date" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="status">Status:</label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="" disabled selected>Select Status</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gender">Gender:</label>
                                <select name="gender" id="gender" class="form-control" required>
                                    <option value="" disabled selected>Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="nic_number">NIC Number:</label>
                                <input type="text" id="nic_number" name="nic_number" class="form-control" placeholder="Enter NIC Number" required>
                            </div>
                            <div class="form-group">
                                <label for="telephone_number">Telephone Number:</label>
                                <input type="text" id="telephone_number" name="telephone_number" class="form-control" placeholder="Enter Telephone Number" required>
                            </div>
                            <div class="form-group">
                                <label for="address">Address:</label>
                                <input type="text" id="address" name="address" class="form-control" placeholder="Enter Address" required>
                            </div>
                            <div class="form-group">
                                <label for="card_valid_date">Card Valid Date:</label>
                                <input type="date" id="card_valid_date" name="card_valid_date" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="card_issued_date">Card Issued Date:</label>
                                <input type="date" id="card_issued_date" name="card_issued_date" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="picture">Picture:</label>
                                <input type="file" id="picture" name="picture" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-lg btn-block">Create Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#division').change(function() {
            var division_id = $(this).val();
            if (division_id) {
                $.ajax({
                    type: 'POST',
                    url: '',
                    data: {
                        division_id: division_id
                    },
                    success: function(response) {
                        $('#section').html(response);
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                    }
                });
            } else {
                $('#section').html('<option value="" disabled selected>Select Section</option>');
            }
        });
    });
</script>

<?php
include('includes/scripts.php');
include('includes/footer.php');


if (isset($_POST['division_id'])) {
    $division_id = $_POST['division_id'];

    // Fetch sections related to the selected division
    $sql = "SELECT section_id, section_name FROM sections WHERE division_id = ?";
    $stmt = mysqli_prepare($connect, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $division_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            echo '<option value="" disabled selected>Select Section</option>';
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<option value="' . htmlspecialchars($row['section_id']) . '">' . htmlspecialchars($row['section_name']) . '</option>';
            }
        } else {
            echo '<option value="" disabled>No Sections Available</option>';
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "Error: " . mysqli_error($connect);
    }
}
?>