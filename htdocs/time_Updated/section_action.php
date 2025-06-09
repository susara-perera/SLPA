<?php
ob_start(); // Start output buffering
include('includes/header.php');
include('includes/navbar.php');
include('dbc.php');

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $division_id = $_POST['div1'];
    $section_name = $_POST['sec'];

    if (!empty($division_id) && !empty($section_name)) {

        // Insert the new section into the database
        $insertSql = "INSERT INTO sections (section_name, division_id) VALUES (?, ?)";
        $stmt = mysqli_prepare($connect, $insertSql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $section_name, $division_id);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                mysqli_close($connect);

                // Redirect to the section page with a success message
                header("Location: section.php?success=" . urlencode("New section created successfully."));
                ob_end_flush(); 
                exit();
            } else {
                $error_message = "Failed to create new section: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_message = "SQL error: " . mysqli_error($connect);
        }
    } else {
        $error_message = "All fields are required.";
    }
} else {
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
    mysqli_close($connect);
}
?>

<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Add New Section</h1>
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
                <?php if (isset($_GET['success'])): ?>
                    <div id="success-message" style="color: green;"><?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="div1">Select Division:</label>
                        <select name="div1" id="div1" class="form-control">
                            <?php foreach ($divisions as $division): ?>
                                <option value="<?php echo htmlspecialchars($division['division_id']); ?>">
                                    <?php echo htmlspecialchars($division['division_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sec">New Section Name</label>
                        <input class="form-control" name="sec" type="text" id="sec" value="<?php echo isset($_POST['sec']) ? htmlspecialchars($_POST['sec']) : ''; ?>" required>
                    </div>
                    <br>
                    <button type="submit" class="btn btn-primary btn-lg btn-block">Create Section</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/scripts.php');
include('includes/footer.php');
ob_end_flush(); 
?>