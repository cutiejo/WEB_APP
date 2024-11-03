<?php
session_start();
header("Content-Type: application/json");

// Check if the user is logged in as an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => false, 'message' => 'Unauthorized']);
    exit();
}

include '../db.php';

$data = json_decode(file_get_contents("php://input"), true);
$receiver_id = $data['receiver_id'] ?? null;
$message_content = $data['message_content'] ?? '';

if (!$receiver_id || !$message_content) {
    echo json_encode(['status' => false, 'message' => 'Receiver ID or message content missing.']);
    exit();
}

// Insert message into the database
$sql = "INSERT INTO messages (sender_id, receiver_id, sender_role, receiver_role, message_content) 
        VALUES (:sender_id, :receiver_id, 'admin', 'student', :message_content)";
$stmt = $pdo->prepare($sql);
$success = $stmt->execute([
    'sender_id' => $_SESSION['user_id'],
    'receiver_id' => $receiver_id,
    'message_content' => $message_content
]);

echo json_encode(['status' => $success, 'message' => $success ? 'Message sent successfully' : 'Failed to send message']);
?>
