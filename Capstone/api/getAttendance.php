<?php
header('Content-Type: application/json');

require '../db.php';

$studentId = $_GET['student_id'];

$query = "SELECT * FROM attendance WHERE student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();

$attendance = [];

while ($row = $result->fetch_assoc()) {
    $attendance[] = $row;
}

echo json_encode(['success' => true, 'attendance' => $attendance]);

$stmt->close();
$conn->close();
?>