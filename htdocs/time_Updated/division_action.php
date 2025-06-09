<?php
include('includes/header.php');
include('includes/navbar.php');
include('./dbc.php');

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $division_id = $_POST['division_id'];
    $division_name = $_POST['division_name'];

    if (!empty($division_id) && !empty($division_name)) {

        // Check if division name already exists
        $checkSql = "SELECT COUNT(*) AS count FROM divisions WHERE division_name = ?";
        $stmt = mysqli_prepare($connect, $checkSql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $division_name);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);

            if ($row['count'] > 0) {
                $error_message = "Division name already exists.";
            } else {
                // Proceed with the insert if name is unique
                $insertSql = "INSERT INTO divisions (division_id, division_name) VALUES (?, ?)";
                $stmt = mysqli_prepare($connect, $insertSql);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "ss", $division_id, $division_name);
                    if (mysqli_stmt_execute($stmt)) {
                        $success_message = "New division created successfully.";
                        $_POST = array();  // Clear the form fields
                    } else {
                        $error_message = "Failed to create new division: " . mysqli_stmt_error($stmt);
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $error_message = "SQL error: " . mysqli_error($connect);
                }
            }
        } else {
            $error_message = "SQL error: " . mysqli_error($connect);
        }
    } else {
        $error_message = "All fields are required.";
    }
}

mysqli_close($connect);
?>

<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Add New Division</h1>
        </div>
    </div>
</div><!-- /.container-fluid -->

<div class="row mx-md-n8">
    <div class="col px-md-5">
        <div class="p-3 border bg-light">
            <div class="container">
                <?php if (!empty($error_message)): ?>
                    <div id="error-message" style="color: red;"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <?php if (!empty($success_message)): ?>
                    <div id="success-message" style="color: green;"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="divisionID">Division ID</label>
                        <input class="form-control" name="division_id" type="text" id="divisionID" value="<?php echo isset($_POST['division_id']) ? htmlspecialchars($_POST['division_id']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="divisionName">New Division Name</label>
                        <input class="form-control" name="division_name" type="text" id="divisionName" value="<?php echo isset($_POST['division_name']) ? htmlspecialchars($_POST['division_name']) : ''; ?>" required>
                    </div>
                    <br>
                    <button type="submit" class="btn btn-primary btn-lg btn-block">Create Division</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>