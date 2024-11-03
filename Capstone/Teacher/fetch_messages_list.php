<?php
session_start();
include '../db.php';

// Verify that the teacher is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    echo json_encode(['status' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT DISTINCT users.id, users.role, messages.created_at AS last_message_time
        FROM users
        JOIN messages ON (messages.sender_id = users.id OR messages.receiver_id = users.id)
        WHERE users.id != ? AND (messages.sender_id = ? OR messages.receiver_id = ?)
        ORDER BY last_message_time DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iii', $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = [
        'id' => $row['id'],
        'role' => $row['role'],
        'last_message_time' => $row['last_message_time'] ? $row['last_message_time'] : "No messages",
    ];
}

echo json_encode(['status' => true, 'users' => $users]);
