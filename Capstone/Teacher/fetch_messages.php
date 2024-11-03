<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    echo json_encode(['status' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$receiver_id = $_GET['receiver_id'];

$sql = "SELECT sender_id, message_content, created_at
        FROM messages
        WHERE (sender_id = ? AND receiver_id = ?)
           OR (sender_id = ? AND receiver_id = ?)
        ORDER BY created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iiii', $user_id, $receiver_id, $receiver_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'sender_id' => $row['sender_id'],
        'message_content' => $row['message_content'],
        'created_at' => $row['created_at'],
    ];
}

echo json_encode(['status' => true, 'messages' => $messages]);
