<?php
include('./dbc.php');
include('includes/header.php');
include('includes/navbar.php');
include('includes/check_access.php'); 


$page = 'division.php';

// Check if the user has access to this page
if (!hasAccess($page)) {
    echo "<div class='container'><div class='row mx-md-n8'><div class='col px-md-5'><h1>Access Denied</h1><p>You do not have permission to access this page.</p></div></div></div>";
    include('includes/scripts.php');
    include('includes/footer.php');
    exit();
}
?>

<div class="row mx-md-n8">
    <div class="col px-md-5">
        <h1>Add New Division</h1>
        <div class="p-3 border bg-light">
            <div class="container">
                <form method="POST" action="./division_action.php">
                    <div class="form-group">
                        <label for="divisionID">Division ID</label>
                        <input class="form-control" name="division_id" type="text" id="divisionID" required>
                    </div>
                    <div class="form-group">
                        <label for="divisionName">New Division Name</label>
                        <input class="form-control" name="division_name" type="text" id="divisionName" required>
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