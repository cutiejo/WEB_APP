<?php
session_start();
header("Content-Type: application/json");

// Check if the user is logged in as an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => false, 'message' => 'Unauthorized']);
    exit();
}

include '../db.php';

$receiver_id = $_GET['receiver_id'] ?? null;
if (!$receiver_id) {
    echo json_encode(['status' => false, 'message' => 'Receiver ID not provided.']);
    exit();
}

// Fetch conversation with specific receiver
$sql = "SELECT * FROM messages WHERE (sender_id = :receiver_id AND receiver_id = :admin_id)
        OR (sender_id = :admin_id AND receiver_id = :receiver_id) ORDER BY created_at ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['receiver_id' => $receiver_id, 'admin_id' => $_SESSION['user_id']]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['status' => true, 'messages' => $messages]);
?>
