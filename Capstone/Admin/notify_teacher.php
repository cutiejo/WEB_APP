<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $teacher_id = $_POST['teacher_id'];

    // Add a notification to the teacher
    $notification_message = "Your profile has been updated by the admin.";
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, created_at) VALUES (?, ?, 'teacher', NOW())");
    $stmt->bind_param("is", $teacher_id, $notification_message);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error notifying teacher: ' . $conn->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
