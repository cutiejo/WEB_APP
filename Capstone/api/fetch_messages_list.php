<?php
session_start();
header("Content-Type: application/json");

// Check if the user is an admin and logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => false, 'message' => 'Unauthorized']);
    exit();
}

include '../db.php';

// Fetch conversations from the database
$sql = "SELECT DISTINCT sender_id AS id, sender_role AS role, MAX(created_at) AS last_message_time 
        FROM messages 
        WHERE receiver_id = :admin_id 
        GROUP BY sender_id 
        ORDER BY last_message_time DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['admin_id' => $_SESSION['user_id']]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($conversations) {
    echo json_encode(['status' => true, 'users' => $conversations]);
} else {
    echo json_encode(['status' => false, 'message' => 'No conversations found.']);
}
?>
