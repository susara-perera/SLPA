<?php
session_start();
ob_start();

include('./dbc.php');
include('includes/header2.php');
include('includes/navbar.php');
include('includes/check_access.php');


$page = 'userManage.php';

// Check if the user has access to this page
if (!hasAccess($page)) {
    echo "<div class='container'><div class='row mx-md-n8'><div class='col px-md-5'><h1>Access Denied</h1><p>You do not have permission to access this page.</p></div></div></div>";
    include('includes/scripts.php');
    include('includes/footer.php');
    exit();
}

// Handle user deletion
if (isset($_GET['delete_id'])) {
    $userId = mysqli_real_escape_string($connect, $_GET['delete_id']);

    // delete query to delete the user
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = mysqli_prepare($connect, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) > 0) {
            header("Location: userManage.php?success=user_deleted");
            ob_end_flush(); 
            exit();
        } else {
            echo '<p style="color: red;">No rows affected. Check if the ID exists.</p>';
        }

        mysqli_stmt_close($stmt);
    } else {
        echo '<p style="color: red;">SQL error: ' . mysqli_error($connect) . '</p>';
    }
}

// Initialize variables for search
$searchRole = isset($_POST['role']) ? mysqli_real_escape_string($connect, $_POST['role']) : '';
$searchEmployeeID = isset($_POST['employee_id']) ? mysqli_real_escape_string($connect, $_POST['employee_id']) : '';

// query to count the users
$userSql = "SELECT * FROM users";
$countSql = "SELECT COUNT(*) as total FROM users";
$conditions = [];

if (!empty($searchRole)) {
    $conditions[] = "role = '$searchRole'";
}
if (!empty($searchEmployeeID)) {
    $conditions[] = "employee_ID = '$searchEmployeeID'";
}

if (!empty($conditions)) {
    $userSql .= " WHERE " . implode(' AND ', $conditions);
    $countSql .= " WHERE " . implode(' AND ', $conditions);
}

// Search users by employee ID and based on the user roles
$users = [];
$result = mysqli_query($connect, $userSql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
} else {
    $error_message = "Failed to fetch users: " . mysqli_error($connect);
}

// Fetch the count of users based on search criteria
$countResult = mysqli_query($connect, $countSql);
$totalCount = 0;
if ($countResult) {
    $countRow = mysqli_fetch_assoc($countResult);
    $totalCount = $countRow['total'];
}
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200&display=swap" rel="stylesheet" />

<div class="row mx-md-n8">
    <div class="col px-md-5">
        <h1>All Users</h1>
        <div class="p-3 border bg-light">
            <div class="container">
                <?php
                if (isset($error_message)) {
                    echo '<p style="color: red;">' . $error_message . '</p>';
                }
                if (isset($_GET['success']) && $_GET['success'] === 'user_deleted') {
                    echo '<p style="color: green;">User has been successfully deleted!</p>';
                }
                if (isset($_GET['error'])) {
                    $error_messages = [
                        'delete_failed' => 'Failed to delete the user.',
                        'sql_error' => 'SQL error occurred.'
                    ];
                    echo '<p style="color: red;">' . $error_messages[$_GET['error']] . '</p>';
                }
                ?>

                <!-- Search Form -->
                <form method="post" action="">
                    <div class="form-group">
                        <label for="role">Search by Role:</label>
                        <select id="role" name="role" class="form-control">
                            <option value="">Select Role</option>
                            <option value="Super_Ad" <?php if ($searchRole == 'Super_Ad') echo 'selected'; ?>>Super Admin</option>
                            <option value="Administration" <?php if ($searchRole == 'Administration') echo 'selected'; ?>>Administration</option>
                            <option value="Administration_clerk" <?php if ($searchRole == 'Administration_clerk') echo 'selected'; ?>>Administrative Clerk</option>
                            <option value="clerk" <?php if ($searchRole == 'clerk') echo 'selected'; ?>>Clerk</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="employee_id">Search by Employee ID:</label>
                        <input type="text" id="employee_id" name="employee_id" class="form-control" value="<?php echo htmlspecialchars($searchEmployeeID); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>

                <?php if (!empty($searchRole)): ?>
                    <p><strong>Total count for role '<?php echo htmlspecialchars($searchRole); ?>': <?php echo $totalCount; ?></strong></p>
                <?php endif; ?>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Serial Number</th>
                            <th>Division</th>
                            <th>Role</th>
                            <th>Employee ID</th>
                            <th>Action</th>
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
                                <td>
                                    <a href="userManage.php?delete_id=<?php echo urlencode($user['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                </td>
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