<?php
include('./dbc.php');

$division_id = $_GET['division_id'];

// Fetch sections based on division_id and also this is a common fetching section file. 
$sql = "SELECT section_id, section_name FROM sections WHERE division_id = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("s", $division_id);
$stmt->execute();
$result = $stmt->get_result();

$sections = [];

while ($row = $result->fetch_assoc()) {
    $sections[] = $row;
}

echo json_encode($sections);

$stmt->close();
$connect->close();
?>
