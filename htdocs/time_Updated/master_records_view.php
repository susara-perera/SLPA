<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login if the session is not set.
    exit();
}

include('./dbc.php');
include('includes/header2.php');
include('includes/navbar.php');
include('includes/check_access.php'); // Include the check_access.php for the access control function

// Define the page name
$page = 'master_records_view.php';

// Check if the user has access to this page
if (!hasAccess($page)) {
    echo "<div class='container'><div class='row mx-md-n8'><div class='col px-md-5'><h1>Access Denied</h1><p>You do not have permission to access this page.</p></div></div></div>";
    include('includes/scripts.php');
    include('includes/footer.php');
    exit();
}


// Handle search
$search_id = '';
if (isset($_GET['search_id'])) {
    $search_id = $_GET['search_id'];
}
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200&display=swap" rel="stylesheet" />



<div class="row mx-md-n8">
    <div class="col px-md-5">
        <h1>Employee Master Records</h1>
        <div class="p-3 border bg-light">
            <div class="container">
                <?php if (isset($error_message)): ?>
                    <div id="error-message" style="color: red;"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <?php if (isset($success_message)): ?>
                    <div id="success-message" style="color: green;"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <!-- Search Form -->
                <form method="GET" action="">
                    <div class="form-group">
                        <label for="search_id">Search by Employee ID:</label>
                        <input type="text" id="search_id" name="search_id" class="form-control" value="<?php echo htmlspecialchars($search_id); ?>">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </form>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Employee ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Division</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($search_id)) {
                            $sql = "SELECT e.employee_ID, e.employee_name, d.division_name
                                    FROM employees e
                                    JOIN divisions d ON e.division = d.division_id
                                    WHERE e.employee_ID = ?";
                            $stmt = mysqli_prepare($connect, $sql);
                            mysqli_stmt_bind_param($stmt, "i", $search_id);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                        } else {
                            $sql = "SELECT e.employee_ID, e.employee_name, d.division_name
                                    FROM employees e
                                    JOIN divisions d ON e.division = d.division_id";
                            $result = mysqli_query($connect, $sql);
                        }

                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>
                                <th scope='row'>{$row['employee_ID']}</th>
                                <td>{$row['employee_name']}</td>
                                <td>{$row['division_name']}</td>
                                <td>
                                    <a href='master_details_view.php?id={$row['employee_ID']}'>
                                        <button type='button' class='btn btn-info btn-sm'>
                                            <i class='fas fa-eye'></i>
                                        </button>
                                    </a>
                                </td>
                            </tr>";
                        }

                        if (!empty($search_id) && mysqli_num_rows($result) == 0) {
                            echo "<tr><td colspan='4'>No employee found with ID: $search_id</td></tr>";
                        }
                        ?>
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