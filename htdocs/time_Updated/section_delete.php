<?php
include('./dbc.php');

// Check if section_id is provided
if (isset($_GET['section_id'])) {
    $section_id = $_GET['section_id'];

    // delete query
    $deleteSql = "DELETE FROM sections WHERE section_id = ?";
    $stmt = mysqli_prepare($connect, $deleteSql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $section_id);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            mysqli_close($connect);

            // Redirect to sectionList.php with success message
            header("Location: sectionList.php?success=Section deleted successfully.");
            exit();
        } else {
            mysqli_stmt_close($stmt);
            mysqli_close($connect);

            // Redirect to sectionList.php with error message
            header("Location: sectionList.php?error=Failed to delete section: " . urlencode(mysqli_stmt_error($stmt)));
            exit();
        }
    } else {
        mysqli_close($connect);

        // Redirect to sectionList.php with SQL error message
        header("Location: sectionList.php?error=SQL error: " . urlencode(mysqli_error($connect)));
        exit();
    }
} else {
    mysqli_close($connect);

    // Redirect to sectionList.php with an error message if no section_id is provided
    header("Location: sectionList.php?error=No section ID provided.");
    exit();
}
