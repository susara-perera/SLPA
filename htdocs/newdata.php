<?php
require 'vendor/autoload.php'; // Adjust the path if needed

use PhpOffice\PhpSpreadsheet\IOFactory;

// Database connection settings
$dbServerName = "localhost";
$dbUserName = "root";
$dbPassword = "";
$dbName = "slpa_db";

// Create a connection
$connect = mysqli_connect($dbServerName, $dbUserName, $dbPassword, $dbName);

if (!$connect) {
    error_log("Connection failed: " . mysqli_connect_error());
    die("Sorry, we are experiencing technical difficulties. Please try again later.");
}

mysqli_set_charset($connect, "utf8");

// Create the employees table if it doesn't exist
$sqlEmployees = "CREATE TABLE IF NOT EXISTS employees (
    employee_ID INT AUTO_INCREMENT PRIMARY KEY,
    employee_name VARCHAR(255) NOT NULL,
    division VARCHAR(255) NOT NULL,
    section VARCHAR(255) NOT NULL,
    designation VARCHAR(255) NOT NULL,
    appointment_date DATE NOT NULL,
    gender ENUM('Male', 'Female') NOT NULL,
    status ENUM('Active', 'Inactive') NOT NULL,
    nic_number VARCHAR(50) NOT NULL UNIQUE,
    telephone_number VARCHAR(50) NOT NULL,
    address TEXT NOT NULL,
    card_valid_date DATE NOT NULL,
    card_issued_date DATE NOT NULL,
    picture VARCHAR(255) NOT NULL
);";

mysqli_query($connect, $sqlEmployees);

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file']['tmp_name'];

    // Load the Excel file
    $spreadsheet = IOFactory::load($file);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();

    // Prepare the SQL statement
    $stmt = $connect->prepare("INSERT INTO employees 
        (employee_name, division, section, designation, appointment_date, gender, status, 
        nic_number, telephone_number, address, card_valid_date, card_issued_date, picture) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($rows as $row) {
        // Adjust these indices based on your Excel file structure
        $employee_name = $row[0];
        $division = $row[1];
        $section = $row[2];
        $designation = $row[3];
        $appointment_date = $row[4]; // Format: YYYY-MM-DD
        $gender = $row[5]; // "Male" or "Female"
        $status = $row[6]; // "Active" or "Inactive"
        $nic_number = $row[7];
        $telephone_number = $row[8];
        $address = $row[9];
        $card_valid_date = $row[10]; // Format: YYYY-MM-DD
        $card_issued_date = $row[11]; // Format: YYYY-MM-DD
        $picture = $row[12]; // URL or path to picture

        // Bind parameters and execute
        $stmt->bind_param("sssssssssssss", 
            $employee_name, $division, $section, $designation, 
            $appointment_date, $gender, $status, 
            $nic_number, $telephone_number, $address, 
            $card_valid_date, $card_issued_date, $picture);

        if (!$stmt->execute()) {
            error_log("Error inserting data: " . $stmt->error);
        }
    }

    $stmt->close();
    echo "Data imported successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Employee Data</title>
</head>
<body>
    <h1>Upload Employee Data</h1>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="file" name="excel_file" accept=".xlsx, .xls" required>
        <button type="submit">Upload</button>
    </form>
</body>
</html>
