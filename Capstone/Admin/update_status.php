<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE students SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $status, $student_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Status updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update status']);
    }
    $stmt->close();
}
?>
