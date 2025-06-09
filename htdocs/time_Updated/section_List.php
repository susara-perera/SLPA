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
$page = 'section_List.php'; 

// Check if the user has access to this page
if (!hasAccess($page)) {
    echo "<div class='container'><div class='row mx-md-n8'><div class='col px-md-5'><h1>Access Denied</h1><p>You do not have permission to access this page.</p></div></div></div>";
    include('includes/scripts.php');
    include('includes/footer.php');
    ob_end_flush(); 
    exit();
}

// Initialize variables
$divisions = [];
$sections = [];
$selected_division = '';
$selected_division_name = '';
$error_message = '';
$success_message = '';

// Handle section deletion
if (isset($_GET['delete_section_id'])) {
    $section_id = $_GET['delete_section_id'];

    // Prepare and execute delete statement
    $deleteSql = "DELETE FROM sections WHERE section_id = ?";
    $stmt = mysqli_prepare($connect, $deleteSql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $section_id);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            $success_message = "Section deleted successfully.";
        } else {
            mysqli_stmt_close($stmt);
            $error_message = "Failed to delete section: " . mysqli_stmt_error($stmt);
        }
    } else {
        $error_message = "SQL error: " . mysqli_error($connect);
    }
}

// Fetch all divisions for the dropdown
$divisionSql = "SELECT division_id, division_name FROM divisions";
$result = mysqli_query($connect, $divisionSql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $divisions[] = $row;
    }
} else {
    $error_message = "Failed to fetch divisions: " . mysqli_error($connect);
}

// Fetch sections based on selected division
if (isset($_GET['division_id'])) {
    $selected_division = $_GET['division_id'];

    // Fetch sections for the selected division
    $sectionSql = "SELECT * FROM sections WHERE division_id = ?";
    $stmt = mysqli_prepare($connect, $sectionSql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $selected_division);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $sections[] = $row;
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "SQL error: " . mysqli_error($connect);
    }

    // Fetch the division name for the selected division
    $divisionNameSql = "SELECT division_name FROM divisions WHERE division_id = ?";
    $stmt = mysqli_prepare($connect, $divisionNameSql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $selected_division);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            $selected_division_name = $row['division_name'];
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "SQL error: " . mysqli_error($connect);
    }
}

mysqli_close($connect);
?>

<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Sections List</h1>
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

                <form method="GET" action="">
                    <div class="form-group">
                        <label for="division_id">Select Division:</label>
                        <select name="division_id" id="division_id" class="form-control" onchange="this.form.submit()">
                            <option value="">Select a Division</option>
                            <?php foreach ($divisions as $division): ?>
                                <option value="<?php echo htmlspecialchars($division['division_id']); ?>" <?php echo ($selected_division === $division['division_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($division['division_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>

                <?php if (!empty($selected_division_name)): ?>
                    <h2>Sections for Division: <?php echo htmlspecialchars($selected_division_name); ?></h2>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Serial Number</th>
                                <th>Section ID</th>
                                <th>Section Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($sections)): ?>
                                <?php
                                $serialNumber = 1; 
                                foreach ($sections as $section): ?>
                                    <tr>
                                        <td><?php echo $serialNumber++; ?></td>
                                        <td><?php echo htmlspecialchars($section['section_id']); ?></td>
                                        <td><?php echo htmlspecialchars($section['section_name']); ?></td>
                                        <td>
                                            <a href="section_edit.php?section_id=<?php echo htmlspecialchars($section['section_id']); ?>&division_id=<?php echo htmlspecialchars($section['division_id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <a href="?delete_section_id=<?php echo htmlspecialchars($section['section_id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this section?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">No sections found for this division.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/scripts.php');
include('includes/footer.php');
ob_end_flush(); 
?>
