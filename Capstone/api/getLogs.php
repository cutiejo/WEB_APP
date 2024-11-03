<?php
header('Content-Type: application/json');

require '../db.php';

$userId = $_GET['user_id'];

$query = "SELECT * FROM logs WHERE user_id = ? ORDER BY timestamp DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$logs = [];

while ($row = $result->fetch_assoc()) {
    $logs[] = $row;
}

echo json_encode(['success' => true, 'logs' => $logs]);

$stmt->close();
$conn->close();
?>