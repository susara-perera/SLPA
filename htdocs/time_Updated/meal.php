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
$page = 'meal.php';

// Check if the user has access to this page
if (!hasAccess($page)) {
    echo "<div class='container'><div class='row mx-md-n8'><div class='col px-md-5'><h1>Access Denied</h1><p>You do not have permission to access this page.</p></div></div></div>";
    include('includes/scripts.php');
    include('includes/footer.php');
    ob_end_flush(); // Flush the output buffer
    exit();
}
?>

<div class="row mx-md-n8">
    <div class="col px-md-5">
        <div class="p-3 border bg-light">
            <div class="container">
                <form action="/action_page.php">
                    <div>
                        <div class="form-group">
                            <label for="section_id">Select Section:</label>
                            <select name="section_id" id="section_id" class="form-control">
                                <!-- You should populate these options dynamically from the database -->
                                <option value="1">Information System</option>
                                <option value="2">Administration</option>
                                <option value="3">Unit 1</option>
                                <option value="4">Level 2</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="date">Select Date:</label>
                            <input type="date" class="form-control" id="date" name="date">
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg btn-block">Report</button>
                    </div>
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
