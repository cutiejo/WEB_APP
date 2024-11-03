<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'db.php';

$sql = "SELECT * FROM notifications WHERE read_status = 'UNREAD' ORDER BY timestamp DESC";
$result = $conn->query($sql);

$notifications = array();
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

echo json_encode($notifications);

$conn->close();
?>