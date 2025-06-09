<?php
include('./dbc.php');
include('includes/header.php');
include('includes/navbar.php');
include('includes/check_access.php'); 


$page = 'division_List.php';

// Check if the user has access to this page
if (!hasAccess($page)) {
    echo "<div class='container'><div class='row mx-md-n8'><div class='col px-md-5'><h1>Access Denied</h1><p>You do not have permission to access this page.</p></div></div></div>";
    include('includes/scripts.php');
    include('includes/footer.php');
    exit();
}

$divisions = [];
$error_message = '';

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

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Serial Number</th>
                            <th>Division ID</th>
                            <th>Division Name</th>
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
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">No divisions found.</td>
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




