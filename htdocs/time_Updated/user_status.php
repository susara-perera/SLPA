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
$page = 'user_status.php';

// Check if the user has access to this page
if (!hasAccess($page)) {
    echo "<div class='container'><div class='row mx-md-n8'><div class='col px-md-5'><h1>Access Denied</h1><p>You do not have permission to access this page.</p></div></div></div>";
    include('includes/scripts.php');
    include('includes/footer.php');
    ob_end_flush(); 
    exit();
}

// Initialize filter variables to check the user status
$filter = '';
if (isset($_POST['search'])) {
    $searchOption = $_POST['search_option'];
    $date = $_POST['date'] ?? '';

    switch ($searchOption) {
        case 'active':
            $filter = "WHERE l.status = 'Active'";
            break;
        case 'last_hour':
            $filter = "WHERE l.login_time >= NOW() - INTERVAL 1 HOUR";
            break;
        case 'specific_date':
            if ($date) {
                $filter = "WHERE DATE(l.login_time) = '$date'";
            }
            break;
    }
}

// Fetch login status from the database
$logins = [];
$sqlLogins = "
    SELECT u.id, u.division, u.role, u.employee_ID, l.login_time, l.logout_time, l.status 
    FROM login l
    JOIN users u ON l.user_id = u.id
    $filter
    ORDER BY l.login_time DESC
";
$result = mysqli_query($connect, $sqlLogins);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $logins[] = $row;
    }
} else {
    $error_message = "Failed to fetch login records: " . mysqli_error($connect);
}

// Count currently active users
$activeCount = 0;
if (empty($filter) || $filter === "WHERE l.status = 'Active'") {
    $sqlActiveCount = "SELECT COUNT(*) as active_count FROM login WHERE status = 'Active'";
    $resultActiveCount = mysqli_query($connect, $sqlActiveCount);
    if ($resultActiveCount) {
        $row = mysqli_fetch_assoc($resultActiveCount);
        $activeCount = $row['active_count'];
    } else {
        $error_message = "Failed to fetch active user count: " . mysqli_error($connect);
    }
}

mysqli_close($connect);
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200&display=swap" rel="stylesheet" />

<div class="row mx-md-n8">
    <div class="col px-md-5">
        <h1>User Login Status</h1>
        <div class="p-3 border bg-light">
            <div class="container">
                <?php
                if (isset($error_message)) {
                    echo '<p style="color: red;">' . $error_message . '</p>';
                }
                ?>

                <p><strong>Currently Active Users: <?php echo $activeCount; ?></strong></p>

                <form method="post" action="">
                    <div class="form-group">
                        <label for="search_option">Search by:</label>
                        <select id="search_option" name="search_option" class="form-control">
                            <option value="">Select Option</option>
                            <option value="active">Currently Active Users</option>
                            <option value="last_hour">Logged in Last Hour</option>
                            <option value="specific_date">Specific Date</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date">Date (MM-DD-YYYY):</label>
                        <input type="date" id="date" name="date" class="form-control" placeholder="Enter date">
                    </div>
                    <button type="submit" name="search" class="btn btn-primary">Search</button>
                </form>

                <table class="table table-striped mt-3">
                    <thead>
                        <tr>
                            <th>Serial Number</th>
                            <th>Employee ID</th>
                            <th>Division</th>
                            <th>Role</th>
                            <th>Login Time</th>
                            <th>Logout Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $serialNumber = 1; // Initialize serial number
                        foreach ($logins as $login): ?>
                            <tr>
                                <td><?php echo $serialNumber++; ?></td>
                                <td><?php echo htmlspecialchars($login['employee_ID']); ?></td>
                                <td><?php echo htmlspecialchars($login['division']); ?></td>
                                <td><?php echo htmlspecialchars($login['role']); ?></td>
                                <td><?php echo htmlspecialchars($login['login_time']); ?></td>
                                <td><?php echo htmlspecialchars($login['logout_time'] ?: 'Still logged in'); ?></td>
                                <td><?php echo htmlspecialchars($login['status']); ?></td>
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
