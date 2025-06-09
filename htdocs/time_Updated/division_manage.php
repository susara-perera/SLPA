<?php

include('./dbc.php');
include('includes/header.php');
include('includes/navbar.php');
include('includes/check_access.php'); 


$page = 'division_manage.php';

// Check if the user has access to this page
if (!hasAccess($page)) {
    echo "<div class='container'><div class='row mx-md-n8'><div class='col px-md-5'><h1>Access Denied</h1><p>You do not have permission to access this page.</p></div></div></div>";
    include('includes/scripts.php');
    include('includes/footer.php');
    exit();
}

$divisions = [];
$error_message = '';
$success_message = '';

// Handle division deletion
if (isset($_GET['delete_division_id'])) {
    $division_id = $_GET['delete_division_id'];

    // Prepare and execute delete statement
    $deleteSql = "DELETE FROM divisions WHERE division_id = ?";
    $stmt = mysqli_prepare($connect, $deleteSql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $division_id);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            $success_message = "Division deleted successfully.";
        } else {
            mysqli_stmt_close($stmt);
            $error_message = "Failed to delete division: " . mysqli_stmt_error($stmt);
        }
    } else {
        $error_message = "SQL error: " . mysqli_error($connect);
    }
}

// Fetch all divisions from the database
$sqlDivisions = "SELECT * FROM divisions";
$result = mysqli_query($connect, $sqlDivisions);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $divisions[] = $row;
    }
} else {
    $error_message = "Failed to fetch divisions: " . mysqli_error($connect);
}

$totalDivisions = count($divisions);

mysqli_close($connect);
?>

<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Division List</h1>
        </div>
        <div class="col-sm-6 text-right">
            <h4>Total Divisions: <?php echo $totalDivisions; ?></h4>
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

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Serial Number</th>
                            <th>Division ID</th>
                            <th>Division Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($divisions)): ?>
                            <?php $serialNumber = 1;
                            ?>
                            <?php foreach ($divisions as $division): ?>
                                <tr>
                                    <td><?php echo $serialNumber++; ?></td>
                                    <td><?php echo htmlspecialchars($division['division_id']); ?></td>
                                    <td><?php echo htmlspecialchars($division['division_name']); ?></td>
                                    <td>
                                        <a href="division_edit.php?division_id=<?php echo htmlspecialchars($division['division_id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="?delete_division_id=<?php echo htmlspecialchars($division['division_id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this division?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">No divisions found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>