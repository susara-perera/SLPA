<?php
include('./dbc.php');
include('includes/header.php');
include('includes/navbar.php');

$success_message = '';
$error_message = '';

if (isset($_GET['section_id'])) {
    $section_id = $_GET['section_id'];
    $division_id = isset($_GET['division_id']) ? $_GET['division_id'] : '';

    // Fetch current section details
    $sectionSql = "SELECT * FROM sections WHERE section_id = ?";
    $stmt = mysqli_prepare($connect, $sectionSql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $section_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $section = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "SQL error: " . mysqli_error($connect);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $section_name = $_POST['section_name'];

        if (!empty($section_name)) {
            $updateSql = "UPDATE sections SET section_name = ? WHERE section_id = ?";
            $stmt = mysqli_prepare($connect, $updateSql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "si", $section_name, $section_id);
                if (mysqli_stmt_execute($stmt)) {
                    $success_message = "Section updated successfully.";
                } else {
                    $error_message = "Failed to update section: " . mysqli_stmt_error($stmt);
                }
                mysqli_stmt_close($stmt);
            } else {
                $error_message = "SQL error: " . mysqli_error($connect);
            }
        } else {
            $error_message = "Section name cannot be empty.";
        }
    }
}

mysqli_close($connect);
?>

<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Edit Section</h1>
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
                        <label for="section_name">Section Name</label>
                        <input class="form-control" name="section_name" type="text" id="section_name" value="<?php echo isset($section['section_name']) ? htmlspecialchars($section['section_name']) : ''; ?>" required>
                    </div>
                    <br>
                    <button type="submit" class="btn btn-primary btn-lg btn-block">Update Section</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>