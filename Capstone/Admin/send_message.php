<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => false, 'message' => 'Unauthorized']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$receiver_id = $input['receiver_id'];
$message_content = $input['message_content'];
$sender_id = $_SESSION['user_id']; // Assuming admin user_id is in session



// Insert new message into the database
$query = "INSERT INTO messages (sender_id, receiver_id, message_content, created_at) VALUES ('$sender_id', '$receiver_id', '$message_content', NOW())";
$result = mysqli_query($conn, $query);

if ($result) {
    echo json_encode(['status' => true, 'message' => 'Message sent']);
} else {
    echo json_encode(['status' => false, 'message' => 'Failed to send message']);
}
?>