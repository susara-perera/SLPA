<?php
include('./dbc.php');
include('includes/header.php');
include('includes/navbar.php');

$division_id = '';
$division_name = '';
$error_message = '';
$success_message = '';

// Fetch the division details for editing
if (isset($_GET['division_id'])) {
    $division_id = $_GET['division_id'];

    $fetchSql = "SELECT * FROM divisions WHERE division_id = ?";
    $stmt = mysqli_prepare($connect, $fetchSql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $division_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            $division_name = $row['division_name'];
        } else {
            $error_message = "Division not found.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "SQL error: " . mysqli_error($connect);
    }
}

// Update division details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_division_name = $_POST['division_name'];
    $division_id = $_POST['division_id'];

    // Check if the new division name already exists
    $checkSql = "SELECT COUNT(*) AS count FROM divisions WHERE division_name = ? AND division_id != ?";
    $stmt = mysqli_prepare($connect, $checkSql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $new_division_name, $division_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        if ($row['count'] > 0) {
            $error_message = "Division name already exists.";
        } else {
            // Proceed with the update if no duplicate name exists
            $updateSql = "UPDATE divisions SET division_name = ? WHERE division_id = ?";
            $stmt = mysqli_prepare($connect, $updateSql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "si", $new_division_name, $division_id);
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    $success_message = "Division updated successfully.";
                } else {
                    mysqli_stmt_close($stmt);
                    $error_message = "Failed to update division: " . mysqli_stmt_error($stmt);
                }
            } else {
                $error_message = "SQL error: " . mysqli_error($connect);
            }
        }
    } else {
        $error_message = "SQL error: " . mysqli_error($connect);
    }
}

mysqli_close($connect);
?>

<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Edit Division</h1>
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
                        <label for="division_name">Division Name:</label>
                        <input type="text" name="division_name" id="division_name" class="form-control" value="<?php echo htmlspecialchars($division_name); ?>" required>
                    </div>
                    <input type="hidden" name="division_id" value="<?php echo htmlspecialchars($division_id); ?>">
                    <button type="submit" class="btn btn-primary">Update Division</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>