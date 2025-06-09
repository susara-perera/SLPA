<?php
include('./dbc.php');

function hasAccess($page) {
    global $connect;

    // Check if user is logged in
    if (!isset($_SESSION['role'])) {
        return false;
    }

    $role = mysqli_real_escape_string($connect, $_SESSION['role']);
    $page = mysqli_real_escape_string($connect, $page);

    // Query to check if the role has access to the page
    $result = mysqli_query($connect, "SELECT COUNT(*) AS count FROM role_access WHERE role = '$role' AND page = '$page'");
    $row = mysqli_fetch_assoc($result);

    return $row['count'] > 0;
}
?>
