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
$page = 'userList.php';

// Check if the user has access to this page
if (!hasAccess($page)) {
    echo "<div class='container'><div class='row mx-md-n8'><div class='col px-md-5'><h1>Access Denied</h1><p>You do not have permission to access this page.</p></div></div></div>";
    include('includes/scripts.php');
    include('includes/footer.php');
    ob_end_flush(); 
    exit();
}

// Initialize search variable
$searchEmployeeID = isset($_POST['employee_id']) ? mysqli_real_escape_string($connect, $_POST['employee_id']) : '';

// Initialize query and conditions
$userSql = "SELECT * FROM users";
$conditions = [];

if (!empty($searchEmployeeID)) {
    $conditions[] = "employee_ID = '$searchEmployeeID'";
}

if (!empty($conditions)) {
    $userSql .= " WHERE " . implode(' AND ', $conditions);
}

// Search users by their Employee ID
$users = [];
$result = mysqli_query($connect, $userSql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
} else {
    $error_message = "Failed to fetch users: " . mysqli_error($connect);
}
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200&display=swap" rel="stylesheet" />

<div class="row mx-md-n8">
    <div class="col px-md-5">
        <h1>All Users</h1>
        <div class="p-3 border bg-light">
            <div class="container">
                <!-- Search Form -->
                <form method="post" action="">
                    <div class="form-group">
                        <label for="employee_id">Search by Employee ID:</label>
                        <input type="text" id="employee_id" name="employee_id" class="form-control" value="<?php echo htmlspecialchars($searchEmployeeID); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>

                <?php
                if (isset($error_message)) {
                    echo '<p style="color: red;">' . $error_message . '</p>';
                }
                ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Serial Number</th>
                            <th>Division</th>
                            <th>Role</th>
                            <th>Employee ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $serialNumber = 1; 
                        foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $serialNumber++; ?></td> 
                                <td><?php echo htmlspecialchars($user['division']); ?></td>
                                <td><?php echo htmlspecialchars($user['role']); ?></td>
                                <td><?php echo htmlspecialchars($user['employee_ID']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/scripts.php');
include('includes/footer.php');

ob_end_flush(); 
?>
