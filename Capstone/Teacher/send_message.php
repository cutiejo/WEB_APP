<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    echo json_encode(['status' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);
$receiver_id = $data['receiver_id'];
$message_content = $data['message_content'];

$sql = "INSERT INTO messages (sender_id, receiver_id, message_content, created_at)
        VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iis', $user_id, $receiver_id, $message_content);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['status' => true, 'message' => 'Message sent']);
} else {
    echo json_encode(['status' => false, 'message' => 'Failed to send message']);
}
