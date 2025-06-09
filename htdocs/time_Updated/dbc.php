<?php
// Database connection settings
$dbServerName = "localhost";
$dbUserName = "root";
$dbPassword = "";
$dbName = "slpa_db";

// Create a connection
$connect = mysqli_connect($dbServerName, $dbUserName, $dbPassword, $dbName);


if (!$connect) {
    // Log the detailed error message to a log file
    error_log("Connection failed: " . mysqli_connect_error());

    // Display error message to the user
    die("Sorry, we are experiencing technical difficulties. Please try again later.");
}

mysqli_set_charset($connect, "utf8");

?>

