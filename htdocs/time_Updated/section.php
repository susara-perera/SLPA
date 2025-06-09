<?php
session_start();
ob_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
}

include('./dbc.php');
include('includes/header2.php');
include('includes/navbar.php');
include('includes/check_access.php');

$page = 'section.php';

// Check if the user has access to this page
if (!hasAccess($page)) {
    echo "<div class='container'><div class='row mx-md-n8'><div class='col px-md-5'><h1>Access Denied</h1><p>You do not have permission to access this page.</p></div></div></div>";
    include('includes/scripts.php');
    include('includes/footer.php');
    ob_end_flush(); 
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

mysqli_close($connect);

// Check for success or error messages 
$success_message = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>

<div class="row mx-md-n8">
    <div class="col px-md-5">
        <h1>Add New Section</h1>
        <div class="p-3 border bg-light">
            <div class="container">
                <?php if (!empty($error_message)): ?>
                    <div id="error-message" style="color: red;"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <?php if (!empty($success_message)): ?>
                    <div id="success-message" style="color: green;"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <form method="POST" action="section_action.php">
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
                        <input class="form-control" name="sec" type="text" id="sec" required>
                    </div>
                    <br>
                    <button type="submit" class="btn btn-primary btn-lg btn-block">Create Section</button>
                    <a href="section.php" class="btn btn-secondary btn-lg btn-block" style="margin-top: 10px;">Cancel</a>
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