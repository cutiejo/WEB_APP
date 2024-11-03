<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include '../db.php';  // Connect to the database

$query = "SELECT id, title, content, posting_date, image, category FROM announcements ORDER BY posting_date DESC";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $announcements = array();
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
    echo json_encode($announcements);
} else {
    echo json_encode(array("message" => "No announcements available"));
}

$conn->close();
?>
